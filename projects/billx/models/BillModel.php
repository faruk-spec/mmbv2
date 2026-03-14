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

    public function adminDeleteMultiple(array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }
        $ids = array_filter(array_map('intval', $ids), fn($id) => $id > 0);
        if (empty($ids)) {
            return 0;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->query(
            "DELETE FROM billx_bills WHERE id IN ($placeholders)",
            array_values($ids)
        );
        return $stmt->rowCount();
    }

    public function searchBills(array $filters, int $limit = 30, int $offset = 0): array
    {
        [$where, $params] = $this->buildSearchWhere($filters);
        return $this->db->fetchAll(
            "SELECT b.*, u.name AS user_name, u.email AS user_email
               FROM billx_bills b
               LEFT JOIN users u ON b.user_id = u.id
              WHERE $where
              ORDER BY b.created_at DESC
              LIMIT ? OFFSET ?",
            array_merge($params, [$limit, $offset])
        ) ?: [];
    }

    public function countSearch(array $filters): int
    {
        [$where, $params] = $this->buildSearchWhere($filters);
        $r = $this->db->fetch(
            "SELECT COUNT(*) AS c FROM billx_bills b WHERE $where",
            $params
        );
        return (int)($r['c'] ?? 0);
    }

    private function buildSearchWhere(array $filters): array
    {
        $where  = '1=1';
        $params = [];
        if (!empty($filters['bill_type'])) {
            $where   .= ' AND b.bill_type = ?';
            $params[] = $filters['bill_type'];
        }
        if (!empty($filters['search'])) {
            $like     = '%' . $filters['search'] . '%';
            $where   .= ' AND (b.bill_number LIKE ? OR b.from_name LIKE ? OR b.to_name LIKE ?)';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        if (!empty($filters['user_search'])) {
            $like     = '%' . $filters['user_search'] . '%';
            $where   .= ' AND (u.name LIKE ? OR u.email LIKE ?)';
            $params[] = $like;
            $params[] = $like;
        }
        if (!empty($filters['date_from'])) {
            $where   .= ' AND DATE(b.created_at) >= ?';
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where   .= ' AND DATE(b.created_at) <= ?';
            $params[] = $filters['date_to'];
        }
        return [$where, $params];
    }

    public function getRevenueStats(): array
    {
        $r = $this->db->fetch(
            "SELECT
                COALESCE(SUM(total_amount), 0) AS total_revenue,
                COALESCE(SUM(CASE WHEN DATE(created_at) = CURDATE() THEN total_amount ELSE 0 END), 0) AS today_revenue,
                COALESCE(SUM(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN total_amount ELSE 0 END), 0) AS month_revenue
             FROM billx_bills"
        );
        return $r ?: ['total_revenue' => 0, 'today_revenue' => 0, 'month_revenue' => 0];
    }

    public function getAllForExport(array $filters = []): array
    {
        [$where, $params] = $this->buildSearchWhere($filters);
        return $this->db->fetchAll(
            "SELECT b.id, b.bill_number, b.bill_type, b.bill_date, b.from_name, b.to_name,
                    b.subtotal, b.tax_amount, b.discount_amount, b.total_amount, b.currency,
                    b.status, b.created_at, u.name AS user_name, u.email AS user_email
               FROM billx_bills b
               LEFT JOIN users u ON b.user_id = u.id
              WHERE $where
              ORDER BY b.created_at DESC",
            $params
        ) ?: [];
    }
}
