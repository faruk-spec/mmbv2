<?php
/**
 * WhatsApp Dashboard Controller
 * 
 * @package MMB\Projects\WhatsApp\Controllers
 */

namespace WhatsApp\Controllers;

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
        $stmt = $this->db->prepare("
            SELECT * FROM whatsapp_sessions 
            WHERE user_id = ? 
            ORDER BY created_at DESC
        ");
        
        $stmt->execute([$this->user['id']]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get recent messages
     */
    private function getRecentMessages($limit = 10)
    {
        $stmt = $this->db->prepare("
            SELECT m.*, s.session_name, s.phone_number 
            FROM whatsapp_messages m
            JOIN whatsapp_sessions s ON m.session_id = s.id
            WHERE s.user_id = ?
            ORDER BY m.created_at DESC
            LIMIT ?
        ");
        
        $stmt->execute([$this->user['id'], $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get dashboard statistics
     */
    private function getStatistics()
    {
        // Total sessions
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total FROM whatsapp_sessions WHERE user_id = ?
        ");
        $stmt->execute([$this->user['id']]);
        $totalSessions = $stmt->fetch()['total'] ?? 0;
        
        // Active sessions
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total FROM whatsapp_sessions 
            WHERE user_id = ? AND status = 'connected'
        ");
        $stmt->execute([$this->user['id']]);
        $activeSessions = $stmt->fetch()['total'] ?? 0;
        
        // Total messages sent today
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total FROM whatsapp_messages m
            JOIN whatsapp_sessions s ON m.session_id = s.id
            WHERE s.user_id = ? AND DATE(m.created_at) = CURDATE()
        ");
        $stmt->execute([$this->user['id']]);
        $messagesToday = $stmt->fetch()['total'] ?? 0;
        
        // Total API calls today
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total FROM whatsapp_api_logs
            WHERE user_id = ? AND DATE(created_at) = CURDATE()
        ");
        $stmt->execute([$this->user['id']]);
        $apiCallsToday = $stmt->fetch()['total'] ?? 0;
        
        return [
            'totalSessions' => $totalSessions,
            'activeSessions' => $activeSessions,
            'messagesToday' => $messagesToday,
            'apiCallsToday' => $apiCallsToday
        ];
    }
}
