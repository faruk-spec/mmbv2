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

        // Broad permission checks used to gate data fetches + view sections
        $canUsers        = Auth::isAdmin() || Auth::hasPermissionGroup('users');
        $canLogs         = Auth::isAdmin() || Auth::hasPermissionGroup('logs');
        $canProjects     = Auth::isAdmin() || Auth::hasPermissionGroup('projects');
        $canCodexPro     = Auth::isAdmin() || Auth::hasPermissionGroup('codexpro');
        $canProShare     = Auth::isAdmin() || Auth::hasPermissionGroup('proshare');
        $canConvertX     = Auth::isAdmin() || Auth::hasPermissionGroup('convertx');
        $canBillX        = Auth::isAdmin() || Auth::hasPermissionGroup('billx');
        $canWhatsApp     = Auth::isAdmin() || Auth::hasPermissionGroup('whatsapp');
        $canQr           = Auth::isAdmin() || Auth::hasPermissionGroup('qr');
        $canSecurity     = Auth::isAdmin() || Auth::hasPermissionGroup('security');
        $canPlatformPlans= Auth::isAdmin() || Auth::hasPermissionGroup('platform_plans');
        $canFormX        = Auth::isAdmin() || Auth::hasPermissionGroup('formx');

        // Determine whether the user has ANY visible module access
        $hasAnyAccess = $canUsers || $canLogs || $canProjects || $canCodexPro
            || $canProShare || $canConvertX || $canBillX || $canWhatsApp || $canQr
            || $canSecurity || $canPlatformPlans || $canFormX;

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
                    'projects' => (int) ($db->fetchColumn("SELECT COUNT(*) FROM codexpro_projects") ?: 0),
                    'snippets' => (int) ($db->fetchColumn("SELECT COUNT(*) FROM codexpro_snippets") ?: 0),
                ];
            } catch (\Exception $e) {
                $projectStats['codexpro'] = ['projects' => 0, 'snippets' => 0];
            }
        }
        if ($canProShare) {
            try {
                $projectStats['proshare'] = [
                    'files' => (int) ($db->fetchColumn("SELECT COUNT(*) FROM proshare_files") ?: 0),
                    'texts' => (int) ($db->fetchColumn("SELECT COUNT(*) FROM proshare_texts") ?: 0),
                ];
            } catch (\Exception $e) {
                $projectStats['proshare'] = ['files' => 0, 'texts' => 0];
            }
        }
        if ($canFormX) {
            try {
                $projectStats['formx'] = [
                    'forms'       => (int) ($db->fetchColumn("SELECT COUNT(*) FROM formx_forms") ?: 0),
                    'submissions' => (int) ($db->fetchColumn("SELECT COALESCE(SUM(submissions_count),0) FROM formx_forms") ?: 0),
                ];
            } catch (\Exception $e) {
                $projectStats['formx'] = ['forms' => 0, 'submissions' => 0];
            }
        }

        $this->view('admin/dashboard', [
            'title'           => 'Admin Dashboard',
            'stats'           => $stats,
            'recentActivity'  => $recentActivity,
            'recentUsers'     => $recentUsers,
            'projects'        => $projects,
            'chartData'       => $chartData,
            'projectStats'    => $projectStats,
            'canUsers'        => $canUsers,
            'canLogs'         => $canLogs,
            'canProjects'     => $canProjects,
            'canCodexPro'     => $canCodexPro,
            'canProShare'     => $canProShare,
            'canConvertX'     => $canConvertX,
            'canBillX'        => $canBillX,
            'canWhatsApp'     => $canWhatsApp,
            'canQr'           => $canQr,
            'canSecurity'     => $canSecurity,
            'canPlatformPlans'=> $canPlatformPlans,
            'canFormX'        => $canFormX,
            'hasAnyAccess'    => $hasAnyAccess,
        ]);
    }

    /**
     * GET /admin/api/live-stats
     * JSON endpoint polled by the admin dashboard every 15 seconds.
     */
    public function liveStats(): void
    {
        try {
            $db = Database::getInstance();

            // Active sessions in the last 15 minutes (users with recent activity)
            $onlineNow = 0;
            try {
                $onlineNow = (int) $db->fetchColumn(
                    "SELECT COUNT(DISTINCT user_id) FROM user_sessions WHERE is_active = 1 AND expires_at > NOW()"
                );
            } catch (\Exception $e) {}

            // Logins today
            $loginsToday = 0;
            try {
                $loginsToday = (int) $db->fetchColumn(
                    "SELECT COUNT(*) FROM activity_logs WHERE action = 'login' AND DATE(created_at) = CURDATE()"
                );
            } catch (\Exception $e) {}

            // Registrations today
            $registrationsToday = 0;
            try {
                $registrationsToday = (int) $db->fetchColumn(
                    "SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()"
                );
            } catch (\Exception $e) {}

            // Recent activity (last 5 events)
            $recentActivity = [];
            try {
                $rows = $db->fetchAll(
                    "SELECT al.action, al.created_at, u.name as user_name
                     FROM activity_logs al
                     LEFT JOIN users u ON u.id = al.user_id
                     ORDER BY al.created_at DESC LIMIT 5"
                );
                foreach ($rows as $row) {
                    $recentActivity[] = [
                        'action'    => $row['action'],
                        'user'      => $row['user_name'] ?? 'Unknown',
                        'time'      => $row['created_at'],
                    ];
                }
            } catch (\Exception $e) {}

            $this->json([
                'success'             => true,
                'online_now'          => $onlineNow,
                'logins_today'        => $loginsToday,
                'registrations_today' => $registrationsToday,
                'recent_activity'     => $recentActivity,
                'ts'                  => time(),
            ]);
        } catch (\Exception $e) {
            $this->json(['success' => false], 500);
        }
    }
}
