<?php
/**
 * Analytics Admin Controller
 * 
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;
use Core\Auth;
use Core\Cache;
use Core\Analytics;
use Core\TrafficTracker;

class AnalyticsController extends BaseController
{
    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
    }
    
    /**
     * Analytics Overview
     */
    public function overview(): void
    {
        $db = Database::getInstance();
        
        // Cache analytics stats for 10 minutes
        $stats = Cache::remember('admin_analytics_overview', function() use ($db) {
            $today = date('Y-m-d');
            $lastWeek = date('Y-m-d', strtotime('-7 days'));
            $lastMonth = date('Y-m-d', strtotime('-30 days'));
            
            return [
                'total_events' => $db->fetch("SELECT COUNT(*) as count FROM analytics_events")['count'],
                'events_today' => $db->fetch(
                    "SELECT COUNT(*) as count FROM analytics_events 
                     WHERE DATE(created_at) = ?",
                    [$today]
                )['count'],
                'events_week' => $db->fetch(
                    "SELECT COUNT(*) as count FROM analytics_events 
                     WHERE DATE(created_at) >= ?",
                    [$lastWeek]
                )['count'],
                'events_month' => $db->fetch(
                    "SELECT COUNT(*) as count FROM analytics_events 
                     WHERE DATE(created_at) >= ?",
                    [$lastMonth]
                )['count'],
                'unique_users_today' => $db->fetch(
                    "SELECT COUNT(DISTINCT user_id) as count FROM analytics_events 
                     WHERE DATE(created_at) = ?",
                    [$today]
                )['count']
            ];
        }, 600);
        
        // Get top events
        $topEvents = $db->fetchAll(
            "SELECT event_type, COUNT(*) as count 
             FROM analytics_events 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             GROUP BY event_type 
             ORDER BY count DESC 
             LIMIT 10"
        );
        
        // Get live traffic stats
        $liveStats = TrafficTracker::getLiveStats();
        
        // Get recent visitors (last 10 minutes)
        $recentVisitors = $db->fetchAll(
            "SELECT DISTINCT ae.ip_address, ae.browser, ae.platform, ae.country, 
                    ae.created_at, u.name as user_name
             FROM analytics_events ae
             LEFT JOIN users u ON ae.user_id = u.id
             WHERE ae.created_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)
             ORDER BY ae.created_at DESC
             LIMIT 20"
        );
        
        // Get conversion stats
        $conversionStats = [
            'registrations_today' => $db->fetch(
                "SELECT COUNT(*) as count FROM analytics_events 
                 WHERE event_type = 'user_register' AND DATE(created_at) = CURDATE()"
            )['count'] ?? 0,
            'logins_today' => $db->fetch(
                "SELECT COUNT(*) as count FROM analytics_events 
                 WHERE event_type = 'user_login' AND DATE(created_at) = CURDATE()"
            )['count'] ?? 0,
            'return_visits_today' => $db->fetch(
                "SELECT COUNT(*) as count FROM analytics_events 
                 WHERE event_type = 'return_visit' AND DATE(created_at) = CURDATE()"
            )['count'] ?? 0
        ];
        
        $this->view('admin/analytics/overview', [
            'title' => 'Analytics Overview',
            'stats' => $stats,
            'topEvents' => $topEvents,
            'liveStats' => $liveStats,
            'recentVisitors' => $recentVisitors,
            'conversionStats' => $conversionStats
        ]);
    }
    
    /**
     * Analytics Events List
     */
    public function events(): void
    {
        $db = Database::getInstance();
        
        $page = $_GET['page'] ?? 1;
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        $eventType = $_GET['event_type'] ?? null;
        $dateFrom = $_GET['date_from'] ?? null;
        $dateTo = $_GET['date_to'] ?? null;
        
        $where = [];
        $params = [];
        
        if ($eventType) {
            $where[] = "event_type = ?";
            $params[] = $eventType;
        }
        
        if ($dateFrom) {
            $where[] = "DATE(created_at) >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where[] = "DATE(created_at) <= ?";
            $params[] = $dateTo;
        }
        
        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Get events with user information
        $events = $db->fetchAll(
            "SELECT ae.id, ae.event_type, ae.user_id, ae.ip_address, ae.browser, 
                    ae.platform, ae.country, ae.metadata as event_data, ae.created_at,
                    u.name as user_name, u.email 
             FROM analytics_events ae 
             LEFT JOIN users u ON ae.user_id = u.id 
             $whereClause 
             ORDER BY ae.created_at DESC 
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );
        
        $total = $db->fetch(
            "SELECT COUNT(*) as count FROM analytics_events $whereClause",
            $params
        )['count'];
        
        // Get distinct event types for filter
        $eventTypes = $db->fetchAll(
            "SELECT DISTINCT event_type FROM analytics_events ORDER BY event_type"
        );
        
        $this->view('admin/analytics/events', [
            'title' => 'Analytics Events',
            'events' => $events,
            'eventTypes' => $eventTypes,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => ceil($total / $perPage),
            'filters' => [
                'event_type' => $eventType,
                'date_from' => $dateFrom,
                'date_to' => $dateTo
            ]
        ]);
    }
    
    /**
     * Analytics Reports
     */
    public function reports(): void
    {
        $db = Database::getInstance();
        
        // Get date range from request
        $dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        
        // Get daily event counts for the date range
        $dailyStats = $db->fetchAll(
            "SELECT DATE(created_at) as date, COUNT(*) as count 
             FROM analytics_events 
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY DATE(created_at) 
             ORDER BY date DESC",
            [$dateFrom, $dateTo]
        );
        
        // Get events by type
        $eventsByType = $db->fetchAll(
            "SELECT event_type, COUNT(*) as count 
             FROM analytics_events 
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY event_type 
             ORDER BY count DESC",
            [$dateFrom, $dateTo]
        );
        
        // Get browser statistics
        $browserStats = $db->fetchAll(
            "SELECT browser, COUNT(*) as count 
             FROM analytics_events 
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY browser 
             ORDER BY count DESC",
            [$dateFrom, $dateTo]
        );
        
        // Get geo location statistics
        $geoStats = $db->fetchAll(
            "SELECT country, COUNT(*) as count 
             FROM analytics_events 
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY country 
             ORDER BY count DESC 
             LIMIT 10",
            [$dateFrom, $dateTo]
        );
        
        // Get hourly statistics
        $hourlyStats = $db->fetchAll(
            "SELECT HOUR(created_at) as hour, COUNT(*) as count 
             FROM analytics_events 
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY HOUR(created_at) 
             ORDER BY hour",
            [$dateFrom, $dateTo]
        );
        
        $this->view('admin/analytics/reports', [
            'title' => 'Analytics Reports',
            'dailyStats' => $dailyStats,
            'eventsByType' => $eventsByType,
            'browserStats' => $browserStats,
            'geoStats' => $geoStats,
            'hourlyStats' => $hourlyStats,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo
            ]
        ]);
    }
    
    /**
     * Advanced Analytics Dashboard (Mixpanel-style)
     */
    public function export(): void
    {
        $db = Database::getInstance();
        
        // Handle AJAX requests for live data
        if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
            header('Content-Type: application/json');
            
            $action = $_GET['action'] ?? 'stats';
            $dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
            $dateTo = $_GET['date_to'] ?? date('Y-m-d');
            $timeframe = $_GET['timeframe'] ?? 'day'; // minute, hour, day
            
            switch ($action) {
                case 'stats':
                    echo json_encode($this->getAnalyticsStats($db, $dateFrom, $dateTo, $timeframe));
                    break;
                default:
                    echo json_encode(['error' => 'Invalid action']);
            }
            exit;
        }
        
        $this->view('admin/analytics/export', [
            'title' => 'Live Analytics Visual'
        ]);
    }
    
    /**
     * Get analytics statistics for dashboard
     */
    private function getAnalyticsStats($db, $dateFrom, $dateTo, $timeframe): array
    {
        // Validate timeframe input
        $allowedTimeframes = ['minute', 'hour', 'day'];
        if (!in_array($timeframe, $allowedTimeframes)) {
            $timeframe = 'day';
        }
        
        // Time grouping based on timeframe
        $timeGroup = match($timeframe) {
            'minute' => "DATE_FORMAT(created_at, '%Y-%m-%d %H:%i')",
            'hour' => "DATE_FORMAT(created_at, '%Y-%m-%d %H:00')",
            'day' => "DATE(created_at)",
            default => "DATE(created_at)"
        };
        
        // Timeline data
        $timeline = $db->fetchAll(
            "SELECT {$timeGroup} as time_period, COUNT(*) as count,
                    COUNT(DISTINCT user_id) as unique_users
             FROM analytics_events 
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY time_period
             ORDER BY time_period ASC",
            [$dateFrom, $dateTo]
        );
        
        // Event types distribution
        $eventTypes = $db->fetchAll(
            "SELECT event_type, COUNT(*) as count 
             FROM analytics_events 
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY event_type 
             ORDER BY count DESC",
            [$dateFrom, $dateTo]
        );
        
        // Browser distribution
        $browsers = $db->fetchAll(
            "SELECT browser, COUNT(*) as count 
             FROM analytics_events 
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY browser 
             ORDER BY count DESC",
            [$dateFrom, $dateTo]
        );
        
        // Platform distribution
        $platforms = $db->fetchAll(
            "SELECT platform, COUNT(*) as count 
             FROM analytics_events 
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY platform 
             ORDER BY count DESC",
            [$dateFrom, $dateTo]
        );
        
        // Geographic distribution
        $countries = $db->fetchAll(
            "SELECT country, COUNT(*) as count 
             FROM analytics_events 
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY country 
             ORDER BY count DESC 
             LIMIT 20",
            [$dateFrom, $dateTo]
        );
        
        // Hourly patterns
        $hourlyPattern = $db->fetchAll(
            "SELECT HOUR(created_at) as hour, COUNT(*) as count,
                    COUNT(DISTINCT user_id) as unique_users
             FROM analytics_events 
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY HOUR(created_at)
             ORDER BY hour",
            [$dateFrom, $dateTo]
        );
        
        // Day of week patterns
        $dayPattern = $db->fetchAll(
            "SELECT DAYNAME(created_at) as day_name, 
                    DAYOFWEEK(created_at) as day_num,
                    COUNT(*) as count
             FROM analytics_events 
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY day_name, day_num
             ORDER BY day_num",
            [$dateFrom, $dateTo]
        );
        
        // Conversion funnel
        $conversions = $db->fetchAll(
            "SELECT event_type, COUNT(*) as count 
             FROM analytics_events 
             WHERE DATE(created_at) BETWEEN ? AND ?
             AND event_type IN ('page_visit', 'user_register', 'user_login', 'return_visit')
             GROUP BY event_type",
            [$dateFrom, $dateTo]
        );
        
        // Real-time active users (last 5 minutes)
        $activeNow = $db->fetch(
            "SELECT COUNT(DISTINCT ip_address) as count 
             FROM analytics_events 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)"
        );
        
        return [
            'timeline' => $timeline,
            'eventTypes' => $eventTypes,
            'browsers' => $browsers,
            'platforms' => $platforms,
            'countries' => $countries,
            'hourlyPattern' => $hourlyPattern,
            'dayPattern' => $dayPattern,
            'conversions' => $conversions,
            'activeNow' => $activeNow['count'] ?? 0,
            'timeframe' => $timeframe
        ];
    }
    

}
