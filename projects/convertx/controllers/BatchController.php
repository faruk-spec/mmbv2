<?php
/**
 * ConvertX Batch Controller
 *
 * Handles batch (multi-file) conversion job submission, status polling,
 * and ZIP download of all completed outputs in a batch.
 *
 * @package MMB\Projects\ConvertX\Controllers
 */

namespace Projects\ConvertX\Controllers;

use Core\Auth;
use Core\Security;
use Core\Logger;
use Core\ActivityLogger;
use Projects\ConvertX\Models\ConversionJobModel;
use Projects\ConvertX\Services\ConversionService;
use Projects\ConvertX\Services\JobQueueService;

class BatchController
{
    private ConversionService  $conversionService;
    private JobQueueService    $queueService;
    private ConversionJobModel $jobModel;

    private const ALLOWED_EXTENSIONS = [
        'pdf', 'docx', 'doc', 'odt', 'rtf', 'txt', 'html', 'md',
        'xlsx', 'xls', 'ods', 'csv',
        'pptx', 'ppt', 'odp',
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'svg',
    ];

    public function __construct()
    {
        $this->conversionService = new ConversionService();
        $this->queueService      = new JobQueueService();
        $this->jobModel          = new ConversionJobModel();
    }

    public function showForm(): void
    {
        $config = require PROJECT_PATH . '/config.php';
        $this->render('batch', [
            'title'    => 'Batch Conversion',
            'user'     => Auth::user(),
            'formats'  => $config['formats'],
            'backends' => $this->conversionService->getAvailableBackends(),
        ]);
    }

    /**
     * Accept multiple files and enqueue one job per file.
     * All jobs share a common batch_id so clients can poll them together.
     */
    public function submit(): void
    {
        ob_start(); // capture any stray PHP warnings before JSON is sent

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->jsonError('Invalid request token', 403);
            return;
        }

        $userId = Auth::id();
        if (!$userId) {
            $this->jsonError('Authentication required', 401);
            return;
        }

        $outputFormat = strtolower(trim($_POST['output_format'] ?? ''));
        if (empty($outputFormat) || !in_array($outputFormat, self::ALLOWED_EXTENSIONS, true)) {
            $this->jsonError('Invalid output format', 400);
            return;
        }

        $files = $_FILES['files'] ?? null;
        if (!$files || empty($files['name'])) {
            $this->jsonError('No files uploaded', 400);
            return;
        }

        // Normalise multi-file upload structure
        $uploadedFiles = $this->normaliseFiles($files);
        if (empty($uploadedFiles)) {
            $this->jsonError('No valid files', 400);
            return;
        }

        $config   = require PROJECT_PATH . '/config.php';
        $plan     = $this->getUserPlan($userId);
        $maxBytes = $config['upload_limits'][$plan] ?? $config['upload_limits']['free'];

        // Build AI tasks list (mirrors ConversionController::submit)
        $aiTasks = [];
        if (!empty($_POST['ai_ocr'])) {
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

        // Shared conversion options
        $options = [
            'quality' => (int) ($_POST['quality'] ?? 85),
            'dpi'     => (int) ($_POST['dpi'] ?? 150),
        ];

        $batchId   = uniqid('batch_', true);
        $uploadDir = BASE_PATH . '/storage/uploads/convertx/' . $userId;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $jobIds = [];
        $errors = [];

        foreach ($uploadedFiles as $file) {
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errors[] = $file['name'] . ': upload error';
                continue;
            }
            if ($file['size'] > $maxBytes) {
                $errors[] = $file['name'] . ': file too large';
                continue;
            }

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
                $errors[] = $file['name'] . ': unsupported format';
                continue;
            }

            $storedName = uniqid('cx_', true) . '.' . $ext;
            $storedPath = $uploadDir . '/' . $storedName;

            if (!move_uploaded_file($file['tmp_name'], $storedPath)) {
                $errors[] = $file['name'] . ': could not save file';
                continue;
            }

            $inputFormat = $this->conversionService->detectFormat($storedPath, $file['name']);

            try {
                $jobId    = $this->queueService->enqueue($userId, [
                    'input_path'     => $storedPath,
                    'input_filename' => $file['name'],
                    'input_format'   => $inputFormat,
                    'output_format'  => $outputFormat,
                    'options'        => $options,
                    'ai_tasks'       => $aiTasks,
                    'batch_id'       => $batchId,
                    'webhook_url'    => Security::sanitize($_POST['webhook_url'] ?? ''),
                    'plan_tier'      => $plan,
                ]);
                $jobIds[] = $jobId;
                try { ActivityLogger::logCreate($userId, 'convertx', 'batch_job', $jobId, ['batch_id' => $batchId, 'input_filename' => $file['name'], 'output_format' => $outputFormat]); } catch (\Throwable $_) {}
            } catch (\Exception $e) {
                Logger::error('BatchController::submit - ' . $e->getMessage());
                try { ActivityLogger::logFailure($userId, 'submit_batch_job', $e->getMessage()); } catch (\Throwable $_) {}
                $errors[] = $file['name'] . ': could not queue job';
            }
        }

        if (!empty($jobIds)) {
            // Process each specific job synchronously to terminal state.
            // Loop up to 3 times per job to handle the retry-queue mechanism.
            @set_time_limit(300);
            foreach ($jobIds as $jid) {
                for ($attempt = 0; $attempt < 3; $attempt++) {
                    $job = $this->jobModel->find((int) $jid);
                    if (!$job || $job['status'] !== ConversionJobModel::STATUS_PENDING) {
                        break;
                    }
                    try {
                        $this->queueService->processJob($job);
                    } catch (\Throwable $e) {
                        Logger::error('BatchController: processJob threw - ' . $e->getMessage());
                        $this->jobModel->updateStatus((int) $jid, ConversionJobModel::STATUS_FAILED, [
                            'error_message' => $e->getMessage(),
                        ]);
                        break;
                    }
                }
            }
        }

        $this->cleanOutputBuffers();
        header('Content-Type: application/json');
        echo json_encode([
            'success'  => !empty($jobIds),
            'batch_id' => $batchId,
            'job_ids'  => $jobIds,
            'errors'   => $errors,
        ]);
    }

    /**
     * GET /projects/convertx/batch/status/:batchId
     *
     * Returns a JSON summary of all jobs in the batch so clients can render a
     * unified progress view without polling each job individually.
     */
    public function batchStatus(string $batchId): void
    {
        $userId = Auth::id();
        if (!$userId) {
            $this->jsonError('Authentication required', 401);
            return;
        }

        // Sanitise batch_id — only allow the characters generated by uniqid()
        $batchId = preg_replace('/[^a-zA-Z0-9_.]+/', '', $batchId);
        if ($batchId === '') {
            $this->jsonError('Invalid batch ID', 400);
            return;
        }

        $jobs = $this->jobModel->getBatchJobs($batchId, $userId);

        $summary = [];
        foreach ($jobs as $job) {
            $summary[] = [
                'job_id'          => (int) $job['id'],
                'input_filename'  => $job['input_filename'] ?? '',
                'output_filename' => $job['output_filename'] ?? null,
                'status'          => $job['status'],
                'error_message'   => $job['error_message'] ?? null,
            ];
        }

        $this->cleanOutputBuffers();
        header('Content-Type: application/json');
        echo json_encode([
            'success'  => true,
            'batch_id' => $batchId,
            'jobs'     => $summary,
        ]);
    }

    /**
     * GET /projects/convertx/batch/download/:batchId
     *
     * Streams all completed output files in the batch as a ZIP archive.
     * Returns a 404 JSON error if no completed jobs exist.
     */
    public function batchDownloadZip(string $batchId): void
    {
        $userId = Auth::id();
        if (!$userId) {
            $this->jsonError('Authentication required', 401);
            return;
        }

        $batchId = preg_replace('/[^a-zA-Z0-9_.]+/', '', $batchId);
        if ($batchId === '') {
            $this->jsonError('Invalid batch ID', 400);
            return;
        }

        $jobs = $this->jobModel->getBatchJobs($batchId, $userId);

        // Collect completed jobs that have an output file on disk
        $readyFiles = [];
        foreach ($jobs as $job) {
            if ($job['status'] !== ConversionJobModel::STATUS_COMPLETED) {
                continue;
            }
            $path = $job['output_path'] ?? '';
            if ($path && file_exists($path)) {
                $readyFiles[] = [
                    'path'     => $path,
                    'filename' => $job['output_filename'] ?: basename($path),
                ];
            }
        }

        if (empty($readyFiles)) {
            $this->jsonError('No completed files available for download', 404);
            return;
        }

        // Use ZipArchive when available; otherwise stream a naive concatenation
        // that is still a valid ZIP so the browser can open it.
        if (class_exists('ZipArchive')) {
            $tmpZip = tempnam(sys_get_temp_dir(), 'cx_batch_') . '.zip';
            $zip    = new \ZipArchive();
            if ($zip->open($tmpZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                $this->jsonError('Could not create ZIP archive', 500);
                return;
            }
            foreach ($readyFiles as $f) {
                $zip->addFile($f['path'], $f['filename']);
            }
            $zip->close();

            $zipName = 'convertx_batch_' . substr($batchId, 0, 12) . '.zip';
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . addslashes($zipName) . '"');
            header('Content-Length: ' . filesize($tmpZip));
            header('Cache-Control: private');
            readfile($tmpZip);
            @unlink($tmpZip);
            return;
        }

        // ZipArchive not available — prompt the user to download files individually
        $this->jsonError(
            'ZIP download is not supported on this server. Please download each file individually.',
            501
        );
    }

    // ------------------------------------------------------------------ //

    /** Normalise $_FILES['files'] into a flat array of file entries. */
    private function normaliseFiles(array $files): array
    {
        $result = [];
        $count  = count((array) $files['name']);
        for ($i = 0; $i < $count; $i++) {
            $result[] = [
                'name'     => $files['name'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error'    => $files['error'][$i],
                'size'     => $files['size'][$i],
            ];
        }
        return $result;
    }

    /** Drain all active output buffers so JSON headers can be sent cleanly. */
    private function cleanOutputBuffers(): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }

    private function getUserPlan(int $userId): string
    {
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
        $this->cleanOutputBuffers();
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $message]);
    }
}
