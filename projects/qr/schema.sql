-- QR Generator Project Database Schema
-- Database: mmb_qr

-- QR Codes table (Enhanced for Production)
CREATE TABLE IF NOT EXISTS `qr_codes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `short_code` VARCHAR(10) UNIQUE,
    `content` TEXT NOT NULL,
    `type` ENUM('url', 'text', 'phone', 'email', 'whatsapp', 'wifi', 'location', 'vcard', 'payment', 'event', 'product') DEFAULT 'url',
    `is_dynamic` TINYINT(1) DEFAULT 0,
    `redirect_url` TEXT NULL,
    
    -- Design settings
    `size` INT DEFAULT 300,
    `foreground_color` VARCHAR(7) DEFAULT '#000000',
    `background_color` VARCHAR(7) DEFAULT '#ffffff',
    `frame_style` VARCHAR(50) NULL,
    `logo_path` VARCHAR(255) NULL,
    
    -- Security
    `password_hash` VARCHAR(255) NULL,
    `expires_at` TIMESTAMP NULL,
    
    -- Analytics
    `scan_count` INT DEFAULT 0,
    `last_scanned_at` TIMESTAMP NULL,
    
    -- Campaign
    `campaign_id` INT UNSIGNED NULL,
    
    -- Status
    `status` ENUM('active', 'inactive', 'blocked') DEFAULT 'active',
    
    -- Timestamps
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_short_code` (`short_code`),
    INDEX `idx_campaign` (`campaign_id`),
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
    `os` VARCHAR(50) NULL,
    `scanned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`qr_id`) REFERENCES `qr_codes`(`id`) ON DELETE CASCADE,
    INDEX `idx_qr_id` (`qr_id`),
    INDEX `idx_scanned_at` (`scanned_at`),
    INDEX `idx_country` (`country`)
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

-- QR Campaigns table
CREATE TABLE IF NOT EXISTS `qr_campaigns` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `status` ENUM('active', 'paused', 'archived') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- QR Subscription Plans table
CREATE TABLE IF NOT EXISTS `qr_subscription_plans` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL,
    `slug` VARCHAR(50) UNIQUE NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `billing_cycle` ENUM('monthly', 'yearly', 'lifetime') DEFAULT 'monthly',
    
    -- Limits
    `max_static_qr` INT DEFAULT 10,
    `max_dynamic_qr` INT DEFAULT 0,
    `max_scans_per_month` INT DEFAULT 1000,
    `max_bulk_generation` INT DEFAULT 0,
    
    -- Features (JSON)
    `features` JSON NULL,
    
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default QR subscription plans
INSERT INTO `qr_subscription_plans` (`name`, `slug`, `price`, `billing_cycle`, `max_static_qr`, `max_dynamic_qr`, `max_scans_per_month`, `features`) VALUES
('Free', 'free', 0.00, 'lifetime', 5, 0, 100, '{"downloads": ["png"], "analytics": false, "bulk": false, "ai": false, "password_protection": false}'),
('Starter', 'starter', 9.99, 'monthly', 50, 10, 5000, '{"downloads": ["png", "svg"], "analytics": true, "bulk": false, "ai": false, "password_protection": true}'),
('Pro', 'pro', 29.99, 'monthly', -1, -1, -1, '{"downloads": ["png", "svg", "pdf"], "analytics": true, "bulk": true, "ai": true, "password_protection": true, "campaigns": true}'),
('Enterprise', 'enterprise', 99.99, 'monthly', -1, -1, -1, '{"downloads": ["png", "svg", "pdf"], "analytics": true, "bulk": true, "ai": true, "password_protection": true, "campaigns": true, "api": true, "whitelabel": true, "priority_support": true}')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- QR User Subscriptions table
CREATE TABLE IF NOT EXISTS `qr_user_subscriptions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `plan_id` INT UNSIGNED NOT NULL,
    `status` ENUM('active', 'cancelled', 'expired', 'trial') DEFAULT 'active',
    `started_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at` TIMESTAMP NULL,
    `cancelled_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`plan_id`) REFERENCES `qr_subscription_plans`(`id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- QR Bulk Generation Jobs table
CREATE TABLE IF NOT EXISTS `qr_bulk_jobs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `campaign_id` INT UNSIGNED NULL,
    `total_count` INT NOT NULL,
    `completed_count` INT DEFAULT 0,
    `failed_count` INT DEFAULT 0,
    `status` ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    `file_path` VARCHAR(255) NULL,
    `error_log` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `completed_at` TIMESTAMP NULL,
    
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- QR Blocked Links table (Admin feature)
CREATE TABLE IF NOT EXISTS `qr_blocked_links` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `url_pattern` VARCHAR(500) NOT NULL,
    `reason` VARCHAR(255) NULL,
    `blocked_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY `unique_pattern` (`url_pattern`),
    INDEX `idx_pattern` (`url_pattern`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
