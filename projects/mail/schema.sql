-- Mail Hosting Server Database Schema
-- Database: mail_server
-- Description: Complete SaaS email hosting platform with subscription management

-- ============================================
-- SUBSCRIPTION & BILLING TABLES
-- ============================================

-- Subscription Plans table - Free, Starter, Business, Developer
CREATE TABLE IF NOT EXISTS `subscription_plans` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `plan_name` VARCHAR(100) NOT NULL UNIQUE,
    `plan_slug` VARCHAR(50) NOT NULL UNIQUE,
    `plan_type` ENUM('free', 'paid') DEFAULT 'free',
    `price_monthly` DECIMAL(10, 2) DEFAULT 0.00,
    `price_yearly` DECIMAL(10, 2) DEFAULT 0.00,
    `max_users` INT UNSIGNED DEFAULT 1 COMMENT 'Max mailbox users',
    `storage_per_user_gb` INT UNSIGNED DEFAULT 1 COMMENT 'GB per user',
    `daily_send_limit` INT UNSIGNED DEFAULT 100,
    `max_attachment_size_mb` INT UNSIGNED DEFAULT 10,
    `max_domains` INT UNSIGNED DEFAULT 1,
    `max_aliases` INT UNSIGNED DEFAULT 5,
    `is_active` TINYINT(1) DEFAULT 1,
    `sort_order` INT UNSIGNED DEFAULT 0,
    `description` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_plan_slug` (`plan_slug`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Plan Features table - Feature gating configuration
CREATE TABLE IF NOT EXISTS `plan_features` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `plan_id` INT UNSIGNED NOT NULL,
    `feature_key` VARCHAR(50) NOT NULL,
    `feature_name` VARCHAR(100) NOT NULL,
    `is_enabled` TINYINT(1) DEFAULT 0,
    `feature_value` VARCHAR(255) NULL COMMENT 'For numeric/string limits',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans`(`id`) ON DELETE CASCADE,
    INDEX `idx_plan_id` (`plan_id`),
    INDEX `idx_feature_key` (`feature_key`),
    UNIQUE KEY `unique_plan_feature` (`plan_id`, `feature_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Subscribers table - Account owners/customers (references main MMB users table)
CREATE TABLE IF NOT EXISTS `subscribers` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `mmb_user_id` INT UNSIGNED NOT NULL UNIQUE COMMENT 'Reference to main MMB users table - the buyer',
    `account_name` VARCHAR(255) NOT NULL,
    `company_name` VARCHAR(255) NULL,
    `billing_email` VARCHAR(255) NULL,
    `billing_address` TEXT NULL,
    `status` ENUM('active', 'suspended', 'cancelled', 'grace_period') DEFAULT 'active',
    `suspension_reason` TEXT NULL,
    `stripe_customer_id` VARCHAR(255) NULL,
    `users_count` INT UNSIGNED DEFAULT 0 COMMENT 'Current number of users added',
    `can_add_users` TINYINT(1) DEFAULT 1 COMMENT 'Can subscriber add more users',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    `suspended_at` TIMESTAMP NULL,
    INDEX `idx_mmb_user_id` (`mmb_user_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_stripe_customer_id` (`stripe_customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Subscriptions table - Active subscriptions
CREATE TABLE IF NOT EXISTS `subscriptions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `subscriber_id` INT UNSIGNED NOT NULL,
    `plan_id` INT UNSIGNED NOT NULL,
    `status` ENUM('active', 'cancelled', 'expired', 'past_due', 'trialing') DEFAULT 'active',
    `billing_cycle` ENUM('monthly', 'yearly') DEFAULT 'monthly',
    `current_period_start` TIMESTAMP NULL,
    `current_period_end` TIMESTAMP NULL,
    `trial_ends_at` TIMESTAMP NULL,
    `cancelled_at` TIMESTAMP NULL,
    `stripe_subscription_id` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`subscriber_id`) REFERENCES `subscribers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans`(`id`) ON DELETE RESTRICT,
    INDEX `idx_subscriber_id` (`subscriber_id`),
    INDEX `idx_plan_id` (`plan_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_stripe_subscription_id` (`stripe_subscription_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payments table - Payment history
CREATE TABLE IF NOT EXISTS `payments` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `subscriber_id` INT UNSIGNED NOT NULL,
    `subscription_id` INT UNSIGNED NULL,
    `amount` DECIMAL(10, 2) NOT NULL,
    `currency` VARCHAR(3) DEFAULT 'USD',
    `status` ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    `payment_method` VARCHAR(50) NULL,
    `stripe_payment_id` VARCHAR(255) NULL,
    `stripe_invoice_id` VARCHAR(255) NULL,
    `description` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`subscriber_id`) REFERENCES `subscribers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions`(`id`) ON DELETE SET NULL,
    INDEX `idx_subscriber_id` (`subscriber_id`),
    INDEX `idx_subscription_id` (`subscription_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_stripe_payment_id` (`stripe_payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Invoices table - Billing invoices
CREATE TABLE IF NOT EXISTS `invoices` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `subscriber_id` INT UNSIGNED NOT NULL,
    `subscription_id` INT UNSIGNED NULL,
    `invoice_number` VARCHAR(50) NOT NULL UNIQUE,
    `amount` DECIMAL(10, 2) NOT NULL,
    `tax_amount` DECIMAL(10, 2) DEFAULT 0.00,
    `total_amount` DECIMAL(10, 2) NOT NULL,
    `currency` VARCHAR(3) DEFAULT 'USD',
    `status` ENUM('draft', 'sent', 'paid', 'overdue', 'cancelled') DEFAULT 'draft',
    `due_date` DATE NULL,
    `paid_at` TIMESTAMP NULL,
    `stripe_invoice_id` VARCHAR(255) NULL,
    `invoice_pdf_url` VARCHAR(500) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`subscriber_id`) REFERENCES `subscribers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions`(`id`) ON DELETE SET NULL,
    INDEX `idx_subscriber_id` (`subscriber_id`),
    INDEX `idx_invoice_number` (`invoice_number`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DOMAIN & MAILBOX TABLES (Multi-Tenant)
-- ============================================

-- Domains table - Custom domains (multi-tenant)
CREATE TABLE IF NOT EXISTS `domains` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `subscriber_id` INT UNSIGNED NOT NULL COMMENT 'Owner subscriber',
    `domain_name` VARCHAR(255) NOT NULL UNIQUE,
    `is_verified` TINYINT(1) DEFAULT 0,
    `verification_token` VARCHAR(64) NULL,
    `verification_method` ENUM('txt', 'cname', 'mx') DEFAULT 'txt',
    `is_active` TINYINT(1) DEFAULT 1,
    `ssl_enabled` TINYINT(1) DEFAULT 0,
    `ssl_certificate` TEXT NULL,
    `ssl_private_key` TEXT NULL,
    `catch_all_enabled` TINYINT(1) DEFAULT 0,
    `catch_all_mailbox_id` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    `verified_at` TIMESTAMP NULL,
    FOREIGN KEY (`subscriber_id`) REFERENCES `subscribers`(`id`) ON DELETE CASCADE,
    INDEX `idx_subscriber_id` (`subscriber_id`),
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

-- ============================================
-- USER ROLES & PERMISSIONS
-- ============================================

-- Mail User Roles table - Platform Super Admin, Subscriber (Account Owner), Domain Admin, End User
CREATE TABLE IF NOT EXISTS `mail_user_roles` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `mmb_user_id` INT UNSIGNED NOT NULL COMMENT 'Reference to main MMB users',
    `subscriber_id` INT UNSIGNED NULL COMMENT 'NULL for platform super admin only',
    `role_type` ENUM('platform_super_admin', 'subscriber_owner', 'domain_admin', 'end_user') NOT NULL,
    `permissions` TEXT NULL COMMENT 'JSON permissions override',
    `is_owner` TINYINT(1) DEFAULT 0 COMMENT '1 if subscriber owner who bought the subscription',
    `invited_by_user_id` INT UNSIGNED NULL COMMENT 'Who invited/added this user',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_mmb_user_id` (`mmb_user_id`),
    INDEX `idx_subscriber_id` (`subscriber_id`),
    INDEX `idx_role_type` (`role_type`),
    INDEX `idx_is_owner` (`is_owner`),
    FOREIGN KEY (`subscriber_id`) REFERENCES `subscribers`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_user_subscriber` (`mmb_user_id`, `subscriber_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Feature Access table - Track plan features + super admin overrides
CREATE TABLE IF NOT EXISTS `feature_access` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `subscriber_id` INT UNSIGNED NULL COMMENT 'Subscriber level override',
    `mailbox_id` INT UNSIGNED NULL COMMENT 'User level override',
    `feature_key` VARCHAR(50) NOT NULL,
    `is_enabled` TINYINT(1) DEFAULT 1,
    `override_by_admin` TINYINT(1) DEFAULT 0,
    `override_reason` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_subscriber_id` (`subscriber_id`),
    INDEX `idx_mailbox_id` (`mailbox_id`),
    INDEX `idx_feature_key` (`feature_key`),
    FOREIGN KEY (`subscriber_id`) REFERENCES `subscribers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mailboxes table - Email accounts (multi-tenant)
CREATE TABLE IF NOT EXISTS `mailboxes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `subscriber_id` INT UNSIGNED NOT NULL COMMENT 'Owner subscriber',
    `domain_id` INT UNSIGNED NOT NULL,
    `mmb_user_id` INT UNSIGNED NULL COMMENT 'Linked MMB user (optional for invited users)',
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `username` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NOT NULL COMMENT 'Hashed password for SMTP/IMAP auth',
    `display_name` VARCHAR(255) NULL,
    `role_type` ENUM('subscriber_owner', 'domain_admin', 'end_user') DEFAULT 'end_user',
    `added_by_user_id` INT UNSIGNED NULL COMMENT 'Subscriber who added this mailbox',
    `storage_quota` BIGINT UNSIGNED DEFAULT 1073741824 COMMENT 'Bytes, default 1GB',
    `storage_used` BIGINT UNSIGNED DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `receive_enabled` TINYINT(1) DEFAULT 1,
    `send_enabled` TINYINT(1) DEFAULT 1,
    `daily_send_limit` INT UNSIGNED DEFAULT 300,
    `daily_send_count` INT UNSIGNED DEFAULT 0,
    `last_send_reset` DATE NULL,
    `last_login_at` TIMESTAMP NULL,
    `last_login_ip` VARCHAR(45) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`subscriber_id`) REFERENCES `subscribers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`domain_id`) REFERENCES `domains`(`id`) ON DELETE CASCADE,
    INDEX `idx_subscriber_id` (`subscriber_id`),
    INDEX `idx_domain_id` (`domain_id`),
    INDEX `idx_mmb_user_id` (`mmb_user_id`),
    INDEX `idx_email` (`email`),
    INDEX `idx_is_active` (`is_active`),
    INDEX `idx_role_type` (`role_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Invitations table - Track invited users by subscriber
CREATE TABLE IF NOT EXISTS `user_invitations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `subscriber_id` INT UNSIGNED NOT NULL,
    `invited_by_user_id` INT UNSIGNED NOT NULL COMMENT 'Subscriber who sent invitation',
    `email` VARCHAR(255) NOT NULL,
    `role_type` ENUM('domain_admin', 'end_user') DEFAULT 'end_user',
    `invitation_token` VARCHAR(64) NOT NULL UNIQUE,
    `status` ENUM('pending', 'accepted', 'expired', 'cancelled') DEFAULT 'pending',
    `expires_at` TIMESTAMP NOT NULL,
    `accepted_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`subscriber_id`) REFERENCES `subscribers`(`id`) ON DELETE CASCADE,
    INDEX `idx_subscriber_id` (`subscriber_id`),
    INDEX `idx_email` (`email`),
    INDEX `idx_invitation_token` (`invitation_token`),
    INDEX `idx_status` (`status`)
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

-- ============================================
-- PAID FEATURE TABLES (SMTP/IMAP/API)
-- ============================================

-- SMTP Credentials table - For paid users to access SMTP
CREATE TABLE IF NOT EXISTS `smtp_credentials` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `mailbox_id` INT UNSIGNED NOT NULL,
    `credential_name` VARCHAR(100) NOT NULL,
    `smtp_username` VARCHAR(255) NOT NULL UNIQUE,
    `smtp_password` VARCHAR(255) NOT NULL COMMENT 'Hashed password',
    `smtp_host` VARCHAR(255) NOT NULL,
    `smtp_port` INT UNSIGNED DEFAULT 587,
    `encryption_type` ENUM('tls', 'ssl', 'none') DEFAULT 'tls',
    `is_active` TINYINT(1) DEFAULT 1,
    `rate_limit_per_hour` INT UNSIGNED DEFAULT 100,
    `last_used_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`mailbox_id`) REFERENCES `mailboxes`(`id`) ON DELETE CASCADE,
    INDEX `idx_mailbox_id` (`mailbox_id`),
    INDEX `idx_smtp_username` (`smtp_username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API Keys table - For paid users to access REST API
CREATE TABLE IF NOT EXISTS `api_keys` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `subscriber_id` INT UNSIGNED NOT NULL,
    `mailbox_id` INT UNSIGNED NULL COMMENT 'NULL for subscriber-level keys',
    `key_name` VARCHAR(100) NOT NULL,
    `api_key` VARCHAR(64) NOT NULL UNIQUE,
    `api_secret` VARCHAR(255) NOT NULL COMMENT 'Hashed secret',
    `permissions` TEXT NULL COMMENT 'JSON permissions array',
    `rate_limit_per_minute` INT UNSIGNED DEFAULT 60,
    `rate_limit_per_day` INT UNSIGNED DEFAULT 10000,
    `is_active` TINYINT(1) DEFAULT 1,
    `last_used_at` TIMESTAMP NULL,
    `expires_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`subscriber_id`) REFERENCES `subscribers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`mailbox_id`) REFERENCES `mailboxes`(`id`) ON DELETE CASCADE,
    INDEX `idx_subscriber_id` (`subscriber_id`),
    INDEX `idx_mailbox_id` (`mailbox_id`),
    INDEX `idx_api_key` (`api_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API Usage Logs table - Track API usage
CREATE TABLE IF NOT EXISTS `api_usage_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `api_key_id` INT UNSIGNED NOT NULL,
    `endpoint` VARCHAR(255) NOT NULL,
    `method` VARCHAR(10) NOT NULL,
    `status_code` INT UNSIGNED NOT NULL,
    `response_time_ms` INT UNSIGNED NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(500) NULL,
    `request_payload` TEXT NULL,
    `response_payload` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`api_key_id`) REFERENCES `api_keys`(`id`) ON DELETE CASCADE,
    INDEX `idx_api_key_id` (`api_key_id`),
    INDEX `idx_endpoint` (`endpoint`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- USAGE TRACKING & MONITORING
-- ============================================

-- Usage Logs table - Track quota and usage per subscriber
CREATE TABLE IF NOT EXISTS `usage_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `subscriber_id` INT UNSIGNED NOT NULL,
    `log_date` DATE NOT NULL,
    `emails_sent` INT UNSIGNED DEFAULT 0,
    `emails_received` INT UNSIGNED DEFAULT 0,
    `api_calls` INT UNSIGNED DEFAULT 0,
    `storage_used_bytes` BIGINT UNSIGNED DEFAULT 0,
    `bandwidth_used_bytes` BIGINT UNSIGNED DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`subscriber_id`) REFERENCES `subscribers`(`id`) ON DELETE CASCADE,
    INDEX `idx_subscriber_id` (`subscriber_id`),
    INDEX `idx_log_date` (`log_date`),
    UNIQUE KEY `unique_subscriber_date` (`subscriber_id`, `log_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ADMIN & AUDIT TABLES
-- ============================================

-- Admin Actions table - Audit trail for super admin actions
CREATE TABLE IF NOT EXISTS `admin_actions` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `admin_user_id` INT UNSIGNED NOT NULL COMMENT 'MMB user ID of admin',
    `action_type` VARCHAR(50) NOT NULL,
    `target_type` VARCHAR(50) NULL COMMENT 'subscriber, domain, mailbox, etc.',
    `target_id` INT UNSIGNED NULL,
    `action_description` TEXT NOT NULL,
    `metadata` TEXT NULL COMMENT 'JSON additional data',
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(500) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_admin_user_id` (`admin_user_id`),
    INDEX `idx_action_type` (`action_type`),
    INDEX `idx_target_type` (`target_type`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Abuse Reports table - Track spam and abuse reports
CREATE TABLE IF NOT EXISTS `abuse_reports` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `reporter_email` VARCHAR(255) NULL,
    `reported_mailbox_id` INT UNSIGNED NULL,
    `reported_domain_id` INT UNSIGNED NULL,
    `report_type` ENUM('spam', 'phishing', 'malware', 'harassment', 'other') NOT NULL,
    `report_description` TEXT NOT NULL,
    `evidence` TEXT NULL COMMENT 'JSON evidence data',
    `status` ENUM('pending', 'investigating', 'resolved', 'dismissed') DEFAULT 'pending',
    `action_taken` TEXT NULL,
    `handled_by_admin_id` INT UNSIGNED NULL,
    `resolved_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`reported_mailbox_id`) REFERENCES `mailboxes`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`reported_domain_id`) REFERENCES `domains`(`id`) ON DELETE SET NULL,
    INDEX `idx_reported_mailbox_id` (`reported_mailbox_id`),
    INDEX `idx_reported_domain_id` (`reported_domain_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Webhooks table - For API webhooks (paid feature)
CREATE TABLE IF NOT EXISTS `webhooks` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `subscriber_id` INT UNSIGNED NOT NULL,
    `webhook_url` VARCHAR(500) NOT NULL,
    `webhook_secret` VARCHAR(255) NOT NULL,
    `events` TEXT NOT NULL COMMENT 'JSON array of subscribed events',
    `is_active` TINYINT(1) DEFAULT 1,
    `last_triggered_at` TIMESTAMP NULL,
    `failure_count` INT UNSIGNED DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`subscriber_id`) REFERENCES `subscribers`(`id`) ON DELETE CASCADE,
    INDEX `idx_subscriber_id` (`subscriber_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Webhook Deliveries table - Track webhook delivery attempts
CREATE TABLE IF NOT EXISTS `webhook_deliveries` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `webhook_id` INT UNSIGNED NOT NULL,
    `event_type` VARCHAR(50) NOT NULL,
    `payload` TEXT NOT NULL COMMENT 'JSON payload',
    `status_code` INT UNSIGNED NULL,
    `response_body` TEXT NULL,
    `delivered_at` TIMESTAMP NULL,
    `failed_at` TIMESTAMP NULL,
    `failure_reason` TEXT NULL,
    `retry_count` INT UNSIGNED DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`webhook_id`) REFERENCES `webhooks`(`id`) ON DELETE CASCADE,
    INDEX `idx_webhook_id` (`webhook_id`),
    INDEX `idx_event_type` (`event_type`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- System Settings table - Global configuration
CREATE TABLE IF NOT EXISTS `system_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT NULL,
    `setting_type` ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    `description` TEXT NULL,
    `is_public` TINYINT(1) DEFAULT 0 COMMENT 'Can non-admins see this?',
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DEFAULT DATA INSERTS
-- ============================================

-- Insert default subscription plans
INSERT INTO `subscription_plans` (`plan_name`, `plan_slug`, `plan_type`, `price_monthly`, `price_yearly`, `max_users`, `storage_per_user_gb`, `daily_send_limit`, `max_attachment_size_mb`, `max_domains`, `max_aliases`, `sort_order`, `description`) VALUES
('Free', 'free', 'free', 0.00, 0.00, 1, 1, 50, 5, 1, 3, 1, 'Perfect for personal use'),
('Starter', 'starter', 'paid', 9.99, 99.00, 5, 5, 500, 25, 3, 25, 2, 'Great for small teams'),
('Business', 'business', 'paid', 29.99, 299.00, 25, 25, 2000, 50, 10, 100, 3, 'For growing businesses'),
('Developer', 'developer', 'paid', 49.99, 499.00, 100, 50, 10000, 100, 50, 500, 4, 'Full API access for developers');

-- Insert default plan features
INSERT INTO `plan_features` (`plan_id`, `feature_key`, `feature_name`, `is_enabled`) VALUES
-- Free plan features
(1, 'webmail', 'Webmail Access', 1),
(1, 'smtp', 'SMTP Access', 0),
(1, 'imap', 'IMAP/POP3 Access', 0),
(1, 'api', 'API Access', 0),
(1, 'domain', 'Custom Domain', 1),
(1, 'alias', 'Email Aliases', 1),
(1, '2fa', 'Two-Factor Authentication', 0),
(1, 'threads', 'Threaded Conversations', 0),
(1, 'scheduled_send', 'Scheduled Sending', 0),
(1, 'read_receipts', 'Read Receipts', 0),
-- Starter plan features
(2, 'webmail', 'Webmail Access', 1),
(2, 'smtp', 'SMTP Access', 1),
(2, 'imap', 'IMAP/POP3 Access', 1),
(2, 'api', 'API Access', 0),
(2, 'domain', 'Custom Domain', 1),
(2, 'alias', 'Email Aliases', 1),
(2, '2fa', 'Two-Factor Authentication', 1),
(2, 'threads', 'Threaded Conversations', 1),
(2, 'scheduled_send', 'Scheduled Sending', 1),
(2, 'read_receipts', 'Read Receipts', 1),
-- Business plan features
(3, 'webmail', 'Webmail Access', 1),
(3, 'smtp', 'SMTP Access', 1),
(3, 'imap', 'IMAP/POP3 Access', 1),
(3, 'api', 'API Access', 1),
(3, 'domain', 'Custom Domain', 1),
(3, 'alias', 'Email Aliases', 1),
(3, '2fa', 'Two-Factor Authentication', 1),
(3, 'threads', 'Threaded Conversations', 1),
(3, 'scheduled_send', 'Scheduled Sending', 1),
(3, 'read_receipts', 'Read Receipts', 1),
-- Developer plan features
(4, 'webmail', 'Webmail Access', 1),
(4, 'smtp', 'SMTP Access', 1),
(4, 'imap', 'IMAP/POP3 Access', 1),
(4, 'api', 'API Access', 1),
(4, 'domain', 'Custom Domain', 1),
(4, 'alias', 'Email Aliases', 1),
(4, '2fa', 'Two-Factor Authentication', 1),
(4, 'threads', 'Threaded Conversations', 1),
(4, 'scheduled_send', 'Scheduled Sending', 1),
(4, 'read_receipts', 'Read Receipts', 1);
