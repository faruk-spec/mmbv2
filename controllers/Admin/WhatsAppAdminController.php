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
use Core\ActivityLogger;

class WhatsAppAdminController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();

        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }

        if (!Auth::hasPermissionGroup('whatsapp')) {
            $_SESSION['flash_error'] = 'You do not have permission to access that section.';
            header('Location: /admin/dashboard');
            exit;
        }
    }
    
    /**
     * WhatsApp Overview/Dashboard
     */
    public function overview()
    {
        if (!Auth::hasPermission('whatsapp.overview')) {
            $_SESSION['flash_error'] = 'You do not have permission to access that section.';
            header('Location: /admin/dashboard');
            exit;
        }
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
        if (!Auth::hasPermission('whatsapp.sessions')) {
            $_SESSION['flash_error'] = 'You do not have permission to access that section.';
            header('Location: /admin/dashboard');
            exit;
        }
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
        if (!Auth::hasPermission('whatsapp.messages')) {
            $_SESSION['flash_error'] = 'You do not have permission to access that section.';
            header('Location: /admin/dashboard');
            exit;
        }
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
        if (!Auth::hasPermission('whatsapp.api_logs')) {
            $_SESSION['flash_error'] = 'You do not have permission to access that section.';
            header('Location: /admin/dashboard');
            exit;
        }
        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 50;
        $offset  = ($page - 1) * $perPage;

        // Optional user filter
        $filterUserId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : null;

        if ($filterUserId) {
            $logs = $this->db->fetchAll("
                SELECT l.*, u.name as username, u.email
                FROM whatsapp_api_logs l
                JOIN users u ON l.user_id = u.id
                WHERE l.user_id = ?
                ORDER BY l.created_at DESC
                LIMIT ? OFFSET ?
            ", [$filterUserId, $perPage, $offset]);

            $totalLogs = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM whatsapp_api_logs WHERE user_id = ?",
                [$filterUserId]
            );

            $filterUser = $this->db->fetch(
                "SELECT id, name, email FROM users WHERE id = ?",
                [$filterUserId]
            );
        } else {
            $logs = $this->db->fetchAll("
                SELECT l.*, u.name as username, u.email
                FROM whatsapp_api_logs l
                JOIN users u ON l.user_id = u.id
                ORDER BY l.created_at DESC
                LIMIT ? OFFSET ?
            ", [$perPage, $offset]);

            $totalLogs = $this->db->fetchColumn("SELECT COUNT(*) FROM whatsapp_api_logs");
            $filterUser = null;
        }

        // Top users by API usage
        $topUsers = $this->db->fetchAll("
            SELECT u.id, u.name, u.email, COUNT(l.id) AS total
            FROM whatsapp_api_logs l
            JOIN users u ON l.user_id = u.id
            GROUP BY u.id, u.name, u.email
            ORDER BY total DESC
            LIMIT 20
        ");

        View::render('admin/projects/whatsapp/api-logs', [
            'logs'         => $logs,
            'currentPage'  => $page,
            'totalPages'   => (int) ceil(($totalLogs ?: 0) / $perPage),
            'totalLogs'    => (int) $totalLogs,
            'filterUserId' => $filterUserId,
            'filterUser'   => $filterUser ?? null,
            'topUsers'     => $topUsers,
            'pageTitle'    => 'API Logs - Admin'
        ]);
    }
    
    /**
     * Manage user settings
     */
    public function userSettings()
    {
        if (!Auth::hasPermission('whatsapp.users')) {
            $_SESSION['flash_error'] = 'You do not have permission to access that section.';
            header('Location: /admin/dashboard');
            exit;
        }
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
            $page = $_GET['page'] ?? 1;
            $perPage = 20;
            $offset = ($page - 1) * $perPage;
            
            $users = $this->db->fetchAll("
                SELECT u.id as user_id, u.name as username, u.email, u.created_at,
                    COUNT(DISTINCT s.id) as total_sessions,
                    ak.api_key,
                    ws.webhook_url,
                    MAX(s.last_activity) as last_activity
                FROM users u
                LEFT JOIN whatsapp_sessions s ON u.id = s.user_id
                LEFT JOIN whatsapp_api_keys ak ON u.id = ak.user_id
                LEFT JOIN whatsapp_user_settings ws ON u.id = ws.user_id
                GROUP BY u.id, u.name, u.email, u.created_at, ak.api_key, ws.webhook_url
                HAVING total_sessions > 0
                ORDER BY last_activity DESC
                LIMIT ? OFFSET ?
            ", [$perPage, $offset]);
            
            $totalUsers = $this->db->fetchColumn("
                SELECT COUNT(DISTINCT u.id)
                FROM users u
                LEFT JOIN whatsapp_sessions s ON u.id = s.user_id
                WHERE s.id IS NOT NULL
            ") ?? 0;
            $totalPages = ceil($totalUsers / $perPage);
            
            View::render('admin/projects/whatsapp/users', [
                'users' => $users,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalUsers' => $totalUsers,
                'pageTitle' => 'WhatsApp Users - Admin'
            ]);
        }
    }
    
    /**
     * Delete session (admin action)
     */
    public function deleteSession()
    {
        if (!Auth::hasPermission('whatsapp.sessions')) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit;
        }
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
            if (!Security::verifyCsrfToken($_POST['_csrf_token'] ?? '')) {
                throw new \Exception('Invalid CSRF token');
            }
            
            $this->db->query("DELETE FROM whatsapp_sessions WHERE id = ?", [$sessionId]);
            try { ActivityLogger::logDelete(Auth::id(), 'whatsapp', 'session', $sessionId); } catch (\Throwable $_) {}
            
            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Session deleted successfully'
            ]);
            exit;
            
        } catch (\Exception $e) {
            http_response_code(400);
            header('Content-Type: application/json');
            try { ActivityLogger::logFailure(Auth::id(), 'session_delete', $e->getMessage()); } catch (\Throwable $_) {}
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }
    
    /**
     * GET /admin/whatsapp/api-keys
     * Show all WhatsApp API keys + per-user usage + generate / revoke.
     */
    public function whatsappApiKeys(): void
    {
        if (!Auth::hasPermission('whatsapp.api_logs')) {
            $_SESSION['flash_error'] = 'You do not have permission to access that section.';
            header('Location: /admin/dashboard');
            exit;
        }

        // Optional filters
        $filterUserId  = isset($_GET['user_id'])     ? (int) $_GET['user_id']     : null;
        $filterKeyId   = isset($_GET['key_id'])      ? (int) $_GET['key_id']      : null;

        // All API keys (optionally filtered by user)
        try {
            $keysWhere  = $filterUserId ? 'WHERE k.user_id = ?' : '';
            $keysParams = $filterUserId ? [$filterUserId] : [];
            $keys = $this->db->fetchAll(
                "SELECT k.*, u.name AS user_name, u.email
                   FROM whatsapp_api_keys k
                   LEFT JOIN users u ON k.user_id = u.id
                   {$keysWhere}
                   ORDER BY k.created_at DESC
                   LIMIT 500",
                $keysParams
            );
        } catch (\Exception $e) {
            $keys = [];
        }

        // Per-user usage summary (from whatsapp_api_logs)
        $userUsage = [];
        try {
            $userUsage = $this->db->fetchAll(
                "SELECT l.user_id, u.name AS user_name, u.email,
                        COUNT(l.id) AS total_requests,
                        MAX(l.created_at) AS last_request,
                        SUM(l.status_code >= 200 AND l.status_code < 300) AS success_count,
                        SUM(l.status_code >= 400) AS error_count
                   FROM whatsapp_api_logs l
                   LEFT JOIN users u ON l.user_id = u.id
                   GROUP BY l.user_id, u.name, u.email
                   ORDER BY total_requests DESC
                   LIMIT 100"
            ) ?: [];
        } catch (\Exception $e) {
            // Table may not exist yet
        }

        // Stats cards
        $totalKeys     = 0;
        $activeKeys    = 0;
        $totalRequests = 0;
        try {
            $statsRow = $this->db->fetch(
                "SELECT COUNT(*) AS total,
                        SUM(status = 'active') AS active
                   FROM whatsapp_api_keys"
            );
            $totalKeys  = (int) ($statsRow['total']  ?? 0);
            $activeKeys = (int) ($statsRow['active'] ?? 0);
            $totalRequests = (int) ($this->db->fetchColumn("SELECT COUNT(*) FROM whatsapp_api_logs") ?? 0);
        } catch (\Exception $e) {
            // Tables may not exist yet
        }

        // Recent request logs (filtered by user and/or key prefix)
        $recentLogs = [];
        $filterKeyPrefix = '';
        try {
            if ($filterKeyId) {
                $kRow = $this->db->fetch("SELECT api_key FROM whatsapp_api_keys WHERE id = ? LIMIT 1", [$filterKeyId]);
                $filterKeyPrefix = $kRow ? substr($kRow['api_key'], 0, 8) : '';
            }

            $conditions = [];
            $logParams  = [];
            if ($filterUserId)  { $conditions[] = 'l.user_id = ?'; $logParams[] = $filterUserId; }
            if ($filterKeyPrefix) { $conditions[] = 'l.api_key_prefix = ?'; $logParams[] = $filterKeyPrefix; }
            $whereClause = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

            $recentLogs = $this->db->fetchAll(
                "SELECT l.id, l.user_id, u.name AS user_name, u.email,
                        l.api_key_prefix, l.endpoint, l.method, l.ip_address,
                        l.status_code, l.response_time, l.action, l.created_at
                   FROM whatsapp_api_logs l
                   LEFT JOIN users u ON l.user_id = u.id
                   {$whereClause}
                   ORDER BY l.id DESC
                   LIMIT 200",
                $logParams
            ) ?: [];
        } catch (\Exception $e) {
            // Table may not exist yet
        }

        // All users for the generate-key dropdown
        $allUsers = [];
        try {
            $allUsers = $this->db->fetchAll(
                "SELECT id, name, email FROM users WHERE status != 'banned' ORDER BY name ASC LIMIT 1000"
            );
        } catch (\Exception $e) {
            // Non-fatal
        }

        $filterUser = null;
        if ($filterUserId) {
            try {
                $filterUser = $this->db->fetch("SELECT id, name, email FROM users WHERE id = ?", [$filterUserId]);
            } catch (\Exception $e) {
                // Non-fatal
            }
        }

        View::render('admin/projects/whatsapp/api-keys', [
            'keys'            => $keys,
            'allUsers'        => $allUsers,
            'userUsage'       => $userUsage,
            'totalKeys'       => $totalKeys,
            'activeKeys'      => $activeKeys,
            'totalRequests'   => $totalRequests,
            'recentLogs'      => $recentLogs,
            'filterUserId'    => $filterUserId,
            'filterUser'      => $filterUser,
            'filterKeyId'     => $filterKeyId,
            'filterKeyPrefix' => $filterKeyPrefix,
            'pageTitle'       => 'WhatsApp API Keys — Admin',
        ]);
    }

    /**
     * POST /admin/whatsapp/api-keys/generate
     * Admin generates a WhatsApp API key for a user.
     */
    public function generateWhatsAppApiKeyForUser(): void
    {
        if (!Auth::hasPermission('whatsapp.api_logs')) {
            $this->jsonResponse(['success' => false, 'error' => 'Permission denied'], 403);
            return;
        }

        $csrfToken = $_POST['_csrf_token'] ?? $_POST['_token'] ?? '';
        if (!Security::validateCsrfToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }

        $userId = (int) ($_POST['user_id'] ?? 0);
        if (!$userId) {
            $this->jsonResponse(['success' => false, 'error' => 'User ID required'], 400);
            return;
        }

        $user = $this->db->fetch("SELECT id, name, email FROM users WHERE id = ? LIMIT 1", [$userId]);
        if (!$user) {
            $this->jsonResponse(['success' => false, 'error' => 'User not found'], 404);
            return;
        }

        try {
            // Ensure table exists
            $this->db->query(
                "CREATE TABLE IF NOT EXISTS whatsapp_api_keys (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    api_key VARCHAR(255) NOT NULL UNIQUE,
                    status ENUM('active','inactive','revoked') DEFAULT 'active',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    last_used_at TIMESTAMP NULL,
                    INDEX idx_user_id (user_id),
                    INDEX idx_api_key (api_key)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );

            // Revoke existing active keys for this user
            $this->db->query(
                "UPDATE whatsapp_api_keys SET status = 'revoked' WHERE user_id = ? AND status = 'active'",
                [$userId]
            );

            $newKey = 'wa_' . bin2hex(random_bytes(20));
            $this->db->query(
                "INSERT INTO whatsapp_api_keys (user_id, api_key, status, created_at)
                 VALUES (?, ?, 'active', NOW())",
                [$userId, $newKey]
            );

            try {
                ActivityLogger::logCreate(Auth::id(), 'whatsapp', 'api_key', 0, [
                    'for_user_id' => $userId,
                    'key_prefix'  => substr($newKey, 0, 8),
                ]);
            } catch (\Throwable $_) {}

            $this->jsonResponse([
                'success' => true,
                'api_key' => $newKey,
                'message' => 'API key generated for ' . $user['name'],
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => 'Server error generating key'], 500);
        }
    }

    /**
     * POST /admin/whatsapp/api-keys/revoke
     * Admin revokes a WhatsApp API key.
     */
    public function revokeWhatsAppApiKey(): void
    {
        if (!Auth::hasPermission('whatsapp.api_logs')) {
            $this->jsonResponse(['success' => false, 'error' => 'Permission denied'], 403);
            return;
        }

        $csrfToken = $_POST['_csrf_token'] ?? $_POST['_token'] ?? '';
        if (!Security::validateCsrfToken($csrfToken)) {
            $this->jsonResponse(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }

        $keyId = (int) ($_POST['key_id'] ?? 0);
        if (!$keyId) {
            $this->jsonResponse(['success' => false, 'error' => 'Key ID required'], 400);
            return;
        }

        try {
            $key = $this->db->fetch("SELECT id, user_id FROM whatsapp_api_keys WHERE id = ? LIMIT 1", [$keyId]);
            if (!$key) {
                $this->jsonResponse(['success' => false, 'error' => 'Key not found'], 404);
                return;
            }
            $this->db->query(
                "UPDATE whatsapp_api_keys SET status = 'revoked' WHERE id = ?",
                [$keyId]
            );
            try {
                ActivityLogger::logDelete(Auth::id(), 'whatsapp', 'api_key', $keyId);
            } catch (\Throwable $_) {}
            $this->jsonResponse(['success' => true, 'message' => 'Key revoked.']);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => 'Server error'], 500);
        }
    }

    /**
     * Helper: emit JSON and exit.
     */
    private function jsonResponse(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
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
