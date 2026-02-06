-- ================================================================
-- QR Code System - Complete Database Schema
-- All QR-related tables with qr_ prefix
-- Version: 1.0 (Production Ready)
-- ================================================================

-- ================================================================
-- Core QR Tables
-- ================================================================

-- QR Codes table (Enhanced for Production)
CREATE TABLE IF NOT EXISTS `qr_codes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `short_code` VARCHAR(10) UNIQUE,
    `content` TEXT NOT NULL,
    `type` ENUM('url', 'text', 'phone', 'email', 'whatsapp', 'wifi', 'location', 'vcard', 'payment', 'event', 'product') DEFAULT 'url',
    `is_dynamic` TINYINT(1) DEFAULT 0,
    `redirect_url` TEXT NULL COMMENT 'For dynamic QR codes',
    
    -- Design settings
    `size` INT DEFAULT 300,
    `foreground_color` VARCHAR(7) DEFAULT '#000000',
    `background_color` VARCHAR(7) DEFAULT '#ffffff',
    `frame_style` VARCHAR(50) NULL COMMENT 'circle, square, rounded, etc.',
    `logo_path` VARCHAR(255) NULL COMMENT 'Path to uploaded logo',
    
    -- Security
    `password_hash` VARCHAR(255) NULL COMMENT 'For password-protected QR codes',
    `expires_at` TIMESTAMP NULL COMMENT 'Expiration date for QR code',
    
    -- Analytics
    `scan_count` INT DEFAULT 0,
    `last_scanned_at` TIMESTAMP NULL,
    
    -- Organization
    `campaign_id` INT UNSIGNED NULL,
    
    -- Status
    `status` ENUM('active', 'inactive', 'blocked') DEFAULT 'active',
    
    -- Timestamps
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_short_code` (`short_code`),
    INDEX `idx_campaign` (`campaign_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_type` (`type`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Main QR codes table';

-- QR Scan Analytics table
CREATE TABLE IF NOT EXISTS `qr_scans` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `qr_id` INT UNSIGNED NOT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(500) NULL,
    `referer` VARCHAR(500) NULL,
    `country` VARCHAR(50) NULL,
    `city` VARCHAR(100) NULL,
    `region` VARCHAR(100) NULL,
    `device_type` VARCHAR(50) NULL COMMENT 'mobile, tablet, desktop',
    `browser` VARCHAR(50) NULL,
    `os` VARCHAR(50) NULL,
    `scanned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`qr_id`) REFERENCES `qr_codes`(`id`) ON DELETE CASCADE,
    INDEX `idx_qr_id` (`qr_id`),
    INDEX `idx_scanned_at` (`scanned_at`),
    INDEX `idx_country` (`country`),
    INDEX `idx_device_type` (`device_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Scan analytics and tracking';

-- ================================================================
-- Organization & Management
-- ================================================================

-- QR Campaigns table
CREATE TABLE IF NOT EXISTS `qr_campaigns` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `tags` VARCHAR(255) NULL COMMENT 'Comma-separated tags',
    `status` ENUM('active', 'paused', 'archived') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Campaign grouping for QR codes';

-- QR Templates table (for design presets)
CREATE TABLE IF NOT EXISTS `qr_templates` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `settings` JSON NULL COMMENT 'Stores design settings: colors, frame, logo, etc.',
    `is_public` TINYINT(1) DEFAULT 0 COMMENT 'Available to all users',
    `is_default` TINYINT(1) DEFAULT 0 COMMENT 'System default template',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_is_public` (`is_public`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Design templates for QR codes';

-- ================================================================
-- Subscription & Plans
-- ================================================================

-- QR Subscription Plans table
CREATE TABLE IF NOT EXISTS `qr_subscription_plans` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL,
    `slug` VARCHAR(50) UNIQUE NOT NULL,
    `description` TEXT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `billing_cycle` ENUM('monthly', 'yearly', 'lifetime') DEFAULT 'monthly',
    
    -- Limits (-1 means unlimited)
    `max_static_qr` INT DEFAULT 10,
    `max_dynamic_qr` INT DEFAULT 0,
    `max_scans_per_month` INT DEFAULT 1000,
    `max_bulk_generation` INT DEFAULT 0,
    
    -- Features (JSON)
    `features` JSON NULL COMMENT 'Available features: downloads, analytics, bulk, ai, api, whitelabel',
    
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_slug` (`slug`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='QR subscription plan definitions';

-- Insert default QR subscription plans
INSERT INTO `qr_subscription_plans` (`name`, `slug`, `description`, `price`, `billing_cycle`, `max_static_qr`, `max_dynamic_qr`, `max_scans_per_month`, `features`, `sort_order`) VALUES
('Free', 'free', 'Perfect for getting started with basic QR codes', 0.00, 'lifetime', 5, 0, 100, '{"downloads": ["png"], "analytics": false, "bulk": false, "ai": false, "password_protection": false, "expiry": false}', 1),
('Starter', 'starter', 'Great for small businesses and personal projects', 9.99, 'monthly', 50, 10, 5000, '{"downloads": ["png", "svg"], "analytics": true, "bulk": false, "ai": false, "password_protection": true, "expiry": true, "campaigns": false}', 2),
('Pro', 'pro', 'Best for professionals and growing businesses', 29.99, 'monthly', -1, -1, -1, '{"downloads": ["png", "svg", "pdf"], "analytics": true, "bulk": true, "ai": true, "password_protection": true, "expiry": true, "campaigns": true, "custom_domains": true}', 3),
('Enterprise', 'enterprise', 'Complete solution for large organizations', 99.99, 'monthly', -1, -1, -1, '{"downloads": ["png", "svg", "pdf"], "analytics": true, "bulk": true, "ai": true, "password_protection": true, "expiry": true, "campaigns": true, "custom_domains": true, "api": true, "whitelabel": true, "priority_support": true, "team_roles": true}', 4)
ON DUPLICATE KEY UPDATE 
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `price` = VALUES(`price`),
    `features` = VALUES(`features`);

-- QR User Subscriptions table
CREATE TABLE IF NOT EXISTS `qr_user_subscriptions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `plan_id` INT UNSIGNED NOT NULL,
    `status` ENUM('active', 'cancelled', 'expired', 'trial') DEFAULT 'active',
    `started_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at` TIMESTAMP NULL,
    `cancelled_at` TIMESTAMP NULL,
    `payment_method` VARCHAR(50) NULL COMMENT 'stripe, paypal, etc.',
    `subscription_ref` VARCHAR(100) NULL COMMENT 'External subscription ID',
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`plan_id`) REFERENCES `qr_subscription_plans`(`id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User subscription records';

-- ================================================================
-- Advanced Features
-- ================================================================

-- QR Bulk Generation Jobs table
CREATE TABLE IF NOT EXISTS `qr_bulk_jobs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `campaign_id` INT UNSIGNED NULL,
    `total_count` INT NOT NULL,
    `completed_count` INT DEFAULT 0,
    `failed_count` INT DEFAULT 0,
    `status` ENUM('pending', 'processing', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    `file_path` VARCHAR(255) NULL COMMENT 'Path to uploaded CSV',
    `output_path` VARCHAR(255) NULL COMMENT 'Path to ZIP file with QR codes',
    `error_log` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `started_at` TIMESTAMP NULL,
    `completed_at` TIMESTAMP NULL,
    
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bulk QR generation job tracking';

-- QR Blocked Links table (Admin feature)
CREATE TABLE IF NOT EXISTS `qr_blocked_links` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `url_pattern` VARCHAR(500) NOT NULL,
    `reason` VARCHAR(255) NULL,
    `blocked_by` INT UNSIGNED NULL COMMENT 'Admin user ID',
    `is_regex` TINYINT(1) DEFAULT 0 COMMENT 'Pattern is regex',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY `unique_pattern` (`url_pattern`),
    INDEX `idx_pattern` (`url_pattern`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Blocked URL patterns for security';

-- QR Abuse Reports table
CREATE TABLE IF NOT EXISTS `qr_abuse_reports` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `qr_id` INT UNSIGNED NOT NULL,
    `reported_by` INT UNSIGNED NULL COMMENT 'User ID or NULL for anonymous',
    `reason` ENUM('spam', 'malware', 'phishing', 'inappropriate', 'copyright', 'other') NOT NULL,
    `description` TEXT NULL,
    `status` ENUM('pending', 'reviewed', 'resolved', 'dismissed') DEFAULT 'pending',
    `resolved_by` INT UNSIGNED NULL COMMENT 'Admin user ID',
    `resolution_notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `resolved_at` TIMESTAMP NULL,
    
    FOREIGN KEY (`qr_id`) REFERENCES `qr_codes`(`id`) ON DELETE CASCADE,
    INDEX `idx_qr_id` (`qr_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User-reported abuse/spam QR codes';

-- ================================================================
-- API & Integration
-- ================================================================

-- QR API Keys table (For Enterprise users)
CREATE TABLE IF NOT EXISTS `qr_api_keys` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL COMMENT 'Friendly name for the key',
    `api_key` VARCHAR(64) UNIQUE NOT NULL,
    `api_secret` VARCHAR(64) NOT NULL,
    `permissions` JSON NULL COMMENT 'Array of allowed operations',
    `rate_limit` INT DEFAULT 1000 COMMENT 'Requests per hour',
    `last_used_at` TIMESTAMP NULL,
    `expires_at` TIMESTAMP NULL,
    `status` ENUM('active', 'revoked') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_api_key` (`api_key`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='API keys for programmatic access';

-- QR API Logs table
CREATE TABLE IF NOT EXISTS `qr_api_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `api_key_id` INT UNSIGNED NOT NULL,
    `endpoint` VARCHAR(255) NOT NULL,
    `method` VARCHAR(10) NOT NULL,
    `response_code` INT NOT NULL,
    `response_time` INT NULL COMMENT 'Response time in milliseconds',
    `ip_address` VARCHAR(45) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`api_key_id`) REFERENCES `qr_api_keys`(`id`) ON DELETE CASCADE,
    INDEX `idx_api_key_id` (`api_key_id`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='API request logs';

-- ================================================================
-- End of Schema
-- ================================================================

-- Verify all tables were created
SELECT 
    TABLE_NAME,
    TABLE_ROWS,
    CREATE_TIME
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME LIKE 'qr_%'
ORDER BY TABLE_NAME;
