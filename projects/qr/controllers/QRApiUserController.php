<?php
/**
 * QR API User Controller
 *
 * Lets users manage their own QR-scoped API keys and read the QR API docs.
 *
 * Routes (handled in projects/qr/routes/web.php):
 *   GET  /projects/qr/api          → index()    – list keys + docs
 *   POST /projects/qr/api          → generate() – create new key
 *   POST /projects/qr/api/revoke   → revoke()   – revoke own key
 *
 * @package MMB\Projects\QR\Controllers
 */

namespace Projects\QR\Controllers;

use Core\Auth;
use Core\Database;
use Core\Logger;
use Core\Security;
use Core\API\ApiAuth;

class QRApiUserController
{
    private Database $db;
    private int $userId;

    public function __construct()
    {
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }
        $this->db     = Database::getInstance();
        $this->userId = Auth::id();
        $this->ensureApiKeysTable();
    }

    // -------------------------------------------------------------------------

    /** GET /projects/qr/api */
    public function index(): void
    {
        $keys = ApiAuth::getUserKeys($this->userId);

        // Collect docs info
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
            . '://' . ($_SERVER['HTTP_HOST'] ?? 'yourdomain.com');

        $newKey = $_SESSION['qr_api_new_key'] ?? null;
        unset($_SESSION['qr_api_new_key']);

        require PROJECT_PATH . '/views/api.php';
    }

    /** POST /projects/qr/api – generate a new API key */
    public function generate(): void
    {
        if (!Security::validateCsrfToken($_POST['_csrf_token'] ?? '')) {
            $this->flash('error', 'Invalid request token.');
            header('Location: /projects/qr/api');
            exit;
        }

        $name = trim(Security::sanitize($_POST['name'] ?? ''));
        if (empty($name)) {
            $this->flash('error', 'Key name is required.');
            header('Location: /projects/qr/api');
            exit;
        }
        if (strlen($name) > 80) {
            $name = substr($name, 0, 80);
        }

        // Count existing active keys — limit to 10 per user
        $count = (int) ($this->db->fetch(
            "SELECT COUNT(*) AS cnt FROM api_keys WHERE user_id = ? AND is_active = 1",
            [$this->userId]
        )['cnt'] ?? 0);

        if ($count >= 10) {
            $this->flash('error', 'You have reached the maximum of 10 active API keys. Please revoke an existing key first.');
            header('Location: /projects/qr/api');
            exit;
        }

        try {
            $keyData = ApiAuth::generateKey(
                $this->userId,
                $name,
                ['qr:read', 'qr:write', 'qr:delete']
            );

            Logger::activity($this->userId, 'qr_api_key_generated', ['key_name' => $name]);

            // Store once so the view can show it in full (only shown once)
            $_SESSION['qr_api_new_key'] = $keyData['api_key'];

            $this->flash('success', 'API key "' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" created successfully. Copy it now — it will not be shown again.');
        } catch (\Exception $e) {
            Logger::error('QR API key generation error: ' . $e->getMessage());
            $this->flash('error', 'Failed to generate API key. Please try again.');
        }

        header('Location: /projects/qr/api');
        exit;
    }

    /** POST /projects/qr/api/revoke – revoke own key */
    public function revoke(): void
    {
        if (!Security::validateCsrfToken($_POST['_csrf_token'] ?? '')) {
            $this->jsonError('Invalid request token.');
            return;
        }

        $keyId = (int) ($_POST['key_id'] ?? 0);
        if (!$keyId) {
            $this->jsonError('Key ID is required.');
            return;
        }

        // Verify ownership
        $key = $this->db->fetch(
            "SELECT id, name FROM api_keys WHERE id = ? AND user_id = ? LIMIT 1",
            [$keyId, $this->userId]
        );

        if (!$key) {
            $this->jsonError('Key not found.', 404);
            return;
        }

        ApiAuth::revokeKey($keyId);
        Logger::activity($this->userId, 'qr_api_key_revoked', ['key_id' => $keyId, 'key_name' => $key['name']]);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'API key revoked.']);
        exit;
    }

    // -------------------------------------------------------------------------

    private function ensureApiKeysTable(): void
    {
        try {
            $this->db->query(
                "CREATE TABLE IF NOT EXISTS api_keys (
                    id            INT AUTO_INCREMENT PRIMARY KEY,
                    user_id       INT NOT NULL,
                    name          VARCHAR(100) NOT NULL,
                    api_key       VARCHAR(100) NOT NULL UNIQUE,
                    permissions   JSON,
                    is_active     TINYINT(1) NOT NULL DEFAULT 1,
                    created_at    DATETIME NOT NULL,
                    last_used_at  DATETIME,
                    expires_at    DATETIME,
                    request_count INT NOT NULL DEFAULT 0,
                    revoked_at    DATETIME,
                    INDEX idx_user (user_id),
                    INDEX idx_key  (api_key)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
            );
        } catch (\Exception $e) {
            // Table already exists or DB error — continue
        }
    }

    private function flash(string $type, string $msg): void
    {
        $_SESSION['flash_' . $type] = $msg;
    }

    private function jsonError(string $msg, int $code = 400): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $msg]);
        exit;
    }
}
