-- Additional SQL for User Settings/Preferences
-- This file contains SQL for user settings that weren't in the main schema

-- User Settings table for QR project
CREATE TABLE IF NOT EXISTS `qr_user_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL UNIQUE,
    
    -- Default QR Settings
    `default_size` INT DEFAULT 300,
    `default_foreground_color` VARCHAR(7) DEFAULT '#000000',
    `default_background_color` VARCHAR(7) DEFAULT '#ffffff',
    `default_error_correction` VARCHAR(1) DEFAULT 'H',
    `default_frame_style` VARCHAR(50) DEFAULT 'none',
    `default_download_format` VARCHAR(10) DEFAULT 'png',
    
    -- Preferences
    `auto_save` TINYINT(1) DEFAULT 1,
    `email_notifications` TINYINT(1) DEFAULT 0,
    `scan_notification_threshold` INT DEFAULT 10,
    
    -- API Settings
    `api_key` VARCHAR(64) NULL,
    `api_enabled` TINYINT(1) DEFAULT 0,
    `api_rate_limit` INT DEFAULT 100,
    
    -- Timestamps
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_api_key` (`api_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add missing columns to qr_codes if they don't exist
ALTER TABLE `qr_codes` 
ADD COLUMN IF NOT EXISTS `error_correction` VARCHAR(1) DEFAULT 'H' AFTER `background_color`;

ALTER TABLE `qr_codes`
ADD COLUMN IF NOT EXISTS `deleted_at` TIMESTAMP NULL AFTER `updated_at`;

-- Create indexes for better performance on campaigns
CREATE INDEX IF NOT EXISTS idx_campaign_status ON qr_campaigns(status);
CREATE INDEX IF NOT EXISTS idx_campaign_user ON qr_campaigns(user_id, status);

-- Create indexes for better performance on bulk jobs
CREATE INDEX IF NOT EXISTS idx_bulk_user_status ON qr_bulk_jobs(user_id, status);
CREATE INDEX IF NOT EXISTS idx_bulk_created ON qr_bulk_jobs(created_at);

-- Create indexes for better performance on templates
CREATE INDEX IF NOT EXISTS idx_template_user ON qr_templates(user_id);
CREATE INDEX IF NOT EXISTS idx_template_public ON qr_templates(is_public);

-- Create full-text index for template search
CREATE FULLTEXT INDEX IF NOT EXISTS idx_template_name_ft ON qr_templates(name);
