<?php
/**
 * ImgTxt Admin Controller
 * 
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;
use Core\Auth;
use Core\Logger;

class ImgTxtAdminController extends BaseController
{
    private $projectDb;
    private $mainDb;
    private $mainDbName;
    private $projectDbName;
    
    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
        $this->projectDb = Database::projectConnection('imgtxt');
        $this->mainDb = Database::getInstance();
        
        // Get main database name from config
        $mainConfig = require BASE_PATH . '/config/database.php';
        $this->mainDbName = $mainConfig['database'];
        
        // Get project database name
        $projectConfig = require BASE_PATH . '/projects/imgtxt/config.php';
        $this->projectDbName = $projectConfig['database']['database'] ?? 'imgtxt';
    }
    
    /**
     * Overview dashboard
     */
    public function overview(): void
    {
        // Get statistics
        $stats = [
            'total_jobs' => $this->projectDb->fetch("SELECT COUNT(*) as count FROM ocr_jobs")['count'] ?? 0,
            'completed_jobs' => $this->projectDb->fetch("SELECT COUNT(*) as count FROM ocr_jobs WHERE status = 'completed'")['count'] ?? 0,
            'failed_jobs' => $this->projectDb->fetch("SELECT COUNT(*) as count FROM ocr_jobs WHERE status = 'failed'")['count'] ?? 0,
            'pending_jobs' => $this->projectDb->fetch("SELECT COUNT(*) as count FROM ocr_jobs WHERE status = 'pending'")['count'] ?? 0,
            'active_users' => $this->projectDb->fetch("SELECT COUNT(DISTINCT user_id) as count FROM ocr_jobs WHERE user_id IS NOT NULL")['count'] ?? 0,
        ];
        
        // Calculate success rate
        if ($stats['total_jobs'] > 0) {
            $stats['success_rate'] = round(($stats['completed_jobs'] / $stats['total_jobs']) * 100, 1);
        } else {
            $stats['success_rate'] = 0;
        }
        
        // Get recent jobs from project DB
        $recentJobs = $this->projectDb->fetchAll(
            "SELECT * FROM ocr_jobs 
             ORDER BY created_at DESC 
             LIMIT 15"
        );
        
        // Get user info for jobs from main DB
        if (!empty($recentJobs)) {
            $userIds = array_unique(array_filter(array_column($recentJobs, 'user_id')));
            if (!empty($userIds)) {
                $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
                $users = $this->mainDb->fetchAll(
                    "SELECT id, name, email FROM users WHERE id IN ($placeholders)",
                    array_values($userIds)
                );
                // Create user lookup array
                $userLookup = [];
                foreach ($users as $user) {
                    $userLookup[$user['id']] = $user;
                }
                // Merge user data into jobs
                foreach ($recentJobs as &$job) {
                    if (isset($job['user_id']) && isset($userLookup[$job['user_id']])) {
                        $job['user_name'] = $userLookup[$job['user_id']]['name'];
                        $job['email'] = $userLookup[$job['user_id']]['email'];
                    } else {
                        $job['user_name'] = 'Unknown';
                        $job['email'] = '';
                    }
                }
                unset($job); // Break reference
            }
        }
        
        // OCR jobs trend (last 7 days)
        $trendData = [];
        $trendLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $count = $this->projectDb->fetch(
                "SELECT COUNT(*) as count FROM ocr_jobs WHERE DATE(created_at) = ?",
                [$date]
            );
            $trendLabels[] = date('M d', strtotime($date));
            $trendData[] = $count['count'] ?? 0;
        }
        
        // Language usage statistics
        $languageStats = $this->projectDb->fetchAll(
            "SELECT language, COUNT(*) as count 
             FROM ocr_jobs 
             WHERE language IS NOT NULL 
             GROUP BY language 
             ORDER BY count DESC 
             LIMIT 10"
        );
        
        // Convert to associative array for chart
        $languageStatsArray = [];
        foreach ($languageStats as $lang) {
            $languageStatsArray[$lang['language']] = $lang['count'];
        }
        
        $this->view('admin/projects/imgtxt/overview', [
            'title' => 'ImgTxt Admin - Overview',
            'stats' => $stats,
            'recent_jobs' => $recentJobs,  // Changed variable name to match view
            'trend_labels' => $trendLabels,  // Added for chart
            'trend_data' => $trendData,  // Changed to match view
            'language_stats' => $languageStatsArray  // Changed to match view
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
        
        // Get current settings from key-value table
        $settingsRows = $this->projectDb->fetchAll("SELECT `key`, `value` FROM settings");
        
        // Convert to associative array
        $settings = [];
        foreach ($settingsRows as $row) {
            $settings[$row['key']] = $row['value'];
        }
        
        // Set defaults if not found
        $settings = array_merge([
            'max_file_size' => '10',
            'batch_size' => '5',
            'ocr_engine' => 'tesseract',
            'default_language' => 'eng',
            'batch_processing_enabled' => '1',
            'multi_language_enabled' => '1'
        ], $settings);
        
        $this->view('admin/projects/imgtxt/settings', [
            'title' => 'ImgTxt Admin - Settings',
            'settings' => $settings
        ]);
    }
    
    /**
     * Update settings
     */
    private function updateSettings(): void
    {
        try {
            // Get form data
            $maxFileSize = (int)($_POST['max_file_size'] ?? 10);
            $batchSize = (int)($_POST['batch_size'] ?? 5);
            $ocrEngine = $_POST['ocr_engine'] ?? 'tesseract';
            $defaultLanguage = $_POST['default_language'] ?? 'eng';
            $batchProcessingEnabled = isset($_POST['batch_processing_enabled']) ? 1 : 0;
            $multiLanguageEnabled = isset($_POST['multi_language_enabled']) ? 1 : 0;
            
            // Update or insert each setting
            $settings = [
                'max_file_size' => $maxFileSize,
                'batch_size' => $batchSize,
                'ocr_engine' => $ocrEngine,
                'default_language' => $defaultLanguage,
                'batch_processing_enabled' => $batchProcessingEnabled,
                'multi_language_enabled' => $multiLanguageEnabled
            ];
            
            foreach ($settings as $key => $value) {
                $existing = $this->projectDb->fetch(
                    "SELECT id FROM settings WHERE `key` = ?",
                    [$key]
                );
                
                if ($existing) {
                    $this->projectDb->update('settings', [
                        'value' => (string)$value,
                        'updated_at' => date('Y-m-d H:i:s')
                    ], '`key` = ?', [$key]);
                } else {
                    // Manually build INSERT to handle 'key' reserved word
                    $this->projectDb->query(
                        "INSERT INTO settings (`key`, `value`, `type`, created_at, updated_at) VALUES (?, ?, ?, ?, ?)",
                        [$key, (string)$value, is_numeric($value) ? 'integer' : 'string', date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]
                    );
                }
            }
            
            // Log the activity
            Logger::activity(Auth::id(), 'imgtxt_settings_updated', $settings);
            
            $_SESSION['flash_message'] = 'Settings updated successfully';
            $_SESSION['flash_type'] = 'success';
        } catch (\Exception $e) {
            $_SESSION['flash_message'] = 'Failed to update settings: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'danger';
        }
        
        $this->redirect('/admin/projects/imgtxt/settings');
    }
    
    /**
     * OCR Jobs monitoring
     */
    public function jobs(): void
    {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $status = $_GET['status'] ?? 'all';
        $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
        
        // Build query
        $whereClause = '';
        $params = [];
        
        if ($status !== 'all') {
            $whereClause = 'WHERE j.status = ?';
            $params[] = $status;
        }
        
        if ($userId) {
            $whereClause .= ($whereClause ? ' AND' : 'WHERE') . ' j.user_id = ?';
            $params[] = $userId;
        }
        
        // Get jobs from project DB
        $jobs = $this->projectDb->fetchAll(
            "SELECT * FROM ocr_jobs j 
             $whereClause
             ORDER BY created_at DESC 
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );
        
        // Get user info for jobs from main DB
        if (!empty($jobs)) {
            $userIds = array_unique(array_filter(array_column($jobs, 'user_id')));
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
                // Merge user data into jobs
                foreach ($jobs as &$job) {
                    if (isset($job['user_id']) && isset($userLookup[$job['user_id']])) {
                        $job['user_name'] = $userLookup[$job['user_id']]['name'];
                        $job['email'] = $userLookup[$job['user_id']]['email'];
                    } else {
                        $job['user_name'] = 'Unknown';
                        $job['email'] = '';
                    }
                }
                unset($job); // Break reference
            }
        }
        
        // Get total count
        $totalCount = $this->projectDb->fetch(
            "SELECT COUNT(*) as count FROM ocr_jobs j $whereClause",
            $params
        )['count'];
        
        $totalPages = ceil($totalCount / $perPage);
        
        // Get filter user info if applicable
        $filterUser = null;
        if ($userId) {
            $filterUser = $this->mainDb->fetch(
                "SELECT id, name, email FROM users WHERE id = ?",
                [$userId]
            );
        }
        
        $this->view('admin/projects/imgtxt/jobs', [
            'title' => 'ImgTxt Admin - OCR Jobs',
            'jobs' => $jobs,
            'currentPage' => $page,
            'current_page' => $page,  // Add alias for view compatibility
            'totalPages' => $totalPages,
            'total_pages' => $totalPages,  // Add alias for view compatibility
            'totalCount' => $totalCount,
            'currentStatus' => $status,
            'filters' => ['status' => $status],  // Add for view compatibility
            'filterUser' => $filterUser
        ]);
    }
    
    /**
     * Language configuration
     */
    public function languages(): void
    {
        // Available languages
        $languages = [
            'eng' => 'English',
            'ara' => 'Arabic',
            'chi_sim' => 'Chinese (Simplified)',
            'chi_tra' => 'Chinese (Traditional)',
            'fra' => 'French',
            'deu' => 'German',
            'hin' => 'Hindi',
            'jpn' => 'Japanese',
            'kor' => 'Korean',
            'por' => 'Portuguese',
            'rus' => 'Russian',
            'spa' => 'Spanish',
            'tur' => 'Turkish',
            'vie' => 'Vietnamese'
        ];
        
        // Get language usage stats
        $stats = [];
        foreach ($languages as $code => $name) {
            $count = $this->projectDb->fetch(
                "SELECT COUNT(*) as count FROM ocr_jobs WHERE language = ?",
                [$code]
            )['count'] ?? 0;
            
            $stats[] = [
                'code' => $code,
                'name' => $name,
                'usage_count' => $count
            ];
        }
        
        // Sort by usage
        usort($stats, function($a, $b) {
            return $b['usage_count'] - $a['usage_count'];
        });
        
        $this->view('admin/projects/imgtxt/languages', [
            'title' => 'ImgTxt Admin - Languages',
            'languages' => $stats
        ]);
    }
    
    /**
     * Retry failed job
     */
    public function retryJob(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/projects/imgtxt/jobs');
            return;
        }
        
        $id = (int)($_POST['job_id'] ?? 0);
        
        if ($id > 0) {
            $this->projectDb->update('ocr_jobs', [
                'status' => 'pending',
                'error_message' => null,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$id]);
            
            Logger::activity(Auth::id(), 'imgtxt_job_retry', ['job_id' => $id]);
            $this->flash('success', 'Job queued for retry.');
        }
        
        $this->redirect('/admin/projects/imgtxt/jobs');
    }
    
    /**
     * Delete job
     */
    public function deleteJob(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/projects/imgtxt/jobs');
            return;
        }
        
        $id = (int)($_POST['job_id'] ?? 0);
        
        if ($id > 0) {
            $this->projectDb->delete('ocr_jobs', 'id = ?', [$id]);
            Logger::activity(Auth::id(), 'imgtxt_job_deleted', ['job_id' => $id]);
            $this->flash('success', 'Job deleted successfully.');
        }
        
        $this->redirect('/admin/projects/imgtxt/jobs');
    }
    
    /**
     * User management for ImgTxt
     */
    public function users(): void
    {
        // Get all users who have used ImgTxt from project DB
        $imgtxtUserIds = $this->projectDb->fetchAll(
            "SELECT DISTINCT user_id FROM ocr_jobs WHERE user_id IS NOT NULL"
        );
        
        $userIds = array_column($imgtxtUserIds, 'user_id');
        
        if (empty($userIds)) {
            $this->view('admin/projects/imgtxt/users', [
                'title' => 'ImgTxt Admin - User Management',
                'users' => []
            ]);
            return;
        }
        
        // Get user details from main DB
        $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
        $users = $this->mainDb->fetchAll(
            "SELECT id, name, email, created_at FROM users WHERE id IN ($placeholders)",
            $userIds
        );
        
        // Get OCR job statistics for each user
        foreach ($users as &$user) {
            $stats = $this->projectDb->fetch(
                "SELECT 
                    COUNT(*) as total_jobs,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_jobs,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_jobs,
                    MAX(created_at) as last_activity
                 FROM ocr_jobs 
                 WHERE user_id = ?",
                [$user['id']]
            );
            
            $user['total_jobs'] = $stats['total_jobs'] ?? 0;
            $user['completed_jobs'] = $stats['completed_jobs'] ?? 0;
            $user['failed_jobs'] = $stats['failed_jobs'] ?? 0;
            $user['last_activity'] = $stats['last_activity'] ?? null;
            
            // Calculate success rate
            if ($user['total_jobs'] > 0) {
                $user['success_rate'] = round(($user['completed_jobs'] / $user['total_jobs']) * 100, 1);
            } else {
                $user['success_rate'] = 0;
            }
        }
        unset($user);
        
        // Sort by total jobs (most active first)
        usort($users, function($a, $b) {
            return $b['total_jobs'] - $a['total_jobs'];
        });
        
        $this->view('admin/projects/imgtxt/users', [
            'title' => 'ImgTxt Admin - User Management',
            'users' => $users
        ]);
    }
    
    /**
     * Statistics and analytics
     */
    public function statistics(): void
    {
        // Overall statistics
        $overallStats = [
            'total_jobs' => $this->projectDb->fetch("SELECT COUNT(*) as count FROM ocr_jobs")['count'] ?? 0,
            'completed_jobs' => $this->projectDb->fetch("SELECT COUNT(*) as count FROM ocr_jobs WHERE status = 'completed'")['count'] ?? 0,
            'failed_jobs' => $this->projectDb->fetch("SELECT COUNT(*) as count FROM ocr_jobs WHERE status = 'failed'")['count'] ?? 0,
            'pending_jobs' => $this->projectDb->fetch("SELECT COUNT(*) as count FROM ocr_jobs WHERE status = 'pending'")['count'] ?? 0,
            'processing_jobs' => $this->projectDb->fetch("SELECT COUNT(*) as count FROM ocr_jobs WHERE status = 'processing'")['count'] ?? 0,
            'active_users' => $this->projectDb->fetch("SELECT COUNT(DISTINCT user_id) as count FROM ocr_jobs WHERE user_id IS NOT NULL")['count'] ?? 0,
        ];
        
        // Calculate success rate
        if ($overallStats['total_jobs'] > 0) {
            $overallStats['success_rate'] = round(($overallStats['completed_jobs'] / $overallStats['total_jobs']) * 100, 1);
            $overallStats['failure_rate'] = round(($overallStats['failed_jobs'] / $overallStats['total_jobs']) * 100, 1);
        } else {
            $overallStats['success_rate'] = 0;
            $overallStats['failure_rate'] = 0;
        }
        
        // Daily statistics (last 30 days)
        $dailyStats = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $stats = $this->projectDb->fetch(
                "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
                 FROM ocr_jobs 
                 WHERE DATE(created_at) = ?",
                [$date]
            );
            
            $dailyStats[] = [
                'date' => date('M d', strtotime($date)),
                'full_date' => $date,
                'total' => $stats['total'] ?? 0,
                'completed' => $stats['completed'] ?? 0,
                'failed' => $stats['failed'] ?? 0
            ];
        }
        
        // Language usage statistics
        $languageStats = $this->projectDb->fetchAll(
            "SELECT 
                language,
                COUNT(*) as count,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
             FROM ocr_jobs 
             WHERE language IS NOT NULL 
             GROUP BY language 
             ORDER BY count DESC"
        );
        
        // Calculate completion rate for each language
        foreach ($languageStats as &$lang) {
            if ($lang['count'] > 0) {
                $lang['completion_rate'] = round(($lang['completed'] / $lang['count']) * 100, 1);
            } else {
                $lang['completion_rate'] = 0;
            }
        }
        unset($lang);
        
        // Hourly distribution (24 hours)
        $hourlyStats = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $count = $this->projectDb->fetch(
                "SELECT COUNT(*) as count 
                 FROM ocr_jobs 
                 WHERE HOUR(created_at) = ?",
                [$hour]
            )['count'] ?? 0;
            
            $hourlyStats[] = [
                'hour' => sprintf('%02d:00', $hour),
                'count' => $count
            ];
        }
        
        // Top users by job count
        $topUsers = $this->projectDb->fetchAll(
            "SELECT user_id, COUNT(*) as job_count 
             FROM ocr_jobs 
             WHERE user_id IS NOT NULL 
             GROUP BY user_id 
             ORDER BY job_count DESC 
             LIMIT 10"
        );
        
        // Get user details from main DB
        if (!empty($topUsers)) {
            $userIds = array_column($topUsers, 'user_id');
            $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
            $users = $this->mainDb->fetchAll(
                "SELECT id, name, email FROM users WHERE id IN ($placeholders)",
                $userIds
            );
            
            $userLookup = [];
            foreach ($users as $user) {
                $userLookup[$user['id']] = $user;
            }
            
            foreach ($topUsers as &$item) {
                if (isset($userLookup[$item['user_id']])) {
                    $item['user_name'] = $userLookup[$item['user_id']]['name'];
                    $item['email'] = $userLookup[$item['user_id']]['email'];
                } else {
                    $item['user_name'] = 'Unknown';
                    $item['email'] = '';
                }
            }
            unset($item);
        }
        
        // Average processing time (for completed jobs)
        $avgProcessingTime = $this->projectDb->fetch(
            "SELECT AVG(TIMESTAMPDIFF(SECOND, created_at, updated_at)) as avg_seconds 
             FROM ocr_jobs 
             WHERE status = 'completed' AND updated_at IS NOT NULL"
        )['avg_seconds'] ?? 0;
        
        $this->view('admin/projects/imgtxt/statistics', [
            'title' => 'ImgTxt Admin - Statistics',
            'overallStats' => $overallStats,
            'dailyStats' => $dailyStats,
            'dailyTrends' => $dailyStats,  // Add alias for view compatibility
            'languageStats' => $languageStats,
            'languageUsage' => $languageStats, // Add alias for view compatibility
            'hourlyStats' => $hourlyStats,
            'topUsers' => $topUsers,
            'avgProcessingTime' => round($avgProcessingTime, 2)
        ]);
    }
    
    /**
     * Activity logs with comprehensive OCR job details
     */
    public function activity(): void
    {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
        $jobId = isset($_GET['job_id']) ? (int)$_GET['job_id'] : null;
        
        // Build where clause for filtering
        $whereClause = '';
        $params = [];
        if ($userId) {
            $whereClause .= ' AND j.user_id = ?';
            $params[] = $userId;
        }
        if ($jobId) {
            $whereClause .= ' AND j.id = ?';
            $params[] = $jobId;
        }
        
        // Get OCR jobs with comprehensive details from project DB
        $jobs = $this->projectDb->fetchAll(
            "SELECT * FROM ocr_jobs j 
             WHERE 1=1 {$whereClause}
             ORDER BY created_at DESC 
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );
        
        // Get user details for jobs
        if (!empty($jobs)) {
            $userIds = array_unique(array_filter(array_column($jobs, 'user_id')));
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
                
                // Enhance jobs with user data and calculated fields
                foreach ($jobs as &$job) {
                    // User information
                    if (isset($job['user_id']) && isset($userLookup[$job['user_id']])) {
                        $job['user_name'] = $userLookup[$job['user_id']]['name'];
                        $job['user_email'] = $userLookup[$job['user_id']]['email'];
                    } else {
                        $job['user_name'] = 'Unknown';
                        $job['user_email'] = '';
                    }
                    
                    // Calculate word and character counts
                    if (!empty($job['extracted_text'])) {
                        $job['character_count'] = mb_strlen($job['extracted_text']);
                        $job['word_count'] = str_word_count($job['extracted_text']);
                        $job['text_preview'] = mb_substr($job['extracted_text'], 0, 200);
                    } else {
                        $job['character_count'] = 0;
                        $job['word_count'] = 0;
                        $job['text_preview'] = '';
                    }
                    
                    // Format file size
                    $size = $job['file_size'];
                    if ($size < 1024) {
                        $job['formatted_size'] = $size . ' B';
                    } elseif ($size < 1048576) {
                        $job['formatted_size'] = round($size / 1024, 2) . ' KB';
                    } else {
                        $job['formatted_size'] = round($size / 1048576, 2) . ' MB';
                    }
                    
                    // Processing time in milliseconds
                    if ($job['processing_time']) {
                        $job['processing_time_ms'] = $job['processing_time'] * 1000;
                    } else {
                        $job['processing_time_ms'] = 0;
                    }
                    
                    // Language full name
                    $languageNames = [
                        'eng' => 'English',
                        'ara' => 'Arabic',
                        'chi_sim' => 'Chinese (Simplified)',
                        'chi_tra' => 'Chinese (Traditional)',
                        'fra' => 'French',
                        'deu' => 'German',
                        'hin' => 'Hindi',
                        'jpn' => 'Japanese',
                        'kor' => 'Korean',
                        'por' => 'Portuguese',
                        'rus' => 'Russian',
                        'spa' => 'Spanish',
                        'tur' => 'Turkish',
                        'vie' => 'Vietnamese'
                    ];
                    $job['language_name'] = $languageNames[$job['language']] ?? $job['language'];
                }
                unset($job);
            }
        }
        
        // Get total count
        $totalCount = $this->projectDb->fetch(
            "SELECT COUNT(*) as count FROM ocr_jobs j WHERE 1=1 {$whereClause}",
            $params
        )['count'];
        
        $totalPages = ceil($totalCount / $perPage);
        
        // Get user info if filtering
        $filterUser = null;
        if ($userId) {
            $filterUser = $this->mainDb->fetch(
                "SELECT id, name, email FROM users WHERE id = ?",
                [$userId]
            );
        }
        
        $this->view('admin/projects/imgtxt/activity', [
            'title' => 'ImgTxt Admin - Activity Logs',
            'logs' => $jobs, // Actually OCR jobs with full details
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount,
            'perPage' => $perPage,
            'filterUser' => $filterUser
        ]);
    }
}
