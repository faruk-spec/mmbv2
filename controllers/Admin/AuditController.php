<?php
/**
 * Audit Explorer Controller
 *
 * Provides a safe, visual query-builder UI against the activity_logs table.
 * Accessible to super_admin, admin, and audit_viewer roles.
 *
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;
use Core\View;

class AuditController extends BaseController
{
    /**
     * Columns users are allowed to SELECT / filter on.
     * Prevents any attempt to touch other tables.
     */
    private const ALLOWED_COLUMNS = [
        'id', 'user_id', 'action', 'module', 'tenant_id',
        'resource_type', 'resource_id', 'user_role', 'status',
        'readable_message', 'ip_address', 'device', 'browser',
        'request_id', 'created_at',
        // joined columns
        'user_name', 'user_email',
    ];

    /**
     * Allowed aggregate functions for GROUP BY / SELECT
     */
    private const ALLOWED_AGGREGATES = ['COUNT', 'SUM', 'AVG', 'MIN', 'MAX'];

    /**
     * Allowed operators for WHERE conditions
     */
    private const ALLOWED_OPERATORS = ['=', '!=', 'LIKE', 'NOT LIKE', '>', '<', '>=', '<=', 'IS NULL', 'IS NOT NULL'];

    public function __construct()
    {
        $this->requireAuth();
        $this->requireAuditAccess();
    }

    // ------------------------------------------------------------------ //
    // Pages
    // ------------------------------------------------------------------ //

    /**
     * Audit Explorer landing page
     */
    /**
     * All known platform modules — always shown in the filter dropdown
     * even if no activity_logs rows exist for that module yet.
     */
    private const KNOWN_MODULES = [
        'auth', 'admin', 'qr', 'whatsapp', 'proshare', 'codexpro',
        'imgtxt', 'convertx', 'billx', 'resumex', 'devzone',
        'settings', 'users', 'security', 'api', 'platform_plans', 'audit',
    ];

    public function index(): void
    {
        $db = Database::getInstance();

        // Stats for the header cards
        $stats = $db->fetch(
            "SELECT
                COUNT(*) AS total,
                COUNT(DISTINCT user_id) AS unique_users,
                COUNT(DISTINCT action) AS unique_actions,
                COUNT(DISTINCT COALESCE(module,'')) AS unique_modules
             FROM activity_logs"
        );

        // Pre-populate filter dropdowns
        $actions = $db->fetchAll('SELECT DISTINCT action FROM activity_logs ORDER BY action');

        // Merge DB modules with the hardcoded known-modules list so all
        // options appear even before any log entries exist for that module.
        $dbModules = $db->fetchAll(
            "SELECT DISTINCT module FROM activity_logs WHERE module IS NOT NULL AND module != '' ORDER BY module"
        );
        $dbModuleValues = array_column($dbModules, 'module');
        $allModules = array_unique(array_merge(self::KNOWN_MODULES, $dbModuleValues));
        sort($allModules);
        $modules = array_map(fn($m) => ['module' => $m], $allModules);

        $userRoles = $db->fetchAll(
            "SELECT DISTINCT user_role FROM activity_logs WHERE user_role IS NOT NULL AND user_role != '' ORDER BY user_role"
        );

        // Users list for the user-filter autocomplete (name + email)
        $users = $db->fetchAll(
            "SELECT id, name, email FROM users ORDER BY name LIMIT 500"
        );

        $this->view('admin/audit/index', [
            'title'       => 'Audit Explorer',
            'stats'       => $stats,
            'actions'     => $actions,
            'modules'     => $modules,
            'userRoles'   => $userRoles,
            'users'       => $users,
            'allowedCols' => self::ALLOWED_COLUMNS,
        ]);
    }

    // ------------------------------------------------------------------ //
    // Query endpoint (POST, returns JSON)
    // ------------------------------------------------------------------ //

    /**
     * Execute a safe query built by the visual query builder.
     *
     * Expected POST body (JSON or form):
     * {
     *   "select"   : ["action","user_name","COUNT(*)"],   // columns / expressions
     *   "where"    : [{"col":"action","op":"=","val":"login"}],
     *   "group_by" : ["action"],                          // optional
     *   "order_by" : "created_at",                        // optional
     *   "order_dir": "DESC",
     *   "limit"    : 100
     * }
     */
    public function query(): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['error' => 'Invalid CSRF token.'], 403);
            return;
        }

        // Accept JSON body or form POST
        $raw = file_get_contents('php://input');
        $body = json_decode($raw, true) ?: $_POST;

        try {
            [$sql, $params] = $this->buildSafeQuery($body);
        } catch (\InvalidArgumentException $e) {
            $this->json(['error' => $e->getMessage()], 400);
            return;
        }

        try {
            $db   = Database::getInstance();
            $rows = $db->fetchAll($sql, $params);

            $this->json([
                'sql'   => $this->redactSql($sql, $params),
                'count' => count($rows),
                'data'  => $rows,
            ]);
        } catch (\Exception $e) {
            $this->json(['error' => 'Query failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Export the query result as CSV or JSON (GET, same params as POST query
     * but passed as query string + format param).
     */
    public function export(): void
    {
        $format = strtolower($this->input('format', 'csv'));
        if (!in_array($format, ['csv', 'json'])) {
            $format = 'csv';
        }

        $body = [
            'select'    => array_filter(explode(',', $this->input('select', '*'))),
            'group_by'  => array_filter(explode(',', $this->input('group_by', ''))),
            'order_by'  => $this->input('order_by', 'created_at'),
            'order_dir' => $this->input('order_dir', 'DESC'),
            'limit'     => min(10000, max(1, (int)$this->input('limit', 1000))),
            'where'     => json_decode($this->input('where', '[]'), true) ?: [],
        ];

        try {
            [$sql, $params] = $this->buildSafeQuery($body);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo $e->getMessage();
            return;
        }

        $db   = Database::getInstance();
        $rows = $db->fetchAll($sql, $params);

        $filename = 'audit_query_' . date('Y-m-d_His');

        if ($format === 'json') {
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="' . $filename . '.json"');
            echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        }

        // CSV
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        echo "\xEF\xBB\xBF";
        if (!empty($rows)) {
            $out = fopen('php://output', 'w');
            fputcsv($out, array_keys($rows[0]));
            foreach ($rows as $row) {
                fputcsv($out, array_values($row));
            }
            fclose($out);
        }
        exit;
    }

    // ------------------------------------------------------------------ //
    // Safe query builder
    // ------------------------------------------------------------------ //

    /**
     * Build a parameterized SELECT from a validated spec array.
     *
     * @throws \InvalidArgumentException on any unsafe input
     * @return array [string $sql, array $params]
     */
    private function buildSafeQuery(array $spec): array
    {
        // ---- SELECT ----
        $selectRaw = $spec['select'] ?? ['*'];
        if (!is_array($selectRaw) || empty($selectRaw)) {
            $selectRaw = ['*'];
        }
        $selectParts = [];
        foreach ($selectRaw as $col) {
            $selectParts[] = $this->validateSelectExpression(trim((string)$col));
        }
        $selectClause = implode(', ', $selectParts);

        // ---- BASE FROM ----
        $from = 'activity_logs al LEFT JOIN users u ON al.user_id = u.id';

        // ---- WHERE ----
        $whereParts = [];
        $params     = [];
        $conditions = $spec['where'] ?? [];
        if (!is_array($conditions)) {
            $conditions = [];
        }
        foreach ($conditions as $cond) {
            if (!is_array($cond)) {
                continue;
            }
            $col = trim((string)($cond['col'] ?? ''));
            $op  = strtoupper(trim((string)($cond['op'] ?? '=')));
            $val = $cond['val'] ?? '';

            // Validate column name
            $qcol = $this->resolveColumn($col);

            // Validate operator
            if (!in_array($op, self::ALLOWED_OPERATORS, true)) {
                throw new \InvalidArgumentException("Unsupported operator: {$op}");
            }

            if ($op === 'IS NULL' || $op === 'IS NOT NULL') {
                $whereParts[] = "{$qcol} {$op}";
            } elseif ($op === 'LIKE' || $op === 'NOT LIKE') {
                $whereParts[] = "{$qcol} {$op} ?";
                $params[]      = $val;
            } else {
                $whereParts[] = "{$qcol} {$op} ?";
                $params[]      = $val;
            }
        }
        $whereClause = $whereParts ? ('WHERE ' . implode(' AND ', $whereParts)) : '';

        // ---- GROUP BY ----
        $groupByRaw  = $spec['group_by'] ?? [];
        $groupByClause = '';
        if (!empty($groupByRaw) && is_array($groupByRaw)) {
            $gbParts = [];
            foreach ($groupByRaw as $col) {
                $gbParts[] = $this->resolveColumn(trim((string)$col));
            }
            $groupByClause = 'GROUP BY ' . implode(', ', $gbParts);
        }

        // ---- ORDER BY ----
        $orderByRaw = trim((string)($spec['order_by'] ?? 'created_at'));
        $orderDir   = strtoupper(trim((string)($spec['order_dir'] ?? 'DESC')));
        if (!in_array($orderDir, ['ASC', 'DESC'], true)) {
            $orderDir = 'DESC';
        }
        $orderByClause = '';
        if ($orderByRaw && $orderByRaw !== 'none') {
            // Allow aggregate expressions in ORDER BY only if GROUP BY is active
            if (!empty($groupByRaw) && preg_match('/^(COUNT|SUM|AVG|MIN|MAX)\(\*\)$/i', $orderByRaw)) {
                $orderByClause = "ORDER BY {$orderByRaw} {$orderDir}";
            } else {
                $orderByClause = 'ORDER BY ' . $this->resolveColumn($orderByRaw) . " {$orderDir}";
            }
        }

        // ---- LIMIT ----
        $limit = min(10000, max(1, (int)($spec['limit'] ?? 100)));

        $sql = trim(implode(' ', array_filter([
            "SELECT {$selectClause}",
            "FROM {$from}",
            $whereClause,
            $groupByClause,
            $orderByClause,
            "LIMIT {$limit}",
        ])));

        return [$sql, $params];
    }

    /**
     * Validate and return a qualified column reference like `al.action`.
     *
     * @throws \InvalidArgumentException
     */
    private function resolveColumn(string $col): string
    {
        // Strip table prefix if provided (al. / u.)
        $bare = preg_replace('/^(al\.|u\.)/', '', $col);

        // Joined columns live in `u`
        $uCols = ['user_name' => 'u.name', 'user_email' => 'u.email'];
        if (isset($uCols[$bare])) {
            return $uCols[$bare];
        }

        if (!in_array($bare, self::ALLOWED_COLUMNS, true)) {
            throw new \InvalidArgumentException("Column not allowed: {$col}");
        }

        // Everything else is in `al`
        return "al.{$bare}";
    }

    /**
     * Validate a SELECT expression (column, aggregate, or literal *).
     *
     * @throws \InvalidArgumentException
     */
    private function validateSelectExpression(string $expr): string
    {
        if ($expr === '*') {
            return 'al.*, u.name AS user_name, u.email AS user_email';
        }

        // COUNT(*) / SUM(col) etc.
        if (preg_match('/^(COUNT|SUM|AVG|MIN|MAX)\((\*|[\w.]+)\)(\s+AS\s+\w+)?$/i', $expr, $m)) {
            $fn  = strtoupper($m[1]);
            $arg = $m[2];
            $as  = isset($m[3]) ? ' ' . trim($m[3]) : '';
            if (!in_array($fn, self::ALLOWED_AGGREGATES, true)) {
                throw new \InvalidArgumentException("Aggregate not allowed: {$fn}");
            }
            if ($arg !== '*') {
                $arg = $this->resolveColumn($arg);
            }
            return "{$fn}({$arg}){$as}";
        }

        // Plain column with optional alias
        if (preg_match('/^([\w.]+)(\s+AS\s+\w+)?$/i', $expr, $m)) {
            $col = $this->resolveColumn($m[1]);
            $as  = isset($m[2]) ? ' ' . trim($m[2]) : '';
            return "{$col}{$as}";
        }

        throw new \InvalidArgumentException("Invalid SELECT expression: {$expr}");
    }

    /**
     * Build a display-safe SQL string (params replaced with placeholders shown).
     */
    private function redactSql(string $sql, array $params): string
    {
        $i = 0;
        return preg_replace_callback('/\?/', function () use (&$i, $params) {
            $v = $params[$i++] ?? '?';
            return is_numeric($v) ? $v : "'" . addslashes((string)$v) . "'";
        }, $sql);
    }
}
