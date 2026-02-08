-- Complete Latest Features Migration
-- Date: 2026-02-08
-- Description: Consolidates all latest QR features including gradient, markers, frame customization

-- Ensure all columns exist in qr_codes table
ALTER TABLE `qr_codes` 
ADD COLUMN IF NOT EXISTS `error_correction` VARCHAR(1) DEFAULT 'H' COMMENT 'Error correction level: L, M, Q, H' AFTER `background_color`,
ADD COLUMN IF NOT EXISTS `gradient_enabled` TINYINT(1) DEFAULT 0 COMMENT 'Enable gradient for foreground' AFTER `foreground_color`,
ADD COLUMN IF NOT EXISTS `gradient_color` VARCHAR(7) NULL COMMENT 'Gradient end color' AFTER `gradient_enabled`,
ADD COLUMN IF NOT EXISTS `transparent_bg` TINYINT(1) DEFAULT 0 COMMENT 'Transparent background' AFTER `background_color`,
ADD COLUMN IF NOT EXISTS `dot_style` VARCHAR(50) DEFAULT 'square' COMMENT 'Dot pattern style' AFTER `error_correction`,
ADD COLUMN IF NOT EXISTS `corner_style` VARCHAR(50) DEFAULT 'square' COMMENT 'Corner square style' AFTER `dot_style`,
ADD COLUMN IF NOT EXISTS `marker_color` VARCHAR(7) NULL COMMENT 'Custom marker color' AFTER `corner_style`,
ADD COLUMN IF NOT EXISTS `marker_border_style` VARCHAR(50) DEFAULT 'square' COMMENT 'Marker border style' AFTER `marker_color`,
ADD COLUMN IF NOT EXISTS `marker_center_style` VARCHAR(50) DEFAULT 'square' COMMENT 'Marker center style' AFTER `marker_border_style`,
ADD COLUMN IF NOT EXISTS `custom_marker_color` TINYINT(1) DEFAULT 0 COMMENT 'Enable custom marker color' AFTER `marker_center_style`,
ADD COLUMN IF NOT EXISTS `logo_size` DECIMAL(3,2) DEFAULT 0.3 COMMENT 'Logo size ratio (0.1-0.5)' AFTER `logo_path`,
ADD COLUMN IF NOT EXISTS `logo_remove_bg` TINYINT(1) DEFAULT 0 COMMENT 'Remove background behind logo' AFTER `logo_size`,
ADD COLUMN IF NOT EXISTS `logo_option` VARCHAR(20) DEFAULT 'none' COMMENT 'Logo option: none, default, upload' AFTER `logo_remove_bg`,
ADD COLUMN IF NOT EXISTS `default_logo` VARCHAR(50) NULL COMMENT 'Default logo icon name' AFTER `logo_option`,
ADD COLUMN IF NOT EXISTS `frame_color` VARCHAR(7) NULL COMMENT 'Frame color' AFTER `frame_style`,
ADD COLUMN IF NOT EXISTS `frame_label` VARCHAR(100) NULL COMMENT 'Frame label text' AFTER `frame_color`,
ADD COLUMN IF NOT EXISTS `frame_font` VARCHAR(50) DEFAULT 'Arial' COMMENT 'Frame font family' AFTER `frame_label`,
ADD COLUMN IF NOT EXISTS `bg_image_path` VARCHAR(255) NULL COMMENT 'Background image path' AFTER `background_color`,
ADD COLUMN IF NOT EXISTS `template_id` INT UNSIGNED NULL COMMENT 'Template used' AFTER `campaign_id`,
ADD COLUMN IF NOT EXISTS `scan_limit` INT DEFAULT -1 COMMENT 'Max scans allowed (-1 = unlimited)' AFTER `scan_count`,
ADD COLUMN IF NOT EXISTS `unique_scans` INT DEFAULT 0 COMMENT 'Unique IP scans' AFTER `scan_limit`,
ADD COLUMN IF NOT EXISTS `deleted_at` TIMESTAMP NULL COMMENT 'Soft delete timestamp' AFTER `updated_at`;

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS `idx_gradient_enabled` ON `qr_codes`(`gradient_enabled`);
CREATE INDEX IF NOT EXISTS `idx_is_dynamic` ON `qr_codes`(`is_dynamic`);
CREATE INDEX IF NOT EXISTS `idx_template_id` ON `qr_codes`(`template_id`);
CREATE INDEX IF NOT EXISTS `idx_deleted_at` ON `qr_codes`(`deleted_at`);
CREATE INDEX IF NOT EXISTS `idx_expires_at` ON `qr_codes`(`expires_at`);

-- Ensure qr_user_settings table exists with all columns
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
    
    -- Design Preferences
    `default_dot_style` VARCHAR(50) DEFAULT 'square',
    `default_corner_style` VARCHAR(50) DEFAULT 'square',
    `default_logo_option` VARCHAR(20) DEFAULT 'none',
    
    -- Preferences
    `auto_save` TINYINT(1) DEFAULT 1,
    `email_notifications` TINYINT(1) DEFAULT 0,
    `scan_notification_threshold` INT DEFAULT 10,
    
    -- API Settings
    `api_key` VARCHAR(64) NULL,
    `api_enabled` TINYINT(1) DEFAULT 0,
    `api_rate_limit` INT DEFAULT 100,
    
    -- UI Preferences
    `sections_collapsed` JSON NULL COMMENT 'Collapsed sections state',
    
    -- Timestamps
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_api_key` (`api_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add sections_collapsed column if it doesn't exist
ALTER TABLE `qr_user_settings` 
ADD COLUMN IF NOT EXISTS `sections_collapsed` JSON NULL COMMENT 'Collapsed sections state' AFTER `api_rate_limit`;

-- Update existing records with default values where NULL
UPDATE `qr_codes` SET 
    `error_correction` = 'H' WHERE `error_correction` IS NULL,
    `dot_style` = 'square' WHERE `dot_style` IS NULL,
    `corner_style` = 'square' WHERE `corner_style` IS NULL,
    `logo_size` = 0.3 WHERE `logo_size` IS NULL,
    `logo_option` = 'none' WHERE `logo_option` IS NULL,
    `scan_limit` = -1 WHERE `scan_limit` IS NULL,
    `gradient_enabled` = 0 WHERE `gradient_enabled` IS NULL,
    `transparent_bg` = 0 WHERE `transparent_bg` IS NULL,
    `logo_remove_bg` = 0 WHERE `logo_remove_bg` IS NULL,
    `custom_marker_color` = 0 WHERE `custom_marker_color` IS NULL;

-- Success message
SELECT 'Complete latest features migration completed successfully!' as message,
       'All QR code features are now available including:' as info1,
       '- Gradient colors with custom marker colors' as feature1,
       '- Advanced dot and corner styles' as feature2,
       '- Logo options (none, default icons, upload)' as feature3,
       '- Frame customization with colors and labels' as feature4,
       '- Background images and transparency' as feature5,
       '- Collapsible sections UI preferences' as feature6;
