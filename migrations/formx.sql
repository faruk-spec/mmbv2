-- FormX: Form Builder tables
-- Run this migration to enable the FormX admin panel feature.

CREATE TABLE IF NOT EXISTS `formx_forms` (
    `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`          INT UNSIGNED NULL COMMENT 'Owner admin/user (NULL = system form)',
    `title`            VARCHAR(255) NOT NULL,
    `slug`             VARCHAR(255) NOT NULL,
    `description`      TEXT NULL,
    `fields`           JSON NOT NULL COMMENT 'Array of field definitions',
    `settings`         JSON NOT NULL COMMENT 'success_message, redirect_url, notify_email, etc.',
    `status`           ENUM('active','inactive','draft') NOT NULL DEFAULT 'draft',
    `submissions_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_slug` (`slug`),
    KEY `idx_status` (`status`),
    KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `formx_submissions` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `form_id`    INT UNSIGNED NOT NULL,
    `data`       JSON NOT NULL COMMENT 'Key-value pairs of submitted field data',
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(500) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_form_id` (`form_id`),
    CONSTRAINT `fk_formx_submissions_form`
        FOREIGN KEY (`form_id`) REFERENCES `formx_forms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
