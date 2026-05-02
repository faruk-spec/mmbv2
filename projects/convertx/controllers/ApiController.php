<?php
/**
 * ConvertX REST API Controller
 *
 * Provides a versioned developer API for programmatic access.
 *
 * Authentication: API key via X-Api-Key header or ?api_key= query param.
 * Rate-limiting is enforced per API key.
 *
 * @package MMB\Projects\ConvertX\Controllers
 */

namespace Projects\ConvertX\Controllers;

use Core\Auth;
use Core\Logger;
use Core\Database;
use Core\SecureUpload;
use Projects\ConvertX\Models\ConversionJobModel;
use Projects\ConvertX\Services\ConversionService;
use Projects\ConvertX\Services\JobQueueService;

class ApiController
{
    private ConversionJobModel $jobModel;
    private ConversionService  $conversionService;
    private JobQueueService    $queueService;

    private const API_VERSION         = 'v1';
    private const RATE_LIMIT_PER_MIN  = 60;

    private const ALLOWED_EXTENSIONS = [
        'pdf', 'docx', 'doc', 'odt', 'rtf', 'txt', 'html', 'md', 'epub',
        'xlsx', 'xls', 'ods', 'csv', 'tsv',
        'pptx', 'ppt', 'odp',
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'svg', 'ico',
    ];

    /** Resolved API key row from DB (set by authenticateApiKey) */
    private ?array $currentKeyRow = null;

    /** Request start time (microtime) for latency tracking */
    private float $requestStart;

    public function __construct()
    {
        $this->requestStart = microtime(true);
        ob_start(); // capture any stray PHP warnings before JSON is sent
        $this->jobModel          = new ConversionJobModel();
        $this->conversionService = new ConversionService();
        $this->queueService      = new JobQueueService();

        ob_end_clean();
        header('Content-Type: application/json');
        header('X-Api-Version: ' . self::API_VERSION);
    }

    // ------------------------------------------------------------------ //
    //  API documentation index                                             //
    // ------------------------------------------------------------------ //

    public function index(): void
    {
        echo json_encode([
            'api'     => 'ConvertX',
            'version' => self::API_VERSION,
            'endpoints' => [
                'POST /projects/convertx/api/convert'           => 'Submit a conversion job',
                'GET  /projects/convertx/api/status/{id}'       => 'Poll job status by ID',
                'GET  /projects/convertx/api/download/{id}'     => 'Download converted file',
                'DELETE /projects/convertx/api/jobs/{id}'       => 'Cancel a pending job',
                'GET  /projects/convertx/api/history'           => 'List your jobs (paged)',
                'GET  /projects/convertx/api/usage'             => 'Show monthly usage stats',
                'GET  /projects/convertx/api/formats'           => 'List supported formats',
                'GET  /projects/convertx/api/plans'             => 'List subscription plans',
            ],
        ]);
    }

    // ------------------------------------------------------------------ //
    //  Submit conversion                                                    //
    // ------------------------------------------------------------------ //

    public function convert(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Method not allowed', 405);
            return;
        }

        $userId = $this->authenticateApiKey();
        if (!$userId) {
            return;
        }

        if (!$this->checkRateLimit($userId)) {
            return;
        }

        if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->error('File upload required', 400);
            return;
        }

        $outputFormat = strtolower(trim($_POST['output_format'] ?? ''));
        if (!in_array($outputFormat, self::ALLOWED_EXTENSIONS, true)) {
            $this->error('Invalid output_format', 400);
            return;
        }

        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            $this->error('Unsupported input file type', 400);
            return;
        }

        $uploadDir = BASE_PATH . '/storage/uploads/convertx/' . $userId;

        $secureResult = SecureUpload::process($_FILES['file'], [
            'destination_dir'    => $uploadDir,
            'allowed_extensions' => self::ALLOWED_EXTENSIONS,
            'source'             => 'convertx.api',
            'user_id'            => $userId,
        ]);
        if (empty($secureResult['success'])) {
            $errMsg = trim($secureResult['error'] ?? '') ?: 'unknown reason';
            $this->errorAndLog($userId, 'convert', 'File rejected by security checks: ' . $errMsg, 422);
            return;
        }
        $storedPath  = $secureResult['path'];

        $inputFormat = $this->conversionService->detectFormat($storedPath, $_FILES['file']['name']);

        $webhookUrl = filter_var($_POST['webhook_url'] ?? '', FILTER_VALIDATE_URL) ?: '';

        // Parse AI tasks from comma-separated string: "ocr,summarize,translate:fr"
        $rawTasks = $_POST['ai_tasks'] ?? '';
        $aiTasks  = array_filter(array_map('trim', explode(',', $rawTasks)));

        try {
            $jobId = $this->queueService->enqueue($userId, [
                'input_path'     => $storedPath,
                'input_filename' => $_FILES['file']['name'],
                'input_format'   => $inputFormat,
                'output_format'  => $outputFormat,
                'webhook_url'    => $webhookUrl,
                'ai_tasks'       => array_values($aiTasks),
            ]);
        } catch (\Exception $e) {
            Logger::error('ConvertX API convert: ' . $e->getMessage());
            $this->error('Failed to queue job', 500);
            return;
        }

        http_response_code(202);
        echo json_encode([
            'success' => true,
            'job_id'  => $jobId,
            'status'  => ConversionJobModel::STATUS_PENDING,
            'message' => 'Job queued. Poll /api/status/' . $jobId . ' for updates.',
        ]);
        $this->logApiRequest($userId, 'convert', 202);
    }

    // ------------------------------------------------------------------ //
    //  Job status                                                           //
    // ------------------------------------------------------------------ //

    public function jobStatus(string $jobId): void
    {
        $userId = $this->authenticateApiKey();
        if (!$userId) {
            return;
        }

        $id  = (int) $jobId;
        $job = $id ? $this->jobModel->findForUser($id, $userId) : null;

        if (!$job) {
            $this->error('Job not found', 404);
            return;
        }

        echo json_encode([
            'success'        => true,
            'job_id'         => (int) $job['id'],
            'status'         => $job['status'],
            'input_format'   => $job['input_format'],
            'output_format'  => $job['output_format'],
            'created_at'     => $job['created_at'],
            'completed_at'   => $job['completed_at'] ?? null,
            'output_filename' => $job['output_filename'] ?? null,
            'error_message'  => $job['error_message'] ?? null,
            'ai_result'      => isset($job['ai_result']) ? json_decode($job['ai_result'], true) : null,
        ]);
        $this->logApiRequest($userId, 'job_status', 200);
    }

    // ------------------------------------------------------------------ //
    //  Download                                                             //
    // ------------------------------------------------------------------ //

    public function download(string $jobId): void
    {
        $userId = $this->authenticateApiKey();
        if (!$userId) {
            return;
        }

        $id  = (int) $jobId;
        $job = $id ? $this->jobModel->findForUser($id, $userId) : null;

        if (!$job || $job['status'] !== ConversionJobModel::STATUS_COMPLETED) {
            $this->error('Completed job not found', 404);
            return;
        }

        $outputPath = $job['output_path'] ?? '';
        if (!$outputPath || !file_exists($outputPath)) {
            $this->error('File not available', 404);
            return;
        }

        $this->logApiRequest($userId, 'download', 200);
        $filename = $job['output_filename'] ?: basename($outputPath);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . addslashes($filename) . '"');
        header('Content-Length: ' . filesize($outputPath));
        readfile($outputPath);
    }

    // ------------------------------------------------------------------ //
    //  History                                                              //
    // ------------------------------------------------------------------ //

    public function history(): void
    {
        $userId = $this->authenticateApiKey();
        if (!$userId) {
            return;
        }

        $page   = max(1, (int) ($_GET['page'] ?? 1));
        $result = $this->jobModel->getHistory($userId, $page);

        echo json_encode([
            'success'  => true,
            'jobs'     => $result['jobs'],
            'total'    => $result['total'],
            'page'     => $result['page'],
            'per_page' => $result['per_page'],
        ]);
        $this->logApiRequest($userId, 'history', 200);
    }

    // ------------------------------------------------------------------ //
    //  Usage                                                                //
    // ------------------------------------------------------------------ //

    public function usage(): void
    {
        $userId = $this->authenticateApiKey();
        if (!$userId) {
            return;
        }

        $usage = $this->jobModel->getMonthlyUsage($userId);

        echo json_encode([
            'success' => true,
            'period'  => date('Y-m'),
            'usage'   => $usage,
        ]);
        $this->logApiRequest($userId, 'usage', 200);
    }

    // ------------------------------------------------------------------ //
    //  Auth helpers                                                         //
    // ------------------------------------------------------------------ //

    /**
     * Validate API key from header or query param, then enforce the api_access
     * feature flag for the owning user.  Returns the user ID on success, or
     * null after sending the appropriate JSON error response.
     *
     * This method must NEVER redirect or output HTML — all callers depend on a
     * clean JSON error so that API clients always receive machine-readable output.
     */
    private function authenticateApiKey(): ?int
    {
        $key = $_SERVER['HTTP_X_API_KEY']
            ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? null);

        if (!$key && isset($_GET['api_key'])) {
            $key = $_GET['api_key'];
        }

        if ($key && str_starts_with($key, 'Bearer ')) {
            $key = substr($key, 7);
        }

        if (!$key) {
            $this->error('API key required', 401);
            return null;
        }

        try {
            $db  = Database::getInstance();
            $row = $db->fetch(
                "SELECT id, user_id FROM convertx_api_keys WHERE api_key = :key AND is_active = 1 LIMIT 1",
                ['key' => $key]
            );
            if ($row) {
                $userId = (int) $row['user_id'];

                // Store row for logging purposes (key_id, masked key prefix)
                $this->currentKeyRow = [
                    'id'         => (int) $row['id'],
                    'user_id'    => $userId,
                    'key_prefix' => substr($key, 0, 8),
                ];

                // Enforce the api_access feature flag.  If the user's plan does
                // not include API access, return a JSON 403 — never a redirect.
                try {
                    require_once PROJECT_PATH . '/services/FeatureService.php';
                    $featureSvc = new \Projects\ConvertX\Services\FeatureService();
                    if (!$featureSvc->can($userId, 'api_access')) {
                        $this->error('API access is not available on your current plan. Please upgrade.', 403);
                        return null;
                    }
                } catch (\Exception $fe) {
                    Logger::error('ConvertX API feature check: ' . $fe->getMessage());
                    // Fail-closed: if feature service is unavailable, deny access to maintain security
                    $this->error('Unable to verify plan access. Please try again later.', 503);
                    return null;
                }

                // Track last-used timestamp and request count (best-effort).
                try {
                    $db->query(
                        "UPDATE convertx_api_keys
                            SET last_used_at  = NOW(),
                                request_count = request_count + 1
                          WHERE api_key = :key LIMIT 1",
                        ['key' => $key]
                    );
                } catch (\Exception $e) {
                    // Non-fatal
                }

                return $userId;
            }
        } catch (\Exception $e) {
            Logger::error('ConvertX API auth: ' . $e->getMessage());
        }

        $this->error('Invalid API key', 401);
        return null;
    }

    /**
     * DB-based per-key per-minute rate limiter.
     * Counts requests in the last 60 seconds from convertx_api_request_logs.
     * Falls back to allow on DB error to avoid blocking legitimate traffic.
     */
    private function checkRateLimit(int $userId): bool
    {
        try {
            $db    = Database::getInstance();
            $count = (int) $db->fetchColumn(
                "SELECT COUNT(*) FROM convertx_api_request_logs
                  WHERE user_id = :uid AND created_at >= DATE_SUB(NOW(), INTERVAL 60 SECOND)",
                ['uid' => $userId]
            );
            if ($count >= self::RATE_LIMIT_PER_MIN) {
                http_response_code(429);
                header('Retry-After: 60');
                echo json_encode(['success' => false, 'error' => 'Rate limit exceeded. Maximum ' . self::RATE_LIMIT_PER_MIN . ' requests per minute.']);
                return false;
            }
        } catch (\Exception $e) {
            Logger::error('ConvertX API rate-limit check failed: ' . $e->getMessage());
            // Fail-open on DB error to avoid blocking legitimate traffic
        }
        return true;
    }

    private function error(string $message, int $code = 400): void
    {
        http_response_code($code);
        echo json_encode(['success' => false, 'error' => $message]);
    }

    /**
     * Send error response and write a failed-request log entry.
     */
    private function errorAndLog(int $userId, string $action, string $message, int $code = 400): void
    {
        $this->error($message, $code);
        $this->logApiRequest($userId, $action, $code);
    }

    /**
     * Log an API request to convertx_api_request_logs.
     * All fields are captured automatically from the current request context.
     * Errors are swallowed — logging must never break the API response.
     */
    private function logApiRequest(int $userId, string $action, int $statusCode): void
    {
        try {
            $db = Database::getInstance();

            // Ensure the log table exists (created lazily to avoid migration dependency).
            $db->query(
                "CREATE TABLE IF NOT EXISTS convertx_api_request_logs (
                    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    user_id         INT UNSIGNED    NOT NULL,
                    api_key_id      INT UNSIGNED    NULL,
                    api_key_prefix  VARCHAR(16)     NOT NULL DEFAULT '',
                    session_id      VARCHAR(128)    NOT NULL DEFAULT '',
                    email           VARCHAR(255)    NOT NULL DEFAULT '',
                    endpoint        VARCHAR(255)    NOT NULL,
                    method          VARCHAR(10)     NOT NULL,
                    ip_address      VARCHAR(45)     NOT NULL,
                    user_agent      TEXT            NULL,
                    status_code     SMALLINT UNSIGNED NOT NULL,
                    response_time   INT UNSIGNED    NOT NULL DEFAULT 0 COMMENT 'Milliseconds',
                    action          VARCHAR(100)    NOT NULL DEFAULT '',
                    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_user_id   (user_id),
                    INDEX idx_created   (created_at),
                    INDEX idx_status    (status_code)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );

            $responseTime = (int) round((microtime(true) - $this->requestStart) * 1000);

            // Resolve email (best-effort, may not be available in API context)
            $email = '';
            try {
                $uRow  = $db->fetch("SELECT email FROM users WHERE id = :uid LIMIT 1", ['uid' => $userId]);
                $email = $uRow['email'] ?? '';
            } catch (\Exception $e) {
                // ignore
            }

            $db->query(
                "INSERT INTO convertx_api_request_logs
                    (user_id, api_key_id, api_key_prefix, session_id, email,
                     endpoint, method, ip_address, user_agent,
                     status_code, response_time, action)
                 VALUES
                    (:uid, :kid, :kpfx, :sid, :email,
                     :endpoint, :method, :ip, :ua,
                     :status, :rt, :action)",
                [
                    'uid'      => $userId,
                    'kid'      => $this->currentKeyRow['id'] ?? null,
                    'kpfx'     => $this->currentKeyRow['key_prefix'] ?? '',
                    // session_id() is '' for API-key-authenticated requests (sessions bypassed by design)
                    'sid'      => substr(session_id() ?: '', 0, 128),
                    'email'    => substr($email, 0, 255),
                    'endpoint' => substr(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '', 0, 255),
                    'method'   => strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
                    'ip'       => substr($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '', 0, 45),
                    'ua'       => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 512),
                    'status'   => $statusCode,
                    'rt'       => $responseTime,
                    'action'   => substr($action, 0, 100),
                ]
            );
        } catch (\Exception $e) {
            Logger::error('ConvertX API log write failed: ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  List supported formats                                              //
    // ------------------------------------------------------------------ //

    public function formats(): void
    {
        $config = require PROJECT_PATH . '/config.php';
        $limits = [];
        foreach ($config['upload_limits'] as $tier => $bytes) {
            if ($bytes >= 1073741824) {
                $limits[$tier] = number_format($bytes / 1073741824, 0) . ' GB';
            } elseif ($bytes >= 1048576) {
                $limits[$tier] = number_format($bytes / 1048576, 0) . ' MB';
            } else {
                $limits[$tier] = number_format($bytes / 1024, 0) . ' KB';
            }
        }
        echo json_encode([
            'success' => true,
            'formats' => $config['formats'],
            'upload_limits' => $limits,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  List plans                                                          //
    // ------------------------------------------------------------------ //

    public function plans(): void
    {
        try {
            $db    = Database::getInstance();
            $plans = $db->fetchAll(
                "SELECT name, slug, description, price, billing_cycle,
                        max_jobs_per_month, max_file_size_mb, ai_access, api_access
                   FROM convertx_subscription_plans
                  WHERE status = 'active'
                  ORDER BY sort_order ASC, price ASC"
            );
        } catch (\Exception $e) {
            $plans = [];
        }
        echo json_encode(['success' => true, 'plans' => $plans]);
    }

    // ------------------------------------------------------------------ //
    //  Cancel a pending job                                                //
    // ------------------------------------------------------------------ //

    public function cancelJob(string $jobId): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Method not allowed', 405);
            return;
        }

        $userId = $this->authenticateApiKey();
        if (!$userId) {
            return;
        }

        $id  = (int) $jobId;
        $job = $id ? $this->jobModel->findForUser($id, $userId) : null;

        if (!$job) {
            $this->error('Job not found', 404);
            return;
        }

        if (!in_array($job['status'], ['pending', 'processing'], true)) {
            $this->error('Job cannot be cancelled in its current state', 409);
            return;
        }

        try {
            $db = Database::getInstance();
            $db->query(
                "UPDATE convertx_jobs SET status='cancelled', updated_at=NOW() WHERE id=:id AND user_id=:uid",
                ['id' => $id, 'uid' => $userId]
            );
            echo json_encode(['success' => true, 'message' => 'Job cancelled']);
            $this->logApiRequest($userId, 'cancel_job', 200);
        } catch (\Exception $e) {
            Logger::error('ConvertX API cancelJob: ' . $e->getMessage());
            $this->error('Failed to cancel job', 500);
        }
    }
}
