<?php
/**
 * WhatsApp Session Controller
 * 
 * @package MMB\Projects\WhatsApp\Controllers
 */

namespace Projects\WhatsApp\Controllers;

use Core\Auth;
use Core\View;
use Core\Database;
use Core\Security;

class SessionController
{
    private $db;
    private $user;
    
    public function __construct()
    {
        $this->user = Auth::user();
        $this->db = Database::getInstance();
    }
    
    /**
     * Display sessions management page
     */
    public function index()
    {
        $sessions = $this->getUserSessions();
        
        View::render('whatsapp/sessions', [
            'user' => $this->user,
            'sessions' => $sessions,
            'pageTitle' => 'WhatsApp Sessions'
        ]);
    }
    
    /**
     * Create a new WhatsApp session
     */
    public function create()
    {
        header('Content-Type: application/json');
        
        try {
            // Validate CSRF token
            if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                throw new \Exception('Invalid CSRF token');
            }
            
            // Check session limit
            $sessionCount = $this->getSessionCount();
            $maxSessions = 5; // From config
            
            if ($sessionCount >= $maxSessions) {
                throw new \Exception("Maximum session limit ($maxSessions) reached");
            }
            
            // Generate session ID
            $sessionId = bin2hex(random_bytes(16));
            $sessionName = $_POST['session_name'] ?? 'WhatsApp Session ' . ($sessionCount + 1);
            
            // Insert session into database
            $this->db->query("
                INSERT INTO whatsapp_sessions (
                    user_id, session_id, session_name, status, created_at
                ) VALUES (?, ?, ?, 'initializing', NOW())
            ", [
                $this->user['id'],
                $sessionId,
                $sessionName
            ]);
            
            $insertId = $this->db->lastInsertId();
            
            // Generate QR code (this would integrate with WhatsApp Web API)
            $qrData = $this->generateQRCode($sessionId);
            
            echo json_encode([
                'success' => true,
                'message' => 'Session created successfully',
                'session_id' => $insertId,
                'qr_code' => $qrData
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
     * Disconnect a WhatsApp session
     */
    public function disconnect()
    {
        header('Content-Type: application/json');
        
        try {
            $sessionId = $_POST['session_id'] ?? null;
            
            if (!$sessionId) {
                throw new \Exception('Session ID required');
            }
            
            // Verify ownership
            $session = $this->db->fetch("
                SELECT id FROM whatsapp_sessions 
                WHERE id = ? AND user_id = ?
            ", [$sessionId, $this->user['id']]);
            
            if (!$session) {
                throw new \Exception('Session not found or access denied');
            }
            
            // Update session status
            $this->db->query("
                UPDATE whatsapp_sessions 
                SET status = 'disconnected', disconnected_at = NOW()
                WHERE id = ?
            ", [$sessionId]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Session disconnected successfully'
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
     * Get QR code for session
     */
    public function getQRCode()
    {
        header('Content-Type: application/json');
        
        try {
            $sessionId = $_GET['session_id'] ?? null;
            
            if (!$sessionId) {
                throw new \Exception('Session ID required');
            }
            
            // Verify ownership
            $session = $this->db->fetch("
                SELECT session_id, status FROM whatsapp_sessions 
                WHERE id = ? AND user_id = ?
            ", [$sessionId, $this->user['id']]);
            
            if (!$session) {
                throw new \Exception('Session not found or access denied');
            }
            
            // Generate fresh QR code
            $qrData = $this->generateQRCode($session['session_id']);
            
            echo json_encode([
                'success' => true,
                'qr_code' => $qrData,
                'status' => $session['status']
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
     * Get session status
     */
    public function status()
    {
        header('Content-Type: application/json');
        
        try {
            $sessionId = $_GET['session_id'] ?? null;
            
            if (!$sessionId) {
                throw new \Exception('Session ID required');
            }
            
            $session = $this->db->fetch("
                SELECT status, phone_number, session_name, created_at
                FROM whatsapp_sessions 
                WHERE id = ? AND user_id = ?
            ", [$sessionId, $this->user['id']]);
            
            if (!$session) {
                throw new \Exception('Session not found');
            }
            
            echo json_encode([
                'success' => true,
                'session' => $session
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
        return $this->db->fetchAll("
            SELECT * FROM whatsapp_sessions 
            WHERE user_id = ? 
            ORDER BY created_at DESC
        ", [$this->user['id']]);
    }
    
    /**
     * Get session count for user
     */
    private function getSessionCount()
    {
        return $this->db->fetchColumn("
            SELECT COUNT(*) FROM whatsapp_sessions 
            WHERE user_id = ? AND status != 'disconnected'
        ", [$this->user['id']]) ?? 0;
    }
    
    /**
     * Generate QR code for WhatsApp authentication
     * NOTE: This is a placeholder. In production, this would integrate
     * with a WhatsApp Web client library (e.g., whatsapp-web.js via Node.js bridge)
     */
    private function generateQRCode($sessionId)
    {
        // Placeholder QR code data
        // In production, this would generate actual WhatsApp Web QR code
        return [
            'data' => 'whatsapp://qr/' . $sessionId,
            'expires_at' => time() + 60 // 60 seconds
        ];
    }
}
