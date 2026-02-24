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
     * Create the convertx_jobs table if it does not exist yet.
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
