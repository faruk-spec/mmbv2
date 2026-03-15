<?php
/**
 * Admin User Access Controller
 *
 * Allows super_admin / admin to grant or revoke granular admin panel
 * permissions for individual users.
 *
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;
use Core\Logger;
use Core\Security;

class AdminUserAccessController extends BaseController
{
    /**
     * All grantable admin panel permissions grouped by section.
     *
     * Each entry: 'key' => ['label', 'icon', 'description']
     */
    public const PERMISSIONS = [
        'dashboard' => [
            'label'       => 'Dashboard',
            'icon'        => 'fas fa-tachometer-alt',
            'description' => 'View the main admin dashboard and stats',
            'group'       => 'Core',
        ],
        'users' => [
            'label'       => 'Users',
            'icon'        => 'fas fa-users',
            'description' => 'View and manage users',
            'group'       => 'Core',
        ],
        'platform_plans' => [
            'label'       => 'Platform Plans',
            'icon'        => 'fas fa-layer-group',
            'description' => 'Manage platform subscription plans',
            'group'       => 'Core',
        ],
        // ── Projects / Modules ────────────────────────────────────────────
        'qr' => [
            'label'       => 'QR Generator',
            'icon'        => 'fas fa-qrcode',
            'description' => 'Access QR code admin panel',
            'group'       => 'Modules',
        ],
        'whatsapp' => [
            'label'       => 'WhatsApp API',
            'icon'        => 'fab fa-whatsapp',
            'description' => 'Access WhatsApp API admin panel',
            'group'       => 'Modules',
        ],
        'convertx' => [
            'label'       => 'ConvertX',
            'icon'        => 'fas fa-file-export',
            'description' => 'Access ConvertX admin panel',
            'group'       => 'Modules',
        ],
        'codexpro' => [
            'label'       => 'CodeXPro',
            'icon'        => 'fas fa-code',
            'description' => 'Access CodeXPro admin panel',
            'group'       => 'Modules',
        ],
        'imgtxt' => [
            'label'       => 'ImgTxt',
            'icon'        => 'fas fa-image',
            'description' => 'Access ImgTxt admin panel',
            'group'       => 'Modules',
        ],
        'proshare' => [
            'label'       => 'ProShare',
            'icon'        => 'fas fa-share-alt',
            'description' => 'Access ProShare admin panel',
            'group'       => 'Modules',
        ],
        'billx' => [
            'label'       => 'BillX',
            'icon'        => 'fas fa-file-invoice',
            'description' => 'Access BillX admin panel',
            'group'       => 'Modules',
        ],
        'resumex' => [
            'label'       => 'ResumeX',
            'icon'        => 'fas fa-file-alt',
            'description' => 'Access ResumeX admin panel',
            'group'       => 'Modules',
        ],
        'devzone' => [
            'label'       => 'DevZone',
            'icon'        => 'fas fa-terminal',
            'description' => 'Access DevZone admin panel',
            'group'       => 'Modules',
        ],
        // ── Security ──────────────────────────────────────────────────────
        'security' => [
            'label'       => 'Security Center',
            'icon'        => 'fas fa-shield-alt',
            'description' => 'View security alerts, blocked IPs and failed logins',
            'group'       => 'Security',
        ],
        'oauth' => [
            'label'       => 'OAuth & SSO',
            'icon'        => 'fas fa-key',
            'description' => 'Manage OAuth providers and connections',
            'group'       => 'Security',
        ],
        'sessions' => [
            'label'       => 'Session Management',
            'icon'        => 'fas fa-clock',
            'description' => 'View and terminate active sessions',
            'group'       => 'Security',
        ],
        '2fa' => [
            'label'       => '2FA Management',
            'icon'        => 'fas fa-shield-alt',
            'description' => 'View and manage user 2FA status',
            'group'       => 'Security',
        ],
        // ── Logs ─────────────────────────────────────────────────────────
        'logs' => [
            'label'       => 'Activity Logs',
            'icon'        => 'fas fa-file-alt',
            'description' => 'View system and user activity logs',
            'group'       => 'Logs',
        ],
        'audit' => [
            'label'       => 'Audit Explorer',
            'icon'        => 'fas fa-search',
            'description' => 'Query and explore the audit trail',
            'group'       => 'Logs',
        ],
        // ── Settings ─────────────────────────────────────────────────────
        'settings' => [
            'label'       => 'Settings',
            'icon'        => 'fas fa-cog',
            'description' => 'Access system settings',
            'group'       => 'Settings',
        ],
        'navbar' => [
            'label'       => 'Navbar & Branding',
            'icon'        => 'fas fa-paint-brush',
            'description' => 'Manage site navbar and branding',
            'group'       => 'Settings',
        ],
        'api' => [
            'label'       => 'API Management',
            'icon'        => 'fas fa-plug',
            'description' => 'Manage API keys and access',
            'group'       => 'Settings',
        ],
        'performance' => [
            'label'       => 'Performance',
            'icon'        => 'fas fa-tachometer-alt',
            'description' => 'Cache and performance tools',
            'group'       => 'Settings',
        ],
        'analytics' => [
            'label'       => 'Analytics',
            'icon'        => 'fas fa-chart-bar',
            'description' => 'View platform analytics',
            'group'       => 'Settings',
        ],
    ];

    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
    }

    /**
     * List all users with their current permission summary.
     */
    public function index(): void
    {
        $db = Database::getInstance();

        $users = $db->fetchAll(
            "SELECT u.id, u.name, u.email, u.role, u.status,
                    COUNT(p.id) AS perm_count
             FROM users u
             LEFT JOIN admin_user_permissions p ON p.user_id = u.id
             GROUP BY u.id
             ORDER BY u.name"
        );

        $this->view('admin/admin-access/index', [
            'title' => 'Admin Users Access',
            'users' => $users,
        ]);
    }

    /**
     * Edit permissions for a single user (GET).
     */
    public function editForm(string $userId): void
    {
        $db = Database::getInstance();

        $user = $db->fetch("SELECT id, name, email, role FROM users WHERE id = ?", [(int) $userId]);
        if (!$user) {
            $this->flash('error', 'User not found.');
            $this->redirect('/admin/admin-access');
            return;
        }

        $granted = $db->fetchAll(
            "SELECT permission_key FROM admin_user_permissions WHERE user_id = ?",
            [(int) $userId]
        );
        $grantedKeys = array_column($granted, 'permission_key');

        $this->view('admin/admin-access/edit', [
            'title'       => 'Admin Access — ' . $user['name'],
            'targetUser'  => $user,
            'permissions' => self::PERMISSIONS,
            'grantedKeys' => $grantedKeys,
        ]);
    }

    /**
     * Save permissions for a user (POST).
     */
    public function save(string $userId): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request token.');
            $this->redirect('/admin/admin-access/' . $userId . '/edit');
            return;
        }

        $db   = Database::getInstance();
        $user = $db->fetch("SELECT id, name FROM users WHERE id = ?", [(int) $userId]);
        if (!$user) {
            $this->flash('error', 'User not found.');
            $this->redirect('/admin/admin-access');
            return;
        }

        // Collect submitted permissions
        $submitted   = isset($_POST['permissions']) ? (array)$_POST['permissions'] : [];
        $allowedKeys = array_keys(self::PERMISSIONS);
        $newKeys     = array_values(array_filter($submitted, fn($k) => in_array($k, $allowedKeys, true)));

        // Old keys for audit log
        $oldRows = $db->fetchAll(
            "SELECT permission_key FROM admin_user_permissions WHERE user_id = ?",
            [(int) $userId]
        );
        $oldKeys = array_column($oldRows, 'permission_key');

        try {
            $db->beginTransaction();

            // Delete all existing permissions for this user, then batch re-insert
            $db->execute(
                "DELETE FROM admin_user_permissions WHERE user_id = ?",
                [(int) $userId]
            );

            if (!empty($newKeys)) {
                $now         = date('Y-m-d H:i:s');
                $grantedBy   = Auth::id();
                $placeholders = implode(', ', array_fill(0, count($newKeys), '(?, ?, ?, ?)'));
                $params       = [];
                foreach ($newKeys as $key) {
                    array_push($params, (int) $userId, $key, $grantedBy, $now);
                }
                $db->execute(
                    "INSERT INTO admin_user_permissions (user_id, permission_key, granted_by, created_at)
                     VALUES {$placeholders}",
                    $params
                );
            }

            $db->commit();

            Logger::activity(Auth::id(), 'admin_access_updated', [
                'target_user_id'  => (int) $userId,
                'old_permissions' => implode(',', $oldKeys),
                'new_permissions' => implode(',', $newKeys),
            ]);

            $this->flash('success', 'Permissions updated for ' . $user['name'] . '.');
        } catch (\Exception $e) {
            try { $db->rollback(); } catch (\Throwable $_) {}
            Logger::error('AdminUserAccessController::save — ' . $e->getMessage());
            $this->flash('error', 'Failed to save permissions: ' . $e->getMessage());
        }

        $this->redirect('/admin/admin-access/' . $userId . '/edit');
    }
}
