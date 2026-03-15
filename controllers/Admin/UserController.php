<?php
/**
 * Admin User Controller
 * 
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;
use Core\Security;
use Core\Auth;
use Core\Logger;

class UserController extends BaseController
{
    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
    }
    
    /**
     * List all users
     */
    public function index(): void
    {
        $db = Database::getInstance();
        
        $page = max(1, (int) $this->input('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        $search = $this->input('search', '');
        $role = $this->input('role', '');
        $status = $this->input('status', '');
        
        $where = "1=1";
        $params = [];
        
        if ($search) {
            $where .= " AND (name LIKE ? OR email LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        if ($role) {
            $where .= " AND role = ?";
            $params[] = $role;
        }
        
        if ($status) {
            $where .= " AND status = ?";
            $params[] = $status;
        }
        
        $users = $db->fetchAll(
            "SELECT * FROM users WHERE {$where} ORDER BY created_at DESC LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );
        
        $total = $db->fetch(
            "SELECT COUNT(*) as count FROM users WHERE {$where}",
            $params
        );
        
        $this->view('admin/users/index', [
            'title' => 'User Management',
            'users' => $users,
            'search' => $search,
            'role' => $role,
            'status' => $status,
            'pagination' => [
                'current' => $page,
                'total' => ceil($total['count'] / $perPage),
                'perPage' => $perPage
            ]
        ]);
    }
    
    /**
     * Create user form
     */
    public function create(): void
    {
        $this->view('admin/users/create', [
            'title' => 'Create User'
        ]);
    }
    
    /**
     * Store new user
     */
    public function store(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/users/create');
            return;
        }
        
        $errors = $this->validate([
            'name' => 'required|min:2|max:50',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role' => 'required'
        ]);
        
        if (!empty($errors)) {
            $this->redirect('/admin/users/create');
            return;
        }
        
        try {
            $db = Database::getInstance();
            
            $userId = $db->insert('users', [
                'name' => Security::sanitize($this->input('name')),
                'email' => $this->input('email'),
                'password' => Security::hashPassword($this->input('password')),
                'role' => $this->input('role'),
                'status' => $this->input('status', 'active'),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Create profile
            $db->insert('user_profiles', [
                'user_id' => $userId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            Logger::activity(Auth::id(), 'user_created', ['user_id' => $userId]);
            
            $this->flash('success', 'User created successfully.');
            $this->redirect('/admin/users');
            
        } catch (\Exception $e) {
            Logger::error('User creation error: ' . $e->getMessage());
            $this->flash('error', 'Failed to create user.');
            $this->redirect('/admin/users/create');
        }
    }
    
    /**
     * Edit user form
     */
    /**
     * All apps available on the platform (slug => label, icon)
     */
    private const APPS = [
        'qr'        => ['label' => 'QR Generator',   'icon' => 'fas fa-qrcode',        'color' => '#00f0ff'],
        'whatsapp'  => ['label' => 'WhatsApp API',    'icon' => 'fab fa-whatsapp',       'color' => '#25d366'],
        'proshare'  => ['label' => 'ProShare',        'icon' => 'fas fa-share-alt',      'color' => '#ff2ec4'],
        'codexpro'  => ['label' => 'CodeXPro',        'icon' => 'fas fa-code',           'color' => '#9b59b6'],
        'imgtxt'    => ['label' => 'ImgTxt',          'icon' => 'fas fa-image',          'color' => '#f39c12'],
        'convertx'  => ['label' => 'ConvertX',        'icon' => 'fas fa-file-export',    'color' => '#3498db'],
        'billx'     => ['label' => 'BillX',           'icon' => 'fas fa-file-invoice',   'color' => '#2ecc71'],
        'resumex'   => ['label' => 'ResumeX',         'icon' => 'fas fa-file-alt',       'color' => '#e67e22'],
        'devzone'   => ['label' => 'DevZone',         'icon' => 'fas fa-terminal',       'color' => '#e74c3c'],
    ];

    public function edit(string $id): void
    {
        $db = Database::getInstance();
        $user = $db->fetch("SELECT * FROM users WHERE id = ?", [(int) $id]);
        
        if (!$user) {
            $this->flash('error', 'User not found.');
            $this->redirect('/admin/users');
            return;
        }

        // QR subscription plans for plan-assignment dropdown
        $qrPlans = [];
        $userQrPlan = null;
        try {
            $qrPlans = $db->fetchAll(
                "SELECT id, name, slug FROM qr_subscription_plans WHERE status = 'active' ORDER BY price ASC"
            );
            $userQrPlan = $db->fetch(
                "SELECT s.plan_id, p.name AS plan_name
                 FROM qr_user_subscriptions s
                 JOIN qr_subscription_plans p ON p.id = s.plan_id
                 WHERE s.user_id = ? AND s.status = 'active'
                 LIMIT 1",
                [(int) $id]
            );
        } catch (\Exception $e) {
            // QR tables may not exist yet; silently ignore
        }

        $this->view('admin/users/edit', [
            'title'      => 'Edit User',
            'editUser'   => $user,
            'qrPlans'    => $qrPlans,
            'userQrPlan' => $userQrPlan,
            'apps'       => self::APPS,
            'userApps'   => json_decode($user['app_access'] ?? 'null', true),
        ]);
    }
    
    /**
     * Update user
     */
    public function update(string $id): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/users/' . $id . '/edit');
            return;
        }
        
        $db = Database::getInstance();
        $user = $db->fetch("SELECT * FROM users WHERE id = ?", [(int) $id]);
        
        if (!$user) {
            $this->flash('error', 'User not found.');
            $this->redirect('/admin/users');
            return;
        }
        
        $errors = $this->validate([
            'name' => 'required|min:2|max:50',
            'email' => 'required|email'
        ]);
        
        if (!empty($errors)) {
            $this->redirect('/admin/users/' . $id . '/edit');
            return;
        }
        
        // Check email uniqueness
        if ($this->input('email') !== $user['email']) {
            $existing = $db->fetch(
                "SELECT id FROM users WHERE email = ? AND id != ?",
                [$this->input('email'), (int) $id]
            );
            if ($existing) {
                $this->flash('error', 'Email already exists.');
                $this->redirect('/admin/users/' . $id . '/edit');
                return;
            }
        }
        
        try {
            $updateData = [
                'name' => Security::sanitize($this->input('name')),
                'email' => $this->input('email'),
                'role' => $this->input('role'),
                'status' => $this->input('status'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // App access: null = all apps allowed; array = restricted list
            $submitted = $this->input('app_access');  // hidden JSON or checkboxes
            if ($submitted !== null) {
                // Checkbox names come as app_access[] in POST
                $selectedApps = isset($_POST['app_access']) ? (array)$_POST['app_access'] : null;
                if ($selectedApps !== null) {
                    // Whitelist against known apps
                    $validSlugs   = array_keys(self::APPS);
                    $selectedApps = array_values(array_filter($selectedApps, fn($s) => in_array($s, $validSlugs, true)));
                    // Empty selection = restrict to nothing; null = no restriction
                    $updateData['app_access'] = json_encode($selectedApps);
                } else {
                    $updateData['app_access'] = null;
                }
            } elseif (isset($_POST['app_access'])) {
                $validSlugs   = array_keys(self::APPS);
                $selectedApps = array_values(array_filter((array)$_POST['app_access'], fn($s) => in_array($s, $validSlugs, true)));
                $updateData['app_access'] = json_encode($selectedApps);
            } elseif (isset($_POST['app_access_unrestricted'])) {
                $updateData['app_access'] = null;
            }
            
            // Update password if provided
            $newPassword = $this->input('password');
            if (!empty($newPassword)) {
                $updateData['password'] = Security::hashPassword($newPassword);
            }
            
            $db->update('users', $updateData, 'id = ?', [(int) $id]);
            
            Logger::activity(Auth::id(), 'user_updated', ['user_id' => (int) $id]);
            
            $this->flash('success', 'User updated successfully.');
            $this->redirect('/admin/users');
            
        } catch (\Exception $e) {
            Logger::error('User update error: ' . $e->getMessage());
            $this->flash('error', 'Failed to update user.');
            $this->redirect('/admin/users/' . $id . '/edit');
        }
    }
    
    /**
     * Delete user
     */
    public function delete(string $id): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/users');
            return;
        }
        
        $userId = (int) $id;
        
        // Prevent self-deletion
        if ($userId === Auth::id()) {
            $this->flash('error', 'Cannot delete your own account.');
            $this->redirect('/admin/users');
            return;
        }
        
        try {
            $db = Database::getInstance();
            
            // Delete related data
            $db->delete('user_profiles', 'user_id = ?', [$userId]);
            $db->delete('user_remember_tokens', 'user_id = ?', [$userId]);
            $db->delete('activity_logs', 'user_id = ?', [$userId]);
            $db->delete('users', 'id = ?', [$userId]);
            
            Logger::activity(Auth::id(), 'user_deleted', ['user_id' => $userId]);
            
            $this->flash('success', 'User deleted successfully.');
            
        } catch (\Exception $e) {
            Logger::error('User deletion error: ' . $e->getMessage());
            $this->flash('error', 'Failed to delete user.');
        }
        
        $this->redirect('/admin/users');
    }
    
    /**
     * Toggle user status
     */
    public function toggle(string $id): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/users');
            return;
        }
        
        $userId = (int) $id;
        
        // Prevent self-disable
        if ($userId === Auth::id()) {
            $this->flash('error', 'Cannot disable your own account.');
            $this->redirect('/admin/users');
            return;
        }
        
        try {
            $db = Database::getInstance();
            $user = $db->fetch("SELECT status FROM users WHERE id = ?", [$userId]);
            
            if ($user) {
                $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
                $db->update('users', [
                    'status' => $newStatus,
                    'updated_at' => date('Y-m-d H:i:s')
                ], 'id = ?', [$userId]);
                
                Logger::activity(Auth::id(), 'user_status_changed', [
                    'user_id' => $userId,
                    'status' => $newStatus
                ]);
                
                $this->flash('success', 'User status updated.');
            }
            
        } catch (\Exception $e) {
            Logger::error('User toggle error: ' . $e->getMessage());
            $this->flash('error', 'Failed to update user status.');
        }
        
        $this->redirect('/admin/users');
    }
}
