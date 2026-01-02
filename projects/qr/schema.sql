-- QR Generator Project Database Schema
-- Database: mmb_qr

-- QR Codes table
CREATE TABLE IF NOT EXISTS `qr_codes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `content` TEXT NOT NULL,
    `type` ENUM('url', 'text', 'email', 'phone', 'sms', 'wifi', 'vcard') DEFAULT 'text',
    `size` INT DEFAULT 200,
    `color` VARCHAR(7) DEFAULT '#000000',
    `bg_color` VARCHAR(7) DEFAULT '#ffffff',
    `image_path` VARCHAR(255) NULL,
    `short_code` VARCHAR(10) NULL UNIQUE,
    `is_dynamic` TINYINT(1) DEFAULT 0,
    `scan_count` INT DEFAULT 0,
    `last_scanned_at` TIMESTAMP NULL,
    `expires_at` TIMESTAMP NULL,
    `status` ENUM('active', 'inactive', 'expired') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_short_code` (`short_code`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- QR Scan Analytics table
CREATE TABLE IF NOT EXISTS `qr_scans` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `qr_id` INT UNSIGNED NOT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(500) NULL,
    `referer` VARCHAR(500) NULL,
    `country` VARCHAR(50) NULL,
    `city` VARCHAR(100) NULL,
    `device_type` VARCHAR(50) NULL,
    `browser` VARCHAR(50) NULL,
    `scanned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`qr_id`) REFERENCES `qr_codes`(`id`) ON DELETE CASCADE,
    INDEX `idx_qr_id` (`qr_id`),
    INDEX `idx_scanned_at` (`scanned_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- QR Templates table (for premium features)
CREATE TABLE IF NOT EXISTS `qr_templates` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `settings` JSON NULL,
    `is_public` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
