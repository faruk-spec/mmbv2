<?php
/**
 * SSO (Single Sign-On) API Controller
 *
 * Endpoints:
 *   GET  /api/sso/validate       – validate an SSO token
 *   POST /api/sso/token          – generate an SSO token
 *   POST /api/sso/refresh        – refresh an SSO token
 *
 * @package MMB\Controllers\Api
 */

namespace Controllers\Api;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;

class SsoController extends BaseController
{
    /**
     * GET /api/sso/validate
     * Validates a bearer token (api_keys table).
     */
    public function validate(): void
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $token      = '';
        if (preg_match('/^Bearer\s+(.+)$/i', $authHeader, $m)) {
            $token = trim($m[1]);
        }
        if (!$token) {
            $token = $_GET['token'] ?? '';
        }

        if (!$token) {
            http_response_code(400);
            $this->json(['error' => 'Bad Request', 'message' => 'No token provided.']);
            return;
        }

        try {
            $db  = Database::getInstance();
            $row = $db->fetch(
                "SELECT ak.*, u.id AS user_id, u.name, u.email, u.role
                 FROM api_keys ak
                 JOIN users u ON ak.user_id = u.id
                 WHERE ak.token = ? AND ak.is_active = 1 LIMIT 1",
                [$token]
            );

            if (!$row) {
                http_response_code(401);
                $this->json(['valid' => false, 'error' => 'Invalid or inactive token.']);
                return;
            }

            $this->json([
                'valid'   => true,
                'user_id' => $row['user_id'],
                'name'    => $row['name'],
                'email'   => $row['email'],
                'role'    => $row['role'],
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            $this->json(['error' => 'Internal Server Error']);
        }
    }

    /**
     * POST /api/sso/token
     * Generate an API token for the authenticated user.
     */
    public function generateToken(): void
    {
        if (!Auth::check()) {
            http_response_code(401);
            $this->json(['error' => 'Unauthorized']);
            return;
        }

        try {
            $db     = Database::getInstance();
            $userId = Auth::id();
            $token  = bin2hex(random_bytes(32));

            $db->insert('api_keys', [
                'user_id'    => $userId,
                'token'      => $token,
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $this->json(['success' => true, 'token' => $token]);
        } catch (\Exception $e) {
            http_response_code(500);
            $this->json(['error' => 'Internal Server Error']);
        }
    }

    /**
     * POST /api/sso/refresh
     * Placeholder – real refresh logic would depend on your SSO setup.
     */
    public function refreshToken(): void
    {
        if (!Auth::check()) {
            http_response_code(401);
            $this->json(['error' => 'Unauthorized']);
            return;
        }

        $this->json(['success' => true, 'message' => 'Token refresh not required for API key auth.']);
    }
}
