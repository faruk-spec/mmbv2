<?php
/**
 * ConvertX Conversion Controller
 *
 * Handles file upload, job submission, status polling, and download.
 *
 * @package MMB\Projects\ConvertX\Controllers
 */

namespace Projects\ConvertX\Controllers;

use Core\Auth;
use Core\Security;
use Core\Logger;
use Projects\ConvertX\Models\ConversionJobModel;
use Projects\ConvertX\Services\ConversionService;
use Projects\ConvertX\Services\JobQueueService;

class ConversionController
{
    private ConversionJobModel $jobModel;
    private ConversionService  $conversionService;
    private JobQueueService    $queueService;

    /** Allowed file extensions (flat list) */
    private const ALLOWED_EXTENSIONS = [
        'pdf', 'docx', 'doc', 'odt', 'rtf', 'txt', 'html', 'md',
        'xlsx', 'xls', 'ods', 'csv',
        'pptx', 'ppt', 'odp',
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'svg',
    ];

    public function __construct()
    {
        $this->jobModel          = new ConversionJobModel();
        $this->conversionService = new ConversionService();
        $this->queueService      = new JobQueueService();
    }

    // ------------------------------------------------------------------ //
    //  Show conversion form                                                //
    // ------------------------------------------------------------------ //

    public function showForm(): void
    {
        $config = require PROJECT_PATH . '/config.php';
        $this->render('convert', [
            'title'   => 'Convert File',
            'user'    => Auth::user(),
            'formats' => $config['formats'],
        ]);
    }

    // ------------------------------------------------------------------ //
    //  Submit a new conversion job                                         //
    // ------------------------------------------------------------------ //

    public function submit(): void
    {
        // CSRF check
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->jsonError('Invalid request token', 403);
            return;
        }

        $userId = Auth::id();
        if (!$userId) {
            $this->jsonError('Authentication required', 401);
            return;
        }

        // Validate upload
        if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonError('File upload failed or no file provided', 400);
            return;
        }

        $outputFormat = strtolower(trim($_POST['output_format'] ?? ''));
        if (empty($outputFormat) || !in_array($outputFormat, self::ALLOWED_EXTENSIONS, true)) {
            $this->jsonError('Invalid output format', 400);
            return;
        }

        // Validate original extension
        $originalName = $_FILES['file']['name'];
        $ext          = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            $this->jsonError('Unsupported file type', 400);
            return;
        }

        // Validate file size against plan
        $config   = require PROJECT_PATH . '/config.php';
        $plan     = $this->getUserPlan($userId);
        $maxBytes = $config['upload_limits'][$plan] ?? $config['upload_limits']['free'];
        if ($_FILES['file']['size'] > $maxBytes) {
            $this->jsonError('File exceeds plan upload limit', 413);
            return;
        }

        // Persist file
        $uploadDir  = BASE_PATH . '/storage/uploads/convertx/' . $userId;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $storedName = uniqid('cx_', true) . '.' . $ext;
        $storedPath = $uploadDir . '/' . $storedName;

        if (!move_uploaded_file($_FILES['file']['tmp_name'], $storedPath)) {
            $this->jsonError('Failed to save uploaded file', 500);
            return;
        }

        // Detect actual format
        $inputFormat = $this->conversionService->detectFormat($storedPath, $originalName);

        // Build AI tasks list
        $aiTasks = [];
        if (!empty($_POST['ai_ocr'])      && $this->conversionService->requiresOCR($inputFormat)) {
            $aiTasks[] = 'ocr';
        }
        if (!empty($_POST['ai_summarize'])) {
            $aiTasks[] = 'summarize';
        }
        if (!empty($_POST['ai_translate']) && !empty($_POST['target_lang'])) {
            $aiTasks[] = 'translate:' . preg_replace('/[^a-z]/', '', strtolower($_POST['target_lang']));
        }
        if (!empty($_POST['ai_classify'])) {
            $aiTasks[] = 'classify';
        }

        // Enqueue
        try {
            $jobId = $this->queueService->enqueue($userId, [
                'input_path'     => $storedPath,
                'input_filename' => $originalName,
                'input_format'   => $inputFormat,
                'output_format'  => $outputFormat,
                'options'        => ['quality' => (int) ($_POST['quality'] ?? 85)],
                'ai_tasks'       => $aiTasks,
                'webhook_url'    => Security::sanitize($_POST['webhook_url'] ?? ''),
                'plan_tier'      => $plan,
            ]);
        } catch (\Exception $e) {
            Logger::error('ConversionController::submit - ' . $e->getMessage());
            $this->jsonError('Could not enqueue job', 500);
            return;
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'job_id'  => $jobId,
            'message' => 'Conversion job queued',
        ]);
    }

    // ------------------------------------------------------------------ //
    //  Poll job status                                                      //
    // ------------------------------------------------------------------ //

    public function status(int $jobId): void
    {
        $userId = Auth::id();
        $job    = $userId
            ? $this->jobModel->findForUser($jobId, $userId)
            : null;

        if (!$job) {
            $this->jsonError('Job not found', 404);
            return;
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success'    => true,
            'job_id'     => $job['id'],
            'status'     => $job['status'],
            'created_at' => $job['created_at'],
            'updated_at' => $job['updated_at'],
            'completed_at'   => $job['completed_at'] ?? null,
            'output_filename' => $job['output_filename'] ?? null,
            'error_message'   => $job['error_message'] ?? null,
            'ai_result'       => isset($job['ai_result']) ? json_decode($job['ai_result'], true) : null,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  Download converted file                                              //
    // ------------------------------------------------------------------ //

    public function download(int $jobId): void
    {
        $userId = Auth::id();
        $job    = $userId ? $this->jobModel->findForUser($jobId, $userId) : null;

        if (!$job || $job['status'] !== ConversionJobModel::STATUS_COMPLETED) {
            http_response_code(404);
            echo 'File not available';
            return;
        }

        $outputPath = $job['output_path'] ?? '';
        if (!$outputPath || !file_exists($outputPath)) {
            http_response_code(404);
            echo 'File not found on disk';
            return;
        }

        $filename = $job['output_filename'] ?: basename($outputPath);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . addslashes($filename) . '"');
        header('Content-Length: ' . filesize($outputPath));
        header('Cache-Control: private');
        readfile($outputPath);
    }

    // ------------------------------------------------------------------ //
    //  Cancel a pending job                                                 //
    // ------------------------------------------------------------------ //

    public function cancel(int $jobId): void
    {
        $userId = Auth::id();
        if (!$userId) {
            $this->jsonError('Authentication required', 401);
            return;
        }

        $this->jobModel->cancel($jobId, $userId);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Job cancelled']);
    }

    // ------------------------------------------------------------------ //
    //  Job history                                                          //
    // ------------------------------------------------------------------ //

    public function history(): void
    {
        $userId = Auth::id();
        if (!$userId) {
            header('Location: /login');
            exit;
        }

        $page   = max(1, (int) ($_GET['page'] ?? 1));
        $result = $this->jobModel->getHistory($userId, $page);

        $this->render('history', [
            'title'  => 'Conversion History',
            'user'   => Auth::user(),
            'result' => $result,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  Helpers                                                              //
    // ------------------------------------------------------------------ //

    private function getUserPlan(int $userId): string
    {
        // Integrate with existing plans system if available
        try {
            $db  = \Core\Database::getInstance();
            $row = $db->fetch(
                "SELECT plan_slug FROM user_plans WHERE user_id = :uid LIMIT 1",
                ['uid' => $userId]
            );
            return $row['plan_slug'] ?? 'free';
        } catch (\Exception $e) {
            return 'free';
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
