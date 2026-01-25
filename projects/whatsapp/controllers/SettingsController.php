<?php
/**
 * WhatsApp Settings Controller
 * 
 * @package MMB\Projects\WhatsApp\Controllers
 */

namespace WhatsApp\Controllers;

use Core\Auth;
use Core\View;
use Core\Database;
use Core\Security;

class SettingsController
{
    private $db;
    private $user;
    
    public function __construct()
    {
        $this->user = Auth::user();
        $this->db = new Database();
    }
    
    /**
     * Display settings page
     */
    public function index()
    {
        $apiKey = $this->getUserApiKey();
        $webhookUrl = $this->getWebhookUrl();
        
        View::render('whatsapp/settings', [
            'user' => $this->user,
            'apiKey' => $apiKey,
            'webhookUrl' => $webhookUrl,
            'pageTitle' => 'WhatsApp Settings'
        ]);
    }
    
    /**
     * Update settings
     */
    public function update()
    {
        header('Content-Type: application/json');
        
        try {
            // Validate CSRF token
            if (!Security::validateCSRF($_POST['csrf_token'] ?? '')) {
                throw new \Exception('Invalid CSRF token');
            }
            
            $action = $_POST['action'] ?? null;
            
            switch ($action) {
                case 'generate_api_key':
                    $apiKey = $this->generateApiKey();
                    echo json_encode([
                        'success' => true,
                        'message' => 'API key generated successfully',
                        'api_key' => $apiKey
                    ]);
                    break;
                    
                case 'update_webhook':
                    $webhookUrl = $_POST['webhook_url'] ?? '';
                    $this->updateWebhookUrl($webhookUrl);
                    echo json_encode([
                        'success' => true,
                        'message' => 'Webhook URL updated successfully'
                    ]);
                    break;
                    
                default:
                    throw new \Exception('Invalid action');
            }
            
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get user's API key
     */
    private function getUserApiKey()
    {
        $stmt = $this->db->prepare("
            SELECT api_key FROM whatsapp_api_keys 
            WHERE user_id = ? AND status = 'active'
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([$this->user['id']]);
        $result = $stmt->fetch();
        
        return $result['api_key'] ?? null;
    }
    
    /**
     * Generate new API key
     */
    private function generateApiKey()
    {
        // Deactivate old keys
        $stmt = $this->db->prepare("
            UPDATE whatsapp_api_keys 
            SET status = 'inactive' 
            WHERE user_id = ?
        ");
        $stmt->execute([$this->user['id']]);
        
        // Generate new key
        $apiKey = 'whapi_' . bin2hex(random_bytes(32));
        
        $stmt = $this->db->prepare("
            INSERT INTO whatsapp_api_keys (
                user_id, api_key, status, created_at
            ) VALUES (?, ?, 'active', NOW())
        ");
        $stmt->execute([$this->user['id'], $apiKey]);
        
        return $apiKey;
    }
    
    /**
     * Get webhook URL
     */
    private function getWebhookUrl()
    {
        $stmt = $this->db->prepare("
            SELECT webhook_url FROM whatsapp_user_settings 
            WHERE user_id = ?
        ");
        $stmt->execute([$this->user['id']]);
        $result = $stmt->fetch();
        
        return $result['webhook_url'] ?? '';
    }
    
    /**
     * Update webhook URL
     */
    private function updateWebhookUrl($url)
    {
        $stmt = $this->db->prepare("
            INSERT INTO whatsapp_user_settings (user_id, webhook_url, updated_at)
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
                webhook_url = VALUES(webhook_url),
                updated_at = NOW()
        ");
        $stmt->execute([$this->user['id'], $url]);
    }
}
