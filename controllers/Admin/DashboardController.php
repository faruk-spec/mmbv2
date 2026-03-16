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
        $this->requirePermission('dashboard');
    }
    
    /**
     * Admin dashboard home
     */
    public function index(): void
    {
        $db = Database::getInstance();

        $canUsers    = Auth::isAdmin() || Auth::hasPermissionGroup('users');
        $canLogs     = Auth::isAdmin() || Auth::hasPermissionGroup('logs');
        $canProjects = Auth::isAdmin() || Auth::hasPermissionGroup('projects');
        $canCodexPro = Auth::isAdmin() || Auth::hasPermissionGroup('codexpro');
        $canImgTxt   = Auth::isAdmin() || Auth::hasPermissionGroup('imgtxt');
        $canProShare = Auth::isAdmin() || Auth::hasPermissionGroup('proshare');

        // User stats — only if the user has access to the users section
        $stats = null;
        $chartData = [];
        $recentUsers = [];
        if ($canUsers) {
            $stats = Cache::remember('admin_dashboard_stats', function() use ($db) {
                return [
                    'total_users'        => $db->fetch("SELECT COUNT(*) as count FROM users")['count'],
                    'active_users'       => $db->fetch("SELECT COUNT(*) as count FROM users WHERE status = 'active'")['count'],
                    'new_users_today'    => $db->fetch(
                        "SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()"
                    )['count'],
                    'total_logins_today' => $db->fetch(
                        "SELECT COUNT(*) as count FROM activity_logs WHERE action = 'login' AND DATE(created_at) = CURDATE()"
                    )['count'],
                ];
            }, 300);

            $chartData = Cache::remember('admin_chart_data_' . date('Y-m-d'), function() use ($db) {
                $data = [];
                for ($i = 6; $i >= 0; $i--) {
                    $date  = date('Y-m-d', strtotime("-{$i} days"));
                    $count = $db->fetch(
                        "SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = ?",
                        [$date]
                    );
                    $data[] = [
                        'date'  => date('M d', strtotime($date)),
                        'count' => $count['count'],
                    ];
                }
                return $data;
            }, 3600);

            $recentUsers = $db->fetchAll(
                "SELECT id, name, email, role, status, created_at FROM users ORDER BY created_at DESC LIMIT 5"
            );
        }

        // Recent activity — only if the user has access to logs
        $recentActivity = [];
        if ($canLogs) {
            $recentActivity = Cache::remember('admin_recent_activity', function() use ($db) {
                return $db->fetchAll(
                    "SELECT al.*, u.name, u.email 
                     FROM activity_logs al 
                     LEFT JOIN users u ON al.user_id = u.id 
                     ORDER BY al.created_at DESC 
                     LIMIT 10"
                );
            }, 120);
        }

        // Projects list — only if the user has access to the projects section
        $projects = [];
        if ($canProjects) {
            $projects = require BASE_PATH . '/config/projects.php';
        }

        // Per-project stats — only fetch for projects the user can access
        $projectStats = [];
        if ($canCodexPro) {
            try {
                $projectStats['codexpro'] = [
                    'projects' => (int) $db->fetchColumn("SELECT COUNT(*) FROM codexpro_projects") ?? 0,
                    'snippets' => (int) $db->fetchColumn("SELECT COUNT(*) FROM codexpro_snippets") ?? 0,
                ];
            } catch (\Exception $e) {
                $projectStats['codexpro'] = ['projects' => 0, 'snippets' => 0];
            }
        }
        if ($canImgTxt) {
            try {
                $projectStats['imgtxt'] = [
                    'total_jobs' => (int) $db->fetchColumn("SELECT COUNT(*) FROM imgtxt_jobs") ?? 0,
                    'completed'  => (int) $db->fetchColumn("SELECT COUNT(*) FROM imgtxt_jobs WHERE status = 'completed'") ?? 0,
                ];
            } catch (\Exception $e) {
                $projectStats['imgtxt'] = ['total_jobs' => 0, 'completed' => 0];
            }
        }
        if ($canProShare) {
            try {
                $projectStats['proshare'] = [
                    'files' => (int) $db->fetchColumn("SELECT COUNT(*) FROM proshare_files") ?? 0,
                    'texts' => (int) $db->fetchColumn("SELECT COUNT(*) FROM proshare_texts") ?? 0,
                ];
            } catch (\Exception $e) {
                $projectStats['proshare'] = ['files' => 0, 'texts' => 0];
            }
        }

        $this->view('admin/dashboard', [
            'title'          => 'Admin Dashboard',
            'stats'          => $stats,
            'recentActivity' => $recentActivity,
            'recentUsers'    => $recentUsers,
            'projects'       => $projects,
            'chartData'      => $chartData,
            'projectStats'   => $projectStats,
            'canUsers'       => $canUsers,
            'canLogs'        => $canLogs,
            'canProjects'    => $canProjects,
            'canCodexPro'    => $canCodexPro,
            'canImgTxt'      => $canImgTxt,
            'canProShare'    => $canProShare,
        ]);
    }
}
