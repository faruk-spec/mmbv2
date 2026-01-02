<?php
/**
 * CodeXPro Admin Controller
 * 
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;
use Core\Auth;
use Core\Logger;
use Core\Cache;

class CodeXProAdminController extends BaseController
{
    private $projectDb;
    private $mainDb;
    private $mainDbName;
    private $projectDbName;
    
    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
        $this->projectDb = Database::projectConnection('codexpro');
        $this->mainDb = Database::getInstance();
        
        // Get main database name from config
        $mainConfig = require BASE_PATH . '/config/database.php';
        $this->mainDbName = $mainConfig['database'];
        
        // Get project database name
        $projectConfig = require BASE_PATH . '/projects/codexpro/config.php';
        $this->projectDbName = $projectConfig['database']['database'] ?? 'codexpro';
    }
    
    /**
     * Overview dashboard
     */
    public function overview(): void
    {
        // Cache statistics for 5 minutes
        $stats = Cache::remember('codexpro_stats', function() {
            return [
                'total_projects' => $this->projectDb->fetch("SELECT COUNT(*) as count FROM projects")['count'] ?? 0,
                'total_snippets' => $this->projectDb->fetch("SELECT COUNT(*) as count FROM snippets")['count'] ?? 0,
                'total_templates' => $this->projectDb->fetch("SELECT COUNT(*) as count FROM templates")['count'] ?? 0,
                'active_users' => $this->projectDb->fetch("SELECT COUNT(DISTINCT user_id) as count FROM projects")['count'] ?? 0,
            ];
        }, 300);
        
        // Get recent projects from project database
        $recentProjects = $this->projectDb->fetchAll(
            "SELECT * FROM projects 
             ORDER BY updated_at DESC 
             LIMIT 10"
        );
        
        // Get user info from main database if we have projects
        if (!empty($recentProjects)) {
            $userIds = array_unique(array_column($recentProjects, 'user_id'));
            $userIds = array_filter($userIds); // Remove nulls
            
            if (!empty($userIds)) {
                $placeholders = implode(',', array_fill(0, count($userIds), '?'));
                $users = $this->mainDb->fetchAll(
                    "SELECT id, name, email FROM users WHERE id IN ($placeholders)",
                    $userIds
                );
                
                // Create user lookup array
                $userLookup = [];
                foreach ($users as $user) {
                    $userLookup[$user['id']] = $user;
                }
                
                // Merge user data into projects
                foreach ($recentProjects as &$project) {
                    $userId = $project['user_id'] ?? null;
                    if ($userId && isset($userLookup[$userId])) {
                        $project['user_name'] = $userLookup[$userId]['name'];
                        $project['email'] = $userLookup[$userId]['email'];
                    } else {
                        $project['user_name'] = 'Unknown';
                        $project['email'] = '';
                    }
                }
            }
        }
        
        // Cache storage usage for 10 minutes
        $storageUsageMB = Cache::remember('codexpro_storage', function() {
            $storageUsage = $this->projectDb->fetch(
                "SELECT SUM(CHAR_LENGTH(html_content) + CHAR_LENGTH(css_content) + CHAR_LENGTH(js_content)) as bytes 
                 FROM projects"
            );
            return round(($storageUsage['bytes'] ?? 0) / (1024 * 1024), 2);
        }, 600);
        
        // Cache trend data for 1 hour (changes daily)
        $trendData = Cache::remember('codexpro_trend_' . date('Y-m-d'), function() {
            $data = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-{$i} days"));
                $count = $this->projectDb->fetch(
                    "SELECT COUNT(*) as count FROM projects WHERE DATE(created_at) = ?",
                    [$date]
                );
                $data[] = [
                    'date' => date('M d', strtotime($date)),
                    'count' => $count['count'] ?? 0
                ];
            }
            return $data;
        }, 3600);
        
        $this->view('admin/projects/codexpro/overview', [
            'title' => 'CodeXPro Admin - Overview',
            'stats' => $stats,
            'recentProjects' => $recentProjects,
            'storageUsageMB' => $storageUsageMB,
            'trendData' => $trendData
        ]);
    }
    
    /**
     * Settings management
     */
    public function settings(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updateSettings();
            return;
        }
        
        // Get current settings
        $settings = $this->projectDb->fetch("SELECT * FROM settings WHERE id = 1") ?? [
            'max_project_size_kb' => 5000,
            'max_projects_per_user' => 50,
            'auto_save_interval' => 30,
            'default_theme' => 'dark',
            'enable_export' => 1,
            'enable_templates' => 1,
            'enable_collaboration' => 0
        ];
        
        $this->view('admin/projects/codexpro/settings', [
            'title' => 'CodeXPro Admin - Settings',
            'settings' => $settings
        ]);
    }
    
    /**
     * Update settings
     */
    private function updateSettings(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/projects/codexpro/settings');
            return;
        }
        
        // Settings to update (key-value structure)
        $settings = [
            'max_project_size' => (int)($_POST['max_project_size_kb'] ?? 5000) * 1024, // Convert KB to bytes
            'max_projects_per_user' => (int)($_POST['max_projects_per_user'] ?? 50),
            'auto_save_interval' => (int)($_POST['auto_save_interval'] ?? 30),
            'default_theme' => $_POST['default_theme'] ?? 'dark',
            'enable_export' => isset($_POST['enable_export']) ? '1' : '0',
            'enable_templates' => isset($_POST['enable_templates']) ? '1' : '0',
            'enable_collaboration' => isset($_POST['enable_collaboration']) ? '1' : '0'
        ];
        
        // Update each setting using key-value structure
        foreach ($settings as $key => $value) {
            $existing = $this->projectDb->fetch("SELECT id FROM settings WHERE `key` = ?", [$key]);
            
            if ($existing) {
                $this->projectDb->update('settings', [
                    'value' => $value,
                    'updated_at' => date('Y-m-d H:i:s')
                ], '`key` = ?', [$key]);
            } else {
                $this->projectDb->insert('settings', [
                    'key' => $key,
                    'value' => $value,
                    'type' => is_numeric($value) ? 'integer' : 'string',
                    'is_system' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
        
        // Invalidate cached statistics after settings update
        Cache::delete('codexpro_stats');
        Cache::delete('codexpro_storage');
        
        Logger::activity(Auth::id(), 'codexpro_settings_updated', $settings);
        
        $this->flash('success', 'Settings updated successfully.');
        $this->redirect('/admin/projects/codexpro/settings');
    }
    
    /**
     * User management
     */
    public function users(): void
    {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // Get all users from main DB
        $users = $this->mainDb->fetchAll(
            "SELECT id, name, email, status, created_at
             FROM users
             ORDER BY id DESC
             LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
        
        // Get project counts for users from project DB
        if (!empty($users)) {
            $userIds = array_column($users, 'id');
            $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
            $projectCounts = $this->projectDb->fetchAll(
                "SELECT user_id, COUNT(*) as project_count, MAX(updated_at) as last_project_update
                 FROM projects
                 WHERE user_id IN ($placeholders)
                 GROUP BY user_id",
                $userIds
            );
            
            // Create project count lookup
            $projectLookup = [];
            foreach ($projectCounts as $pc) {
                $projectLookup[$pc['user_id']] = $pc;
            }
            
            // Merge project data into users
            foreach ($users as &$user) {
                if (isset($projectLookup[$user['id']])) {
                    $user['project_count'] = $projectLookup[$user['id']]['project_count'];
                    $user['last_project_update'] = $projectLookup[$user['id']]['last_project_update'];
                } else {
                    $user['project_count'] = 0;
                    $user['last_project_update'] = null;
                }
            }
            unset($user); // Break reference
        }
        
        // Get total count from main DB
        $totalCount = $this->mainDb->fetch(
            "SELECT COUNT(*) as count FROM users"
        )['count'];
        
        $totalPages = ceil($totalCount / $perPage);
        
        $this->view('admin/projects/codexpro/users', [
            'title' => 'CodeXPro Admin - Users',
            'users' => $users,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount
        ]);
    }
    
    /**
     * Templates management
     */
    public function templates(): void
    {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // Get all templates from project DB
        $templates = $this->projectDb->fetchAll(
            "SELECT * FROM templates
             ORDER BY created_at DESC
             LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
        
        // Get user info for templates from main DB
        if (!empty($templates)) {
            $userIds = array_unique(array_filter(array_column($templates, 'user_id')));
            if (!empty($userIds)) {
                $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
                $users = $this->mainDb->fetchAll(
                    "SELECT id, name FROM users WHERE id IN ($placeholders)",
                    $userIds
                );
                // Create user lookup array
                $userLookup = [];
                foreach ($users as $user) {
                    $userLookup[$user['id']] = $user;
                }
                // Merge user data into templates
                foreach ($templates as &$template) {
                    if (isset($template['user_id']) && isset($userLookup[$template['user_id']])) {
                        $template['creator_name'] = $userLookup[$template['user_id']]['name'];
                    } else {
                        $template['creator_name'] = 'Unknown';
                    }
                }
                unset($template); // Break reference
            }
        }
        
        // Get total count
        $totalCount = $this->projectDb->fetch("SELECT COUNT(*) as count FROM templates")['count'];
        $totalPages = ceil($totalCount / $perPage);
        
        $this->view('admin/projects/codexpro/templates', [
            'title' => 'CodeXPro Admin - Templates',
            'templates' => $templates,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount
        ]);
    }
    
    /**
     * Delete template
     */
    public function deleteTemplate(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/projects/codexpro/templates');
            return;
        }
        
        $id = (int)($_POST['template_id'] ?? 0);
        
        if ($id > 0) {
            $this->projectDb->delete('templates', 'id = ?', [$id]);
            Logger::activity(Auth::id(), 'codexpro_template_deleted', ['template_id' => $id]);
            $this->flash('success', 'Template deleted successfully.');
        } else {
            $this->flash('error', 'Invalid template ID.');
        }
        
        $this->redirect('/admin/projects/codexpro/templates');
    }
    
    /**
     * Toggle template status
     */
    public function toggleTemplate(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/projects/codexpro/templates');
            return;
        }
        
        $id = (int)($_POST['template_id'] ?? 0);
        
        if ($id > 0) {
            $template = $this->projectDb->fetch("SELECT is_active FROM templates WHERE id = ?", [$id]);
            if ($template) {
                $newStatus = $template['is_active'] ? 0 : 1;
                $this->projectDb->update('templates', ['is_active' => $newStatus], 'id = ?', [$id]);
                Logger::activity(Auth::id(), 'codexpro_template_toggled', ['template_id' => $id, 'status' => $newStatus]);
                $this->flash('success', 'Template status updated.');
            }
        }
        
        $this->redirect('/admin/projects/codexpro/templates');
    }
}
