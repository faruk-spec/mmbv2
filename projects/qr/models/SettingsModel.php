<?php
/**
 * Settings Model
 * Handles database operations for user settings
 * 
 * @package MMB\Projects\QR\Models
 */

namespace Projects\QR\Models;

use Core\Database;

class SettingsModel
{
    private Database $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get user settings
     * 
     * @param int $userId User ID
     * @return array Settings
     */
    public function get(int $userId): array
    {
        $sql = "SELECT * FROM qr_user_settings WHERE user_id = ?";
        
        try {
            $result = $this->db->query($sql, [$userId])->fetch();
            
            // If no settings exist, return defaults
            if (!$result) {
                return $this->getDefaults();
            }
            
            return $result;
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to get user settings: ' . $e->getMessage());
            return $this->getDefaults();
        }
    }
    
    /**
     * Get default settings
     * 
     * @return array Default settings
     */
    private function getDefaults(): array
    {
        return [
            'default_size' => 300,
            'default_foreground_color' => '#000000',
            'default_background_color' => '#ffffff',
            'default_error_correction' => 'H',
            'default_frame_style' => 'none',
            'default_download_format' => 'png',
            'auto_save' => 1,
            'email_notifications' => 0,
            'scan_notification_threshold' => 10,
            'api_enabled' => 0,
            'api_rate_limit' => 100
        ];
    }
    
    /**
     * Save or update user settings
     * 
     * @param int $userId User ID
     * @param array $data Settings data
     * @return bool Success status
     */
    public function save(int $userId, array $data): bool
    {
        // Check if settings exist
        $existing = $this->get($userId);
        
        if (isset($existing['id'])) {
            return $this->update($userId, $data);
        } else {
            return $this->create($userId, $data);
        }
    }
    
    /**
     * Create new user settings
     * 
     * @param int $userId User ID
     * @param array $data Settings data
     * @return bool Success status
     */
    private function create(int $userId, array $data): bool
    {
        $sql = "INSERT INTO qr_user_settings (
            user_id, default_size, default_foreground_color, 
            default_background_color, default_error_correction, 
            default_frame_style, default_download_format,
            auto_save, email_notifications, scan_notification_threshold,
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $params = [
            $userId,
            $data['default_size'] ?? 300,
            $data['default_foreground_color'] ?? '#000000',
            $data['default_background_color'] ?? '#ffffff',
            $data['default_error_correction'] ?? 'H',
            $data['default_frame_style'] ?? 'none',
            $data['default_download_format'] ?? 'png',
            $data['auto_save'] ?? 1,
            $data['email_notifications'] ?? 0,
            $data['scan_notification_threshold'] ?? 10
        ];
        
        try {
            $this->db->query($sql, $params);
            return true;
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to create user settings: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update user settings
     * 
     * @param int $userId User ID
     * @param array $data Settings data
     * @return bool Success status
     */
    private function update(int $userId, array $data): bool
    {
        $sql = "UPDATE qr_user_settings SET
            default_size = ?,
            default_foreground_color = ?,
            default_background_color = ?,
            default_error_correction = ?,
            default_frame_style = ?,
            default_download_format = ?,
            auto_save = ?,
            email_notifications = ?,
            scan_notification_threshold = ?,
            updated_at = NOW()
            WHERE user_id = ?";
        
        $params = [
            $data['default_size'] ?? 300,
            $data['default_foreground_color'] ?? '#000000',
            $data['default_background_color'] ?? '#ffffff',
            $data['default_error_correction'] ?? 'H',
            $data['default_frame_style'] ?? 'none',
            $data['default_download_format'] ?? 'png',
            $data['auto_save'] ?? 1,
            $data['email_notifications'] ?? 0,
            $data['scan_notification_threshold'] ?? 10,
            $userId
        ];
        
        try {
            $this->db->query($sql, $params);
            return true;
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to update user settings: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate or regenerate API key
     * 
     * @param int $userId User ID
     * @return string|false API key or false on failure
     */
    public function generateApiKey(int $userId)
    {
        $apiKey = bin2hex(random_bytes(32));
        
        $sql = "UPDATE qr_user_settings 
                SET api_key = ?, api_enabled = 1, updated_at = NOW()
                WHERE user_id = ?";
        
        try {
            $this->db->query($sql, [$apiKey, $userId]);
            return $apiKey;
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to generate API key: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Disable API access
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public function disableApi(int $userId): bool
    {
        $sql = "UPDATE qr_user_settings 
                SET api_enabled = 0, updated_at = NOW()
                WHERE user_id = ?";
        
        try {
            $this->db->query($sql, [$userId]);
            return true;
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to disable API: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verify API key
     * 
     * @param string $apiKey API key
     * @return int|false User ID or false if invalid
     */
    public function verifyApiKey(string $apiKey)
    {
        $sql = "SELECT user_id FROM qr_user_settings 
                WHERE api_key = ? AND api_enabled = 1";
        
        try {
            $result = $this->db->query($sql, [$apiKey])->fetch();
            return $result ? $result['user_id'] : false;
        } catch (\Exception $e) {
            \Core\Logger::error('Failed to verify API key: ' . $e->getMessage());
            return false;
        }
    }
}
