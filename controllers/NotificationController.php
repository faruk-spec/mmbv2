<?php
/**
 * User-Facing Notification Controller
 *
 * Provides JSON API endpoints for the notification bell widget.
 *
 * @package MMB\Controllers
 */

namespace Controllers;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;
use Core\Notification;
use Core\Security;

class NotificationController extends BaseController
{
    public function __construct()
    {
        $this->requireAuth();
        $this->ensureTable();
    }

    /**
     * Auto-create the notifications table if it does not exist yet.
     */
    private function ensureTable(): void
    {
        try {
            $db = Database::getInstance();
            $db->query("CREATE TABLE IF NOT EXISTS notifications (
                id          INT AUTO_INCREMENT PRIMARY KEY,
                user_id     INT NOT NULL,
                type        VARCHAR(100) NOT NULL DEFAULT 'info',
                message     TEXT NOT NULL,
                data        TEXT NULL,
                is_read     TINYINT(1) NOT NULL DEFAULT 0,
                read_at     DATETIME NULL,
                created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_unread (user_id, is_read),
                INDEX idx_user_created (user_id, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (\Exception $e) {
            // Table may already exist — safe to ignore
        }
    }

    /**
     * GET /notifications
     * Renders the full notifications page for the logged-in user.
     */
    public function viewAll(): void
    {
        $userId        = Auth::id();
        $notifications = Notification::getForUser($userId, false, 100);

        foreach ($notifications as &$n) {
            $n['data'] = !empty($n['data']) ? json_decode($n['data'], true) : null;
        }
        unset($n);

        // Mark all as read now that the user is viewing them
        Notification::markAllAsRead($userId);

        $this->view('notifications/index', [
            'title'         => 'All Notifications',
            'notifications' => $notifications,
        ]);
    }

    /**
     * Returns the latest 15 notifications for the logged-in user as JSON.
     */
    public function getList(): void
    {
        $userId = Auth::id();
        $notifications = Notification::getForUser($userId, false, 15);

        // Decode data JSON so the client doesn't need to
        foreach ($notifications as &$n) {
            $n['data'] = !empty($n['data']) ? json_decode($n['data'], true) : null;
        }
        unset($n);

        $this->json([
            'success'       => true,
            'notifications' => $notifications,
            'unread_count'  => Notification::getUnreadCount($userId),
        ]);
    }

    /**
     * POST /api/notifications/mark-read
     * Body: id=<notif_id>  (or omit to mark all read)
     */
    public function markRead(): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid token'], 403);
            return;
        }

        $userId = Auth::id();
        $id     = (int) ($_POST['id'] ?? 0);

        if ($id) {
            Notification::markAsRead($id);
        }

        $this->json([
            'success'      => true,
            'unread_count' => Notification::getUnreadCount($userId),
        ]);
    }

    /**
     * POST /api/notifications/mark-all-read
     */
    public function markAllRead(): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid token'], 403);
            return;
        }

        $userId = Auth::id();
        Notification::markAllAsRead($userId);

        $this->json(['success' => true, 'unread_count' => 0]);
    }
}
