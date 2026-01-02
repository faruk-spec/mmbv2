<?php
/**
 * ProShare Download Controller
 * 
 * @package MMB\Projects\ProShare\Controllers
 */

namespace Projects\ProShare\Controllers;

use Core\Database;
use Core\Auth;
use Core\Security;
use Core\View;

class DownloadController
{
    /**
     * Download file by short code
     */
    public function download(string $shortcode): void
    {
        $db = Database::projectConnection('proshare');
        
        // Get file info
        $file = $db->fetch(
            "SELECT * FROM files WHERE short_code = ? AND status = 'active'",
            [$shortcode]
        );
        
        if (!$file) {
            http_response_code(404);
            echo "File not found or has expired.";
            return;
        }
        
        // Check if file has expired
        if ($file['expires_at'] && strtotime($file['expires_at']) < time()) {
            $db->update('files', ['status' => 'expired'], 'id = ?', [$file['id']]);
            
            // Create notification for user if logged in
            if ($file['user_id']) {
                $this->sendNotification(
                    $file['user_id'],
                    'expiry_warning',
                    "Your file '{$file['original_name']}' has expired",
                    $file['id']
                );
            }
            
            http_response_code(410);
            echo "This file has expired.";
            return;
        }
        
        // Check max downloads - BEFORE incrementing the counter
        if ($file['max_downloads'] && $file['downloads'] >= $file['max_downloads']) {
            $db->update('files', ['status' => 'expired'], 'id = ?', [$file['id']]);
            
            // Create notification for user if logged in
            if ($file['user_id']) {
                $this->sendNotification(
                    $file['user_id'],
                    'expiry_warning',
                    "Your file '{$file['original_name']}' has reached its download limit",
                    $file['id']
                );
            }
            
            http_response_code(410);
            echo "Download limit reached.";
            return;
        }
        
        // Check password if set
        if ($file['password']) {
            session_start();
            $sessionKey = 'proshare_auth_' . $shortcode;
            
            if (!isset($_SESSION[$sessionKey])) {
                // Show password form
                $this->showPasswordForm($shortcode, $file);
                return;
            }
        }
        
        // Verify file integrity
        if ($file['checksum'] && file_exists($file['path'])) {
            $currentChecksum = hash_file('sha256', $file['path']);
            if ($currentChecksum !== $file['checksum']) {
                http_response_code(500);
                echo "File integrity check failed. File may be corrupted.";
                
                // Log integrity failure
                $this->logAudit(null, 'integrity_failure', 'file', $file['id'], [
                    'expected' => $file['checksum'],
                    'actual' => $currentChecksum
                ]);
                return;
            }
        }
        
        // Check if file exists
        if (!file_exists($file['path'])) {
            http_response_code(404);
            echo "File not found on server.";
            return;
        }
        
        // Track download
        $db->insert('file_downloads', [
            'file_id' => $file['id'],
            'ip_address' => Security::getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'referer' => $_SERVER['HTTP_REFERER'] ?? null,
        ]);
        
        // Increment download counter
        $db->query("UPDATE files SET downloads = downloads + 1 WHERE id = ?", [$file['id']]);
        
        // Log download
        $this->logAudit($file['user_id'], 'file_download', 'file', $file['id'], [
            'short_code' => $shortcode,
            'filename' => $file['original_name']
        ]);
        
        // Send notification if user is logged in
        if ($file['user_id']) {
            $this->sendNotification($file['user_id'], 'download', 
                "Your file '{$file['original_name']}' was downloaded.", $file['id']);
        }
        
        // Self-destruct ONLY if enabled (check flag is 1)
        if ($file['self_destruct'] == 1) {
            $db->update('files', ['status' => 'deleted'], 'id = ?', [$file['id']]);
            
            // Create notification for user if logged in
            if ($file['user_id']) {
                $this->sendNotification(
                    $file['user_id'],
                    'security_alert',
                    "Your file '{$file['original_name']}' was automatically deleted after download",
                    $file['id']
                );
            }
            
            @unlink($file['path']);
        }
        
        // Serve file
        header('Content-Type: ' . $file['mime_type']);
        header('Content-Disposition: attachment; filename="' . $file['original_name'] . '"');
        header('Content-Length: ' . $file['size']);
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');
        
        readfile($file['path']);
        exit;
    }
    
    /**
     * Verify password for protected file
     */
    public function verifyPassword(): void
    {
        header('Content-Type: application/json');
        
        $shortCode = $_POST['short_code'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (!$shortCode || !$password) {
            echo json_encode(['success' => false, 'error' => 'Short code and password required']);
            return;
        }
        
        $db = Database::projectConnection('proshare');
        $file = $db->fetch(
            "SELECT * FROM files WHERE short_code = ? AND status = 'active'",
            [$shortCode]
        );
        
        if (!$file || !$file['password']) {
            echo json_encode(['success' => false, 'error' => 'Invalid request']);
            return;
        }
        
        if (Security::verifyPassword($password, $file['password'])) {
            session_start();
            $_SESSION['proshare_auth_' . $shortCode] = true;
            echo json_encode(['success' => true]);
        } else {
            // Log failed attempt
            $this->logAudit(null, 'failed_password_attempt', 'file', $file['id'], [
                'short_code' => $shortCode
            ]);
            echo json_encode(['success' => false, 'error' => 'Incorrect password']);
        }
    }
    
    /**
     * Show password form
     */
    private function showPasswordForm(string $shortCode, array $file): void
    {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Password Protected - ProShare</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    background: #0f0f23;
                    color: #fff;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                    padding: 20px;
                }
                .password-container {
                    background: #1a1a2e;
                    border: 2px solid #00f0ff;
                    border-radius: 12px;
                    padding: 40px;
                    max-width: 500px;
                    width: 100%;
                    text-align: center;
                }
                h1 { color: #00f0ff; margin-bottom: 20px; }
                .file-info {
                    background: #0f0f23;
                    padding: 15px;
                    border-radius: 8px;
                    margin-bottom: 25px;
                }
                .file-name { color: #00f0ff; font-weight: bold; margin-bottom: 5px; }
                .file-size { color: #888; font-size: 0.9em; }
                input[type="password"] {
                    width: 100%;
                    padding: 15px;
                    background: #0f0f23;
                    color: #fff;
                    border: 1px solid #00f0ff;
                    border-radius: 4px;
                    font-size: 1em;
                    margin-bottom: 20px;
                }
                .btn {
                    background: #00f0ff;
                    color: #0f0f23;
                    border: none;
                    padding: 15px 30px;
                    border-radius: 4px;
                    cursor: pointer;
                    font-weight: bold;
                    font-size: 1.1em;
                    width: 100%;
                    transition: all 0.3s;
                }
                .btn:hover { background: #00d4dd; transform: translateY(-2px); }
                .error { color: #f44; margin-top: 15px; display: none; }
                .error.show { display: block; }
            </style>
        </head>
        <body>
            <div class="password-container">
                <h1>🔒 Password Protected</h1>
                <div class="file-info">
                    <div class="file-name"><?= htmlspecialchars($file['original_name']) ?></div>
                    <div class="file-size"><?= round($file['size'] / 1024 / 1024, 2) ?> MB</div>
                </div>
                <p style="color: #888; margin-bottom: 20px;">This file is password protected. Please enter the password to continue.</p>
                <form id="passwordForm">
                    <input type="password" id="password" placeholder="Enter password" required autofocus>
                    <button type="submit" class="btn">Unlock & Download</button>
                </form>
                <div class="error" id="error"></div>
            </div>
            
            <script>
                document.getElementById('passwordForm').addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const password = document.getElementById('password').value;
                    const error = document.getElementById('error');
                    
                    try {
                        const response = await fetch('/projects/proshare/verify-password', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `short_code=<?= urlencode($shortCode) ?>&password=${encodeURIComponent(password)}`
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            window.location.href = '/projects/proshare/download/<?= $shortCode ?>';
                        } else {
                            error.textContent = data.error || 'Incorrect password';
                            error.classList.add('show');
                        }
                    } catch (err) {
                        error.textContent = 'An error occurred. Please try again.';
                        error.classList.add('show');
                    }
                });
            </script>
        </body>
        </html>
        <?php
    }
    
    /**
     * Log audit trail (logs to both audit_logs and activity_logs)
     */
    private function logAudit(?int $userId, string $action, string $resourceType, ?int $resourceId, array $details = []): void
    {
        $db = Database::projectConnection('proshare');
        
        // Log to audit_logs (with JSON details)
        $db->insert('audit_logs', [
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
        $db->insert('activity_logs', [
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
     * Send notification
     */
    private function sendNotification(int $userId, string $type, string $message, ?int $relatedId = null): void
    {
        $db = Database::projectConnection('proshare');
        
        $db->insert('notifications', [
            'user_id' => $userId,
            'type' => $type,
            'message' => $message,
            'related_id' => $relatedId,
        ]);
    }
    
    /**
     * Preview file (view file info and download option)
     */
    public function preview(string $shortcode): void
    {
        $db = Database::projectConnection('proshare');
        
        // Get file info
        $file = $db->fetch(
            "SELECT * FROM files WHERE short_code = ? AND status = 'active'",
            [$shortcode]
        );
        
        if (!$file) {
            http_response_code(404);
            View::render('errors/404', [
                'title' => 'File Not Found',
                'message' => 'File not found or has expired.'
            ]);
            return;
        }
        
        // Check if file has expired
        if ($file['expires_at'] && strtotime($file['expires_at']) < time()) {
            $db->update('files', ['status' => 'expired'], 'id = ?', [$file['id']]);
            
            // Create notification for user if logged in
            if ($file['user_id']) {
                $this->sendNotification(
                    $file['user_id'],
                    'expiry_warning',
                    "Your file '{$file['original_name']}' has expired",
                    $file['id']
                );
            }
            
            http_response_code(410);
            View::render('errors/404', [
                'title' => 'File Expired',
                'message' => 'This file has expired.'
            ]);
            return;
        }
        
        // Check max downloads
        if ($file['max_downloads'] && $file['downloads'] >= $file['max_downloads']) {
            $db->update('files', ['status' => 'expired'], 'id = ?', [$file['id']]);
            
            // Create notification for user if logged in
            if ($file['user_id']) {
                $this->sendNotification(
                    $file['user_id'],
                    'expiry_warning',
                    "Your file '{$file['original_name']}' has reached its download limit",
                    $file['id']
                );
            }
            
            http_response_code(410);
            View::render('errors/404', [
                'title' => 'Download Limit Reached',
                'message' => 'Download limit reached for this file.'
            ]);
            return;
        }
        
        // Check password if set
        if ($file['password']) {
            session_start();
            $sessionKey = 'proshare_auth_' . $shortcode;
            
            if (!isset($_SESSION[$sessionKey])) {
                // Show password form
                $this->showPasswordForm($shortcode, $file);
                return;
            }
        }
        
        // Calculate file size in human readable format
        $fileSize = $this->formatBytes($file['size']);
        
        // Render preview page
        View::render('projects/proshare/file-preview', [
            'title' => 'File Preview',
            'subtitle' => $file['original_name'],
            'file' => $file,
            'fileSize' => $fileSize,
            'shortcode' => $shortcode,
        ]);
    }
    
    /**
     * Format bytes to human readable size
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
