<?php
/**
 * Conversion Job Model
 *
 * Handles persistence for async conversion jobs.
 *
 * @package MMB\Projects\ConvertX\Models
 */

namespace Projects\ConvertX\Models;

use Core\Database;

class ConversionJobModel
{
    private Database $db;

    // Job status constants
    public const STATUS_PENDING    = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED  = 'completed';
    public const STATUS_FAILED     = 'failed';
    public const STATUS_CANCELLED  = 'cancelled';

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureSchema();
    }

    /**
     * Create the convertx_jobs and convertx_tool_jobs tables if they do not exist yet.
     * This lets the app work on first install without running schema.sql manually.
     */
    private function ensureSchema(): void
    {
        try {
            $this->db->query(
                "CREATE TABLE IF NOT EXISTS convertx_jobs (
                    id               INT AUTO_INCREMENT PRIMARY KEY,
                    user_id          INT          NOT NULL,
                    input_path       VARCHAR(512) NOT NULL DEFAULT '',
                    input_filename   VARCHAR(255) NOT NULL DEFAULT '',
                    input_format     VARCHAR(32)  NOT NULL DEFAULT '',
                    output_format    VARCHAR(32)  NOT NULL DEFAULT '',
                    output_path      VARCHAR(512)          DEFAULT NULL,
                    output_filename  VARCHAR(255)          DEFAULT NULL,
                    options          TEXT                  DEFAULT NULL,
                    ai_tasks         TEXT                  DEFAULT NULL,
                    ai_result        LONGTEXT              DEFAULT NULL,
                    webhook_url      VARCHAR(512)          DEFAULT NULL,
                    batch_id         VARCHAR(64)           DEFAULT NULL,
                    plan_tier        VARCHAR(32)  NOT NULL DEFAULT 'free',
                    status           VARCHAR(32)  NOT NULL DEFAULT 'pending',
                    error_message    TEXT                  DEFAULT NULL,
                    provider_used    VARCHAR(64)           DEFAULT NULL,
                    tokens_used      INT                   DEFAULT 0,
                    retry_count      INT          NOT NULL DEFAULT 0,
                    created_at       DATETIME     NOT NULL,
                    started_at       DATETIME              DEFAULT NULL,
                    updated_at       DATETIME              DEFAULT NULL,
                    completed_at     DATETIME              DEFAULT NULL,
                    INDEX idx_user_status (user_id, status),
                    INDEX idx_batch (batch_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
            );
        } catch (\Exception $e) {
            // Table may already exist or DB may be read-only — silently continue
        }
        try {
            $this->db->query(
                "CREATE TABLE IF NOT EXISTS convertx_tool_jobs (
                    id               INT AUTO_INCREMENT PRIMARY KEY,
                    user_id          INT          NOT NULL,
                    tool_type        VARCHAR(64)  NOT NULL DEFAULT '',
                    input_count      INT          NOT NULL DEFAULT 1,
                    input_filenames  TEXT                  DEFAULT NULL,
                    output_filename  VARCHAR(255)          DEFAULT NULL,
                    original_size    BIGINT       NOT NULL DEFAULT 0,
                    output_size      BIGINT       NOT NULL DEFAULT 0,
                    status           VARCHAR(32)  NOT NULL DEFAULT 'completed',
                    error_message    TEXT                  DEFAULT NULL,
                    created_at       DATETIME     NOT NULL,
                    INDEX idx_tool_user (user_id),
                    INDEX idx_tool_type (tool_type)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
            );
        } catch (\Exception $e) {
            // silently continue
        }
    }

    /**
     * Create a new conversion job.
     *
     * @param int    $userId
     * @param array  $data  Keys: input_path, input_format, output_format,
     *                      options (JSON), webhook_url, batch_id, ai_tasks (JSON)
     * @return int|false  New job ID or false on failure
     */
    public function create(int $userId, array $data)
    {
        $sql = "INSERT INTO convertx_jobs
                    (user_id, input_path, input_filename, input_format,
                     output_format, options, ai_tasks, webhook_url,
                     batch_id, plan_tier, status, created_at)
                VALUES
                    (:user_id, :input_path, :input_filename, :input_format,
                     :output_format, :options, :ai_tasks, :webhook_url,
                     :batch_id, :plan_tier, :status, NOW())";

        $this->db->query($sql, [
            'user_id'        => $userId,
            'input_path'     => $data['input_path']     ?? '',
            'input_filename' => $data['input_filename'] ?? '',
            'input_format'   => $data['input_format']   ?? '',
            'output_format'  => $data['output_format']  ?? '',
            'options'        => isset($data['options'])  ? json_encode($data['options'])  : '{}',
            'ai_tasks'       => isset($data['ai_tasks']) ? json_encode($data['ai_tasks']) : '[]',
            'webhook_url'    => $data['webhook_url']    ?? null,
            'batch_id'       => $data['batch_id']       ?? null,
            'plan_tier'      => $data['plan_tier']      ?? 'free',
            'status'         => self::STATUS_PENDING,
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Find a job by its ID.
     */
    public function find(int $id): ?array
    {
        $row = $this->db->fetch(
            "SELECT * FROM convertx_jobs WHERE id = :id LIMIT 1",
            ['id' => $id]
        );
        return $row ?: null;
    }

    /**
     * Find a job by ID scoped to a specific user.
     */
    public function findForUser(int $id, int $userId): ?array
    {
        $row = $this->db->fetch(
            "SELECT * FROM convertx_jobs WHERE id = :id AND user_id = :uid LIMIT 1",
            ['id' => $id, 'uid' => $userId]
        );
        return $row ?: null;
    }

    /**
     * Update job status and optional result fields.
     */
    public function updateStatus(int $id, string $status, array $extra = []): bool
    {
        $set  = ['status = :status', 'updated_at = NOW()'];
        $bind = ['id' => $id, 'status' => $status];

        if ($status === self::STATUS_PROCESSING) {
            $set[]          = 'started_at = NOW()';
        }
        if ($status === self::STATUS_COMPLETED || $status === self::STATUS_FAILED) {
            $set[]          = 'completed_at = NOW()';
        }
        foreach (['output_path', 'output_filename', 'error_message',
                  'ai_result', 'provider_used', 'tokens_used'] as $field) {
            if (array_key_exists($field, $extra)) {
                $set[]         = "$field = :$field";
                $bind[$field]  = $extra[$field];
            }
        }

        $sql = "UPDATE convertx_jobs SET " . implode(', ', $set) . " WHERE id = :id";
        $this->db->query($sql, $bind);
        return true;
    }

    /**
     * Increment retry counter and re-queue a failed job.
     */
    public function retry(int $id): bool
    {
        $this->db->query(
            "UPDATE convertx_jobs
             SET status = :status, retry_count = retry_count + 1, updated_at = NOW()
             WHERE id = :id",
            ['id' => $id, 'status' => self::STATUS_PENDING]
        );
        return true;
    }

    /**
     * Cancel a job that is still pending.
     */
    public function cancel(int $id, int $userId): bool
    {
        $this->db->query(
            "UPDATE convertx_jobs
             SET status = :status, updated_at = NOW()
             WHERE id = :id AND user_id = :uid AND status = :pending",
            [
                'id'      => $id,
                'uid'     => $userId,
                'status'  => self::STATUS_CANCELLED,
                'pending' => self::STATUS_PENDING,
            ]
        );
        return true;
    }

    /**
     * Paginated job history for a user with optional status filter.
     *
     * @param int    $userId
     * @param int    $page
     * @param int    $perPage
     * @param string $status  '' = all, or one of the STATUS_* constants
     */
    public function getHistory(int $userId, int $page = 1, int $perPage = 20, string $status = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $where  = 'WHERE user_id = :uid';
        $bind   = ['uid' => $userId];

        if ($status !== '') {
            $where        .= ' AND status = :status';
            $bind['status'] = $status;
        }

        // ── Conversion jobs ────────────────────────────────────────────────
        $convRows = $this->db->fetchAll(
            "SELECT
                 id,
                 'conversion'          AS job_source,
                 input_filename,
                 input_format,
                 output_format,
                 NULL                  AS tool_type,
                 NULL                  AS input_count,
                 NULL                  AS input_filenames,
                 output_filename,
                 NULL                  AS original_size,
                 NULL                  AS output_size,
                 ai_tasks,
                 status,
                 error_message,
                 created_at
             FROM convertx_jobs
             {$where}
             ORDER BY created_at DESC
             LIMIT :limit OFFSET :offset",
            array_merge($bind, ['limit' => $perPage, 'offset' => $offset])
        ) ?: [];

        $convTotal = (int)($this->db->fetch(
            "SELECT COUNT(*) AS cnt FROM convertx_jobs {$where}",
            $bind
        )['cnt'] ?? 0);

        // ── Tool jobs (synchronous PDF/image operations) ───────────────────
        // Status filter translates: 'completed' maps to 'completed', any other
        // active filter is incompatible with tool jobs (which are always
        // completed or failed), so we skip the tool table for pending/processing.
        $toolRows  = [];
        $toolTotal = 0;
        $skipToolStatus = $status !== '' && !in_array($status, [self::STATUS_COMPLETED, self::STATUS_FAILED], true);
        if (!$skipToolStatus) {
            $toolWhere = 'WHERE user_id = :uid';
            $toolBind  = ['uid' => $userId];
            if ($status !== '') {
                $toolWhere         .= ' AND status = :status';
                $toolBind['status'] = $status;
            }
            try {
                $toolRows = $this->db->fetchAll(
                    "SELECT
                         id,
                         'tool'                AS job_source,
                         input_filenames        AS input_filename,
                         NULL                  AS input_format,
                         NULL                  AS output_format,
                         tool_type,
                         input_count,
                         input_filenames,
                         output_filename,
                         original_size,
                         output_size,
                         NULL                  AS ai_tasks,
                         status,
                         error_message,
                         created_at
                     FROM convertx_tool_jobs
                     {$toolWhere}
                     ORDER BY created_at DESC
                     LIMIT :limit OFFSET :offset",
                    array_merge($toolBind, ['limit' => $perPage, 'offset' => $offset])
                ) ?: [];

                $toolTotal = (int)($this->db->fetch(
                    "SELECT COUNT(*) AS cnt FROM convertx_tool_jobs {$toolWhere}",
                    $toolBind
                )['cnt'] ?? 0);
            } catch (\Exception $_) {
                // Table may not exist yet on older installs
            }
        }

        // ── Merge and re-sort by created_at, paginate ──────────────────────
        $merged = array_merge($convRows, $toolRows);
        usort($merged, static function (array $a, array $b): int {
            return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
        });
        // Slice to the requested page (simple merge pagination)
        $merged = array_slice($merged, 0, $perPage);

        return [
            'jobs'     => $merged,
            'total'    => $convTotal + $toolTotal,
            'page'     => $page,
            'per_page' => $perPage,
        ];
    }

    /**
     * Record a completed (synchronous) image/PDF tool operation.
     *
     * @param int    $userId
     * @param string $toolType        e.g. 'img_compress', 'pdf_merge', 'img_crop'
     * @param int    $inputCount      number of input files
     * @param array  $inputFilenames  original filenames
     * @param string $outputFilename  final download filename
     * @param int    $originalSize    total bytes before processing
     * @param int    $outputSize      total bytes after processing
     * @param string $status          'completed' or 'failed'
     * @param string $errorMessage    non-empty only when failed
     * @return int|false  Inserted row ID or false
     */
    public function createToolJob(
        int    $userId,
        string $toolType,
        int    $inputCount,
        array  $inputFilenames,
        string $outputFilename,
        int    $originalSize = 0,
        int    $outputSize   = 0,
        string $status       = 'completed',
        string $errorMessage = ''
    ) {
        try {
            $this->db->query(
                "INSERT INTO convertx_tool_jobs
                     (user_id, tool_type, input_count, input_filenames,
                      output_filename, original_size, output_size,
                      status, error_message, created_at)
                 VALUES
                     (:uid, :tool, :cnt, :names,
                      :outname, :origsz, :outsz,
                      :status, :errmsg, NOW())",
                [
                    'uid'     => $userId,
                    'tool'    => $toolType,
                    'cnt'     => $inputCount,
                    'names'   => implode(', ', array_slice($inputFilenames, 0, 5))
                                . (count($inputFilenames) > 5 ? ' …' : ''),
                    'outname' => $outputFilename,
                    'origsz'  => $originalSize,
                    'outsz'   => $outputSize,
                    'status'  => in_array($status, ['completed','failed'], true) ? $status : 'completed',
                    'errmsg'  => $errorMessage,
                ]
            );
            $stmt = $this->db->query('SELECT LAST_INSERT_ID() AS lid');
            return (int)($stmt->fetch(\PDO::FETCH_ASSOC)['lid'] ?? 0) ?: false;
        } catch (\Exception $e) {
            return false;
        }
    }

    // Keep the original simple query for callers that only need conversion jobs
    public function getConversionHistory(int $userId, int $page = 1, int $perPage = 20, string $status = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $where  = 'WHERE user_id = :uid';
        $bind   = ['uid' => $userId];

        if ($status !== '') {
            $where        .= ' AND status = :status';
            $bind['status'] = $status;
        }

        $rows = $this->db->fetchAll(
            "SELECT * FROM convertx_jobs {$where}
             ORDER BY created_at DESC
             LIMIT :limit OFFSET :offset",
            array_merge($bind, ['limit' => $perPage, 'offset' => $offset])
        );

        $total = $this->db->fetch(
            "SELECT COUNT(*) AS cnt FROM convertx_jobs {$where}",
            $bind
        )['cnt'] ?? 0;

        return ['jobs' => $rows ?: [], 'total' => (int) $total, 'page' => $page, 'per_page' => $perPage];
    }

    /**
     * Jobs that are pending and have not exceeded max retries.
     */
    public function getPendingJobs(int $limit = 10, int $maxRetries = 3): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM convertx_jobs
             WHERE status = :status AND retry_count < :max
             ORDER BY created_at ASC
             LIMIT :limit",
            ['status' => self::STATUS_PENDING, 'max' => $maxRetries, 'limit' => $limit]
        ) ?: [];
    }

    /**
     * Return all jobs belonging to a batch, scoped to a user.
     *
     * @param string $batchId  The batch_id token generated at submission time
     * @param int    $userId   Owner user ID (security scope)
     * @return array  Flat array of job rows, ordered by created_at ASC
     */
    public function getBatchJobs(string $batchId, int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM convertx_jobs
             WHERE batch_id = :bid AND user_id = :uid
             ORDER BY created_at ASC",
            ['bid' => $batchId, 'uid' => $userId]
        ) ?: [];
    }

    /**
     * Usage statistics for a user in the current month.
     */
    public function getMonthlyUsage(int $userId): array
    {
        $row = $this->db->fetch(
            "SELECT
                COUNT(*) AS total_jobs,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed,
                SUM(CASE WHEN status = 'failed'    THEN 1 ELSE 0 END) AS failed,
                COALESCE(SUM(tokens_used), 0) AS tokens_used
             FROM convertx_jobs
             WHERE user_id = :uid
               AND YEAR(created_at)  = YEAR(NOW())
               AND MONTH(created_at) = MONTH(NOW())",
            ['uid' => $userId]
        );
        return $row ?: ['total_jobs' => 0, 'completed' => 0, 'failed' => 0, 'tokens_used' => 0];
    }

    /**
     * Breakdown of output formats for a user (for analytics).
     *
     * @return array  e.g. [['output_format' => 'pdf', 'cnt' => 12], ...]
     */
    public function getFormatBreakdown(int $userId, int $limit = 8): array
    {
        return $this->db->fetchAll(
            "SELECT output_format, COUNT(*) AS cnt
             FROM convertx_jobs
             WHERE user_id = :uid AND status = 'completed'
             GROUP BY output_format
             ORDER BY cnt DESC
             LIMIT :limit",
            ['uid' => $userId, 'limit' => $limit]
        ) ?: [];
    }

    /**
     * Daily job counts for the last N days (for the analytics sparkline).
     *
     * @return array  e.g. [['day' => '2026-02-20', 'cnt' => 5], ...]
     */
    public function getDailyActivity(int $userId, int $days = 14): array
    {
        return $this->db->fetchAll(
            "SELECT DATE(created_at) AS day, COUNT(*) AS cnt
             FROM convertx_jobs
             WHERE user_id = :uid
               AND created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
             GROUP BY DATE(created_at)
             ORDER BY day ASC",
            ['uid' => $userId, 'days' => $days]
        ) ?: [];
    }


    public function deleteExpired(int $userId, int $ttlSeconds): int
    {
        $threshold = date('Y-m-d H:i:s', time() - $ttlSeconds);
        $result    = $this->db->query(
            "DELETE FROM convertx_jobs
             WHERE user_id = :uid
               AND status IN ('completed', 'cancelled')
               AND created_at < :threshold",
            ['uid' => $userId, 'threshold' => $threshold]
        );
        return $result ? (int) $result->rowCount() : 0;
    }
}
