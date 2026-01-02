<?php
/**
 * Admin Log Controller
 * 
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;
use Core\Logger;

class LogController extends BaseController
{
    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
    }
    
    /**
     * Log viewer dashboard
     */
    public function index(): void
    {
        $this->view('admin/logs/index', [
            'title' => 'Logs'
        ]);
    }
    
    /**
     * Activity logs
     */
    public function activity(): void
    {
        $db = Database::getInstance();
        
        $page = max(1, (int) $this->input('page', 1));
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        $action = $this->input('action', '');
        $userId = $this->input('user_id', '');
        
        $where = "1=1";
        $params = [];
        
        if ($action) {
            $where .= " AND al.action = ?";
            $params[] = $action;
        }
        
        if ($userId) {
            $where .= " AND al.user_id = ?";
            $params[] = (int) $userId;
        }
        
        $logs = $db->fetchAll(
            "SELECT al.*, u.name, u.email 
             FROM activity_logs al 
             LEFT JOIN users u ON al.user_id = u.id 
             WHERE {$where} 
             ORDER BY al.created_at DESC 
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );
        
        $total = $db->fetch(
            "SELECT COUNT(*) as count FROM activity_logs al WHERE {$where}",
            $params
        );
        
        // Get unique actions for filter
        $actions = $db->fetchAll("SELECT DISTINCT action FROM activity_logs ORDER BY action");
        
        $this->view('admin/logs/activity', [
            'title' => 'Activity Logs',
            'logs' => $logs,
            'actions' => $actions,
            'currentAction' => $action,
            'currentUserId' => $userId,
            'pagination' => [
                'current' => $page,
                'total' => ceil($total['count'] / $perPage),
                'perPage' => $perPage
            ]
        ]);
    }
    
    /**
     * System logs
     */
    public function system(): void
    {
        $logFiles = Logger::getLogFiles();
        $selectedFile = $this->input('file', '');
        $logContent = [];
        
        if ($selectedFile && in_array($selectedFile, $logFiles)) {
            $logContent = Logger::readLog($selectedFile, 200);
        }
        
        $this->view('admin/logs/system', [
            'title' => 'System Logs',
            'logFiles' => $logFiles,
            'selectedFile' => $selectedFile,
            'logContent' => $logContent
        ]);
    }
}
