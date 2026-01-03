-- OAuth and Session Management Enhancement Migration
-- This migration adds Google SSO support and enhanced session tracking

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

-- Add oauth_id column to users table if not exists
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `google_id` VARCHAR(255) NULL AFTER `email_verification_token`;
ALTER TABLE `users` ADD UNIQUE INDEX IF NOT EXISTS `idx_google_id` (`google_id`);

-- Add session tracking fields to users table if not exists
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `session_timeout_minutes` INT DEFAULT 120 AFTER `two_factor_enabled`;
