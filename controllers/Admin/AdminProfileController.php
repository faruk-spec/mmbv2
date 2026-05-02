<?php
/**
 * Admin Profile Controller
 *
 * Handles the /admin/profile page where admin users can view and update
 * their own profile information and change their password.
 *
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;
use Core\Security;
use Core\Logger;
use Core\Helpers;

class AdminProfileController extends BaseController
{
    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
    }

    /**
     * GET /admin/profile
     */
    public function index(): void
    {
        $db      = Database::getInstance();
        $userId  = Auth::id();
        $user    = Auth::user();

        $profile = $db->fetch(
            "SELECT * FROM user_profiles WHERE user_id = ? LIMIT 1",
            [$userId]
        );

        if (!$profile) {
            // Create empty profile row if missing
            $db->insert('user_profiles', ['user_id' => $userId]);
            $profile = $db->fetch("SELECT * FROM user_profiles WHERE user_id = ? LIMIT 1", [$userId]);
        }

        $this->view('admin/profile', [
            'title'   => 'My Profile',
            'user'    => $user,
            'profile' => $profile ?? [],
        ]);
    }

    /**
     * POST /admin/profile/update – update name, bio, phone, avatar
     */
    public function update(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/profile');
            return;
        }

        $db     = Database::getInstance();
        $userId = Auth::id();

        $name = Security::sanitize($this->input('name', ''));
        if (strlen($name) < 2) {
            $this->flash('error', 'Name must be at least 2 characters.');
            $this->redirect('/admin/profile');
            return;
        }

        $db->update('users', [
            'name'       => $name,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$userId]);

        $profileData = [
            'bio'        => Security::sanitize($this->input('bio', '')),
            'phone'      => Security::sanitize($this->input('phone', '')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Handle avatar upload
        $avatarUploadError = null;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
                $avatarUploadError = 'Photo could not be uploaded. Please check the file size and try again.';
            } else {
                $avatar = Helpers::uploadFile(
                    $_FILES['avatar'],
                    BASE_PATH . '/storage/uploads/avatars',
                    ['jpg', 'jpeg', 'png', 'gif', 'webp']
                );
                if ($avatar) {
                    $profileData['avatar'] = $avatar;
                } else {
                    $avatarUploadError = 'Photo could not be saved. Please use a JPG, PNG, GIF or WebP image.';
                }
            }
        }

        $existing = $db->fetch("SELECT id FROM user_profiles WHERE user_id = ?", [$userId]);
        if ($existing) {
            $db->update('user_profiles', $profileData, 'user_id = ?', [$userId]);
        } else {
            $db->insert('user_profiles', array_merge($profileData, ['user_id' => $userId]));
        }

        Logger::activity($userId, 'admin_profile_updated');
        if ($avatarUploadError) {
            $this->flash('warning', 'Profile saved, but: ' . $avatarUploadError);
        } else {
            $this->flash('success', 'Profile updated successfully.');
        }
        $this->redirect('/admin/profile');
    }

    /**
     * POST /admin/profile/change-password
     */
    public function changePassword(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/profile');
            return;
        }

        $db     = Database::getInstance();
        $userId = Auth::id();
        $user   = $db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);

        if (!$user) {
            $this->flash('error', 'User not found.');
            $this->redirect('/admin/profile');
            return;
        }

        $currentPw  = $this->input('current_password', '');
        $newPw      = $this->input('new_password', '');
        $confirmPw  = $this->input('confirm_password', '');

        if (!password_verify($currentPw, $user['password'] ?? '')) {
            $this->flash('error', 'Current password is incorrect.');
            $this->redirect('/admin/profile');
            return;
        }

        if (strlen($newPw) < 8) {
            $this->flash('error', 'New password must be at least 8 characters.');
            $this->redirect('/admin/profile');
            return;
        }

        if ($newPw !== $confirmPw) {
            $this->flash('error', 'New passwords do not match.');
            $this->redirect('/admin/profile');
            return;
        }

        $db->update('users', [
            'password'   => password_hash($newPw, PASSWORD_DEFAULT),
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$userId]);

        Logger::activity($userId, 'admin_password_changed');
        $this->flash('success', 'Password changed successfully.');
        $this->redirect('/admin/profile');
    }
}
