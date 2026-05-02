<?php
/**
 * WhatsApp API Keys & Analytics Controller
 *
 * Manages user-facing API key management and usage analytics for WhatsApp.
 *
 * Routes:
 *   GET  /projects/whatsapp/api            → index()    – show keys + analytics
 *   POST /projects/whatsapp/api/generate   → generate() – create/rotate key
 *   POST /projects/whatsapp/api/revoke     → revoke()   – deactivate all keys
 *
 * @package MMB\Projects\WhatsApp\Controllers
 */

namespace Projects\WhatsApp\Controllers;

use Core\Auth;
use Core\Database;
use Core\Security;
use Core\ActivityLogger;

class ApiKeysController
{
    private Database $db;
    private array    $user;
    private int      $userId;

    public function __construct()
    {
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }
        $this->db     = Database::getInstance();
        $this->user   = Auth::user();
        $this->userId = (int) $this->user['id'];
        $this->ensureTables();
    }

    // ── Routes ───────────────────────────────────────────────────────────────

    /** GET /projects/whatsapp/api */
    public function index(): void
    {
        $keys       = $this->getUserKeys();
        $activeKey  = null;
        foreach ($keys as $k) {
            if ($k['status'] === 'active') { $activeKey = $k; break; }
        }

        $newKey = $_SESSION['wa_api_new_key'] ?? null;
        unset($_SESSION['wa_api_new_key']);

        // Usage analytics
        $totalRequests = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM whatsapp_api_logs WHERE user_id = ?",
            [$this->userId]
        ) ?? 0;

        $requestsToday = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM whatsapp_api_logs WHERE user_id = ? AND DATE(created_at) = CURDATE()",
            [$this->userId]
        ) ?? 0;

        $lastRequest = $this->db->fetch(
            "SELECT created_at FROM whatsapp_api_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 1",
            [$this->userId]
        );
        $lastRequestAt = $lastRequest['created_at'] ?? null;

        // Daily usage — last 14 days
        $dailyRows = $this->db->fetchAll(
            "SELECT DATE(created_at) AS day, COUNT(*) AS cnt
               FROM whatsapp_api_logs
              WHERE user_id = ?
                AND created_at >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
              GROUP BY DATE(created_at)
              ORDER BY day ASC",
            [$this->userId]
        );
        $dailyUsage = [];
        foreach ($dailyRows as $r) {
            $dailyUsage[$r['day']] = (int) $r['cnt'];
        }

        // Endpoint breakdown
        $endpointStats = $this->db->fetchAll(
            "SELECT endpoint, COUNT(*) AS cnt
               FROM whatsapp_api_logs
              WHERE user_id = ?
              GROUP BY endpoint
              ORDER BY cnt DESC",
            [$this->userId]
        );

        // Recent activity (last 20 requests)
        $recentLogs = $this->db->fetchAll(
            "SELECT endpoint, method, ip_address, created_at
               FROM whatsapp_api_logs
              WHERE user_id = ?
              ORDER BY created_at DESC
              LIMIT 20",
            [$this->userId]
        );

        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
            . '://' . ($_SERVER['HTTP_HOST'] ?? 'yourdomain.com');

        $apiEndpoints = $this->getApiEndpoints();

        $data = [
            'user'          => $this->user,
            'keys'          => $keys,
            'activeKey'     => $activeKey,
            'newKey'        => $newKey,
            'totalRequests' => (int) $totalRequests,
            'requestsToday' => (int) $requestsToday,
            'lastRequestAt' => $lastRequestAt,
            'dailyUsage'    => $dailyUsage,
            'endpointStats' => $endpointStats,
            'recentLogs'    => $recentLogs,
            'baseUrl'       => $baseUrl,
            'apiEndpoints'  => $apiEndpoints,
            'pageTitle'     => 'WhatsApp API & Analytics',
        ];

        \Core\View::render('whatsapp/api', $data);
    }

    /** POST /projects/whatsapp/api/generate */
    public function generate(): void
    {
        header('Content-Type: application/json');

        if (!Security::validateCsrfToken($_POST['_csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid CSRF token.']);
            return;
        }

        try {
            // Deactivate any existing key
            $this->db->query(
                "UPDATE whatsapp_api_keys SET status = 'inactive' WHERE user_id = ?",
                [$this->userId]
            );

            $apiKey = 'whapi_' . bin2hex(random_bytes(32));

            $this->db->query(
                "INSERT INTO whatsapp_api_keys (user_id, api_key, status, created_at)
                 VALUES (?, ?, 'active', NOW())",
                [$this->userId, $apiKey]
            );

            $_SESSION['wa_api_new_key'] = $apiKey;

            try {
                ActivityLogger::log($this->userId, 'whatsapp_api_key_generated');
            } catch (\Throwable $_) {}

            echo json_encode(['success' => true, 'api_key' => $apiKey]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to generate key.']);
        }
    }

    /** POST /projects/whatsapp/api/revoke */
    public function revoke(): void
    {
        header('Content-Type: application/json');

        if (!Security::validateCsrfToken($_POST['_csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid CSRF token.']);
            return;
        }

        try {
            $this->db->query(
                "UPDATE whatsapp_api_keys SET status = 'inactive' WHERE user_id = ?",
                [$this->userId]
            );

            try {
                ActivityLogger::log($this->userId, 'whatsapp_api_key_revoked');
            } catch (\Throwable $_) {}

            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to revoke key.']);
        }
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function getUserKeys(): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT * FROM whatsapp_api_keys WHERE user_id = ? ORDER BY created_at DESC",
                [$this->userId]
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    private function ensureTables(): void
    {
        try {
            $this->db->query(
                "CREATE TABLE IF NOT EXISTS whatsapp_api_keys (
                    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    user_id    INT UNSIGNED NOT NULL,
                    api_key    VARCHAR(100) NOT NULL,
                    status     ENUM('active','inactive') NOT NULL DEFAULT 'active',
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_user (user_id),
                    INDEX idx_key  (api_key)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
            );
            $this->db->query(
                "CREATE TABLE IF NOT EXISTS whatsapp_api_logs (
                    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    user_id    INT UNSIGNED NOT NULL,
                    endpoint   VARCHAR(200) NOT NULL,
                    method     VARCHAR(10)  NOT NULL DEFAULT 'POST',
                    ip_address VARCHAR(45)  NOT NULL DEFAULT '',
                    user_agent TEXT,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_user (user_id),
                    INDEX idx_created (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
            );
        } catch (\Exception $e) {
            // Tables may already exist — safe to ignore
        }
    }

    private function getApiEndpoints(): array
    {
        return [
            ['method' => 'POST', 'path' => '/projects/whatsapp/api/send-message', 'desc' => 'Send a text message'],
            ['method' => 'POST', 'path' => '/projects/whatsapp/api/send-media',   'desc' => 'Send media (image, video, document)'],
            ['method' => 'GET',  'path' => '/projects/whatsapp/api/messages',      'desc' => 'Retrieve message history'],
            ['method' => 'GET',  'path' => '/projects/whatsapp/api/contacts',      'desc' => 'List contacts for a session'],
            ['method' => 'GET',  'path' => '/projects/whatsapp/api/status',        'desc' => 'Get session connection status'],
        ];
    }
}
