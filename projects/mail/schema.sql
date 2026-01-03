-- Mail Hosting Server Database Schema
-- Database: mail_server
-- Description: Complete mail hosting server with custom domain support

-- Domains table - Custom domains added by users
CREATE TABLE IF NOT EXISTS `domains` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL COMMENT 'Owner of the domain',
    `domain_name` VARCHAR(255) NOT NULL UNIQUE,
    `is_verified` TINYINT(1) DEFAULT 0,
    `verification_token` VARCHAR(64) NULL,
    `verification_method` ENUM('txt', 'cname', 'mx') DEFAULT 'txt',
    `is_active` TINYINT(1) DEFAULT 1,
    `ssl_enabled` TINYINT(1) DEFAULT 0,
    `ssl_certificate` TEXT NULL,
    `ssl_private_key` TEXT NULL,
    `catch_all_enabled` TINYINT(1) DEFAULT 0,
    `catch_all_mailbox` VARCHAR(255) NULL,
    `max_mailboxes` INT UNSIGNED DEFAULT 10,
    `storage_quota` BIGINT UNSIGNED DEFAULT 5368709120 COMMENT 'Bytes, default 5GB',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    `verified_at` TIMESTAMP NULL,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_domain_name` (`domain_name`),
    INDEX `idx_is_active` (`is_active`),
    INDEX `idx_is_verified` (`is_verified`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DNS Records table - MX, SPF, DKIM, DMARC records
CREATE TABLE IF NOT EXISTS `dns_records` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `domain_id` INT UNSIGNED NOT NULL,
    `record_type` ENUM('MX', 'TXT', 'CNAME', 'A', 'AAAA', 'SPF', 'DKIM', 'DMARC') NOT NULL,
    `record_name` VARCHAR(255) NOT NULL,
    `record_value` TEXT NOT NULL,
    `priority` INT UNSIGNED NULL COMMENT 'For MX records',
    `ttl` INT UNSIGNED DEFAULT 3600,
    `is_verified` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `verified_at` TIMESTAMP NULL,
    FOREIGN KEY (`domain_id`) REFERENCES `domains`(`id`) ON DELETE CASCADE,
    INDEX `idx_domain_id` (`domain_id`),
    INDEX `idx_record_type` (`record_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mailboxes table - Email accounts
CREATE TABLE IF NOT EXISTS `mailboxes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `domain_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL COMMENT 'MMB user who owns this mailbox',
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `username` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NOT NULL COMMENT 'Hashed password for SMTP/IMAP auth',
    `display_name` VARCHAR(255) NULL,
    `storage_quota` BIGINT UNSIGNED DEFAULT 1073741824 COMMENT 'Bytes, default 1GB',
    `storage_used` BIGINT UNSIGNED DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `is_admin` TINYINT(1) DEFAULT 0 COMMENT 'Domain admin',
    `receive_enabled` TINYINT(1) DEFAULT 1,
    `send_enabled` TINYINT(1) DEFAULT 1,
    `daily_send_limit` INT UNSIGNED DEFAULT 300,
    `daily_send_count` INT UNSIGNED DEFAULT 0,
    `last_send_reset` DATE NULL,
    `last_login_at` TIMESTAMP NULL,
    `last_login_ip` VARCHAR(45) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`domain_id`) REFERENCES `domains`(`id`) ON DELETE CASCADE,
    INDEX `idx_domain_id` (`domain_id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_email` (`email`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email Aliases table - Email forwarding and aliases
CREATE TABLE IF NOT EXISTS `aliases` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `domain_id` INT UNSIGNED NOT NULL,
    `source_email` VARCHAR(255) NOT NULL,
    `destination_emails` TEXT NOT NULL COMMENT 'Comma-separated list of destination emails',
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`domain_id`) REFERENCES `domains`(`id`) ON DELETE CASCADE,
    INDEX `idx_domain_id` (`domain_id`),
    INDEX `idx_source_email` (`source_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mail Folders table - Inbox, Sent, Drafts, Trash, Custom folders
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
    FOREIGN KEY (`mailbox_id`) REFERENCES `mailboxes`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`parent_folder_id`) REFERENCES `mail_folders`(`id`) ON DELETE CASCADE,
    INDEX `idx_mailbox_id` (`mailbox_id`),
    INDEX `idx_folder_type` (`folder_type`),
    UNIQUE KEY `unique_mailbox_folder` (`mailbox_id`, `folder_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mail Messages table - Email storage
CREATE TABLE IF NOT EXISTS `mail_messages` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `mailbox_id` INT UNSIGNED NOT NULL,
    `folder_id` INT UNSIGNED NOT NULL,
    `message_id` VARCHAR(255) NOT NULL COMMENT 'RFC822 Message-ID',
    `in_reply_to` VARCHAR(255) NULL,
    `references` TEXT NULL,
    `from_email` VARCHAR(255) NOT NULL,
    `from_name` VARCHAR(255) NULL,
    `to_emails` TEXT NOT NULL COMMENT 'JSON array',
    `cc_emails` TEXT NULL COMMENT 'JSON array',
    `bcc_emails` TEXT NULL COMMENT 'JSON array',
    `reply_to` VARCHAR(255) NULL,
    `subject` VARCHAR(500) NULL,
    `body_text` LONGTEXT NULL,
    `body_html` LONGTEXT NULL,
    `headers` LONGTEXT NULL COMMENT 'Full email headers',
    `raw_message` LONGTEXT NULL COMMENT 'Complete raw email',
    `size` INT UNSIGNED DEFAULT 0 COMMENT 'Message size in bytes',
    `has_attachments` TINYINT(1) DEFAULT 0,
    `is_read` TINYINT(1) DEFAULT 0,
    `is_starred` TINYINT(1) DEFAULT 0,
    `is_draft` TINYINT(1) DEFAULT 0,
    `is_spam` TINYINT(1) DEFAULT 0,
    `spam_score` DECIMAL(5,2) DEFAULT 0.00,
    `priority` ENUM('high', 'normal', 'low') DEFAULT 'normal',
    `received_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`mailbox_id`) REFERENCES `mailboxes`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`folder_id`) REFERENCES `mail_folders`(`id`) ON DELETE CASCADE,
    INDEX `idx_mailbox_id` (`mailbox_id`),
    INDEX `idx_folder_id` (`folder_id`),
    INDEX `idx_message_id` (`message_id`),
    INDEX `idx_from_email` (`from_email`),
    INDEX `idx_is_read` (`is_read`),
    INDEX `idx_is_spam` (`is_spam`),
    INDEX `idx_received_at` (`received_at`),
    FULLTEXT INDEX `idx_search` (`subject`, `body_text`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mail Attachments table
CREATE TABLE IF NOT EXISTS `mail_attachments` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `message_id` BIGINT UNSIGNED NOT NULL,
    `filename` VARCHAR(255) NOT NULL,
    `original_filename` VARCHAR(255) NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `size` INT UNSIGNED NOT NULL,
    `storage_path` VARCHAR(500) NOT NULL,
    `content_id` VARCHAR(255) NULL COMMENT 'For inline attachments',
    `is_inline` TINYINT(1) DEFAULT 0,
    `checksum` VARCHAR(64) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`message_id`) REFERENCES `mail_messages`(`id`) ON DELETE CASCADE,
    INDEX `idx_message_id` (`message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mail Filters table - Auto-reply, forwarding, spam rules
CREATE TABLE IF NOT EXISTS `mail_filters` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `mailbox_id` INT UNSIGNED NOT NULL,
    `filter_name` VARCHAR(100) NOT NULL,
    `filter_type` ENUM('auto_reply', 'forward', 'move', 'delete', 'mark', 'spam') NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `priority` INT UNSIGNED DEFAULT 0,
    `conditions` TEXT NOT NULL COMMENT 'JSON conditions',
    `actions` TEXT NOT NULL COMMENT 'JSON actions',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`mailbox_id`) REFERENCES `mailboxes`(`id`) ON DELETE CASCADE,
    INDEX `idx_mailbox_id` (`mailbox_id`),
    INDEX `idx_filter_type` (`filter_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Auto Responders table
CREATE TABLE IF NOT EXISTS `auto_responders` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `mailbox_id` INT UNSIGNED NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `body` TEXT NOT NULL,
    `is_active` TINYINT(1) DEFAULT 0,
    `start_date` TIMESTAMP NULL,
    `end_date` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`mailbox_id`) REFERENCES `mailboxes`(`id`) ON DELETE CASCADE,
    INDEX `idx_mailbox_id` (`mailbox_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mail Logs table - Sending and receiving logs
CREATE TABLE IF NOT EXISTS `mail_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `mailbox_id` INT UNSIGNED NULL,
    `message_id` BIGINT UNSIGNED NULL,
    `log_type` ENUM('send', 'receive', 'bounce', 'spam', 'error') NOT NULL,
    `status` ENUM('pending', 'sent', 'delivered', 'failed', 'bounced', 'rejected') NOT NULL,
    `from_email` VARCHAR(255) NOT NULL,
    `to_email` VARCHAR(255) NOT NULL,
    `subject` VARCHAR(500) NULL,
    `error_message` TEXT NULL,
    `smtp_response` TEXT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(500) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`mailbox_id`) REFERENCES `mailboxes`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`message_id`) REFERENCES `mail_messages`(`id`) ON DELETE SET NULL,
    INDEX `idx_mailbox_id` (`mailbox_id`),
    INDEX `idx_message_id` (`message_id`),
    INDEX `idx_log_type` (`log_type`),
    INDEX `idx_status` (`status`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contacts table
CREATE TABLE IF NOT EXISTS `contacts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `mailbox_id` INT UNSIGNED NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(100) NULL,
    `last_name` VARCHAR(100) NULL,
    `display_name` VARCHAR(255) NULL,
    `phone` VARCHAR(50) NULL,
    `company` VARCHAR(255) NULL,
    `notes` TEXT NULL,
    `is_favorite` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`mailbox_id`) REFERENCES `mailboxes`(`id`) ON DELETE CASCADE,
    INDEX `idx_mailbox_id` (`mailbox_id`),
    INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email Templates table
CREATE TABLE IF NOT EXISTS `email_templates` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `mailbox_id` INT UNSIGNED NOT NULL,
    `template_name` VARCHAR(100) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `body` TEXT NOT NULL,
    `is_html` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`mailbox_id`) REFERENCES `mailboxes`(`id`) ON DELETE CASCADE,
    INDEX `idx_mailbox_id` (`mailbox_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blacklist/Whitelist table
CREATE TABLE IF NOT EXISTS `mail_lists` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `mailbox_id` INT UNSIGNED NULL COMMENT 'NULL for global lists',
    `list_type` ENUM('blacklist', 'whitelist') NOT NULL,
    `email_pattern` VARCHAR(255) NOT NULL,
    `reason` VARCHAR(255) NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`mailbox_id`) REFERENCES `mailboxes`(`id`) ON DELETE CASCADE,
    INDEX `idx_mailbox_id` (`mailbox_id`),
    INDEX `idx_list_type` (`list_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mail Queue table - For outgoing emails
CREATE TABLE IF NOT EXISTS `mail_queue` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `mailbox_id` INT UNSIGNED NOT NULL,
    `from_email` VARCHAR(255) NOT NULL,
    `to_emails` TEXT NOT NULL COMMENT 'JSON array',
    `cc_emails` TEXT NULL COMMENT 'JSON array',
    `bcc_emails` TEXT NULL COMMENT 'JSON array',
    `subject` VARCHAR(500) NULL,
    `body_text` LONGTEXT NULL,
    `body_html` LONGTEXT NULL,
    `attachments` TEXT NULL COMMENT 'JSON array of attachment paths',
    `priority` ENUM('high', 'normal', 'low') DEFAULT 'normal',
    `status` ENUM('pending', 'processing', 'sent', 'failed') DEFAULT 'pending',
    `attempts` INT UNSIGNED DEFAULT 0,
    `max_attempts` INT UNSIGNED DEFAULT 3,
    `error_message` TEXT NULL,
    `scheduled_at` TIMESTAMP NULL,
    `processed_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`mailbox_id`) REFERENCES `mailboxes`(`id`) ON DELETE CASCADE,
    INDEX `idx_mailbox_id` (`mailbox_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_scheduled_at` (`scheduled_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Statistics table
CREATE TABLE IF NOT EXISTS `mail_statistics` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `domain_id` INT UNSIGNED NULL,
    `mailbox_id` INT UNSIGNED NULL,
    `stat_date` DATE NOT NULL,
    `emails_sent` INT UNSIGNED DEFAULT 0,
    `emails_received` INT UNSIGNED DEFAULT 0,
    `emails_bounced` INT UNSIGNED DEFAULT 0,
    `emails_spam` INT UNSIGNED DEFAULT 0,
    `storage_used` BIGINT UNSIGNED DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`domain_id`) REFERENCES `domains`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`mailbox_id`) REFERENCES `mailboxes`(`id`) ON DELETE CASCADE,
    INDEX `idx_domain_id` (`domain_id`),
    INDEX `idx_mailbox_id` (`mailbox_id`),
    INDEX `idx_stat_date` (`stat_date`),
    UNIQUE KEY `unique_domain_date` (`domain_id`, `stat_date`),
    UNIQUE KEY `unique_mailbox_date` (`mailbox_id`, `stat_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sessions table - For webmail sessions
CREATE TABLE IF NOT EXISTS `mail_sessions` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `mailbox_id` INT UNSIGNED NOT NULL,
    `session_token` VARCHAR(64) NOT NULL UNIQUE,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` VARCHAR(500) NULL,
    `last_activity` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `expires_at` TIMESTAMP NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`mailbox_id`) REFERENCES `mailboxes`(`id`) ON DELETE CASCADE,
    INDEX `idx_mailbox_id` (`mailbox_id`),
    INDEX `idx_session_token` (`session_token`),
    INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
