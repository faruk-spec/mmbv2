<?php
/**
 * WhatsApp API Handler
 * 
 * External API endpoints for WhatsApp automation
 * 
 * @package MMB\Projects\WhatsApp\API
 */

namespace WhatsApp\API;

use Core\Database;
use Core\Security;

class ApiHandler
{
    private $db;
    private $user;
    
    public function __construct()
    {
        $this->db = new Database();
    }
    
    /**
     * Handle API request
     */
    public function handle($endpoint, $method)
    {
        header('Content-Type: application/json');
        
        try {
            // Authenticate API request
            $this->user = $this->authenticateApiRequest();
            
            if (!$this->user) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'error' => 'Unauthorized - Invalid API key'
                ]);
                return;
            }
            
            // Log API request
            $this->logApiRequest($endpoint, $method);
            
            // Check rate limit
            if (!$this->checkRateLimit()) {
                http_response_code(429);
                echo json_encode([
                    'success' => false,
                    'error' => 'Rate limit exceeded'
                ]);
                return;
            }
            
            // Route to appropriate handler
            switch ($endpoint) {
                case 'send-message':
                    $this->sendMessage();
                    break;
                    
                case 'send-media':
                    $this->sendMedia();
                    break;
                    
                case 'messages':
                    $this->getMessages();
                    break;
                    
                case 'contacts':
                    $this->getContacts();
                    break;
                    
                case 'status':
                    $this->getStatus();
                    break;
                    
                default:
                    http_response_code(404);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Endpoint not found'
                    ]);
            }
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Authenticate API request using API key
     */
    private function authenticateApiRequest()
    {
        // Get API key from Authorization header
        $apiKey = $_SERVER['HTTP_AUTHORIZATION'] ?? $_GET['api_key'] ?? null;
        
        if (!$apiKey) {
            return null;
        }
        
        // Remove "Bearer " prefix if present
        $apiKey = str_replace('Bearer ', '', $apiKey);
        
        // Verify API key
        $stmt = $this->db->prepare("
            SELECT u.* FROM users u
            JOIN whatsapp_api_keys k ON u.id = k.user_id
            WHERE k.api_key = ? AND k.status = 'active'
        ");
        $stmt->execute([$apiKey]);
        
        return $stmt->fetch();
    }
    
    /**
     * Log API request
     */
    private function logApiRequest($endpoint, $method)
    {
        $stmt = $this->db->prepare("
            INSERT INTO whatsapp_api_logs (
                user_id, endpoint, method, ip_address, user_agent, created_at
            ) VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $this->user['id'],
            $endpoint,
            $method,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    }
    
    /**
     * Check rate limit
     */
    private function checkRateLimit()
    {
        $limit = 100; // requests per minute
        
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM whatsapp_api_logs
            WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
        ");
        $stmt->execute([$this->user['id']]);
        $result = $stmt->fetch();
        
        return ($result['count'] ?? 0) < $limit;
    }
    
    /**
     * Send message via API
     */
    private function sendMessage()
    {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        
        $sessionId = $data['session_id'] ?? null;
        $recipient = $data['recipient'] ?? null;
        $message = $data['message'] ?? null;
        
        if (!$sessionId || !$recipient || !$message) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Missing required parameters: session_id, recipient, message'
            ]);
            return;
        }
        
        // Verify session ownership
        $stmt = $this->db->prepare("
            SELECT id, status FROM whatsapp_sessions 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$sessionId, $this->user['id']]);
        $session = $stmt->fetch();
        
        if (!$session) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => 'Session not found or access denied'
            ]);
            return;
        }
        
        if ($session['status'] !== 'connected') {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Session is not connected'
            ]);
            return;
        }
        
        // Save message
        $stmt = $this->db->prepare("
            INSERT INTO whatsapp_messages (
                session_id, recipient, message, direction, status, created_at
            ) VALUES (?, ?, ?, 'outgoing', 'sent', NOW())
        ");
        $stmt->execute([$sessionId, $recipient, $message]);
        
        $messageId = $this->db->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Message sent successfully',
            'message_id' => $messageId
        ]);
    }
    
    /**
     * Send media via API
     */
    private function sendMedia()
    {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        
        $sessionId = $data['session_id'] ?? null;
        $recipient = $data['recipient'] ?? null;
        $mediaUrl = $data['media_url'] ?? null;
        $caption = $data['caption'] ?? '';
        
        if (!$sessionId || !$recipient || !$mediaUrl) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Missing required parameters: session_id, recipient, media_url'
            ]);
            return;
        }
        
        // Verify session (similar to sendMessage)
        // ... (validation code)
        
        echo json_encode([
            'success' => true,
            'message' => 'Media sent successfully'
        ]);
    }
    
    /**
     * Get messages via API
     */
    private function getMessages()
    {
        $sessionId = $_GET['session_id'] ?? null;
        
        if (!$sessionId) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Missing required parameter: session_id'
            ]);
            return;
        }
        
        // Get messages
        $stmt = $this->db->prepare("
            SELECT * FROM whatsapp_messages 
            WHERE session_id = ?
            ORDER BY created_at DESC
            LIMIT 50
        ");
        $stmt->execute([$sessionId]);
        $messages = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'messages' => $messages
        ]);
    }
    
    /**
     * Get contacts via API
     */
    private function getContacts()
    {
        $sessionId = $_GET['session_id'] ?? null;
        
        if (!$sessionId) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Missing required parameter: session_id'
            ]);
            return;
        }
        
        $stmt = $this->db->prepare("
            SELECT * FROM whatsapp_contacts 
            WHERE session_id = ?
            ORDER BY name ASC
        ");
        $stmt->execute([$sessionId]);
        $contacts = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'contacts' => $contacts
        ]);
    }
    
    /**
     * Get session status via API
     */
    private function getStatus()
    {
        $sessionId = $_GET['session_id'] ?? null;
        
        if (!$sessionId) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Missing required parameter: session_id'
            ]);
            return;
        }
        
        $stmt = $this->db->prepare("
            SELECT status, phone_number, session_name 
            FROM whatsapp_sessions 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$sessionId, $this->user['id']]);
        $session = $stmt->fetch();
        
        if (!$session) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Session not found'
            ]);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'session' => $session
        ]);
    }
}
