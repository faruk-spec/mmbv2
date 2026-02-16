-- Add Settings Page Default Columns Migration
-- Date: 2026-02-16
-- Description: Adds columns for Design, Logo, and Advanced default settings to qr_user_settings

-- Design Defaults Tab columns (some already exist in complete_latest_features.sql)
-- default_corner_style and default_dot_style already exist, just add the marker styles
ALTER TABLE `qr_user_settings` 
ADD COLUMN IF NOT EXISTS `default_marker_border_style` VARCHAR(50) DEFAULT 'square' COMMENT 'Default marker border style' AFTER `default_download_format`,
ADD COLUMN IF NOT EXISTS `default_marker_center_style` VARCHAR(50) DEFAULT 'square' COMMENT 'Default marker center style' AFTER `default_marker_border_style`;

-- Logo Defaults Tab columns
ALTER TABLE `qr_user_settings` 
ADD COLUMN IF NOT EXISTS `default_logo_color` VARCHAR(7) DEFAULT '#9945ff' COMMENT 'Default logo color' AFTER `default_marker_center_style`,
ADD COLUMN IF NOT EXISTS `default_logo_size` DECIMAL(3,2) DEFAULT 0.30 COMMENT 'Default logo size ratio (0.1-0.5)' AFTER `default_logo_color`,
ADD COLUMN IF NOT EXISTS `default_logo_remove_bg` TINYINT(1) DEFAULT 0 COMMENT 'Default: remove background behind logo' AFTER `default_logo_size`;

-- Advanced Defaults Tab columns
ALTER TABLE `qr_user_settings` 
ADD COLUMN IF NOT EXISTS `default_gradient_enabled` TINYINT(1) DEFAULT 0 COMMENT 'Default: enable gradient for foreground' AFTER `default_logo_remove_bg`,
ADD COLUMN IF NOT EXISTS `default_gradient_color` VARCHAR(7) DEFAULT '#9945ff' COMMENT 'Default gradient end color' AFTER `default_gradient_enabled`,
ADD COLUMN IF NOT EXISTS `default_transparent_bg` TINYINT(1) DEFAULT 0 COMMENT 'Default: transparent background' AFTER `default_gradient_color`,
ADD COLUMN IF NOT EXISTS `default_custom_marker_color` TINYINT(1) DEFAULT 0 COMMENT 'Default: enable custom marker color' AFTER `default_transparent_bg`,
ADD COLUMN IF NOT EXISTS `default_marker_color` VARCHAR(7) DEFAULT '#9945ff' COMMENT 'Default marker color when custom enabled' AFTER `default_custom_marker_color`;

-- Create indexes for better performance on new columns
CREATE INDEX IF NOT EXISTS `idx_gradient_defaults` ON `qr_user_settings`(`default_gradient_enabled`);
CREATE INDEX IF NOT EXISTS `idx_marker_defaults` ON `qr_user_settings`(`default_custom_marker_color`);

-- Success message
SELECT 'Settings defaults migration completed successfully!' as message,
       'New default columns added for:' as info1,
       '- Design Defaults (marker border and center styles)' as feature1,
       '- Logo Defaults (color, size, remove background)' as feature2,
       '- Advanced Defaults (gradient, transparent bg, marker color)' as feature3;
