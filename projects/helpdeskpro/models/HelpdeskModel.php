<?php
/**
 * Helpdesk Pro Model
 *
 * @package MMB\Projects\HelpdeskPro\Models
 */

namespace Projects\HelpdeskPro\Models;

use Core\Database;

class HelpdeskModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureTables();
    }

    private function ensureTables(): void
    {
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `helpdesk_tickets` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT UNSIGNED NOT NULL,
                `subject` VARCHAR(255) NOT NULL,
                `description` TEXT NOT NULL,
                `priority` ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
                `status` ENUM('open','in_progress','waiting_customer','resolved','closed') NOT NULL DEFAULT 'open',
                `requester_email` VARCHAR(255) NULL,
                `assigned_agent_id` INT UNSIGNED NULL,
                `channel` VARCHAR(30) NOT NULL DEFAULT 'web',
                `last_reply_at` DATETIME NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_helpdesk_tickets_user` (`user_id`),
                INDEX `idx_helpdesk_tickets_status` (`status`),
                INDEX `idx_helpdesk_tickets_assigned` (`assigned_agent_id`),
                INDEX `idx_helpdesk_tickets_created` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `helpdesk_ticket_messages` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `ticket_id` INT UNSIGNED NOT NULL,
                `sender_type` ENUM('customer','agent','ai') NOT NULL,
                `sender_user_id` INT UNSIGNED NULL,
                `message` TEXT NOT NULL,
                `is_internal` TINYINT(1) NOT NULL DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_helpdesk_messages_ticket` (`ticket_id`),
                INDEX `idx_helpdesk_messages_sender` (`sender_user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `helpdesk_live_sessions` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT UNSIGNED NOT NULL,
                `customer_name` VARCHAR(120) NULL,
                `customer_email` VARCHAR(255) NULL,
                `status` ENUM('open','waiting_agent','closed') NOT NULL DEFAULT 'open',
                `assigned_agent_id` INT UNSIGNED NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
                `closed_at` DATETIME NULL,
                INDEX `idx_helpdesk_live_user` (`user_id`),
                INDEX `idx_helpdesk_live_status` (`status`),
                INDEX `idx_helpdesk_live_assigned` (`assigned_agent_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `helpdesk_live_messages` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `session_id` INT UNSIGNED NOT NULL,
                `sender_type` ENUM('customer','agent','ai') NOT NULL,
                `sender_user_id` INT UNSIGNED NULL,
                `message` TEXT NOT NULL,
                `is_ai` TINYINT(1) NOT NULL DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_helpdesk_live_messages_session` (`session_id`),
                INDEX `idx_helpdesk_live_messages_sender` (`sender_user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    public function getDashboardStats(int $userId, bool $isAgent): array
    {
        if ($isAgent) {
            return [
                'tickets_open' => (int) ($this->db->fetchColumn("SELECT COUNT(*) FROM helpdesk_tickets WHERE status IN ('open','in_progress','waiting_customer')") ?? 0),
                'tickets_mine' => (int) ($this->db->fetchColumn("SELECT COUNT(*) FROM helpdesk_tickets WHERE assigned_agent_id = ? AND status IN ('open','in_progress','waiting_customer')", [$userId]) ?? 0),
                'live_waiting' => (int) ($this->db->fetchColumn("SELECT COUNT(*) FROM helpdesk_live_sessions WHERE status IN ('open','waiting_agent')") ?? 0),
            ];
        }

        return [
            'tickets_open' => (int) ($this->db->fetchColumn("SELECT COUNT(*) FROM helpdesk_tickets WHERE user_id = ? AND status IN ('open','in_progress','waiting_customer')", [$userId]) ?? 0),
            'tickets_mine' => (int) ($this->db->fetchColumn("SELECT COUNT(*) FROM helpdesk_tickets WHERE user_id = ?", [$userId]) ?? 0),
            'live_waiting' => (int) ($this->db->fetchColumn("SELECT COUNT(*) FROM helpdesk_live_sessions WHERE user_id = ? AND status IN ('open','waiting_agent')", [$userId]) ?? 0),
        ];
    }

    public function getTickets(int $userId, bool $isAgent, int $limit = 50): array
    {
        if ($isAgent) {
            return $this->db->fetchAll(
                "SELECT t.*, u.name AS requester_name
                 FROM helpdesk_tickets t
                 LEFT JOIN users u ON u.id = t.user_id
                 ORDER BY FIELD(t.status, 'open', 'in_progress', 'waiting_customer', 'resolved', 'closed'), t.updated_at DESC, t.created_at DESC
                 LIMIT ?",
                [$limit]
            ) ?: [];
        }

        return $this->db->fetchAll(
            "SELECT t.*, u.name AS requester_name
             FROM helpdesk_tickets t
             LEFT JOIN users u ON u.id = t.user_id
             WHERE t.user_id = ?
             ORDER BY t.updated_at DESC, t.created_at DESC
             LIMIT ?",
            [$userId, $limit]
        ) ?: [];
    }

    public function createTicket(
        int $userId,
        string $subject,
        string $description,
        string $priority,
        ?string $requesterEmail = null
    ): int {
        $this->db->query(
            "INSERT INTO helpdesk_tickets (user_id, subject, description, priority, status, requester_email, channel, last_reply_at)
             VALUES (?, ?, ?, ?, 'open', ?, 'web', NOW())",
            [$userId, $subject, $description, $priority, $requesterEmail]
        );
        return (int) $this->db->lastInsertId();
    }

    public function getTicketById(int $ticketId, int $userId, bool $isAgent): ?array
    {
        if ($isAgent) {
            return $this->db->fetch(
                "SELECT t.*, u.name AS requester_name, u.email AS requester_user_email,
                        a.name AS assigned_agent_name
                 FROM helpdesk_tickets t
                 LEFT JOIN users u ON u.id = t.user_id
                 LEFT JOIN users a ON a.id = t.assigned_agent_id
                 WHERE t.id = ?",
                [$ticketId]
            ) ?: null;
        }

        return $this->db->fetch(
            "SELECT t.*, u.name AS requester_name, u.email AS requester_user_email,
                    a.name AS assigned_agent_name
             FROM helpdesk_tickets t
             LEFT JOIN users u ON u.id = t.user_id
             LEFT JOIN users a ON a.id = t.assigned_agent_id
             WHERE t.id = ? AND t.user_id = ?",
            [$ticketId, $userId]
        ) ?: null;
    }

    public function getTicketMessages(int $ticketId, bool $isAgent): array
    {
        $sql = "SELECT m.*, u.name AS sender_name
                FROM helpdesk_ticket_messages m
                LEFT JOIN users u ON u.id = m.sender_user_id
                WHERE m.ticket_id = ?";

        if (!$isAgent) {
            $sql .= " AND m.is_internal = 0";
        }

        $sql .= " ORDER BY m.created_at ASC";

        return $this->db->fetchAll($sql, [$ticketId]) ?: [];
    }

    public function addTicketMessage(
        int $ticketId,
        string $senderType,
        ?int $senderUserId,
        string $message,
        bool $isInternal = false
    ): void {
        $this->db->query(
            "INSERT INTO helpdesk_ticket_messages (ticket_id, sender_type, sender_user_id, message, is_internal)
             VALUES (?, ?, ?, ?, ?)",
            [$ticketId, $senderType, $senderUserId, $message, $isInternal ? 1 : 0]
        );

        $nextStatus = $senderType === 'customer' ? 'open' : 'in_progress';
        $this->db->query(
            "UPDATE helpdesk_tickets SET status = ?, last_reply_at = NOW() WHERE id = ?",
            [$nextStatus, $ticketId]
        );
    }

    public function updateTicketStatus(int $ticketId, string $status, ?int $assignedAgentId = null): void
    {
        if ($assignedAgentId !== null) {
            $this->db->query(
                "UPDATE helpdesk_tickets SET status = ?, assigned_agent_id = ? WHERE id = ?",
                [$status, $assignedAgentId, $ticketId]
            );
            return;
        }

        $this->db->query(
            "UPDATE helpdesk_tickets SET status = ? WHERE id = ?",
            [$status, $ticketId]
        );
    }

    public function getOpenLiveSessionByUser(int $userId): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM helpdesk_live_sessions WHERE user_id = ? AND status IN ('open','waiting_agent') ORDER BY id DESC LIMIT 1",
            [$userId]
        ) ?: null;
    }

    public function createLiveSession(int $userId, string $name, string $email): int
    {
        $this->db->query(
            "INSERT INTO helpdesk_live_sessions (user_id, customer_name, customer_email, status)
             VALUES (?, ?, ?, 'open')",
            [$userId, $name, $email]
        );
        return (int) $this->db->lastInsertId();
    }

    public function getLiveSessionById(int $sessionId, int $userId, bool $isAgent): ?array
    {
        if ($isAgent) {
            return $this->db->fetch(
                "SELECT s.*, u.name AS user_name, u.email AS user_email, a.name AS assigned_agent_name
                 FROM helpdesk_live_sessions s
                 LEFT JOIN users u ON u.id = s.user_id
                 LEFT JOIN users a ON a.id = s.assigned_agent_id
                 WHERE s.id = ?",
                [$sessionId]
            ) ?: null;
        }

        return $this->db->fetch(
            "SELECT s.*, u.name AS user_name, u.email AS user_email, a.name AS assigned_agent_name
             FROM helpdesk_live_sessions s
             LEFT JOIN users u ON u.id = s.user_id
             LEFT JOIN users a ON a.id = s.assigned_agent_id
             WHERE s.id = ? AND s.user_id = ?",
            [$sessionId, $userId]
        ) ?: null;
    }

    public function getLiveMessages(int $sessionId): array
    {
        return $this->db->fetchAll(
            "SELECT m.*, u.name AS sender_name
             FROM helpdesk_live_messages m
             LEFT JOIN users u ON u.id = m.sender_user_id
             WHERE m.session_id = ?
             ORDER BY m.created_at ASC",
            [$sessionId]
        ) ?: [];
    }

    public function addLiveMessage(
        int $sessionId,
        string $senderType,
        ?int $senderUserId,
        string $message,
        bool $isAi = false
    ): void {
        $this->db->query(
            "INSERT INTO helpdesk_live_messages (session_id, sender_type, sender_user_id, message, is_ai)
             VALUES (?, ?, ?, ?, ?)",
            [$sessionId, $senderType, $senderUserId, $message, $isAi ? 1 : 0]
        );
    }

    public function assignLiveSessionToAgent(int $sessionId, int $agentId): void
    {
        $this->db->query(
            "UPDATE helpdesk_live_sessions SET assigned_agent_id = ?, status = 'waiting_agent' WHERE id = ?",
            [$agentId, $sessionId]
        );
    }

    public function getAgentLiveSessions(int $limit = 50): array
    {
        return $this->db->fetchAll(
            "SELECT s.*, u.name AS user_name, u.email AS user_email, a.name AS assigned_agent_name,
                    (SELECT COUNT(*) FROM helpdesk_live_messages lm WHERE lm.session_id = s.id) AS message_count
             FROM helpdesk_live_sessions s
             LEFT JOIN users u ON u.id = s.user_id
             LEFT JOIN users a ON a.id = s.assigned_agent_id
             WHERE s.status IN ('open','waiting_agent')
             ORDER BY s.updated_at DESC, s.created_at DESC
             LIMIT ?",
            [$limit]
        ) ?: [];
    }

    public function getSupportAgents(): array
    {
        return $this->db->fetchAll(
            "SELECT id, name, email, role FROM users
             WHERE role LIKE ? OR role LIKE ?
             ORDER BY id ASC",
            ['%admin%', '%support%']
        ) ?: [];
    }
}
