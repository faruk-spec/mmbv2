<?php
/**
 * Notification Service
 * 
 * Handles in-app notifications and multi-channel delivery
 * Part of Phase 9: Email & Notification System
 * 
 * @package MMB\Core
 */

namespace Core;

class Notification
{
    /**
     * Send notification
     * 
     * @param int $userId User ID
     * @param string $type Notification type
     * @param string $message Notification message
     * @param array $data Additional data
     * @param array $channels Delivery channels (email, sms, push, database)
     * @return bool Success status
     */
    public static function send(int $userId, string $type, string $message, array $data = [], array $channels = ['database']): bool
    {
        $success = true;
        
        // Store in database
        if (in_array('database', $channels)) {
            $success = $success && self::storeInDatabase($userId, $type, $message, $data);
        }
        
        // Send email
        if (in_array('email', $channels)) {
            $success = $success && self::sendEmail($userId, $type, $message, $data);
        }
        
        // Send SMS
        if (in_array('sms', $channels)) {
            $success = $success && self::sendSMS($userId, $type, $message, $data);
        }
        
        // Send push notification
        if (in_array('push', $channels)) {
            $success = $success && self::sendPush($userId, $type, $message, $data);
        }
        
        return $success;
    }
    
    /**
     * Store notification in database
     * 
     * @param int $userId User ID
     * @param string $type Notification type
     * @param string $message Notification message
     * @param array $data Additional data
     * @return bool Success status
     */
    private static function storeInDatabase(int $userId, string $type, string $message, array $data = []): bool
    {
        try {
            $db = Database::getInstance();
            
            // Check if notifications table exists
            // In production, create this table during installation
            $table = 'notifications';
            
            $result = $db->insert($table, [
                'user_id' => $userId,
                'type' => $type,
                'message' => $message,
                'data' => json_encode($data),
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            return $result !== false;
        } catch (\Exception $e) {
            Logger::error("Failed to store notification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send email notification
     * 
     * @param int $userId User ID
     * @param string $type Notification type
     * @param string $message Notification message
     * @param array $data Additional data
     * @return bool Success status
     */
    private static function sendEmail(int $userId, string $type, string $message, array $data = []): bool
    {
        try {
            $db = Database::getInstance();
            $user = $db->fetch("SELECT email, name FROM users WHERE id = ?", [$userId]);
            
            if (!$user) {
                return false;
            }
            
            // Check user preferences
            if (!self::userAllowsEmailNotifications($userId, $type)) {
                return true; // User opted out, not an error
            }
            
            $subject = self::getEmailSubject($type);
            $body = self::formatEmailBody($message, $data, $user);
            
            return Email::send($user['email'], $subject, $body);
        } catch (\Exception $e) {
            Logger::error("Failed to send email notification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send SMS notification
     * 
     * @param int $userId User ID
     * @param string $type Notification type
     * @param string $message Notification message
     * @param array $data Additional data
     * @return bool Success status
     */
    private static function sendSMS(int $userId, string $type, string $message, array $data = []): bool
    {
        try {
            $db = Database::getInstance();
            $user = $db->fetch("SELECT phone FROM user_profiles WHERE user_id = ?", [$userId]);
            
            if (!$user || empty($user['phone'])) {
                return false;
            }
            
            // Check user preferences
            if (!self::userAllowsSMSNotifications($userId, $type)) {
                return true; // User opted out, not an error
            }
            
            // In production, integrate with Twilio or similar SMS service
            Logger::info("SMS notification queued for user {$userId}: {$message}");
            
            return true;
        } catch (\Exception $e) {
            Logger::error("Failed to send SMS notification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send push notification
     * 
     * @param int $userId User ID
     * @param string $type Notification type
     * @param string $message Notification message
     * @param array $data Additional data
     * @return bool Success status
     */
    private static function sendPush(int $userId, string $type, string $message, array $data = []): bool
    {
        try {
            // Check user preferences
            if (!self::userAllowsPushNotifications($userId, $type)) {
                return true; // User opted out, not an error
            }
            
            // In production, integrate with push notification service
            // Store in queue for service worker to pick up
            $pushQueue = Cache::get('push_queue', []);
            $pushQueue[] = [
                'user_id' => $userId,
                'type' => $type,
                'title' => self::getPushTitle($type),
                'body' => $message,
                'data' => $data,
                'created_at' => date('Y-m-d H:i:s')
            ];
            Cache::set('push_queue', $pushQueue, 3600);
            
            Logger::info("Push notification queued for user {$userId}: {$message}");
            
            return true;
        } catch (\Exception $e) {
            Logger::error("Failed to send push notification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user notifications
     * 
     * @param int $userId User ID
     * @param bool $unreadOnly Only unread notifications
     * @param int $limit Maximum number of notifications
     * @return array Notifications
     */
    public static function getForUser(int $userId, bool $unreadOnly = false, int $limit = 50): array
    {
        try {
            $db = Database::getInstance();
            
            $sql = "SELECT * FROM notifications WHERE user_id = ?";
            $params = [$userId];
            
            if ($unreadOnly) {
                $sql .= " AND is_read = 0";
            }
            
            $sql .= " ORDER BY created_at DESC LIMIT ?";
            $params[] = $limit;
            
            return $db->fetchAll($sql, $params);
        } catch (\Exception $e) {
            Logger::error("Failed to get notifications: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Mark notification as read
     * 
     * @param int $notificationId Notification ID
     * @return bool Success status
     */
    public static function markAsRead(int $notificationId): bool
    {
        try {
            $db = Database::getInstance();
            $db->update('notifications', [
                'is_read' => 1,
                'read_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$notificationId]);
            
            return true;
        } catch (\Exception $e) {
            Logger::error("Failed to mark notification as read: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mark all notifications as read for user
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public static function markAllAsRead(int $userId): bool
    {
        try {
            $db = Database::getInstance();
            $db->update('notifications', [
                'is_read' => 1,
                'read_at' => date('Y-m-d H:i:s')
            ], 'user_id = ? AND is_read = 0', [$userId]);
            
            return true;
        } catch (\Exception $e) {
            Logger::error("Failed to mark all notifications as read: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get unread count for user
     * 
     * @param int $userId User ID
     * @return int Unread count
     */
    public static function getUnreadCount(int $userId): int
    {
        try {
            $db = Database::getInstance();
            $result = $db->fetch("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0", [$userId]);
            
            return (int)($result['count'] ?? 0);
        } catch (\Exception $e) {
            Logger::error("Failed to get unread count: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Check if user allows email notifications
     * 
     * @param int $userId User ID
     * @param string $type Notification type
     * @return bool Allowed
     */
    private static function userAllowsEmailNotifications(int $userId, string $type): bool
    {
        // In production, check user preferences from database
        return true;
    }
    
    /**
     * Check if user allows SMS notifications
     * 
     * @param int $userId User ID
     * @param string $type Notification type
     * @return bool Allowed
     */
    private static function userAllowsSMSNotifications(int $userId, string $type): bool
    {
        // In production, check user preferences from database
        return false; // Default to false for SMS
    }
    
    /**
     * Check if user allows push notifications
     * 
     * @param int $userId User ID
     * @param string $type Notification type
     * @return bool Allowed
     */
    private static function userAllowsPushNotifications(int $userId, string $type): bool
    {
        // In production, check user preferences from database
        return true;
    }
    
    /**
     * Get email subject for notification type
     * 
     * @param string $type Notification type
     * @return string Subject
     */
    private static function getEmailSubject(string $type): string
    {
        $subjects = [
            'file_downloaded' => 'Your file was downloaded',
            'link_expiring' => 'Your share link is expiring soon',
            'ocr_completed' => 'Your OCR job is complete',
            'collaboration_invite' => 'You have been invited to collaborate',
            'welcome' => 'Welcome to ' . (APP_NAME ?? 'MMB Platform'),
            'password_reset' => 'Password Reset Request'
        ];
        
        return $subjects[$type] ?? 'New Notification';
    }
    
    /**
     * Get push notification title
     * 
     * @param string $type Notification type
     * @return string Title
     */
    private static function getPushTitle(string $type): string
    {
        return self::getEmailSubject($type);
    }
    
    /**
     * Format email body
     * 
     * @param string $message Message
     * @param array $data Additional data
     * @param array $user User data
     * @return string Formatted HTML body
     */
    private static function formatEmailBody(string $message, array $data, array $user): string
    {
        $userName = $user['name'] ?? 'User';
        
        return <<<HTML
<p>Hi {$userName},</p>
<p>{$message}</p>
HTML;
    }
}
