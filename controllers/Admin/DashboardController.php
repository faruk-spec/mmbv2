<?php
/**
 * Admin Dashboard Controller
 * 
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;
use Core\Auth;
use Core\Cache;

class DashboardController extends BaseController
{
    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
    }
    
    /**
     * Admin dashboard home
     */
    public function index(): void
    {
        $db = Database::getInstance();
        
        // Cache statistics for 5 minutes
        $stats = Cache::remember('admin_dashboard_stats', function() use ($db) {
            return [
                'total_users' => $db->fetch("SELECT COUNT(*) as count FROM users")['count'],
                'active_users' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE status = 'active'")['count'],
                'new_users_today' => $db->fetch(
                    "SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()"
                )['count'],
                'total_logins_today' => $db->fetch(
                    "SELECT COUNT(*) as count FROM activity_logs WHERE action = 'login' AND DATE(created_at) = CURDATE()"
                )['count']
            ];
        }, 300);
        
        // Cache recent activity for 2 minutes
        $recentActivity = Cache::remember('admin_recent_activity', function() use ($db) {
            return $db->fetchAll(
                "SELECT al.*, u.name, u.email 
                 FROM activity_logs al 
                 LEFT JOIN users u ON al.user_id = u.id 
                 ORDER BY al.created_at DESC 
                 LIMIT 10"
            );
        }, 120);
        
        // Get recent users (always fresh)
        $recentUsers = $db->fetchAll(
            "SELECT * FROM users ORDER BY created_at DESC LIMIT 5"
        );
        
        // Get projects
        $projects = require BASE_PATH . '/config/projects.php';
        
        // Cache chart data for 1 hour
        $chartData = Cache::remember('admin_chart_data_' . date('Y-m-d'), function() use ($db) {
            $data = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-{$i} days"));
                $count = $db->fetch(
                    "SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = ?",
                    [$date]
                );
                $data[] = [
                    'date' => date('M d', strtotime($date)),
                    'count' => $count['count']
                ];
            }
            return $data;
        }, 3600);
        
        $this->view('admin/dashboard', [
            'title' => 'Admin Dashboard',
            'stats' => $stats,
            'recentActivity' => $recentActivity,
            'recentUsers' => $recentUsers,
            'projects' => $projects,
            'chartData' => $chartData
        ]);
    }
}
