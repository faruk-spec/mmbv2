<?php
/**
 * ConvertX Admin Controller
 *
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;
use Core\Auth;
use Core\Security;
use Core\Logger;
use Core\ActivityLogger;

class ConvertXAdminController extends BaseController
{
    private $db;

    public function __construct()
    {
        $this->requireAuth();
        $this->requirePermissionGroup('convertx');
        $this->db = Database::getInstance();
    }

    // ------------------------------------------------------------------ //
    //  Overview                                                            //
    // ------------------------------------------------------------------ //

    public function overview(): void
    {
        $this->requirePermission('convertx');
        $stats = $this->getStats();
        $recentJobs = $this->getRecentJobs(15);
        $aiUsage = $this->getAiUsageStats();

        $this->view('admin/projects/convertx/overview', [
            'title'       => 'ConvertX Admin — Overview',
            'stats'       => $stats,
            'recentJobs'  => $recentJobs,
            'aiUsage'     => $aiUsage,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  All Jobs                                                            //
    // ------------------------------------------------------------------ //

    public function jobs(): void
    {
        $this->requirePermission('convertx.jobs');
        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 30;
        $offset  = ($page - 1) * $perPage;
        $status  = $_GET['status'] ?? '';
        $search  = trim($_GET['search'] ?? '');

        $where  = '1=1';
        $params = [];

        if ($status && in_array($status, ['pending','processing','completed','failed','cancelled'], true)) {
            $where    .= ' AND j.status = :status';
            $params['status'] = $status;
        }
        if ($search) {
            $where    .= ' AND (j.input_filename LIKE :search OR u.email LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $total = $this->db->fetch(
            "SELECT COUNT(*) as cnt
               FROM convertx_jobs j
               LEFT JOIN users u ON u.id = j.user_id
              WHERE $where",
            $params
        )['cnt'] ?? 0;

        $jobs = $this->db->fetchAll(
            "SELECT j.*, u.name AS user_name, u.email AS user_email
               FROM convertx_jobs j
               LEFT JOIN users u ON u.id = j.user_id
              WHERE $where
              ORDER BY j.created_at DESC
              LIMIT $perPage OFFSET $offset",
            $params
        );

        $this->view('admin/projects/convertx/jobs', [
            'title'   => 'ConvertX Admin — All Jobs',
            'jobs'    => $jobs,
            'total'   => (int) $total,
            'page'    => $page,
            'perPage' => $perPage,
            'status'  => $status,
            'search'  => $search,
        ]);
    }

    public function cancelJob(): void
    {
        $this->requirePermission('convertx.jobs');
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->json(['success' => false, 'error' => 'Invalid token'], 403);
            return;
        }
        $id = (int) ($_POST['job_id'] ?? 0);
        if ($id) {
            $this->db->query(
                "UPDATE convertx_jobs SET status='cancelled' WHERE id=:id AND status IN ('pending','processing')",
                ['id' => $id]
            );
            try { ActivityLogger::logUpdate(Auth::id(), 'convertx', 'conversion_job', $id, ['status' => 'pending/processing'], ['status' => 'cancelled']); } catch (\Throwable $_) {}
        }
        $this->redirect('/admin/projects/convertx/jobs');
    }

    public function deleteJob(): void
    {
        $this->requirePermission('convertx.jobs');
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->json(['success' => false, 'error' => 'Invalid token'], 403);
            return;
        }
        $id = (int) ($_POST['job_id'] ?? 0);
        if ($id) {
            $this->db->query("DELETE FROM convertx_jobs WHERE id=:id", ['id' => $id]);
            try { ActivityLogger::logDelete(Auth::id(), 'convertx', 'conversion_job', $id); } catch (\Throwable $_) {}
        }
        $this->redirect('/admin/projects/convertx/jobs');
    }

    // ------------------------------------------------------------------ //
    //  Users                                                               //
    // ------------------------------------------------------------------ //

    public function users(): void
    {
        $this->requirePermission('convertx.users');
        $users = $this->db->fetchAll(
            "SELECT u.id, u.name, u.email,
                    COUNT(j.id) AS total_jobs,
                    SUM(CASE WHEN j.status='completed' THEN 1 ELSE 0 END) AS completed,
                    SUM(CASE WHEN j.status='failed'    THEN 1 ELSE 0 END) AS failed,
                    MAX(j.created_at) AS last_job_at,
                    k.api_key, k.is_active AS key_active
               FROM users u
               LEFT JOIN convertx_jobs j ON j.user_id = u.id
               LEFT JOIN api_keys k ON k.user_id = u.id AND k.is_active = 1
              GROUP BY u.id, u.name, u.email, k.api_key, k.is_active
              ORDER BY total_jobs DESC"
        );

        $this->view('admin/projects/convertx/users', [
            'title' => 'ConvertX Admin — Users',
            'users' => $users,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  API Keys & Usage                                                   //
    // ------------------------------------------------------------------ //

    public function apiKeys(): void
    {
        $this->requirePermission('convertx.api_keys');

        // Optional per-user filter
        $filterUserId  = isset($_GET['user_id'])     ? (int) $_GET['user_id']     : null;
        $filterKeyPfx  = isset($_GET['key_prefix'])  ? trim($_GET['key_prefix'])  : null;
        if ($filterKeyPfx && !preg_match('/^[a-zA-Z0-9_]{1,16}$/', $filterKeyPfx)) {
            $filterKeyPfx = null; // reject invalid prefix
        }

        if ($filterUserId) {
            $keys = $this->db->fetchAll(
                "SELECT k.*, u.name AS user_name, u.email AS user_email
                   FROM api_keys k
                   LEFT JOIN users u ON u.id = k.user_id
                  WHERE k.user_id = ? AND k.api_key LIKE 'cx_%'
                  ORDER BY k.created_at DESC",
                [$filterUserId]
            );
        } else {
            $keys = $this->db->fetchAll(
                "SELECT k.*, u.name AS user_name, u.email AS user_email
                   FROM api_keys k
                   LEFT JOIN users u ON u.id = k.user_id
                  WHERE k.api_key LIKE 'cx_%'
                  ORDER BY k.created_at DESC"
            );
        }
        $userUsage = $this->db->fetchAll(
            "SELECT u.id, u.name AS user_name, u.email AS user_email,
                    COUNT(k.id) AS key_count,
                    SUM(k.request_count) AS total_requests,
                    MAX(k.last_used_at) AS last_used_at
               FROM api_keys k
               LEFT JOIN users u ON u.id = k.user_id
              WHERE k.api_key LIKE 'cx_%'
              GROUP BY u.id, u.name, u.email
              ORDER BY total_requests DESC
              LIMIT 50"
        );

        // Fetch all users for the generate-key dropdown
        $users = [];
        try {
            $users = $this->db->fetchAll(
                "SELECT id, name, email FROM users ORDER BY name ASC"
            );
        } catch (\Exception $e) {
            // Non-fatal
        }

        $filterUser = null;
        if ($filterUserId) {
            $filterUser = $this->db->fetch("SELECT id, name, email FROM users WHERE id = ?", [$filterUserId]);
        }

        // Fetch recent request logs (filtered by user and/or key prefix)
        $recentLogs = [];
        try {
            $conditions = [];
            $logsParams = [];
            if ($filterUserId)  { $conditions[] = 'l.user_id = ?';        $logsParams[] = $filterUserId; }
            if ($filterKeyPfx)  { $conditions[] = 'l.api_key_prefix = ?'; $logsParams[] = $filterKeyPfx; }
            $logsWhere  = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
            $recentLogs = $this->db->fetchAll(
                "SELECT l.id, l.user_id, u.email, u.name AS user_name,
                        l.api_key_prefix, l.endpoint, l.method,
                        l.ip_address, l.status_code, l.response_time, l.action, l.created_at
                   FROM convertx_api_request_logs l
                   LEFT JOIN users u ON l.user_id = u.id
                   {$logsWhere}
                   ORDER BY l.id DESC
                   LIMIT 200",
                $logsParams
            ) ?: [];
        } catch (\Exception $e) {
            // Table may not exist yet
        }

        // Distinct key prefixes for the filter dropdown
        $keyPrefixes = [];
        try {
            $rows = $this->db->fetchAll(
                "SELECT DISTINCT api_key_prefix FROM convertx_api_request_logs ORDER BY api_key_prefix LIMIT 200"
            );
            $keyPrefixes = array_column($rows, 'api_key_prefix');
        } catch (\Exception $e) {
            // Non-fatal
        }

        $this->view('admin/projects/convertx/api-keys', [
            'title'          => 'ConvertX Admin — API Keys & Usage',
            'keys'           => $keys,
            'users'          => $users,
            'userUsage'      => $userUsage,
            'filterUserId'   => $filterUserId,
            'filterUser'     => $filterUser,
            'filterKeyPfx'   => $filterKeyPfx,
            'keyPrefixes'    => $keyPrefixes,
            'recentLogs'     => $recentLogs,
        ]);
    }

    public function revokeApiKey(): void
    {
        $this->requirePermission('convertx.api_keys');
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->json(['success' => false, 'error' => 'Invalid token'], 403);
            return;
        }
        $id = (int) ($_POST['key_id'] ?? 0);
        if ($id) {
            $this->db->query("UPDATE api_keys SET is_active=0 WHERE id=:id", ['id' => $id]);
            try { ActivityLogger::logDelete(Auth::id(), 'convertx', 'api_key', $id); } catch (\Throwable $_) {}
        }
        $this->redirect('/admin/projects/convertx/api-keys');
    }

    // ------------------------------------------------------------------ //
    //  Settings                                                            //
    // ------------------------------------------------------------------ //

    public function settings(): void
    {
        $this->requirePermission('convertx.settings');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updateSettings();
            return;
        }

        $providers = $this->db->fetchAll(
            "SELECT * FROM convertx_ai_providers ORDER BY priority ASC"
        );

        $this->view('admin/projects/convertx/settings', [
            'title'     => 'ConvertX Admin — Settings',
            'providers' => $providers,
        ]);
    }

    public function updateSettings(): void
    {
        $this->requirePermission('convertx.settings');
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request token.';
            $this->redirect('/admin/projects/convertx/settings');
            return;
        }

        $id       = (int) ($_POST['provider_id'] ?? 0);
        $isActive = !empty($_POST['is_active']) ? 1 : 0;

        if ($id) {
            $this->db->query(
                "UPDATE convertx_ai_providers SET is_active=:a WHERE id=:id",
                ['a' => $isActive, 'id' => $id]
            );
            try { ActivityLogger::logUpdate(Auth::id(), 'convertx', 'settings', $id, [], ['provider_id' => $id, 'is_active' => $isActive]); } catch (\Throwable $_) {}
        }

        $_SESSION['_flash']['success'] = 'Settings updated.';
        $this->redirect('/admin/projects/convertx/settings');
    }

    // ------------------------------------------------------------------ //
    //  SQL Schema                                                          //
    // ------------------------------------------------------------------ //

    public function schema(): void
    {
        $this->requirePermission('convertx.settings');
        $schemaFile = BASE_PATH . '/projects/convertx/schema.sql';
        $schema = file_exists($schemaFile) ? file_get_contents($schemaFile) : '-- schema.sql not found';

        $this->view('admin/projects/convertx/schema', [
            'title'  => 'ConvertX Admin — SQL Schema',
            'schema' => $schema,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  Generate API Key for User                                          //
    // ------------------------------------------------------------------ //

    public function generateApiKeyForUser(): void
    {
        $this->requirePermission('convertx.api_keys');
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->json(['success' => false, 'error' => 'Invalid token'], 403);
            return;
        }
        $userId = (int) ($_POST['user_id'] ?? 0);
        if (!$userId) {
            $_SESSION['_flash']['error'] = 'Invalid user ID.';
            $this->redirect('/admin/projects/convertx/api-keys');
            return;
        }
        // Check user exists
        $user = $this->db->fetch("SELECT id, name, email FROM users WHERE id=:id", ['id' => $userId]);
        if (!$user) {
            $_SESSION['_flash']['error'] = 'User not found.';
            $this->redirect('/admin/projects/convertx/api-keys');
            return;
        }
        $key = 'cx_' . bin2hex(random_bytes(20));
        try {
            $this->db->query(
                "CREATE TABLE IF NOT EXISTS api_keys (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    api_key VARCHAR(64) NOT NULL,
                    is_active TINYINT(1) NOT NULL DEFAULT 1,
                    created_at DATETIME NOT NULL,
                    INDEX idx_user (user_id),
                    UNIQUE KEY uniq_key (api_key)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
            );
            $this->db->query(
                "UPDATE api_keys SET is_active=0 WHERE user_id=:uid AND api_key LIKE 'cx_%'",
                ['uid' => $userId]
            );
            $this->db->query(
                "INSERT INTO api_keys (user_id, api_key, is_active, created_at) VALUES (:uid, :key, 1, NOW())",
                ['uid' => $userId, 'key' => $key]
            );
            $_SESSION['_flash']['success'] = 'API key generated for ' . $user['name'] . ': ' . $key;
            $_SESSION['_flash']['new_key'] = $key;
            $_SESSION['_flash']['new_key_user'] = $user['name'];
            try { ActivityLogger::logCreate(Auth::id(), 'convertx', 'api_key', 0, ['for_user_id' => $userId, 'key_prefix' => substr($key, 0, 8)]); } catch (\Throwable $_) {}
        } catch (\Exception $e) {
            Logger::error('ConvertX Admin generateApiKeyForUser: ' . $e->getMessage());
            try { ActivityLogger::logFailure(Auth::id(), 'generate_api_key_for_user', $e->getMessage()); } catch (\Throwable $_) {}
            $_SESSION['_flash']['error'] = 'Failed to generate API key.';
        }
        $this->redirect('/admin/projects/convertx/api-keys');
    }

    // ------------------------------------------------------------------ //
    //  AI Provider CRUD                                                    //
    // ------------------------------------------------------------------ //

    public function createProvider(): void
    {
        $this->requirePermission('convertx.settings');
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid token.';
            $this->redirect('/admin/projects/convertx/settings');
            return;
        }
        $name     = trim($_POST['name'] ?? '');
        $slug     = strtolower(trim(preg_replace('/[^a-z0-9_\-]/i', '-', $_POST['slug'] ?? '')));
        $baseUrl  = trim($_POST['base_url'] ?? '');
        $apiKey   = trim($_POST['api_key'] ?? '');
        $model    = trim($_POST['model'] ?? '');
        $priority = max(1, min(100, (int) ($_POST['priority'] ?? 10)));
        $cost     = max(0, (float) ($_POST['cost_per_1k_tokens'] ?? 0));
        $caps     = array_filter(array_map('trim', explode(',', $_POST['capabilities'] ?? '')));
        $tiers    = array_filter(array_map('trim', explode(',', $_POST['allowed_tiers'] ?? 'free,pro,enterprise')));

        if (!$name || !$slug) {
            $_SESSION['_flash']['error'] = 'Name and slug are required.';
            $this->redirect('/admin/projects/convertx/settings');
            return;
        }
        try {
            $this->db->query(
                "INSERT INTO convertx_ai_providers
                    (name, slug, base_url, api_key, model, capabilities, allowed_tiers, priority, cost_per_1k_tokens, is_active, created_at)
                 VALUES (:name, :slug, :url, :key, :model, :caps, :tiers, :pri, :cost, 1, NOW())",
                [
                    'name'  => $name,
                    'slug'  => $slug,
                    'url'   => $baseUrl ?: null,
                    'key'   => $apiKey ?: null,
                    'model' => $model ?: null,
                    'caps'  => json_encode(array_values($caps)),
                    'tiers' => json_encode(array_values($tiers)),
                    'pri'   => $priority,
                    'cost'  => $cost,
                ]
            );
            $_SESSION['_flash']['success'] = 'Provider created.';
            try { ActivityLogger::logCreate(Auth::id(), 'convertx', 'ai_provider', 0, ['name' => $name, 'slug' => $slug]); } catch (\Throwable $_) {}
        } catch (\Exception $e) {
            Logger::error('ConvertX createProvider: ' . $e->getMessage());
            try { ActivityLogger::logFailure(Auth::id(), 'create_ai_provider', $e->getMessage()); } catch (\Throwable $_) {}
            $_SESSION['_flash']['error'] = 'Failed to create provider: ' . $e->getMessage();
        }
        $this->redirect('/admin/projects/convertx/settings');
    }

    public function editProvider(): void
    {
        $this->requirePermission('convertx.settings');
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid token.';
            $this->redirect('/admin/projects/convertx/settings');
            return;
        }
        $id       = (int) ($_POST['provider_id'] ?? 0);
        $name     = trim($_POST['name'] ?? '');
        $baseUrl  = trim($_POST['base_url'] ?? '');
        $apiKey   = trim($_POST['api_key'] ?? '');
        $model    = trim($_POST['model'] ?? '');
        $priority = max(1, min(100, (int) ($_POST['priority'] ?? 10)));
        $cost     = max(0, (float) ($_POST['cost_per_1k_tokens'] ?? 0));
        $caps     = array_filter(array_map('trim', explode(',', $_POST['capabilities'] ?? '')));
        $tiers    = array_filter(array_map('trim', explode(',', $_POST['allowed_tiers'] ?? 'free,pro,enterprise')));

        if (!$id || !$name) {
            $_SESSION['_flash']['error'] = 'Invalid request.';
            $this->redirect('/admin/projects/convertx/settings');
            return;
        }
        try {
            // Only update api_key when the admin explicitly typed a new value.
            // An empty submission means "keep existing" — this prevents wiping
            // a configured key when editing other fields.
            $updateFields = "name=:name, base_url=:url, model=:model, "
                          . "capabilities=:caps, allowed_tiers=:tiers, priority=:pri, "
                          . "cost_per_1k_tokens=:cost";
            $params = [
                'name'  => $name,
                'url'   => $baseUrl ?: null,
                'model' => $model ?: null,
                'caps'  => json_encode(array_values($caps)),
                'tiers' => json_encode(array_values($tiers)),
                'pri'   => $priority,
                'cost'  => $cost,
                'id'    => $id,
            ];
            if ($apiKey !== '') {
                $updateFields .= ', api_key=:key';
                $params['key'] = $apiKey;
            }
            $this->db->query(
                "UPDATE convertx_ai_providers SET {$updateFields} WHERE id=:id",
                $params
            );
            $_SESSION['_flash']['success'] = 'Provider updated.';
            try { ActivityLogger::logUpdate(Auth::id(), 'convertx', 'ai_provider', $id, [], ['name' => $name, 'base_url' => $baseUrl, 'model' => $model, 'priority' => $priority]); } catch (\Throwable $_) {}
        } catch (\Exception $e) {
            Logger::error('ConvertX editProvider: ' . $e->getMessage());
            try { ActivityLogger::logFailure(Auth::id(), 'edit_ai_provider', $e->getMessage()); } catch (\Throwable $_) {}
            $_SESSION['_flash']['error'] = 'Failed to update provider.';
        }
        $this->redirect('/admin/projects/convertx/settings');
    }

    public function deleteProvider(): void
    {
        $this->requirePermission('convertx.settings');
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid token.';
            $this->redirect('/admin/projects/convertx/settings');
            return;
        }
        $id = (int) ($_POST['provider_id'] ?? 0);
        if ($id) {
            try {
                $this->db->query("DELETE FROM convertx_ai_providers WHERE id=:id", ['id' => $id]);
                $_SESSION['_flash']['success'] = 'Provider deleted.';
                try { ActivityLogger::logDelete(Auth::id(), 'convertx', 'ai_provider', $id); } catch (\Throwable $_) {}
            } catch (\Exception $e) {
                $_SESSION['_flash']['error'] = 'Failed to delete provider.';
            }
        }
        $this->redirect('/admin/projects/convertx/settings');
    }

    // ------------------------------------------------------------------ //
    //  Test AI Provider Connection                                         //
    // ------------------------------------------------------------------ //

    /**
     * POST /admin/projects/convertx/settings/test-provider
     * Returns JSON: { success, message, latency_ms }
     */
    public function testProvider(): void
    {
        $this->requirePermission('convertx.settings');
        // Prevent any accidental buffered output from leaking into the JSON
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/json');

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            return;
        }

        $id = (int) ($_POST['provider_id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Missing provider ID']);
            return;
        }

        $row = $this->db->fetch(
            "SELECT * FROM convertx_ai_providers WHERE id = :id LIMIT 1",
            ['id' => $id]
        );
        if (!$row) {
            echo json_encode(['success' => false, 'message' => 'Provider not found']);
            return;
        }

        $apiKey  = $row['api_key']  ?? '';
        $baseUrl = rtrim($row['base_url'] ?? '', '/');
        if (empty($baseUrl)) {
            // Apply sensible defaults so the test works even when base_url was never saved
            $defaults = [
                'openai'      => 'https://api.openai.com',
                'huggingface' => 'https://api-inference.huggingface.co',
            ];
            $baseUrl = $defaults[$row['slug'] ?? ''] ?? '';
        }
        $slug    = $row['slug'] ?? '';
        $start   = microtime(true);

        switch ($slug) {
            case 'openai':
                if (empty($apiKey)) {
                    echo json_encode(['success' => false, 'message' => 'No API key configured']);
                    return;
                }
                // List models — lightweight, authenticated GET
                $ch = curl_init($baseUrl . '/v1/models');
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $apiKey],
                    CURLOPT_TIMEOUT        => 15,
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_FOLLOWLOCATION => true,
                ]);
                $body     = (string) curl_exec($ch);
                $curlErr  = curl_error($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                $latency  = (int) round((microtime(true) - $start) * 1000);
                if ($curlErr) {
                    echo json_encode(['success' => false, 'message' => 'Connection error: ' . $curlErr, 'latency_ms' => $latency]);
                    return;
                }
                if ($httpCode === 200) {
                    $data = json_decode($body, true);
                    $cnt  = count($data['data'] ?? []);
                    $this->db->query(
                        "UPDATE convertx_ai_providers SET is_healthy=1, health_checked_at=NOW() WHERE id=:id",
                        ['id' => $id]
                    );
                    echo json_encode(['success' => true,
                        'message' => "Connected — {$cnt} model(s) available", 'latency_ms' => $latency]);
                } else {
                    $err = json_decode($body, true)['error']['message'] ?? "HTTP {$httpCode}";
                    $this->db->query(
                        "UPDATE convertx_ai_providers SET is_healthy=0, health_checked_at=NOW() WHERE id=:id",
                        ['id' => $id]
                    );
                    echo json_encode(['success' => false, 'message' => $err, 'latency_ms' => $latency]);
                }
                break;

            case 'huggingface':
                if (empty($apiKey)) {
                    echo json_encode(['success' => false, 'message' => 'No API key configured']);
                    return;
                }
                // Validate token via whoami endpoint
                $ch = curl_init('https://huggingface.co/api/whoami-v2');
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $apiKey],
                    CURLOPT_TIMEOUT        => 15,
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_FOLLOWLOCATION => true,
                ]);
                $body     = (string) curl_exec($ch);
                $curlErr  = curl_error($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                $latency  = (int) round((microtime(true) - $start) * 1000);
                if ($curlErr) {
                    echo json_encode(['success' => false, 'message' => 'Connection error: ' . $curlErr, 'latency_ms' => $latency]);
                    break;
                }
                if ($httpCode === 200) {
                    $data = json_decode($body, true);
                    $name = $data['name'] ?? 'unknown';
                    $this->db->query(
                        "UPDATE convertx_ai_providers SET is_healthy=1, health_checked_at=NOW() WHERE id=:id",
                        ['id' => $id]
                    );
                    echo json_encode(['success' => true,
                        'message' => "Connected as @{$name}", 'latency_ms' => $latency]);
                } else {
                    $errMsg = json_decode($body, true)['error'] ?? "HTTP {$httpCode}";
                    $this->db->query(
                        "UPDATE convertx_ai_providers SET is_healthy=0, health_checked_at=NOW() WHERE id=:id",
                        ['id' => $id]
                    );
                    echo json_encode(['success' => false, 'message' => $errMsg, 'latency_ms' => $latency]);
                }
                break;

            case 'tesseract':
                $tess    = trim((string) shell_exec('which tesseract 2>/dev/null'));
                $latency = (int) round((microtime(true) - $start) * 1000);
                if ($tess) {
                    exec('tesseract --version 2>&1', $verLines);
                    $ver = trim($verLines[0] ?? 'unknown');
                    $this->db->query(
                        "UPDATE convertx_ai_providers SET is_healthy=1, health_checked_at=NOW() WHERE id=:id",
                        ['id' => $id]
                    );
                    echo json_encode(['success' => true,
                        'message' => "Tesseract found: {$ver}", 'latency_ms' => $latency]);
                } else {
                    $this->db->query(
                        "UPDATE convertx_ai_providers SET is_healthy=0, health_checked_at=NOW() WHERE id=:id",
                        ['id' => $id]
                    );
                    echo json_encode(['success' => false, 'message' => 'Tesseract not found in PATH', 'latency_ms' => $latency]);
                }
                break;

            default:
                // Generic: try a HEAD request to the base URL if set
                if ($baseUrl && $apiKey) {
                    $ch = curl_init($baseUrl);
                    curl_setopt_array($ch, [
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_NOBODY         => true,
                        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $apiKey],
                        CURLOPT_TIMEOUT        => 15,
                    ]);
                    curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    $latency  = (int) round((microtime(true) - $start) * 1000);
                    $ok = $httpCode > 0 && $httpCode < 500;
                    $this->db->query(
                        "UPDATE convertx_ai_providers SET is_healthy=:h, health_checked_at=NOW() WHERE id=:id",
                        ['h' => (int) $ok, 'id' => $id]
                    );
                    echo json_encode(['success' => $ok,
                        'message' => "HTTP {$httpCode}", 'latency_ms' => $latency]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No base URL or API key to test']);
                }
                break;
        }
    }

    // ------------------------------------------------------------------ //
    //  Storage Monitor                                                     //
    // ------------------------------------------------------------------ //

    public function storage(): void
    {
        $this->requirePermission('convertx.storage');
        $uploadDir = BASE_PATH . '/storage/uploads/convertx';
        $diskUsed  = 0;
        $fileCount = 0;
        if (is_dir($uploadDir)) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($uploadDir, \RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
                if ($file->isFile()) {
                    $diskUsed  += $file->getSize();
                    $fileCount++;
                }
            }
        }

        $outputDir  = BASE_PATH . '/storage/converted';
        $outputUsed  = 0;
        $outputCount = 0;
        if (is_dir($outputDir)) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($outputDir, \RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
                if ($file->isFile()) {
                    $outputUsed  += $file->getSize();
                    $outputCount++;
                }
            }
        }

        $userStats = [];
        try {
            $userStats = $this->db->fetchAll(
                "SELECT u.id, u.name, u.email,
                        COUNT(j.id) AS total_jobs,
                        SUM(CASE WHEN j.status='completed' THEN 1 ELSE 0 END) AS completed
                   FROM users u
                   LEFT JOIN convertx_jobs j ON j.user_id = u.id
                  GROUP BY u.id
                 HAVING total_jobs > 0
                  ORDER BY total_jobs DESC
                  LIMIT 50"
            );
        } catch (\Exception $e) {}

        $this->view('admin/projects/convertx/storage', [
            'title'       => 'ConvertX — Storage Monitor',
            'diskUsed'    => $diskUsed,
            'fileCount'   => $fileCount,
            'outputUsed'  => $outputUsed,
            'outputCount' => $outputCount,
            'userStats'   => $userStats,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  Subscription Plans CRUD                                            //
    // ------------------------------------------------------------------ //

    public function plans(): void
    {
        $this->requirePermission('convertx.plans');
        $this->ensurePlansTables();
        $plans = [];
        try {
            $plans = $this->db->fetchAll(
                "SELECT p.*, COUNT(s.id) AS subscriber_count
                    FROM convertx_subscription_plans p
                    LEFT JOIN convertx_user_subscriptions s ON s.plan_id=p.id AND s.status='active'
                   GROUP BY p.id
                   ORDER BY p.sort_order ASC, p.price ASC"
            );
        } catch (\Exception $e) {}

        $featureKeys = array_keys($this->getPlanFeatures());
        foreach ($plans as &$plan) {
            $features = json_decode($plan['features'] ?? '{}', true) ?: [];
            $changed = false;
            foreach ($featureKeys as $k) {
                if (!array_key_exists($k, $features)) {
                    $features[$k] = ($plan['slug'] ?? '') === 'free' ? $this->getDefaultFreePlanFeatures()[$k] ?? false : false;
                    $changed = true;
                }
            }
            if ($changed) {
                try {
                    $this->db->query(
                        "UPDATE convertx_subscription_plans SET features = :features, updated_at = NOW() WHERE id = :id",
                        ['features' => json_encode($features), 'id' => (int) $plan['id']]
                    );
                } catch (\Exception $e) {}
                $plan['features'] = json_encode($features);
            }
        }
        unset($plan);

        $this->view('admin/projects/convertx/plans', [
            'title' => 'ConvertX — Subscription Plans',
            'plans' => $plans,
            'planFeatureLabels' => $this->getPlanFeatures(),
        ]);
    }

    public function createPlan(): void
    {
        $this->requirePermission('convertx.plans');
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid token.';
            $this->redirect('/admin/projects/convertx/plans');
            return;
        }
        $name       = trim($_POST['name'] ?? '');
        $slug       = strtolower(trim(preg_replace('/[^a-z0-9_\-]/', '-', $_POST['slug'] ?? '')));
        $desc       = trim($_POST['description'] ?? '');
        $price      = max(0, (float) ($_POST['price'] ?? 0));
        $billing    = in_array($_POST['billing_cycle'] ?? '', ['monthly','yearly','lifetime']) ? $_POST['billing_cycle'] : 'monthly';
        $maxJobs    = (int) ($_POST['max_jobs_per_month'] ?? 50);
        $maxFile    = (int) ($_POST['max_file_size_mb'] ?? 10);
        $maxBatch   = (int) ($_POST['max_batch_size'] ?? 5);
        $aiAccess   = !empty($_POST['ai_access']) ? 1 : 0;
        $apiAccess  = !empty($_POST['api_access']) ? 1 : 0;
        $batchConv  = !empty($_POST['batch_convert']) ? 1 : 0;
        $priority   = !empty($_POST['priority_processing']) ? 1 : 0;
        $status     = $_POST['status'] ?? 'active';
        $sortOrder  = (int) ($_POST['sort_order'] ?? 0);
        $features   = $this->collectPlanFeaturesFromPost();

        if (!$name || !$slug) {
            $_SESSION['_flash']['error'] = 'Name and slug are required.';
            $this->redirect('/admin/projects/convertx/plans');
            return;
        }
        try {
            $this->db->query(
                "INSERT INTO convertx_subscription_plans
                    (name,slug,description,price,billing_cycle,max_jobs_per_month,max_file_size_mb,max_batch_size,ai_access,api_access,batch_convert,priority_processing,features,status,sort_order)
                 VALUES (:name,:slug,:desc,:price,:billing,:jobs,:file,:batch,:ai,:api,:bconv,:prio,:features,:status,:sort)",
                [
                    'name'=>$name,'slug'=>$slug,'desc'=>$desc,'price'=>$price,'billing'=>$billing,
                    'jobs'=>$maxJobs,'file'=>$maxFile,'batch'=>$maxBatch,'ai'=>$aiAccess,
                    'api'=>$apiAccess,'bconv'=>$batchConv,'prio'=>$priority,'features'=>json_encode($features),
                    'status'=>$status,'sort'=>$sortOrder
                ]
            );
            $_SESSION['_flash']['success'] = 'Plan created.';
            try { ActivityLogger::logCreate(Auth::id(), 'convertx', 'subscription_plan', 0, ['name' => $name, 'slug' => $slug, 'price' => $price]); } catch (\Throwable $_) {}
        } catch (\Exception $e) {
            Logger::error('ConvertX createPlan: ' . $e->getMessage());
            try { ActivityLogger::logFailure(Auth::id(), 'create_subscription_plan', $e->getMessage()); } catch (\Throwable $_) {}
            $_SESSION['_flash']['error'] = 'Failed to create plan: ' . $e->getMessage();
        }
        $this->redirect('/admin/projects/convertx/plans');
    }

    public function updatePlan(): void
    {
        $this->requirePermission('convertx.plans');
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid token.';
            $this->redirect('/admin/projects/convertx/plans');
            return;
        }
        $id         = (int) ($_POST['plan_id'] ?? 0);
        $name       = trim($_POST['name'] ?? '');
        $desc       = trim($_POST['description'] ?? '');
        $price      = max(0, (float) ($_POST['price'] ?? 0));
        $billing    = in_array($_POST['billing_cycle'] ?? '', ['monthly','yearly','lifetime']) ? $_POST['billing_cycle'] : 'monthly';
        $maxJobs    = (int) ($_POST['max_jobs_per_month'] ?? 50);
        $maxFile    = (int) ($_POST['max_file_size_mb'] ?? 10);
        $maxBatch   = (int) ($_POST['max_batch_size'] ?? 5);
        $aiAccess   = !empty($_POST['ai_access']) ? 1 : 0;
        $apiAccess  = !empty($_POST['api_access']) ? 1 : 0;
        $batchConv  = !empty($_POST['batch_convert']) ? 1 : 0;
        $priority   = !empty($_POST['priority_processing']) ? 1 : 0;
        $status     = $_POST['status'] ?? 'active';
        $features   = $this->collectPlanFeaturesFromPost();

        if (!$id || !$name) {
            $_SESSION['_flash']['error'] = 'Invalid request.';
            $this->redirect('/admin/projects/convertx/plans');
            return;
        }
        try {
            $this->db->query(
                "UPDATE convertx_subscription_plans SET
                    name=:name, description=:desc, price=:price, billing_cycle=:billing,
                    max_jobs_per_month=:jobs, max_file_size_mb=:file, max_batch_size=:batch,
                    ai_access=:ai, api_access=:api, batch_convert=:bconv, priority_processing=:prio,
                    features=:features, status=:status
                 WHERE id=:id",
                [
                    'name'=>$name,'desc'=>$desc,'price'=>$price,'billing'=>$billing,
                    'jobs'=>$maxJobs,'file'=>$maxFile,'batch'=>$maxBatch,'ai'=>$aiAccess,
                    'api'=>$apiAccess,'bconv'=>$batchConv,'prio'=>$priority,'features'=>json_encode($features),
                    'status'=>$status,'id'=>$id
                ]
            );
            $_SESSION['_flash']['success'] = 'Plan updated.';
            try { ActivityLogger::logUpdate(Auth::id(), 'convertx', 'subscription_plan', $id, [], ['name' => $name, 'price' => $price, 'status' => $status]); } catch (\Throwable $_) {}
        } catch (\Exception $e) {
            $_SESSION['_flash']['error'] = 'Failed to update plan.';
        }
        $this->redirect('/admin/projects/convertx/plans');
    }

    public function deletePlan(): void
    {
        $this->requirePermission('convertx.plans');
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid token.';
            $this->redirect('/admin/projects/convertx/plans');
            return;
        }
        $id = (int) ($_POST['plan_id'] ?? 0);
        if ($id) {
            try {
                $this->db->query("DELETE FROM convertx_subscription_plans WHERE id=:id", ['id' => $id]);
                $_SESSION['_flash']['success'] = 'Plan deleted.';
                try { ActivityLogger::logDelete(Auth::id(), 'convertx', 'subscription_plan', $id); } catch (\Throwable $_) {}
            } catch (\Exception $e) {
                $_SESSION['_flash']['error'] = 'Failed to delete plan.';
            }
        }
        $this->redirect('/admin/projects/convertx/plans');
    }

    private function ensurePlansTables(): void
    {
        try {
            $this->db->query("CREATE TABLE IF NOT EXISTS convertx_subscription_plans (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                slug VARCHAR(50) NOT NULL UNIQUE,
                description TEXT NULL,
                price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                billing_cycle ENUM('monthly','yearly','lifetime') DEFAULT 'monthly',
                max_jobs_per_month INT NOT NULL DEFAULT 50,
                max_file_size_mb INT NOT NULL DEFAULT 10,
                max_batch_size INT NOT NULL DEFAULT 5,
                ai_access TINYINT(1) NOT NULL DEFAULT 0,
                api_access TINYINT(1) NOT NULL DEFAULT 0,
                batch_convert TINYINT(1) NOT NULL DEFAULT 1,
                priority_processing TINYINT(1) NOT NULL DEFAULT 0,
                features TEXT NULL,
                status ENUM('active','inactive') DEFAULT 'active',
                sort_order INT DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            try {
                $this->db->query("ALTER TABLE convertx_subscription_plans ADD COLUMN features TEXT NULL AFTER priority_processing");
            } catch (\Exception $e) {}
            $this->db->query("CREATE TABLE IF NOT EXISTS convertx_user_subscriptions (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                plan_id INT UNSIGNED NOT NULL,
                status ENUM('active','cancelled','expired','trial') DEFAULT 'active',
                started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                expires_at DATETIME NULL,
                assigned_by INT UNSIGNED NULL,
                notes VARCHAR(500) NULL,
                updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_plan_id (plan_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $this->db->query("INSERT IGNORE INTO convertx_subscription_plans
                (name,slug,description,price,billing_cycle,max_jobs_per_month,max_file_size_mb,max_batch_size,ai_access,api_access,batch_convert,priority_processing,features,status,sort_order)
                VALUES
                ('Free','free','Basic conversion, limited monthly jobs.',0.00,'monthly',50,10,5,0,0,1,0,:free_features,'active',1),
                ('Pro','pro','Unlimited conversions with AI and API access.',9.99,'monthly',-1,100,50,1,1,1,1,:pro_features,'active',2),
                ('Enterprise','enterprise','Full access, custom limits, priority support.',29.99,'monthly',-1,500,100,1,1,1,1,:ent_features,'active',3)", [
                    'free_features' => json_encode($this->getDefaultFreePlanFeatures()),
                    'pro_features'  => json_encode(array_fill_keys(array_keys($this->getPlanFeatures()), true)),
                    'ent_features'  => json_encode(array_fill_keys(array_keys($this->getPlanFeatures()), true)),
                ]);
        } catch (\Exception $e) {}
    }

    public function roles(): void
    {
        $this->requirePermission('convertx.settings');
        $this->ensureFeatureTables();

        $users = [];
        $userFeatures = [];
        try {
            $users = $this->db->fetchAll("SELECT id, name, email, role FROM users ORDER BY name");
            $rows = $this->db->fetchAll(
                "SELECT uf.*, u.name AS user_name, u.email AS user_email
                 FROM convertx_user_features uf
                 JOIN users u ON u.id = uf.user_id
                 ORDER BY u.name, uf.feature"
            );
            foreach ($rows as $row) {
                if (($row['feature'] ?? '') === '_use_plan') {
                    continue;
                }
                if (!isset($userFeatures[$row['user_id']])) {
                    $userFeatures[$row['user_id']] = [
                        'user_name' => $row['user_name'],
                        'user_email' => $row['user_email'],
                        'features' => [],
                    ];
                }
                $userFeatures[$row['user_id']]['features'][$row['feature']] = (bool) $row['enabled'];
            }
        } catch (\Exception $e) {}

        $this->view('admin/projects/convertx/roles', [
            'title' => 'ConvertX — Roles & Features',
            'allFeatures' => $this->getPlanFeatures(),
            'users' => $users,
            'userFeatures' => $userFeatures,
        ]);
    }

    public function getUserFeaturesApi(string $id): void
    {
        $this->requirePermission('convertx.settings');
        $userId = (int) $id;
        if ($userId <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid user.'], 400);
            return;
        }
        try {
            $service = new \Projects\ConvertX\Services\FeatureService();
            $features = $service->getFeatures($userId);
            $rows = $this->db->fetchAll(
                "SELECT feature, enabled FROM convertx_user_features WHERE user_id = :uid",
                ['uid' => $userId]
            );
            $raw = [];
            foreach ($rows as $r) {
                $raw[$r['feature']] = (bool) $r['enabled'];
            }
            $this->json(['success' => true, 'features' => $features, 'raw_overrides' => $raw]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    public function setUserFeature(): void
    {
        $this->requirePermission('convertx.settings');
        if (!Security::validateCsrfToken($_POST['_csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid request.'], 403);
            return;
        }
        $userId = (int) ($_POST['user_id'] ?? 0);
        $feature = trim((string) ($_POST['feature'] ?? ''));
        $enabled = !empty($_POST['enabled']) ? 1 : 0;
        if ($userId <= 0 || !array_key_exists($feature, $this->getPlanFeatures())) {
            $this->json(['success' => false, 'message' => 'Invalid user or feature.'], 400);
            return;
        }
        try {
            $this->db->query(
                "INSERT INTO convertx_user_features (user_id, feature, enabled, updated_at)
                 VALUES (:uid, :feature, :enabled, NOW())
                 ON DUPLICATE KEY UPDATE enabled = VALUES(enabled), updated_at = NOW()",
                ['uid' => $userId, 'feature' => $feature, 'enabled' => $enabled]
            );
            $this->json(['success' => true]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    public function removeUserFeatures(): void
    {
        $this->requirePermission('convertx.settings');
        if (!Security::validateCsrfToken($_POST['_csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid request.'], 403);
            return;
        }
        $userId = (int) ($_POST['user_id'] ?? 0);
        if ($userId <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid user.'], 400);
            return;
        }
        try {
            $this->db->query("DELETE FROM convertx_user_features WHERE user_id = :uid", ['uid' => $userId]);
            $this->json(['success' => true]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    // ------------------------------------------------------------------ //
    //  Private helpers                                                     //
    // ------------------------------------------------------------------ //

    private function getStats(): array
    {
        $stats = [
            'total_jobs'     => 0,
            'completed_jobs' => 0,
            'failed_jobs'    => 0,
            'pending_jobs'   => 0,
            'active_users'   => 0,
            'tokens_used'    => 0,
        ];

        try {
            $row = $this->db->fetch(
                "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN status='completed'  THEN 1 ELSE 0 END) AS completed,
                    SUM(CASE WHEN status='failed'     THEN 1 ELSE 0 END) AS failed,
                    SUM(CASE WHEN status='pending'    THEN 1 ELSE 0 END) AS pending,
                    COUNT(DISTINCT user_id) AS active_users,
                    COALESCE(SUM(tokens_used), 0) AS tokens
                   FROM convertx_jobs"
            );
            if ($row) {
                $stats['total_jobs']     = (int) $row['total'];
                $stats['completed_jobs'] = (int) $row['completed'];
                $stats['failed_jobs']    = (int) $row['failed'];
                $stats['pending_jobs']   = (int) $row['pending'];
                $stats['active_users']   = (int) $row['active_users'];
                $stats['tokens_used']    = (int) $row['tokens'];
            }
        } catch (\Exception $e) {
            // table may not exist yet
        }

        return $stats;
    }

    private function getRecentJobs(int $limit): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT j.*, u.name AS user_name, u.email AS user_email
                   FROM convertx_jobs j
                   LEFT JOIN users u ON u.id = j.user_id
                  ORDER BY j.created_at DESC
                  LIMIT $limit"
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    // ------------------------------------------------------------------ //
    //  Image Tools Settings                                                //
    // ------------------------------------------------------------------ //

    public function imageToolsSettings(): void
    {
        $this->requirePermission('convertx.settings');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updateImageToolsSettings();
            return;
        }

        $rows = [];
        try {
            $rows = $this->db->fetchAll('SELECT setting_key, setting_value FROM convertx_image_tools_settings');
        } catch (\Exception $e) {
            // Table may not exist yet
        }

        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        $defaults = [
            'upscale_api_key'       => '',
            'upscale_api_provider'  => '',
            'removebg_api_key'      => '',
            'removebg_api_provider' => 'iloveapi',
            'max_files'             => '20',
            'max_file_size_mb'      => '50',
            'allowed_image_formats' => 'jpg,jpeg,png,gif,webp,bmp',
        ];
        foreach ($defaults as $k => $v) {
            if (!array_key_exists($k, $settings)) {
                $settings[$k] = $v;
            }
        }

        $this->view('admin/projects/convertx/image-tools-settings', [
            'title'    => 'Image Tools Settings',
            'settings' => $settings,
        ]);
    }

    public function updateImageToolsSettings(): void
    {
        $this->requirePermission('convertx.settings');

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->redirect('/admin/projects/convertx/image-tools-settings');
            return;
        }

        $keys = [
            'upscale_api_key', 'upscale_api_provider',
            'removebg_api_key', 'removebg_api_provider',
            'max_files', 'max_file_size_mb', 'allowed_image_formats',
        ];
        foreach ($keys as $k) {
            $raw = trim((string) ($_POST[$k] ?? ''));
            // Sanitize numeric fields
            if ($k === 'max_files') {
                $v = (string) max(1, min(100, (int) $raw ?: 20));
            } elseif ($k === 'max_file_size_mb') {
                $v = (string) max(1, min(500, (int) $raw ?: 50));
            } elseif ($k === 'allowed_image_formats') {
                // Allow only safe extension characters
                $exts = array_filter(array_map('trim', explode(',', strtolower($raw))));
                $exts = array_filter($exts, fn($e) => preg_match('/^[a-z0-9]{1,10}$/', $e));
                $v    = implode(',', $exts) ?: 'jpg,jpeg,png,gif,webp,bmp';
            } else {
                $v = $raw;
            }
            try {
                $this->db->query(
                    'INSERT INTO convertx_image_tools_settings (setting_key, setting_value, updated_at)
                     VALUES (:k, :v, NOW())
                     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()',
                    ['k' => $k, 'v' => $v]
                );
            } catch (\Exception $e) {
                Logger::error('ImageToolsSettings update failed for key ' . $k . ': ' . $e->getMessage());
            }
        }

        try {
            ActivityLogger::logSettingsUpdated(Auth::id(), 'convertx', [], ['image_tools_settings' => 'updated']);
        } catch (\Throwable $_) {}

        $_SESSION['_flash']['success'] = 'Image Tools settings saved.';
        $this->redirect('/admin/projects/convertx/image-tools-settings');
    }

    // ------------------------------------------------------------------ //
    //  Upload Limits (all ConvertX tools)                                  //
    // ------------------------------------------------------------------ //

    public function uploadLimits(): void
    {
        $this->requirePermission('convertx.settings');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updateUploadLimits();
            return;
        }

        $rows = [];
        try {
            $this->db->query(
                "CREATE TABLE IF NOT EXISTS convertx_image_tools_settings (
                    setting_key   VARCHAR(80)  NOT NULL PRIMARY KEY,
                    setting_value TEXT         NOT NULL DEFAULT '',
                    updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
            );
            $rows = $this->db->fetchAll('SELECT setting_key, setting_value FROM convertx_image_tools_settings');
        } catch (\Exception $e) {
            // silently continue
        }

        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        $defaults = [
            // Image tool limits
            'max_files'             => '20',
            'max_file_size_mb'      => '50',
            'allowed_image_formats' => 'jpg,jpeg,png,gif,webp,bmp',
            // PDF tool limits
            'max_pdf_files'         => '20',
            'max_pdf_size_mb'       => '200',
            // Conversion limits
            'max_conversion_file_size_mb' => '200',
        ];
        foreach ($defaults as $k => $v) {
            if (!array_key_exists($k, $settings)) {
                $settings[$k] = $v;
            }
        }

        $this->view('admin/projects/convertx/upload-limits', [
            'title'    => 'Upload Limits',
            'settings' => $settings,
        ]);
    }

    public function updateUploadLimits(): void
    {
        $this->requirePermission('convertx.settings');

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->redirect('/admin/projects/convertx/upload-limits');
            return;
        }

        $sanitize = [
            'max_files'                   => fn($v) => (string) max(1, min(200, (int) $v ?: 20)),
            'max_file_size_mb'            => fn($v) => (string) max(1, min(2000, (int) $v ?: 50)),
            'allowed_image_formats'       => function ($v) {
                $exts = array_filter(array_map('trim', explode(',', strtolower((string) $v))));
                $exts = array_filter($exts, fn($e) => preg_match('/^[a-z0-9]{1,10}$/', $e));
                return implode(',', $exts) ?: 'jpg,jpeg,png,gif,webp,bmp';
            },
            'max_pdf_files'               => fn($v) => (string) max(1, min(100, (int) $v ?: 20)),
            'max_pdf_size_mb'             => fn($v) => (string) max(1, min(2000, (int) $v ?: 200)),
            'max_conversion_file_size_mb' => fn($v) => (string) max(1, min(2000, (int) $v ?: 200)),
        ];

        foreach ($sanitize as $k => $fn) {
            $raw = trim((string) ($_POST[$k] ?? ''));
            $v   = $fn($raw);
            try {
                $this->db->query(
                    'INSERT INTO convertx_image_tools_settings (setting_key, setting_value, updated_at)
                     VALUES (:k, :v, NOW())
                     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()',
                    ['k' => $k, 'v' => $v]
                );
            } catch (\Exception $e) {
                Logger::error('UploadLimits update failed for key ' . $k . ': ' . $e->getMessage());
            }
        }

        try {
            ActivityLogger::logSettingsUpdated(Auth::id(), 'convertx', [], ['upload_limits' => 'updated']);
        } catch (\Throwable $_) {}

        $_SESSION['_flash']['success'] = 'Upload limits saved successfully.';
        $this->redirect('/admin/projects/convertx/upload-limits');
    }

    private function ensureFeatureTables(): void
    {
        try {
            $this->db->query("CREATE TABLE IF NOT EXISTS convertx_user_features (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                feature VARCHAR(100) NOT NULL,
                enabled TINYINT(1) NOT NULL DEFAULT 1,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_user_feature (user_id, feature),
                INDEX idx_user (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (\Exception $e) {}
    }

    private function collectPlanFeaturesFromPost(): array
    {
        $features = [];
        foreach (array_keys($this->getPlanFeatures()) as $key) {
            $features[$key] = !empty($_POST['feature_' . $key]);
        }
        return $features;
    }

    private function getDefaultFreePlanFeatures(): array
    {
        return [
            'page_dashboard'    => true,
            'page_convert'      => true,
            'page_history'      => true,
            'page_plan'         => true,
            'page_settings'     => true,
            'page_ocr'          => true,
            'page_pdf_merge'    => true,
            'page_pdf_split'    => true,
            'page_pdf_compress' => true,
            'page_img_compress' => true,
            'page_img_resize'   => true,
            'page_img_crop'     => true,
            'page_img_rotate'   => true,
            'page_img_watermark'=> true,
            'page_docs'         => false,
            'page_apikeys'      => false,
            'page_batch'        => false,
            'page_ai_process'   => false,
            'page_ocr_ai'       => false,
            'page_img_meme'     => false,
            'page_img_editor'   => false,
            'page_img_upscale'  => false,
            'page_img_remove_bg'=> false,
        ];
    }

    private function getPlanFeatures(): array
    {
        return [
            'page_dashboard'    => 'Dashboard',
            'page_convert'      => 'Convert File',
            'page_ai_process'   => 'AI Process',
            'page_batch'        => 'Batch Convert',
            'page_history'      => 'History',
            'page_ocr'          => 'OCR Extract',
            'page_ocr_ai'       => 'AI OCR',
            'page_pdf_merge'    => 'PDF Merge',
            'page_pdf_split'    => 'PDF Split',
            'page_pdf_compress' => 'PDF Compress',
            'page_img_compress' => 'Image Compress',
            'page_img_resize'   => 'Image Resize',
            'page_img_crop'     => 'Image Crop',
            'page_img_watermark'=> 'Image Watermark',
            'page_img_rotate'   => 'Image Rotate',
            'page_img_meme'     => 'Meme Generator',
            'page_img_editor'   => 'Photo Editor',
            'page_img_upscale'  => 'Upscale Image',
            'page_img_remove_bg'=> 'Remove Background',
            'page_docs'         => 'API Docs',
            'page_apikeys'      => 'API Keys & Analytics',
            'page_plan'         => 'Plans & Pricing',
            'page_settings'     => 'Settings',
        ];
    }

    private function getAiUsageStats(): array
    {
        $stats = [
            'providers' => [],
            'users' => [],
            'this_month_tokens' => 0,
            'this_month_ai_jobs' => 0,
        ];
        try {
            $month = $this->db->fetch(
                "SELECT
                    COALESCE(SUM(tokens_used), 0) AS tokens,
                    SUM(CASE WHEN ai_tasks IS NOT NULL AND ai_tasks != '[]' AND ai_tasks != '' THEN 1 ELSE 0 END) AS ai_jobs
                 FROM convertx_jobs
                 WHERE YEAR(created_at)=YEAR(NOW()) AND MONTH(created_at)=MONTH(NOW())"
            );
            $stats['this_month_tokens'] = (int) ($month['tokens'] ?? 0);
            $stats['this_month_ai_jobs'] = (int) ($month['ai_jobs'] ?? 0);
        } catch (\Exception $e) {}

        try {
            $stats['providers'] = $this->db->fetchAll(
                "SELECT provider_used, COUNT(*) AS jobs, COALESCE(SUM(tokens_used),0) AS tokens
                 FROM convertx_jobs
                 WHERE provider_used IS NOT NULL AND provider_used != ''
                 GROUP BY provider_used
                 ORDER BY tokens DESC, jobs DESC
                 LIMIT 10"
            ) ?: [];
        } catch (\Exception $e) {}

        try {
            $stats['users'] = $this->db->fetchAll(
                "SELECT u.id, u.name, u.email, COUNT(j.id) AS jobs, COALESCE(SUM(j.tokens_used),0) AS tokens
                 FROM convertx_jobs j
                 JOIN users u ON u.id = j.user_id
                 WHERE j.tokens_used > 0
                 GROUP BY u.id, u.name, u.email
                 ORDER BY tokens DESC
                 LIMIT 10"
            ) ?: [];
        } catch (\Exception $e) {}

        return $stats;
    }
}
