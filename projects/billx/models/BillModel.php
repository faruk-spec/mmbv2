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
    private $db;

    public function __construct()
    {
        try {
            $this->db = Database::getProjectInstance('billx');
        } catch (\Exception $e) {
            $this->db = null;
        }
    }

    public function create(array $data): int
    {
        if (!$this->db) return 0;
        return $this->db->insert('billx_bills', $data);
    }

    public function getById(int $id): ?array
    {
        if (!$this->db) return null;
        return $this->db->fetch("SELECT * FROM billx_bills WHERE id = ?", [$id]) ?: null;
    }

    public function getByUser(int $userId, int $limit = 50, int $offset = 0): array
    {
        if (!$this->db) return [];
        return $this->db->fetchAll(
            "SELECT * FROM billx_bills WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$userId, $limit, $offset]
        );
    }

    public function countByUser(int $userId): int
    {
        if (!$this->db) return 0;
        $r = $this->db->fetch("SELECT COUNT(*) as c FROM billx_bills WHERE user_id = ?", [$userId]);
        return (int)($r['c'] ?? 0);
    }

    public function delete(int $id, int $userId): bool
    {
        if (!$this->db) return false;
        return (bool)$this->db->query(
            "DELETE FROM billx_bills WHERE id = ? AND user_id = ?",
            [$id, $userId]
        );
    }

    public function getAll(int $limit = 100, int $offset = 0): array
    {
        if (!$this->db) return [];
        return $this->db->fetchAll(
            "SELECT b.*, u.name as user_name, u.email as user_email
             FROM billx_bills b
             LEFT JOIN mmb_main.users u ON b.user_id = u.id
             ORDER BY b.created_at DESC LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }

    public function countAll(): int
    {
        if (!$this->db) return 0;
        $r = $this->db->fetch("SELECT COUNT(*) as c FROM billx_bills");
        return (int)($r['c'] ?? 0);
    }

    public function getStats(): array
    {
        if (!$this->db) return ['total' => 0, 'today' => 0, 'this_month' => 0];
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
        if (!$this->db) return false;
        return (bool)$this->db->query("DELETE FROM billx_bills WHERE id = ?", [$id]);
    }
}
