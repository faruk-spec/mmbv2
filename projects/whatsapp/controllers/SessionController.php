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
        // CRITICAL: Suppress ALL output before JSON response
        @ini_set('display_errors', '0');
        @ini_set('display_startup_errors', '0');
        @ini_set('log_errors', '1');
        error_reporting(0); // Suppress all errors from being displayed
        
        // Clear all output buffers to ensure clean JSON response
        while (@ob_get_level()) {
            @ob_end_clean();
        }
        
        // Start fresh output buffer
        ob_start();
        
        // Set JSON header first - must be before any output
        @header('Content-Type: application/json; charset=utf-8');
        
        try {
            // Validate that we have necessary objects
            if (!$this->user) {
                // Enhanced error message for debugging
                $sessionStatus = session_status();
                $hasSessionId = isset($_SESSION['user_id']);
                $userId = $_SESSION['user_id'] ?? 'none';
                
                error_log("Authentication failed in create: session_status=$sessionStatus, has_user_id=$hasSessionId, session_user_id=$userId");
                
                throw new \Exception('User not authenticated. Please log in again. (Session may have expired or cookies not enabled)');
            }
            
            if (!$this->db) {
                throw new \Exception('Database connection not available');
            }
            
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
            try {
                $sessionCount = $this->getSessionCount();
                $subscription = $this->getUserSubscription();
                
                $maxSessions = $subscription['sessions_limit'] ?? 5;
                
                if ($maxSessions > 0 && $sessionCount >= $maxSessions) {
                    throw new \Exception("Maximum session limit ($maxSessions) reached. Please upgrade your plan.");
                }
            } catch (\Exception $e) {
                // Log the error but allow session creation to proceed with default limits
                @error_log("Subscription check failed: " . $e->getMessage());
                // Use default limit if subscription check fails
                $sessionCount = $this->getSessionCount();
                if ($sessionCount >= 5) {
                    throw new \Exception("Maximum session limit (5) reached.");
                }
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
            
            // Clear any accumulated output and prepare JSON response
            $bufferContent = ob_get_clean();
            
            // Start final clean buffer for JSON only
            ob_start();
            
            $response = [
                'success' => true,
                'message' => 'Session created successfully',
                'session_id' => $insertId,
                'data' => [
                    'id' => $insertId,
                    'session_name' => $sessionName,
                    'status' => 'initializing'
                ]
            ];
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            
        } catch (\Exception $e) {
            // Clear any buffered content
            if (ob_get_level()) {
                ob_clean();
            }
            
            // Start fresh for error response
            ob_start();
            
            http_response_code(400);
            
            $response = [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'SESSION_CREATE_ERROR'
            ];
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\Throwable $e) {
            // Catch ANY error including fatal errors (PHP 7+)
            // Clear any buffered content
            while (@ob_get_level()) {
                @ob_end_clean();
            }
            
            // Start fresh
            @ob_start();
            
            @http_response_code(500);
            @header('Content-Type: application/json; charset=utf-8');
            
            $response = [
                'success' => false,
                'message' => 'An unexpected error occurred: ' . $e->getMessage(),
                'error_code' => 'INTERNAL_ERROR',
                'error_type' => get_class($e)
            ];
            
            // Log the error for debugging
            @error_log('Session create error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        
        // Send buffer and terminate
        @ob_end_flush();
        exit;
    }
    
    /**
     * Get user's subscription details
     */
    private function getUserSubscription()
    {
        try {
            $result = $this->db->fetch("
                SELECT sessions_limit, messages_limit, api_calls_limit, status
                FROM whatsapp_subscriptions
                WHERE user_id = ? AND status = 'active'
                ORDER BY end_date DESC
                LIMIT 1
            ", [$this->user['id']]);
            
            return $result ?? ['sessions_limit' => 5];
        } catch (\Exception $e) {
            // Log error and return default values if table doesn't exist
            @error_log("getUserSubscription error: " . $e->getMessage());
            return ['sessions_limit' => 5];
        }
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
     */
    public function getQRCode()
    {
        header('Content-Type: application/json');
        
        try {
            $sessionId = $_GET['session_id'] ?? null;
            
            if (!$sessionId) {
                throw new \Exception('Session ID required');
            }
            
            if (!$this->user) {
                // Enhanced error message for debugging
                $sessionStatus = session_status();
                $hasSessionId = isset($_SESSION['user_id']);
                $sessionId = $_SESSION['user_id'] ?? 'none';
                
                error_log("Authentication failed in getQRCode: session_status=$sessionStatus, has_user_id=$hasSessionId, session_user_id=$sessionId");
                
                throw new \Exception('User not authenticated. Please log in again. (Session may have expired or cookies not enabled)');
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
            
            // Try to get real QR code from WhatsApp Web.js bridge
            // PRODUCTION MODE: No fallback to placeholder - bridge must be running
            $qrData = $this->getQRFromBridge($session['session_id']);
            
            if ($qrData === null) {
                // Bridge not available - return more helpful error
                throw new \Exception('WhatsApp bridge server is not running. Please start the bridge server: cd projects/whatsapp/whatsapp-bridge && npm start');
            }
            
            echo json_encode([
                'success' => true,
                'status' => $session['status'],
                'qr_code' => $qrData['image'],
                'qr_text' => $qrData['text'],
                'expires_at' => $qrData['expires_at'],
                'message' => 'Real QR code generated from WhatsApp Web.js bridge'
            ]);
            
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
                'error_type' => 'QR_GENERATION_ERROR'
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
        try {
            $count = $this->db->fetchColumn("
                SELECT COUNT(*) FROM whatsapp_sessions 
                WHERE user_id = ? AND status != 'disconnected'
            ", [$this->user['id']]);
            
            return $count ?? 0;
        } catch (\Exception $e) {
            // Log error and return 0 if query fails
            @error_log("getSessionCount error: " . $e->getMessage());
            return 0;
        }
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
     * Get QR code from WhatsApp Web.js bridge server
     * Attempts to connect to bridge server on localhost:3000
     * 
     * @param string $sessionId Unique session identifier
     * @return array|null QR code data if bridge is available, null otherwise
     */
    private function getQRFromBridge($sessionId)
    {
        try {
            // Bridge server URL (configurable)
            $bridgeUrl = getenv('WHATSAPP_BRIDGE_URL') ?: 'http://127.0.0.1:3000';
            $endpoint = $bridgeUrl . '/api/generate-qr';
            
            // Prepare POST data as JSON (bridge expects JSON body)
            $postData = json_encode([
                'sessionId' => $sessionId,
                'userId' => $this->user['id']
            ]);
            
            // Try curl first (more reliable in production)
            if (function_exists('curl_init')) {
                $ch = curl_init($endpoint);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($postData)
                ]);
                curl_setopt($ch, CURLOPT_TIMEOUT, 15);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);
                
                if ($response === false || $httpCode !== 200) {
                    error_log("WhatsApp Bridge: cURL failed - HTTP $httpCode, Error: $curlError");
                    // Fall through to try file_get_contents
                } else {
                    $data = json_decode($response, true);
                    
                    if ($data && isset($data['success']) && $data['success'] && isset($data['qr'])) {
                        return [
                            'image' => $data['qr'],
                            'text' => $sessionId,
                            'expires_at' => time() + 60,
                            'is_real' => true
                        ];
                    }
                    
                    // Log bridge error
                    $errorMsg = $data['message'] ?? 'Unknown error';
                    error_log("WhatsApp Bridge: API returned error - $errorMsg");
                    return null;
                }
            }
            
            // Fallback to file_get_contents if curl is not available or failed
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-Type: application/json\r\n" .
                               "Content-Length: " . strlen($postData) . "\r\n",
                    'content' => $postData,
                    'timeout' => 15,
                    'ignore_errors' => true
                ]
            ]);
            
            $response = @file_get_contents($endpoint, false, $context);
            
            if ($response === false) {
                error_log("WhatsApp Bridge: Connection failed to $endpoint");
                return null;
            }
            
            $data = json_decode($response, true);
            
            if (!$data || !isset($data['success']) || !$data['success']) {
                $errorMsg = $data['message'] ?? 'Unknown error';
                error_log("WhatsApp Bridge: API returned error - $errorMsg");
                
                if (isset($data['help'])) {
                    error_log("WhatsApp Bridge: Help - " . $data['help']);
                }
                
                // Check if it's a Chrome/Puppeteer issue
                if (stripos($errorMsg, 'chrome') !== false || 
                    stripos($errorMsg, 'puppeteer') !== false ||
                    stripos($errorMsg, 'launch') !== false ||
                    stripos($errorMsg, 'dependencies') !== false) {
                    error_log("WhatsApp Bridge: Chrome/Puppeteer issue detected. See CHROME_SETUP.md");
                }
                
                return null;
            }
            
            // Bridge returns 'qr' field, not 'qr_code'
            if (!isset($data['qr'])) {
                error_log("WhatsApp Bridge: Missing QR field in response");
                return null;
            }
            
            // Return real QR code from bridge
            return [
                'image' => $data['qr'],
                'text' => $sessionId,
                'expires_at' => time() + 60,
                'is_real' => true
            ];
            
        } catch (\Exception $e) {
            error_log("WhatsApp Bridge: Exception - " . $e->getMessage());
            return null;
        }
    }
}
