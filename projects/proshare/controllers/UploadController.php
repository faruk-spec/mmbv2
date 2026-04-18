<?php
/**
 * ProShare Enhanced File Upload Controller
 * 
 * @package MMB\Projects\ProShare\Controllers
 */

namespace Projects\ProShare\Controllers;

use Core\Database;
use Core\View;
use Core\Auth;
use Core\Security;
use Core\Helpers;
use Core\ActivityLogger;

class UploadController
{
    private const MAX_FILE_SIZE = 524288000; // 500MB
    private const ALLOWED_TYPES = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'application/pdf', 'application/zip', 'application/x-rar-compressed',
        'text/plain', 'text/csv',
        'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'video/mp4', 'video/mpeg', 'audio/mpeg', 'audio/wav'
    ];
    
    /**
     * Show upload page
     */
    public function index(): void
    {
        $user = Auth::check() ? Auth::user() : null;
        $db = Database::getInstance();
        
        // Get user settings if logged in
        $settings = null;
        if ($user) {
            $settings = $db->fetch(
                "SELECT * FROM proshare_user_settings WHERE user_id = ?",
                [$user['id']]
            );
            
            if (!$settings) {
                $db->insert('proshare_user_settings', ['user_id' => $user['id']]);
                $settings = $db->fetch("SELECT * FROM proshare_user_settings WHERE user_id = ?", [$user['id']]);
            }
        }

        // Fetch global ProShare settings (default_self_destruct, etc.)
        $globalSettingsRows = $db->fetchAll("SELECT `key`, `value` FROM proshare_settings");
        $globalSettings = [];
        foreach ($globalSettingsRows as $row) {
            $globalSettings[$row['key']] = $row['value'];
        }
        
        View::render('projects/proshare/upload', [
            'title' => 'Upload Files',
            'subtitle' => 'Share files securely with password protection and expiry options',
            'user' => $user,
            'settings' => $settings,
            'globalSettings' => $globalSettings,
            'maxSize' => (int)($globalSettings['max_file_size'] ?? self::MAX_FILE_SIZE),
        ]);
    }
    
    /**
     * Handle file upload (supports anonymous)
     */
    public function upload(): void
    {
        header('Content-Type: application/json');
        
        try {
            $user = Auth::check() ? Auth::user() : null;
            $userId = $user ? $user['id'] : null;
            
            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'error' => 'Please select a valid file']);
                return;
            }
            
            $file = $_FILES['file'];

            // Get admin-configured max file size
            $db = Database::getInstance();
            $adminMaxSizeRow = $db->fetch("SELECT `value` FROM proshare_settings WHERE `key` = 'max_file_size'");
            $maxFileSize = $adminMaxSizeRow ? (int)$adminMaxSizeRow['value'] : self::MAX_FILE_SIZE;
            
            // Validate file size
            if ($file['size'] > $maxFileSize) {
                $limitMb = round($maxFileSize / 1048576);
                echo json_encode(['success' => false, 'error' => "File size exceeds the {$limitMb} MB upload limit"]);
                return;
            }
            
            // Validate file type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, self::ALLOWED_TYPES)) {
                echo json_encode(['success' => false, 'error' => 'File type not allowed']);
                return;
            }
            
            // Generate unique short code
            $shortCode = $this->generateShortCode();
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $storedFilename = $shortCode . '_' . time() . '.' . strtolower($ext);
            
            // Create upload directory
            $uploadDir = BASE_PATH . '/storage/uploads/proshare/' . date('Y/m');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $destination = $uploadDir . '/' . $storedFilename;
            
            // Optional compression - accept both 'compression' and 'enable_compression'
            $enableCompression = !empty($_POST['enable_compression']) || !empty($_POST['compression']);
            $isCompressed = false;
            
            // Calculate checksum for integrity (before possible compression)
            $checksum = hash_file('sha256', $file['tmp_name']);
            
            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                echo json_encode(['success' => false, 'error' => 'Failed to save file']);
                return;
            }
            
            // Apply gzip compression after saving (skip for already-compressed types)
            $skipCompressionTypes = ['image/jpeg','image/png','image/gif','image/webp','video/mp4','video/webm','audio/mpeg','audio/ogg','application/zip','application/x-rar-compressed','application/gzip'];
            if ($enableCompression && !in_array($mimeType, $skipCompressionTypes)) {
                $original = file_get_contents($destination);
                $compressed = gzencode($original, 6);
                if ($compressed !== false && strlen($compressed) < strlen($original)) {
                    file_put_contents($destination, $compressed);
                    $isCompressed = true;
                }
            }
            
            // Store in database
            $db = Database::getInstance();
            
            $password = isset($_POST['password']) && !empty($_POST['password']) 
                ? Security::hashPassword($_POST['password']) 
                : null;
            
            $maxDownloads = isset($_POST['max_downloads']) && !empty($_POST['max_downloads']) 
                ? (int)$_POST['max_downloads'] 
                : null;
            
            // Handle expiry - check both form input and user settings
            $expiresAt = null;
            
            // Get user settings if logged in
            $userSettings = null;
            if ($userId) {
                $userSettings = $db->fetch(
                    "SELECT auto_delete, default_expiry FROM proshare_user_settings WHERE user_id = ?",
                    [$userId]
                );
            }
            
            // Check form input first (accept both 'expiry' and 'expiry_hours')
            $formExpiry = isset($_POST['expiry_hours']) ? (int)$_POST['expiry_hours'] : 
                         (isset($_POST['expiry']) ? (int)$_POST['expiry'] : null);
            
            if ($formExpiry !== null && $formExpiry > 0) {
                // User explicitly set expiry in form (not "Never" which is 0)
                $expiresAt = date('Y-m-d H:i:s', strtotime("+{$formExpiry} hours"));
            } elseif ($formExpiry === 0) {
                // User explicitly selected "Never" (0) - no expiry
                $expiresAt = null;
            } elseif (!$userId) {
                // Anonymous users: default to 24 hours for security
                $expiresAt = date('Y-m-d H:i:s', strtotime("+24 hours"));
            } elseif ($userSettings && $userSettings['auto_delete'] == 1) {
                // Logged-in user with auto_delete enabled: use their default expiry
                $expiryHours = $userSettings['default_expiry'] ?? 24;
                $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expiryHours} hours"));
            }
            // else: logged-in user without auto_delete enabled and no form expiry = null (no expiry)
            
            $selfDestruct = (!empty($_POST['self_destruct']) && $_POST['self_destruct'] == '1') ? 1 : 0;
            
            $fileId = $db->insert('proshare_files', [
                'user_id' => $userId,
                'short_code' => $shortCode,
                'original_name' => $file['name'],
                'filename' => $storedFilename,
                'path' => $destination,
                'size' => $file['size'],
                'mime_type' => $mimeType,
                'password' => $password,
                'max_downloads' => $maxDownloads,
                'expires_at' => $expiresAt,
                'self_destruct' => $selfDestruct,
                'is_encrypted' => 0,
                'encryption_key' => null,
                'is_compressed' => $isCompressed ? 1 : 0,
                'checksum' => $checksum,
                'status' => 'active',
            ]);
            
            if ($fileId) {
                // Log action
                $this->logAudit($userId, 'file_upload', 'file', $fileId, [
                    'filename' => $file['name'],
                    'size' => $file['size']
                ]);
                try { ActivityLogger::logCreate($userId, 'proshare', 'file', $fileId ?? null, ['filename' => $file['name'] ?? null, 'size' => $file['size'] ?? null]); } catch (\Throwable $_) {}
                
                // Create backup if enabled
                if ($user) {
                    $this->createBackup($fileId, $destination, $file['size']);
                }
                
                // Build share URL - check if APP_URL is defined
                $baseUrl = defined('APP_URL') ? APP_URL : ($_SERVER['REQUEST_SCHEME'] ?? 'https') . '://' . ($_SERVER['HTTP_HOST'] ?? '');
                $shareUrl = $baseUrl . '/s/' . $shortCode;
                
                echo json_encode([
                    'success' => true,
                    'file_id' => $fileId,
                    'short_code' => $shortCode,
                    'share_url' => $shareUrl,
                    'share_link' => $shareUrl, // For backward compatibility with frontend
                    'expires_at' => $expiresAt
                ]);
            } else {
                @unlink($destination);
                echo json_encode(['success' => false, 'error' => 'Failed to save file info']);
            }
        } catch (\Exception $e) {
            error_log('Upload error: ' . $e->getMessage());
            try { ActivityLogger::logFailure($userId ?? null, 'file_upload', $e->getMessage()); } catch (\Throwable $_) {}
            echo json_encode(['success' => false, 'error' => 'Upload failed: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Generate unique short code
     */
    private function generateShortCode(int $length = 8): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        // Check if code already exists
        $db = Database::getInstance();
        $exists = $db->fetch("SELECT id FROM proshare_files WHERE short_code = ?", [$code]);
        
        if ($exists) {
            return $this->generateShortCode($length);
        }
        
        return $code;
    }
    
    /**
     * Log audit trail (logs to both audit_logs and activity_logs)
     */
    private function logAudit(?int $userId, string $action, string $resourceType, ?int $resourceId, array $details = []): void
    {
        $db = Database::getInstance();
        
        // Log to audit_logs (with JSON details)
        $db->insert('proshare_audit_logs', [
            'user_id' => $userId,
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'ip_address' => Security::getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'details' => json_encode($details),
        ]);
        
        // Also log to activity_logs (for admin activity tracking)
        $description = !empty($details) ? json_encode($details) : null;
        $db->insert('proshare_activity_logs', [
            'user_id' => $userId,
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'description' => $description,
            'ip_address' => Security::getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
    }
    
    /**
     * Create backup
     */
    private function createBackup(int $fileId, string $filePath, int $fileSize): void
    {
        $db = Database::getInstance();
        
        $backupDir = BASE_PATH . '/storage/backups/proshare/' . date('Y/m');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        $backupPath = $backupDir . '/' . basename($filePath);
        
        if (copy($filePath, $backupPath)) {
            $db->insert('proshare_backups', [
                'file_id' => $fileId,
                'backup_path' => $backupPath,
                'backup_size' => $fileSize,
            ]);
        }
    }
}
