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

    public function __construct()
    {
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
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $storedName = uniqid('cx_', true) . '.' . $ext;
        $storedPath = $uploadDir . '/' . $storedName;

        if (!move_uploaded_file($_FILES['file']['tmp_name'], $storedPath)) {
            $this->error('Failed to save uploaded file', 500);
            return;
        }

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
    }

    // ------------------------------------------------------------------ //
    //  Auth helpers                                                         //
    // ------------------------------------------------------------------ //

    /**
     * Validate API key from header or query param.
     * Returns the owning user ID on success, or null after sending 401.
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
                "SELECT user_id FROM convertx_api_keys WHERE api_key = :key AND is_active = 1 LIMIT 1",
                ['key' => $key]
            );
            if ($row) {
                return (int) $row['user_id'];
            }
        } catch (\Exception $e) {
            Logger::error('ConvertX API auth: ' . $e->getMessage());
        }

        $this->error('Invalid API key', 401);
        return null;
    }

    /**
     * Simple per-user-per-minute rate limiter using file-based lock.
     */
    private function checkRateLimit(int $userId): bool
    {
        $file  = sys_get_temp_dir() . '/cx_rl_' . $userId . '_' . date('YmdHi');
        $count = (int) @file_get_contents($file);

        if ($count >= self::RATE_LIMIT_PER_MIN) {
            http_response_code(429);
            header('Retry-After: 60');
            echo json_encode(['success' => false, 'error' => 'Rate limit exceeded']);
            return false;
        }

        file_put_contents($file, $count + 1);
        return true;
    }

    private function error(string $message, int $code = 400): void
    {
        http_response_code($code);
        echo json_encode(['success' => false, 'error' => $message]);
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
        } catch (\Exception $e) {
            Logger::error('ConvertX API cancelJob: ' . $e->getMessage());
            $this->error('Failed to cancel job', 500);
        }
    }
}
