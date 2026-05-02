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
use Core\ActivityLogger;
use Core\Security;
use Core\API\ApiAuth;
use Projects\QR\Services\QRFeatureService;

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

        $featureService = new QRFeatureService();
        $canApiAccess   = $featureService->can($this->userId, 'api_access');

        // Usage analytics: total requests across all user keys
        $totalRequests = array_sum(array_column($keys, 'request_count'));
        $activeKeys    = count(array_filter($keys, fn($k) => $k['is_active']));
        $lastUsedAt    = null;
        foreach ($keys as $k) {
            if ($k['last_used_at'] && (!$lastUsedAt || $k['last_used_at'] > $lastUsedAt)) {
                $lastUsedAt = $k['last_used_at'];
            }
        }

        // Daily QR codes generated via API for last 14 days (from qr_codes table source = 'api')
        $dailyApiUsage = [];
        try {
            $rows = $this->db->fetchAll(
                "SELECT DATE(created_at) AS day, COUNT(*) AS cnt
                   FROM qr_codes
                  WHERE user_id = :uid
                    AND source = 'api'
                    AND created_at >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
                  GROUP BY DATE(created_at)
                  ORDER BY day ASC",
                ['uid' => $this->userId]
            );
            foreach ($rows as $r) {
                $dailyApiUsage[$r['day']] = (int) $r['cnt'];
            }
        } catch (\Exception $e) {
            // qr_codes.source column may not exist yet — continue without chart data
        }

        $title   = 'API & Analytics';

        // Request logs for this user (last 100)
        $apiLogs = [];
        try {
            $apiLogs = $this->db->fetchAll(
                "SELECT id, api_key_prefix, endpoint, method, ip_address,
                        user_agent, status_code, response_time, action, created_at
                   FROM qr_api_request_logs
                  WHERE user_id = :uid
                  ORDER BY id DESC
                  LIMIT 100",
                ['uid' => $this->userId]
            ) ?: [];
        } catch (\Exception $e) {
            // Table may not exist yet
        }

        // Per-key endpoint breakdown (from request logs)
        $keyLogStats = [];
        try {
            $rows = $this->db->fetchAll(
                "SELECT api_key_prefix,
                        COUNT(*)                                                AS total,
                        SUM(CASE WHEN status_code >= 200 AND status_code < 300 THEN 1 ELSE 0 END) AS success,
                        SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END)    AS errors,
                        ROUND(AVG(response_time), 0)                           AS avg_ms,
                        MAX(created_at)                                        AS last_at
                   FROM qr_api_request_logs
                  WHERE user_id = :uid
                  GROUP BY api_key_prefix
                  ORDER BY total DESC",
                ['uid' => $this->userId]
            ) ?: [];
            foreach ($rows as $r) {
                $keyLogStats[$r['api_key_prefix']] = $r;
            }
        } catch (\Exception $e) {
            // Table may not exist yet
        }

        $content_vars = [
            'keys'          => $keys,
            'baseUrl'       => $baseUrl,
            'newKey'        => $newKey,
            'title'         => $title,
            'canApiAccess'  => $canApiAccess,
            'totalRequests' => $totalRequests,
            'activeKeys'    => $activeKeys,
            'lastUsedAt'    => $lastUsedAt,
            'dailyApiUsage' => $dailyApiUsage,
            'apiLogs'       => $apiLogs,
            'keyLogStats'   => $keyLogStats,
        ];

        // Buffer the view content then wrap in layout.php (navbar + sidebar).
        ob_start();
        extract($content_vars);
        require PROJECT_PATH . '/views/api.php';
        $content = ob_get_clean();

        require PROJECT_PATH . '/views/layout.php';
    }

    /** POST /projects/qr/api – generate a new API key */
    public function generate(): void
    {
        if (!Security::validateCsrfToken($_POST['_csrf_token'] ?? '')) {
            $this->flash('error', 'Invalid request token.');
            header('Location: /projects/qr/api');
            exit;
        }

        // Enforce api_access feature flag
        try {
            $featureService = new QRFeatureService();
            if (!$featureService->can($this->userId, 'api_access')) {
                $this->flash('error', 'API access is not available on your current plan. Please upgrade to generate API keys.');
                header('Location: /projects/qr/api');
                exit;
            }
        } catch (\Exception $e) {
            Logger::error('QRApiUserController feature check error: ' . $e->getMessage());
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
            try { ActivityLogger::logCreate($this->userId, 'qr', 'api_key', $keyData['id'] ?? 0, ['key_name' => $name]); } catch (\Throwable $_) {}

            // Store once so the view can show it in full (only shown once)
            $_SESSION['qr_api_new_key'] = $keyData['api_key'];

            $this->flash('success', 'API key "' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" created successfully. Copy it now — it will not be shown again.');
        } catch (\Exception $e) {
            Logger::error('QR API key generation error: ' . $e->getMessage());
            try { ActivityLogger::logFailure($this->userId, 'api_key_create', $e->getMessage()); } catch (\Throwable $_) {}
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
        try { ActivityLogger::logDelete($this->userId, 'qr', 'api_key', $keyId, ['key_name' => $key['name']]); } catch (\Throwable $_) {}

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
