<?php
/**
 * ConvertX Settings Controller
 *
 * @package MMB\Projects\ConvertX\Controllers
 */

namespace Projects\ConvertX\Controllers;

use Core\Auth;
use Core\Security;
use Core\Database;

class SettingsController
{
    public function index(): void
    {
        $userId = Auth::id();
        $apiKey = $userId ? $this->getApiKey($userId) : null;

        $this->render('settings', [
            'title'  => 'ConvertX Settings',
            'user'   => Auth::user(),
            'apiKey' => $apiKey,
        ]);
    }

    public function update(): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->jsonError('Invalid request token', 403);
            return;
        }

        $userId = Auth::id();
        if (!$userId) {
            $this->jsonError('Authentication required', 401);
            return;
        }

        $action = $_POST['action'] ?? '';

        if ($action === 'generate_api_key') {
            $key = $this->generateApiKey($userId);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'api_key' => $key]);
            return;
        }

        if ($action === 'revoke_api_key') {
            $this->revokeApiKey($userId);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'API key revoked']);
            return;
        }

        $this->jsonError('Unknown action', 400);
    }

    // ------------------------------------------------------------------ //

    private function getApiKey(int $userId): ?string
    {
        try {
            $db  = Database::getInstance();
            $row = $db->fetch(
                "SELECT api_key FROM api_keys WHERE user_id = :uid AND is_active = 1 LIMIT 1",
                ['uid' => $userId]
            );
            return $row['api_key'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function generateApiKey(int $userId): string
    {
        $key = 'cx_' . bin2hex(random_bytes(20));
        try {
            $db = Database::getInstance();
            // Deactivate old keys
            $db->query(
                "UPDATE api_keys SET is_active = 0 WHERE user_id = :uid",
                ['uid' => $userId]
            );
            $db->query(
                "INSERT INTO api_keys (user_id, api_key, is_active, created_at)
                 VALUES (:uid, :key, 1, NOW())",
                ['uid' => $userId, 'key' => $key]
            );
        } catch (\Exception $e) {
            // Silently continue — return the key so the UI can show it
        }
        return $key;
    }

    private function revokeApiKey(int $userId): void
    {
        try {
            $db = Database::getInstance();
            $db->query(
                "UPDATE api_keys SET is_active = 0 WHERE user_id = :uid",
                ['uid' => $userId]
            );
        } catch (\Exception $e) {
            // Silently continue
        }
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require PROJECT_PATH . '/views/layout.php';
    }

    private function jsonError(string $message, int $code = 400): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $message]);
    }
}
