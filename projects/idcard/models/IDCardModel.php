<?php
/**
 * IDCard Model
 *
 * @package MMB\Projects\IDCard\Models
 */

namespace Projects\IDCard\Models;

use Core\Database;
use Core\Logger;

class IDCardModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureTables();
    }

    // ------------------------------------------------------------------ //
    //  Schema bootstrap                                                    //
    // ------------------------------------------------------------------ //

    private function ensureTables(): void
    {
        try {
            $this->db->query(
                "CREATE TABLE IF NOT EXISTS `idcard_bulk_jobs` (
                    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `user_id`       INT UNSIGNED NOT NULL,
                    `template_key`  VARCHAR(50)  NOT NULL DEFAULT 'corporate',
                    `total_rows`    INT UNSIGNED NOT NULL DEFAULT 0,
                    `completed`     INT UNSIGNED NOT NULL DEFAULT 0,
                    `failed`        INT UNSIGNED NOT NULL DEFAULT 0,
                    `status`        ENUM('pending','processing','done','error') DEFAULT 'pending',
                    `created_at`    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
                    `updated_at`    TIMESTAMP    NULL ON UPDATE CURRENT_TIMESTAMP,
                    INDEX `idx_ibj_user`    (`user_id`),
                    INDEX `idx_ibj_status`  (`status`),
                    INDEX `idx_ibj_created` (`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
            $this->db->query(
                "CREATE TABLE IF NOT EXISTS `idcard_cards` (
                    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `user_id`        INT UNSIGNED NOT NULL,
                    `template_key`   VARCHAR(50)  NOT NULL DEFAULT 'corporate',
                    `card_number`    VARCHAR(50)  NOT NULL,
                    `card_data`      JSON         NOT NULL,
                    `design`         JSON         NULL,
                    `photo_path`     VARCHAR(500) NULL,
                    `logo_path`      VARCHAR(500) NULL,
                    `ai_prompt`      TEXT         NULL,
                    `ai_suggestions` JSON         NULL,
                    `status`         ENUM('draft','generated') DEFAULT 'generated',
                    `created_at`     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
                    `updated_at`     TIMESTAMP    NULL ON UPDATE CURRENT_TIMESTAMP,
                    INDEX `idx_ic_user`     (`user_id`),
                    INDEX `idx_ic_tpl`      (`template_key`),
                    INDEX `idx_ic_created`  (`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
            $this->db->query(
                "CREATE TABLE IF NOT EXISTS `idcard_settings` (
                    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `setting_key`   VARCHAR(100) NOT NULL UNIQUE,
                    `setting_value` TEXT         NULL,
                    `created_at`    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
                    `updated_at`    TIMESTAMP    NULL ON UPDATE CURRENT_TIMESTAMP,
                    INDEX `idx_ics_key` (`setting_key`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
            // Migrate: add bulk_job_id column if it doesn't exist yet
            $colCheck = $this->db->fetch(
                "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                  WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME   = 'idcard_cards'
                    AND COLUMN_NAME  = 'bulk_job_id'"
            );
            if (!$colCheck) {
                $this->db->query(
                    "ALTER TABLE `idcard_cards` ADD COLUMN `bulk_job_id` INT UNSIGNED NULL,
                     ADD INDEX `idx_ic_bulk` (`bulk_job_id`)"
                );
            }
            // Migrate: add ai_card_html column for AI-generated card designs
            $htmlColCheck = $this->db->fetch(
                "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                  WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME   = 'idcard_cards'
                    AND COLUMN_NAME  = 'ai_card_html'"
            );
            if (!$htmlColCheck) {
                $this->db->query(
                    "ALTER TABLE `idcard_cards` ADD COLUMN `ai_card_html` MEDIUMTEXT NULL"
                );
            }
        } catch (\Exception $e) {
            Logger::error('IDCard ensureTables: ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Card CRUD                                                           //
    // ------------------------------------------------------------------ //

    /**
     * Create a new ID card record and return its ID.
     */
    public function create(array $data): int
    {
        $this->db->query(
            "INSERT INTO idcard_cards
             (user_id, template_key, card_number, card_data, design, photo_path, logo_path, ai_prompt, ai_suggestions, ai_card_html, status, bulk_job_id)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?)",
            [
                $data['user_id'],
                $data['template_key']   ?? 'corporate',
                $data['card_number']    ?? $this->generateCardNumber(),
                json_encode($data['card_data']      ?? []),
                json_encode($data['design']         ?? []),
                $data['photo_path']     ?? null,
                $data['logo_path']      ?? null,
                $data['ai_prompt']      ?? null,
                json_encode($data['ai_suggestions'] ?? []),
                $data['ai_card_html']   ?? null,
                $data['status']         ?? 'generated',
                $data['bulk_job_id']    ?? null,
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    /**
     * Find a single card by ID (and optionally restrict to a user).
     */
    public function findById(int $id, ?int $userId = null): ?array
    {
        $sql    = "SELECT * FROM idcard_cards WHERE id = ?";
        $params = [$id];
        if ($userId !== null) {
            $sql    .= " AND user_id = ?";
            $params[] = $userId;
        }
        $row = $this->db->fetch($sql, $params);
        return $row ? $this->decode($row) : null;
    }

    /**
     * Return paginated cards for a user (newest first).
     */
    public function getByUser(int $userId, int $limit = 20, int $offset = 0): array
    {
        $rows = $this->db->fetchAll(
            "SELECT * FROM idcard_cards WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$userId, $limit, $offset]
        );
        return array_map([$this, 'decode'], $rows ?: []);
    }

    /**
     * Return paginated cards generated via bulk jobs (bulk_job_id IS NOT NULL), newest first.
     */
    public function getByUserBulk(int $userId, int $limit = 20, int $offset = 0): array
    {
        $rows = $this->db->fetchAll(
            "SELECT * FROM idcard_cards WHERE user_id = ? AND bulk_job_id IS NOT NULL ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$userId, $limit, $offset]
        );
        return array_map([$this, 'decode'], $rows ?: []);
    }

    /**
     * Count total cards for a user.
     */
    public function countByUser(int $userId): int
    {
        $row = $this->db->fetch(
            "SELECT COUNT(*) c FROM idcard_cards WHERE user_id = ?",
            [$userId]
        );
        return (int) ($row['c'] ?? 0);
    }

    /**
     * Count bulk-generated cards for a user.
     */
    public function countByUserBulk(int $userId): int
    {
        $row = $this->db->fetch(
            "SELECT COUNT(*) c FROM idcard_cards WHERE user_id = ? AND bulk_job_id IS NOT NULL",
            [$userId]
        );
        return (int) ($row['c'] ?? 0);
    }

    /**
     * Delete a card (only if it belongs to the user).
     */
    public function delete(int $id, int $userId): bool
    {
        $card = $this->findById($id, $userId);
        if (!$card) {
            return false;
        }
        // Remove uploaded files if present
        $this->removeFile($card['photo_path'] ?? '');
        $this->removeFile($card['logo_path']  ?? '');

        $this->db->query(
            "DELETE FROM idcard_cards WHERE id = ? AND user_id = ?",
            [$id, $userId]
        );
        return true;
    }


    /**
     * Update an existing card (only if it belongs to the user).
     */
    public function update(int $id, int $userId, array $data): bool
    {
        $card = $this->findById($id, $userId);
        if (!$card) {
            return false;
        }

        $sets   = [];
        $params = [];

        if (isset($data['template_key'])) {
            $sets[]   = 'template_key = ?';
            $params[] = $data['template_key'];
        }
        if (isset($data['card_data'])) {
            $sets[]   = 'card_data = ?';
            $params[] = json_encode($data['card_data']);
        }
        if (isset($data['design'])) {
            $sets[]   = 'design = ?';
            $params[] = json_encode($data['design']);
        }
        if (array_key_exists('photo_path', $data)) {
            $sets[]   = 'photo_path = ?';
            $params[] = $data['photo_path'];
        }
        if (array_key_exists('logo_path', $data)) {
            $sets[]   = 'logo_path = ?';
            $params[] = $data['logo_path'];
        }
        if (isset($data['ai_prompt'])) {
            $sets[]   = 'ai_prompt = ?';
            $params[] = $data['ai_prompt'];
        }
        if (isset($data['ai_suggestions'])) {
            $sets[]   = 'ai_suggestions = ?';
            $params[] = json_encode($data['ai_suggestions']);
        }
        if (array_key_exists('ai_card_html', $data)) {
            $sets[]   = 'ai_card_html = ?';
            $params[] = $data['ai_card_html'];
        }

        if (empty($sets)) {
            // No fields to update — treat as no-op success
            return true;
        }

        $params[] = $id;
        $params[] = $userId;
        $this->db->query(
            "UPDATE idcard_cards SET " . implode(', ', $sets) . " WHERE id = ? AND user_id = ?",
            $params
        );
        return true;
    }

    // ------------------------------------------------------------------ //
    //  Admin helpers                                                       //
    // ------------------------------------------------------------------ //

    public function countAll(): int
    {
        $row = $this->db->fetch("SELECT COUNT(*) c FROM idcard_cards");
        return (int) ($row['c'] ?? 0);
    }

    public function countToday(): int
    {
        $row = $this->db->fetch(
            "SELECT COUNT(*) c FROM idcard_cards WHERE DATE(created_at) = CURDATE()"
        );
        return (int) ($row['c'] ?? 0);
    }

    public function countThisMonth(): int
    {
        $row = $this->db->fetch(
            "SELECT COUNT(*) c FROM idcard_cards
             WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())"
        );
        return (int) ($row['c'] ?? 0);
    }

    public function countActiveUsers(): int
    {
        $row = $this->db->fetch(
            "SELECT COUNT(DISTINCT user_id) c FROM idcard_cards
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
        );
        return (int) ($row['c'] ?? 0);
    }

    public function getRecentAll(int $limit = 20): array
    {
        $rows = $this->db->fetchAll(
            "SELECT c.*, u.name user_name, u.email user_email
             FROM idcard_cards c
             LEFT JOIN users u ON u.id = c.user_id
             ORDER BY c.created_at DESC LIMIT ?",
            [$limit]
        );
        return array_map([$this, 'decode'], $rows ?: []);
    }

    /**
     * Paginated search for admin.
     */
    public function searchAdmin(array $filters, int $limit, int $offset): array
    {
        [$where, $params] = $this->buildAdminWhere($filters);
        $rows = $this->db->fetchAll(
            "SELECT c.*, u.name user_name, u.email user_email
             FROM idcard_cards c
             LEFT JOIN users u ON u.id = c.user_id
             {$where}
             ORDER BY c.created_at DESC LIMIT ? OFFSET ?",
            array_merge($params, [$limit, $offset])
        );
        return array_map([$this, 'decode'], $rows ?: []);
    }

    public function countSearch(array $filters): int
    {
        [$where, $params] = $this->buildAdminWhere($filters);
        $row = $this->db->fetch(
            "SELECT COUNT(*) c FROM idcard_cards c
             LEFT JOIN users u ON u.id = c.user_id {$where}",
            $params
        );
        return (int) ($row['c'] ?? 0);
    }

    private function buildAdminWhere(array $f): array
    {
        $conditions = [];
        $params     = [];

        if (!empty($f['template'])) {
            $conditions[] = "c.template_key = ?";
            $params[]     = $f['template'];
        }
        if (!empty($f['search'])) {
            $conditions[] = "(u.name LIKE ? OR u.email LIKE ? OR c.card_number LIKE ?)";
            $like = '%' . $f['search'] . '%';
            $params = array_merge($params, [$like, $like, $like]);
        }
        if (!empty($f['date_from'])) {
            $conditions[] = "DATE(c.created_at) >= ?";
            $params[]     = $f['date_from'];
        }
        if (!empty($f['date_to'])) {
            $conditions[] = "DATE(c.created_at) <= ?";
            $params[]     = $f['date_to'];
        }

        $where = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';
        return [$where, $params];
    }

    // ------------------------------------------------------------------ //
    //  Settings                                                            //
    // ------------------------------------------------------------------ //

    public function getSetting(string $key, mixed $default = null): mixed
    {
        try {
            $row = $this->db->fetch(
                "SELECT setting_value FROM idcard_settings WHERE setting_key = ?",
                [$key]
            );
            if ($row && $row['setting_value'] !== null) {
                $val = $row['setting_value'];
                // Only JSON-decode arrays/objects; return plain strings as-is so that
                // values like '1' / '0' stay strings and === comparisons work correctly.
                if (strlen($val) > 0 && ($val[0] === '{' || $val[0] === '[')) {
                    $decoded = json_decode($val, true);
                    if ($decoded !== null) {
                        return $decoded;
                    }
                }
                return $val;
            }
        } catch (\Exception $e) {
            // table may not exist yet
        }
        return $default;
    }

    public function setSetting(string $key, mixed $value): void
    {
        $encoded = is_string($value) ? $value : json_encode($value);
        $this->db->query(
            "INSERT INTO idcard_settings (setting_key, setting_value)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()",
            [$key, $encoded]
        );
    }

    // ------------------------------------------------------------------ //
    //  Bulk Job CRUD                                                       //
    // ------------------------------------------------------------------ //

    public function createBulkJob(int $userId, string $templateKey, int $totalRows): int
    {
        $this->db->query(
            "INSERT INTO idcard_bulk_jobs (user_id, template_key, total_rows, status)
             VALUES (?, ?, ?, 'pending')",
            [$userId, $templateKey, $totalRows]
        );
        return (int) $this->db->lastInsertId();
    }

    public function updateBulkJob(int $jobId, int $completed, int $failed, string $status = 'done'): void
    {
        $this->db->query(
            "UPDATE idcard_bulk_jobs SET completed = ?, failed = ?, status = ? WHERE id = ?",
            [$completed, $failed, $status, $jobId]
        );
    }

    public function getBulkJobsByUser(int $userId, int $limit = 20): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM idcard_bulk_jobs WHERE user_id = ? ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        ) ?: [];
    }

    public function getAllBulkJobs(int $limit = 50, int $offset = 0): array
    {
        return $this->db->fetchAll(
            "SELECT j.*, u.name user_name, u.email user_email
             FROM idcard_bulk_jobs j
             LEFT JOIN users u ON u.id = j.user_id
             ORDER BY j.created_at DESC LIMIT ? OFFSET ?",
            [$limit, $offset]
        ) ?: [];
    }

    public function countAllBulkJobs(): int
    {
        $row = $this->db->fetch("SELECT COUNT(*) c FROM idcard_bulk_jobs");
        return (int) ($row['c'] ?? 0);
    }

    public function countBulkJobsToday(): int
    {
        $row = $this->db->fetch(
            "SELECT COUNT(*) c FROM idcard_bulk_jobs WHERE DATE(created_at) = CURDATE()"
        );
        return (int) ($row['c'] ?? 0);
    }

    public function sumBulkCardsGenerated(): int
    {
        $row = $this->db->fetch("SELECT COALESCE(SUM(completed), 0) c FROM idcard_bulk_jobs");
        return (int) ($row['c'] ?? 0);
    }

    // ------------------------------------------------------------------ //
    //  Helpers                                                             //
    // ------------------------------------------------------------------ //

    private function decode(array $row): array
    {
        foreach (['card_data', 'design', 'ai_suggestions'] as $col) {
            if (isset($row[$col]) && is_string($row[$col])) {
                $row[$col] = json_decode($row[$col], true) ?: [];
            }
        }
        return $row;
    }

    private function removeFile(string $path): void
    {
        if (!$path) {
            return;
        }
        $base = realpath(BASE_PATH);
        $full = realpath(BASE_PATH . '/' . ltrim($path, '/'));
        if ($full && $base && strncmp($full, $base . DIRECTORY_SEPARATOR, strlen($base) + 1) === 0) {
            unlink($full);
        }
    }

    private function generateCardNumber(): string
    {
        return 'CX-' . strtoupper(bin2hex(random_bytes(4)));
    }
}
