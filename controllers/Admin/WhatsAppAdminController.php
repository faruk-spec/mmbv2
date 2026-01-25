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
        $this->db = new Database();
        
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
        $stmt = $this->db->prepare("
            SELECT s.*, u.username, u.email 
            FROM whatsapp_sessions s
            JOIN users u ON s.user_id = u.id
            ORDER BY s.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$perPage, $offset]);
        $sessions = $stmt->fetchAll();
        
        // Get total count
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM whatsapp_sessions");
        $stmt->execute();
        $totalSessions = $stmt->fetch()['total'];
        
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
        
        $stmt = $this->db->prepare("
            SELECT m.*, s.session_name, s.phone_number, u.username
            FROM whatsapp_messages m
            JOIN whatsapp_sessions s ON m.session_id = s.id
            JOIN users u ON s.user_id = u.id
            ORDER BY m.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$perPage, $offset]);
        $messages = $stmt->fetchAll();
        
        // Get total count
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM whatsapp_messages");
        $stmt->execute();
        $totalMessages = $stmt->fetch()['total'];
        
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
        
        $stmt = $this->db->prepare("
            SELECT l.*, u.username, u.email
            FROM whatsapp_api_logs l
            JOIN users u ON l.user_id = u.id
            ORDER BY l.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$perPage, $offset]);
        $logs = $stmt->fetchAll();
        
        // Get total count
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM whatsapp_api_logs");
        $stmt->execute();
        $totalLogs = $stmt->fetch()['total'];
        
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
            $stmt = $this->db->prepare("
                SELECT u.*, w.webhook_url, w.webhook_enabled, w.notifications_enabled
                FROM users u
                LEFT JOIN whatsapp_user_settings w ON u.id = w.user_id
                WHERE u.id = ?
            ");
            $stmt->execute([$userId]);
            $userSettings = $stmt->fetch();
            
            // Get user's sessions
            $stmt = $this->db->prepare("
                SELECT * FROM whatsapp_sessions WHERE user_id = ? ORDER BY created_at DESC
            ");
            $stmt->execute([$userId]);
            $userSessions = $stmt->fetchAll();
            
            // Get user's API keys
            $stmt = $this->db->prepare("
                SELECT * FROM whatsapp_api_keys WHERE user_id = ? ORDER BY created_at DESC
            ");
            $stmt->execute([$userId]);
            $apiKeys = $stmt->fetchAll();
            
            View::render('admin/projects/whatsapp/user-detail', [
                'userSettings' => $userSettings,
                'userSessions' => $userSessions,
                'apiKeys' => $apiKeys,
                'pageTitle' => 'User Settings - Admin'
            ]);
        } else {
            // List all users with WhatsApp access
            $stmt = $this->db->prepare("
                SELECT u.id, u.username, u.email,
                    COUNT(DISTINCT s.id) as session_count,
                    COUNT(DISTINCT m.id) as message_count,
                    MAX(s.created_at) as last_session
                FROM users u
                LEFT JOIN whatsapp_sessions s ON u.id = s.user_id
                LEFT JOIN whatsapp_messages m ON s.id = m.session_id
                GROUP BY u.id, u.username, u.email
                HAVING session_count > 0
                ORDER BY last_session DESC
            ");
            $stmt->execute();
            $users = $stmt->fetchAll();
            
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
            
            $stmt = $this->db->prepare("DELETE FROM whatsapp_sessions WHERE id = ?");
            $stmt->execute([$sessionId]);
            
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
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM whatsapp_sessions");
        $stmt->execute();
        $totalSessions = $stmt->fetch()['total'] ?? 0;
        
        // Active sessions
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total FROM whatsapp_sessions WHERE status = 'connected'
        ");
        $stmt->execute();
        $activeSessions = $stmt->fetch()['total'] ?? 0;
        
        // Total messages
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM whatsapp_messages");
        $stmt->execute();
        $totalMessages = $stmt->fetch()['total'] ?? 0;
        
        // Messages today
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total FROM whatsapp_messages 
            WHERE DATE(created_at) = CURDATE()
        ");
        $stmt->execute();
        $messagesToday = $stmt->fetch()['total'] ?? 0;
        
        // Total users with WhatsApp
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT user_id) as total FROM whatsapp_sessions
        ");
        $stmt->execute();
        $totalUsers = $stmt->fetch()['total'] ?? 0;
        
        // API calls today
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total FROM whatsapp_api_logs 
            WHERE DATE(created_at) = CURDATE()
        ");
        $stmt->execute();
        $apiCallsToday = $stmt->fetch()['total'] ?? 0;
        
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
        $stmt = $this->db->prepare("
            SELECT s.*, u.username 
            FROM whatsapp_sessions s
            JOIN users u ON s.user_id = u.id
            ORDER BY s.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get recent messages
     */
    private function getRecentMessages($limit = 10)
    {
        $stmt = $this->db->prepare("
            SELECT m.*, s.session_name, u.username
            FROM whatsapp_messages m
            JOIN whatsapp_sessions s ON m.session_id = s.id
            JOIN users u ON s.user_id = u.id
            ORDER BY m.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
