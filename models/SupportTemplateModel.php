<?php
/**
 * Support Template Model
 *
 * Handles the dynamic ticket system:
 *  - Template Groups (departments)
 *  - Dynamic Categories (issue types)
 *  - Versioned Form Templates (JSON schema, append-only)
 *  - Dynamic Tickets + Attachments
 *
 * @package Models
 */

namespace Models;

use Core\Database;
use Core\Auth;

class SupportTemplateModel
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
        $this->db->query("CREATE TABLE IF NOT EXISTS `support_template_groups` (
            `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `name`        VARCHAR(100) NOT NULL,
            `slug`        VARCHAR(100) NOT NULL,
            `description` TEXT NULL,
            `icon`        VARCHAR(50)  NOT NULL DEFAULT 'users',
            `color`       VARCHAR(20)  NOT NULL DEFAULT '#00f0ff',
            `sort_order`  INT          NOT NULL DEFAULT 0,
            `is_active`   TINYINT(1)   NOT NULL DEFAULT 1,
            `created_at`  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
            `updated_at`  TIMESTAMP    NULL ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uq_stg_slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS `support_dyn_categories` (
            `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `group_id`    INT UNSIGNED NOT NULL,
            `name`        VARCHAR(150) NOT NULL,
            `slug`        VARCHAR(150) NOT NULL,
            `description` TEXT NULL,
            `icon`        VARCHAR(50)  NOT NULL DEFAULT 'tag',
            `sort_order`  INT          NOT NULL DEFAULT 0,
            `is_active`   TINYINT(1)   NOT NULL DEFAULT 1,
            `created_at`  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
            `updated_at`  TIMESTAMP    NULL ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uq_sdc_slug` (`group_id`, `slug`),
            KEY `idx_sdc_group` (`group_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS `support_dyn_templates` (
            `id`          INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
            `category_id` INT UNSIGNED  NOT NULL,
            `version`     SMALLINT UNSIGNED NOT NULL DEFAULT 1,
            `schema_json` LONGTEXT      NOT NULL,
            `is_active`   TINYINT(1)    NOT NULL DEFAULT 1,
            `created_by`  INT UNSIGNED  NULL,
            `created_at`  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
            KEY `idx_sdt_cat`        (`category_id`),
            KEY `idx_sdt_cat_active` (`category_id`, `is_active`),
            UNIQUE KEY `uq_sdt_cat_ver` (`category_id`, `version`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS `support_dyn_tickets` (
            `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id`        INT UNSIGNED NOT NULL,
            `template_id`    INT UNSIGNED NOT NULL,
            `group_id`       INT UNSIGNED NOT NULL,
            `category_id`    INT UNSIGNED NOT NULL,
            `subject`        VARCHAR(255) NOT NULL,
            `submitted_data` LONGTEXT     NOT NULL,
            `priority`       ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
            `status`         ENUM('open','in_progress','waiting_customer','resolved','closed') NOT NULL DEFAULT 'open',
            `assigned_to`    INT UNSIGNED NULL,
            `last_reply_at`  DATETIME     NULL,
            `closed_at`      DATETIME     NULL,
            `created_at`     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
            `updated_at`     TIMESTAMP    NULL ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_sdt2_user`     (`user_id`),
            INDEX `idx_sdt2_status`   (`status`),
            INDEX `idx_sdt2_group`    (`group_id`),
            INDEX `idx_sdt2_cat`      (`category_id`),
            INDEX `idx_sdt2_assigned` (`assigned_to`),
            INDEX `idx_sdt2_created`  (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS `support_dyn_attachments` (
            `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `ticket_id`  INT UNSIGNED NOT NULL,
            `field_name` VARCHAR(100) NOT NULL,
            `file_name`  VARCHAR(255) NOT NULL,
            `file_path`  VARCHAR(500) NOT NULL,
            `mime_type`  VARCHAR(100) NULL,
            `file_size`  INT UNSIGNED NULL,
            `created_at` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
            KEY `idx_sda_ticket` (`ticket_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS `support_dyn_messages` (
            `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `ticket_id`   INT UNSIGNED NOT NULL,
            `sender_type` ENUM('user','agent') NOT NULL,
            `sender_id`   INT UNSIGNED NOT NULL,
            `message`     TEXT         NOT NULL,
            `is_internal` TINYINT(1)   NOT NULL DEFAULT 0,
            `created_at`  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_sdm_ticket` (`ticket_id`),
            INDEX `idx_sdm_sender` (`sender_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    // -------------------------------------------------------------------------
    // Template Groups
    // -------------------------------------------------------------------------

    public function getAllGroups(bool $activeOnly = false): array
    {
        $where = $activeOnly ? 'WHERE is_active = 1' : '';
        return $this->db->fetchAll(
            "SELECT * FROM support_template_groups {$where} ORDER BY sort_order ASC, name ASC"
        ) ?: [];
    }

    public function getGroupById(int $id): ?array
    {
        return $this->db->fetch("SELECT * FROM support_template_groups WHERE id = ?", [$id]) ?: null;
    }

    public function createGroup(array $data): int
    {
        $slug = $this->makeSlug($data['name'] ?? '', 'support_template_groups');
        $this->db->insert('support_template_groups', [
            'name'        => $data['name'],
            'slug'        => $slug,
            'description' => $data['description'] ?? null,
            'icon'        => $data['icon'] ?? 'users',
            'color'       => $data['color'] ?? '#00f0ff',
            'sort_order'  => (int) ($data['sort_order'] ?? 0),
            'is_active'   => 1,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updateGroup(int $id, array $data): void
    {
        $fields = array_intersect_key($data, array_flip(['name', 'description', 'icon', 'color', 'sort_order', 'is_active']));
        if (isset($fields['sort_order'])) {
            $fields['sort_order'] = (int) $fields['sort_order'];
        }
        if (isset($fields['is_active'])) {
            $fields['is_active'] = (int) $fields['is_active'];
        }
        if (!empty($fields)) {
            $this->db->update('support_template_groups', $fields, 'id = ?', [$id]);
        }
    }

    public function deleteGroup(int $id): void
    {
        $this->db->delete('support_dyn_categories', 'group_id = ?', [$id]);
        $this->db->delete('support_template_groups', 'id = ?', [$id]);
    }

    // -------------------------------------------------------------------------
    // Dynamic Categories
    // -------------------------------------------------------------------------

    public function getCategoriesByGroup(int $groupId, bool $activeOnly = false): array
    {
        $where  = $activeOnly ? 'AND is_active = 1' : '';
        return $this->db->fetchAll(
            "SELECT c.*, g.name AS group_name FROM support_dyn_categories c
             LEFT JOIN support_template_groups g ON g.id = c.group_id
             WHERE c.group_id = ? {$where}
             ORDER BY c.sort_order ASC, c.name ASC",
            [$groupId]
        ) ?: [];
    }

    public function getAllCategories(bool $activeOnly = false): array
    {
        $where = $activeOnly ? 'WHERE c.is_active = 1' : '';
        return $this->db->fetchAll(
            "SELECT c.*, g.name AS group_name FROM support_dyn_categories c
             LEFT JOIN support_template_groups g ON g.id = c.group_id
             {$where}
             ORDER BY g.sort_order ASC, c.sort_order ASC, c.name ASC"
        ) ?: [];
    }

    public function getCategoryById(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT c.*, g.name AS group_name FROM support_dyn_categories c
             LEFT JOIN support_template_groups g ON g.id = c.group_id
             WHERE c.id = ?",
            [$id]
        ) ?: null;
    }

    public function createCategory(array $data): int
    {
        $groupId = (int) $data['group_id'];
        $slug    = $this->makeSlugScoped($data['name'] ?? '', 'support_dyn_categories', 'group_id', $groupId);
        $this->db->insert('support_dyn_categories', [
            'group_id'    => $groupId,
            'name'        => $data['name'],
            'slug'        => $slug,
            'description' => $data['description'] ?? null,
            'icon'        => $data['icon'] ?? 'tag',
            'sort_order'  => (int) ($data['sort_order'] ?? 0),
            'is_active'   => 1,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updateCategory(int $id, array $data): void
    {
        $fields = array_intersect_key($data, array_flip(['name', 'description', 'icon', 'sort_order', 'is_active']));
        if (isset($fields['sort_order'])) {
            $fields['sort_order'] = (int) $fields['sort_order'];
        }
        if (isset($fields['is_active'])) {
            $fields['is_active'] = (int) $fields['is_active'];
        }
        if (!empty($fields)) {
            $this->db->update('support_dyn_categories', $fields, 'id = ?', [$id]);
        }
    }

    public function deleteCategory(int $id): void
    {
        // Deactivate all template versions for this category
        $this->db->query("UPDATE support_dyn_templates SET is_active = 0 WHERE category_id = ?", [$id]);
        $this->db->delete('support_dyn_categories', 'id = ?', [$id]);
    }

    // -------------------------------------------------------------------------
    // Versioned Templates
    // -------------------------------------------------------------------------

    /**
     * Get the currently-active template for a category.
     */
    public function getActiveTemplate(int $categoryId): ?array
    {
        return $this->db->fetch(
            "SELECT t.*, c.name AS category_name, g.name AS group_name
             FROM support_dyn_templates t
             LEFT JOIN support_dyn_categories c ON c.id = t.category_id
             LEFT JOIN support_template_groups g ON g.id = c.group_id
             WHERE t.category_id = ? AND t.is_active = 1
             LIMIT 1",
            [$categoryId]
        ) ?: null;
    }

    /**
     * Get a specific template version by its primary key.
     */
    public function getTemplateById(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT t.*, c.name AS category_name
             FROM support_dyn_templates t
             LEFT JOIN support_dyn_categories c ON c.id = t.category_id
             WHERE t.id = ?",
            [$id]
        ) ?: null;
    }

    /**
     * List all versions for a category (newest first).
     */
    public function getTemplateHistory(int $categoryId): array
    {
        return $this->db->fetchAll(
            "SELECT t.id, t.version, t.is_active, t.created_at, u.name AS created_by_name
             FROM support_dyn_templates t
             LEFT JOIN users u ON u.id = t.created_by
             WHERE t.category_id = ?
             ORDER BY t.version DESC",
            [$categoryId]
        ) ?: [];
    }

    /**
     * Save a new template version (append-only).
     * Deactivates previous active version and bumps version number.
     */
    public function saveTemplate(int $categoryId, array $schema, ?int $createdBy = null): int
    {
        $row = $this->db->fetch(
            "SELECT MAX(version) AS v FROM support_dyn_templates WHERE category_id = ?",
            [$categoryId]
        );
        $nextVersion      = (int) ($row['v'] ?? 0) + 1;
        $schema['version'] = $nextVersion;

        // Deactivate old version(s)
        $this->db->query(
            "UPDATE support_dyn_templates SET is_active = 0 WHERE category_id = ?",
            [$categoryId]
        );

        $this->db->insert('support_dyn_templates', [
            'category_id' => $categoryId,
            'version'     => $nextVersion,
            'schema_json' => json_encode($schema, JSON_UNESCAPED_UNICODE),
            'is_active'   => 1,
            'created_by'  => $createdBy,
        ]);

        return (int) $this->db->lastInsertId();
    }

    // -------------------------------------------------------------------------
    // Dynamic Tickets
    // -------------------------------------------------------------------------

    public function createTicket(array $data): int
    {
        $this->db->insert('support_dyn_tickets', [
            'user_id'        => $data['user_id'],
            'template_id'    => $data['template_id'],
            'group_id'       => $data['group_id'],
            'category_id'    => $data['category_id'],
            'subject'        => $data['subject'],
            'submitted_data' => json_encode($data['submitted_data'] ?? [], JSON_UNESCAPED_UNICODE),
            'priority'       => $data['priority'] ?? 'medium',
            'status'         => 'open',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getTicketById(int $id): ?array
    {
        $row = $this->db->fetch(
            "SELECT t.*,
                    u.name  AS user_name,
                    u.email AS user_email,
                    a.name  AS agent_name,
                    cat.name AS category_name,
                    grp.name AS group_name
             FROM support_dyn_tickets t
             LEFT JOIN users u   ON u.id  = t.user_id
             LEFT JOIN users a   ON a.id  = t.assigned_to
             LEFT JOIN support_dyn_categories cat ON cat.id = t.category_id
             LEFT JOIN support_template_groups grp ON grp.id = t.group_id
             WHERE t.id = ?",
            [$id]
        );
        return $row ?: null;
    }

    public function getTicketsByUser(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT t.*,
                    cat.name AS category_name,
                    grp.name AS group_name
             FROM support_dyn_tickets t
             LEFT JOIN support_dyn_categories cat ON cat.id = t.category_id
             LEFT JOIN support_template_groups grp ON grp.id = t.group_id
             WHERE t.user_id = ?
             ORDER BY t.updated_at DESC, t.created_at DESC",
            [$userId]
        ) ?: [];
    }

    public function getAllTickets(array $filters = []): array
    {
        $where  = ['1=1'];
        $params = [];

        foreach (['status', 'priority', 'group_id', 'category_id', 'assigned_to'] as $col) {
            if (!empty($filters[$col])) {
                $where[]  = "t.{$col} = ?";
                $params[] = $filters[$col];
            }
        }
        if (!empty($filters['user_id'])) {
            $where[]  = 't.user_id = ?';
            $params[] = (int) $filters['user_id'];
        }
        if (!empty($filters['query'])) {
            $where[]  = '(t.subject LIKE ? OR u.name LIKE ? OR u.email LIKE ?)';
            $q        = '%' . $filters['query'] . '%';
            $params   = array_merge($params, [$q, $q, $q]);
        }
        if (!empty($filters['from_date'])) {
            $where[]  = 'DATE(t.created_at) >= ?';
            $params[] = $filters['from_date'];
        }
        if (!empty($filters['to_date'])) {
            $where[]  = 'DATE(t.created_at) <= ?';
            $params[] = $filters['to_date'];
        }

        $whereClause = implode(' AND ', $where);
        return $this->db->fetchAll(
            "SELECT t.*,
                    u.name  AS user_name,
                    u.email AS user_email,
                    a.name  AS agent_name,
                    cat.name AS category_name,
                    grp.name AS group_name
             FROM support_dyn_tickets t
             LEFT JOIN users u   ON u.id  = t.user_id
             LEFT JOIN users a   ON a.id  = t.assigned_to
             LEFT JOIN support_dyn_categories cat ON cat.id = t.category_id
             LEFT JOIN support_template_groups grp ON grp.id = t.group_id
             WHERE {$whereClause}
             ORDER BY
               FIELD(t.status,'open','in_progress','waiting_customer','resolved','closed'),
               t.updated_at DESC, t.created_at DESC",
            $params
        ) ?: [];
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
        $this->db->update('support_dyn_tickets', $data, 'id = ?', [$id]);
    }

    public function getTicketStats(): array
    {
        $rows  = $this->db->fetchAll("SELECT status, COUNT(*) AS cnt FROM support_dyn_tickets GROUP BY status") ?: [];
        $stats = ['open' => 0, 'in_progress' => 0, 'waiting_customer' => 0, 'resolved' => 0, 'closed' => 0, 'total' => 0];
        foreach ($rows as $row) {
            $stats[$row['status']] = (int) $row['cnt'];
            $stats['total']       += (int) $row['cnt'];
        }
        return $stats;
    }

    public function getTicketStatsByUser(int $userId): array
    {
        $rows  = $this->db->fetchAll(
            "SELECT status, COUNT(*) AS cnt FROM support_dyn_tickets WHERE user_id = ? GROUP BY status",
            [$userId]
        ) ?: [];
        $stats = ['open' => 0, 'in_progress' => 0, 'waiting_customer' => 0, 'resolved' => 0, 'closed' => 0, 'total' => 0];
        foreach ($rows as $row) {
            $stats[$row['status']] = (int) $row['cnt'];
            $stats['total']       += (int) $row['cnt'];
        }
        return $stats;
    }

    // -------------------------------------------------------------------------
    // Ticket Messages
    // -------------------------------------------------------------------------

    public function addTicketMessage(int $ticketId, string $senderType, int $senderId, string $message, bool $isInternal = false): void
    {
        $this->db->insert('support_dyn_messages', [
            'ticket_id'   => $ticketId,
            'sender_type' => $senderType,
            'sender_id'   => $senderId,
            'message'     => $message,
            'is_internal' => $isInternal ? 1 : 0,
        ]);

        $statusData = ['last_reply_at' => date('Y-m-d H:i:s')];
        if ($senderType === 'user') {
            $statusData['status'] = 'open';
        } elseif ($senderType === 'agent' && !$isInternal) {
            $statusData['status'] = 'in_progress';
        }
        $this->db->update('support_dyn_tickets', $statusData, 'id = ?', [$ticketId]);
    }

    public function getTicketMessages(int $ticketId, bool $includeInternal = false): array
    {
        $clause = $includeInternal ? '' : 'AND m.is_internal = 0';
        return $this->db->fetchAll(
            "SELECT m.*, u.name AS sender_name
             FROM support_dyn_messages m
             LEFT JOIN users u ON u.id = m.sender_id
             WHERE m.ticket_id = ? {$clause}
             ORDER BY m.created_at ASC",
            [$ticketId]
        ) ?: [];
    }

    // -------------------------------------------------------------------------
    // Attachments
    // -------------------------------------------------------------------------

    public function saveAttachment(int $ticketId, string $fieldName, string $fileName, string $filePath, string $mimeType, int $fileSize): int
    {
        $this->db->insert('support_dyn_attachments', [
            'ticket_id'  => $ticketId,
            'field_name' => $fieldName,
            'file_name'  => $fileName,
            'file_path'  => $filePath,
            'mime_type'  => $mimeType,
            'file_size'  => $fileSize,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getAttachmentsByTicket(int $ticketId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM support_dyn_attachments WHERE ticket_id = ? ORDER BY created_at ASC",
            [$ticketId]
        ) ?: [];
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeSlug(string $name, string $table): string
    {
        $base = preg_replace('/[^a-z0-9]+/', '-', strtolower(trim($name)));
        $base = trim($base, '-') ?: 'item';
        $slug = $base;
        $i    = 1;
        while ($this->db->fetch("SELECT id FROM `{$table}` WHERE slug = ?", [$slug])) {
            $slug = $base . '-' . (++$i);
        }
        return $slug;
    }

    private function makeSlugScoped(string $name, string $table, string $scopeCol, int $scopeVal): string
    {
        $base = preg_replace('/[^a-z0-9]+/', '-', strtolower(trim($name)));
        $base = trim($base, '-') ?: 'item';
        $slug = $base;
        $i    = 1;
        while ($this->db->fetch("SELECT id FROM `{$table}` WHERE slug = ? AND `{$scopeCol}` = ?", [$slug, $scopeVal])) {
            $slug = $base . '-' . (++$i);
        }
        return $slug;
    }
}
