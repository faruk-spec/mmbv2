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
            `sender_type` ENUM('user','agent','system') NOT NULL,
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
            `department` VARCHAR(100) NULL,
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

        $this->db->query("CREATE TABLE IF NOT EXISTS `support_ticket_activities` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `ticket_id` INT UNSIGNED NOT NULL,
            `activity_type` VARCHAR(50) NOT NULL,
            `actor_id` INT UNSIGNED NULL,
            `description` TEXT NOT NULL,
            `meta_json` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_sta_ticket` (`ticket_id`),
            INDEX `idx_sta_type` (`activity_type`)
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

        // Add fields_schema column to support_template_items if it was created without it
        try {
            $col = $this->db->fetch(
                "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'support_template_items' AND COLUMN_NAME = 'fields_schema'"
            );
            if (!$col) {
                $this->db->query("ALTER TABLE `support_template_items` ADD COLUMN `fields_schema` TEXT NULL COMMENT 'JSON array of field definitions'");
            }
        } catch (\Exception $e) {
            // Non-fatal: table may not exist yet (will be created with column above)
        }

        // Add department column to support_template_categories if missing
        try {
            $col = $this->db->fetch(
                "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'support_template_categories' AND COLUMN_NAME = 'department'"
            );
            if (!$col) {
                $this->db->query("ALTER TABLE `support_template_categories` ADD COLUMN `department` VARCHAR(100) NULL AFTER `name`");
            }
        } catch (\Exception $e) {
            // Non-fatal
        }

        // Ensure 'system' is included in sender_type ENUM (added for status-change system messages)
        try {
            $enumRow = $this->db->fetch(
                "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'support_ticket_messages' AND COLUMN_NAME = 'sender_type'"
            );
            if ($enumRow && strpos($enumRow['COLUMN_TYPE'] ?? $enumRow['column_type'] ?? '', 'system') === false) {
                $this->db->query(
                    "ALTER TABLE `support_ticket_messages` MODIFY COLUMN `sender_type` ENUM('user','agent','system') NOT NULL"
                );
            }
        } catch (\Exception $e) {
            // Non-fatal: column will accept 'system' once migrated
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
        $ticketId = (int) $this->db->lastInsertId();
        $this->addTicketActivity($ticketId, 'ticket_created', $userId, 'Ticket was created.');
        return $ticketId;
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
        if (!empty($filters['assigned_to'])) {
            $where[]  = 'st.assigned_to = ?';
            $params[] = (int) $filters['assigned_to'];
        }
        if (!empty($filters['query'])) {
            $where[]  = '(st.subject LIKE ? OR st.description LIKE ? OR u.name LIKE ?)';
            $q        = '%' . $filters['query'] . '%';
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
        }
        if (!empty($filters['from_date'])) {
            $where[]  = 'DATE(st.created_at) >= ?';
            $params[] = $filters['from_date'];
        }
        if (!empty($filters['to_date'])) {
            $where[]  = 'DATE(st.created_at) <= ?';
            $params[] = $filters['to_date'];
        }

        $whereClause = implode(' AND ', $where);

        return $this->db->fetchAll(
             "SELECT st.*,
                     u.name  AS user_name,
                     u.email AS user_email,
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
            $this->addTicketActivity($ticketId, 'customer_reply', $senderId, 'Customer replied to the ticket.');
        } elseif ($senderType === 'agent' && !$isInternal) {
            $statusUpdate['status'] = 'in_progress';
            $this->addTicketActivity($ticketId, 'agent_reply', $senderId, 'Agent replied to the customer.');
        } elseif ($senderType === 'agent' && $isInternal) {
            $this->addTicketActivity($ticketId, 'internal_note', $senderId, 'Agent added an internal note.');
        }
        $statusUpdate['last_reply_at'] = date('Y-m-d H:i:s');

        $this->db->update('support_tickets', $statusUpdate, 'id = ?', [$ticketId]);
    }

    /**
     * Add a system-generated message to the ticket chat thread (visible to both
     * user and agent). Does NOT auto-change the ticket status, so it is safe to
     * call alongside updateTicketStatus().
     * Typical use: surfacing status-change reasons in the conversation.
     */
    public function addSystemMessage(int $ticketId, string $message): void
    {
        $this->db->insert('support_ticket_messages', [
            'ticket_id'   => $ticketId,
            'sender_type' => 'system',
            'sender_id'   => 0,
            'message'     => $message,
            'is_internal' => 0,
        ]);
        $this->db->update('support_tickets', ['last_reply_at' => date('Y-m-d H:i:s')], 'id = ?', [$ticketId]);
    }


    public function updateTicketStatus(int $id, string $status, ?int $agentId = null): void
    {
        $existing = $this->getTicketById($id);
        $oldStatus = $existing['status'] ?? null;
        $data = ['status' => $status];
        if ($status === 'closed') {
            $data['closed_at'] = date('Y-m-d H:i:s');
        }
        if ($agentId !== null) {
            $data['assigned_to'] = $agentId;
        }
        $this->db->update('support_tickets', $data, 'id = ?', [$id]);
        if ($oldStatus !== null && $oldStatus !== $status) {
            $this->addTicketActivity(
                $id,
                'status_changed',
                $agentId,
                'Status changed from ' . str_replace('_', ' ', $oldStatus) . ' to ' . str_replace('_', ' ', $status) . '.',
                ['from' => $oldStatus, 'to' => $status]
            );
        }
    }

    public function updateTicketPriority(int $id, string $priority): void
    {
        $this->db->update('support_tickets', ['priority' => $priority], 'id = ?', [$id]);
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

    /**
     * Same as getTemplateItems() but also returns fields_schema for JS rendering.
     */
    public function getTemplateItemsWithSchema(): array
    {
        return $this->db->fetchAll(
            "SELECT i.id, i.name, i.description, i.default_priority, i.fields_schema, c.name AS category_name, c.department
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

    public function createTemplateCategory(string $name, string $description, string $icon, string $department = ''): int
    {
        $this->db->insert('support_template_categories', [
            'name'        => $name,
            'department'  => $department !== '' ? $department : null,
            'description' => $description,
            'icon'        => $icon,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updateTemplateCategory(int $id, string $name, string $description, string $icon, string $department = ''): void
    {
        $this->db->update('support_template_categories', [
            'name'        => $name,
            'department'  => $department !== '' ? $department : null,
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

    public function getLiveChats(string $status = ''): array
    {
        $where  = $status ? 'WHERE c.status = ?' : 'WHERE 1=1';
        $params = $status ? [$status] : [];
        return $this->db->fetchAll(
            "SELECT c.*, u.name AS user_name, a.name AS agent_name
             FROM support_live_chats c
             LEFT JOIN users u ON u.id = c.user_id
             LEFT JOIN users a ON a.id = c.assigned_agent_id
             {$where}
             ORDER BY c.created_at DESC",
            $params
        ) ?: [];
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
        try {
            $row = $this->db->fetch(
                "SELECT id FROM support_agents WHERE user_id = ? AND is_active = 1",
                [$userId]
            );
            return $row !== null && $row !== false;
        } catch (\Exception $e) {
            return false;
        }
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

    public function getAssignableAgents(): array
    {
        return $this->db->fetchAll(
            "SELECT sa.user_id AS id, u.name, u.email
             FROM support_agents sa
             INNER JOIN users u ON u.id = sa.user_id
             WHERE sa.is_active = 1
             ORDER BY u.name ASC"
        ) ?: [];
    }

    public function assignTicketAgent(int $ticketId, int $agentId, int $actorId): void
    {
        $old = $this->getTicketById($ticketId);
        $this->db->update('support_tickets', ['assigned_to' => $agentId], 'id = ?', [$ticketId]);
        $new = $this->getTicketById($ticketId);
        $oldName = $old['agent_name'] ?? 'Unassigned';
        $newName = $new['agent_name'] ?? 'Unassigned';
        if ((int) ($old['assigned_to'] ?? 0) !== $agentId) {
            $this->addTicketActivity(
                $ticketId,
                'agent_assigned',
                $actorId,
                "Assignment changed from {$oldName} to {$newName}.",
                ['from' => $old['assigned_to'] ?? null, 'to' => $agentId]
            );
        }
    }

    public function addTicketActivity(
        int $ticketId,
        string $activityType,
        ?int $actorId,
        string $description,
        array $meta = []
    ): void {
        $this->db->insert('support_ticket_activities', [
            'ticket_id'      => $ticketId,
            'activity_type'  => $activityType,
            'actor_id'       => $actorId,
            'description'    => $description,
            'meta_json'      => !empty($meta) ? json_encode($meta) : null,
        ]);
    }

    public function getTicketActivities(int $ticketId): array
    {
        return $this->db->fetchAll(
            "SELECT a.*, u.name AS actor_name
             FROM support_ticket_activities a
             LEFT JOIN users u ON u.id = a.actor_id
             WHERE a.ticket_id = ?
             ORDER BY a.created_at DESC, a.id DESC",
            [$ticketId]
        ) ?: [];
    }

    public function getFirstAgentReplyAt(int $ticketId): ?string
    {
        return $this->db->fetchColumn(
            "SELECT MIN(created_at)
             FROM support_ticket_messages
             WHERE ticket_id = ? AND sender_type = 'agent' AND is_internal = 0",
            [$ticketId]
        ) ?: null;
    }

    public function getFirstAgentReplyMap(array $ticketIds): array
    {
        $ticketIds = array_values(array_filter(array_map('intval', $ticketIds), static fn ($id) => $id > 0));
        if (empty($ticketIds)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ticketIds), '?'));
        $rows = $this->db->fetchAll(
            "SELECT ticket_id, MIN(created_at) AS first_reply_at
             FROM support_ticket_messages
             WHERE sender_type = 'agent' AND is_internal = 0 AND ticket_id IN ({$placeholders})
             GROUP BY ticket_id",
            $ticketIds
        ) ?: [];
        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['ticket_id']] = $row['first_reply_at'];
        }
        return $map;
    }
}
