<?php
/**
 * WhatsApp Admin Controller
 * 
 * Admin panel controller for managing WhatsApp API automation
 * 
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Core\Auth;
use Core\View;
use Core\Database;
use Core\Security;

class WhatsAppAdminController
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        
        // Check if user is admin
        if (!Auth::isAdmin()) {
            header('Location: /dashboard');
            exit;
        }
    }
    
    /**
     * WhatsApp Overview/Dashboard
     */
    public function overview()
    {
        $stats = $this->getOverviewStats();
        $recentSessions = $this->getRecentSessions();
        $recentMessages = $this->getRecentMessages();
        
        View::render('admin/projects/whatsapp/overview', [
            'stats' => $stats,
            'recentSessions' => $recentSessions,
            'recentMessages' => $recentMessages,
            'pageTitle' => 'WhatsApp API - Admin Overview'
        ]);
    }
    
    /**
     * Manage all WhatsApp sessions
     */
    public function sessions()
    {
        $page = $_GET['page'] ?? 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // Get all sessions with user info
        $sessions = $this->db->fetchAll("
            SELECT s.*, u.name as username, u.email 
            FROM whatsapp_sessions s
            JOIN users u ON s.user_id = u.id
            ORDER BY s.created_at DESC
            LIMIT ? OFFSET ?
        ", [$perPage, $offset]);
        
        // Get total count
        $totalSessions = $this->db->fetchColumn("SELECT COUNT(*) FROM whatsapp_sessions");
        
        View::render('admin/projects/whatsapp/sessions', [
            'sessions' => $sessions,
            'currentPage' => $page,
            'totalPages' => ceil($totalSessions / $perPage),
            'pageTitle' => 'WhatsApp Sessions - Admin'
        ]);
    }
    
    /**
     * View all messages
     */
    public function messages()
    {
        $page = $_GET['page'] ?? 1;
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        $messages = $this->db->fetchAll("
            SELECT m.*, s.session_name, s.phone_number, u.name as username
            FROM whatsapp_messages m
            JOIN whatsapp_sessions s ON m.session_id = s.id
            JOIN users u ON s.user_id = u.id
            ORDER BY m.created_at DESC
            LIMIT ? OFFSET ?
        ", [$perPage, $offset]);
        
        // Get total count
        $totalMessages = $this->db->fetchColumn("SELECT COUNT(*) FROM whatsapp_messages");
        
        View::render('admin/projects/whatsapp/messages', [
            'messages' => $messages,
            'currentPage' => $page,
            'totalPages' => ceil($totalMessages / $perPage),
            'pageTitle' => 'WhatsApp Messages - Admin'
        ]);
    }
    
    /**
     * View API usage logs
     */
    public function apiLogs()
    {
        $page = $_GET['page'] ?? 1;
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        $logs = $this->db->fetchAll("
            SELECT l.*, u.name as username, u.email
            FROM whatsapp_api_logs l
            JOIN users u ON l.user_id = u.id
            ORDER BY l.created_at DESC
            LIMIT ? OFFSET ?
        ", [$perPage, $offset]);
        
        // Get total count
        $totalLogs = $this->db->fetchColumn("SELECT COUNT(*) FROM whatsapp_api_logs");
        
        View::render('admin/projects/whatsapp/api-logs', [
            'logs' => $logs,
            'currentPage' => $page,
            'totalPages' => ceil($totalLogs / $perPage),
            'pageTitle' => 'API Logs - Admin'
        ]);
    }
    
    /**
     * Manage user settings
     */
    public function userSettings()
    {
        $userId = $_GET['user_id'] ?? null;
        
        if ($userId) {
            // Get specific user settings
            $userSettings = $this->db->fetch("
                SELECT u.*, w.webhook_url, w.webhook_enabled, w.notifications_enabled
                FROM users u
                LEFT JOIN whatsapp_user_settings w ON u.id = w.user_id
                WHERE u.id = ?
            ", [$userId]);
            
            // Get user's sessions
            $userSessions = $this->db->fetchAll("
                SELECT * FROM whatsapp_sessions WHERE user_id = ? ORDER BY created_at DESC
            ", [$userId]);
            
            // Get user's API keys
            $apiKeys = $this->db->fetchAll("
                SELECT * FROM whatsapp_api_keys WHERE user_id = ? ORDER BY created_at DESC
            ", [$userId]);
            
            View::render('admin/projects/whatsapp/user-detail', [
                'userSettings' => $userSettings,
                'userSessions' => $userSessions,
                'apiKeys' => $apiKeys,
                'pageTitle' => 'User Settings - Admin'
            ]);
        } else {
            // List all users with WhatsApp access
            $users = $this->db->fetchAll("
                SELECT u.id, u.name as username, u.email,
                    COUNT(DISTINCT s.id) as session_count,
                    COUNT(DISTINCT m.id) as message_count,
                    MAX(s.created_at) as last_session
                FROM users u
                LEFT JOIN whatsapp_sessions s ON u.id = s.user_id
                LEFT JOIN whatsapp_messages m ON s.id = m.session_id
                GROUP BY u.id, u.name, u.email
                HAVING session_count > 0
                ORDER BY last_session DESC
            ");
            
            View::render('admin/projects/whatsapp/users', [
                'users' => $users,
                'pageTitle' => 'WhatsApp Users - Admin'
            ]);
        }
    }
    
    /**
     * Delete session (admin action)
     */
    public function deleteSession()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        try {
            $sessionId = $_POST['session_id'] ?? null;
            
            if (!$sessionId) {
                throw new \Exception('Session ID required');
            }
            
            // Validate CSRF
            if (!Security::validateCSRF($_POST['csrf_token'] ?? '')) {
                throw new \Exception('Invalid CSRF token');
            }
            
            $this->db->query("DELETE FROM whatsapp_sessions WHERE id = ?", [$sessionId]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Session deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get overview statistics
     */
    private function getOverviewStats()
    {
        // Total sessions
        $totalSessions = $this->db->fetchColumn("SELECT COUNT(*) FROM whatsapp_sessions") ?? 0;
        
        // Active sessions
        $activeSessions = $this->db->fetchColumn("
            SELECT COUNT(*) FROM whatsapp_sessions WHERE status = 'connected'
        ") ?? 0;
        
        // Total messages
        $totalMessages = $this->db->fetchColumn("SELECT COUNT(*) FROM whatsapp_messages") ?? 0;
        
        // Messages today
        $messagesToday = $this->db->fetchColumn("
            SELECT COUNT(*) FROM whatsapp_messages 
            WHERE DATE(created_at) = CURDATE()
        ") ?? 0;
        
        // Total users with WhatsApp
        $totalUsers = $this->db->fetchColumn("
            SELECT COUNT(DISTINCT user_id) FROM whatsapp_sessions
        ") ?? 0;
        
        // API calls today
        $apiCallsToday = $this->db->fetchColumn("
            SELECT COUNT(*) FROM whatsapp_api_logs 
            WHERE DATE(created_at) = CURDATE()
        ") ?? 0;
        
        return [
            'totalSessions' => $totalSessions,
            'activeSessions' => $activeSessions,
            'totalMessages' => $totalMessages,
            'messagesToday' => $messagesToday,
            'totalUsers' => $totalUsers,
            'apiCallsToday' => $apiCallsToday
        ];
    }
    
    /**
     * Get recent sessions
     */
    private function getRecentSessions($limit = 10)
    {
        return $this->db->fetchAll("
            SELECT s.*, u.name as username 
            FROM whatsapp_sessions s
            JOIN users u ON s.user_id = u.id
            ORDER BY s.created_at DESC
            LIMIT ?
        ", [$limit]);
    }
    
    /**
     * Get recent messages
     */
    private function getRecentMessages($limit = 10)
    {
        return $this->db->fetchAll("
            SELECT m.*, s.session_name, u.name as username
            FROM whatsapp_messages m
            JOIN whatsapp_sessions s ON m.session_id = s.id
            JOIN users u ON s.user_id = u.id
            ORDER BY m.created_at DESC
            LIMIT ?
        ", [$limit]);
    }
}
