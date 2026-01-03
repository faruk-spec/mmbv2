<?php
/**
 * Admin Session Controller
 * Manages user sessions
 * 
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;
use Core\Security;
use Core\Auth;
use Core\Logger;
use Core\SessionManager;

class SessionController extends BaseController
{
    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
    }
    
    /**
     * List active sessions
     */
    public function index(): void
    {
        $db = Database::getInstance();
        
        $page = max(1, (int) $this->input('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        $search = $this->input('search', '');
        $where = "is_active = 1";
        $params = [];
        
        if ($search) {
            $where .= " AND (u.name LIKE ? OR u.email LIKE ? OR us.ip_address LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        $sessions = $db->fetchAll(
            "SELECT us.*, u.name as user_name, u.email as user_email
             FROM user_sessions us
             JOIN users u ON us.user_id = u.id
             WHERE {$where}
             ORDER BY us.last_activity_at DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );
        
        $total = $db->fetch(
            "SELECT COUNT(*) as count FROM user_sessions us 
             JOIN users u ON us.user_id = u.id 
             WHERE {$where}",
            $params
        );
        
        $this->view('admin/sessions/index', [
            'title' => 'Active Sessions',
            'sessions' => $sessions,
            'search' => $search,
            'pagination' => [
                'current' => $page,
                'total' => ceil($total['count'] / $perPage),
                'perPage' => $perPage
            ]
        ]);
    }
    
    /**
     * Revoke session
     */
    public function revoke(string $id): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/sessions');
            return;
        }
        
        if (SessionManager::revokeSession((int) $id)) {
            Logger::activity(Auth::id(), 'session_revoked', ['session_id' => (int) $id]);
            $this->flash('success', 'Session revoked successfully.');
        } else {
            $this->flash('error', 'Failed to revoke session.');
        }
        
        $this->redirect('/admin/sessions');
    }
    
    /**
     * Cleanup expired sessions
     */
    public function cleanup(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/sessions');
            return;
        }
        
        $count = SessionManager::cleanupExpiredSessions();
        
        Logger::activity(Auth::id(), 'sessions_cleaned', ['count' => $count]);
        
        $this->flash('success', "Cleaned up {$count} expired sessions.");
        $this->redirect('/admin/sessions');
    }
    
    /**
     * View login history
     */
    public function loginHistory(): void
    {
        $db = Database::getInstance();
        
        $page = max(1, (int) $this->input('page', 1));
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        $search = $this->input('search', '');
        $status = $this->input('status', '');
        $method = $this->input('method', '');
        
        $where = "1=1";
        $params = [];
        
        if ($search) {
            $where .= " AND (lh.email LIKE ? OR lh.ip_address LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        if ($status) {
            $where .= " AND lh.status = ?";
            $params[] = $status;
        }
        
        if ($method) {
            $where .= " AND lh.login_method = ?";
            $params[] = $method;
        }
        
        $history = $db->fetchAll(
            "SELECT lh.*, u.name as user_name
             FROM login_history lh
             LEFT JOIN users u ON lh.user_id = u.id
             WHERE {$where}
             ORDER BY lh.created_at DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );
        
        $total = $db->fetch(
            "SELECT COUNT(*) as count FROM login_history lh WHERE {$where}",
            $params
        );
        
        $this->view('admin/sessions/login-history', [
            'title' => 'Login History',
            'history' => $history,
            'search' => $search,
            'status' => $status,
            'method' => $method,
            'pagination' => [
                'current' => $page,
                'total' => ceil($total['count'] / $perPage),
                'perPage' => $perPage
            ]
        ]);
    }
}
