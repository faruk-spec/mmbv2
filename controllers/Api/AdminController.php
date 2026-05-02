<?php
/**
 * Admin API Controller
 *
 * Endpoints:
 *   GET /api/admin/stats    – platform stats
 *   GET /api/admin/users    – user list
 *   GET /api/admin/activity – recent activity
 *
 * @package MMB\Controllers\Api
 */

namespace Controllers\Api;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;

class AdminController extends BaseController
{
    private function requireAdmin(): void
    {
        if (!Auth::check()) {
            http_response_code(401);
            $this->json(['error' => 'Unauthorized']);
            exit;
        }
        if (!Auth::isAdmin()) {
            http_response_code(403);
            $this->json(['error' => 'Forbidden', 'message' => 'Admin access required.']);
            exit;
        }
    }

    /**
     * GET /api/admin/stats
     */
    public function stats(): void
    {
        $this->requireAdmin();
        $db = Database::getInstance();

        $stats = [];
        try {
            $stats['total_users']   = (int)($db->fetch("SELECT COUNT(*) AS c FROM users")['c'] ?? 0);
            $stats['active_users']  = (int)($db->fetch("SELECT COUNT(*) AS c FROM users WHERE is_active = 1")['c'] ?? 0);
        } catch (\Exception $e) {
            // ignore
        }

        $this->json(['success' => true, 'stats' => $stats]);
    }

    /**
     * GET /api/admin/users
     */
    public function users(): void
    {
        $this->requireAdmin();
        $db = Database::getInstance();

        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = min(100, max(1, (int)($_GET['per_page'] ?? 20)));
        $offset  = ($page - 1) * $perPage;

        $users = $db->fetchAll(
            "SELECT id, name, email, role, is_active, created_at FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );

        $total = (int)($db->fetch("SELECT COUNT(*) AS c FROM users")['c'] ?? 0);

        $this->json([
            'success'    => true,
            'users'      => $users,
            'total'      => $total,
            'page'       => $page,
            'per_page'   => $perPage,
            'total_pages'=> (int)ceil($total / $perPage),
        ]);
    }

    /**
     * GET /api/admin/activity
     */
    public function activity(): void
    {
        $this->requireAdmin();
        $db = Database::getInstance();

        $limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));
        $rows  = [];

        try {
            $rows = $db->fetchAll(
                "SELECT al.*, u.name AS user_name, u.email AS user_email
                 FROM activity_logs al
                 LEFT JOIN users u ON al.user_id = u.id
                 ORDER BY al.created_at DESC LIMIT ?",
                [$limit]
            );
        } catch (\Exception $e) {
            // ignore if table not available
        }

        $this->json(['success' => true, 'activity' => $rows]);
    }
}
