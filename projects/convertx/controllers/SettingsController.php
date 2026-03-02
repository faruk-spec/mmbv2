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
            $newKey = $this->generateApiKey($userId);
            if ($newKey) {
                $_SESSION['_flash']['success'] = 'New API key generated successfully.';
                $_SESSION['_new_api_key'] = $newKey;
            } else {
                $_SESSION['_flash']['error'] = 'Failed to generate API key. Please try again or contact support.';
            }
            header('Location: /projects/convertx/apikeys?tab=apikey');
            exit;
        }

        if ($action === 'revoke_api_key') {
            $this->revokeApiKey($userId);
            $_SESSION['_flash']['success'] = 'API key revoked.';
            header('Location: /projects/convertx/apikeys?tab=apikey');
            exit;
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
                "SELECT api_key FROM convertx_api_keys WHERE user_id = :uid AND is_active = 1 LIMIT 1",
                ['uid' => $userId]
            );
            return $row['api_key'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function generateApiKey(int $userId): ?string
    {
        $key = 'cx_' . bin2hex(random_bytes(20));
        $db  = Database::getInstance();

        // Ensure the ConvertX-specific API keys table exists.
        // Uses a separate table name (convertx_api_keys) to avoid colliding with
        // the platform's api_keys table (which has extra NOT NULL columns).
        try {
            $db->query(
                "CREATE TABLE IF NOT EXISTS convertx_api_keys (
                    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    user_id    INT UNSIGNED NOT NULL,
                    api_key    VARCHAR(100) NOT NULL,
                    is_active  TINYINT(1)   NOT NULL DEFAULT 1,
                    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_user (user_id),
                    UNIQUE KEY uniq_key (api_key)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
            );
        } catch (\Exception $e) {
            // CREATE failed (permission denied or similar); the table may have
            // been created by the migration — proceeding to INSERT attempt.
            \Core\Logger::error('ConvertX: convertx_api_keys CREATE TABLE failed (INSERT will be attempted anyway): ' . $e->getMessage());
        }

        try {
            $db->query(
                "UPDATE convertx_api_keys SET is_active = 0 WHERE user_id = :uid",
                ['uid' => $userId]
            );
            $db->query(
                "INSERT INTO convertx_api_keys (user_id, api_key, is_active, created_at)
                 VALUES (:uid, :key, 1, NOW())",
                ['uid' => $userId, 'key' => $key]
            );
            return $key;
        } catch (\Exception $e) {
            \Core\Logger::error('ConvertX: generateApiKey failed: ' . $e->getMessage());
            return null;
        }
    }

    private function revokeApiKey(int $userId): void
    {
        try {
            $db = Database::getInstance();
            $db->query(
                "UPDATE convertx_api_keys SET is_active = 0 WHERE user_id = :uid",
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
            // Auto-create settings table if not present
            $db->query(
                "CREATE TABLE IF NOT EXISTS convertx_user_settings (
                    user_id            INT         NOT NULL PRIMARY KEY,
                    default_quality    INT         NOT NULL DEFAULT 85,
                    default_dpi        INT         NOT NULL DEFAULT 150,
                    notify_on_complete TINYINT(1)  NOT NULL DEFAULT 0,
                    updated_at         DATETIME             DEFAULT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
            );
            // Use VALUES(col) so each named param appears only once – PDO does not
            // allow the same named placeholder to appear twice in a statement.
            $db->query(
                "INSERT INTO convertx_user_settings
                     (user_id, default_quality, default_dpi, notify_on_complete)
                 VALUES (:uid, :q, :d, :n)
                 ON DUPLICATE KEY UPDATE
                     default_quality    = VALUES(default_quality),
                     default_dpi        = VALUES(default_dpi),
                     notify_on_complete = VALUES(notify_on_complete)",
                ['uid' => $userId, 'q' => $quality, 'd' => $dpi, 'n' => $notify]
            );
        } catch (\Exception $e) {
            // Silently continue – table may not exist yet; schema.sql must be run
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
