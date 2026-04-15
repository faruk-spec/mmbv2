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

        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `helpdesk_template_categories` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(100) NOT NULL,
                `description` TEXT NULL,
                `icon` VARCHAR(50) NOT NULL DEFAULT 'folder',
                `sort_order` INT NOT NULL DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `helpdesk_template_subcategories` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `category_id` INT UNSIGNED NOT NULL,
                `name` VARCHAR(100) NOT NULL,
                `description` TEXT NULL,
                `sort_order` INT NOT NULL DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (`category_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `helpdesk_template_items` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `subcategory_id` INT UNSIGNED NOT NULL,
                `name` VARCHAR(150) NOT NULL,
                `description` TEXT NULL,
                `default_priority` ENUM('low','medium','high','urgent') DEFAULT 'medium',
                `sort_order` INT NOT NULL DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (`subcategory_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `helpdesk_template_fields` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `item_id` INT UNSIGNED NOT NULL,
                `field_label` VARCHAR(120) NOT NULL,
                `field_type` ENUM('text','number','dropdown','multiselect','date','boolean','file') NOT NULL DEFAULT 'text',
                `field_options` TEXT NULL COMMENT 'JSON array for dropdown/multiselect',
                `is_required` TINYINT(1) DEFAULT 0,
                `sort_order` INT NOT NULL DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (`item_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `helpdesk_workflows` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(150) NOT NULL,
                `description` TEXT NULL,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `conditions` TEXT NOT NULL COMMENT 'JSON',
                `actions` TEXT NOT NULL COMMENT 'JSON',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `helpdesk_sla_rules` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `priority` ENUM('low','medium','high','urgent') NOT NULL UNIQUE,
                `first_response_hours` INT NOT NULL DEFAULT 24,
                `resolution_hours` INT NOT NULL DEFAULT 72
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `helpdesk_api_keys` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT UNSIGNED NOT NULL,
                `name` VARCHAR(100) NOT NULL,
                `api_key` VARCHAR(64) NOT NULL UNIQUE,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `last_used_at` DATETIME NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `helpdesk_widget_settings` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `greeting_text` VARCHAR(255) NOT NULL DEFAULT 'Hi! How can we help you today?',
                `primary_color` VARCHAR(20) NOT NULL DEFAULT '#3b82f6',
                `position` ENUM('bottom-right','bottom-left') NOT NULL DEFAULT 'bottom-right',
                `widget_title` VARCHAR(100) NOT NULL DEFAULT 'Support',
                `is_active` TINYINT(1) NOT NULL DEFAULT 1
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `helpdesk_webhooks` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(100) NOT NULL,
                `url` VARCHAR(500) NOT NULL,
                `events` TEXT NOT NULL COMMENT 'JSON array of event names',
                `secret` VARCHAR(64) NOT NULL DEFAULT '',
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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
        $users = $this->db->fetchAll(
            "SELECT id, name, email, role
             FROM users
             WHERE role IS NOT NULL AND role <> ''
             ORDER BY id ASC"
        ) ?: [];

        $agents = [];
        foreach ($users as $user) {
            $roles = array_values(array_filter(array_map('trim', explode(',', (string) ($user['role'] ?? '')))));
            if (in_array('admin', $roles, true) || in_array('super_admin', $roles, true) || in_array('support', $roles, true)) {
                $agents[] = $user;
            }
        }

        return $agents;
    }

    public function getAgentActiveWorkload(int $agentId): int
    {
        $ticketLoad = (int) ($this->db->fetchColumn(
            "SELECT COUNT(*) FROM helpdesk_tickets WHERE assigned_agent_id = ? AND status IN ('open','in_progress','waiting_customer')",
            [$agentId]
        ) ?? 0);

        $liveLoad = (int) ($this->db->fetchColumn(
            "SELECT COUNT(*) FROM helpdesk_live_sessions WHERE assigned_agent_id = ? AND status IN ('open','waiting_agent')",
            [$agentId]
        ) ?? 0);

        return $ticketLoad + $liveLoad;
    }

    // -------------------------------------------------------------------------
    // Template methods
    // -------------------------------------------------------------------------

    public function getTemplateCategories(): array
    {
        return $this->db->fetchAll(
            "SELECT c.*, (SELECT COUNT(*) FROM helpdesk_template_subcategories s WHERE s.category_id = c.id) AS sub_count
             FROM helpdesk_template_categories c ORDER BY c.sort_order ASC, c.id ASC"
        ) ?: [];
    }

    public function getTemplateCategoryById(int $id): ?array
    {
        return $this->db->fetch("SELECT * FROM helpdesk_template_categories WHERE id = ?", [$id]) ?: null;
    }

    public function createTemplateCategory(string $name, string $description, string $icon): int
    {
        $this->db->query(
            "INSERT INTO helpdesk_template_categories (name, description, icon) VALUES (?, ?, ?)",
            [$name, $description ?: null, $icon ?: 'folder']
        );
        return (int) $this->db->lastInsertId();
    }

    public function updateTemplateCategory(int $id, string $name, string $description, string $icon): void
    {
        $this->db->query(
            "UPDATE helpdesk_template_categories SET name=?, description=?, icon=? WHERE id=?",
            [$name, $description ?: null, $icon ?: 'folder', $id]
        );
    }

    public function deleteTemplateCategory(int $id): void
    {
        $this->db->query("DELETE FROM helpdesk_template_categories WHERE id=?", [$id]);
    }

    public function getTemplateSubcategories(int $categoryId): array
    {
        return $this->db->fetchAll(
            "SELECT s.*, (SELECT COUNT(*) FROM helpdesk_template_items i WHERE i.subcategory_id = s.id) AS item_count
             FROM helpdesk_template_subcategories s WHERE s.category_id=? ORDER BY s.sort_order ASC, s.id ASC",
            [$categoryId]
        ) ?: [];
    }

    public function getTemplateSubcategoryById(int $id): ?array
    {
        return $this->db->fetch("SELECT * FROM helpdesk_template_subcategories WHERE id=?", [$id]) ?: null;
    }

    public function createTemplateSubcategory(int $categoryId, string $name, string $description): int
    {
        $this->db->query(
            "INSERT INTO helpdesk_template_subcategories (category_id, name, description) VALUES (?, ?, ?)",
            [$categoryId, $name, $description ?: null]
        );
        return (int) $this->db->lastInsertId();
    }

    public function updateTemplateSubcategory(int $id, int $categoryId, string $name, string $description): void
    {
        $this->db->query(
            "UPDATE helpdesk_template_subcategories SET category_id=?, name=?, description=? WHERE id=?",
            [$categoryId, $name, $description ?: null, $id]
        );
    }

    public function deleteTemplateSubcategory(int $id): void
    {
        $this->db->query("DELETE FROM helpdesk_template_subcategories WHERE id=?", [$id]);
    }

    public function getTemplateItems(int $subcategoryId): array
    {
        return $this->db->fetchAll(
            "SELECT i.*, (SELECT COUNT(*) FROM helpdesk_template_fields f WHERE f.item_id = i.id) AS field_count
             FROM helpdesk_template_items i WHERE i.subcategory_id=? ORDER BY i.sort_order ASC, i.id ASC",
            [$subcategoryId]
        ) ?: [];
    }

    public function getTemplateItemById(int $id): ?array
    {
        return $this->db->fetch("SELECT * FROM helpdesk_template_items WHERE id=?", [$id]) ?: null;
    }

    public function createTemplateItem(int $subcategoryId, string $name, string $description, string $defaultPriority): int
    {
        $allowed = ['low', 'medium', 'high', 'urgent'];
        if (!in_array($defaultPriority, $allowed, true)) {
            $defaultPriority = 'medium';
        }
        $this->db->query(
            "INSERT INTO helpdesk_template_items (subcategory_id, name, description, default_priority) VALUES (?, ?, ?, ?)",
            [$subcategoryId, $name, $description ?: null, $defaultPriority]
        );
        return (int) $this->db->lastInsertId();
    }

    public function updateTemplateItem(int $id, string $name, string $description, string $defaultPriority): void
    {
        $allowed = ['low', 'medium', 'high', 'urgent'];
        if (!in_array($defaultPriority, $allowed, true)) {
            $defaultPriority = 'medium';
        }
        $this->db->query(
            "UPDATE helpdesk_template_items SET name=?, description=?, default_priority=? WHERE id=?",
            [$name, $description ?: null, $defaultPriority, $id]
        );
    }

    public function deleteTemplateItem(int $id): void
    {
        $this->db->query("DELETE FROM helpdesk_template_items WHERE id=?", [$id]);
    }

    public function getTemplateFields(int $itemId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM helpdesk_template_fields WHERE item_id=? ORDER BY sort_order ASC, id ASC",
            [$itemId]
        ) ?: [];
    }

    public function createTemplateField(int $itemId, string $label, string $type, string $options, bool $required): int
    {
        $allowedTypes = ['text', 'number', 'dropdown', 'multiselect', 'date', 'boolean', 'file'];
        if (!in_array($type, $allowedTypes, true)) {
            $type = 'text';
        }
        $this->db->query(
            "INSERT INTO helpdesk_template_fields (item_id, field_label, field_type, field_options, is_required) VALUES (?, ?, ?, ?, ?)",
            [$itemId, $label, $type, $options ?: null, $required ? 1 : 0]
        );
        return (int) $this->db->lastInsertId();
    }

    public function updateTemplateField(int $id, string $label, string $type, string $options, bool $required, int $sortOrder): void
    {
        $allowedTypes = ['text', 'number', 'dropdown', 'multiselect', 'date', 'boolean', 'file'];
        if (!in_array($type, $allowedTypes, true)) {
            $type = 'text';
        }
        $this->db->query(
            "UPDATE helpdesk_template_fields SET field_label=?, field_type=?, field_options=?, is_required=?, sort_order=? WHERE id=?",
            [$label, $type, $options ?: null, $required ? 1 : 0, $sortOrder, $id]
        );
    }

    public function deleteTemplateField(int $id): void
    {
        $this->db->query("DELETE FROM helpdesk_template_fields WHERE id=?", [$id]);
    }

    // -------------------------------------------------------------------------
    // Analytics methods
    // -------------------------------------------------------------------------

    public function getTicketAnalytics(): array
    {
        $total     = (int) ($this->db->fetchColumn("SELECT COUNT(*) FROM helpdesk_tickets") ?? 0);
        $open      = (int) ($this->db->fetchColumn("SELECT COUNT(*) FROM helpdesk_tickets WHERE status='open'") ?? 0);
        $inProg    = (int) ($this->db->fetchColumn("SELECT COUNT(*) FROM helpdesk_tickets WHERE status='in_progress'") ?? 0);
        $resolved  = (int) ($this->db->fetchColumn("SELECT COUNT(*) FROM helpdesk_tickets WHERE status='resolved'") ?? 0);
        $closed    = (int) ($this->db->fetchColumn("SELECT COUNT(*) FROM helpdesk_tickets WHERE status='closed'") ?? 0);

        $byPriority = $this->db->fetchAll(
            "SELECT priority, COUNT(*) AS cnt FROM helpdesk_tickets GROUP BY priority"
        ) ?: [];

        return [
            'total'       => $total,
            'open'        => $open,
            'in_progress' => $inProg,
            'resolved'    => $resolved,
            'closed'      => $closed,
            'by_priority' => $byPriority,
            'by_category' => [],
        ];
    }

    public function getLiveAnalytics(): array
    {
        $total   = (int) ($this->db->fetchColumn("SELECT COUNT(*) FROM helpdesk_live_sessions") ?? 0);
        $active  = (int) ($this->db->fetchColumn("SELECT COUNT(*) FROM helpdesk_live_sessions WHERE status IN ('open','waiting_agent')") ?? 0);
        $closed  = (int) ($this->db->fetchColumn("SELECT COUNT(*) FROM helpdesk_live_sessions WHERE status='closed'") ?? 0);
        $aiMsgs  = (int) ($this->db->fetchColumn("SELECT COUNT(*) FROM helpdesk_live_messages WHERE is_ai=1") ?? 0);
        $humanMsgs = (int) ($this->db->fetchColumn("SELECT COUNT(*) FROM helpdesk_live_messages WHERE is_ai=0 AND sender_type='agent'") ?? 0);

        return [
            'total_sessions' => $total,
            'active'         => $active,
            'closed'         => $closed,
            'ai_handled'     => $aiMsgs,
            'human_handled'  => $humanMsgs,
        ];
    }

    public function getAgentPerformance(): array
    {
        $agents = $this->getSupportAgents();
        $result = [];
        foreach ($agents as $agent) {
            $agentId = (int) $agent['id'];
            $handled = (int) ($this->db->fetchColumn(
                "SELECT COUNT(*) FROM helpdesk_tickets WHERE assigned_agent_id=? AND status IN ('resolved','closed')",
                [$agentId]
            ) ?? 0);
            $active = (int) ($this->db->fetchColumn(
                "SELECT COUNT(*) FROM helpdesk_tickets WHERE assigned_agent_id=? AND status IN ('open','in_progress','waiting_customer')",
                [$agentId]
            ) ?? 0);
            $avgHours = (float) ($this->db->fetchColumn(
                "SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) FROM helpdesk_tickets WHERE assigned_agent_id=? AND status IN ('resolved','closed')",
                [$agentId]
            ) ?? 0);
            $result[] = [
                'id'                   => $agentId,
                'name'                 => $agent['name'],
                'email'                => $agent['email'],
                'role'                 => $agent['role'],
                'tickets_handled'      => $handled,
                'active_tickets'       => $active,
                'avg_resolution_hours' => round($avgHours, 1),
            ];
        }
        return $result;
    }

    // -------------------------------------------------------------------------
    // Agent methods
    // -------------------------------------------------------------------------

    public function getAllAgents(): array
    {
        return $this->getSupportAgents();
    }

    public function getAgentById(int $id): ?array
    {
        $user = $this->db->fetch("SELECT id, name, email, role FROM users WHERE id=?", [$id]) ?: null;
        if (!$user) {
            return null;
        }
        $roles = array_values(array_filter(array_map('trim', explode(',', (string) ($user['role'] ?? '')))));
        if (in_array('admin', $roles, true) || in_array('super_admin', $roles, true) || in_array('support', $roles, true)) {
            return $user;
        }
        return null;
    }

    // -------------------------------------------------------------------------
    // Customer methods
    // -------------------------------------------------------------------------

    public function getCustomers(int $limit = 50): array
    {
        return $this->db->fetchAll(
            "SELECT u.id, u.name, u.email,
                    COUNT(DISTINCT t.id) AS ticket_count,
                    MAX(t.created_at) AS last_ticket_at,
                    MAX(ls.created_at) AS last_chat_at
             FROM users u
             LEFT JOIN helpdesk_tickets t ON t.user_id = u.id
             LEFT JOIN helpdesk_live_sessions ls ON ls.user_id = u.id
             GROUP BY u.id, u.name, u.email
             HAVING ticket_count > 0 OR COUNT(DISTINCT ls.id) > 0
             ORDER BY GREATEST(COALESCE(MAX(t.created_at),'1970-01-01'), COALESCE(MAX(ls.created_at),'1970-01-01')) DESC
             LIMIT ?",
            [$limit]
        ) ?: [];
    }

    public function getCustomerById(int $userId): ?array
    {
        return $this->db->fetch("SELECT id, name, email, role, created_at FROM users WHERE id=?", [$userId]) ?: null;
    }

    public function getCustomerTickets(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM helpdesk_tickets WHERE user_id=? ORDER BY created_at DESC LIMIT 20",
            [$userId]
        ) ?: [];
    }

    public function getCustomerLiveSessions(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM helpdesk_live_sessions WHERE user_id=? ORDER BY created_at DESC LIMIT 20",
            [$userId]
        ) ?: [];
    }

    // -------------------------------------------------------------------------
    // Workflow methods
    // -------------------------------------------------------------------------

    public function getWorkflows(): array
    {
        return $this->db->fetchAll("SELECT * FROM helpdesk_workflows ORDER BY id ASC") ?: [];
    }

    public function getWorkflowById(int $id): ?array
    {
        return $this->db->fetch("SELECT * FROM helpdesk_workflows WHERE id=?", [$id]) ?: null;
    }

    public function createWorkflow(string $name, string $description, array $conditions, array $actions): int
    {
        $this->db->query(
            "INSERT INTO helpdesk_workflows (name, description, conditions, actions) VALUES (?, ?, ?, ?)",
            [$name, $description ?: null, json_encode($conditions), json_encode($actions)]
        );
        return (int) $this->db->lastInsertId();
    }

    public function updateWorkflow(int $id, string $name, string $description, array $conditions, array $actions, bool $isActive): void
    {
        $this->db->query(
            "UPDATE helpdesk_workflows SET name=?, description=?, conditions=?, actions=?, is_active=? WHERE id=?",
            [$name, $description ?: null, json_encode($conditions), json_encode($actions), $isActive ? 1 : 0, $id]
        );
    }

    public function deleteWorkflow(int $id): void
    {
        $this->db->query("DELETE FROM helpdesk_workflows WHERE id=?", [$id]);
    }

    // -------------------------------------------------------------------------
    // SLA methods
    // -------------------------------------------------------------------------

    public function getSlaRules(): array
    {
        return $this->db->fetchAll("SELECT * FROM helpdesk_sla_rules ORDER BY CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 WHEN 'low' THEN 4 ELSE 5 END") ?: [];
    }

    public function upsertSlaRule(string $priority, int $firstResponseHours, int $resolutionHours): void
    {
        $allowed = ['low', 'medium', 'high', 'urgent'];
        if (!in_array($priority, $allowed, true)) {
            return;
        }
        $this->db->query(
            "INSERT INTO helpdesk_sla_rules (priority, first_response_hours, resolution_hours)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE first_response_hours=VALUES(first_response_hours), resolution_hours=VALUES(resolution_hours)",
            [$priority, max(1, $firstResponseHours), max(1, $resolutionHours)]
        );
    }

    // -------------------------------------------------------------------------
    // API key methods
    // -------------------------------------------------------------------------

    public function getApiKeys(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM helpdesk_api_keys WHERE user_id=? ORDER BY created_at DESC",
            [$userId]
        ) ?: [];
    }

    public function createApiKey(int $userId, string $name): string
    {
        $key = bin2hex(random_bytes(32));
        $this->db->query(
            "INSERT INTO helpdesk_api_keys (user_id, name, api_key) VALUES (?, ?, ?)",
            [$userId, $name, $key]
        );
        return $key;
    }

    public function revokeApiKey(int $id, int $userId): void
    {
        $this->db->query(
            "UPDATE helpdesk_api_keys SET is_active=0 WHERE id=? AND user_id=?",
            [$id, $userId]
        );
    }

    // -------------------------------------------------------------------------
    // Widget settings
    // -------------------------------------------------------------------------

    public function getWidgetSettings(): array
    {
        $row = $this->db->fetch("SELECT * FROM helpdesk_widget_settings WHERE id=1") ?: null;
        if (!$row) {
            return [
                'greeting_text' => 'Hi! How can we help you today?',
                'primary_color' => '#3b82f6',
                'position'      => 'bottom-right',
                'widget_title'  => 'Support',
                'is_active'     => 1,
            ];
        }
        return $row;
    }

    public function saveWidgetSettings(string $greetingText, string $primaryColor, string $position, string $title): void
    {
        $allowedPositions = ['bottom-right', 'bottom-left'];
        if (!in_array($position, $allowedPositions, true)) {
            $position = 'bottom-right';
        }
        $exists = $this->db->fetchColumn("SELECT COUNT(*) FROM helpdesk_widget_settings WHERE id=1");
        if ($exists) {
            $this->db->query(
                "UPDATE helpdesk_widget_settings SET greeting_text=?, primary_color=?, position=?, widget_title=? WHERE id=1",
                [$greetingText, $primaryColor, $position, $title]
            );
        } else {
            $this->db->query(
                "INSERT INTO helpdesk_widget_settings (id, greeting_text, primary_color, position, widget_title) VALUES (1, ?, ?, ?, ?)",
                [$greetingText, $primaryColor, $position, $title]
            );
        }
    }

    // -------------------------------------------------------------------------
    // Webhook methods
    // -------------------------------------------------------------------------

    public function getWebhooks(): array
    {
        return $this->db->fetchAll("SELECT * FROM helpdesk_webhooks ORDER BY id ASC") ?: [];
    }

    public function createWebhook(string $name, string $url, array $events): int
    {
        $this->db->query(
            "INSERT INTO helpdesk_webhooks (name, url, events) VALUES (?, ?, ?)",
            [$name, $url, json_encode($events)]
        );
        return (int) $this->db->lastInsertId();
    }

    public function deleteWebhook(int $id): void
    {
        $this->db->query("DELETE FROM helpdesk_webhooks WHERE id=?", [$id]);
    }
}
