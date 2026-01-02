<?php
/**
 * API Management Admin Controller
 * 
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;
use Core\Auth;
use Core\Cache;
use Core\API\ApiAuth;
use Core\API\RateLimiter;

class ApiController extends BaseController
{
    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
    }
    
    /**
     * API Keys Management
     */
    public function keys(): void
    {
        $db = Database::getInstance();
        
        // Get all API keys with user information
        $keys = $db->fetchAll(
            "SELECT ak.*, u.name as user_name, u.email 
             FROM api_keys ak 
             LEFT JOIN users u ON ak.user_id = u.id 
             ORDER BY ak.created_at DESC"
        );
        
        $this->view('admin/api/keys', [
            'title' => 'API Keys Management',
            'keys' => $keys
        ]);
    }
    
    /**
     * API Request Logs
     */
    public function logs(): void
    {
        $db = Database::getInstance();
        
        $page = $_GET['page'] ?? 1;
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        // Get request logs with pagination
        $logs = $db->fetchAll(
            "SELECT arl.*, ak.name as key_name, u.name as user_name 
             FROM api_request_logs arl 
             LEFT JOIN api_keys ak ON arl.api_key_id = ak.id 
             LEFT JOIN users u ON ak.user_id = u.id 
             ORDER BY arl.created_at DESC 
             LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
        
        $total = $db->fetch("SELECT COUNT(*) as count FROM api_request_logs")['count'];
        
        $this->view('admin/api/logs', [
            'title' => 'API Request Logs',
            'logs' => $logs,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => ceil($total / $perPage)
        ]);
    }
    
    /**
     * Rate Limits Configuration
     */
    public function rateLimits(): void
    {
        $db = Database::getInstance();
        
        // Get rate limit settings from system_settings
        $settings = $db->fetchAll(
            "SELECT * FROM system_settings 
             WHERE setting_key LIKE 'api_rate_limit_%' 
             ORDER BY setting_key"
        );
        
        $this->view('admin/api/rate-limits', [
            'title' => 'API Rate Limits',
            'settings' => $settings
        ]);
    }
    
    /**
     * API Documentation
     */
    public function documentation(): void
    {
        $this->view('admin/api/documentation', [
            'title' => 'API Documentation'
        ]);
    }
    
    /**
     * Generate new API key (AJAX)
     */
    public function generateKey(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        $userId = $_POST['user_id'] ?? null;
        $name = $_POST['name'] ?? 'API Key';
        $permissions = $_POST['permissions'] ?? ['*'];
        $expiresAt = $_POST['expires_at'] ?? null;
        
        if (!$userId) {
            $this->jsonResponse(['success' => false, 'message' => 'User ID is required']);
            return;
        }
        
        try {
            $keyData = ApiAuth::generateKey($userId, $name, $permissions, $expiresAt);
            $this->jsonResponse(['success' => true, 'data' => $keyData]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * Revoke API key (AJAX)
     */
    public function revokeKey(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        $keyId = $_POST['key_id'] ?? null;
        
        if (!$keyId) {
            $this->jsonResponse(['success' => false, 'message' => 'Key ID is required']);
            return;
        }
        
        try {
            ApiAuth::revokeKey($keyId);
            $this->jsonResponse(['success' => true, 'message' => 'API key revoked successfully']);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
