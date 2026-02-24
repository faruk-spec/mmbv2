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
use Projects\ConvertX\Models\ConversionJobModel;

class SettingsController
{
    // ------------------------------------------------------------------ //
    //  General Settings page                                               //
    // ------------------------------------------------------------------ //

    public function index(): void
    {
        $userId   = Auth::id();
        $prefs    = $userId ? $this->getUserPrefs($userId) : [];

        $this->render('settings', [
            'title' => 'Settings',
            'user'  => Auth::user(),
            'prefs' => $prefs,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  API Keys & Analytics page                                           //
    // ------------------------------------------------------------------ //

    public function apikeys(): void
    {
        $userId   = Auth::id();
        $apiKey   = $userId ? $this->getApiKey($userId) : null;
        $jobModel = new ConversionJobModel();
        $usage    = $userId ? $jobModel->getMonthlyUsage($userId) : [];
        $formats  = $userId ? $jobModel->getFormatBreakdown($userId) : [];
        $activity = $userId ? $jobModel->getDailyActivity($userId, 14) : [];

        $this->render('apikeys', [
            'title'    => 'API Keys & Analytics',
            'user'     => Auth::user(),
            'apiKey'   => $apiKey,
            'usage'    => $usage,
            'formats'  => $formats,
            'activity' => $activity,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  POST handler (shared by both pages)                                 //
    // ------------------------------------------------------------------ //

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

        if ($action === 'save_settings') {
            $quality  = max(10, min(100, (int) ($_POST['default_quality'] ?? 85)));
            $dpi      = in_array((int) ($_POST['default_dpi'] ?? 150), [72, 96, 150, 300, 600], true)
                            ? (int) $_POST['default_dpi'] : 150;
            $notify   = !empty($_POST['notify_on_complete']) ? 1 : 0;

            $this->saveUserPrefs($userId, $quality, $dpi, $notify);
            $_SESSION['_flash']['success'] = 'Settings saved successfully.';
            header('Location: /projects/convertx/settings');
            exit;
        }

        $this->jsonError('Unknown action', 400);
    }

    // ------------------------------------------------------------------ //
    //  Private helpers                                                     //
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

    private function getUserPrefs(int $userId): array
    {
        try {
            $db  = Database::getInstance();
            $row = $db->fetch(
                "SELECT * FROM convertx_user_settings WHERE user_id = :uid LIMIT 1",
                ['uid' => $userId]
            );
            return $row ?: ['default_quality' => 85, 'default_dpi' => 150, 'notify_on_complete' => 0];
        } catch (\Exception $e) {
            return ['default_quality' => 85, 'default_dpi' => 150, 'notify_on_complete' => 0];
        }
    }

    private function saveUserPrefs(int $userId, int $quality, int $dpi, int $notify): void
    {
        try {
            $db = Database::getInstance();
            $db->query(
                "INSERT INTO convertx_user_settings
                     (user_id, default_quality, default_dpi, notify_on_complete)
                 VALUES (:uid, :q, :d, :n)
                 ON DUPLICATE KEY UPDATE
                     default_quality = :q, default_dpi = :d, notify_on_complete = :n",
                ['uid' => $userId, 'q' => $quality, 'd' => $dpi, 'n' => $notify]
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
