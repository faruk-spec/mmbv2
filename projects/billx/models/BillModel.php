<?php
/**
 * BillX Bill Model
 *
 * @package MMB\Projects\BillX\Models
 */

namespace Projects\BillX\Models;

use Core\Database;

class BillModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureTable();
    }

    private function ensureTable(): void
    {
        try {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `billx_bills` (
                    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `user_id` INT UNSIGNED NOT NULL,
                    `bill_type` VARCHAR(50) NOT NULL,
                    `bill_number` VARCHAR(50) NOT NULL,
                    `bill_date` DATE NOT NULL,
                    `from_name` VARCHAR(255) NOT NULL DEFAULT '',
                    `from_address` TEXT NULL,
                    `from_phone` VARCHAR(50) NULL,
                    `from_email` VARCHAR(255) NULL,
                    `to_name` VARCHAR(255) NOT NULL DEFAULT '',
                    `to_address` TEXT NULL,
                    `to_phone` VARCHAR(50) NULL,
                    `to_email` VARCHAR(255) NULL,
                    `items` JSON NOT NULL,
                    `subtotal` DECIMAL(12,2) DEFAULT 0.00,
                    `tax_percent` DECIMAL(5,2) DEFAULT 0.00,
                    `tax_amount` DECIMAL(12,2) DEFAULT 0.00,
                    `discount_amount` DECIMAL(12,2) DEFAULT 0.00,
                    `total_amount` DECIMAL(12,2) DEFAULT 0.00,
                    `notes` TEXT NULL,
                    `currency` VARCHAR(10) DEFAULT 'INR',
                    `template_data` JSON NULL,
                    `status` ENUM('draft','generated') DEFAULT 'generated',
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
                    INDEX `idx_billx_user_id` (`user_id`),
                    INDEX `idx_billx_bill_type` (`bill_type`),
                    INDEX `idx_billx_created_at` (`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (\Exception $e) {
            // Table already exists or DB error — safe to ignore
        }
    }

    public function create(array $data): int
    {
        return (int)$this->db->insert('billx_bills', $data);
    }

    public function getById(int $id): ?array
    {
        return $this->db->fetch("SELECT * FROM billx_bills WHERE id = ?", [$id]) ?: null;
    }

    public function getByUser(int $userId, int $limit = 50, int $offset = 0): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM billx_bills WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$userId, $limit, $offset]
        ) ?: [];
    }

    public function countByUser(int $userId): int
    {
        $r = $this->db->fetch("SELECT COUNT(*) as c FROM billx_bills WHERE user_id = ?", [$userId]);
        return (int)($r['c'] ?? 0);
    }

    public function delete(int $id, int $userId): bool
    {
        return (bool)$this->db->query(
            "DELETE FROM billx_bills WHERE id = ? AND user_id = ?",
            [$id, $userId]
        );
    }

    public function getAll(int $limit = 100, int $offset = 0): array
    {
        return $this->db->fetchAll(
            "SELECT b.*, u.name as user_name, u.email as user_email
             FROM billx_bills b
             LEFT JOIN users u ON b.user_id = u.id
             ORDER BY b.created_at DESC LIMIT ? OFFSET ?",
            [$limit, $offset]
        ) ?: [];
    }

    public function countAll(): int
    {
        $r = $this->db->fetch("SELECT COUNT(*) as c FROM billx_bills");
        return (int)($r['c'] ?? 0);
    }

    public function getStats(): array
    {
        $r = $this->db->fetch(
            "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) AS today,
                SUM(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN 1 ELSE 0 END) AS this_month
             FROM billx_bills"
        );
        return $r ?: ['total' => 0, 'today' => 0, 'this_month' => 0];
    }

    public function adminDelete(int $id): bool
    {
        return (bool)$this->db->query("DELETE FROM billx_bills WHERE id = ?", [$id]);
    }
}
