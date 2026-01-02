<?php
/**
 * Analytics System
 * 
 * Track and analyze user interactions, downloads, and engagement
 * Part of Phase 7: Advanced ProShare Features
 * 
 * @package MMB\Core
 */

namespace Core;

class Analytics
{
    /**
     * Track an event
     * 
     * @param string $event Event name
     * @param array $data Event data
     * @param int|null $userId User ID (optional)
     * @return bool Success status
     */
    public static function track(string $event, array $data = [], ?int $userId = null): bool
    {
        // Get database connection based on context
        // For ProShare analytics, use ProShare database
        $db = Database::getInstance();
        
        $eventData = [
            'event' => $event,
            'user_id' => $userId,
            'data' => json_encode($data),
            'ip_address' => self::getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'referer' => $_SERVER['HTTP_REFERER'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        try {
            // Store in cache for batch processing
            $cacheKey = 'analytics_queue_' . date('YmdH');
            $queue = Cache::get($cacheKey, []);
            $queue[] = $eventData;
            Cache::set($cacheKey, $queue, 3600);
            
            return true;
        } catch (\Exception $e) {
            Logger::error('Analytics tracking failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Track file download
     * 
     * @param int $fileId File ID
     * @param int|null $userId User ID
     * @return bool
     */
    public static function trackDownload(int $fileId, ?int $userId = null): bool
    {
        return self::track('file_download', [
            'file_id' => $fileId,
            'browser' => self::getBrowser(),
            'platform' => self::getPlatform(),
            'country' => self::getCountry()
        ], $userId);
    }
    
    /**
     * Track page view
     * 
     * @param string $page Page identifier
     * @param int|null $userId User ID
     * @return bool
     */
    public static function trackPageView(string $page, ?int $userId = null): bool
    {
        return self::track('page_view', [
            'page' => $page,
            'url' => $_SERVER['REQUEST_URI'] ?? null
        ], $userId);
    }
    
    /**
     * Get analytics summary for date range
     * 
     * @param string $startDate Start date (Y-m-d)
     * @param string $endDate End date (Y-m-d)
     * @param string $event Event type filter (optional)
     * @return array Analytics data
     */
    public static function getSummary(string $startDate, string $endDate, ?string $event = null): array
    {
        // This would query the analytics data
        // For now, return placeholder data
        return [
            'total_events' => 0,
            'unique_users' => 0,
            'top_events' => [],
            'timeline' => []
        ];
    }
    
    /**
     * Get download statistics
     * 
     * @param int|null $fileId Specific file ID (optional)
     * @param int $days Number of days to analyze
     * @return array Download stats
     */
    public static function getDownloadStats(?int $fileId = null, int $days = 7): array
    {
        $cacheKey = $fileId ? "download_stats_{$fileId}_{$days}" : "download_stats_all_{$days}";
        
        return Cache::remember($cacheKey, function() use ($fileId, $days) {
            // This would query actual analytics data
            // For now, return placeholder
            return [
                'total_downloads' => 0,
                'unique_downloaders' => 0,
                'downloads_by_day' => [],
                'downloads_by_country' => [],
                'downloads_by_browser' => [],
                'downloads_by_platform' => []
            ];
        }, 300);
    }
    
    /**
     * Generate analytics report
     * 
     * @param string $type Report type (daily, weekly, monthly)
     * @param string $format Format (html, csv, json)
     * @return array Report data
     */
    public static function generateReport(string $type = 'daily', string $format = 'html'): array
    {
        $data = [
            'type' => $type,
            'generated_at' => date('Y-m-d H:i:s'),
            'summary' => self::getSummary(
                date('Y-m-d', strtotime('-7 days')),
                date('Y-m-d')
            )
        ];
        
        switch ($format) {
            case 'csv':
                return self::formatAsCSV($data);
            case 'json':
                return ['data' => json_encode($data, JSON_PRETTY_PRINT)];
            default:
                return $data;
        }
    }
    
    /**
     * Get client IP address
     * 
     * @return string|null
     */
    private static function getClientIp(): ?string
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
        
        return null;
    }
    
    /**
     * Get browser name
     * 
     * @return string
     */
    private static function getBrowser(): string
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if (strpos($userAgent, 'Chrome') !== false) return 'Chrome';
        if (strpos($userAgent, 'Firefox') !== false) return 'Firefox';
        if (strpos($userAgent, 'Safari') !== false) return 'Safari';
        if (strpos($userAgent, 'Edge') !== false) return 'Edge';
        if (strpos($userAgent, 'Opera') !== false) return 'Opera';
        
        return 'Other';
    }
    
    /**
     * Get platform/OS
     * 
     * @return string
     */
    private static function getPlatform(): string
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if (strpos($userAgent, 'Windows') !== false) return 'Windows';
        if (strpos($userAgent, 'Mac') !== false) return 'macOS';
        if (strpos($userAgent, 'Linux') !== false) return 'Linux';
        if (strpos($userAgent, 'Android') !== false) return 'Android';
        if (strpos($userAgent, 'iOS') !== false) return 'iOS';
        
        return 'Other';
    }
    
    /**
     * Get country from IP (simplified)
     * 
     * @return string
     */
    private static function getCountry(): string
    {
        // In production, use a GeoIP service
        // For now, return unknown
        return 'Unknown';
    }
    
    /**
     * Format data as CSV
     * 
     * @param array $data
     * @return array
     */
    private static function formatAsCSV(array $data): array
    {
        // Simplified CSV generation
        $csv = "Type,Generated At\n";
        $csv .= "\"{$data['type']}\",\"{$data['generated_at']}\"\n";
        
        return [
            'data' => $csv,
            'filename' => 'analytics_' . date('Y-m-d') . '.csv'
        ];
    }
    
    /**
     * Flush analytics queue to database
     * 
     * @return int Number of events flushed
     */
    public static function flush(): int
    {
        $cacheKey = 'analytics_queue_' . date('YmdH');
        $queue = Cache::get($cacheKey, []);
        
        if (empty($queue)) {
            return 0;
        }
        
        // In production, batch insert into database
        // For now, just clear the queue
        Cache::delete($cacheKey);
        
        return count($queue);
    }
}
