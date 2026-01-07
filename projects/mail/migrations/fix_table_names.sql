-- Migration: Fix table names that have double prefixes
-- Description: Rename tables with incorrect names
-- Date: 2026-01-07

-- Rename mail_mail_user_roles to mail_user_roles if exists
RENAME TABLE IF EXISTS `mail_mail_user_roles` TO `mail_user_roles`;

-- Rename mail_mail_folders to mail_folders if exists
RENAME TABLE IF EXISTS `mail_mail_folders` TO `mail_folders`;

-- Create mail_folders table if it doesn't exist
CREATE TABLE IF NOT EXISTS `mail_folders` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `mailbox_id` INT UNSIGNED NOT NULL,
    `folder_name` VARCHAR(100) NOT NULL,
    `folder_type` ENUM('inbox', 'sent', 'drafts', 'trash', 'spam', 'archive', 'custom') NOT NULL,
    `parent_folder_id` INT UNSIGNED NULL,
    `message_count` INT UNSIGNED DEFAULT 0,
    `unread_count` INT UNSIGNED DEFAULT 0,
    `sort_order` INT UNSIGNED DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`mailbox_id`) REFERENCES `mail_mailboxes`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`parent_folder_id`) REFERENCES `mail_folders`(`id`) ON DELETE CASCADE,
    INDEX `idx_mailbox_id` (`mailbox_id`),
    INDEX `idx_folder_type` (`folder_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create mail_messages table if it doesn't exist
CREATE TABLE IF NOT EXISTS `mail_messages` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `mailbox_id` INT UNSIGNED NOT NULL,
    `folder_id` INT UNSIGNED NOT NULL,
    `message_type` ENUM('received', 'sent', 'draft') DEFAULT 'received',
    `message_id` VARCHAR(255) NULL COMMENT 'RFC 822 Message-ID',
    `in_reply_to` VARCHAR(255) NULL,
    `from_email` VARCHAR(255) NOT NULL,
    `from_name` VARCHAR(255) NULL,
    `to_email` TEXT NOT NULL,
    `cc_email` TEXT NULL,
    `bcc_email` TEXT NULL,
    `subject` VARCHAR(500) NULL,
    `body_text` LONGTEXT NULL,
    `body_html` LONGTEXT NULL,
    `size` INT UNSIGNED DEFAULT 0,
    `is_read` TINYINT(1) DEFAULT 0,
    `is_starred` TINYINT(1) DEFAULT 0,
    `is_flagged` TINYINT(1) DEFAULT 0,
    `priority` ENUM('low', 'normal', 'high') DEFAULT 'normal',
    `read_at` TIMESTAMP NULL,
    `sent_at` TIMESTAMP NULL,
    `received_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`mailbox_id`) REFERENCES `mail_mailboxes`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`folder_id`) REFERENCES `mail_folders`(`id`) ON DELETE CASCADE,
    INDEX `idx_mailbox_id` (`mailbox_id`),
    INDEX `idx_folder_id` (`folder_id`),
    INDEX `idx_message_id` (`message_id`),
    INDEX `idx_is_read` (`is_read`),
    INDEX `idx_received_at` (`received_at`),
    FULLTEXT INDEX `ft_search` (`subject`, `body_text`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create mail_attachments table if it doesn't exist
CREATE TABLE IF NOT EXISTS `mail_attachments` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `message_id` BIGINT UNSIGNED NULL,
    `queue_id` BIGINT UNSIGNED NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `file_size` INT UNSIGNED NOT NULL,
    `mime_type` VARCHAR(100) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_message_id` (`message_id`),
    INDEX `idx_queue_id` (`queue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create mail_queue table if it doesn't exist
CREATE TABLE IF NOT EXISTS `mail_queue` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `mailbox_id` INT UNSIGNED NULL,
    `from_email` VARCHAR(255) NOT NULL,
    `from_name` VARCHAR(255) NULL,
    `to_email` TEXT NOT NULL,
    `cc_email` TEXT NULL,
    `bcc_email` TEXT NULL,
    `reply_to_email` VARCHAR(255) NULL,
    `reply_to_message_id` BIGINT UNSIGNED NULL,
    `subject` VARCHAR(500) NULL,
    `body_html` LONGTEXT NULL,
    `body_text` LONGTEXT NULL,
    `status` ENUM('pending', 'processing', 'sent', 'failed', 'cancelled') DEFAULT 'pending',
    `priority` ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    `attempts` INT UNSIGNED DEFAULT 0,
    `max_attempts` INT UNSIGNED DEFAULT 3,
    `error_message` TEXT NULL,
    `scheduled_at` TIMESTAMP NULL,
    `sent_at` TIMESTAMP NULL,
    `failed_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`mailbox_id`) REFERENCES `mail_mailboxes`(`id`) ON DELETE SET NULL,
    INDEX `idx_mailbox_id` (`mailbox_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_scheduled_at` (`scheduled_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create mail_logs table if it doesn't exist
CREATE TABLE IF NOT EXISTS `mail_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `mailbox_id` INT UNSIGNED NULL,
    `log_type` ENUM('send', 'receive', 'bounce', 'spam', 'error') NOT NULL,
    `email_from` VARCHAR(255) NULL,
    `email_to` VARCHAR(255) NULL,
    `subject` VARCHAR(500) NULL,
    `status` VARCHAR(50) NULL,
    `message` TEXT NULL,
    `ip_address` VARCHAR(45) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_mailbox_id` (`mailbox_id`),
    INDEX `idx_log_type` (`log_type`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
