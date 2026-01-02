<?php
/**
 * API Rate Limiter
 * 
 * Handles API rate limiting per key
 * Part of Phase 11: API Development
 * 
 * @package MMB\Core\API
 */

namespace Core\API;

use Core\Cache;
use Core\Logger;

class RateLimiter
{
    // Default limits
    private static $defaultLimits = [
        'requests_per_minute' => 60,
        'requests_per_hour' => 1000,
        'requests_per_day' => 10000
    ];
    
    /**
     * Check if request is within rate limit
     * 
     * @param string $apiKey API key
     * @return bool Within limit
     */
    public static function check(string $apiKey): bool
    {
        $keyHash = md5($apiKey);
        
        // Check minute limit
        if (!self::checkLimit($keyHash, 'minute', self::$defaultLimits['requests_per_minute'])) {
            Logger::warning("Rate limit exceeded (minute) for API key: {$keyHash}");
            return false;
        }
        
        // Check hour limit
        if (!self::checkLimit($keyHash, 'hour', self::$defaultLimits['requests_per_hour'])) {
            Logger::warning("Rate limit exceeded (hour) for API key: {$keyHash}");
            return false;
        }
        
        // Check day limit
        if (!self::checkLimit($keyHash, 'day', self::$defaultLimits['requests_per_day'])) {
            Logger::warning("Rate limit exceeded (day) for API key: {$keyHash}");
            return false;
        }
        
        return true;
    }
    
    /**
     * Check specific time window limit
     * 
     * @param string $keyHash Hashed API key
     * @param string $window Time window (minute, hour, day)
     * @param int $limit Request limit
     * @return bool Within limit
     */
    private static function checkLimit(string $keyHash, string $window, int $limit): bool
    {
        $cacheKey = self::getCacheKey($keyHash, $window);
        $count = (int)Cache::get($cacheKey, 0);
        
        if ($count >= $limit) {
            return false;
        }
        
        // Increment counter
        Cache::set($cacheKey, $count + 1, self::getTTL($window));
        
        return true;
    }
    
    /**
     * Get remaining requests
     * 
     * @param string $apiKey API key
     * @param string $window Time window
     * @return int Remaining requests
     */
    public static function getRemaining(string $apiKey, string $window = 'minute'): int
    {
        $keyHash = md5($apiKey);
        $cacheKey = self::getCacheKey($keyHash, $window);
        $count = (int)Cache::get($cacheKey, 0);
        
        $limit = self::$defaultLimits["requests_per_{$window}"] ?? 60;
        
        return max(0, $limit - $count);
    }
    
    /**
     * Reset rate limit for key
     * 
     * @param string $apiKey API key
     * @param string|null $window Time window (null for all)
     */
    public static function reset(string $apiKey, ?string $window = null): void
    {
        $keyHash = md5($apiKey);
        
        if ($window) {
            $cacheKey = self::getCacheKey($keyHash, $window);
            Cache::delete($cacheKey);
        } else {
            // Reset all windows
            foreach (['minute', 'hour', 'day'] as $w) {
                $cacheKey = self::getCacheKey($keyHash, $w);
                Cache::delete($cacheKey);
            }
        }
    }
    
    /**
     * Get rate limit info
     * 
     * @param string $apiKey API key
     * @return array Rate limit info
     */
    public static function getInfo(string $apiKey): array
    {
        $keyHash = md5($apiKey);
        
        return [
            'minute' => [
                'limit' => self::$defaultLimits['requests_per_minute'],
                'remaining' => self::getRemaining($apiKey, 'minute'),
                'reset_at' => time() + self::getTTL('minute')
            ],
            'hour' => [
                'limit' => self::$defaultLimits['requests_per_hour'],
                'remaining' => self::getRemaining($apiKey, 'hour'),
                'reset_at' => time() + self::getTTL('hour')
            ],
            'day' => [
                'limit' => self::$defaultLimits['requests_per_day'],
                'remaining' => self::getRemaining($apiKey, 'day'),
                'reset_at' => time() + self::getTTL('day')
            ]
        ];
    }
    
    /**
     * Set custom limits for specific API key
     * 
     * @param string $apiKey API key
     * @param array $limits Custom limits
     */
    public static function setLimits(string $apiKey, array $limits): void
    {
        $keyHash = md5($apiKey);
        $cacheKey = "rate_limit_custom_{$keyHash}";
        Cache::set($cacheKey, $limits, 0); // No expiration
    }
    
    /**
     * Get cache key for rate limit
     * 
     * @param string $keyHash Hashed API key
     * @param string $window Time window
     * @return string Cache key
     */
    private static function getCacheKey(string $keyHash, string $window): string
    {
        $timestamp = self::getWindowTimestamp($window);
        return "rate_limit_{$keyHash}_{$window}_{$timestamp}";
    }
    
    /**
     * Get window timestamp
     * 
     * @param string $window Time window
     * @return int Timestamp
     */
    private static function getWindowTimestamp(string $window): int
    {
        switch ($window) {
            case 'minute':
                return floor(time() / 60);
            case 'hour':
                return floor(time() / 3600);
            case 'day':
                return floor(time() / 86400);
            default:
                return time();
        }
    }
    
    /**
     * Get TTL for cache key
     * 
     * @param string $window Time window
     * @return int TTL in seconds
     */
    private static function getTTL(string $window): int
    {
        switch ($window) {
            case 'minute':
                return 60;
            case 'hour':
                return 3600;
            case 'day':
                return 86400;
            default:
                return 3600;
        }
    }
}
