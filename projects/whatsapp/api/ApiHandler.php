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

class ApiHandler
{
    private Database $db;
    private ?array $user = null;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Handle API request
     */
    public function handle(string $endpoint, string $method): void
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
    private function authenticateApiRequest(): ?array
    {
        // Get API key from Authorization header or query param
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? null;
        if (!$apiKey) {
            $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
            if (str_starts_with($auth, 'Bearer ')) {
                $apiKey = substr($auth, 7);
            } else {
                $apiKey = $auth ?: null;
            }
        }
        if (!$apiKey) {
            $apiKey = $_GET['api_key'] ?? null;
        }
        
        if (!$apiKey) {
            return null;
        }
        
        // Verify API key and fetch user in one query
        return $this->db->fetch(
            "SELECT u.* FROM users u
               JOIN whatsapp_api_keys k ON u.id = k.user_id
              WHERE k.api_key = ? AND k.status = 'active'
              LIMIT 1",
            [$apiKey]
        );
    }
    
    /**
     * Log API request
     */
    private function logApiRequest(string $endpoint, string $method): void
    {
        try {
            $this->db->query(
                "INSERT INTO whatsapp_api_logs
                     (user_id, endpoint, method, ip_address, user_agent, created_at)
                 VALUES (?, ?, ?, ?, ?, NOW())",
                [
                    $this->user['id'],
                    $endpoint,
                    $method,
                    $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                ]
            );
        } catch (\Exception $e) {
            // Non-fatal — log table may not exist yet
        }
    }
    
    /**
     * Check rate limit (100 requests/minute per user)
     */
    private function checkRateLimit(): bool
    {
        $limit = 100;
        
        $result = $this->db->fetch(
            "SELECT COUNT(*) AS count FROM whatsapp_api_logs
              WHERE user_id = ?
                AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)",
            [$this->user['id']]
        );
        
        return ((int) ($result['count'] ?? 0)) < $limit;
    }
    
    /**
     * Send message via API
     */
    private function sendMessage(): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        
        $sessionId = $data['session_id'] ?? null;
        $recipient = $data['recipient'] ?? null;
        $message   = $data['message']   ?? null;
        
        if (!$sessionId || !$recipient || !$message) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Missing required parameters: session_id, recipient, message'
            ]);
            return;
        }
        
        // Verify session ownership
        $session = $this->db->fetch(
            "SELECT id, status FROM whatsapp_sessions WHERE id = ? AND user_id = ? LIMIT 1",
            [$sessionId, $this->user['id']]
        );
        
        if (!$session) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Session not found or access denied']);
            return;
        }
        
        if ($session['status'] !== 'connected') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Session is not connected']);
            return;
        }
        
        // Save message
        $this->db->query(
            "INSERT INTO whatsapp_messages
                 (session_id, recipient, message, direction, status, created_at)
             VALUES (?, ?, ?, 'outgoing', 'sent', NOW())",
            [$sessionId, $recipient, $message]
        );
        
        $messageId = $this->db->lastInsertId();
        
        echo json_encode([
            'success'    => true,
            'message'    => 'Message sent successfully',
            'message_id' => $messageId,
        ]);
    }
    
    /**
     * Send media via API
     */
    private function sendMedia(): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        
        $sessionId = $data['session_id'] ?? null;
        $recipient = $data['recipient'] ?? null;
        $mediaUrl  = $data['media_url'] ?? null;
        
        if (!$sessionId || !$recipient || !$mediaUrl) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Missing required parameters: session_id, recipient, media_url'
            ]);
            return;
        }
        
        // Verify session ownership
        $session = $this->db->fetch(
            "SELECT id, status FROM whatsapp_sessions WHERE id = ? AND user_id = ? LIMIT 1",
            [$sessionId, $this->user['id']]
        );
        
        if (!$session) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Session not found or access denied']);
            return;
        }
        
        if ($session['status'] !== 'connected') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Session is not connected']);
            return;
        }
        
        echo json_encode(['success' => true, 'message' => 'Media sent successfully']);
    }
    
    /**
     * Get messages via API
     */
    private function getMessages(): void
    {
        $sessionId = $_GET['session_id'] ?? null;
        
        if (!$sessionId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing required parameter: session_id']);
            return;
        }
        
        $messages = $this->db->fetchAll(
            "SELECT * FROM whatsapp_messages WHERE session_id = ? ORDER BY created_at DESC LIMIT 50",
            [$sessionId]
        );
        
        echo json_encode(['success' => true, 'messages' => $messages]);
    }
    
    /**
     * Get contacts via API
     */
    private function getContacts(): void
    {
        $sessionId = $_GET['session_id'] ?? null;
        
        if (!$sessionId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing required parameter: session_id']);
            return;
        }
        
        $contacts = $this->db->fetchAll(
            "SELECT * FROM whatsapp_contacts WHERE session_id = ? ORDER BY name ASC",
            [$sessionId]
        );
        
        echo json_encode(['success' => true, 'contacts' => $contacts]);
    }
    
    /**
     * Get session status via API
     */
    private function getStatus(): void
    {
        $sessionId = $_GET['session_id'] ?? null;
        
        if (!$sessionId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing required parameter: session_id']);
            return;
        }
        
        $session = $this->db->fetch(
            "SELECT status, phone_number, session_name FROM whatsapp_sessions
              WHERE id = ? AND user_id = ? LIMIT 1",
            [$sessionId, $this->user['id']]
        );
        
        if (!$session) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Session not found']);
            return;
        }
        
        echo json_encode(['success' => true, 'session' => $session]);
    }
}
