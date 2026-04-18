<?php
/**
 * ProShare Settings Controller
 * 
 * @package MMB\Projects\ProShare\Controllers
 */

namespace Projects\ProShare\Controllers;

use Core\Database;
use Core\View;
use Core\Auth;
use Core\Security;
use Core\ActivityLogger;

class SettingsController
{
    /**
     * Show settings page
     */
    public function index(): void
    {
        $user = Auth::user();
        $db = Database::getInstance();
        
        $settings = $db->fetch(
            "SELECT * FROM proshare_user_settings WHERE user_id = ?",
            [$user['id']]
        );
        
        if (!$settings) {
            $db->insert('proshare_user_settings', ['user_id' => $user['id']]);
            $settings = $db->fetch("SELECT * FROM proshare_user_settings WHERE user_id = ?", [$user['id']]);
        }

        // Get global admin settings
        $globalSettingsRows = $db->fetchAll("SELECT `key`, `value` FROM proshare_settings");
        $globalSettings = [];
        foreach ($globalSettingsRows as $row) {
            $globalSettings[$row['key']] = $row['value'];
        }

        // Apply admin defaults to user settings on first load (fill nulls with admin defaults)
        if (empty($settings['default_expiry'])) {
            $settings['default_expiry'] = (int)($globalSettings['default_expiry_hours'] ?? 24);
        }
        if (!isset($settings['auto_delete'])) {
            $settings['auto_delete'] = (int)($globalSettings['default_auto_delete'] ?? 0);
        }
        
        // Get statistics
        $stats = [
            'total_files'     => (int)($db->fetch("SELECT COUNT(*) as c FROM proshare_files WHERE user_id = ?", [$user['id']])['c'] ?? 0),
            'total_texts'     => (int)($db->fetch("SELECT COUNT(*) as c FROM proshare_text_shares WHERE user_id = ?", [$user['id']])['c'] ?? 0),
            'total_downloads' => (int)($db->fetch("SELECT COALESCE(SUM(downloads),0) as c FROM proshare_files WHERE user_id = ?", [$user['id']])['c'] ?? 0),
            'storage_used'    => round(($db->fetch("SELECT COALESCE(SUM(size),0) as c FROM proshare_files WHERE user_id = ?", [$user['id']])['c'] ?? 0) / 1024 / 1024, 2),
        ];
        
        View::render('projects/proshare/settings', [
            'title' => 'Settings',
            'subtitle' => 'Manage your preferences and account settings',
            'settings' => $settings,
            'globalSettings' => $globalSettings,
            'stats' => $stats,
        ]);
    }
    
    /**
     * Update settings
     */
    public function update(): void
    {
        header('Content-Type: application/json');
        
        $user = Auth::user();
        if (!$user) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            return;
        }
        
        $db = Database::getInstance();
        
        $emailNotifications = isset($_POST['email_notifications']) ? 1 : 0;
        $smsNotifications = isset($_POST['sms_notifications']) ? 1 : 0;
        $defaultExpiry = (int)($_POST['default_expiry'] ?? 24);
        $autoDelete = isset($_POST['auto_delete']) ? 1 : 0;
        $enableEncryption = isset($_POST['enable_encryption']) ? 1 : 0;
        $enableCompression = isset($_POST['enable_compression']) ? 1 : 0;
        $maxFileSize = (int)($_POST['max_file_size'] ?? 524288000);
        
        try {
            $updated = $db->update('proshare_user_settings', [
                'email_notifications' => $emailNotifications,
                'sms_notifications' => $smsNotifications,
                'default_expiry' => $defaultExpiry,
                'auto_delete' => $autoDelete,
                'enable_encryption' => $enableEncryption,
                'enable_compression' => $enableCompression,
                'max_file_size' => $maxFileSize,
            ], 'user_id = ?', [$user['id']]);
            
            // If no rows were updated, settings may not exist yet - try insert
            if ($updated === 0) {
                // Check if record exists
                $exists = $db->fetch("SELECT id FROM proshare_user_settings WHERE user_id = ?", [$user['id']]);
                if (!$exists) {
                    // Insert new settings record
                    $db->insert('proshare_user_settings', [
                        'user_id' => $user['id'],
                        'email_notifications' => $emailNotifications,
                        'sms_notifications' => $smsNotifications,
                        'default_expiry' => $defaultExpiry,
                        'auto_delete' => $autoDelete,
                        'enable_encryption' => $enableEncryption,
                        'enable_compression' => $enableCompression,
                        'max_file_size' => $maxFileSize,
                    ]);
                    try { ActivityLogger::logUpdate($user['id'], 'proshare', 'settings', $user['id'] ?? null, [], ['email_notifications' => $emailNotifications, 'sms_notifications' => $smsNotifications, 'default_expiry' => $defaultExpiry, 'auto_delete' => $autoDelete, 'enable_encryption' => $enableEncryption, 'enable_compression' => $enableCompression, 'max_file_size' => $maxFileSize]); } catch (\Throwable $_) {}
                    echo json_encode(['success' => true, 'message' => 'Settings saved successfully']);
                } else {
                    // Settings exist but no changes were made
                    echo json_encode(['success' => true, 'message' => 'Settings saved successfully']);
                }
            } else {
                try { ActivityLogger::logUpdate($user['id'], 'proshare', 'settings', $user['id'] ?? null, [], ['email_notifications' => $emailNotifications, 'sms_notifications' => $smsNotifications, 'default_expiry' => $defaultExpiry, 'auto_delete' => $autoDelete, 'enable_encryption' => $enableEncryption, 'enable_compression' => $enableCompression, 'max_file_size' => $maxFileSize]); } catch (\Throwable $_) {}
                echo json_encode(['success' => true, 'message' => 'Settings updated successfully']);
            }
        } catch (\Exception $e) {
            error_log('Settings update failed: ' . $e->getMessage());
            try { ActivityLogger::logFailure($user['id'] ?? null, 'settings_update', $e->getMessage()); } catch (\Throwable $_) {}
            echo json_encode(['success' => false, 'error' => 'Failed to save settings']);
        }
    }
}
