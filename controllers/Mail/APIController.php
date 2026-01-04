<?php

/**
 * REST API Controller
 * 
 * Provides RESTful API endpoints for programmatic access
 * Supports email operations, mailbox management, domain operations
 */

namespace Controllers\Mail;

use Core\View;

use Controllers\BaseController;
use Core\Database;

class APIController extends BaseController
{
    private $db;
    private $apiKey;
    private $subscriberId;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->authenticate();
    }
    
    /**
     * Authenticate API request using Bearer token
     */
    private function authenticate()
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $this->apiKey = $matches[1];
        } else {
            $this->jsonError('Missing API key', 401);
        }
        
        // Validate API key
        $key = $this->db->fetch(
            "SELECT ak.*, s.id as subscriber_id 
             FROM mail_api_keys ak
             JOIN mail_subscribers s ON ak.subscriber_id = s.id
             WHERE ak.api_key = ? AND ak.is_active = 1",
            [$this->apiKey]
        );
        
        if (!$key) {
            $this->jsonError('Invalid API key', 401);
        }
        
        $this->subscriberId = $key['subscriber_id'];
        
        // Check rate limit
        if (!$this->checkRateLimit($key['id'])) {
            $this->jsonError('Rate limit exceeded', 429);
        }
    }
    
    /**
     * Send email via API
     */
    public function sendMail()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        $required = ['to', 'subject', 'body'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return $this->jsonError("Field '$field' is required", 400);
            }
        }
        
        // Get default mailbox for subscriber
        $mailbox = $this->db->fetch(
            "SELECT id, email FROM mail_mailboxes 
             WHERE subscriber_id = ? AND role_type = 'subscriber_owner'
             LIMIT 1",
            [$this->subscriberId]
        );
        
        if (!$mailbox) {
            return $this->jsonError('No mailbox found', 404);
        }
        
        // Queue email for sending
        $queueId = $this->db->query(
            "INSERT INTO mail_queue (mailbox_id, from_email, to_email, subject, 
                                    body_html, body_text, is_html, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())",
            [$mailbox['id'], $mailbox['email'], $data['to'], $data['subject'],
             $data['html'] ?? true ? $data['body'] : null,
             $data['html'] ?? true ? null : $data['body'],
             $data['html'] ?? true]
        );
        
        $this->jsonSuccess([
            'message_id' => $queueId,
            'status' => 'queued',
        ], 202);
    }
    
    /**
     * Get inbox messages
     */
    public function getInbox()
    {
        $page = $_GET['page'] ?? 1;
        $limit = min($_GET['limit'] ?? 50, 100);
        $offset = ($page - 1) * $limit;
        
        $mailboxId = $_GET['mailbox_id'] ?? null;
        
        $messages = $this->db->fetchAll(
            "SELECT m.id, m.from_email, m.subject, m.is_read, m.is_starred, 
                    m.has_attachments, m.created_at
             FROM mail_messages m
             JOIN mail_folders f ON m.folder_id = f.id
             JOIN mail_mailboxes mb ON m.mailbox_id = mb.id
             WHERE mb.subscriber_id = ? 
             AND f.folder_type = 'inbox'
             " . ($mailboxId ? "AND mb.id = ?" : "") . "
             ORDER BY m.created_at DESC
             LIMIT ? OFFSET ?",
            array_filter([$this->subscriberId, $mailboxId, $limit, $offset])
        );
        
        $this->jsonSuccess([
            'messages' => $messages,
            'page' => $page,
            'limit' => $limit,
        ]);
    }
    
    /**
     * Get single message
     */
    public function getMessage($messageId)
    {
        $message = $this->db->fetch(
            "SELECT m.*, f.folder_type
             FROM mail_messages m
             JOIN mail_folders f ON m.folder_id = f.id
             JOIN mail_mailboxes mb ON m.mailbox_id = mb.id
             WHERE m.id = ? AND mb.subscriber_id = ?",
            [$messageId, $this->subscriberId]
        );
        
        if (!$message) {
            return $this->jsonError('Message not found', 404);
        }
        
        // Get attachments
        $attachments = $this->db->fetchAll(
            "SELECT file_name, file_size, mime_type 
             FROM mail_attachments WHERE message_id = ?",
            [$messageId]
        );
        
        $message['attachments'] = $attachments;
        
        $this->jsonSuccess($message);
    }
    
    /**
     * Delete message
     */
    public function deleteMessage($messageId)
    {
        // Verify ownership
        $message = $this->db->fetch(
            "SELECT m.id FROM mail_messages m
             JOIN mail_mailboxes mb ON m.mailbox_id = mb.id
             WHERE m.id = ? AND mb.subscriber_id = ?",
            [$messageId, $this->subscriberId]
        );
        
        if (!$message) {
            return $this->jsonError('Message not found', 404);
        }
        
        // Soft delete
        $this->db->query(
            "UPDATE mail_messages SET deleted_at = NOW() WHERE id = ?",
            [$messageId]
        );
        
        $this->jsonSuccess(['message' => 'Message deleted'], 200);
    }
    
    /**
     * Mark message as read/unread
     */
    public function updateMessageReadStatus($messageId)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $isRead = $data['is_read'] ?? true;
        
        $this->db->query(
            "UPDATE mail_messages m
             JOIN mail_mailboxes mb ON m.mailbox_id = mb.id
             SET m.is_read = ?
             WHERE m.id = ? AND mb.subscriber_id = ?",
            [$isRead, $messageId, $this->subscriberId]
        );
        
        $this->jsonSuccess(['message' => 'Status updated']);
    }
    
    /**
     * List mailboxes
     */
    public function getMailboxes()
    {
        $mailboxes = $this->db->fetchAll(
            "SELECT id, email, role_type, storage_used, storage_quota, is_active
             FROM mail_mailboxes 
             WHERE subscriber_id = ? AND deleted_at IS NULL",
            [$this->subscriberId]
        );
        
        $this->jsonSuccess(['mailboxes' => $mailboxes]);
    }
    
    /**
     * Get usage statistics
     */
    public function getStats()
    {
        $stats = [
            'mailboxes' => $this->db->fetch(
                "SELECT COUNT(*) as count FROM mail_mailboxes 
                 WHERE subscriber_id = ? AND deleted_at IS NULL",
                [$this->subscriberId]
            )['count'],
            'domains' => $this->db->fetch(
                "SELECT COUNT(*) as count FROM mail_domains 
                 WHERE subscriber_id = ? AND deleted_at IS NULL",
                [$this->subscriberId]
            )['count'],
            'messages' => $this->db->fetch(
                "SELECT COUNT(*) as count FROM mail_messages m
                 JOIN mail_mailboxes mb ON m.mailbox_id = mb.id
                 WHERE mb.subscriber_id = ?",
                [$this->subscriberId]
            )['count'],
            'storage_used' => $this->db->fetch(
                "SELECT SUM(storage_used) as total FROM mail_mailboxes 
                 WHERE subscriber_id = ?",
                [$this->subscriberId]
            )['total'] ?? 0,
        ];
        
        $this->jsonSuccess($stats);
    }
    
    /**
     * Check rate limit for API key
     */
    private function checkRateLimit($keyId)
    {
        // Get plan limits
        $limit = $this->db->fetch(
            "SELECT sp.api_rate_limit
             FROM mail_subscriptions sub
             JOIN mail_subscription_plans sp ON sub.plan_id = sp.id
             WHERE sub.subscriber_id = ?",
            [$this->subscriberId]
        );
        
        if (!$limit) {
            return true; // No limit set
        }
        
        // Count API calls in last hour
        $calls = $this->db->fetch(
            "SELECT COUNT(*) as count FROM mail_api_logs 
             WHERE api_key_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)",
            [$keyId]
        );
        
        // Log this API call
        $this->db->query(
            "INSERT INTO mail_api_logs (api_key_id, endpoint, method, ip_address, created_at)
             VALUES (?, ?, ?, ?, NOW())",
            [$keyId, $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'], 
             $_SERVER['REMOTE_ADDR']]
        );
        
        return $calls['count'] < $limit['api_rate_limit'];
    }
    
    private function jsonSuccess($data, $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $data,
            'timestamp' => time(),
        ]);
        exit;
    }
    
    private function jsonError($message, $code = 400)
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => $code,
            ],
            'timestamp' => time(),
        ]);
        exit;
    }
}
