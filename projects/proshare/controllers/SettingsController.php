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

class SettingsController
{
    /**
     * Show settings page
     */
    public function index(): void
    {
        $user = Auth::user();
        $db = Database::projectConnection('proshare');
        
        $settings = $db->fetch(
            "SELECT * FROM user_settings WHERE user_id = ?",
            [$user['id']]
        );
        
        if (!$settings) {
            $db->insert('user_settings', ['user_id' => $user['id']]);
            $settings = $db->fetch("SELECT * FROM user_settings WHERE user_id = ?", [$user['id']]);
        }
        
        // Get statistics
        $stats = [
            'total_files' => $db->fetchColumn("SELECT COUNT(*) FROM files WHERE user_id = ?", [$user['id']]),
            'total_texts' => $db->fetchColumn("SELECT COUNT(*) FROM text_shares WHERE user_id = ?", [$user['id']]),
            'total_downloads' => $db->fetchColumn("SELECT SUM(downloads) FROM files WHERE user_id = ?", [$user['id']]) ?: 0,
            'storage_used' => number_format($db->fetchColumn("SELECT SUM(size) FROM files WHERE user_id = ?", [$user['id']]) / 1024 / 1024, 2),
        ];
        
        View::render('projects/proshare/settings', [
            'title' => 'Settings',
            'subtitle' => 'Manage your preferences and account settings',
            'settings' => $settings,
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
        
        $db = Database::projectConnection('proshare');
        
        $emailNotifications = isset($_POST['email_notifications']) ? 1 : 0;
        $smsNotifications = isset($_POST['sms_notifications']) ? 1 : 0;
        $defaultExpiry = (int)($_POST['default_expiry'] ?? 24);
        $autoDelete = isset($_POST['auto_delete']) ? 1 : 0;
        $enableEncryption = isset($_POST['enable_encryption']) ? 1 : 0;
        $enableCompression = isset($_POST['enable_compression']) ? 1 : 0;
        $maxFileSize = (int)($_POST['max_file_size'] ?? 524288000);
        
        try {
            $updated = $db->update('user_settings', [
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
                $exists = $db->fetch("SELECT id FROM user_settings WHERE user_id = ?", [$user['id']]);
                if (!$exists) {
                    // Insert new settings record
                    $db->insert('user_settings', [
                        'user_id' => $user['id'],
                        'email_notifications' => $emailNotifications,
                        'sms_notifications' => $smsNotifications,
                        'default_expiry' => $defaultExpiry,
                        'auto_delete' => $autoDelete,
                        'enable_encryption' => $enableEncryption,
                        'enable_compression' => $enableCompression,
                        'max_file_size' => $maxFileSize,
                    ]);
                    echo json_encode(['success' => true, 'message' => 'Settings saved successfully']);
                } else {
                    // Settings exist but no changes were made
                    echo json_encode(['success' => true, 'message' => 'Settings saved successfully']);
                }
            } else {
                echo json_encode(['success' => true, 'message' => 'Settings updated successfully']);
            }
        } catch (\Exception $e) {
            error_log('Settings update failed: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to save settings']);
        }
    }
}
