-- Complete Authentication System Migration
-- Includes OAuth, Session Management, Timezone Settings, and 2FA
-- Run this migration to set up all authentication features

-- ============================================
-- PART 1: OAuth and Session Management
-- ============================================

-- OAuth providers table
CREATE TABLE IF NOT EXISTS `oauth_providers` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `display_name` VARCHAR(100) NOT NULL,
    `client_id` VARCHAR(255) NULL,
    `client_secret` VARCHAR(255) NULL,
    `redirect_uri` VARCHAR(255) NULL,
    `scopes` TEXT NULL,
    `is_enabled` TINYINT(1) DEFAULT 0,
    `config` JSON NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_name` (`name`),
    INDEX `idx_enabled` (`is_enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- OAuth user connections table
CREATE TABLE IF NOT EXISTS `oauth_user_connections` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `provider_id` INT UNSIGNED NOT NULL,
    `provider_user_id` VARCHAR(255) NOT NULL,
    `provider_email` VARCHAR(255) NULL,
    `provider_name` VARCHAR(255) NULL,
    `provider_avatar` VARCHAR(500) NULL,
    `access_token` TEXT NULL,
    `refresh_token` TEXT NULL,
    `token_expires_at` TIMESTAMP NULL,
    `last_used_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`provider_id`) REFERENCES `oauth_providers`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `idx_provider_user` (`provider_id`, `provider_user_id`),
    INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User sessions table for enhanced session tracking
CREATE TABLE IF NOT EXISTS `user_sessions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `session_id` VARCHAR(128) NOT NULL UNIQUE,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` VARCHAR(500) NULL,
    `device_info` JSON NULL,
    `last_activity_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `expires_at` TIMESTAMP NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_session_id` (`session_id`),
    INDEX `idx_active` (`is_active`),
    INDEX `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Login history table for audit purposes
CREATE TABLE IF NOT EXISTS `login_history` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NULL,
    `email` VARCHAR(255) NOT NULL,
    `login_method` ENUM('email_password', 'google_oauth', 'remember_token', '2fa') DEFAULT 'email_password',
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` VARCHAR(500) NULL,
    `status` ENUM('success', 'failed', 'blocked') DEFAULT 'success',
    `failure_reason` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_email` (`email`),
    INDEX `idx_status` (`status`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default Google OAuth provider
INSERT INTO `oauth_providers` (`name`, `display_name`, `scopes`, `is_enabled`, `config`) VALUES
('google', 'Google', 'openid email profile', 0, JSON_OBJECT(
    'auth_url', 'https://accounts.google.com/o/oauth2/v2/auth',
    'token_url', 'https://oauth2.googleapis.com/token',
    'userinfo_url', 'https://www.googleapis.com/oauth2/v2/userinfo'
)) ON DUPLICATE KEY UPDATE display_name = 'Google';

-- ============================================
-- PART 2: User Table Extensions
-- ============================================

-- Add OAuth columns to users table if not exists
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `google_id` VARCHAR(255) NULL;
ALTER TABLE `users` ADD UNIQUE INDEX IF NOT EXISTS `idx_google_id` (`google_id`);

-- Add session tracking fields to users table if not exists
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `session_timeout_minutes` INT DEFAULT 120;

-- ============================================
-- PART 3: Two-Factor Authentication
-- ============================================

-- Add 2FA columns to users table if they don't exist
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `two_factor_secret` VARCHAR(255) NULL COMMENT 'Base32-encoded TOTP secret';
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `two_factor_enabled` TINYINT(1) DEFAULT 0 COMMENT 'Whether 2FA is enabled';
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `two_factor_backup_codes` TEXT NULL COMMENT 'JSON array of hashed backup codes';
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `two_factor_enabled_at` TIMESTAMP NULL COMMENT 'When 2FA was enabled';

-- Add index for 2FA enabled users
ALTER TABLE `users` ADD INDEX IF NOT EXISTS `idx_two_factor_enabled` (`two_factor_enabled`);

-- ============================================
-- PART 4: System Settings
-- ============================================

-- Create settings table if not exists
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(255) NOT NULL UNIQUE,
    `value` TEXT NULL,
    `type` VARCHAR(50) DEFAULT 'string',
    `description` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default timezone and date/time format settings
INSERT INTO `settings` (`key`, `value`, `type`, `description`) VALUES
('system_timezone', 'UTC', 'string', 'System timezone for displaying dates/times'),
('date_format', 'M d, Y', 'string', 'Date format for displaying dates'),
('time_format', 'g:i A', 'string', 'Time format for displaying times'),
('default_session_timeout', '120', 'integer', 'Default session timeout in minutes'),
('remember_me_duration', '30', 'integer', 'Remember me duration in days'),
('max_concurrent_sessions', '5', 'integer', 'Maximum concurrent sessions per user'),
('auto_logout_enabled', '1', 'boolean', 'Enable automatic logout on inactivity'),
('session_ip_validation', '0', 'boolean', 'Validate session IP address'),
('max_failed_login_attempts', '5', 'integer', 'Maximum failed login attempts before lockout'),
('account_lockout_duration', '15', 'integer', 'Account lockout duration in minutes'),
('password_min_length', '8', 'integer', 'Minimum password length'),
('require_email_verification', '0', 'boolean', 'Require email verification for new accounts'),
('force_password_change', '0', 'boolean', 'Force password change every 90 days')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

-- ============================================
-- VERIFICATION QUERIES
-- ============================================

-- Check that all tables were created
SELECT 
    'oauth_providers' as table_name, 
    COUNT(*) as row_count 
FROM oauth_providers
UNION ALL
SELECT 'oauth_user_connections', COUNT(*) FROM oauth_user_connections
UNION ALL
SELECT 'user_sessions', COUNT(*) FROM user_sessions
UNION ALL
SELECT 'login_history', COUNT(*) FROM login_history
UNION ALL
SELECT 'settings', COUNT(*) FROM settings;

-- Show column additions to users table
SHOW COLUMNS FROM users LIKE '%google%';
SHOW COLUMNS FROM users LIKE '%session%';
SHOW COLUMNS FROM users LIKE '%two_factor%';
