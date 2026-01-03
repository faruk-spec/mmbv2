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

class SettingsController extends BaseController
{
    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
    }
    
    /**
     * Settings page
     */
    public function index(): void
    {
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
                'time_format'
            ];
            
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
            }
            
            // Update timezone in app config if changed
            if ($this->input('system_timezone')) {
                date_default_timezone_set($this->input('system_timezone'));
            }
            
            Logger::activity(Auth::id(), 'settings_updated');
            
            $this->flash('success', 'Settings updated successfully.');
            
        } catch (\Exception $e) {
            Logger::error('Settings update error: ' . $e->getMessage());
            $this->flash('error', 'Failed to update settings.');
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
            $newValue = ($current && $current['value'] === '1') ? '0' : '1';
            
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
            
            Logger::activity(Auth::id(), 'maintenance_mode_toggled', ['enabled' => $newValue === '1']);
            
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
}
