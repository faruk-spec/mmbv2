<?php
/**
 * API Authentication
 * 
 * Handles API key validation and management
 * Part of Phase 11: API Development
 * 
 * @package MMB\Core\API
 */

namespace Core\API;

use Core\Database;
use Core\Logger;
use Core\Cache;

class ApiAuth
{
    /**
     * Validate API key
     * 
     * @param string $apiKey API key to validate
     * @return bool Valid status
     */
    public static function validateKey(string $apiKey): bool
    {
        if (empty($apiKey)) {
            return false;
        }
        
        // Check cache first
        $cacheKey = 'api_key_valid_' . md5($apiKey);
        $cached = Cache::get($cacheKey);
        
        if ($cached !== null) {
            return $cached === 'valid';
        }
        
        try {
            $db = Database::getInstance();
            
            $key = $db->fetch(
                "SELECT * FROM api_keys WHERE api_key = ? AND is_active = 1",
                [$apiKey]
            );
            
            if (!$key) {
                Cache::set($cacheKey, 'invalid', 300); // Cache for 5 minutes
                return false;
            }
            
            // Check expiration
            if ($key['expires_at'] && strtotime($key['expires_at']) < time()) {
                Cache::set($cacheKey, 'invalid', 300);
                return false;
            }
            
            // Update last used
            $db->query(
                "UPDATE api_keys SET last_used_at = NOW(), request_count = request_count + 1 WHERE id = ?",
                [$key['id']]
            );
            
            // Cache valid status
            Cache::set($cacheKey, 'valid', 300);
            
            // Store key info in request context
            self::setCurrentKey($key);
            
            return true;
            
        } catch (\Exception $e) {
            Logger::error('API key validation error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate new API key
     * 
     * @param int $userId User ID
     * @param string $name Key name/description
     * @param array $permissions Key permissions
     * @param string|null $expiresAt Expiration date
     * @return array Key data
     */
    public static function generateKey(int $userId, string $name, array $permissions = [], ?string $expiresAt = null): array
    {
        $apiKey = self::createRandomKey();
        
        try {
            $db = Database::getInstance();
            
            $data = [
                'user_id' => $userId,
                'name' => $name,
                'api_key' => $apiKey,
                'permissions' => json_encode($permissions),
                'expires_at' => $expiresAt,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $id = $db->insert('api_keys', $data);
            
            if ($id) {
                Logger::info("API key generated for user {$userId}: {$name}");
                
                return [
                    'id' => $id,
                    'api_key' => $apiKey,
                    'name' => $name,
                    'created_at' => $data['created_at']
                ];
            }
            
            throw new \Exception('Failed to generate API key');
            
        } catch (\Exception $e) {
            Logger::error('API key generation error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Revoke API key
     * 
     * @param int $keyId Key ID
     * @return bool Success status
     */
    public static function revokeKey(int $keyId): bool
    {
        try {
            $db = Database::getInstance();
            
            $db->update('api_keys', [
                'is_active' => 0,
                'revoked_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$keyId]);
            
            // Clear cache
            $key = $db->fetch("SELECT api_key FROM api_keys WHERE id = ?", [$keyId]);
            if ($key) {
                $cacheKey = 'api_key_valid_' . md5($key['api_key']);
                Cache::delete($cacheKey);
            }
            
            Logger::info("API key revoked: {$keyId}");
            
            return true;
            
        } catch (\Exception $e) {
            Logger::error('API key revocation error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user API keys
     * 
     * @param int $userId User ID
     * @return array API keys
     */
    public static function getUserKeys(int $userId): array
    {
        try {
            $db = Database::getInstance();
            
            return $db->fetchAll(
                "SELECT id, name, api_key, permissions, is_active, created_at, last_used_at, expires_at, request_count 
                 FROM api_keys 
                 WHERE user_id = ? 
                 ORDER BY created_at DESC",
                [$userId]
            );
            
        } catch (\Exception $e) {
            Logger::error('Get user API keys error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check if key has permission
     * 
     * @param string $permission Permission to check
     * @return bool Has permission
     */
    public static function hasPermission(string $permission): bool
    {
        $key = self::getCurrentKey();
        
        if (!$key) {
            return false;
        }
        
        $permissions = json_decode($key['permissions'] ?? '[]', true);
        
        // Wildcard permission
        if (in_array('*', $permissions)) {
            return true;
        }
        
        return in_array($permission, $permissions);
    }
    
    /**
     * Get current API key info
     * 
     * @return array|null Key info
     */
    public static function getCurrentKey(): ?array
    {
        return $_REQUEST['_api_key_info'] ?? null;
    }
    
    /**
     * Set current API key info
     * 
     * @param array $key Key info
     */
    private static function setCurrentKey(array $key): void
    {
        $_REQUEST['_api_key_info'] = $key;
    }
    
    /**
     * Get current user ID from API key
     * 
     * @return int|null User ID
     */
    public static function getUserId(): ?int
    {
        $key = self::getCurrentKey();
        return $key['user_id'] ?? null;
    }
    
    /**
     * Create random API key
     * 
     * @return string API key
     */
    private static function createRandomKey(): string
    {
        return 'mmb_' . bin2hex(random_bytes(32));
    }
}
