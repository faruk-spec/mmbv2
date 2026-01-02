<?php
/**
 * Notifications Admin Controller
 * 
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;
use Core\Auth;
use Core\Notification;

class NotificationController extends BaseController
{
    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
    }
    
    /**
     * All Notifications
     */
    public function allNotifications(): void
    {
        $db = Database::getInstance();
        
        $page = $_GET['page'] ?? 1;
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        $type = $_GET['type'] ?? null;
        $isRead = $_GET['is_read'] ?? null;
        
        $where = [];
        $params = [];
        
        if ($type) {
            $where[] = "type = ?";
            $params[] = $type;
        }
        
        if ($isRead !== null) {
            $where[] = "is_read = ?";
            $params[] = $isRead;
        }
        
        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Get notifications with user information
        $notifications = $db->fetchAll(
            "SELECT n.*, u.name as user_name, u.email 
             FROM notifications n 
             LEFT JOIN users u ON n.user_id = u.id 
             $whereClause 
             ORDER BY n.created_at DESC 
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );
        
        $total = $db->fetch(
            "SELECT COUNT(*) as count FROM notifications $whereClause",
            $params
        )['count'];
        
        // Get notification types for filter
        $types = $db->fetchAll(
            "SELECT DISTINCT type FROM notifications ORDER BY type"
        );
        
        // Get statistics
        $stats = [
            'total' => $db->fetch("SELECT COUNT(*) as count FROM notifications")['count'],
            'unread' => $db->fetch("SELECT COUNT(*) as count FROM notifications WHERE is_read = 0")['count'],
            'today' => $db->fetch(
                "SELECT COUNT(*) as count FROM notifications WHERE DATE(created_at) = CURDATE()"
            )['count']
        ];
        
        $this->view('admin/notifications/all', [
            'title' => 'All Notifications',
            'notifications' => $notifications,
            'types' => $types,
            'stats' => $stats,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => ceil($total / $perPage),
            'filters' => [
                'type' => $type,
                'is_read' => $isRead
            ]
        ]);
    }
    
    /**
     * Notification Preferences
     */
    public function preferences(): void
    {
        $db = Database::getInstance();
        
        // Get all notification preferences grouped by user
        $preferences = $db->fetchAll(
            "SELECT np.*, u.name as user_name, u.email 
             FROM notification_preferences np 
             LEFT JOIN users u ON np.user_id = u.id 
             ORDER BY u.name, np.type"
        );
        
        // Get distinct notification types
        $types = $db->fetchAll(
            "SELECT DISTINCT type FROM notification_preferences ORDER BY type"
        );
        
        $this->view('admin/notifications/preferences', [
            'title' => 'Notification Preferences',
            'preferences' => $preferences,
            'types' => $types
        ]);
    }
    
    /**
     * Send Test Notification (AJAX)
     */
    public function sendTest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        $userId = $_POST['user_id'] ?? Auth::id();
        $type = $_POST['type'] ?? 'test';
        $message = $_POST['message'] ?? 'This is a test notification';
        $channels = $_POST['channels'] ?? ['database'];
        
        try {
            Notification::send($userId, $type, $message, [], $channels);
            $this->jsonResponse([
                'success' => true,
                'message' => 'Test notification sent successfully'
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * Delete Old Notifications (AJAX)
     */
    public function deleteOld(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        $days = $_POST['days'] ?? 30;
        $db = Database::getInstance();
        
        try {
            $deleted = $db->execute(
                "DELETE FROM notifications 
                 WHERE is_read = 1 
                 AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
                [$days]
            );
            
            $this->jsonResponse([
                'success' => true,
                'message' => "Deleted $deleted old notifications",
                'deleted' => $deleted
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
