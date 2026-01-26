<?php
/**
 * WhatsApp Subscription Helper
 * 
 * Helper functions to check subscription limits and usage
 * 
 * @package MMB\Projects\WhatsApp\Helpers
 */

namespace Projects\WhatsApp\Helpers;

use Core\Database;

class SubscriptionHelper
{
    private static $db = null;
    
    /**
     * Get database instance
     */
    private static function getDb()
    {
        if (self::$db === null) {
            self::$db = Database::getInstance();
        }
        return self::$db;
    }
    
    /**
     * Get user's active subscription
     */
    public static function getUserSubscription($userId)
    {
        $db = self::getDb();
        return $db->fetch("
            SELECT * FROM whatsapp_subscriptions 
            WHERE user_id = ? AND status = 'active' 
            ORDER BY created_at DESC 
            LIMIT 1
        ", [$userId]);
    }
    
    /**
     * Check if user can send message
     */
    public static function canSendMessage($userId)
    {
        $subscription = self::getUserSubscription($userId);
        
        if (!$subscription) {
            return ['allowed' => false, 'reason' => 'No active subscription'];
        }
        
        // Check if subscription is expired
        if (strtotime($subscription['end_date']) < time()) {
            return ['allowed' => false, 'reason' => 'Subscription expired'];
        }
        
        // Check message limit (0 = unlimited)
        if ($subscription['messages_limit'] > 0 && $subscription['messages_used'] >= $subscription['messages_limit']) {
            return ['allowed' => false, 'reason' => 'Message limit reached'];
        }
        
        return ['allowed' => true];
    }
    
    /**
     * Check if user can create session
     */
    public static function canCreateSession($userId)
    {
        $subscription = self::getUserSubscription($userId);
        
        if (!$subscription) {
            return ['allowed' => false, 'reason' => 'No active subscription'];
        }
        
        // Check if subscription is expired
        if (strtotime($subscription['end_date']) < time()) {
            return ['allowed' => false, 'reason' => 'Subscription expired'];
        }
        
        // Check session limit (0 = unlimited)
        if ($subscription['sessions_limit'] > 0 && $subscription['sessions_used'] >= $subscription['sessions_limit']) {
            return ['allowed' => false, 'reason' => 'Session limit reached'];
        }
        
        return ['allowed' => true];
    }
    
    /**
     * Check if user can make API call
     */
    public static function canMakeApiCall($userId)
    {
        $subscription = self::getUserSubscription($userId);
        
        if (!$subscription) {
            return ['allowed' => false, 'reason' => 'No active subscription'];
        }
        
        // Check if subscription is expired
        if (strtotime($subscription['end_date']) < time()) {
            return ['allowed' => false, 'reason' => 'Subscription expired'];
        }
        
        // Check API call limit (0 = unlimited)
        if ($subscription['api_calls_limit'] > 0 && $subscription['api_calls_used'] >= $subscription['api_calls_limit']) {
            return ['allowed' => false, 'reason' => 'API call limit reached'];
        }
        
        return ['allowed' => true];
    }
    
    /**
     * Increment message usage
     */
    public static function incrementMessageUsage($userId, $count = 1)
    {
        $db = self::getDb();
        $db->execute("
            UPDATE whatsapp_subscriptions 
            SET messages_used = messages_used + ? 
            WHERE user_id = ? AND status = 'active'
        ", [$count, $userId]);
    }
    
    /**
     * Increment session usage
     */
    public static function incrementSessionUsage($userId, $count = 1)
    {
        $db = self::getDb();
        $db->execute("
            UPDATE whatsapp_subscriptions 
            SET sessions_used = sessions_used + ? 
            WHERE user_id = ? AND status = 'active'
        ", [$count, $userId]);
    }
    
    /**
     * Decrement session usage (when session is deleted)
     */
    public static function decrementSessionUsage($userId, $count = 1)
    {
        $db = self::getDb();
        $db->execute("
            UPDATE whatsapp_subscriptions 
            SET sessions_used = GREATEST(0, sessions_used - ?) 
            WHERE user_id = ? AND status = 'active'
        ", [$count, $userId]);
    }
    
    /**
     * Increment API call usage
     */
    public static function incrementApiCallUsage($userId, $count = 1)
    {
        $db = self::getDb();
        $db->execute("
            UPDATE whatsapp_subscriptions 
            SET api_calls_used = api_calls_used + ? 
            WHERE user_id = ? AND status = 'active'
        ", [$count, $userId]);
    }
    
    /**
     * Get subscription usage statistics
     */
    public static function getUsageStats($userId)
    {
        $subscription = self::getUserSubscription($userId);
        
        if (!$subscription) {
            return null;
        }
        
        return [
            'messages' => [
                'used' => $subscription['messages_used'],
                'limit' => $subscription['messages_limit'],
                'percent' => $subscription['messages_limit'] > 0 
                    ? ($subscription['messages_used'] / $subscription['messages_limit']) * 100 
                    : 0
            ],
            'sessions' => [
                'used' => $subscription['sessions_used'],
                'limit' => $subscription['sessions_limit'],
                'percent' => $subscription['sessions_limit'] > 0 
                    ? ($subscription['sessions_used'] / $subscription['sessions_limit']) * 100 
                    : 0
            ],
            'api_calls' => [
                'used' => $subscription['api_calls_used'],
                'limit' => $subscription['api_calls_limit'],
                'percent' => $subscription['api_calls_limit'] > 0 
                    ? ($subscription['api_calls_used'] / $subscription['api_calls_limit']) * 100 
                    : 0
            ]
        ];
    }
    
    /**
     * Check and update expired subscriptions
     */
    public static function updateExpiredSubscriptions()
    {
        $db = self::getDb();
        $db->execute("
            UPDATE whatsapp_subscriptions 
            SET status = 'expired' 
            WHERE status = 'active' AND end_date < NOW()
        ");
    }
}
