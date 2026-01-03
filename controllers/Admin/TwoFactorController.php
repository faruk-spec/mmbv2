<?php
/**
 * Admin Two-Factor Authentication Controller
 * 
 * Manages 2FA for all users from admin panel
 * 
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;
use Core\Auth;
use Core\Logger;
use Core\Helpers;

class TwoFactorController extends BaseController
{
    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
    }
    
    /**
     * List all users with 2FA status
     */
    public function index(): void
    {
        $db = Database::getInstance();
        
        // Search functionality
        $search = $this->input('search', '');
        $status = $this->input('status', 'all'); // all, enabled, disabled
        
        $sql = "SELECT id, name, email, two_factor_enabled, two_factor_enabled_at, created_at, last_login_at 
                FROM users WHERE 1=1";
        $params = [];
        
        if ($search) {
            $sql .= " AND (name LIKE ? OR email LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        if ($status === 'enabled') {
            $sql .= " AND two_factor_enabled = 1";
        } elseif ($status === 'disabled') {
            $sql .= " AND (two_factor_enabled = 0 OR two_factor_enabled IS NULL)";
        }
        
        $sql .= " ORDER BY two_factor_enabled DESC, name ASC";
        
        $users = $db->fetchAll($sql, $params);
        
        // Get statistics
        $stats = [
            'total_users' => $db->fetch("SELECT COUNT(*) as count FROM users")['count'],
            '2fa_enabled' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE two_factor_enabled = 1")['count'],
            '2fa_disabled' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE two_factor_enabled = 0 OR two_factor_enabled IS NULL")['count'],
        ];
        
        $this->view('admin/2fa/index', [
            'title' => '2FA Management',
            'users' => $users,
            'stats' => $stats,
            'search' => $search,
            'status' => $status
        ]);
    }
    
    /**
     * Reset user's 2FA (force them to re-setup)
     */
    public function reset(int $userId): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/2fa');
            return;
        }
        
        try {
            $db = Database::getInstance();
            
            // Get user info
            $user = $db->fetch("SELECT name, email FROM users WHERE id = ?", [$userId]);
            
            if (!$user) {
                $this->flash('error', 'User not found.');
                $this->redirect('/admin/2fa');
                return;
            }
            
            // Disable 2FA and clear secrets
            $db->update('users', [
                'two_factor_secret' => null,
                'two_factor_enabled' => 0,
                'two_factor_backup_codes' => null,
                'two_factor_enabled_at' => null,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$userId]);
            
            // Log activity
            Logger::activity(Auth::id(), '2fa_reset_by_admin', [
                'target_user_id' => $userId,
                'target_user_email' => $user['email']
            ]);
            
            $this->flash('success', "2FA has been reset for {$user['name']}. They will need to set it up again.");
            
        } catch (\Exception $e) {
            Logger::error('Admin 2FA reset error: ' . $e->getMessage());
            $this->flash('error', 'Failed to reset 2FA.');
        }
        
        $this->redirect('/admin/2fa');
    }
    
    /**
     * Toggle 2FA status for a user
     */
    public function toggle(int $userId): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/2fa');
            return;
        }
        
        try {
            $db = Database::getInstance();
            
            // Get user info
            $user = $db->fetch("SELECT name, email, two_factor_enabled, two_factor_secret FROM users WHERE id = ?", [$userId]);
            
            if (!$user) {
                $this->flash('error', 'User not found.');
                $this->redirect('/admin/2fa');
                return;
            }
            
            // Can only disable if currently enabled and has secret
            if ($user['two_factor_enabled'] && !empty($user['two_factor_secret'])) {
                // Disable 2FA
                $db->update('users', [
                    'two_factor_secret' => null,
                    'two_factor_enabled' => 0,
                    'two_factor_backup_codes' => null,
                    'two_factor_enabled_at' => null,
                    'updated_at' => date('Y-m-d H:i:s')
                ], 'id = ?', [$userId]);
                
                Logger::activity(Auth::id(), '2fa_disabled_by_admin', [
                    'target_user_id' => $userId,
                    'target_user_email' => $user['email']
                ]);
                
                $this->flash('success', "2FA has been disabled for {$user['name']}.");
            } else {
                $this->flash('error', '2FA is not enabled for this user or they haven\'t completed setup.');
            }
            
        } catch (\Exception $e) {
            Logger::error('Admin 2FA toggle error: ' . $e->getMessage());
            $this->flash('error', 'Failed to toggle 2FA.');
        }
        
        $this->redirect('/admin/2fa');
    }
}
