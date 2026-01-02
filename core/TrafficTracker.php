<?php
/**
 * Live Traffic Tracker
 * 
 * Tracks real-time user traffic including:
 * - Page visits
 * - IP addresses
 * - Browser and platform info
 * - Geographic location
 * - User actions (login, register, return visits)
 * - Session timing
 * 
 * @package MMB\Core
 */

namespace Core;

use Core\Logger;
use Core\Cache;

class TrafficTracker
{
    /**
     * Track a page visit
     * 
     * @param string $page Page URL or identifier
     * @param int|null $userId User ID if logged in
     * @return bool Success status
     */
    public static function trackVisit(string $page, ?int $userId = null): bool
    {
        $db = Database::getInstance();
        
        try {
            $data = [
                'project' => 'platform',
                'resource_type' => 'page',
                'resource_id' => 0,
                'event_type' => 'page_visit',
                'user_id' => $userId,
                'ip_address' => self::getClientIp(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'browser' => self::getBrowser(),
                'platform' => self::getPlatform(),
                'country' => self::getCountry(),
                'metadata' => json_encode([
                    'page' => $page,
                    'url' => $_SERVER['REQUEST_URI'] ?? null,
                    'referer' => $_SERVER['HTTP_REFERER'] ?? null,
                    'timestamp' => date('Y-m-d H:i:s')
                ])
            ];
            
            $db->insert('analytics_events', $data);
            return true;
        } catch (\Exception $e) {
            Logger::error('Traffic tracking failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Track user login
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public static function trackLogin(int $userId): bool
    {
        $db = Database::getInstance();
        
        try {
            $data = [
                'project' => 'platform',
                'resource_type' => 'auth',
                'resource_id' => $userId,
                'event_type' => 'user_login',
                'user_id' => $userId,
                'ip_address' => self::getClientIp(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'browser' => self::getBrowser(),
                'platform' => self::getPlatform(),
                'country' => self::getCountry(),
                'metadata' => json_encode([
                    'timestamp' => date('Y-m-d H:i:s'),
                    'login_method' => 'standard'
                ])
            ];
            
            $db->insert('analytics_events', $data);
            return true;
        } catch (\Exception $e) {
            Logger::error('Login tracking failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Track user registration
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public static function trackRegistration(int $userId): bool
    {
        $db = Database::getInstance();
        
        try {
            $data = [
                'project' => 'platform',
                'resource_type' => 'auth',
                'resource_id' => $userId,
                'event_type' => 'user_register',
                'user_id' => $userId,
                'ip_address' => self::getClientIp(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'browser' => self::getBrowser(),
                'platform' => self::getPlatform(),
                'country' => self::getCountry(),
                'metadata' => json_encode([
                    'timestamp' => date('Y-m-d H:i:s')
                ])
            ];
            
            $db->insert('analytics_events', $data);
            return true;
        } catch (\Exception $e) {
            Logger::error('Registration tracking failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Track conversion event
     * 
     * @param string $conversionType Type of conversion
     * @param int|null $userId User ID
     * @param array $metadata Additional data
     * @return bool Success status
     */
    public static function trackConversion(string $conversionType, ?int $userId = null, array $metadata = []): bool
    {
        $db = Database::getInstance();
        
        try {
            $data = [
                'project' => 'platform',
                'resource_type' => 'conversion',
                'resource_id' => 0,
                'event_type' => 'conversion_' . $conversionType,
                'user_id' => $userId,
                'ip_address' => self::getClientIp(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'browser' => self::getBrowser(),
                'platform' => self::getPlatform(),
                'country' => self::getCountry(),
                'metadata' => json_encode(array_merge($metadata, [
                    'conversion_type' => $conversionType,
                    'timestamp' => date('Y-m-d H:i:s')
                ]))
            ];
            
            $db->insert('analytics_events', $data);
            return true;
        } catch (\Exception $e) {
            Logger::error('Conversion tracking failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Track return visit
     * Uses session cache to avoid DB query on every page load
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public static function trackReturnVisit(int $userId): bool
    {
        // Check if we've already tracked return visit in this session
        if (isset($_SESSION['return_visit_tracked_' . $userId])) {
            return true;
        }
        
        $db = Database::getInstance();
        
        try {
            // Check cache first for last visit time
            $cacheKey = "last_visit_{$userId}";
            $lastVisitDate = Cache::get($cacheKey);
            
            if ($lastVisitDate === null) {
                // Not in cache, query database
                $lastVisit = $db->fetch(
                    "SELECT created_at FROM analytics_events 
                     WHERE user_id = ? AND event_type = 'page_visit'
                     ORDER BY created_at DESC LIMIT 1",
                    [$userId]
                );
                
                if ($lastVisit) {
                    $lastVisitDate = strtotime($lastVisit['created_at']);
                    // Cache for 1 hour
                    Cache::set($cacheKey, $lastVisitDate, 3600);
                }
            }
            
            $isReturn = false;
            $daysSinceLastVisit = 0;
            
            if ($lastVisitDate) {
                $daysSinceLastVisit = floor((time() - $lastVisitDate) / 86400);
                $isReturn = $daysSinceLastVisit >= 1;
            }
            
            if ($isReturn) {
                $data = [
                    'project' => 'platform',
                    'resource_type' => 'user',
                    'resource_id' => $userId,
                    'event_type' => 'return_visit',
                    'user_id' => $userId,
                    'ip_address' => self::getClientIp(),
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                    'browser' => self::getBrowser(),
                    'platform' => self::getPlatform(),
                    'country' => self::getCountry(),
                    'metadata' => json_encode([
                        'days_since_last_visit' => $daysSinceLastVisit,
                        'timestamp' => date('Y-m-d H:i:s')
                    ])
                ];
                
                $db->insert('analytics_events', $data);
                
                // Mark as tracked in session
                $_SESSION['return_visit_tracked_' . $userId] = true;
            }
            
            return true;
        } catch (\Exception $e) {
            Logger::error('Return visit tracking failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get current active users (in last 5 minutes)
     * 
     * @return int Number of active users
     */
    public static function getActiveUsers(): int
    {
        $db = Database::getInstance();
        
        try {
            $result = $db->fetch(
                "SELECT COUNT(DISTINCT user_id) as count 
                 FROM analytics_events 
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)"
            );
            
            return (int) ($result['count'] ?? 0);
        } catch (\Exception $e) {
            Logger::error('Failed to get active users: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get live traffic statistics
     * 
     * @return array Traffic statistics
     */
    public static function getLiveStats(): array
    {
        $db = Database::getInstance();
        
        try {
            // Last hour stats
            $lastHour = $db->fetch(
                "SELECT 
                    COUNT(*) as total_events,
                    COUNT(DISTINCT user_id) as unique_users,
                    COUNT(DISTINCT ip_address) as unique_ips
                 FROM analytics_events 
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)"
            );
            
            // Current minute stats
            $currentMinute = $db->fetch(
                "SELECT COUNT(*) as count 
                 FROM analytics_events 
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)"
            );
            
            // Top pages
            $topPages = $db->fetchAll(
                "SELECT 
                    JSON_EXTRACT(metadata, '$.page') as page,
                    COUNT(*) as count 
                 FROM analytics_events 
                 WHERE event_type = 'page_visit' 
                 AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                 GROUP BY JSON_EXTRACT(metadata, '$.page')
                 ORDER BY count DESC 
                 LIMIT 5"
            );
            
            return [
                'active_users' => self::getActiveUsers(),
                'last_hour' => $lastHour,
                'current_minute' => $currentMinute['count'] ?? 0,
                'top_pages' => $topPages
            ];
        } catch (\Exception $e) {
            Logger::error('Failed to get live stats: ' . $e->getMessage());
            return [
                'active_users' => 0,
                'last_hour' => ['total_events' => 0, 'unique_users' => 0, 'unique_ips' => 0],
                'current_minute' => 0,
                'top_pages' => []
            ];
        }
    }
    
    /**
     * Get client IP address
     * 
     * @return string IP address
     */
    private static function getClientIp(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP', // CloudFlare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR'
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                // Get first IP if multiple
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                return $ip;
            }
        }
        
        return '0.0.0.0';
    }
    
    /**
     * Get browser name
     * 
     * @return string Browser name
     */
    private static function getBrowser(): string
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if (strpos($userAgent, 'Edg') !== false) return 'Edge';
        if (strpos($userAgent, 'Chrome') !== false) return 'Chrome';
        if (strpos($userAgent, 'Firefox') !== false) return 'Firefox';
        if (strpos($userAgent, 'Safari') !== false) return 'Safari';
        if (strpos($userAgent, 'Opera') !== false) return 'Opera';
        if (strpos($userAgent, 'MSIE') !== false || strpos($userAgent, 'Trident') !== false) return 'IE';
        
        return 'Other';
    }
    
    /**
     * Get platform/OS
     * 
     * @return string Platform name
     */
    private static function getPlatform(): string
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if (strpos($userAgent, 'Windows') !== false) return 'Windows';
        if (strpos($userAgent, 'Mac') !== false) return 'macOS';
        if (strpos($userAgent, 'Linux') !== false) return 'Linux';
        if (strpos($userAgent, 'Android') !== false) return 'Android';
        if (strpos($userAgent, 'iOS') !== false || strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPad') !== false) return 'iOS';
        
        return 'Other';
    }
    
    /**
     * Get country from IP using ip-api.com (free tier)
     * 
     * @return string Country code
     */
    private static function getCountry(): string
    {
        $ip = self::getClientIp();
        
        // Skip for local IPs
        if ($ip === '0.0.0.0' || $ip === '127.0.0.1' || strpos($ip, '192.168.') === 0 || strpos($ip, '10.') === 0) {
            return 'XX';
        }
        
        // Check cache first
        $cacheKey = "geo_ip_{$ip}";
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
        
        try {
            // Use ip-api.com free tier (limit: 45 requests per minute)
            // In production, consider using MaxMind GeoIP2 for better performance
            $context = stream_context_create([
                'http' => [
                    'timeout' => 2, // 2 second timeout
                    'ignore_errors' => true
                ]
            ]);
            
            $response = @file_get_contents(
                "http://ip-api.com/json/{$ip}?fields=countryCode",
                false,
                $context
            );
            
            if ($response) {
                $data = json_decode($response, true);
                $country = $data['countryCode'] ?? 'XX';
                
                // Cache for 24 hours
                Cache::set($cacheKey, $country, 86400);
                
                return $country;
            }
        } catch (\Exception $e) {
            // Silently fail and return unknown
            error_log('GeoIP lookup failed: ' . $e->getMessage());
        }
        
        // Default to unknown
        Cache::set($cacheKey, 'XX', 86400);
        return 'XX';
    }
}
