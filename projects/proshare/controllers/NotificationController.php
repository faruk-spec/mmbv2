<?php
/**
 * ProShare Notification Controller
 * 
 * @package MMB\Projects\ProShare\Controllers
 */

namespace Projects\ProShare\Controllers;

use Core\Database;
use Core\View;
use Core\Auth;

class NotificationController
{
    /**
     * Show notifications
     */
    public function index(): void
    {
        $user = Auth::user();
        $db = Database::projectConnection('proshare');
        
        $notifications = $db->fetchAll(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50",
            [$user['id']]
        );
        
        View::render('projects/proshare/notifications', [
            'title' => 'Notifications',
            'subtitle' => 'Stay updated with your activity',
            'notifications' => $notifications,
        ]);
    }
    
    /**
     * Mark notification as read
     */
    public function markRead(): void
    {
        header('Content-Type: application/json');
        
        $user = Auth::user();
        $db = Database::projectConnection('proshare');
        
        $notificationId = (int)($_POST['notification_id'] ?? 0);
        
        if ($notificationId) {
            $db->query(
                "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?",
                [$notificationId, $user['id']]
            );
        } else {
            // Mark all as read
            $db->query(
                "UPDATE notifications SET is_read = 1 WHERE user_id = ?",
                [$user['id']]
            );
        }
        
        echo json_encode(['success' => true]);
    }
}
