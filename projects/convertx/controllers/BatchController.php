<?php
/**
 * ConvertX Batch Controller
 *
 * Handles batch (multi-file) conversion job submission.
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

class BatchController
{
    private ConversionService $conversionService;
    private JobQueueService   $queueService;
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

        $batchId  = uniqid('batch_', true);
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
                    'batch_id'       => $batchId,
                    'webhook_url'    => Security::sanitize($_POST['webhook_url'] ?? ''),
                    'plan_tier'      => $plan,
                ]);
                $jobIds[] = $jobId;
            } catch (\Exception $e) {
                Logger::error('BatchController::submit - ' . $e->getMessage());
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

        header('Content-Type: application/json');
        echo json_encode([
            'success'  => !empty($jobIds),
            'batch_id' => $batchId,
            'job_ids'  => $jobIds,
            'errors'   => $errors,
        ]);
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
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $message]);
    }
}
