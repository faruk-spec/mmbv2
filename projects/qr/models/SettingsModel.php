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
            // Design defaults
            'default_corner_style' => 'square',
            'default_dot_style' => 'square',
            'default_marker_border_style' => 'square',
            'default_marker_center_style' => 'square',
            // Logo defaults
            'default_logo_color' => '#9945ff',
            'default_logo_size' => 0.30,
            'default_logo_remove_bg' => 0,
            // Advanced defaults
            'default_gradient_enabled' => 0,
            'default_gradient_color' => '#9945ff',
            'default_transparent_bg' => 0,
            'default_custom_marker_color' => 0,
            'default_marker_color' => '#9945ff',
            // Preferences
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
        // Build dynamic SQL based on what columns actually exist
        $fields = ['user_id'];
        $placeholders = ['?'];
        $params = [$userId];
        
        // Basic settings that should always exist
        $basicFields = [
            'default_size', 'default_foreground_color', 'default_background_color',
            'default_error_correction', 'default_frame_style', 'default_download_format',
            'auto_save', 'email_notifications', 'scan_notification_threshold'
        ];
        
        foreach ($basicFields as $field) {
            $fields[] = $field;
            $placeholders[] = '?';
            $params[] = $data[$field] ?? $this->getDefaults()[$field];
        }
        
        // Optional fields that might not exist yet
        $optionalFields = [
            'default_corner_style', 'default_dot_style',
            'default_marker_border_style', 'default_marker_center_style',
            'default_logo_color', 'default_logo_size', 'default_logo_remove_bg',
            'default_gradient_enabled', 'default_gradient_color',
            'default_transparent_bg', 'default_custom_marker_color', 'default_marker_color'
        ];
        
        // Try to get table structure to see what columns exist
        try {
            $checkSql = "SHOW COLUMNS FROM qr_user_settings";
            $columns = $this->db->query($checkSql)->fetchAll();
            $existingColumns = array_column($columns, 'Field');
            
            foreach ($optionalFields as $field) {
                if (in_array($field, $existingColumns)) {
                    $fields[] = $field;
                    $placeholders[] = '?';
                    $value = isset($data[$field]) ? $data[$field] : $this->getDefaults()[$field];
                    // Handle checkbox values
                    if (strpos($field, '_enabled') !== false || 
                        strpos($field, '_remove_bg') !== false || 
                        strpos($field, '_custom_') !== false ||
                        strpos($field, '_transparent_') !== false) {
                        $value = isset($data[$field]) ? 1 : 0;
                    }
                    $params[] = $value;
                }
            }
        } catch (\Exception $e) {
            // If we can't check columns, just use basic fields
            \Core\Logger::warning('Could not check table structure: ' . $e->getMessage());
        }
        
        $fields[] = 'created_at';
        $placeholders[] = 'NOW()';
        
        $sql = "INSERT INTO qr_user_settings (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
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
        // Build dynamic SQL based on what columns actually exist
        $setClauses = [];
        $params = [];
        
        // Basic settings that should always exist
        $basicFields = [
            'default_size', 'default_foreground_color', 'default_background_color',
            'default_error_correction', 'default_frame_style', 'default_download_format',
            'auto_save', 'email_notifications', 'scan_notification_threshold'
        ];
        
        foreach ($basicFields as $field) {
            $setClauses[] = "$field = ?";
            $params[] = $data[$field] ?? $this->getDefaults()[$field];
        }
        
        // Optional fields that might not exist yet
        $optionalFields = [
            'default_corner_style', 'default_dot_style',
            'default_marker_border_style', 'default_marker_center_style',
            'default_logo_color', 'default_logo_size', 'default_logo_remove_bg',
            'default_gradient_enabled', 'default_gradient_color',
            'default_transparent_bg', 'default_custom_marker_color', 'default_marker_color'
        ];
        
        // Try to get table structure to see what columns exist
        try {
            $checkSql = "SHOW COLUMNS FROM qr_user_settings";
            $columns = $this->db->query($checkSql)->fetchAll();
            $existingColumns = array_column($columns, 'Field');
            
            foreach ($optionalFields as $field) {
                if (in_array($field, $existingColumns)) {
                    $setClauses[] = "$field = ?";
                    $value = isset($data[$field]) ? $data[$field] : $this->getDefaults()[$field];
                    // Handle checkbox values
                    if (strpos($field, '_enabled') !== false || 
                        strpos($field, '_remove_bg') !== false || 
                        strpos($field, '_custom_') !== false ||
                        strpos($field, '_transparent_') !== false) {
                        $value = isset($data[$field]) ? 1 : 0;
                    }
                    $params[] = $value;
                }
            }
        } catch (\Exception $e) {
            // If we can't check columns, just use basic fields
            \Core\Logger::warning('Could not check table structure: ' . $e->getMessage());
        }
        
        $setClauses[] = "updated_at = NOW()";
        $params[] = $userId;
        
        $sql = "UPDATE qr_user_settings SET " . implode(', ', $setClauses) . " WHERE user_id = ?";
        
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
