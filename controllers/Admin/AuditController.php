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
        'resource_type', 'resource_id', 'entity_name', 'user_role', 'status',
        'readable_message', 'ip_address', 'device', 'browser',
        'request_id', 'old_values', 'new_values', 'changes', 'created_at',
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
    private const ALLOWED_OPERATORS = ['=', '!=', 'LIKE', 'NOT LIKE', '>', '<', '>=', '<=', 'IS NULL', 'IS NOT NULL', 'IN', 'NOT IN'];

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
        'convertx', 'billx', 'resumex', 'devzone', 'formx',
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

        // Resource types for the exclude-filter in the sidebar
        $resourceTypes = $db->fetchAll(
            "SELECT DISTINCT resource_type FROM activity_logs WHERE resource_type IS NOT NULL AND resource_type != '' ORDER BY resource_type"
        );

        // Entity names for inclusion/exclusion autocomplete
        $entityNames = $db->fetchAll(
            "SELECT DISTINCT entity_name FROM activity_logs WHERE entity_name IS NOT NULL AND entity_name != '' ORDER BY entity_name LIMIT 300"
        );

        // IP addresses for inclusion/exclusion autocomplete (cap to 200 most-recent distinct)
        $ipAddresses = $db->fetchAll(
            "SELECT DISTINCT ip_address FROM activity_logs WHERE ip_address IS NOT NULL AND ip_address != '' ORDER BY ip_address LIMIT 200"
        );

        // Users list for the user-filter autocomplete (name + email)
        $users = $db->fetchAll(
            "SELECT id, name, email FROM users ORDER BY name LIMIT 500"
        );

        $this->view('admin/audit/index', [
            'title'         => 'Audit Explorer',
            'stats'         => $stats,
            'actions'       => $actions,
            'modules'       => $modules,
            'userRoles'     => $userRoles,
            'resourceTypes' => $resourceTypes,
            'entityNames'   => $entityNames,
            'ipAddresses'   => $ipAddresses,
            'users'         => $users,
            'allowedCols'   => self::ALLOWED_COLUMNS,
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
    // Raw SQL endpoint
    // ------------------------------------------------------------------ //

    /**
     * Execute a user-supplied raw SQL statement against the activity_logs table.
     *
     * Safety rules enforced server-side:
     *  - Must be a single SELECT statement
     *  - Must not reference any table other than activity_logs / users
     *  - No DDL, DML, stored-procedure calls, or semicolons
     *  - LIMIT is added / capped at 5000 rows
     *
     * Returns JSON: { sql, count, data }
     */
    public function rawSql(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Method not allowed'], 405);
            return;
        }

        // CSRF via header (Ajax call)
        $csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!\Core\Security::validateCsrfToken($csrfHeader)) {
            $this->json(['error' => 'Invalid CSRF token'], 403);
            return;
        }

        $raw = file_get_contents('php://input');
        $body = json_decode($raw, true);
        if (!is_array($body)) {
            $body = [];
        }
        $sql = trim((string)($body['sql'] ?? ''));

        try {
            $safeSql = $this->validateAndPrepareSql($sql);
        } catch (\InvalidArgumentException $e) {
            $this->json(['error' => $e->getMessage()], 400);
            return;
        }

        try {
            $db   = Database::getInstance();
            $rows = $db->fetchAll($safeSql, []);

            $this->json([
                'sql'   => $safeSql,
                'count' => count($rows),
                'data'  => $rows,
            ]);
        } catch (\Exception $e) {
            $this->json(['error' => 'Query failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Validate and prepare a raw SQL string for safe execution.
     *
     * @throws \InvalidArgumentException
     */
    private function validateAndPrepareSql(string $sql): string
    {
        if (empty($sql)) {
            throw new \InvalidArgumentException('SQL query cannot be empty.');
        }

        // Normalize whitespace for pattern matching
        $norm = preg_replace('/\s+/', ' ', strtoupper($sql));

        // Must start with SELECT
        if (!preg_match('/^\s*SELECT\s/i', $sql)) {
            throw new \InvalidArgumentException('Only SELECT statements are permitted.');
        }

        // Block dangerous keywords (DDL / DML / exec)
        $blocked = ['INSERT', 'UPDATE', 'DELETE', 'DROP', 'CREATE', 'ALTER', 'TRUNCATE',
                    'EXEC', 'EXECUTE', 'CALL', 'LOAD', 'OUTFILE', 'INFILE', 'GRANT', 'REVOKE',
                    'REPLACE', 'MERGE', 'PRAGMA', 'ATTACH', 'DETACH', 'UNION'];
        foreach ($blocked as $kw) {
            if (preg_match('/\b' . preg_quote($kw, '/') . '\b/', $norm)) {
                throw new \InvalidArgumentException("Keyword '{$kw}' is not allowed in audit queries.");
            }
        }

        // No semicolons (prevent statement stacking)
        if (strpos($sql, ';') !== false) {
            throw new \InvalidArgumentException('Semicolons are not permitted.');
        }

        // Only allowed table references
        $allowedTables = ['activity_logs', 'users'];
        preg_match_all('/\bFROM\s+([\w,\s]+?)(?:\s+WHERE|\s+JOIN|\s+LEFT|\s+RIGHT|\s+INNER|\s+GROUP|\s+ORDER|\s+LIMIT|$)/i', $sql, $froms);
        preg_match_all('/\bJOIN\s+([\w]+)/i', $sql, $joins);
        $fromTableNames = preg_split('/[\s,]+/', trim($froms[1][0] ?? ''));
        $joinTableNames = $joins[1] ?? [];
        $referencedTables = array_filter(array_map('trim', array_merge($fromTableNames, $joinTableNames)));
        foreach ($referencedTables as $tbl) {
            if ($tbl === '') {
                continue;
            }
            // Strip alias (e.g., "activity_logs" if written as "activity_logs al" already split)
            $tbl = preg_replace('/\s+\w+$/', '', $tbl);
            if (!in_array(strtolower($tbl), $allowedTables, true)) {
                throw new \InvalidArgumentException("Table '{$tbl}' is not allowed. Only 'activity_logs' and 'users' may be queried.");
            }
        }

        // Enforce LIMIT cap — if the user didn't add a LIMIT, add one
        if (!preg_match('/\bLIMIT\s+\d+/i', $sql)) {
            $sql .= ' LIMIT 1000';
        } else {
            // Clamp existing LIMIT to 5000
            $sql = preg_replace_callback('/\bLIMIT\s+(\d+)/i', function ($m) {
                return 'LIMIT ' . min(5000, (int)$m[1]);
            }, $sql);
        }

        return $sql;
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
            } elseif ($op === 'IN' || $op === 'NOT IN') {
                $vals = is_array($val) ? array_values($val) : [$val];
                if (empty($vals)) {
                    continue; // nothing to filter on
                }
                $placeholders = implode(', ', array_fill(0, count($vals), '?'));
                $whereParts[] = "{$qcol} {$op} ({$placeholders})";
                foreach ($vals as $v) {
                    $params[] = $v;
                }
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
