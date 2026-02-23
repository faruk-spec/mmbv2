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
        $this->requireAdmin();
    }
    
    /**
     * Log viewer dashboard
     */
    public function index(): void
    {
        $this->view('admin/logs/index', [
            'title' => 'Logs'
        ]);
    }
    
    /**
     * Activity logs
     */
    public function activity(): void
    {
        $db = Database::getInstance();

        $page    = max(1, (int) $this->input('page', 1));
        $perPage = 50;
        $offset  = ($page - 1) * $perPage;

        $action    = $this->input('action', '');
        $userId    = $this->input('user_id', '');
        $category  = $this->input('category', '');   // 'admin' | 'user' | ''
        $dateFrom  = $this->input('date_from', '');
        $dateTo    = $this->input('date_to', '');
        $search    = $this->input('search', '');

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

        if ($search) {
            $where   .= ' AND (al.action LIKE ? OR u.name LIKE ? OR u.email LIKE ? OR al.ip_address LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        // Category filter — use isAdminAction() prefixes/exact list from the static helper
        // We query by prefix patterns which is efficient and consistent with the view badge logic.
        $adminPrefixPatterns = [
            'admin_%', 'settings_%', 'ip_%', 'session%', 'maintenance_%', 'security_%',
            'project_%', 'oauth_%', 'navbar_%', 'proshare_%', 'codexpro_%', 'imgtxt_%',
        ];
        $adminExactActions = [
            'user_created', 'user_updated', 'user_deleted', 'user_status_changed',
            '2fa_reset_by_admin', '2fa_disabled_by_admin', 'sessions_cleaned',
        ];
        if ($category === 'admin' || $category === 'user') {
            $likeOr   = implode(',', array_fill(0, count($adminExactActions), '?'));
            $likeParts = array_fill(0, count($adminPrefixPatterns), 'al.action LIKE ?');
            $adminSql = '(' . implode(' OR ', $likeParts) . ' OR al.action IN (' . $likeOr . '))';

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

        // Stats
        $stats = $db->fetch(
            "SELECT
                COUNT(*) AS total,
                COUNT(DISTINCT user_id) AS unique_users,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) AS today
             FROM activity_logs"
        );

        $this->view('admin/logs/activity', [
            'title'         => 'Activity Logs',
            'logs'          => $logs,
            'actions'       => $actions,
            'stats'         => $stats,
            'currentAction' => $action,
            'currentUserId' => $userId,
            'category'      => $category,
            'dateFrom'      => $dateFrom,
            'dateTo'        => $dateTo,
            'search'        => $search,
            'pagination'    => [
                'current' => $page,
                'total'   => ceil(($total['count'] ?? 0) / $perPage),
                'perPage' => $perPage,
            ],
        ]);
    }
    
    /**
     * System logs
     */
    public function system(): void
    {
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
