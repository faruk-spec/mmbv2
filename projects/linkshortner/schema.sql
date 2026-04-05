-- LinkShortner Project Database Schema
-- Database: mmb_linkshortner

CREATE TABLE IF NOT EXISTS `short_links` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `code` VARCHAR(20) NOT NULL UNIQUE,
    `original_url` TEXT NOT NULL,
    `title` VARCHAR(255) NULL,
    `password` VARCHAR(255) NULL,
    `expires_at` TIMESTAMP NULL,
    `click_limit` INT UNSIGNED NULL,
    `total_clicks` INT UNSIGNED DEFAULT 0,
    `unique_clicks` INT UNSIGNED DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `status` ENUM('active', 'expired', 'disabled') DEFAULT 'active',
    `utm_source` VARCHAR(255) NULL,
    `utm_medium` VARCHAR(255) NULL,
    `utm_campaign` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_code` (`code`),
    INDEX `idx_status` (`status`),
    INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `link_clicks` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `link_id` INT UNSIGNED NOT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(500) NULL,
    `referer` VARCHAR(500) NULL,
    `country` VARCHAR(100) NULL,
    `city` VARCHAR(100) NULL,
    `device` ENUM('desktop', 'mobile', 'tablet', 'unknown') DEFAULT 'unknown',
    `os` VARCHAR(100) NULL,
    `browser` VARCHAR(100) NULL,
    `clicked_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`link_id`) REFERENCES `short_links`(`id`) ON DELETE CASCADE,
    INDEX `idx_link_id` (`link_id`),
    INDEX `idx_clicked_at` (`clicked_at`),
    INDEX `idx_ip` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `link_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL UNIQUE,
    `default_expiry_days` INT NULL,
    `notifications_enabled` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
