<?php
/**
 * WhatsApp Dashboard Controller
 * 
 * @package MMB\Projects\WhatsApp\Controllers
 */

namespace Projects\WhatsApp\Controllers;

use Core\Auth;
use Core\View;
use Core\Database;

class DashboardController
{
    private $db;
    private $user;
    
    public function __construct()
    {
        $this->user = Auth::user();
        $this->db = Database::getInstance();
    }
    
    /**
     * Display the WhatsApp dashboard
     */
    public function index()
    {
        // Get user's WhatsApp sessions
        $sessions = $this->getUserSessions();
        
        // Get recent messages
        $recentMessages = $this->getRecentMessages();
        
        // Get statistics
        $stats = $this->getStatistics();
        
        // Render dashboard view
        View::render('whatsapp/dashboard', [
            'user' => $this->user,
            'sessions' => $sessions,
            'recentMessages' => $recentMessages,
            'stats' => $stats,
            'pageTitle' => 'WhatsApp API Automation - Dashboard'
        ]);
    }
    
    /**
     * Get user's WhatsApp sessions
     */
    private function getUserSessions()
    {
        return $this->db->fetchAll("
            SELECT * FROM whatsapp_sessions 
            WHERE user_id = ? 
            ORDER BY created_at DESC
        ", [$this->user['id']]);
    }
    
    /**
     * Get recent messages
     */
    private function getRecentMessages($limit = 10)
    {
        return $this->db->fetchAll("
            SELECT m.*, s.session_name, s.phone_number 
            FROM whatsapp_messages m
            JOIN whatsapp_sessions s ON m.session_id = s.id
            WHERE s.user_id = ?
            ORDER BY m.created_at DESC
            LIMIT ?
        ", [$this->user['id'], $limit]);
    }
    
    /**
     * Get dashboard statistics
     */
    private function getStatistics()
    {
        // Total sessions
        $totalSessions = $this->db->fetchColumn("
            SELECT COUNT(*) FROM whatsapp_sessions WHERE user_id = ?
        ", [$this->user['id']]) ?? 0;
        
        // Active sessions
        $activeSessions = $this->db->fetchColumn("
            SELECT COUNT(*) FROM whatsapp_sessions 
            WHERE user_id = ? AND status = 'connected'
        ", [$this->user['id']]) ?? 0;
        
        // Total messages sent today
        $messagesToday = $this->db->fetchColumn("
            SELECT COUNT(*) FROM whatsapp_messages m
            JOIN whatsapp_sessions s ON m.session_id = s.id
            WHERE s.user_id = ? AND DATE(m.created_at) = CURDATE()
        ", [$this->user['id']]) ?? 0;
        
        // Total API calls today
        $apiCallsToday = $this->db->fetchColumn("
            SELECT COUNT(*) FROM whatsapp_api_logs
            WHERE user_id = ? AND DATE(created_at) = CURDATE()
        ", [$this->user['id']]) ?? 0;
        
        return [
            'totalSessions' => $totalSessions,
            'activeSessions' => $activeSessions,
            'messagesToday' => $messagesToday,
            'apiCallsToday' => $apiCallsToday
        ];
    }
    
    /**
     * Display user's subscription page
     */
    public function subscription()
    {
        // Get user's current subscription
        $subscription = $this->db->fetch("
            SELECT * FROM whatsapp_subscription_details
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 1
        ", [$this->user['id']]);
        
        // Get all available plans
        $plans = $this->db->fetchAll("
            SELECT * FROM whatsapp_subscription_plans 
            WHERE is_active = 1 
            ORDER BY price ASC
        ");
        
        // Calculate usage percentages
        if ($subscription) {
            $subscription['messages_percent'] = $subscription['messages_limit'] > 0 
                ? min(($subscription['messages_used'] / $subscription['messages_limit']) * 100, 100) 
                : 0;
            $subscription['sessions_percent'] = $subscription['sessions_limit'] > 0 
                ? min(($subscription['sessions_used'] / $subscription['sessions_limit']) * 100, 100) 
                : 0;
            $subscription['api_calls_percent'] = $subscription['api_calls_limit'] > 0 
                ? min(($subscription['api_calls_used'] / $subscription['api_calls_limit']) * 100, 100) 
                : 0;
        }
        
        View::render('whatsapp/subscription', [
            'user' => $this->user,
            'subscription' => $subscription,
            'plans' => $plans,
            'pageTitle' => 'My Subscription - WhatsApp API'
        ]);
    }
}
