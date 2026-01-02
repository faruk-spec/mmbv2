<?php
/**
 * ProShare Text Share Controller
 * 
 * @package MMB\Projects\ProShare\Controllers
 */

namespace Projects\ProShare\Controllers;

use Core\Database;
use Core\View;
use Core\Auth;
use Core\Security;

class TextShareController
{
    /**
     * Show text share page
     */
    public function index(): void
    {
        $user = Auth::check() ? Auth::user() : null;
        
        View::render('projects/proshare/text-share', [
            'title' => 'Share Text',
            'subtitle' => 'Share text, code, or notes securely',
            'user' => $user,
        ]);
    }
    
    /**
     * Create text share
     */
    public function create(): void
    {
        header('Content-Type: application/json');
        
        try {
            $user = Auth::check() ? Auth::user() : null;
            $userId = $user ? $user['id'] : null;
            
            $title = Security::sanitize($_POST['title'] ?? '');
            $content = $_POST['content'] ?? '';
            
            if (empty($content)) {
                echo json_encode(['success' => false, 'error' => 'Content is required']);
                return;
            }
            
            if (strlen($content) > 1000000) { // 1MB limit
                echo json_encode(['success' => false, 'error' => 'Content too large (max 1MB)']);
                return;
            }
            
            $db = Database::projectConnection('proshare');
            
            // Generate unique short code
            $shortCode = $this->generateShortCode();
            
            $password = isset($_POST['password']) && !empty($_POST['password']) 
                ? Security::hashPassword($_POST['password']) 
                : null;
            
            $maxViews = isset($_POST['max_views']) && !empty($_POST['max_views']) 
                ? (int)$_POST['max_views'] 
                : null;
            
            // Handle expiry - check both form input and user settings
            $expiresAt = null;
            
            // Get user settings if logged in
            $userSettings = null;
            if ($userId) {
                $userSettings = $db->fetch(
                    "SELECT auto_delete, default_expiry FROM user_settings WHERE user_id = ?",
                    [$userId]
                );
            }
            
            // Check form input (text share form uses 'expiry' field)
            $formExpiry = isset($_POST['expiry']) ? (int)$_POST['expiry'] : null;
            
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
            
            $selfDestruct = isset($_POST['self_destruct']) ? 1 : 0;
            
            $textId = $db->insert('text_shares', [
                'user_id' => $userId,
                'short_code' => $shortCode,
                'title' => $title,
                'content' => $content,
                'password' => $password,
                'max_views' => $maxViews,
                'expires_at' => $expiresAt,
                'self_destruct' => $selfDestruct,
                'status' => 'active',
            ]);
            
            if ($textId) {
                // Log action
                $this->logAudit($userId, 'text_share_created', 'text', $textId, [
                    'title' => $title,
                    'length' => strlen($content)
                ]);
                
                // Build share URL - check if APP_URL is defined
                $baseUrl = defined('APP_URL') ? APP_URL : ($_SERVER['REQUEST_SCHEME'] ?? 'https') . '://' . ($_SERVER['HTTP_HOST'] ?? '');
                $shareUrl = $baseUrl . '/t/' . $shortCode;
                
                echo json_encode([
                    'success' => true,
                    'text_id' => $textId,
                    'short_code' => $shortCode,
                    'share_url' => $shareUrl,
                    'expires_at' => $expiresAt
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to create text share']);
            }
        } catch (\Exception $e) {
            error_log('Text share creation error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to create text share: ' . $e->getMessage()]);
        }
    }
    
    /**
     * View text share
     */
    public function view(string $shortcode): void
    {
        $db = Database::projectConnection('proshare');
        
        $text = $db->fetch(
            "SELECT * FROM text_shares WHERE short_code = ? AND status = 'active'",
            [$shortcode]
        );
        
        if (!$text) {
            http_response_code(404);
            echo "Text not found or has expired.";
            return;
        }
        
        // Check if text has expired
        if ($text['expires_at'] && strtotime($text['expires_at']) < time()) {
            $db->update('text_shares', ['status' => 'expired'], 'id = ?', [$text['id']]);
            
            // Create notification for user if logged in
            if ($text['user_id']) {
                $this->createNotification(
                    $text['user_id'],
                    'expiry_warning',
                    "Your text share '{$text['title']}' has expired",
                    $text['id']
                );
            }
            
            http_response_code(410);
            echo "This text has expired.";
            return;
        }
        
        // Check max views - BEFORE incrementing the counter
        if ($text['max_views'] && $text['views'] >= $text['max_views']) {
            $db->update('text_shares', ['status' => 'expired'], 'id = ?', [$text['id']]);
            
            // Create notification for user if logged in
            if ($text['user_id']) {
                $this->createNotification(
                    $text['user_id'],
                    'expiry_warning',
                    "Your text share '{$text['title']}' has reached its view limit",
                    $text['id']
                );
            }
            
            http_response_code(410);
            echo "View limit reached.";
            return;
        }
        
        // Check password if set
        if ($text['password']) {
            session_start();
            $sessionKey = 'proshare_text_auth_' . $shortcode;
            
            if (!isset($_SESSION[$sessionKey])) {
                // Show password form - use $shortcode consistently
                $this->showPasswordForm($shortcode, $text);
                return;
            }
        }
        
        // Increment view counter
        $db->query("UPDATE text_shares SET views = views + 1 WHERE id = ?", [$text['id']]);
        
        // Log view
        $this->logAudit($text['user_id'], 'text_view', 'text', $text['id'], [
            'short_code' => $shortcode
        ]);
        
        // Self-destruct ONLY if enabled (check flag is 1)
        if ($text['self_destruct'] == 1) {
            $db->update('text_shares', ['status' => 'deleted'], 'id = ?', [$text['id']]);
            
            // Create notification for user if logged in
            if ($text['user_id']) {
                $this->createNotification(
                    $text['user_id'],
                    'security_alert',
                    "Your text share '{$text['title']}' was automatically deleted after viewing",
                    $text['id']
                );
            }
        }
        
        // Display text
        View::render('projects/proshare/text-view', [
            'text' => $text,
        ]);
    }
    
    /**
     * Verify password for protected text
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
        $text = $db->fetch(
            "SELECT * FROM text_shares WHERE short_code = ? AND status = 'active'",
            [$shortCode]
        );
        
        if (!$text || !$text['password']) {
            echo json_encode(['success' => false, 'error' => 'Invalid request']);
            return;
        }
        
        if (Security::verifyPassword($password, $text['password'])) {
            session_start();
            $_SESSION['proshare_text_auth_' . $shortCode] = true;
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Incorrect password']);
        }
    }
    
    /**
     * Show password form
     */
    private function showPasswordForm(string $shortCode, array $text): void
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
                .text-info {
                    background: #0f0f23;
                    padding: 15px;
                    border-radius: 8px;
                    margin-bottom: 25px;
                }
                .text-title { color: #00f0ff; font-weight: bold; }
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
                <div class="text-info">
                    <div class="text-title"><?= htmlspecialchars($text['title'] ?: 'Shared Text') ?></div>
                </div>
                <p style="color: #888; margin-bottom: 20px;">This content is password protected. Please enter the password to continue.</p>
                <form id="passwordForm">
                    <input type="password" id="password" placeholder="Enter password" required autofocus>
                    <button type="submit" class="btn">Unlock & View</button>
                </form>
                <div class="error" id="error"></div>
            </div>
            
            <script>
                document.getElementById('passwordForm').addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const password = document.getElementById('password').value;
                    const error = document.getElementById('error');
                    
                    try {
                        const response = await fetch('/projects/proshare/text/verify-password', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `short_code=<?= urlencode($shortCode) ?>&password=${encodeURIComponent(password)}`
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            window.location.reload();
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
        $db = Database::projectConnection('proshare');
        $exists = $db->fetch("SELECT id FROM text_shares WHERE short_code = ?", [$code]);
        
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
     * Create notification
     */
    private function createNotification(int $userId, string $type, string $message, ?int $relatedId = null): void
    {
        $db = Database::projectConnection('proshare');
        
        $db->insert('notifications', [
            'user_id' => $userId,
            'type' => $type,
            'message' => $message,
            'related_id' => $relatedId,
        ]);
    }
}
