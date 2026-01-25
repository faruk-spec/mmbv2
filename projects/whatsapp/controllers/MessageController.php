<?php
/**
 * WhatsApp Message Controller
 * 
 * @package MMB\Projects\WhatsApp\Controllers
 */

namespace WhatsApp\Controllers;

use Core\Auth;
use Core\View;
use Core\Database;
use Core\Security;

class MessageController
{
    private $db;
    private $user;
    
    public function __construct()
    {
        $this->user = Auth::user();
        $this->db = new Database();
    }
    
    /**
     * Display messages page
     */
    public function index()
    {
        $sessions = $this->getUserSessions();
        
        View::render('whatsapp/messages', [
            'user' => $this->user,
            'sessions' => $sessions,
            'pageTitle' => 'WhatsApp Messages'
        ]);
    }
    
    /**
     * Send a message
     */
    public function send()
    {
        header('Content-Type: application/json');
        
        try {
            // Validate CSRF token
            if (!Security::validateCSRF($_POST['csrf_token'] ?? '')) {
                throw new \Exception('Invalid CSRF token');
            }
            
            $sessionId = $_POST['session_id'] ?? null;
            $recipient = $_POST['recipient'] ?? null;
            $message = $_POST['message'] ?? null;
            $mediaUrl = $_POST['media_url'] ?? null;
            
            if (!$sessionId || !$recipient || !$message) {
                throw new \Exception('Missing required fields');
            }
            
            // Verify session ownership and status
            $stmt = $this->db->prepare("
                SELECT id, status FROM whatsapp_sessions 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$sessionId, $this->user['id']]);
            $session = $stmt->fetch();
            
            if (!$session) {
                throw new \Exception('Session not found or access denied');
            }
            
            if ($session['status'] !== 'connected') {
                throw new \Exception('Session is not connected');
            }
            
            // Save message to database
            $stmt = $this->db->prepare("
                INSERT INTO whatsapp_messages (
                    session_id, recipient, message, media_url, 
                    direction, status, created_at
                ) VALUES (?, ?, ?, ?, 'outgoing', 'pending', NOW())
            ");
            
            $stmt->execute([
                $sessionId,
                $recipient,
                $message,
                $mediaUrl
            ]);
            
            $messageId = $this->db->lastInsertId();
            
            // In production, this would send to WhatsApp Web API
            $sent = $this->sendToWhatsApp($sessionId, $recipient, $message, $mediaUrl);
            
            // Update status
            if ($sent) {
                $stmt = $this->db->prepare("
                    UPDATE whatsapp_messages 
                    SET status = 'sent', sent_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$messageId]);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Message sent successfully',
                'message_id' => $messageId
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
     * Get message history
     */
    public function history()
    {
        header('Content-Type: application/json');
        
        try {
            $sessionId = $_GET['session_id'] ?? null;
            $recipient = $_GET['recipient'] ?? null;
            $limit = $_GET['limit'] ?? 50;
            $offset = $_GET['offset'] ?? 0;
            
            if (!$sessionId) {
                throw new \Exception('Session ID required');
            }
            
            // Verify session ownership
            $stmt = $this->db->prepare("
                SELECT id FROM whatsapp_sessions 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$sessionId, $this->user['id']]);
            
            if (!$stmt->fetch()) {
                throw new \Exception('Session not found or access denied');
            }
            
            // Build query
            $query = "
                SELECT * FROM whatsapp_messages 
                WHERE session_id = ?
            ";
            $params = [$sessionId];
            
            if ($recipient) {
                $query .= " AND recipient = ?";
                $params[] = $recipient;
            }
            
            $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $params[] = (int)$limit;
            $params[] = (int)$offset;
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $messages = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'messages' => $messages
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
     * Get user's sessions
     */
    private function getUserSessions()
    {
        $stmt = $this->db->prepare("
            SELECT * FROM whatsapp_sessions 
            WHERE user_id = ? AND status = 'connected'
            ORDER BY created_at DESC
        ");
        $stmt->execute([$this->user['id']]);
        return $stmt->fetchAll();
    }
    
    /**
     * Send message to WhatsApp (placeholder)
     * In production, this would integrate with WhatsApp Web client
     */
    private function sendToWhatsApp($sessionId, $recipient, $message, $mediaUrl = null)
    {
        // Placeholder - would integrate with actual WhatsApp Web API
        // via Node.js bridge using whatsapp-web.js or similar
        return true;
    }
}
