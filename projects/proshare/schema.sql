-- ProShare Project Database Schema
-- Database: proshare
-- Note: Each project has its own separate database independent from the main MMB database

-- Files table
CREATE TABLE IF NOT EXISTS `files` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NULL COMMENT 'NULL for anonymous uploads',
    `short_code` VARCHAR(10) NOT NULL UNIQUE,
    `original_name` VARCHAR(255) NOT NULL,
    `filename` VARCHAR(255) NOT NULL,
    `path` VARCHAR(500) NOT NULL,
    `size` BIGINT UNSIGNED NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NULL,
    `downloads` INT UNSIGNED DEFAULT 0,
    `max_downloads` INT UNSIGNED NULL,
    `expires_at` TIMESTAMP NULL,
    `is_public` TINYINT(1) DEFAULT 1,
    `is_encrypted` TINYINT(1) DEFAULT 0,
    `encryption_key` VARCHAR(255) NULL,
    `self_destruct` TINYINT(1) DEFAULT 0,
    `checksum` VARCHAR(64) NULL COMMENT 'SHA-256 hash for integrity',
    `is_compressed` TINYINT(1) DEFAULT 0,
    `status` ENUM('active', 'expired', 'deleted', 'reported') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_short_code` (`short_code`),
    INDEX `idx_status` (`status`),
    INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- File downloads tracking
CREATE TABLE IF NOT EXISTS `file_downloads` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `file_id` INT UNSIGNED NOT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(500) NULL,
    `referer` VARCHAR(500) NULL,
    `downloaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`file_id`) REFERENCES `files`(`id`) ON DELETE CASCADE,
    INDEX `idx_file_id` (`file_id`),
    INDEX `idx_downloaded_at` (`downloaded_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Folders/Collections
CREATE TABLE IF NOT EXISTS `folders` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `short_code` VARCHAR(10) NOT NULL UNIQUE,
    `password` VARCHAR(255) NULL,
    `expires_at` TIMESTAMP NULL,
    `is_public` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_short_code` (`short_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- File-Folder relationship
CREATE TABLE IF NOT EXISTS `file_folders` (
    `file_id` INT UNSIGNED NOT NULL,
    `folder_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`file_id`, `folder_id`),
    FOREIGN KEY (`file_id`) REFERENCES `files`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`folder_id`) REFERENCES `folders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Text shares table (for quick text sharing)
CREATE TABLE IF NOT EXISTS `text_shares` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NULL,
    `short_code` VARCHAR(10) NOT NULL UNIQUE,
    `title` VARCHAR(255) NULL,
    `content` LONGTEXT NOT NULL,
    `is_encrypted` TINYINT(1) DEFAULT 0,
    `password` VARCHAR(255) NULL,
    `views` INT UNSIGNED DEFAULT 0,
    `max_views` INT UNSIGNED NULL,
    `expires_at` TIMESTAMP NULL,
    `self_destruct` TINYINT(1) DEFAULT 0,
    `status` ENUM('active', 'expired', 'deleted') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_short_code` (`short_code`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Messages/Chat table
CREATE TABLE IF NOT EXISTS `messages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `room_id` VARCHAR(50) NOT NULL,
    `user_id` INT UNSIGNED NULL,
    `username` VARCHAR(100) NOT NULL,
    `message` TEXT NOT NULL,
    `is_encrypted` TINYINT(1) DEFAULT 0,
    `expires_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_room_id` (`room_id`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Chat rooms table
CREATE TABLE IF NOT EXISTS `chat_rooms` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `room_code` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `creator_id` INT UNSIGNED NULL,
    `password` VARCHAR(255) NULL,
    `max_users` INT DEFAULT 10,
    `is_private` TINYINT(1) DEFAULT 0,
    `expires_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_room_code` (`room_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Link access control
CREATE TABLE IF NOT EXISTS `link_access` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `file_id` INT UNSIGNED NULL,
    `text_id` INT UNSIGNED NULL,
    `email` VARCHAR(255) NOT NULL,
    `can_download` TINYINT(1) DEFAULT 1,
    `access_count` INT UNSIGNED DEFAULT 0,
    `max_access` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`file_id`) REFERENCES `files`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`text_id`) REFERENCES `text_shares`(`id`) ON DELETE CASCADE,
    INDEX `idx_file_id` (`file_id`),
    INDEX `idx_text_id` (`text_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications table
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `type` ENUM('download', 'expiry_warning', 'security_alert', 'upload_complete') NOT NULL,
    `message` TEXT NOT NULL,
    `related_id` INT UNSIGNED NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_is_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit logs for security and compliance
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NULL,
    `action` VARCHAR(100) NOT NULL,
    `resource_type` VARCHAR(50) NOT NULL,
    `resource_id` INT UNSIGNED NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(500) NULL,
    `details` JSON NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User preferences/settings
CREATE TABLE IF NOT EXISTS `user_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL UNIQUE,
    `email_notifications` TINYINT(1) DEFAULT 1,
    `sms_notifications` TINYINT(1) DEFAULT 0,
    `default_expiry` INT DEFAULT 24 COMMENT 'hours',
    `auto_delete` TINYINT(1) DEFAULT 0,
    `enable_encryption` TINYINT(1) DEFAULT 0,
    `enable_compression` TINYINT(1) DEFAULT 1,
    `max_file_size` BIGINT UNSIGNED DEFAULT 524288000 COMMENT '500MB default',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Backups metadata
CREATE TABLE IF NOT EXISTS `backups` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `file_id` INT UNSIGNED NOT NULL,
    `backup_path` VARCHAR(500) NOT NULL,
    `backup_size` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`file_id`) REFERENCES `files`(`id`) ON DELETE CASCADE,
    INDEX `idx_file_id` (`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings table for project configuration
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    `value` TEXT NULL,
    `type` ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    `description` TEXT NULL,
    `is_system` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT INTO `settings` (`key`, `value`, `type`, `description`, `is_system`) VALUES
('max_file_size', '524288000', 'integer', 'Maximum file size in bytes (500MB)', 1),
('default_expiry_hours', '24', 'integer', 'Default link expiry in hours', 1),
('enable_password_protection', '1', 'boolean', 'Enable password protection', 1),
('enable_self_destruct', '1', 'boolean', 'Enable self-destruct feature', 1),
('enable_compression', '1', 'boolean', 'Enable file compression', 1),
('enable_anonymous_upload', '1', 'boolean', 'Enable anonymous uploads', 1);

-- Activity logs for admin actions
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NULL,
    `action` VARCHAR(100) NOT NULL,
    `resource_type` VARCHAR(50) NOT NULL,
    `resource_id` INT UNSIGNED NULL,
    `description` TEXT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(500) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_resource_type` (`resource_type`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
