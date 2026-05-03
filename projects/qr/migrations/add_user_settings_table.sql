-- Migration: Add qr_user_settings table
-- Date: 2026-05-03
-- Purpose: Add user settings table for QR Generator with all default preferences

CREATE TABLE IF NOT EXISTS `qr_user_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL UNIQUE,
    
    -- Basic QR Code Defaults
    `default_size` INT DEFAULT 300,
    `default_foreground_color` VARCHAR(7) DEFAULT '#000000',
    `default_background_color` VARCHAR(7) DEFAULT '#ffffff',
    `default_error_correction` CHAR(1) DEFAULT 'H',
    `default_frame_style` VARCHAR(50) DEFAULT 'none',
    `default_download_format` VARCHAR(10) DEFAULT 'png',
    
    -- Design Defaults
    `default_corner_style` VARCHAR(50) DEFAULT 'square',
    `default_dot_style` VARCHAR(50) DEFAULT 'square',
    `default_marker_border_style` VARCHAR(50) DEFAULT 'square',
    `default_marker_center_style` VARCHAR(50) DEFAULT 'square',
    
    -- Logo Defaults
    `default_logo_color` VARCHAR(7) DEFAULT '#9945ff',
    `default_logo_size` DECIMAL(3,2) DEFAULT 0.30,
    `default_logo_remove_bg` TINYINT(1) DEFAULT 0,
    
    -- Advanced Defaults
    `default_gradient_enabled` TINYINT(1) DEFAULT 0,
    `default_gradient_color` VARCHAR(7) DEFAULT '#9945ff',
    `default_transparent_bg` TINYINT(1) DEFAULT 0,
    `default_custom_marker_color` TINYINT(1) DEFAULT 0,
    `default_marker_color` VARCHAR(7) DEFAULT '#9945ff',
    
    -- User Preferences
    `auto_save` TINYINT(1) DEFAULT 1,
    `email_notifications` TINYINT(1) DEFAULT 0,
    `scan_notification_threshold` INT DEFAULT 10,
    
    -- API Settings
    `api_enabled` TINYINT(1) DEFAULT 0,
    `api_key` VARCHAR(64) NULL,
    `api_rate_limit` INT DEFAULT 100,
    
    -- Timestamps
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_api_key` (`api_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
