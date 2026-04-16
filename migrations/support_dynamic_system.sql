-- ============================================================
-- Dynamic Support Ticket System — Migration
-- Adds: template groups, versioned templates, updated tickets,
-- and ticket attachments table.
-- The legacy support_template_categories / support_template_items
-- tables are left untouched for backward compatibility.
-- ============================================================

-- Template Groups (departments / teams)
CREATE TABLE IF NOT EXISTS `support_template_groups` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dynamic Categories (issue types within a group)
CREATE TABLE IF NOT EXISTS `support_dyn_categories` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Versioned Form Templates (one active per category, append-only)
CREATE TABLE IF NOT EXISTS `support_dyn_templates` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dynamic Tickets (stores submitted JSON data, version-locked template ref)
CREATE TABLE IF NOT EXISTS `support_dyn_tickets` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- File attachments per ticket field
CREATE TABLE IF NOT EXISTS `support_dyn_attachments` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ticket_id`  INT UNSIGNED NOT NULL,
    `field_name` VARCHAR(100) NOT NULL,
    `file_name`  VARCHAR(255) NOT NULL,
    `file_path`  VARCHAR(500) NOT NULL,
    `mime_type`  VARCHAR(100) NULL,
    `file_size`  INT UNSIGNED NULL,
    `created_at` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_sda_ticket` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ticket messages for dynamic tickets
CREATE TABLE IF NOT EXISTS `support_dyn_messages` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ticket_id`   INT UNSIGNED NOT NULL,
    `sender_type` ENUM('user','agent') NOT NULL,
    `sender_id`   INT UNSIGNED NOT NULL,
    `message`     TEXT         NOT NULL,
    `is_internal` TINYINT(1)   NOT NULL DEFAULT 0,
    `created_at`  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_sdm_ticket` (`ticket_id`),
    INDEX `idx_sdm_sender` (`sender_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
