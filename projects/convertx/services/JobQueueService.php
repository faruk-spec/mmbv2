<?php
/**
 * Job Queue Service
 *
 * Manages the async conversion job lifecycle:
 *   - Enqueue new jobs
 *   - Process pending jobs (called by cron / worker script)
 *   - Retry failed jobs
 *   - Fire webhook callbacks on completion
 *
 * @package MMB\Projects\ConvertX\Services
 */

namespace Projects\ConvertX\Services;

use Core\Logger;
use Projects\ConvertX\Models\ConversionJobModel;

class JobQueueService
{
    private ConversionJobModel $jobModel;
    private ConversionService  $conversionService;
    private AIService          $aiService;

    /** Maximum retries per job (matches config.php queue.max_retries) */
    private int $maxRetries;

    public function __construct()
    {
        $this->jobModel          = new ConversionJobModel();
        $this->conversionService = new ConversionService();
        $this->aiService         = new AIService();

        $config            = require PROJECT_PATH . '/config.php';
        $this->maxRetries  = (int) ($config['queue']['max_retries'] ?? 3);
    }

    /**
     * Enqueue a new conversion job and return the job ID.
     *
     * @param int   $userId
     * @param array $data   See ConversionJobModel::create() for expected keys
     * @return int  New job ID
     * @throws \RuntimeException on persistence failure
     */
    public function enqueue(int $userId, array $data): int
    {
        $jobId = $this->jobModel->create($userId, $data);
        if (!$jobId) {
            throw new \RuntimeException('Failed to create conversion job');
        }
        Logger::info("ConvertX: enqueued job #{$jobId} for user #{$userId}");
        return (int) $jobId;
    }

    /**
     * Process up to $batchSize pending jobs.
     *
     * Called by cron job or dedicated worker. Safe to call concurrently as
     * each job is immediately moved to PROCESSING status before work begins.
     *
     * @param int $batchSize
     * @return int Number of jobs processed
     */
    public function processPending(int $batchSize = 5): int
    {
        $jobs      = $this->jobModel->getPendingJobs($batchSize, $this->maxRetries);
        $processed = 0;

        foreach ($jobs as $job) {
            $this->processJob($job);
            $processed++;
        }

        return $processed;
    }

    /**
     * Process a single job end-to-end.
     */
    public function processJob(array $job): void
    {
        $jobId = (int) $job['id'];

        try {
            // Claim the job (inside try-catch so a column issue doesn't leave job as 'pending')
            $this->jobModel->updateStatus($jobId, ConversionJobModel::STATUS_PROCESSING);
            // 1. Perform core file conversion
            $convResult = $this->conversionService->convert(
                $job['input_path'],
                $job['input_format'],
                $job['output_format'],
                json_decode($job['options'] ?? '{}', true)
            );

            if (!$convResult['success']) {
                $this->fail($job, $convResult['error']);
                return;
            }

            $outputPath = $convResult['output_path'];
            $aiResult   = [];

            // 2. Run AI tasks if requested
            $aiTasks = json_decode($job['ai_tasks'] ?? '[]', true);
            if (!empty($aiTasks)) {
                $aiResult = $this->runAITasks($job, $outputPath, $aiTasks, $job['input_path'] ?? '');
            }

            // 3. Mark job as completed
            $this->jobModel->updateStatus($jobId, ConversionJobModel::STATUS_COMPLETED, [
                'output_path'     => $outputPath,
                'output_filename' => basename($outputPath),
                // json_encode can return false for non-UTF8 content; use null fallback
                // to ensure we never store an empty string in a JSON/LONGTEXT column.
                'ai_result'       => !empty($aiResult) ? (json_encode($aiResult) ?: null) : null,
            ]);

            Logger::info("ConvertX: job #{$jobId} completed");

            // 4. Fire webhook if configured
            if (!empty($job['webhook_url'])) {
                $this->fireWebhook($job['webhook_url'], $jobId, ConversionJobModel::STATUS_COMPLETED, $aiResult);
            }

        } catch (\Exception $e) {
            $this->fail($job, $e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  AI task orchestration                                               //
    // ------------------------------------------------------------------ //

    /**
     * Run the requested AI tasks on the converted output.
     *
     * @param array  $job
     * @param string $outputPath  Path to the converted file
     * @param array  $tasks       e.g. ['ocr', 'summarize', 'translate:fr']
     * @param string $inputPath   Original uploaded file path (used for OCR so we scan the source)
     * @return array  Keyed results from each task
     */
    private function runAITasks(array $job, string $outputPath, array $tasks, string $inputPath = ''): array
    {
        $planTier = $job['plan_tier'] ?? 'free';
        $results  = [];
        $text     = '';

        foreach ($tasks as $task) {
            [$taskName, $taskParam] = array_pad(explode(':', $task, 2), 2, null);

            switch ($taskName) {
                case 'ocr':
                    // OCR the original uploaded input (scanned image/PDF), not the
                    // converted output — the output may be a binary DOCX which cannot
                    // be OCR'd meaningfully.
                    $ocrTarget = ($inputPath && file_exists($inputPath)) ? $inputPath : $outputPath;
                    $res  = $this->aiService->ocr($ocrTarget, $planTier);
                    $text = $res['text'] ?? '';
                    $results['ocr'] = $res;
                    break;

                case 'summarize':
                    // Use OCR text if available, otherwise extract text from the output file
                    $srcText = $text ?: $this->readTextContent($outputPath);
                    $results['summarize'] = $this->aiService->summarize($srcText, $planTier);
                    break;

                case 'translate':
                    $srcText = $text ?: $this->readTextContent($outputPath);
                    $results['translate'] = $this->aiService->translate($srcText, $taskParam ?? 'en', $planTier);
                    break;

                case 'classify':
                    $srcText = $text ?: $this->readTextContent($outputPath);
                    $results['classify'] = $this->aiService->classify($srcText, $planTier);
                    break;
            }
        }

        return $results;
    }

    private function readTextContent(string $path): string
    {
        if (!file_exists($path)) {
            return '';
        }
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        // Plain text / markup — read directly
        if (in_array($ext, ['txt', 'csv', 'md', 'rst', 'text'], true)) {
            return (string) file_get_contents($path);
        }
        if (in_array($ext, ['html', 'htm', 'xml'], true)) {
            return strip_tags((string) file_get_contents($path));
        }

        // PDF — pdftotext (poppler-utils) is fast and accurate for digital PDFs
        if ($ext === 'pdf') {
            $pdftotext = trim((string) shell_exec('which pdftotext 2>/dev/null'));
            if ($pdftotext) {
                $tmp = tempnam(sys_get_temp_dir(), 'cx_ptt_');
                exec(escapeshellarg($pdftotext) . ' ' . escapeshellarg($path)
                     . ' ' . escapeshellarg($tmp) . ' 2>/dev/null', $_, $code);
                if ($code === 0 && file_exists($tmp)) {
                    $text = (string) file_get_contents($tmp);
                    @unlink($tmp);
                    if (!empty(trim($text))) {
                        return $text;
                    }
                }
            }
        }

        // Office / document formats — LibreOffice --cat extracts plain text
        $loFormats = ['docx', 'doc', 'odt', 'rtf', 'xlsx', 'xls', 'ods', 'pptx', 'ppt', 'odp', 'pdf'];
        if (in_array($ext, $loFormats, true)) {
            $lo = trim((string) shell_exec('which libreoffice 2>/dev/null'))
               ?: trim((string) shell_exec('which soffice 2>/dev/null'));
            if ($lo) {
                $pid = getmypid();
                $cmd = 'DISPLAY= HOME=/tmp ' . escapeshellarg($lo) . ' --headless --cat '
                     . '-env:UserInstallation=file:///tmp/lo-' . $pid . ' '
                     . escapeshellarg($path) . ' 2>/dev/null';
                exec($cmd, $lines, $code);
                $text = trim(implode("\n", $lines));
                if (!empty($text)) {
                    return $text;
                }
            }
        }

        // Fallback: read raw and strip non-printable bytes.
        // Keep: \x09 (tab), \x0A (LF), \x0D (CR), \x20-\x7E (printable ASCII),
        //        \xA0-\xFF (Latin-1 / extended ASCII).
        $content = file_get_contents($path);
        if ($content === false) {
            return '';
        }
        return preg_replace('/[^\x09\x0A\x0D\x20-\x7E\xA0-\xFF]/', '', $content);
    }

    // ------------------------------------------------------------------ //
    //  Failure handling                                                     //
    // ------------------------------------------------------------------ //

    private function fail(array $job, string $reason): void
    {
        $jobId = (int) $job['id'];
        Logger::error("ConvertX: job #{$jobId} failed - {$reason}");

        if ((int) ($job['retry_count'] ?? 0) < $this->maxRetries - 1) {
            $this->jobModel->retry($jobId);
        } else {
            $this->jobModel->updateStatus($jobId, ConversionJobModel::STATUS_FAILED, [
                'error_message' => $reason,
            ]);

            if (!empty($job['webhook_url'])) {
                $this->fireWebhook($job['webhook_url'], $jobId, ConversionJobModel::STATUS_FAILED, [
                    'error' => $reason,
                ]);
            }
        }
    }

    // ------------------------------------------------------------------ //
    //  Webhook delivery                                                     //
    // ------------------------------------------------------------------ //

    /**
     * Send an HTTP POST callback to the client's webhook URL.
     *
     * Payload:
     * {
     *   "job_id":   123,
     *   "status":   "completed",
     *   "event":    "job.completed",
     *   "data":     { ... },
     *   "timestamp": "2026-01-01T00:00:00Z"
     * }
     */
    private function fireWebhook(string $url, int $jobId, string $status, array $data = []): void
    {
        $payload = json_encode([
            'job_id'    => $jobId,
            'status'    => $status,
            'event'     => 'job.' . $status,
            'data'      => $data,
            'timestamp' => gmdate('c'),
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 10,
        ]);
        $response = curl_exec($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code < 200 || $code >= 300) {
            Logger::warning("ConvertX: webhook for job #{$jobId} returned HTTP {$code}");
        }
    }
}
