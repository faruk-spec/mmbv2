<?php
/**
 * Cache Management System
 * 
 * Provides simple file-based caching with TTL support
 * Part of Phase 8: Performance Optimization
 * 
 * @package MMB\Core
 */

namespace Core;

class Cache
{
    private static $cacheDir;
    private static $defaultTTL = 3600; // 1 hour default
    
    /**
     * Initialize cache directory
     */
    private static function init(): void
    {
        if (!self::$cacheDir) {
            self::$cacheDir = BASE_PATH . '/storage/cache';
            
            // Create cache directory if it doesn't exist
            if (!is_dir(self::$cacheDir)) {
                mkdir(self::$cacheDir, 0755, true);
            }
        }
    }
    
    /**
     * Get cache file path
     */
    private static function getCacheFilePath(string $key): string
    {
        self::init();
        $hash = md5($key);
        return self::$cacheDir . '/' . $hash . '.cache';
    }
    
    /**
     * Store value in cache
     * 
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int $ttl Time to live in seconds (0 = no expiry)
     * @return bool Success status
     */
    public static function set(string $key, $value, int $ttl = null): bool
    {
        self::init();
        
        if ($ttl === null) {
            $ttl = self::$defaultTTL;
        }
        
        $cacheData = [
            'expires_at' => $ttl > 0 ? time() + $ttl : 0,
            'value' => $value
        ];
        
        $filePath = self::getCacheFilePath($key);
        $result = file_put_contents($filePath, serialize($cacheData), LOCK_EX);
        
        return $result !== false;
    }
    
    /**
     * Get value from cache
     * 
     * @param string $key Cache key
     * @param mixed $default Default value if not found or expired
     * @return mixed Cached value or default
     */
    public static function get(string $key, $default = null)
    {
        self::init();
        
        $filePath = self::getCacheFilePath($key);
        
        if (!file_exists($filePath)) {
            return $default;
        }
        
        $content = file_get_contents($filePath);
        if ($content === false) {
            return $default;
        }
        
        $cacheData = unserialize($content);
        
        // Check if expired
        if ($cacheData['expires_at'] > 0 && $cacheData['expires_at'] < time()) {
            self::delete($key);
            return $default;
        }
        
        return $cacheData['value'];
    }
    
    /**
     * Check if cache key exists and is not expired
     * 
     * @param string $key Cache key
     * @return bool
     */
    public static function has(string $key): bool
    {
        self::init();
        
        $filePath = self::getCacheFilePath($key);
        
        if (!file_exists($filePath)) {
            return false;
        }
        
        $content = file_get_contents($filePath);
        if ($content === false) {
            return false;
        }
        
        $cacheData = unserialize($content);
        
        // Check if expired
        if ($cacheData['expires_at'] > 0 && $cacheData['expires_at'] < time()) {
            self::delete($key);
            return false;
        }
        
        return true;
    }
    
    /**
     * Delete cache entry
     * 
     * @param string $key Cache key
     * @return bool Success status
     */
    public static function delete(string $key): bool
    {
        self::init();
        
        $filePath = self::getCacheFilePath($key);
        
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        
        return true;
    }
    
    /**
     * Clear all cache entries or entries matching a pattern
     * 
     * @param string|null $pattern Optional pattern to match (e.g., 'user_*')
     * @return int Number of entries cleared
     */
    public static function clear(string $pattern = null): int
    {
        self::init();
        
        $count = 0;
        $files = glob(self::$cacheDir . '/*.cache');
        
        foreach ($files as $file) {
            if ($pattern) {
                // Get original key from file content to check pattern
                $content = file_get_contents($file);
                if ($content === false) continue;
                
                // For pattern matching, we'd need to store the original key
                // For now, just delete if no pattern specified
                continue;
            }
            
            if (unlink($file)) {
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Remember (get or set) a value in cache
     * 
     * @param string $key Cache key
     * @param callable $callback Callback to generate value if not cached
     * @param int $ttl Time to live in seconds
     * @return mixed Cached or generated value
     */
    public static function remember(string $key, callable $callback, int $ttl = null)
    {
        if (self::has($key)) {
            return self::get($key);
        }
        
        $value = $callback();
        self::set($key, $value, $ttl);
        
        return $value;
    }
    
    /**
     * Remove expired cache entries
     * 
     * @return int Number of entries removed
     */
    public static function cleanup(): int
    {
        self::init();
        
        $count = 0;
        $files = glob(self::$cacheDir . '/*.cache');
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if ($content === false) continue;
            
            $cacheData = unserialize($content);
            
            // Delete if expired
            if ($cacheData['expires_at'] > 0 && $cacheData['expires_at'] < time()) {
                if (unlink($file)) {
                    $count++;
                }
            }
        }
        
        return $count;
    }
    
    /**
     * Get cache statistics
     * 
     * @return array Cache stats
     */
    public static function stats(): array
    {
        self::init();
        
        $files = glob(self::$cacheDir . '/*.cache');
        $totalSize = 0;
        $expiredCount = 0;
        $activeCount = 0;
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
            
            $content = file_get_contents($file);
            if ($content === false) continue;
            
            $cacheData = unserialize($content);
            
            if ($cacheData['expires_at'] > 0 && $cacheData['expires_at'] < time()) {
                $expiredCount++;
            } else {
                $activeCount++;
            }
        }
        
        return [
            'total_entries' => count($files),
            'active_entries' => $activeCount,
            'expired_entries' => $expiredCount,
            'total_size' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2)
        ];
    }
    
    /**
     * Set default TTL
     * 
     * @param int $seconds TTL in seconds
     */
    public static function setDefaultTTL(int $seconds): void
    {
        self::$defaultTTL = $seconds;
    }
}
