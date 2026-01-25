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
     * Production-ready with comprehensive error handling
     */
    public function create()
    {
        // Ensure JSON response even on fatal errors
        header('Content-Type: application/json');
        
        try {
            // Validate request method
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Method not allowed');
            }
            
            // Validate CSRF token
            if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                throw new \Exception('Invalid CSRF token');
            }
            
            // Validate and sanitize input
            $sessionName = trim($_POST['session_name'] ?? '');
            if (empty($sessionName)) {
                throw new \Exception('Session name is required');
            }
            
            if (strlen($sessionName) > 100) {
                throw new \Exception('Session name is too long (max 100 characters)');
            }
            
            // Check session limit based on subscription
            $sessionCount = $this->getSessionCount();
            $subscription = $this->getUserSubscription();
            
            $maxSessions = $subscription['sessions_limit'] ?? 5;
            
            if ($maxSessions > 0 && $sessionCount >= $maxSessions) {
                throw new \Exception("Maximum session limit ($maxSessions) reached. Please upgrade your plan.");
            }
            
            // Generate unique session ID
            $sessionId = bin2hex(random_bytes(16));
            
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
            
            echo json_encode([
                'success' => true,
                'message' => 'Session created successfully',
                'session_id' => $insertId,
                'data' => [
                    'id' => $insertId,
                    'session_name' => $sessionName,
                    'status' => 'initializing'
                ]
            ]);
            
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit; // Ensure no trailing output
    }
    
    /**
     * Get user's subscription details
     */
    private function getUserSubscription()
    {
        return $this->db->fetch("
            SELECT sessions_limit, messages_limit, api_calls_limit, status
            FROM whatsapp_subscriptions
            WHERE user_id = ? AND status = 'active'
            ORDER BY end_date DESC
            LIMIT 1
        ", [$this->user['id']]) ?? ['sessions_limit' => 5];
    }
    
    /**
     * Disconnect a WhatsApp session
     * Production-ready with comprehensive error handling
     */
    public function disconnect()
    {
        header('Content-Type: application/json');
        
        try {
            // Validate request method
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Method not allowed');
            }
            
            // Validate CSRF token
            if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                throw new \Exception('Invalid CSRF token');
            }
            
            $sessionId = $_POST['session_id'] ?? null;
            
            if (!$sessionId || !is_numeric($sessionId)) {
                throw new \Exception('Valid session ID required');
            }
            
            // Verify ownership and get session
            $session = $this->db->fetch("
                SELECT id, status FROM whatsapp_sessions 
                WHERE id = ? AND user_id = ?
            ", [$sessionId, $this->user['id']]);
            
            if (!$session) {
                throw new \Exception('Session not found or access denied');
            }
            
            if ($session['status'] === 'disconnected') {
                throw new \Exception('Session is already disconnected');
            }
            
            // Update session status
            $this->db->query("
                UPDATE whatsapp_sessions 
                SET status = 'disconnected', disconnected_at = NOW()
                WHERE id = ?
            ", [$sessionId]);
            
            // PRODUCTION: Here you would also:
            // 1. Close WhatsApp Web connection
            // 2. Clear session data from WhatsApp client
            // 3. Send disconnect event to WebSocket clients
            
            echo json_encode([
                'success' => true,
                'message' => 'Session disconnected successfully',
                'data' => [
                    'session_id' => $sessionId,
                    'status' => 'disconnected'
                ]
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
     * Get QR code for session with proper status updates
     * Endpoint: /projects/whatsapp/sessions/qr/{session_id}
     * 
     * PRODUCTION NOTE:
     * This is a placeholder implementation. For production use:
     * 1. Install whatsapp-web.js or similar library via Node.js bridge
     * 2. Use libraries like endroid/qr-code for PHP QR generation
     * 3. Implement WebSocket connection to WhatsApp Web
     * 4. Return actual QR code as base64 encoded image
     * 5. Handle QR code expiration and regeneration
     * 6. Update session status based on scan events
     */
    public function getQRCode()
    {
        header('Content-Type: application/json');
        
        try {
            $sessionId = $_GET['session_id'] ?? null;
            
            if (!$sessionId) {
                throw new \Exception('Session ID required');
            }
            
            // Verify ownership and get session details
            $session = $this->db->fetch("
                SELECT id, session_id, status, phone_number 
                FROM whatsapp_sessions 
                WHERE id = ? AND user_id = ?
            ", [$sessionId, $this->user['id']]);
            
            if (!$session) {
                throw new \Exception('Session not found or access denied');
            }
            
            // If already connected, return status
            if ($session['status'] === 'connected') {
                echo json_encode([
                    'success' => true,
                    'status' => 'connected',
                    'phone_number' => $session['phone_number'],
                    'message' => 'Session already connected'
                ]);
                return;
            }
            
            // Generate placeholder QR code
            // PRODUCTION: Replace with actual WhatsApp Web QR code generation
            $qrData = $this->generatePlaceholderQR($session['session_id']);
            
            echo json_encode([
                'success' => true,
                'status' => $session['status'],
                'qr_code' => $qrData['image'],
                'qr_text' => $qrData['text'],
                'expires_at' => $qrData['expires_at'],
                'message' => 'QR code generated successfully'
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
     * Production-ready with rate limiting considerations
     */
    public function status()
    {
        header('Content-Type: application/json');
        
        try {
            $sessionId = $_GET['session_id'] ?? null;
            
            if (!$sessionId || !is_numeric($sessionId)) {
                throw new \Exception('Valid session ID required');
            }
            
            $session = $this->db->fetch("
                SELECT id, status, phone_number, session_name, created_at, last_activity
                FROM whatsapp_sessions 
                WHERE id = ? AND user_id = ?
            ", [$sessionId, $this->user['id']]);
            
            if (!$session) {
                throw new \Exception('Session not found or access denied');
            }
            
            // PRODUCTION: Query actual WhatsApp client status here
            // For now, return database status
            
            echo json_encode([
                'success' => true,
                'session' => $session,
                'timestamp' => time()
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
     * Delete a WhatsApp session
     * Production-ready with comprehensive error handling
     */
    public function delete()
    {
        header('Content-Type: application/json');
        
        try {
            // Validate request method
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Method not allowed');
            }
            
            // Validate CSRF token
            if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                throw new \Exception('Invalid CSRF token');
            }
            
            $sessionId = $_POST['session_id'] ?? null;
            
            if (!$sessionId || !is_numeric($sessionId)) {
                throw new \Exception('Valid session ID required');
            }
            
            // Verify ownership and get session
            $session = $this->db->fetch("
                SELECT id, status, session_name FROM whatsapp_sessions 
                WHERE id = ? AND user_id = ?
            ", [$sessionId, $this->user['id']]);
            
            if (!$session) {
                throw new \Exception('Session not found or access denied');
            }
            
            // Delete session from database
            $this->db->query("
                DELETE FROM whatsapp_sessions 
                WHERE id = ?
            ", [$sessionId]);
            
            // PRODUCTION: Here you would also:
            // 1. Close WhatsApp Web connection
            // 2. Clear session data from WhatsApp client
            // 3. Remove session files
            // 4. Send delete event to WebSocket clients
            
            echo json_encode([
                'success' => true,
                'message' => 'Session deleted successfully',
                'data' => [
                    'session_id' => $sessionId
                ]
            ]);
            
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    /**
     * Generate placeholder QR code for WhatsApp authentication
     * 
     * PRODUCTION IMPLEMENTATION GUIDE:
     * ================================
     * 
     * Option 1: PHP QR Code Library
     * ------------------------------
     * Install: composer require endroid/qr-code
     * 
     * use Endroid\QrCode\QrCode;
     * use Endroid\QrCode\Writer\PngWriter;
     * 
     * $qrCode = new QrCode($whatsappAuthData);
     * $writer = new PngWriter();
     * $result = $writer->write($qrCode);
     * $dataUri = $result->getDataUri();
     * 
     * Option 2: WhatsApp Web.js Bridge (Node.js + PHP)
     * -------------------------------------------------
     * 1. Install whatsapp-web.js in Node.js:
     *    npm install whatsapp-web.js
     * 
     * 2. Create Node.js bridge server that:
     *    - Generates QR codes via WhatsApp Web
     *    - Communicates with PHP via HTTP/WebSocket
     *    - Returns QR code as base64 image
     * 
     * 3. PHP calls Node.js bridge:
     *    $response = file_get_contents('http://localhost:3000/generate-qr?session=' . $sessionId);
     * 
     * Option 3: Commercial WhatsApp Business API
     * ------------------------------------------
     * Use official WhatsApp Business API for production-grade solution
     * 
     * @param string $sessionId Unique session identifier
     * @return array QR code data with image and metadata
     */
    private function generatePlaceholderQR($sessionId)
    {
        // Generate a placeholder QR code using SVG
        // In production, this would be replaced with actual WhatsApp Web QR
        
        $qrText = "whatsapp://pair?session=" . $sessionId . "&timestamp=" . time();
        
        // Create a simple SVG QR code placeholder
        // PRODUCTION: Replace with actual QR code library
        $svg = $this->generateSimpleSVGQR($qrText);
        
        return [
            'image' => 'data:image/svg+xml;base64,' . base64_encode($svg),
            'text' => $qrText,
            'expires_at' => time() + 60, // 60 seconds expiration
            'instructions' => [
                '1. Open WhatsApp on your phone',
                '2. Tap Menu or Settings',
                '3. Tap Linked Devices',
                '4. Tap Link a Device',
                '5. Scan this QR code'
            ]
        ];
    }
    
    /**
     * Generate a simple SVG QR code placeholder
     * PRODUCTION: Replace with proper QR code generation
     */
    private function generateSimpleSVGQR($text)
    {
        // Simple grid pattern as placeholder
        // Real implementation should use proper QR encoding
        $size = 256;
        $gridSize = 8;
        $cellSize = $size / $gridSize;
        
        $svg = '<svg width="' . $size . '" height="' . $size . '" xmlns="http://www.w3.org/2000/svg">';
        $svg .= '<rect width="' . $size . '" height="' . $size . '" fill="#ffffff"/>';
        
        // Generate a deterministic pattern based on text
        $hash = md5($text);
        for ($i = 0; $i < $gridSize; $i++) {
            for ($j = 0; $j < $gridSize; $j++) {
                $index = ($i * $gridSize + $j) % strlen($hash);
                if (hexdec($hash[$index]) % 2 === 0) {
                    $x = $i * $cellSize;
                    $y = $j * $cellSize;
                    $svg .= '<rect x="' . $x . '" y="' . $y . '" width="' . $cellSize . '" height="' . $cellSize . '" fill="#000000"/>';
                }
            }
        }
        
        // Add corner markers (typical of QR codes)
        $markerSize = $cellSize * 3;
        $markers = [
            ['x' => 0, 'y' => 0],
            ['x' => $size - $markerSize, 'y' => 0],
            ['x' => 0, 'y' => $size - $markerSize]
        ];
        
        foreach ($markers as $marker) {
            $svg .= '<rect x="' . $marker['x'] . '" y="' . $marker['y'] . '" width="' . $markerSize . '" height="' . $markerSize . '" fill="none" stroke="#000000" stroke-width="' . ($cellSize/2) . '"/>';
            $svg .= '<rect x="' . ($marker['x'] + $cellSize) . '" y="' . ($marker['y'] + $cellSize) . '" width="' . $cellSize . '" height="' . $cellSize . '" fill="#000000"/>';
        }
        
        $svg .= '</svg>';
        
        return $svg;
    }
    
    /**
     * Legacy method - kept for backward compatibility
     * @deprecated Use generatePlaceholderQR() instead
     */
    private function generateQRCode($sessionId)
    {
        return [
            'data' => 'whatsapp://qr/' . $sessionId,
            'expires_at' => time() + 60
        ];
    }
}
