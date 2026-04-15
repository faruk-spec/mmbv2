<?php
/**
 * Support System Model
 *
 * Handles all database operations for the platform-level support ticket
 * and live chat system.
 *
 * @package Models
 */

namespace Models;

use Core\Database;

class SupportModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureTables();
    }

    // -------------------------------------------------------------------------
    // Schema bootstrap
    // -------------------------------------------------------------------------

    private function ensureTables(): void
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `support_tickets` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT UNSIGNED NOT NULL,
            `template_item_id` INT UNSIGNED NULL,
            `subject` VARCHAR(255) NOT NULL,
            `description` TEXT NOT NULL,
            `priority` ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
            `status` ENUM('open','in_progress','waiting_customer','resolved','closed') NOT NULL DEFAULT 'open',
            `assigned_to` INT UNSIGNED NULL COMMENT 'admin user id',
            `last_reply_at` DATETIME NULL,
            `closed_at` DATETIME NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_st_user` (`user_id`),
            INDEX `idx_st_status` (`status`),
            INDEX `idx_st_created` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS `support_ticket_messages` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `ticket_id` INT UNSIGNED NOT NULL,
            `sender_type` ENUM('user','agent') NOT NULL,
            `sender_id` INT UNSIGNED NOT NULL,
            `message` TEXT NOT NULL,
            `is_internal` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_stm_ticket` (`ticket_id`),
            INDEX `idx_stm_sender` (`sender_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS `support_template_categories` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `description` TEXT NULL,
            `icon` VARCHAR(50) NOT NULL DEFAULT 'folder',
            `sort_order` INT NOT NULL DEFAULT 0,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS `support_template_items` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `category_id` INT UNSIGNED NOT NULL,
            `name` VARCHAR(150) NOT NULL,
            `description` TEXT NULL,
            `default_priority` ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
            `fields_schema` TEXT NULL COMMENT 'JSON array of field definitions',
            `sort_order` INT NOT NULL DEFAULT 0,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_sti_category` (`category_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS `support_live_chats` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT UNSIGNED NULL COMMENT 'NULL for guests',
            `guest_name` VARCHAR(120) NULL,
            `guest_email` VARCHAR(255) NULL,
            `session_key` VARCHAR(64) NOT NULL UNIQUE COMMENT 'for guest session tracking',
            `status` ENUM('active','closed') NOT NULL DEFAULT 'active',
            `assigned_agent_id` INT UNSIGNED NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `closed_at` DATETIME NULL,
            INDEX `idx_slc_user` (`user_id`),
            INDEX `idx_slc_status` (`status`),
            INDEX `idx_slc_session_key` (`session_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS `support_live_messages` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `chat_id` INT UNSIGNED NOT NULL,
            `sender_type` ENUM('user','guest','agent','ai') NOT NULL,
            `sender_id` INT UNSIGNED NULL,
            `message` TEXT NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_slm_chat` (`chat_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        try {
            $this->db->query("CREATE TABLE IF NOT EXISTS `support_agents` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT UNSIGNED NOT NULL,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `assigned_by` INT UNSIGNED NULL,
                `notes` TEXT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `uq_sa_user` (`user_id`),
                INDEX `idx_sa_user` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (\Exception $e) {
            // Table may already exist or DB user lacks DDL privilege; non-fatal
        }
    }

    // -------------------------------------------------------------------------
    // Ticket methods
    // -------------------------------------------------------------------------

    public function createTicket(
        int $userId,
        ?int $templateItemId,
        string $subject,
        string $description,
        string $priority
    ): int {
        $this->db->insert('support_tickets', [
            'user_id'          => $userId,
            'template_item_id' => $templateItemId,
            'subject'          => $subject,
            'description'      => $description,
            'priority'         => $priority,
            'status'           => 'open',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getTicketsByUser(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT st.*, u.name AS user_name
             FROM support_tickets st
             LEFT JOIN users u ON u.id = st.user_id
             WHERE st.user_id = ?
             ORDER BY st.updated_at DESC, st.created_at DESC",
            [$userId]
        ) ?: [];
    }

    public function getAllTickets(array $filters = []): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[]  = 'st.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['priority'])) {
            $where[]  = 'st.priority = ?';
            $params[] = $filters['priority'];
        }
        if (!empty($filters['user_id'])) {
            $where[]  = 'st.user_id = ?';
            $params[] = (int) $filters['user_id'];
        }

        $whereClause = implode(' AND ', $where);

        return $this->db->fetchAll(
            "SELECT st.*,
                    u.name  AS user_name,
                    a.name  AS agent_name
             FROM support_tickets st
             LEFT JOIN users u ON u.id = st.user_id
             LEFT JOIN users a ON a.id = st.assigned_to
             WHERE {$whereClause}
             ORDER BY
                 FIELD(st.status,'open','in_progress','waiting_customer','resolved','closed'),
                 st.updated_at DESC,
                 st.created_at DESC",
            $params
        ) ?: [];
    }

    public function getTicketById(int $id): ?array
    {
        $row = $this->db->fetch(
            "SELECT st.*,
                    u.name  AS user_name,
                    u.email AS user_email,
                    a.name  AS agent_name
             FROM support_tickets st
             LEFT JOIN users u ON u.id = st.user_id
             LEFT JOIN users a ON a.id = st.assigned_to
             WHERE st.id = ?",
            [$id]
        );
        return $row ?: null;
    }

    public function getTicketMessages(int $ticketId, bool $includeInternal = false): array
    {
        $internalClause = $includeInternal ? '' : 'AND m.is_internal = 0';

        return $this->db->fetchAll(
            "SELECT m.*, u.name AS sender_name
             FROM support_ticket_messages m
             LEFT JOIN users u ON u.id = m.sender_id
             WHERE m.ticket_id = ? {$internalClause}
             ORDER BY m.created_at ASC",
            [$ticketId]
        ) ?: [];
    }

    public function addTicketMessage(
        int $ticketId,
        string $senderType,
        int $senderId,
        string $message,
        bool $isInternal = false
    ): void {
        $this->db->insert('support_ticket_messages', [
            'ticket_id'   => $ticketId,
            'sender_type' => $senderType,
            'sender_id'   => $senderId,
            'message'     => $message,
            'is_internal' => $isInternal ? 1 : 0,
        ]);

        $statusUpdate = [];
        if ($senderType === 'user') {
            $statusUpdate['status'] = 'open';
        } elseif ($senderType === 'agent' && !$isInternal) {
            $statusUpdate['status'] = 'in_progress';
        }
        $statusUpdate['last_reply_at'] = date('Y-m-d H:i:s');

        $this->db->update('support_tickets', $statusUpdate, 'id = ?', [$ticketId]);
    }

    public function updateTicketStatus(int $id, string $status, ?int $agentId = null): void
    {
        $data = ['status' => $status];
        if ($status === 'closed') {
            $data['closed_at'] = date('Y-m-d H:i:s');
        }
        if ($agentId !== null) {
            $data['assigned_to'] = $agentId;
        }
        $this->db->update('support_tickets', $data, 'id = ?', [$id]);
    }

    public function getTicketStats(): array
    {
        $rows = $this->db->fetchAll(
            "SELECT status, COUNT(*) AS cnt FROM support_tickets GROUP BY status"
        ) ?: [];

        $stats = ['open' => 0, 'in_progress' => 0, 'waiting_customer' => 0, 'resolved' => 0, 'closed' => 0, 'total' => 0];
        foreach ($rows as $row) {
            $stats[$row['status']] = (int) $row['cnt'];
            $stats['total'] += (int) $row['cnt'];
        }
        return $stats;
    }

    public function getTicketStatsByUser(int $userId): array
    {
        $rows = $this->db->fetchAll(
            "SELECT status, COUNT(*) AS cnt FROM support_tickets WHERE user_id = ? GROUP BY status",
            [$userId]
        ) ?: [];

        $stats = ['open' => 0, 'in_progress' => 0, 'waiting_customer' => 0, 'resolved' => 0, 'closed' => 0, 'total' => 0];
        foreach ($rows as $row) {
            $stats[$row['status']] = (int) $row['cnt'];
            $stats['total'] += (int) $row['cnt'];
        }
        return $stats;
    }

    // -------------------------------------------------------------------------
    // Template methods
    // -------------------------------------------------------------------------

    public function getTemplateCategories(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM support_template_categories WHERE is_active = 1 ORDER BY sort_order ASC, name ASC"
        ) ?: [];
    }

    public function getTemplateItems(?int $categoryId = null): array
    {
        if ($categoryId !== null) {
            return $this->db->fetchAll(
                "SELECT i.*, c.name AS category_name
                 FROM support_template_items i
                 LEFT JOIN support_template_categories c ON c.id = i.category_id
                 WHERE i.is_active = 1 AND i.category_id = ?
                 ORDER BY i.sort_order ASC, i.name ASC",
                [$categoryId]
            ) ?: [];
        }
        return $this->db->fetchAll(
            "SELECT i.*, c.name AS category_name
             FROM support_template_items i
             LEFT JOIN support_template_categories c ON c.id = i.category_id
             WHERE i.is_active = 1
             ORDER BY i.sort_order ASC, i.name ASC"
        ) ?: [];
    }

    public function getTemplateItemById(int $id): ?array
    {
        $row = $this->db->fetch(
            "SELECT i.*, c.name AS category_name
             FROM support_template_items i
             LEFT JOIN support_template_categories c ON c.id = i.category_id
             WHERE i.id = ?",
            [$id]
        );
        return $row ?: null;
    }

    public function createTemplateCategory(string $name, string $description, string $icon): int
    {
        $this->db->insert('support_template_categories', [
            'name'        => $name,
            'description' => $description,
            'icon'        => $icon,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updateTemplateCategory(int $id, string $name, string $description, string $icon): void
    {
        $this->db->update('support_template_categories', [
            'name'        => $name,
            'description' => $description,
            'icon'        => $icon,
        ], 'id = ?', [$id]);
    }

    public function deleteTemplateCategory(int $id): void
    {
        $this->db->delete('support_template_categories', 'id = ?', [$id]);
    }

    public function createTemplateItem(
        int $categoryId,
        string $name,
        string $description,
        string $defaultPriority,
        string $fieldsSchema
    ): int {
        $this->db->insert('support_template_items', [
            'category_id'      => $categoryId,
            'name'             => $name,
            'description'      => $description,
            'default_priority' => $defaultPriority,
            'fields_schema'    => $fieldsSchema,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updateTemplateItem(
        int $id,
        string $name,
        string $description,
        string $defaultPriority,
        string $fieldsSchema
    ): void {
        $this->db->update('support_template_items', [
            'name'             => $name,
            'description'      => $description,
            'default_priority' => $defaultPriority,
            'fields_schema'    => $fieldsSchema,
        ], 'id = ?', [$id]);
    }

    public function deleteTemplateItem(int $id): void
    {
        $this->db->delete('support_template_items', 'id = ?', [$id]);
    }

    // -------------------------------------------------------------------------
    // Live chat methods
    // -------------------------------------------------------------------------

    public function findOrCreateChat(
        ?int $userId,
        string $sessionKey,
        string $guestName = '',
        string $guestEmail = ''
    ): array {
        $existing = $this->db->fetch(
            "SELECT * FROM support_live_chats WHERE session_key = ? AND status = 'active'",
            [$sessionKey]
        );
        if ($existing) {
            return $existing;
        }

        $this->db->insert('support_live_chats', [
            'user_id'     => $userId,
            'guest_name'  => $guestName ?: null,
            'guest_email' => $guestEmail ?: null,
            'session_key' => $sessionKey,
            'status'      => 'active',
        ]);
        $id = (int) $this->db->lastInsertId();
        return $this->db->fetch("SELECT * FROM support_live_chats WHERE id = ?", [$id]);
    }

    public function getChatById(int $id): ?array
    {
        $row = $this->db->fetch(
            "SELECT c.*, u.name AS user_name, a.name AS agent_name
             FROM support_live_chats c
             LEFT JOIN users u ON u.id = c.user_id
             LEFT JOIN users a ON a.id = c.assigned_agent_id
             WHERE c.id = ?",
            [$id]
        );
        return $row ?: null;
    }

    public function getChatBySessionKey(string $sessionKey): ?array
    {
        $row = $this->db->fetch(
            "SELECT c.*, u.name AS user_name, a.name AS agent_name
             FROM support_live_chats c
             LEFT JOIN users u ON u.id = c.user_id
             LEFT JOIN users a ON a.id = c.assigned_agent_id
             WHERE c.session_key = ?",
            [$sessionKey]
        );
        return $row ?: null;
    }

    public function addLiveMessage(
        int $chatId,
        string $senderType,
        ?int $senderId,
        string $message
    ): void {
        $this->db->insert('support_live_messages', [
            'chat_id'     => $chatId,
            'sender_type' => $senderType,
            'sender_id'   => $senderId,
            'message'     => $message,
        ]);
    }

    public function getLiveMessages(int $chatId): array
    {
        return $this->db->fetchAll(
            "SELECT m.*, u.name AS sender_name
             FROM support_live_messages m
             LEFT JOIN users u ON u.id = m.sender_id
             WHERE m.chat_id = ?
             ORDER BY m.created_at ASC",
            [$chatId]
        ) ?: [];
    }

    public function getAllActiveChats(): array
    {
        return $this->db->fetchAll(
            "SELECT c.*, u.name AS user_name, a.name AS agent_name
             FROM support_live_chats c
             LEFT JOIN users u ON u.id = c.user_id
             LEFT JOIN users a ON a.id = c.assigned_agent_id
             WHERE c.status = 'active'
             ORDER BY c.created_at DESC"
        ) ?: [];
    }

    public function getAllChats(): array
    {
        return $this->db->fetchAll(
            "SELECT c.*, u.name AS user_name, a.name AS agent_name
             FROM support_live_chats c
             LEFT JOIN users u ON u.id = c.user_id
             LEFT JOIN users a ON a.id = c.assigned_agent_id
             ORDER BY c.created_at DESC"
        ) ?: [];
    }

    public function closeChat(int $chatId): void
    {
        $this->db->update('support_live_chats', [
            'status'    => 'closed',
            'closed_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$chatId]);
    }

    public function reopenChat(int $chatId): void
    {
        $this->db->update('support_live_chats', [
            'status'    => 'active',
            'closed_at' => null,
        ], 'id = ?', [$chatId]);
    }

    public function assignChatAgent(int $chatId, int $agentId): void
    {
        $this->db->update('support_live_chats', ['assigned_agent_id' => $agentId], 'id = ?', [$chatId]);
    }

    // -------------------------------------------------------------------------
    // User access methods
    // -------------------------------------------------------------------------

    public function getSupportUsers(): array
    {
        return $this->db->fetchAll(
            "SELECT u.id, u.name, u.email,
                    COUNT(DISTINCT st.id)  AS ticket_count,
                    COUNT(DISTINCT slc.id) AS chat_count,
                    MAX(GREATEST(
                        COALESCE(st.updated_at, '1970-01-01'),
                        COALESCE(st.created_at, '1970-01-01'),
                        COALESCE(slc.created_at, '1970-01-01')
                    )) AS last_activity
             FROM users u
             LEFT JOIN support_tickets     st  ON st.user_id  = u.id
             LEFT JOIN support_live_chats  slc ON slc.user_id = u.id
             WHERE st.id IS NOT NULL OR slc.id IS NOT NULL
             GROUP BY u.id, u.name, u.email
             ORDER BY last_activity DESC"
        ) ?: [];
    }

    // -------------------------------------------------------------------------
    // Agent management methods
    // -------------------------------------------------------------------------

    public function getAllAgents(): array
    {
        return $this->db->fetchAll(
            "SELECT sa.*, u.name, u.email, u.role,
                    ab.name AS assigned_by_name
             FROM support_agents sa
             JOIN users u ON u.id = sa.user_id
             LEFT JOIN users ab ON ab.id = sa.assigned_by
             WHERE sa.is_active = 1
             ORDER BY sa.created_at DESC"
        ) ?: [];
    }

    public function isAgent(int $userId): bool
    {
        $row = $this->db->fetch(
            "SELECT id FROM support_agents WHERE user_id = ? AND is_active = 1",
            [$userId]
        );
        return $row !== null && $row !== false;
    }

    public function addAgent(int $userId, int $assignedBy, string $notes = ''): void
    {
        // Upsert: if already exists (maybe inactive) update, otherwise insert
        $existing = $this->db->fetch("SELECT id FROM support_agents WHERE user_id = ?", [$userId]);
        if ($existing) {
            $this->db->update('support_agents', [
                'is_active'   => 1,
                'assigned_by' => $assignedBy,
                'notes'       => $notes,
            ], 'user_id = ?', [$userId]);
        } else {
            $this->db->insert('support_agents', [
                'user_id'     => $userId,
                'is_active'   => 1,
                'assigned_by' => $assignedBy,
                'notes'       => $notes,
            ]);
        }
    }

    public function removeAgent(int $userId): void
    {
        $this->db->update('support_agents', ['is_active' => 0], 'user_id = ?', [$userId]);
    }

    public function getAllUsersForAgentAssign(): array
    {
        // Returns all non-admin users who could be made agents (plus current agents)
        return $this->db->fetchAll(
            "SELECT u.id, u.name, u.email, u.role,
                    IF(sa.is_active = 1, 1, 0) AS is_agent
             FROM users u
             LEFT JOIN support_agents sa ON sa.user_id = u.id AND sa.is_active = 1
             ORDER BY u.name ASC"
        ) ?: [];
    }
}
