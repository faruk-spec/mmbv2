<?php
/**
 * Admin Settings Controller
 * 
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;
use Core\Security;
use Core\Auth;
use Core\Logger;
use Core\ActivityLogger;

class SettingsController extends BaseController
{
    public function __construct()
    {
        $this->requireAuth();
        $this->requirePermissionGroup('settings');
    }
    
    /**
     * Settings page
     */
    public function index(): void
    {
        $this->requirePermission('settings');
        $db = Database::getInstance();
        
        // Get current settings
        $settings = $db->fetchAll("SELECT * FROM settings");
        $settingsMap = [];
        foreach ($settings as $setting) {
            $settingsMap[$setting['key']] = $setting['value'];
        }
        
        $this->view('admin/settings/index', [
            'title' => 'Site Settings',
            'settings' => $settingsMap
        ]);
    }
    
    /**
     * Update settings
     */
    public function update(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/settings');
            return;
        }
        
        try {
            $db = Database::getInstance();
            
            $settingsToUpdate = [
                'site_name',
                'site_description',
                'contact_email',
                'maintenance_mode',
                'registration_enabled',
                'system_timezone',
                'date_format',
                'time_format',
                'auth_tagline',
                'auth_logo',
            ];

            // Snapshot current values before writing
            $oldValues = [];
            $newValues = [];
            foreach ($settingsToUpdate as $key) {
                $row = $db->fetch("SELECT value FROM settings WHERE `key` = ?", [$key]);
                $oldValues[$key] = $row ? $row['value'] : null;
            }
            
            foreach ($settingsToUpdate as $key) {
                $value = $this->input($key, '');
                
                // Check if setting exists
                $existing = $db->fetch("SELECT id FROM settings WHERE `key` = ?", [$key]);
                
                if ($existing) {
                    $db->update('settings', [
                        'value' => Security::sanitize($value),
                        'updated_at' => date('Y-m-d H:i:s')
                    ], '`key` = ?', [$key]);
                } else {
                    $db->insert('settings', [
                        'key' => $key,
                        'value' => Security::sanitize($value),
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
                $newValues[$key] = Security::sanitize($value);
            }
            
            // Update timezone in app config if changed
            if ($this->input('system_timezone')) {
                date_default_timezone_set($this->input('system_timezone'));
            }

            // Only log keys that actually changed
            $changedOld = [];
            $changedNew = [];
            foreach ($settingsToUpdate as $key) {
                if ($oldValues[$key] !== $newValues[$key]) {
                    $changedOld[$key] = $oldValues[$key];
                    $changedNew[$key] = $newValues[$key];
                }
            }

            ActivityLogger::logUpdate(
                Auth::id(),
                'settings',
                'settings',
                0,
                $changedOld,
                $changedNew
            );
            
            $this->flash('success', 'Settings updated successfully.');
            
        } catch (\Exception $e) {
            Logger::error('Settings update error: ' . $e->getMessage());
            $this->flash('error', 'Failed to update settings.');
        }
        
        $this->redirect('/admin/settings');
    }

    /**
     * Upload auth logo image
     */
    public function uploadLogo(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/settings');
            return;
        }

        try {
            if (empty($_FILES['auth_logo_file']) || $_FILES['auth_logo_file']['error'] !== UPLOAD_ERR_OK) {
                $uploadError = $_FILES['auth_logo_file']['error'] ?? UPLOAD_ERR_NO_FILE;
                $msg = $uploadError === UPLOAD_ERR_NO_FILE ? 'No file was selected.' : 'File upload failed (error code ' . $uploadError . ').';
                $this->flash('error', $msg);
                $this->redirect('/admin/settings');
                return;
            }

            $file = $_FILES['auth_logo_file'];

            // Enforce 2 MB size limit
            $maxBytes = 2 * 1024 * 1024;
            if ($file['size'] > $maxBytes) {
                $this->flash('error', 'File is too large. Maximum allowed size is 2 MB.');
                $this->redirect('/admin/settings');
                return;
            }

            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {
                $this->flash('error', 'Invalid file type. Allowed: JPG, PNG, GIF, WebP.');
                $this->redirect('/admin/settings');
                return;
            }

            // Validate it is actually an image
            if (!@getimagesize($file['tmp_name'])) {
                $this->flash('error', 'Uploaded file is not a valid image.');
                $this->redirect('/admin/settings');
                return;
            }

            $uploadDir = BASE_PATH . '/storage/uploads/oauth';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filename = 'auth-logo-' . uniqid() . '.' . $ext;
            $destPath = $uploadDir . '/' . $filename;

            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                $this->flash('error', 'Failed to save uploaded file.');
                $this->redirect('/admin/settings');
                return;
            }

            $webPath = '/uploads/oauth/' . $filename;

            $db = Database::getInstance();

            // Delete old logo file if it was uploaded via this feature
            $existing = $db->fetch("SELECT value FROM settings WHERE `key` = 'auth_logo'");
            if ($existing && !empty($existing['value'])) {
                $oldPath = BASE_PATH . '/storage' . $existing['value'];
                if (
                    strpos($existing['value'], '/uploads/oauth/') === 0
                    && file_exists($oldPath)
                ) {
                    @unlink($oldPath);
                }
            }

            $row = $db->fetch("SELECT id FROM settings WHERE `key` = 'auth_logo'");
            if ($row) {
                $db->update('settings', [
                    'value'      => $webPath,
                    'updated_at' => date('Y-m-d H:i:s'),
                ], '`key` = ?', ['auth_logo']);
            } else {
                $db->insert('settings', [
                    'key'        => 'auth_logo',
                    'value'      => $webPath,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            ActivityLogger::logUpdate(Auth::id(), 'settings', 'settings', 0, [], ['auth_logo' => $webPath]);
            $this->flash('success', 'Auth logo uploaded successfully.');

        } catch (\Exception $e) {
            Logger::error('Logo upload error: ' . $e->getMessage());
            $this->flash('error', 'Failed to upload logo.');
        }

        $this->redirect('/admin/settings');
    }

    /**
     * Delete auth logo image
     */
    public function deleteLogo(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/settings');
            return;
        }

        try {
            $db = Database::getInstance();
            $existing = $db->fetch("SELECT value FROM settings WHERE `key` = 'auth_logo'");

            if ($existing && !empty($existing['value'])) {
                // Only delete file if it lives in our managed uploads folder
                if (strpos($existing['value'], '/uploads/oauth/') === 0) {
                    $filePath = BASE_PATH . '/storage' . $existing['value'];
                    if (file_exists($filePath)) {
                        @unlink($filePath);
                    }
                }
                $db->update('settings', [
                    'value'      => '',
                    'updated_at' => date('Y-m-d H:i:s'),
                ], '`key` = ?', ['auth_logo']);
            }

            ActivityLogger::logUpdate(Auth::id(), 'settings', 'settings', 0, ['auth_logo' => $existing['value'] ?? ''], ['auth_logo' => '']);
            $this->flash('success', 'Auth logo removed.');

        } catch (\Exception $e) {
            Logger::error('Logo delete error: ' . $e->getMessage());
            $this->flash('error', 'Failed to remove logo.');
        }

        $this->redirect('/admin/settings');
    }
    
    /**
     * Maintenance mode page
     */
    public function maintenance(): void
    {
        $db = Database::getInstance();
        
        $setting = $db->fetch("SELECT value FROM settings WHERE `key` = 'maintenance_mode'");
        $maintenanceMode = $setting ? $setting['value'] === '1' : false;
        
        // Get maintenance settings
        $maintenanceSettings = [
            'title' => $db->fetch("SELECT value FROM settings WHERE `key` = 'maintenance_title'"),
            'message' => $db->fetch("SELECT value FROM settings WHERE `key` = 'maintenance_message'"),
            'custom_html' => $db->fetch("SELECT value FROM settings WHERE `key` = 'maintenance_custom_html'"),
            'show_countdown' => $db->fetch("SELECT value FROM settings WHERE `key` = 'maintenance_show_countdown'"),
            'end_time' => $db->fetch("SELECT value FROM settings WHERE `key` = 'maintenance_end_time'"),
            'contact_email' => $db->fetch("SELECT value FROM settings WHERE `key` = 'maintenance_contact_email'"),
        ];
        
        $this->view('admin/settings/maintenance', [
            'title' => 'Maintenance Mode',
            'maintenanceMode' => $maintenanceMode,
            'maintenanceTitle' => $maintenanceSettings['title']['value'] ?? 'We\'ll Be Back Soon!',
            'maintenanceMessage' => $maintenanceSettings['message']['value'] ?? 'We\'re currently performing scheduled maintenance to improve your experience. Please check back in a few minutes.',
            'maintenanceCustomHtml' => $maintenanceSettings['custom_html']['value'] ?? '',
            'showCountdown' => ($maintenanceSettings['show_countdown']['value'] ?? '0') === '1',
            'endTime' => $maintenanceSettings['end_time']['value'] ?? '',
            'contactEmail' => $maintenanceSettings['contact_email']['value'] ?? ''
        ]);
    }
    
    /**
     * Toggle maintenance mode
     */
    public function toggleMaintenance(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/settings/maintenance');
            return;
        }
        
        try {
            $db = Database::getInstance();
            
            $current = $db->fetch("SELECT value FROM settings WHERE `key` = 'maintenance_mode'");
            $oldValue = ($current && $current['value'] === '1') ? '1' : '0';
            $newValue = $oldValue === '1' ? '0' : '1';
            
            if ($current) {
                $db->update('settings', [
                    'value' => $newValue,
                    'updated_at' => date('Y-m-d H:i:s')
                ], "`key` = 'maintenance_mode'", []);
            } else {
                $db->insert('settings', [
                    'key' => 'maintenance_mode',
                    'value' => $newValue,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            ActivityLogger::logUpdate(
                Auth::id(),
                'settings',
                'settings',
                0,
                ['maintenance_mode' => $oldValue === '1' ? 'enabled' : 'disabled'],
                ['maintenance_mode' => $newValue === '1' ? 'enabled' : 'disabled']
            );
            
            $status = $newValue === '1' ? 'enabled' : 'disabled';
            $this->flash('success', "Maintenance mode {$status}.");
            
        } catch (\Exception $e) {
            Logger::error('Maintenance toggle error: ' . $e->getMessage());
            $this->flash('error', 'Failed to toggle maintenance mode.');
        }
        
        $this->redirect('/admin/settings/maintenance');
    }
    
    /**
     * Update maintenance settings
     */
    public function updateMaintenanceSettings(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/settings/maintenance');
            return;
        }
        
        try {
            $db = Database::getInstance();
            
            // Get values with appropriate sanitization
            $title = $this->input('maintenance_title', 'We\'ll Be Back Soon!');
            $message = $this->input('maintenance_message', '');
            $customHtml = $this->input('maintenance_custom_html', '');
            $showCountdown = $this->input('show_countdown') ? '1' : '0';
            $endTime = $this->input('end_time', '');
            $contactEmail = $this->input('contact_email', '');
            
            $settingsToUpdate = [
                'maintenance_title' => $title,
                // Allow full HTML in message with safe tag whitelist
                'maintenance_message' => strip_tags($message, '<p><br><b><strong><i><em><u><ul><ol><li><span><div><h1><h2><h3><h4><h5><h6><a><img><table><tr><td><th><thead><tbody><hr><blockquote><code><pre>'),
                // Allow full HTML template (no restrictions except for dangerous scripts)
                'maintenance_custom_html' => str_replace(['<script', '</script>', 'javascript:', 'onerror=', 'onclick='], '', $customHtml),
                'maintenance_show_countdown' => $showCountdown,
                'maintenance_end_time' => $endTime,
                'maintenance_contact_email' => filter_var($contactEmail, FILTER_VALIDATE_EMAIL) ? $contactEmail : ''
            ];
            
            foreach ($settingsToUpdate as $key => $value) {
                // Use INSERT ... ON DUPLICATE KEY UPDATE for better reliability
                $result = $db->query(
                    "INSERT INTO settings (`key`, `value`, `created_at`, `updated_at`) 
                     VALUES (?, ?, NOW(), NOW()) 
                     ON DUPLICATE KEY UPDATE `value` = ?, `updated_at` = NOW()",
                    [$key, $value, $value]
                );
                
                if (!$result) {
                    throw new \Exception("Failed to save setting: {$key}");
                }
            }
            
            Logger::activity(Auth::id(), 'maintenance_settings_updated');
            
            $this->flash('success', 'Maintenance settings updated successfully.');
            
        } catch (\Exception $e) {
            Logger::error('Maintenance settings update error: ' . $e->getMessage());
            $this->flash('error', 'Failed to update maintenance settings: ' . $e->getMessage());
        }
        
        $this->redirect('/admin/settings/maintenance');
    }
    
    /**
     * Feature Flags Management
     */
    public function features(): void
    {
        $db = Database::getInstance();
        
        // Get all feature flags
        $features = $db->fetchAll("SELECT * FROM feature_flags ORDER BY feature_name");
        
        $this->view('admin/settings/features', [
            'title' => 'Feature Flags',
            'features' => $features
        ]);
    }
    
    /**
     * Toggle feature flag
     */
    public function toggleFeature(): void
    {
        if (!$this->validateCsrf() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request']);
            return;
        }
        
        try {
            $db = Database::getInstance();
            $featureId = $this->input('feature_id');
            
            $feature = $db->fetch("SELECT * FROM feature_flags WHERE id = ?", [$featureId]);
            
            if (!$feature) {
                $this->json(['success' => false, 'message' => 'Feature not found']);
                return;
            }
            
            $newStatus = $feature['is_enabled'] ? 0 : 1;
            
            $db->update('feature_flags', [
                'is_enabled' => $newStatus,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$featureId]);
            
            $this->json([
                'success' => true,
                'message' => 'Feature ' . ($newStatus ? 'enabled' : 'disabled'),
                'is_enabled' => $newStatus
            ]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * Session settings page
     */
    public function session(): void
    {
        $db = Database::getInstance();
        
        // Get current settings
        $settings = $db->fetchAll("SELECT * FROM settings");
        $settingsMap = [];
        foreach ($settings as $setting) {
            $settingsMap[$setting['key']] = $setting['value'];
        }
        
        // Get session statistics
        $stats = [
            'active_sessions' => $db->fetch("SELECT COUNT(*) as count FROM user_sessions WHERE is_active = 1")['count'] ?? 0,
            'sessions_today' => $db->fetch("SELECT COUNT(*) as count FROM user_sessions WHERE DATE(created_at) = CURDATE()")['count'] ?? 0,
            'expired_sessions' => $db->fetch("SELECT COUNT(*) as count FROM user_sessions WHERE is_active = 0 AND expires_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)")['count'] ?? 0,
        ];
        
        // Calculate average session duration
        $avgDuration = $db->fetch("SELECT AVG(TIMESTAMPDIFF(MINUTE, created_at, last_activity_at)) as avg_minutes FROM user_sessions WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $stats['avg_duration'] = $avgDuration && $avgDuration['avg_minutes'] ? round($avgDuration['avg_minutes']) . ' min' : 'N/A';

        $activeSessions = $db->fetchAll(
            "SELECT us.user_id, u.name as user_name, u.email, COUNT(*) as session_count 
             FROM user_sessions us 
             LEFT JOIN users u ON us.user_id = u.id 
             WHERE us.is_active = 1 AND us.expires_at > NOW()
             GROUP BY us.user_id, u.name, u.email
             ORDER BY session_count DESC
             LIMIT 50"
        );
        
        $this->view('admin/settings/session', [
            'title' => 'Session & Security Settings',
            'settings' => $settingsMap,
            'stats' => $stats,
            'activeSessions' => $activeSessions,
        ]);
    }
    
    /**
     * Update session settings
     */
    public function updateSession(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/settings/session');
            return;
        }
        
        try {
            $db = Database::getInstance();
            
            $settingsToUpdate = [
                'default_session_timeout' => $this->input('default_session_timeout', '120'),
                'remember_me_duration' => $this->input('remember_me_duration', '30'),
                'max_concurrent_sessions' => $this->input('max_concurrent_sessions', '5'),
                'auto_logout_enabled' => $this->input('auto_logout_enabled') === '1' ? '1' : '0',
                'session_ip_validation' => $this->input('session_ip_validation') === '1' ? '1' : '0',
            ];
            
            foreach ($settingsToUpdate as $key => $value) {
                // Check if setting exists
                $existing = $db->fetch("SELECT id FROM settings WHERE `key` = ?", [$key]);
                
                if ($existing) {
                    $db->update('settings', [
                        'value' => Security::sanitize($value),
                        'updated_at' => date('Y-m-d H:i:s')
                    ], '`key` = ?', [$key]);
                } else {
                    $db->insert('settings', [
                        'key' => $key,
                        'value' => Security::sanitize($value),
                        'type' => 'string',
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
            
            Logger::activity(Auth::id(), 'session_settings_updated');
            
            $this->flash('success', 'Session settings updated successfully.');
            
        } catch (\Exception $e) {
            Logger::error('Session settings update error: ' . $e->getMessage());
            $this->flash('error', 'Failed to update session settings: ' . $e->getMessage());
        }
        
        $this->redirect('/admin/settings/session');
    }
    
    /**
     * Update security policy settings
     */
    public function updateSecurityPolicy(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/settings/session');
            return;
        }
        
        try {
            $db = Database::getInstance();
            
            $settingsToUpdate = [
                'max_failed_login_attempts' => $this->input('max_failed_login_attempts', '5'),
                'account_lockout_duration' => $this->input('account_lockout_duration', '15'),
                'password_min_length' => $this->input('password_min_length', '8'),
                'require_email_verification' => $this->input('require_email_verification') === '1' ? '1' : '0',
                'force_password_change' => $this->input('force_password_change') === '1' ? '1' : '0',
            ];
            
            foreach ($settingsToUpdate as $key => $value) {
                // Check if setting exists
                $existing = $db->fetch("SELECT id FROM settings WHERE `key` = ?", [$key]);
                
                if ($existing) {
                    $db->update('settings', [
                        'value' => Security::sanitize($value),
                        'updated_at' => date('Y-m-d H:i:s')
                    ], '`key` = ?', [$key]);
                } else {
                    $db->insert('settings', [
                        'key' => $key,
                        'value' => Security::sanitize($value),
                        'type' => 'string',
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
            
            Logger::activity(Auth::id(), 'security_policy_updated');
            
            $this->flash('success', 'Security policies updated successfully.');
            
        } catch (\Exception $e) {
            Logger::error('Security policy update error: ' . $e->getMessage());
            $this->flash('error', 'Failed to update security policies: ' . $e->getMessage());
        }
        
        $this->redirect('/admin/settings/session');
    }

    public function forceLogoutAll(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/settings/session');
            return;
        }

        try {
            sleep(1);
            $db = Database::getInstance();
            $currentSessionId = session_id();
            $stmt = $db->query(
                "UPDATE user_sessions SET is_active = 0, last_activity_at = NOW() WHERE session_id != ? AND is_active = 1",
                [$currentSessionId]
            );
            $affected = $stmt->rowCount();
            ActivityLogger::log(Auth::id(), 'force_logout_all', ['module' => 'sessions', 'entity_name' => 'All users', 'new_values' => ['affected' => $affected]]);
            $this->flash('success', "Force logout complete. {$affected} session(s) were terminated.");
        } catch (\Exception $e) {
            $this->flash('error', 'Failed to force logout: ' . $e->getMessage());
        }

        $this->redirect('/admin/settings/session');
    }


    public function forceLogoutUser(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/settings/session');
            return;
        }

        $targetUserId = (int)$this->input('user_id');
        if ($targetUserId <= 0) {
            $this->flash('error', 'Please select a user.');
            $this->redirect('/admin/settings/session');
            return;
        }

        if ($targetUserId === Auth::id()) {
            $this->flash('error', 'You cannot force logout yourself.');
            $this->redirect('/admin/settings/session');
            return;
        }

        try {
            sleep(1);
            $db = Database::getInstance();
            $stmt = $db->query(
                "UPDATE user_sessions SET is_active = 0, last_activity_at = NOW() WHERE user_id = ? AND is_active = 1",
                [$targetUserId]
            );
            $affected = $stmt->rowCount();

            $targetUser = $db->fetch("SELECT name, email FROM users WHERE id = ?", [$targetUserId]);
            $userName = $targetUser ? ($targetUser['name'] ?: $targetUser['email']) : 'User #' . $targetUserId;

            ActivityLogger::log(Auth::id(), 'force_logout_user', [
                'module' => 'sessions',
                'entity_name' => $userName,
                'resource_type' => 'user',
                'resource_id' => $targetUserId,
                'new_values' => ['affected_sessions' => $affected]
            ]);

            $this->flash('success', "Logged out {$affected} session(s) for {$userName}.");
        } catch (\Exception $e) {
            $this->flash('error', 'Failed to force logout user: ' . $e->getMessage());
        }

        $this->redirect('/admin/settings/session');
    }
}
