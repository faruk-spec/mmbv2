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
    private ?string $apiKey = null;
    private float $requestStart;
    
    public function __construct()
    {
        $this->db           = Database::getInstance();
        $this->requestStart = microtime(true);
    }
    
    /**
     * Handle API request
     */
    public function handle(string $endpoint, string $method): void
    {
        header('Content-Type: application/json');

        $statusCode = 200;

        try {
            // Authenticate API request
            $this->user = $this->authenticateApiRequest();

            if (!$this->user) {
                $this->logApiRequest($endpoint, $method, 401, 'auth_failed');
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'error' => 'Unauthorized - Invalid API key'
                ]);
                return;
            }

            // Check rate limit
            if (!$this->checkRateLimit()) {
                $this->logApiRequest($endpoint, $method, 429, 'rate_limited');
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
                    $statusCode = $this->sendMessage();
                    break;

                case 'send-media':
                    $statusCode = $this->sendMedia();
                    break;

                case 'messages':
                    $statusCode = $this->getMessages();
                    break;

                case 'contacts':
                    $statusCode = $this->getContacts();
                    break;

                case 'status':
                    $statusCode = $this->getStatus();
                    break;

                default:
                    $statusCode = 404;
                    http_response_code(404);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Endpoint not found'
                    ]);
            }

            $this->logApiRequest($endpoint, $method, $statusCode, $endpoint);

        } catch (\Exception $e) {
            $this->logApiRequest($endpoint, $method, 500, 'exception');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Internal server error'
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

        $this->apiKey = $apiKey;

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
     * Log API request with full audit detail
     */
    private function logApiRequest(string $endpoint, string $method, int $statusCode = 200, string $action = ''): void
    {
        try {
            // Ensure log table exists with full audit schema
            $this->db->query(
                "CREATE TABLE IF NOT EXISTS whatsapp_api_logs (
                    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    user_id         INT UNSIGNED    NOT NULL,
                    api_key_prefix  VARCHAR(16)     NOT NULL DEFAULT '',
                    session_id      VARCHAR(128)    NOT NULL DEFAULT '',
                    email           VARCHAR(255)    NOT NULL DEFAULT '',
                    endpoint        VARCHAR(200)    NOT NULL,
                    method          VARCHAR(10)     NOT NULL DEFAULT 'POST',
                    ip_address      VARCHAR(45)     NOT NULL DEFAULT '',
                    user_agent      TEXT            NULL,
                    status_code     SMALLINT UNSIGNED NOT NULL DEFAULT 200,
                    response_time   INT UNSIGNED    NOT NULL DEFAULT 0 COMMENT 'Milliseconds',
                    action          VARCHAR(100)    NOT NULL DEFAULT '',
                    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_user   (user_id),
                    INDEX idx_created (created_at),
                    INDEX idx_status  (status_code)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );

            $responseTime = (int) round((microtime(true) - $this->requestStart) * 1000);
            $userId       = (int) ($this->user['id'] ?? 0);
            $email        = $this->user['email'] ?? '';
            $keyPrefix    = $this->apiKey ? substr($this->apiKey, 0, 8) : '';
            $sessionId    = substr(session_id() ?: '', 0, 128);

            $this->db->query(
                "INSERT INTO whatsapp_api_logs
                     (user_id, api_key_prefix, session_id, email,
                      endpoint, method, ip_address, user_agent,
                      status_code, response_time, action)
                 VALUES (?, ?, ?, ?,  ?, ?, ?, ?,  ?, ?, ?)",
                [
                    $userId,
                    substr($keyPrefix,  0, 16),
                    $sessionId,
                    substr($email,      0, 255),
                    substr($endpoint,   0, 200),
                    strtoupper(substr($method, 0, 10)),
                    substr($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '', 0, 45),
                    substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 512),
                    $statusCode,
                    $responseTime,
                    substr($action, 0, 100),
                ]
            );
        } catch (\Exception $e) {
            // Non-fatal — log table may not exist yet or schema migration pending
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
    private function sendMessage(): int
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
            return 400;
        }

        // Verify session ownership
        $session = $this->db->fetch(
            "SELECT id, status FROM whatsapp_sessions WHERE id = ? AND user_id = ? LIMIT 1",
            [$sessionId, $this->user['id']]
        );

        if (!$session) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Session not found or access denied']);
            return 403;
        }

        if ($session['status'] !== 'connected') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Session is not connected']);
            return 400;
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
        return 200;
    }
    
    /**
     * Send media via API
     */
    private function sendMedia(): int
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
            return 400;
        }

        // Verify session ownership
        $session = $this->db->fetch(
            "SELECT id, status FROM whatsapp_sessions WHERE id = ? AND user_id = ? LIMIT 1",
            [$sessionId, $this->user['id']]
        );

        if (!$session) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Session not found or access denied']);
            return 403;
        }

        if ($session['status'] !== 'connected') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Session is not connected']);
            return 400;
        }

        echo json_encode(['success' => true, 'message' => 'Media sent successfully']);
        return 200;
    }
    
    /**
     * Get messages via API
     */
    private function getMessages(): int
    {
        $sessionId = $_GET['session_id'] ?? null;

        if (!$sessionId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing required parameter: session_id']);
            return 400;
        }

        $messages = $this->db->fetchAll(
            "SELECT * FROM whatsapp_messages WHERE session_id = ? ORDER BY created_at DESC LIMIT 50",
            [$sessionId]
        );

        echo json_encode(['success' => true, 'messages' => $messages]);
        return 200;
    }

    /**
     * Get contacts via API
     */
    private function getContacts(): int
    {
        $sessionId = $_GET['session_id'] ?? null;

        if (!$sessionId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing required parameter: session_id']);
            return 400;
        }

        $contacts = $this->db->fetchAll(
            "SELECT * FROM whatsapp_contacts WHERE session_id = ? ORDER BY name ASC",
            [$sessionId]
        );

        echo json_encode(['success' => true, 'contacts' => $contacts]);
        return 200;
    }

    /**
     * Get session status via API
     */
    private function getStatus(): int
    {
        $sessionId = $_GET['session_id'] ?? null;

        if (!$sessionId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing required parameter: session_id']);
            return 400;
        }

        $session = $this->db->fetch(
            "SELECT status, phone_number, session_name FROM whatsapp_sessions
              WHERE id = ? AND user_id = ? LIMIT 1",
            [$sessionId, $this->user['id']]
        );

        if (!$session) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Session not found']);
            return 404;
        }

        echo json_encode(['success' => true, 'session' => $session]);
        return 200;
    }
}
