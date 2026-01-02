<?php
/**
 * ProShare Helper Trait
 * 
 * Common functionality for ProShare controllers including logging,
 * error handling, and admin panel integration hooks.
 * 
 * @package MMB\Projects\ProShare
 */

namespace Projects\ProShare;

use Core\Logger;
use Core\Database;

trait ProShareHelpers
{
    /**
     * Log activity for admin panel integration
     * 
     * @param int|null $userId User ID (null for anonymous)
     * @param string $action Action performed
     * @param string $resourceType Type of resource (file, text, setting)
     * @param int|null $resourceId Resource ID
     * @param array $details Additional details
     * @return void
     */
    protected function logActivity(?int $userId, string $action, string $resourceType, ?int $resourceId = null, array $details = []): void
    {
        try {
            $db = Database::projectConnection('proshare');
            
            $db->insert('audit_logs', [
                'user_id' => $userId,
                'action' => $action,
                'resource_type' => $resourceType,
                'resource_id' => $resourceId,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'details' => json_encode($details),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            
            // Also log to system logger for admin panel
            Logger::info("ProShare: {$action}", [
                'user_id' => $userId,
                'resource_type' => $resourceType,
                'resource_id' => $resourceId,
                'details' => $details,
            ]);
        } catch (\Exception $e) {
            // Don't fail the main operation if logging fails
            Logger::error('Failed to log ProShare activity: ' . $e->getMessage());
        }
    }
    
    /**
     * Create notification for user
     * 
     * @param int $userId User ID
     * @param string $type Notification type
     * @param string $message Message
     * @param int|null $relatedId Related resource ID
     * @return void
     */
    protected function createNotification(int $userId, string $type, string $message, ?int $relatedId = null): void
    {
        try {
            $db = Database::projectConnection('proshare');
            
            $db->insert('notifications', [
                'user_id' => $userId,
                'type' => $type,
                'message' => $message,
                'related_id' => $relatedId,
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            Logger::error('Failed to create ProShare notification: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate unique short code
     * 
     * @param int $length Length of code
     * @return string Short code
     */
    protected function generateShortCode(int $length = 8): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';
        
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        // Ensure uniqueness
        $db = Database::projectConnection('proshare');
        $exists = $db->fetchColumn(
            "SELECT COUNT(*) FROM files WHERE short_code = ? 
             UNION ALL 
             SELECT COUNT(*) FROM text_shares WHERE short_code = ?",
            [$code, $code]
        );
        
        if ($exists) {
            return $this->generateShortCode($length);
        }
        
        return $code;
    }
    
    /**
     * Format file size for display
     * 
     * @param int $bytes File size in bytes
     * @return string Formatted size
     */
    protected function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * Validate and sanitize input
     * 
     * @param array $data Input data
     * @param array $rules Validation rules
     * @return array ['valid' => bool, 'errors' => array, 'data' => array]
     */
    protected function validateInput(array $data, array $rules): array
    {
        $errors = [];
        $sanitized = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            // Required check
            if (isset($rule['required']) && $rule['required'] && empty($value)) {
                $errors[$field] = ucfirst($field) . ' is required';
                continue;
            }
            
            // Skip further validation if not required and empty
            if (empty($value) && !isset($rule['required'])) {
                $sanitized[$field] = null;
                continue;
            }
            
            // Type validation
            if (isset($rule['type'])) {
                switch ($rule['type']) {
                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field] = 'Invalid email address';
                        }
                        $sanitized[$field] = filter_var($value, FILTER_SANITIZE_EMAIL);
                        break;
                    
                    case 'int':
                        if (!is_numeric($value) || (int)$value != $value) {
                            $errors[$field] = ucfirst($field) . ' must be an integer';
                        }
                        $sanitized[$field] = (int)$value;
                        break;
                    
                    case 'string':
                        $sanitized[$field] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
                        break;
                    
                    case 'text':
                        // Preserve line breaks for text content
                        $sanitized[$field] = trim($value);
                        break;
                    
                    default:
                        $sanitized[$field] = $value;
                }
            } else {
                $sanitized[$field] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            }
            
            // Length validation
            if (isset($rule['max_length']) && strlen($sanitized[$field]) > $rule['max_length']) {
                $errors[$field] = ucfirst($field) . ' must be less than ' . $rule['max_length'] . ' characters';
            }
            
            if (isset($rule['min_length']) && strlen($sanitized[$field]) < $rule['min_length']) {
                $errors[$field] = ucfirst($field) . ' must be at least ' . $rule['min_length'] . ' characters';
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => $sanitized,
        ];
    }
    
    /**
     * Handle errors with proper logging and user feedback
     * 
     * @param \Exception $e Exception
     * @param string $userMessage User-friendly message
     * @param array $context Additional context
     * @return void
     */
    protected function handleError(\Exception $e, string $userMessage = 'An error occurred', array $context = []): void
    {
        // Log detailed error for debugging
        Logger::error('ProShare Error: ' . $e->getMessage(), array_merge($context, [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]));
        
        // Return user-friendly error
        if (headers_sent()) {
            echo $userMessage;
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $userMessage,
            ]);
        }
    }
    
    /**
     * Check if file has expired or reached limits
     * 
     * @param array $file File data
     * @return array ['expired' => bool, 'reason' => string]
     */
    protected function checkFileStatus(array $file): array
    {
        // Check expiry
        if ($file['expires_at'] && strtotime($file['expires_at']) < time()) {
            return ['expired' => true, 'reason' => 'This file has expired'];
        }
        
        // Check download limit
        if ($file['max_downloads'] && $file['downloads'] >= $file['max_downloads']) {
            return ['expired' => true, 'reason' => 'Download limit reached'];
        }
        
        // Check status
        if ($file['status'] !== 'active') {
            return ['expired' => true, 'reason' => 'This file is no longer available'];
        }
        
        return ['expired' => false, 'reason' => ''];
    }
    
    /**
     * Clean expired files (for cron job)
     * 
     * @return int Number of files cleaned
     */
    protected function cleanExpiredFiles(): int
    {
        try {
            $db = Database::projectConnection('proshare');
            
            // Find expired files
            $expiredFiles = $db->fetchAll(
                "SELECT * FROM files 
                 WHERE status = 'active' 
                 AND (
                     (expires_at IS NOT NULL AND expires_at < NOW())
                     OR (max_downloads IS NOT NULL AND downloads >= max_downloads)
                 )"
            );
            
            $cleaned = 0;
            foreach ($expiredFiles as $file) {
                // Delete physical file
                if (file_exists($file['path'])) {
                    unlink($file['path']);
                }
                
                // Update status
                $db->update(
                    'files',
                    ['status' => 'expired'],
                    'id = ?',
                    [$file['id']]
                );
                
                $cleaned++;
                
                // Log activity
                $this->logActivity(
                    $file['user_id'],
                    'auto_cleanup',
                    'file',
                    $file['id'],
                    ['reason' => 'expired']
                );
            }
            
            return $cleaned;
        } catch (\Exception $e) {
            Logger::error('Failed to clean expired files: ' . $e->getMessage());
            return 0;
        }
    }
}
