<?php
/**
 * User API Controller
 *
 * Endpoints:
 *   GET /api/user         – current user info
 *   GET /api/user/profile – current user profile
 *   PUT /api/user/profile – update profile
 *
 * @package MMB\Controllers\Api
 */

namespace Controllers\Api;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;
use Core\Security;

class UserController extends BaseController
{
    private function requireAuth(): void
    {
        if (!Auth::check()) {
            http_response_code(401);
            $this->json(['error' => 'Unauthorized', 'message' => 'Authentication required.']);
            exit;
        }
    }

    /**
     * GET /api/user
     */
    public function current(): void
    {
        $this->requireAuth();
        $user = Auth::user();

        $this->json([
            'success' => true,
            'user'    => [
                'id'         => $user['id'],
                'name'       => $user['name'],
                'email'      => $user['email'],
                'role'       => $user['role'],
                'created_at' => $user['created_at'] ?? null,
            ],
        ]);
    }

    /**
     * GET /api/user/profile
     */
    public function profile(): void
    {
        $this->requireAuth();
        $db     = Database::getInstance();
        $userId = Auth::id();

        $profile = $db->fetch(
            "SELECT up.bio, up.phone, up.avatar FROM user_profiles up WHERE up.user_id = ? LIMIT 1",
            [$userId]
        );

        $user = Auth::user();

        $this->json([
            'success' => true,
            'profile' => [
                'id'         => $user['id'],
                'name'       => $user['name'],
                'email'      => $user['email'],
                'bio'        => $profile['bio'] ?? null,
                'phone'      => $profile['phone'] ?? null,
                'avatar_url' => !empty($profile['avatar'])
                    ? (str_starts_with($profile['avatar'], '/') ? $profile['avatar'] : '/storage/uploads/avatars/' . $profile['avatar'])
                    : null,
            ],
        ]);
    }

    /**
     * PUT /api/user/profile
     */
    public function updateProfile(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            $this->json(['error' => 'Method Not Allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $name  = Security::sanitize($input['name'] ?? '');
        $bio   = Security::sanitize($input['bio']   ?? '');
        $phone = Security::sanitize($input['phone'] ?? '');

        if (strlen($name) < 2) {
            http_response_code(422);
            $this->json(['error' => 'Validation Failed', 'message' => 'Name must be at least 2 characters.']);
            return;
        }

        $db     = Database::getInstance();
        $userId = Auth::id();

        $db->update('users', ['name' => $name, 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$userId]);

        $existing = $db->fetch("SELECT id FROM user_profiles WHERE user_id = ?", [$userId]);
        $profileData = ['bio' => $bio, 'phone' => $phone, 'updated_at' => date('Y-m-d H:i:s')];
        if ($existing) {
            $db->update('user_profiles', $profileData, 'user_id = ?', [$userId]);
        } else {
            $db->insert('user_profiles', array_merge($profileData, ['user_id' => $userId]));
        }

        $this->json(['success' => true, 'message' => 'Profile updated.']);
    }
}
