<?php
/**
 * Admin Log Controller
 * 
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;
use Core\Logger;

class LogController extends BaseController
{
    public function __construct()
    {
        $this->requireAuth();
        $this->requirePermissionGroup('logs');
    }
    
    /**
     * Log viewer dashboard
     */
    public function index(): void
    {
        $this->requirePermission('logs');
        $db = Database::getInstance();

        // Quick stats for the index dashboard
        $stats = $db->fetch(
            "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) AS today,
                SUM(CASE WHEN status = 'failure' THEN 1 ELSE 0 END) AS failures,
                COUNT(DISTINCT user_id) AS unique_users
             FROM activity_logs"
        ) ?: ['total' => 0, 'today' => 0, 'failures' => 0, 'unique_users' => 0];

        // Latest 8 activity entries for the stream
        $recentActivity = $db->fetchAll(
            "SELECT al.action, al.module, al.readable_message, al.status,
                    al.created_at, u.name AS user_name
             FROM activity_logs al
             LEFT JOIN users u ON al.user_id = u.id
             ORDER BY al.created_at DESC
             LIMIT 8"
        ) ?: [];

        // Module breakdown (top 6)
        $moduleBreakdown = $db->fetchAll(
            "SELECT COALESCE(module, 'core') AS module, COUNT(*) AS cnt
             FROM activity_logs
             GROUP BY module
             ORDER BY cnt DESC
             LIMIT 6"
        ) ?: [];

        // Status breakdown
        $statusBreakdown = $db->fetchAll(
            "SELECT COALESCE(status, 'success') AS status, COUNT(*) AS cnt
             FROM activity_logs GROUP BY status"
        ) ?: [];

        $this->view('admin/logs/index', [
            'title'          => 'Logs',
            'stats'          => $stats,
            'recentActivity' => $recentActivity,
            'moduleBreakdown'=> $moduleBreakdown,
            'statusBreakdown'=> $statusBreakdown,
        ]);
    }
    
    /**
     * Activity logs
     */
    public function activity(): void
    {
        $this->requirePermission('logs.activity');
        $db = Database::getInstance();

        $page    = max(1, (int) $this->input('page', 1));
        $perPage = 50;
        $offset  = ($page - 1) * $perPage;

        $action       = $this->input('action', '');
        $userId       = $this->input('user_id', '');
        $category     = $this->input('category', '');   // 'admin' | 'user' | ''
        $module       = $this->input('module', '');
        $resourceType = $this->input('resource_type', '');
        $entityId     = $this->input('entity_id', '');
        $entityName   = $this->input('entity_name', '');
        $tenantId     = $this->input('tenant_id', '');
        $status       = $this->input('status', '');
        $dateFrom     = $this->input('date_from', '');
        $dateTo       = $this->input('date_to', '');
        $search       = $this->input('search', '');

        [$where, $params] = $this->buildWhereClause(
            $action, $userId, $category, $module, $resourceType, $entityId, $entityName, $tenantId, $status, $dateFrom, $dateTo, $search
        );

        $logs = $db->fetchAll(
            "SELECT al.*, u.name, u.email
             FROM activity_logs al
             LEFT JOIN users u ON al.user_id = u.id
             WHERE {$where}
             ORDER BY al.created_at DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        $total = $db->fetch(
            "SELECT COUNT(*) AS count FROM activity_logs al
             LEFT JOIN users u ON al.user_id = u.id
             WHERE {$where}",
            $params
        );

        // Get unique actions for filter dropdown
        $actions = $db->fetchAll('SELECT DISTINCT action FROM activity_logs ORDER BY action');

        // Get unique modules for filter dropdown
        $modules = $db->fetchAll(
            "SELECT DISTINCT module FROM activity_logs WHERE module IS NOT NULL AND module != '' ORDER BY module"
        );

        // Stats
        $stats = $db->fetch(
            "SELECT
                COUNT(*) AS total,
                COUNT(DISTINCT user_id) AS unique_users,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) AS today,
                SUM(CASE WHEN status = 'failure' THEN 1 ELSE 0 END) AS failures
             FROM activity_logs"
        );

        // 7-day trend
        $trend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date  = date('Y-m-d', strtotime("-{$i} days"));
            $row   = $db->fetch(
                "SELECT COUNT(*) AS cnt FROM activity_logs WHERE DATE(created_at) = ?", [$date]
            );
            $trend[] = ['date' => date('M d', strtotime($date)), 'count' => (int)($row['cnt'] ?? 0)];
        }

        // Top 8 actions
        $topActions = $db->fetchAll(
            "SELECT action, COUNT(*) AS cnt FROM activity_logs GROUP BY action ORDER BY cnt DESC LIMIT 8"
        );

        // Module distribution
        $moduleDistrib = $db->fetchAll(
            "SELECT COALESCE(module, 'core') AS module, COUNT(*) AS cnt
             FROM activity_logs GROUP BY module ORDER BY cnt DESC LIMIT 8"
        );

        // Status breakdown
        $statusDistrib = $db->fetchAll(
            "SELECT COALESCE(status,'success') AS status, COUNT(*) AS cnt FROM activity_logs GROUP BY status"
        );

        $this->view('admin/logs/activity', [
            'title'               => 'Activity Logs',
            'logs'                => $logs,
            'actions'             => $actions,
            'modules'             => $modules,
            'stats'               => $stats,
            'trend'               => $trend,
            'topActions'          => $topActions,
            'moduleDistrib'       => $moduleDistrib,
            'statusDistrib'       => $statusDistrib,
            'currentAction'       => $action,
            'currentUserId'       => $userId,
            'currentModule'       => $module,
            'currentResourceType' => $resourceType,
            'currentEntityId'     => $entityId,
            'currentEntityName'   => $entityName,
            'currentTenantId'     => $tenantId,
            'currentStatus'       => $status,
            'category'            => $category,
            'dateFrom'            => $dateFrom,
            'dateTo'              => $dateTo,
            'search'              => $search,
            'pagination'          => [
                'current' => $page,
                'total'   => ceil(($total['count'] ?? 0) / $perPage),
                'perPage' => $perPage,
                'count'   => (int)($total['count'] ?? 0),
            ],
        ]);
    }

    /**
     * Export activity logs as CSV or JSON
     */
    public function export(): void
    {
        $this->requirePermission('logs');
        $db     = Database::getInstance();
        $format = strtolower($this->input('format', 'csv'));
        if (!in_array($format, ['csv', 'json'])) {
            $format = 'csv';
        }

        $action       = $this->input('action', '');
        $userId       = $this->input('user_id', '');
        $category     = $this->input('category', '');
        $module       = $this->input('module', '');
        $resourceType = $this->input('resource_type', '');
        $entityId     = $this->input('entity_id', '');
        $entityName   = $this->input('entity_name', '');
        $tenantId     = $this->input('tenant_id', '');
        $status       = $this->input('status', '');
        $dateFrom     = $this->input('date_from', '');
        $dateTo       = $this->input('date_to', '');
        $search       = $this->input('search', '');

        [$where, $params] = $this->buildWhereClause(
            $action, $userId, $category, $module, $resourceType, $entityId, $entityName, $tenantId, $status, $dateFrom, $dateTo, $search
        );

        $logs = $db->fetchAll(
            "SELECT al.id, al.action, al.module, al.tenant_id, al.resource_type, al.resource_id,
                    al.entity_name, al.user_name AS log_user_name,
                    al.user_role, al.readable_message, al.status,
                    al.old_values, al.new_values, al.changes, al.data,
                    al.ip_address, al.device, al.browser,
                    al.request_id, al.created_at,
                    u.name AS user_name, u.email AS user_email
             FROM activity_logs al
             LEFT JOIN users u ON al.user_id = u.id
             WHERE {$where}
             ORDER BY al.created_at DESC
             LIMIT 10000",
            $params
        );

        $filename = 'activity_logs_' . date('Y-m-d_His');

        if ($format === 'json') {
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="' . $filename . '.json"');
            echo json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        }

        // CSV export
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        echo "\xEF\xBB\xBF"; // UTF-8 BOM for Excel

        $out = fopen('php://output', 'w');
        fputcsv($out, [
            'ID', 'User Name', 'User Email', 'Action', 'Module', 'Tenant ID',
            'Resource Type', 'Resource ID', 'Entity Name', 'User Role',
            'Readable Message', 'Changes',
            'Status', 'IP Address', 'Device', 'Browser', 'Request ID', 'Created At',
        ]);
        foreach ($logs as $row) {
            fputcsv($out, [
                $row['id'],
                $row['user_name'] ?? 'System',
                $row['user_email'] ?? '',
                $row['action'],
                $row['module'] ?? '',
                $row['tenant_id'] ?? '',
                $row['resource_type'] ?? '',
                $row['resource_id'] ?? '',
                $row['entity_name'] ?? '',
                $row['user_role'] ?? '',
                $row['readable_message'] ?? '',
                $row['changes'] ?? '',
                $row['status'] ?? 'success',
                $row['ip_address'] ?? '',
                $row['device'] ?? '',
                $row['browser'] ?? '',
                $row['request_id'] ?? '',
                $row['created_at'],
            ]);
        }
        fclose($out);
        exit;
    }

    /**
     * JSON API endpoint for activity logs (for headless / AJAX consumers)
     */
    public function api(): void
    {
        $this->requirePermission('logs');
        $db = Database::getInstance();

        $page    = max(1, (int) $this->input('page', 1));
        $perPage = min(100, max(1, (int) $this->input('per_page', 50)));
        $offset  = ($page - 1) * $perPage;

        $action       = $this->input('action', '');
        $userId       = $this->input('user_id', '');
        $category     = $this->input('category', '');
        $module       = $this->input('module', '');
        $resourceType = $this->input('resource_type', '');
        $entityId     = $this->input('entity_id', '');
        $entityName   = $this->input('entity_name', '');
        $tenantId     = $this->input('tenant_id', '');
        $status       = $this->input('status', '');
        $dateFrom     = $this->input('date_from', '');
        $dateTo       = $this->input('date_to', '');
        $search       = $this->input('search', '');

        [$where, $params] = $this->buildWhereClause(
            $action, $userId, $category, $module, $resourceType, $entityId, $entityName, $tenantId, $status, $dateFrom, $dateTo, $search
        );

        $logs = $db->fetchAll(
            "SELECT al.id, al.action, al.module, al.tenant_id, al.resource_type, al.resource_id,
                    al.entity_name, al.user_name AS log_user_name,
                    al.user_role, al.readable_message, al.status,
                    al.old_values, al.new_values, al.changes, al.data,
                    al.ip_address, al.device, al.browser,
                    al.request_id, al.created_at,
                    u.name AS user_name, u.email AS user_email
             FROM activity_logs al
             LEFT JOIN users u ON al.user_id = u.id
             WHERE {$where}
             ORDER BY al.created_at DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        $total = $db->fetch(
            "SELECT COUNT(*) AS count FROM activity_logs al
             LEFT JOIN users u ON al.user_id = u.id
             WHERE {$where}",
            $params
        );

        // Decode JSON sub-fields for convenience
        foreach ($logs as &$log) {
            foreach (['old_values', 'new_values', 'changes', 'data'] as $col) {
                if (isset($log[$col]) && is_string($log[$col])) {
                    $decoded = json_decode($log[$col], true);
                    $log[$col] = $decoded !== null ? $decoded : $log[$col];
                }
            }
        }
        unset($log);

        $this->json([
            'data'       => $logs,
            'pagination' => [
                'current'  => $page,
                'per_page' => $perPage,
                'total'    => (int)($total['count'] ?? 0),
                'pages'    => (int)ceil(($total['count'] ?? 0) / $perPage),
            ],
        ]);
    }

    /**
     * System logs
     */
    public function system(): void
    {
        $this->requirePermission('logs.system');
        $logFiles = Logger::getLogFiles();
        $selectedFile = $this->input('file', '');
        $logContent = [];
        
        if ($selectedFile && in_array($selectedFile, $logFiles)) {
            $logContent = Logger::readLog($selectedFile, 200);
        }
        
        $this->view('admin/logs/system', [
            'title' => 'System Logs',
            'logFiles' => $logFiles,
            'selectedFile' => $selectedFile,
            'logContent' => $logContent
        ]);
    }

    // ------------------------------------------------------------------ //
    // Helpers
    // ------------------------------------------------------------------ //

    /**
     * Build the shared WHERE clause and params array used by activity(),
     * export(), and api().
     */
    private function buildWhereClause(
        string $action, string $userId, string $category,
        string $module, string $resourceType, string $entityId, string $entityName,
        string $tenantId, string $status, string $dateFrom, string $dateTo, string $search
    ): array {
        $where  = '1=1';
        $params = [];

        if ($action) {
            $where   .= ' AND al.action = ?';
            $params[] = $action;
        }

        if ($userId) {
            $where   .= ' AND al.user_id = ?';
            $params[] = (int) $userId;
        }

        if ($module) {
            $where   .= ' AND al.module = ?';
            $params[] = $module;
        }

        if ($resourceType) {
            $where   .= ' AND al.resource_type = ?';
            $params[] = $resourceType;
        }

        if ($entityId) {
            $where   .= ' AND al.resource_id = ?';
            $params[] = $entityId;
        }

        if ($entityName) {
            $where   .= ' AND al.entity_name LIKE ?';
            $params[] = "%{$entityName}%";
        }

        if ($tenantId) {
            $where   .= ' AND al.tenant_id = ?';
            $params[] = (int) $tenantId;
        }

        if ($status) {
            $where   .= ' AND al.status = ?';
            $params[] = $status;
        }

        if ($search) {
            $where   .= ' AND (al.action LIKE ? OR al.readable_message LIKE ? OR al.entity_name LIKE ? OR al.user_name LIKE ? OR u.name LIKE ? OR u.email LIKE ? OR al.ip_address LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        // Category filter — use isAdminAction() prefixes/exact list from the static helper
        $adminPrefixPatterns = [
            'admin_%', 'settings_%', 'ip_%', 'session%', 'maintenance_%', 'security_%',
            'project_%', 'oauth_%', 'navbar_%', 'proshare_%', 'codexpro_%', 'imgtxt_%',
        ];
        $adminExactActions = [
            'user_created', 'user_updated', 'user_deleted', 'user_status_changed',
            '2fa_reset_by_admin', '2fa_disabled_by_admin', 'sessions_cleaned',
        ];
        if ($category === 'admin' || $category === 'user') {
            $likeOr    = implode(',', array_fill(0, count($adminExactActions), '?'));
            $likeParts = array_fill(0, count($adminPrefixPatterns), 'al.action LIKE ?');
            $adminSql  = '(' . implode(' OR ', $likeParts) . ' OR al.action IN (' . $likeOr . '))';

            if ($category === 'admin') {
                $where .= ' AND ' . $adminSql;
                $params = array_merge($params, $adminPrefixPatterns, $adminExactActions);
            } else {
                $where .= ' AND NOT ' . $adminSql;
                $params = array_merge($params, $adminPrefixPatterns, $adminExactActions);
            }
        }

        if ($dateFrom) {
            $where   .= ' AND al.created_at >= ?';
            $params[] = $dateFrom . ' 00:00:00';
        }

        if ($dateTo) {
            $where   .= ' AND al.created_at <= ?';
            $params[] = $dateTo . ' 23:59:59';
        }

        return [$where, $params];
    }

    /**
     * Determine whether a logged action is an admin-initiated action.
     * Used both in the controller (SQL filter) and the view (badge colour).
     */
    public static function isAdminAction(string $action): bool
    {
        static $adminPrefixes = [
            'admin_', 'settings_', 'ip_', 'session', 'maintenance_', 'security_',
            'project_', 'oauth_', 'navbar_', 'proshare_', 'codexpro_', 'imgtxt_',
        ];
        static $adminExact = [
            'user_created', 'user_updated', 'user_deleted', 'user_status_changed',
            '2fa_reset_by_admin', '2fa_disabled_by_admin', 'sessions_cleaned',
        ];

        if (in_array($action, $adminExact, true)) {
            return true;
        }
        foreach ($adminPrefixes as $prefix) {
            if (strpos($action, $prefix) === 0) {
                return true;
            }
        }
        return false;
    }
}

