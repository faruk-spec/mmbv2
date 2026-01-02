<?php
/**
 * ProShare Admin Controller
 * 
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;
use Core\Auth;
use Core\Logger;

class ProShareAdminController extends BaseController
{
    private $projectDb;
    private $mainDb;
    private $mainDbName;
    private $projectDbName;
    
    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
        $this->projectDb = Database::projectConnection('proshare');
        $this->mainDb = Database::getInstance();
        
        // Get main database name from config
        $mainConfig = require BASE_PATH . '/config/database.php';
        $this->mainDbName = $mainConfig['database'];
        
        // Get project database name
        $projectConfig = require BASE_PATH . '/projects/proshare/config.php';
        $this->projectDbName = $projectConfig['database']['database'] ?? 'proshare';
    }
    
    /**
     * Overview dashboard
     */
    public function overview(): void
    {
        // Get statistics
        $stats = [
            'total_files' => $this->projectDb->fetch("SELECT COUNT(*) as count FROM files")['count'] ?? 0,
            'total_texts' => $this->projectDb->fetch("SELECT COUNT(*) as count FROM text_shares")['count'] ?? 0,
            'total_downloads' => $this->getDownloadCount(),
            'total_views' => $this->getViewCount(),
            'active_files' => $this->projectDb->fetch("SELECT COUNT(*) as count FROM files WHERE expires_at > NOW() OR expires_at IS NULL")['count'] ?? 0,
            'active_users' => $this->projectDb->fetch("SELECT COUNT(DISTINCT user_id) as count FROM files WHERE user_id IS NOT NULL")['count'] ?? 0,
        ];
        
        // Get storage usage
        $storageUsageGB = $this->getStorageUsage();
        
        // Get recent files
        $recentFiles = $this->projectDb->fetchAll(
            "SELECT * FROM files 
             ORDER BY created_at DESC 
             LIMIT 10"
        );
        
        // Get user info for recent files
        if (!empty($recentFiles)) {
            $userIds = array_values(array_unique(array_filter(array_column($recentFiles, 'user_id'), function($id) {
                return $id !== null && $id !== '';
            })));
            if (!empty($userIds)) {
                $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
                $users = $this->mainDb->fetchAll(
                    "SELECT id, name, email FROM users WHERE id IN ($placeholders)",
                    $userIds
                );
                $userLookup = [];
                foreach ($users as $user) {
                    $userLookup[$user['id']] = $user;
                }
                foreach ($recentFiles as &$file) {
                    if (isset($file['user_id']) && isset($userLookup[$file['user_id']])) {
                        $file['user_name'] = $userLookup[$file['user_id']]['name'];
                        $file['email'] = $userLookup[$file['user_id']]['email'];
                    } else {
                        $file['user_name'] = 'Anonymous';
                        $file['email'] = '';
                    }
                }
                unset($file);
            } else {
                // No valid user IDs, set all as anonymous
                foreach ($recentFiles as &$file) {
                    $file['user_name'] = 'Anonymous';
                    $file['email'] = '';
                }
                unset($file);
            }
        }
        
        // Get recent texts
        $recentTexts = $this->projectDb->fetchAll(
            "SELECT * FROM text_shares 
             ORDER BY created_at DESC 
             LIMIT 10"
        );
        
        // Get user info for recent texts
        if (!empty($recentTexts)) {
            $userIds = array_values(array_unique(array_filter(array_column($recentTexts, 'user_id'), function($id) {
                return $id !== null && $id !== '';
            })));
            if (!empty($userIds)) {
                $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
                $users = $this->mainDb->fetchAll(
                    "SELECT id, name, email FROM users WHERE id IN ($placeholders)",
                    $userIds
                );
                $userLookup = [];
                foreach ($users as $user) {
                    $userLookup[$user['id']] = $user;
                }
                foreach ($recentTexts as &$text) {
                    if (isset($text['user_id']) && isset($userLookup[$text['user_id']])) {
                        $text['user_name'] = $userLookup[$text['user_id']]['name'];
                        $text['email'] = $userLookup[$text['user_id']]['email'];
                    } else {
                        $text['user_name'] = 'Anonymous';
                        $text['email'] = '';
                    }
                }
                unset($text);
            } else {
                // No valid user IDs, set all as anonymous
                foreach ($recentTexts as &$text) {
                    $text['user_name'] = 'Anonymous';
                    $text['email'] = '';
                }
                unset($text);
            }
        }
        
        // Sharing trend (last 7 days)
        $trendData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $fileCount = $this->projectDb->fetch(
                "SELECT COUNT(*) as count FROM files WHERE DATE(created_at) = ?",
                [$date]
            )['count'] ?? 0;
            $textCount = $this->projectDb->fetch(
                "SELECT COUNT(*) as count FROM text_shares WHERE DATE(created_at) = ?",
                [$date]
            )['count'] ?? 0;
            
            $trendData[] = [
                'date' => date('M d', strtotime($date)),
                'files' => $fileCount,
                'texts' => $textCount,
                'total' => $fileCount + $textCount
            ];
        }
        
        $this->view('admin/projects/proshare/overview', [
            'title' => 'ProShare Admin - Overview',
            'stats' => $stats,
            'storageUsageGB' => $storageUsageGB,
            'recentFiles' => $recentFiles,
            'recentTexts' => $recentTexts,
            'trendData' => $trendData
        ], 'admin');
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
        
        // Get current settings from key-value table
        $settingsRows = $this->projectDb->fetchAll("SELECT `key`, `value` FROM settings");
        $settings = [];
        foreach ($settingsRows as $row) {
            $settings[$row['key']] = $row['value'];
        }
        
        // Apply defaults if not set
        $settings['max_file_size'] = $settings['max_file_size'] ?? 524288000;
        $settings['max_file_size_mb'] = round(($settings['max_file_size'] ?? 524288000) / 1048576);
        $settings['default_expiry_hours'] = $settings['default_expiry_hours'] ?? 24;
        $settings['max_expiry_days'] = $settings['max_expiry_days'] ?? 30;
        $settings['enable_password_protection'] = $settings['enable_password_protection'] ?? 1;
        $settings['enable_self_destruct'] = $settings['enable_self_destruct'] ?? 1;
        $settings['enable_compression'] = $settings['enable_compression'] ?? 1;
        $settings['enable_anonymous_sharing'] = $settings['enable_anonymous_sharing'] ?? $settings['enable_anonymous_upload'] ?? 1;
        $settings['require_email_verification'] = $settings['require_email_verification'] ?? 0;
        
        $this->view('admin/projects/proshare/settings', [
            'title' => 'ProShare Admin - Settings',
            'settings' => $settings,
            'csrf_token' => $_SESSION['csrf_token'] ?? ''
        ], 'admin');
    }
    
    /**
     * Update settings
     */
    private function updateSettings(): void
    {
        // Check both csrf_token and _csrf_token for compatibility
        $csrfToken = $_POST['csrf_token'] ?? $_POST['_csrf_token'] ?? '';
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/projects/proshare/settings');
            return;
        }
        
        // Settings to update (key-value structure)
        $settings = [
            'max_file_size' => (int)($_POST['max_file_size_mb'] ?? 100) * 1048576, // Convert MB to bytes
            'max_expiry_days' => (int)($_POST['max_expiry_days'] ?? 30),
            'default_expiry_hours' => (int)($_POST['default_expiry_hours'] ?? 24),
            'enable_password_protection' => isset($_POST['enable_password_protection']) ? '1' : '0',
            'enable_self_destruct' => isset($_POST['enable_self_destruct']) ? '1' : '0',
            'enable_compression' => isset($_POST['enable_compression']) ? '1' : '0',
            'enable_anonymous_sharing' => isset($_POST['enable_anonymous_sharing']) ? '1' : '0',
            'require_email_verification' => isset($_POST['require_email_verification']) ? '1' : '0'
        ];
        
        // Update each setting using key-value structure
        foreach ($settings as $key => $value) {
            $existing = $this->projectDb->fetch("SELECT id FROM settings WHERE `key` = ?", [$key]);
            
            if ($existing) {
                // Check if updated_at column exists
                try {
                    $this->projectDb->update('settings', [
                        'value' => $value,
                        'updated_at' => date('Y-m-d H:i:s')
                    ], '`key` = ?', [$key]);
                } catch (\PDOException $e) {
                    // If updated_at doesn't exist, update without it
                    $this->projectDb->update('settings', [
                        'value' => $value
                    ], '`key` = ?', [$key]);
                }
            } else {
                // Check if created_at column exists
                try {
                    $this->projectDb->insert('settings', [
                        'key' => $key,
                        'value' => $value,
                        'type' => is_numeric($value) ? 'integer' : 'string',
                        'is_system' => 1,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                } catch (\PDOException $e) {
                    // If created_at doesn't exist, insert without it
                    $this->projectDb->insert('settings', [
                        'key' => $key,
                        'value' => $value,
                        'type' => is_numeric($value) ? 'integer' : 'string',
                        'is_system' => 1
                    ]);
                }
            }
        }
        
        Logger::activity(Auth::id(), 'proshare_settings_updated', $settings);
        
        $this->flash('success', 'Settings updated successfully.');
        $this->redirect('/admin/projects/proshare/settings');
    }
    
    /**
     * Files management
     */
    public function files(): void
    {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $status = $_GET['status'] ?? 'all';
        
        // Build query - without alias
        $whereClause = '';
        $params = [];
        
        if ($status === 'active') {
            $whereClause = 'WHERE (expires_at > NOW() OR expires_at IS NULL OR expires_at = "0000-00-00 00:00:00")';
        } elseif ($status === 'expired') {
            $whereClause = 'WHERE expires_at < NOW() AND expires_at != "0000-00-00 00:00:00"';
        }
        
        // Get files from project DB
        $files = $this->projectDb->fetchAll(
            "SELECT * FROM files 
             $whereClause
             ORDER BY created_at DESC 
             LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
        
        // Get user info for files from main DB
        if (!empty($files)) {
            $userIds = array_values(array_unique(array_filter(array_column($files, 'user_id'), function($id) {
                return $id !== null && $id !== '';
            })));
            if (!empty($userIds)) {
                $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
                $users = $this->mainDb->fetchAll(
                    "SELECT id, name, email FROM users WHERE id IN ($placeholders)",
                    $userIds
                );
                // Create user lookup array
                $userLookup = [];
                foreach ($users as $user) {
                    $userLookup[$user['id']] = $user;
                }
                // Merge user data into files
                foreach ($files as &$file) {
                    if (isset($file['user_id']) && isset($userLookup[$file['user_id']])) {
                        $file['user_name'] = $userLookup[$file['user_id']]['name'];
                        $file['email'] = $userLookup[$file['user_id']]['email'];
                    } else {
                        $file['user_name'] = 'Anonymous';
                        $file['email'] = '';
                    }
                }
                unset($file); // Break reference
            } else {
                // No valid user IDs, set all as anonymous
                foreach ($files as &$file) {
                    $file['user_name'] = 'Anonymous';
                    $file['email'] = '';
                }
                unset($file);
            }
        }
        
        // Get total count
        $totalCount = $this->projectDb->fetch(
            "SELECT COUNT(*) as count FROM files $whereClause"
        )['count'];
        
        $totalPages = ceil($totalCount / $perPage);
        
        $this->view('admin/projects/proshare/files', [
            'title' => 'ProShare Admin - Files',
            'files' => $files,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount,
            'currentStatus' => $status,
            'csrf_token' => $_SESSION['csrf_token'] ?? ''
        ], 'admin');
    }
    
    /**
     * Text shares management
     */
    public function texts(): void
    {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $status = $_GET['status'] ?? 'all';
        
        // Build query - without alias or with proper checking
        $whereClause = '';
        if ($status === 'active') {
            // Check if expires_at column exists, if not, show all
            $whereClause = 'WHERE (expires_at > NOW() OR expires_at IS NULL OR expires_at = "0000-00-00 00:00:00")';
        } elseif ($status === 'expired') {
            $whereClause = 'WHERE expires_at < NOW() AND expires_at != "0000-00-00 00:00:00"';
        }
        
        // Get text shares from project DB
        $texts = $this->projectDb->fetchAll(
            "SELECT * FROM text_shares 
             $whereClause
             ORDER BY created_at DESC 
             LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
        
        // Get user info for text shares from main DB
        if (!empty($texts)) {
            $userIds = array_unique(array_filter(array_column($texts, 'user_id')));
            if (!empty($userIds)) {
                $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
                $users = $this->mainDb->fetchAll(
                    "SELECT id, name, email FROM users WHERE id IN ($placeholders)",
                    $userIds
                );
                // Create user lookup array
                $userLookup = [];
                foreach ($users as $user) {
                    $userLookup[$user['id']] = $user;
                }
                // Merge user data into texts
                foreach ($texts as &$text) {
                    if (isset($text['user_id']) && isset($userLookup[$text['user_id']])) {
                        $text['user_name'] = $userLookup[$text['user_id']]['name'];
                        $text['email'] = $userLookup[$text['user_id']]['email'];
                    } else {
                        $text['user_name'] = 'Anonymous';
                        $text['email'] = '';
                    }
                }
                unset($text); // Break reference
            }
        }
        
        // Get total count
        $totalCount = $this->projectDb->fetch(
            "SELECT COUNT(*) as count FROM text_shares $whereClause"
        )['count'];
        
        $totalPages = ceil($totalCount / $perPage);
        
        $this->view('admin/projects/proshare/texts', [
            'title' => 'ProShare Admin - Text Shares',
            'texts' => $texts,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount,
            'currentStatus' => $status,
            'csrf_token' => $_SESSION['csrf_token'] ?? ''
        ], 'admin');
    }
    
    /**
     * Notifications management
     */
    public function notifications(): void
    {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 30;
        $offset = ($page - 1) * $perPage;
        
        // Get notifications from project DB
        $notifications = $this->projectDb->fetchAll(
            "SELECT * FROM notifications 
             ORDER BY created_at DESC 
             LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
        
        // Get user info for notifications from main DB
        if (!empty($notifications)) {
            $userIds = array_values(array_unique(array_filter(array_column($notifications, 'user_id'), function($id) {
                return $id !== null && $id !== '';
            })));
            if (!empty($userIds)) {
                $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
                $users = $this->mainDb->fetchAll(
                    "SELECT id, name, email FROM users WHERE id IN ($placeholders)",
                    $userIds
                );
                // Create user lookup array
                $userLookup = [];
                foreach ($users as $user) {
                    $userLookup[$user['id']] = $user;
                }
                // Merge user data into notifications
                foreach ($notifications as &$notification) {
                    if (isset($notification['user_id']) && isset($userLookup[$notification['user_id']])) {
                        $notification['user_name'] = $userLookup[$notification['user_id']]['name'];
                        $notification['email'] = $userLookup[$notification['user_id']]['email'];
                    } else {
                        $notification['user_name'] = 'System';
                        $notification['email'] = '';
                    }
                }
                unset($notification); // Break reference
            }
        }
        
        // Get total count
        $totalCount = $this->projectDb->fetch("SELECT COUNT(*) as count FROM notifications")['count'];
        $totalPages = ceil($totalCount / $perPage);
        
        // Get notification stats
        $stats = [
            'total' => $totalCount,
            'unread' => $this->projectDb->fetch("SELECT COUNT(*) as count FROM notifications WHERE is_read = 0")['count'] ?? 0,
            'download_alerts' => $this->projectDb->fetch("SELECT COUNT(*) as count FROM notifications WHERE type = 'download'")['count'] ?? 0,
            'expiry_warnings' => $this->projectDb->fetch("SELECT COUNT(*) as count FROM notifications WHERE type = 'expiry_warning'")['count'] ?? 0,
        ];
        
        $this->view('admin/projects/proshare/notifications', [
            'title' => 'ProShare Admin - Notifications',
            'notifications' => $notifications,
            'stats' => $stats,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ], 'admin');
    }
    
    /**
     * Delete file
     */
    public function deleteFile(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/projects/proshare/files');
            return;
        }
        
        $id = (int)($_POST['file_id'] ?? 0);
        
        if ($id > 0) {
            // Get file info for logging and physical deletion
            $file = $this->projectDb->fetch("SELECT * FROM files WHERE id = ?", [$id]);
            
            if ($file) {
                // Delete physical file if exists
                $filePath = BASE_PATH . '/storage/proshare/' . $file['file_path'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                
                // Delete from database
                $this->projectDb->delete('files', 'id = ?', [$id]);
                
                Logger::activity(Auth::id(), 'proshare_file_deleted', ['file_id' => $id, 'filename' => $file['original_filename']]);
                $this->flash('success', 'File deleted successfully.');
            }
        }
        
        $this->redirect('/admin/projects/proshare/files');
    }
    
    /**
     * Force expire file
     */
    public function expireFile(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/projects/proshare/files');
            return;
        }
        
        $id = (int)($_POST['file_id'] ?? 0);
        
        if ($id > 0) {
            $this->projectDb->update('files', [
                'expires_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$id]);
            
            Logger::activity(Auth::id(), 'proshare_file_expired', ['file_id' => $id]);
            $this->flash('success', 'File expired successfully.');
        }
        
        $this->redirect('/admin/projects/proshare/files');
    }
    
    /**
     * Delete text share
     */
    public function deleteText(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/projects/proshare/texts');
            return;
        }
        
        $id = (int)($_POST['text_id'] ?? 0);
        
        if ($id > 0) {
            $this->projectDb->delete('text_shares', 'id = ?', [$id]);
            Logger::activity(Auth::id(), 'proshare_text_deleted', ['text_id' => $id]);
            $this->flash('success', 'Text share deleted successfully.');
        }
        
        $this->redirect('/admin/projects/proshare/texts');
    }
    
    /**
     * Get download count safely, handling missing download_count column
     */
    private function getDownloadCount(): int
    {
        try {
            $result = $this->projectDb->fetch("SELECT SUM(download_count) as count FROM files");
            return (int)($result['count'] ?? 0);
        } catch (\PDOException $e) {
            // Column doesn't exist, check if downloads column exists
            try {
                $result = $this->projectDb->fetch("SELECT SUM(downloads) as count FROM files");
                return (int)($result['count'] ?? 0);
            } catch (\PDOException $e2) {
                // Neither column exists, return 0
                Logger::warning("Download count column not found in files table for project: {$this->projectDbName}");
                return 0;
            }
        }
    }
    
    /**
     * Get view count safely, handling missing view_count column
     */
    private function getViewCount(): int
    {
        try {
            $result = $this->projectDb->fetch("SELECT SUM(view_count) as count FROM text_shares");
            return (int)($result['count'] ?? 0);
        } catch (\PDOException $e) {
            // Column doesn't exist, check if views column exists
            try {
                $result = $this->projectDb->fetch("SELECT SUM(views) as count FROM text_shares");
                return (int)($result['count'] ?? 0);
            } catch (\PDOException $e2) {
                // Neither column exists, return 0
                Logger::warning("View count column not found in text_shares table for project: {$this->projectDbName}");
                return 0;
            }
        }
    }
    
    /**
     * Get storage usage safely, handling missing file_size column
     */
    private function getStorageUsage(): float
    {
        try {
            $result = $this->projectDb->fetch("SELECT SUM(file_size) as bytes FROM files");
            return round(($result['bytes'] ?? 0) / (1024 * 1024 * 1024), 2);
        } catch (\PDOException $e) {
            // Column doesn't exist, check if size column exists
            try {
                $result = $this->projectDb->fetch("SELECT SUM(size) as bytes FROM files");
                return round(($result['bytes'] ?? 0) / (1024 * 1024 * 1024), 2);
            } catch (\PDOException $e2) {
                // Neither column exists, return 0
                Logger::warning("File size column not found in files table for project: {$this->projectDbName}");
                return 0;
            }
        }
    }
    
    /**
     * User Dashboard - User-facing dashboard features
     */
    public function userDashboard(): void
    {
        // Get all user IDs from ProShare files
        $userIdsFromFiles = $this->projectDb->fetchAll(
            "SELECT DISTINCT user_id FROM files WHERE user_id IS NOT NULL"
        );
        
        $users = [];
        if (!empty($userIdsFromFiles)) {
            $userIds = array_column($userIdsFromFiles, 'user_id');
            $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
            $users = $this->mainDb->fetchAll(
                "SELECT id, name, email FROM users WHERE id IN ($placeholders) ORDER BY name",
                $userIds
            );
        }
        
        $this->view('admin/projects/proshare/user-dashboard', [
            'title' => 'ProShare - User Dashboard',
            'users' => $users
        ], 'admin');
    }
    
    /**
     * User Files - View files by user
     */
    public function userFiles(): void
    {
        $userId = $_GET['user_id'] ?? null;
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // Get all user IDs from ProShare files
        $userIdsFromFiles = $this->projectDb->fetchAll(
            "SELECT DISTINCT user_id FROM files WHERE user_id IS NOT NULL"
        );
        
        $users = [];
        if (!empty($userIdsFromFiles)) {
            $userIds = array_values(array_unique(array_filter(array_column($userIdsFromFiles, 'user_id'), function($id) {
                return $id !== null && $id !== '';
            })));
            if (!empty($userIds)) {
                $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
                $users = $this->mainDb->fetchAll(
                    "SELECT id, name, email FROM users WHERE id IN ($placeholders) ORDER BY name",
                    $userIds
                );
            }
        }
        
        $files = [];
        $totalCount = 0;
        $selectedUser = null;
        
        if ($userId) {
            // Get user info
            $selectedUser = $this->mainDb->fetch(
                "SELECT id, name, email FROM users WHERE id = ?",
                [$userId]
            );
            
            // Get user's files
            $files = $this->projectDb->fetchAll(
                "SELECT * FROM files WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?",
                [$userId, $perPage, $offset]
            );
            
            $totalCount = $this->projectDb->fetch(
                "SELECT COUNT(*) as count FROM files WHERE user_id = ?",
                [$userId]
            )['count'];
        }
        
        $totalPages = $totalCount > 0 ? ceil($totalCount / $perPage) : 0;
        
        $this->view('admin/projects/proshare/user-files', [
            'title' => 'ProShare - User Files',
            'users' => $users,
            'files' => $files,
            'selectedUser' => $selectedUser,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount
        ], 'admin');
    }
    
    /**
     * User Activity - View activity by user
     */
    public function userActivity(): void
    {
        $userId = $_GET['user_id'] ?? null;
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        // Get all user IDs from activity logs
        $userIdsFromLogs = $this->projectDb->fetchAll(
            "SELECT DISTINCT user_id FROM activity_logs WHERE user_id IS NOT NULL"
        );
        
        $users = [];
        if (!empty($userIdsFromLogs)) {
            $userIds = array_column($userIdsFromLogs, 'user_id');
            $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
            $users = $this->mainDb->fetchAll(
                "SELECT id, name, email FROM users WHERE id IN ($placeholders) ORDER BY name",
                $userIds
            );
        }
        
        $activities = [];
        $totalCount = 0;
        $selectedUser = null;
        
        if ($userId) {
            // Get user info
            $selectedUser = $this->mainDb->fetch(
                "SELECT id, name, email FROM users WHERE id = ?",
                [$userId]
            );
            
            // Get user's activities
            $activities = $this->projectDb->fetchAll(
                "SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?",
                [$userId, $perPage, $offset]
            );
            
            $totalCount = $this->projectDb->fetch(
                "SELECT COUNT(*) as count FROM activity_logs WHERE user_id = ?",
                [$userId]
            )['count'];
        }
        
        $totalPages = $totalCount > 0 ? ceil($totalCount / $perPage) : 0;
        
        $this->view('admin/projects/proshare/user-activity', [
            'title' => 'ProShare - User Activity',
            'users' => $users,
            'activities' => $activities,
            'selectedUser' => $selectedUser,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount
        ], 'admin');
    }
    
    /**
     * User Logs - User activity logs with session history
     */
    public function userLogs(): void
    {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        $userId = $_GET['user_id'] ?? null;
        
        // Build query
        $whereClause = '';
        $params = [];
        if ($userId) {
            $whereClause = 'WHERE user_id = ?';
            $params[] = $userId;
        }
        
        // Get activity logs from project DB
        $logs = $this->projectDb->fetchAll(
            "SELECT * FROM activity_logs $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );
        
        // Get user info
        if (!empty($logs)) {
            $userIds = array_values(array_unique(array_filter(array_column($logs, 'user_id'), function($id) {
                return $id !== null && $id !== '';
            })));
            if (!empty($userIds)) {
                $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
                $users = $this->mainDb->fetchAll(
                    "SELECT id, name, email FROM users WHERE id IN ($placeholders)",
                    $userIds
                );
                $userLookup = [];
                foreach ($users as $user) {
                    $userLookup[$user['id']] = $user;
                }
                foreach ($logs as &$log) {
                    if (isset($log['user_id']) && isset($userLookup[$log['user_id']])) {
                        $log['user_name'] = $userLookup[$log['user_id']]['name'];
                        $log['email'] = $userLookup[$log['user_id']]['email'];
                    }
                }
                unset($log);
            }
        }
        
        // Get total count
        $totalCount = $this->projectDb->fetch(
            "SELECT COUNT(*) as count FROM activity_logs $whereClause",
            $params
        )['count'];
        
        $totalPages = ceil($totalCount / $perPage);
        
        // Get all user IDs from activity logs for filter dropdown
        $userIdsFromLogs = $this->projectDb->fetchAll(
            "SELECT DISTINCT user_id FROM activity_logs WHERE user_id IS NOT NULL"
        );
        
        $allUsers = [];
        if (!empty($userIdsFromLogs)) {
            $userIds = array_values(array_unique(array_filter(array_column($userIdsFromLogs, 'user_id'), function($id) {
                return $id !== null && $id !== '';
            })));
            if (!empty($userIds)) {
                $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
                $allUsers = $this->mainDb->fetchAll(
                    "SELECT id, name, email FROM users WHERE id IN ($placeholders) ORDER BY name",
                    $userIds
                );
            }
        }
        
        $this->view('admin/projects/proshare/user-logs', [
            'title' => 'ProShare - User Activity Logs',
            'logs' => $logs,
            'allUsers' => $allUsers,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount,
            'selectedUserId' => $userId
        ], 'admin');
    }
    
    /**
     * Sessions - Session history with IP and device info
     */
    public function sessions(): void
    {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        // Get user devices from main DB
        $devices = $this->mainDb->fetchAll(
            "SELECT d.*, u.name, u.email 
             FROM user_devices d
             LEFT JOIN users u ON d.user_id = u.id
             ORDER BY d.last_active_at DESC
             LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
        
        // Get total count
        $totalCount = $this->mainDb->fetch("SELECT COUNT(*) as count FROM user_devices")['count'];
        $totalPages = ceil($totalCount / $perPage);
        
        $this->view('admin/projects/proshare/sessions', [
            'title' => 'ProShare - Session History',
            'devices' => $devices,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount
        ], 'admin');
    }
    
    /**
     * File Activity - File uploads, downloads, deletes, etc.
     */
    public function fileActivity(): void
    {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        $action = $_GET['action'] ?? 'all';
        
        // Build query based on action filter
        $whereClause = "WHERE resource_type = 'file'";
        $params = [];
        
        if ($action !== 'all') {
            $whereClause .= " AND action LIKE ?";
            $params[] = "%{$action}%";
        }
        
        // Get file activity logs
        $activities = $this->projectDb->fetchAll(
            "SELECT * FROM activity_logs $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );
        
        // Get user info
        if (!empty($activities)) {
            $userIds = array_values(array_unique(array_filter(array_column($activities, 'user_id'), function($id) {
                return $id !== null && $id !== '';
            })));
            if (!empty($userIds)) {
                $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
                $users = $this->mainDb->fetchAll(
                    "SELECT id, name, email FROM users WHERE id IN ($placeholders)",
                    $userIds
                );
                $userLookup = [];
                foreach ($users as $user) {
                    $userLookup[$user['id']] = $user;
                }
                foreach ($activities as &$activity) {
                    if (isset($activity['user_id']) && isset($userLookup[$activity['user_id']])) {
                        $activity['user_name'] = $userLookup[$activity['user_id']]['name'];
                        $activity['email'] = $userLookup[$activity['user_id']]['email'];
                    } else {
                        $activity['user_name'] = 'System';
                        $activity['email'] = '';
                    }
                }
                unset($activity);
            } else {
                // No valid user IDs, set all as System
                foreach ($activities as &$activity) {
                    $activity['user_name'] = 'System';
                    $activity['email'] = '';
                }
                unset($activity);
            }
        }
        
        // Get total count
        $totalCount = $this->projectDb->fetch(
            "SELECT COUNT(*) as count FROM activity_logs $whereClause",
            $params
        )['count'];
        
        $totalPages = ceil($totalCount / $perPage);
        
        // Get activity statistics
        $stats = [
            'uploads' => $this->projectDb->fetch(
                "SELECT COUNT(*) as count FROM activity_logs WHERE action LIKE '%upload%'"
            )['count'] ?? 0,
            'downloads' => $this->projectDb->fetch(
                "SELECT COUNT(*) as count FROM file_downloads"
            )['count'] ?? 0,
            'deletes' => $this->projectDb->fetch(
                "SELECT COUNT(*) as count FROM activity_logs WHERE action LIKE '%delete%'"
            )['count'] ?? 0,
            'shares' => $this->projectDb->fetch(
                "SELECT COUNT(*) as count FROM activity_logs WHERE action LIKE '%share%'"
            )['count'] ?? 0
        ];
        
        $this->view('admin/projects/proshare/file-activity', [
            'title' => 'ProShare - File Activity',
            'activities' => $activities,
            'stats' => $stats,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount,
            'currentAction' => $action
        ], 'admin');
    }
    
    /**
     * Security - Security monitoring with unauthorized access and alerts
     */
    public function security(): void
    {
        // Get failed login attempts from main DB
        $failedLogins = $this->mainDb->fetchAll(
            "SELECT * FROM failed_logins ORDER BY attempted_at DESC LIMIT 100"
        );
        
        // Get blocked IPs
        $blockedIps = $this->mainDb->fetchAll(
            "SELECT b.*, u.name as blocked_by_name 
             FROM blocked_ips b
             LEFT JOIN users u ON b.blocked_by = u.id
             ORDER BY b.created_at DESC"
        );
        
        // Get suspicious activities from audit logs
        $suspiciousActivities = $this->projectDb->fetchAll(
            "SELECT * FROM audit_logs 
             WHERE action LIKE '%unauthorized%' OR action LIKE '%failed%' OR action LIKE '%suspicious%'
             ORDER BY created_at DESC 
             LIMIT 50"
        );
        
        // Get security statistics
        $stats = [
            'failed_logins_24h' => $this->mainDb->fetch(
                "SELECT COUNT(*) as count FROM failed_logins WHERE attempted_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)"
            )['count'] ?? 0,
            'blocked_ips' => count($blockedIps),
            'suspicious_activities' => count($suspiciousActivities),
            'unique_attackers' => $this->mainDb->fetch(
                "SELECT COUNT(DISTINCT ip_address) as count FROM failed_logins WHERE attempted_at > DATE_SUB(NOW(), INTERVAL 7 DAY)"
            )['count'] ?? 0
        ];
        
        $this->view('admin/projects/proshare/security', [
            'title' => 'ProShare - Security Monitoring',
            'failedLogins' => $failedLogins,
            'blockedIps' => $blockedIps,
            'suspiciousActivities' => $suspiciousActivities,
            'stats' => $stats
        ], 'admin');
    }
    
    /**
     * Server Health - CPU, RAM, Disk monitoring
     */
    public function serverHealth(): void
    {
        // Get server health metrics
        $health = [
            'cpu_usage' => $this->getCpuUsage(),
            'memory_usage' => $this->getMemoryUsage(),
            'disk_usage' => $this->getDiskUsage(),
            'uptime' => $this->getUptime(),
            'load_average' => sys_getloadavg()
        ];
        
        // Get database performance from main DB
        $dbPerf = [
            'connections' => $this->mainDb->fetch("SHOW STATUS LIKE 'Threads_connected'"),
            'queries' => $this->mainDb->fetch("SHOW STATUS LIKE 'Questions'"),
            'slow_queries' => $this->mainDb->fetch("SHOW STATUS LIKE 'Slow_queries'")
        ];
        
        // Get error logs (last 100)
        $errorLogs = [];
        $errorLogPath = BASE_PATH . '/storage/logs/error.log';
        if (file_exists($errorLogPath)) {
            $lines = file($errorLogPath);
            $errorLogs = array_slice(array_reverse($lines), 0, 100);
        }
        
        $this->view('admin/projects/proshare/server-health', [
            'title' => 'ProShare - Server Health',
            'health' => $health,
            'dbPerf' => $dbPerf,
            'errorLogs' => $errorLogs
        ], 'admin');
    }
    
    /**
     * Storage - Storage monitoring and usage statistics
     */
    public function storage(): void
    {
        // Get total storage used
        $totalStorage = $this->getStorageUsage();
        
        // Get storage per user
        $storagePerUser = $this->projectDb->fetchAll(
            "SELECT user_id, SUM(size) as total_size, COUNT(*) as file_count 
             FROM files 
             WHERE user_id IS NOT NULL
             GROUP BY user_id
             ORDER BY total_size DESC"
        );
        
        // Get user info
        if (!empty($storagePerUser)) {
            $userIds = array_column($storagePerUser, 'user_id');
            $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
            $users = $this->mainDb->fetchAll(
                "SELECT id, name, email FROM users WHERE id IN ($placeholders)",
                $userIds
            );
            $userLookup = [];
            foreach ($users as $user) {
                $userLookup[$user['id']] = $user;
            }
            foreach ($storagePerUser as &$storage) {
                if (isset($userLookup[$storage['user_id']])) {
                    $storage['user_name'] = $userLookup[$storage['user_id']]['name'];
                    $storage['email'] = $userLookup[$storage['user_id']]['email'];
                }
            }
            unset($storage);
        }
        
        // Get storage growth trend (last 30 days)
        $growthTrend = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $size = $this->projectDb->fetch(
                "SELECT SUM(size) as size FROM files WHERE DATE(created_at) = ?",
                [$date]
            )['size'] ?? 0;
            
            $growthTrend[] = [
                'date' => date('M d', strtotime($date)),
                'size' => round($size / (1024 * 1024), 2) // Convert to MB
            ];
        }
        
        // Get storage statistics
        $stats = [
            'total_storage_gb' => $totalStorage,
            'total_files' => $this->projectDb->fetch("SELECT COUNT(*) as count FROM files")['count'] ?? 0,
            'total_users' => count($storagePerUser),
            'avg_file_size_mb' => $this->projectDb->fetch(
                "SELECT AVG(size) as avg FROM files"
            )['avg'] ? round($this->projectDb->fetch("SELECT AVG(size) as avg FROM files")['avg'] / (1024 * 1024), 2) : 0
        ];
        
        $this->view('admin/projects/proshare/storage', [
            'title' => 'ProShare - Storage Monitoring',
            'stats' => $stats,
            'storagePerUser' => $storagePerUser,
            'growthTrend' => $growthTrend
        ], 'admin');
    }
    
    /**
     * Audit Trail - Admin actions and configuration changes
     */
    public function auditTrail(): void
    {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        // Get audit logs
        $auditLogs = $this->projectDb->fetchAll(
            "SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
        
        // Get user info
        if (!empty($auditLogs)) {
            $userIds = array_values(array_unique(array_filter(array_column($auditLogs, 'user_id'), function($id) {
                return $id !== null && $id !== '';
            })));
            if (!empty($userIds)) {
                $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
                $users = $this->mainDb->fetchAll(
                    "SELECT id, name, email FROM users WHERE id IN ($placeholders)",
                    $userIds
                );
                $userLookup = [];
                foreach ($users as $user) {
                    $userLookup[$user['id']] = $user;
                }
                foreach ($auditLogs as &$log) {
                    if (isset($log['user_id']) && isset($userLookup[$log['user_id']])) {
                        $log['user_name'] = $userLookup[$log['user_id']]['name'];
                        $log['email'] = $userLookup[$log['user_id']]['email'];
                    } else {
                        $log['user_name'] = 'System';
                        $log['email'] = '';
                    }
                }
                unset($log);
            } else {
                // No valid user IDs, set all as System
                foreach ($auditLogs as &$log) {
                    $log['user_name'] = 'System';
                    $log['email'] = '';
                }
                unset($log);
            }
        }
        
        // Get total count
        $totalCount = $this->projectDb->fetch("SELECT COUNT(*) as count FROM audit_logs")['count'];
        $totalPages = ceil($totalCount / $perPage);
        
        $this->view('admin/projects/proshare/audit-trail', [
            'title' => 'ProShare - Audit Trail',
            'auditLogs' => $auditLogs,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount
        ], 'admin');
    }
    
    /**
     * Export Audit Trail - Export to CSV or JSON
     */
    public function exportAuditTrail(): void
    {
        $format = $_GET['format'] ?? 'csv';
        
        // Get all audit logs
        $auditLogs = $this->projectDb->fetchAll(
            "SELECT * FROM audit_logs ORDER BY created_at DESC"
        );
        
        // Get user info
        if (!empty($auditLogs)) {
            $userIds = array_values(array_unique(array_filter(array_column($auditLogs, 'user_id'), function($id) {
                return $id !== null && $id !== '';
            })));
            if (!empty($userIds)) {
                $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
                $users = $this->mainDb->fetchAll(
                    "SELECT id, name, email FROM users WHERE id IN ($placeholders)",
                    $userIds
                );
                $userLookup = [];
                foreach ($users as $user) {
                    $userLookup[$user['id']] = $user;
                }
                foreach ($auditLogs as &$log) {
                    if (isset($log['user_id']) && isset($userLookup[$log['user_id']])) {
                        $log['user_name'] = $userLookup[$log['user_id']]['name'];
                        $log['email'] = $userLookup[$log['user_id']]['email'];
                    } else {
                        $log['user_name'] = 'System';
                        $log['email'] = '';
                    }
                }
                unset($log);
            } else {
                // No valid user IDs, set all as System
                foreach ($auditLogs as &$log) {
                    $log['user_name'] = 'System';
                    $log['email'] = '';
                }
                unset($log);
            }
        }
        
        if ($format === 'json') {
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="audit_trail_' . date('Y-m-d') . '.json"');
            echo json_encode($auditLogs, JSON_PRETTY_PRINT);
        } else {
            // CSV format
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="audit_trail_' . date('Y-m-d') . '.csv"');
            
            $output = fopen('php://output', 'w');
            fputcsv($output, ['ID', 'User', 'Email', 'Action', 'Resource Type', 'Resource ID', 'IP Address', 'Created At']);
            
            foreach ($auditLogs as $log) {
                fputcsv($output, [
                    $log['id'],
                    $log['user_name'] ?? '',
                    $log['email'] ?? '',
                    $log['action'],
                    $log['resource_type'],
                    $log['resource_id'] ?? '',
                    $log['ip_address'] ?? '',
                    $log['created_at']
                ]);
            }
            
            fclose($output);
        }
        
        exit;
    }
    
    /**
     * Analytics - Active users, most downloaded files, traffic overview
     */
    public function analytics(): void
    {
        // Get recently logged in users
        $recentUsers = $this->mainDb->fetchAll(
            "SELECT id, name, email, last_login_at 
             FROM users 
             WHERE last_login_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
             ORDER BY last_login_at DESC"
        );
        
        // Get activity counts for these users from project DB
        $activeUsers = [];
        if (!empty($recentUsers)) {
            foreach ($recentUsers as $user) {
                $activityCount = $this->projectDb->fetch(
                    "SELECT COUNT(*) as count FROM activity_logs 
                     WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)",
                    [$user['id']]
                )['count'] ?? 0;
                
                $user['activity_count'] = $activityCount;
                $activeUsers[] = $user;
            }
            
            // Sort by activity count
            usort($activeUsers, function($a, $b) {
                return $b['activity_count'] - $a['activity_count'];
            });
            
            $activeUsers = array_slice($activeUsers, 0, 20);
        }
        
        // Get most downloaded files
        $mostDownloaded = $this->projectDb->fetchAll(
            "SELECT f.*, COUNT(d.id) as download_count
             FROM files f
             LEFT JOIN file_downloads d ON f.id = d.file_id
             GROUP BY f.id
             ORDER BY download_count DESC
             LIMIT 20"
        );
        
        // Get most active users
        $mostActive = $this->projectDb->fetchAll(
            "SELECT user_id, COUNT(*) as activity_count
             FROM activity_logs
             WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY user_id
             ORDER BY activity_count DESC
             LIMIT 20"
        );
        
        // Get user info for most active
        if (!empty($mostActive)) {
            $userIds = array_column($mostActive, 'user_id');
            $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
            $users = $this->mainDb->fetchAll(
                "SELECT id, name, email FROM users WHERE id IN ($placeholders)",
                $userIds
            );
            $userLookup = [];
            foreach ($users as $user) {
                $userLookup[$user['id']] = $user;
            }
            foreach ($mostActive as &$active) {
                if (isset($userLookup[$active['user_id']])) {
                    $active['user_name'] = $userLookup[$active['user_id']]['name'];
                    $active['email'] = $userLookup[$active['user_id']]['email'];
                }
            }
            unset($active);
        }
        
        // Get traffic overview (last 30 days)
        $trafficData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            
            $uploads = $this->projectDb->fetch(
                "SELECT COUNT(*) as count FROM files WHERE DATE(created_at) = ?",
                [$date]
            )['count'] ?? 0;
            
            // Use created_at instead of downloaded_at for file_downloads
            $downloads = $this->projectDb->fetch(
                "SELECT COUNT(*) as count FROM file_downloads WHERE DATE(created_at) = ?",
                [$date]
            )['count'] ?? 0;
            
            $trafficData[] = [
                'date' => date('M d', strtotime($date)),
                'uploads' => $uploads,
                'downloads' => $downloads
            ];
        }
        
        // Get statistics
        $stats = [
            'active_users_30d' => count($activeUsers),
            'total_downloads' => $this->projectDb->fetch("SELECT COUNT(*) as count FROM file_downloads")['count'] ?? 0,
            'total_uploads' => $this->projectDb->fetch("SELECT COUNT(*) as count FROM files")['count'] ?? 0,
            'avg_downloads_per_file' => $this->projectDb->fetch(
                "SELECT AVG(download_count) as avg FROM (
                    SELECT COUNT(d.id) as download_count 
                    FROM files f 
                    LEFT JOIN file_downloads d ON f.id = d.file_id 
                    GROUP BY f.id
                ) as subquery"
            )['avg'] ? round($this->projectDb->fetch(
                "SELECT AVG(download_count) as avg FROM (
                    SELECT COUNT(d.id) as download_count 
                    FROM files f 
                    LEFT JOIN file_downloads d ON f.id = d.file_id 
                    GROUP BY f.id
                ) as subquery"
            )['avg'], 2) : 0
        ];
        
        $this->view('admin/projects/proshare/analytics', [
            'title' => 'ProShare - Analytics & Insights',
            'stats' => $stats,
            'activeUsers' => $activeUsers,
            'mostDownloaded' => $mostDownloaded,
            'mostActive' => $mostActive,
            'trafficData' => $trafficData
        ], 'admin');
    }
    
    /**
     * Helper: Get CPU usage
     */
    private function getCpuUsage(): float
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return 0; // Windows not supported
        }
        
        $load = sys_getloadavg();
        
        // Try to get actual CPU core count
        $cores = 4; // Default fallback
        try {
            $coreCount = shell_exec('nproc');
            if ($coreCount !== null && is_numeric(trim($coreCount))) {
                $cores = max(1, (int)trim($coreCount)); // Ensure at least 1 to prevent division by zero
            }
        } catch (\Exception $e) {
            // Use default
        }
        
        // Prevent division by zero
        if ($cores <= 0) {
            $cores = 1;
        }
        
        return round($load[0] * 100 / $cores, 2);
    }
    
    /**
     * Helper: Get memory usage
     */
    private function getMemoryUsage(): array
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return ['used' => 0, 'total' => 0, 'percentage' => 0];
        }
        
        try {
            $free = shell_exec('free');
            if ($free === null || empty($free)) {
                return ['used' => 0, 'total' => 0, 'percentage' => 0];
            }
            
            $free = (string)trim($free);
            $free_arr = explode("\n", $free);
            
            if (!isset($free_arr[1])) {
                return ['used' => 0, 'total' => 0, 'percentage' => 0];
            }
            
            $mem = explode(" ", $free_arr[1]);
            $mem = array_filter($mem);
            $mem = array_merge($mem);
            
            if (count($mem) < 3) {
                return ['used' => 0, 'total' => 0, 'percentage' => 0];
            }
            
            $total = (float)$mem[1];
            $used = (float)$mem[2];
            
            if ($total <= 0) {
                return ['used' => 0, 'total' => 0, 'percentage' => 0];
            }
            
            return [
                'used' => round($used / 1024 / 1024, 2),
                'total' => round($total / 1024 / 1024, 2),
                'percentage' => round(($used / $total) * 100, 2)
            ];
        } catch (\Exception $e) {
            return ['used' => 0, 'total' => 0, 'percentage' => 0];
        }
    }
    
    /**
     * Helper: Get disk usage
     */
    private function getDiskUsage(): array
    {
        $total = disk_total_space('/');
        $free = disk_free_space('/');
        
        // Prevent division by zero
        if ($total <= 0 || $total === false || $free === false) {
            return [
                'used' => 0,
                'total' => 0,
                'percentage' => 0
            ];
        }
        
        $used = $total - $free;
        
        return [
            'used' => round($used / 1024 / 1024 / 1024, 2),
            'total' => round($total / 1024 / 1024 / 1024, 2),
            'percentage' => round(($used / $total) * 100, 2)
        ];
    }
    
    /**
     * Helper: Get system uptime
     */
    private function getUptime(): string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return 'N/A';
        }
        
        try {
            $uptime = shell_exec('uptime -p');
            if ($uptime === null || empty($uptime)) {
                return 'N/A';
            }
            return trim($uptime);
        } catch (\Exception $e) {
            return 'N/A';
        }
    }
}
