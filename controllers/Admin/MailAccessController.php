<?php
/**
 * Admin Mail Access Controller
 *
 * Manages which users can access /mail and which providers they are assigned to.
 * Builds on the existing admin_user_permissions (permission_key = 'mail') and adds
 * mail_user_providers to link users to specific SMTP/IMAP accounts.
 *
 * Routes:
 *   GET  /admin/mail/access              — list users with mail access
 *   GET  /admin/mail/access/{id}/edit    — edit a user's mail access
 *   POST /admin/mail/access/{id}/save    — save user mail access + provider assignments
 *   POST /admin/mail/access/{id}/revoke  — fully revoke mail access
 *
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;
use Core\Security;
use Core\Logger;

class MailAccessController extends BaseController
{
    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
    }

    // ------------------------------------------------------------------
    // List all users + their mail access status
    // ------------------------------------------------------------------

    public function index(): void
    {
        $db = Database::getInstance();

        $users = $db->fetchAll(
            "SELECT u.id, u.name, u.email, u.role, u.status,
                    (SELECT COUNT(*) FROM admin_user_permissions p
                     WHERE p.user_id = u.id AND p.permission_key = 'mail') AS has_mail_perm,
                    (SELECT GROUP_CONCAT(mpc.from_email ORDER BY mpc.id SEPARATOR ', ')
                     FROM mail_user_providers mup
                     JOIN mail_provider_configs mpc ON mpc.id = mup.provider_config_id
                     WHERE mup.user_id = u.id) AS assigned_providers
             FROM users u
             ORDER BY has_mail_perm DESC, u.name ASC"
        );

        $providers = $db->fetchAll(
            "SELECT id, name, from_name, from_email, provider_type, is_active FROM mail_provider_configs ORDER BY is_active DESC, id ASC"
        );

        $this->view('admin/mail/access', [
            'title'     => 'Mail User Access',
            'users'     => $users,
            'providers' => $providers,
        ]);
    }

    // ------------------------------------------------------------------
    // Edit form for a single user
    // ------------------------------------------------------------------

    public function editForm(string $userId): void
    {
        $db   = Database::getInstance();
        $user = $db->fetch("SELECT id, name, email, role FROM users WHERE id = ?", [(int)$userId]);

        if (!$user) {
            $this->flash('error', 'User not found.');
            $this->redirect('/admin/mail/access');
            return;
        }

        $hasMailPerm = (bool)$db->fetch(
            "SELECT id FROM admin_user_permissions WHERE user_id = ? AND permission_key = 'mail'",
            [(int)$userId]
        );

        $assignedProviderIds = array_column(
            $db->fetchAll(
                "SELECT provider_config_id FROM mail_user_providers WHERE user_id = ?",
                [(int)$userId]
            ),
            'provider_config_id'
        );

        $providers = $db->fetchAll(
            "SELECT id, name, from_name, from_email, provider_type, is_active FROM mail_provider_configs ORDER BY is_active DESC, id ASC"
        );

        $this->view('admin/mail/access-edit', [
            'title'               => 'Mail Access — ' . $user['name'],
            'targetUser'          => $user,
            'hasMailPerm'         => $hasMailPerm,
            'assignedProviderIds' => $assignedProviderIds,
            'providers'           => $providers,
        ]);
    }

    // ------------------------------------------------------------------
    // Save a user's mail access + provider assignments
    // ------------------------------------------------------------------

    public function save(string $userId): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request token.');
            $this->redirect('/admin/mail/access/' . $userId . '/edit');
            return;
        }

        $db   = Database::getInstance();
        $user = $db->fetch("SELECT id, name FROM users WHERE id = ?", [(int)$userId]);

        if (!$user) {
            $this->flash('error', 'User not found.');
            $this->redirect('/admin/mail/access');
            return;
        }

        $uid          = (int)$user['id'];
        $grantAccess  = (bool)($_POST['grant_mail'] ?? false);
        $providerIds  = array_map('intval', (array)($_POST['provider_ids'] ?? []));

        // 1. Grant or revoke the 'mail' permission
        $hasPerm = (bool)$db->fetch(
            "SELECT id FROM admin_user_permissions WHERE user_id = ? AND permission_key = 'mail'",
            [$uid]
        );

        if ($grantAccess && !$hasPerm) {
            $db->insert('admin_user_permissions', [
                'user_id'        => $uid,
                'permission_key' => 'mail',
                'granted_by'     => Auth::id(),
            ]);
        } elseif (!$grantAccess && $hasPerm) {
            $db->delete('admin_user_permissions', 'user_id = ? AND permission_key = ?', [$uid, 'mail']);
        }

        // 2. Sync provider assignments — delete all and re-insert selected
        $db->delete('mail_user_providers', 'user_id = ?', [$uid]);

        // Get valid provider IDs from DB to prevent injection
        $allProviderIds = array_column(
            $db->fetchAll("SELECT id FROM mail_provider_configs") ?: [],
            'id'
        );

        foreach ($providerIds as $pid) {
            if (in_array($pid, $allProviderIds, true)) {
                try {
                    $db->insert('mail_user_providers', [
                        'user_id'            => $uid,
                        'provider_config_id' => $pid,
                        'granted_by'         => Auth::id(),
                    ]);
                } catch (\Exception $e) {
                    // duplicate — skip
                }
            }
        }

        Logger::activity(Auth::id(), 'mail_access_updated', [
            'target_user_id' => $uid,
            'grant_access'   => $grantAccess,
            'provider_count' => count($providerIds),
        ]);

        $this->flash('success', 'Mail access updated for ' . $user['name'] . '.');
        $this->redirect('/admin/mail/access');
    }

    // ------------------------------------------------------------------
    // Quick revoke all mail access from a user
    // ------------------------------------------------------------------

    public function revoke(string $userId): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request token.');
            $this->redirect('/admin/mail/access');
            return;
        }

        $db  = Database::getInstance();
        $uid = (int)$userId;

        $db->delete('admin_user_permissions', 'user_id = ? AND permission_key = ?', [$uid, 'mail']);
        $db->delete('mail_user_providers', 'user_id = ?', [$uid]);

        $this->flash('success', 'Mail access revoked.');
        $this->redirect('/admin/mail/access');
    }
}
