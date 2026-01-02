<?php
/**
 * Traffic Tracking Middleware
 * 
 * Automatically tracks page visits and user activity
 * 
 * @package MMB\Core\Middleware
 */

namespace Core\Middleware;

use Core\Auth;
use Core\TrafficTracker;

class TrafficTrackingMiddleware
{
    /**
     * Handle the middleware
     * Track page visits for analytics
     * 
     * @return bool Always returns true to allow request to continue
     */
    public function handle(): bool
    {
        // Skip tracking for certain paths
        $path = $_SERVER['REQUEST_URI'] ?? '';
        $skipPaths = [
            '/api/',
            '/websocket',
            '/assets/',
            '/public/',
            '/favicon.ico',
            '/_health',
            '/_status'
        ];
        
        foreach ($skipPaths as $skipPath) {
            if (strpos($path, $skipPath) !== false) {
                return true;
            }
        }
        
        // Track page visit
        try {
            $userId = Auth::check() ? Auth::id() : null;
            TrafficTracker::trackVisit($path, $userId);
            
            // Check if this is a return visit for logged-in users
            if ($userId) {
                TrafficTracker::trackReturnVisit($userId);
            }
        } catch (\Exception $e) {
            // Silently fail - don't break the application if tracking fails
            error_log('Traffic tracking error: ' . $e->getMessage());
        }
        
        return true;
    }
}
