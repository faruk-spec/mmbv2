-- Migration: Add Enhanced QR Features
-- Date: 2026-02-07
-- Description: Adds fields for logo, frame style, error correction, and other advanced features

-- Add new columns to qr_codes table if they don't exist
ALTER TABLE `qr_codes` 
ADD COLUMN IF NOT EXISTS `error_correction` ENUM('L', 'M', 'Q', 'H') DEFAULT 'H' COMMENT 'Error correction level' AFTER `background_color`,
ADD COLUMN IF NOT EXISTS `logo_size` INT DEFAULT 20 COMMENT 'Logo size as percentage of QR size' AFTER `logo_path`,
ADD COLUMN IF NOT EXISTS `corner_style` VARCHAR(50) DEFAULT 'square' COMMENT 'square, rounded, dots, etc.' AFTER `frame_style`,
ADD COLUMN IF NOT EXISTS `gradient_start` VARCHAR(7) NULL COMMENT 'Gradient start color' AFTER `foreground_color`,
ADD COLUMN IF NOT EXISTS `gradient_end` VARCHAR(7) NULL COMMENT 'Gradient end color' AFTER `gradient_start`,
ADD COLUMN IF NOT EXISTS `template_id` INT UNSIGNED NULL COMMENT 'Design template used' AFTER `corner_style`,
ADD COLUMN IF NOT EXISTS `short_url` VARCHAR(50) UNIQUE NULL COMMENT 'Short URL for dynamic QR' AFTER `short_code`,
ADD COLUMN IF NOT EXISTS `scan_limit` INT DEFAULT -1 COMMENT 'Max scans allowed (-1 = unlimited)' AFTER `scan_count`,
ADD COLUMN IF NOT EXISTS `unique_scans` INT DEFAULT 0 COMMENT 'Count of unique IP scans' AFTER `scan_limit`;

-- Create indexes for new columns
CREATE INDEX IF NOT EXISTS `idx_short_url` ON `qr_codes`(`short_url`);
CREATE INDEX IF NOT EXISTS `idx_template_id` ON `qr_codes`(`template_id`);
CREATE INDEX IF NOT EXISTS `idx_is_dynamic` ON `qr_codes`(`is_dynamic`);
CREATE INDEX IF NOT EXISTS `idx_expires_at` ON `qr_codes`(`expires_at`);

-- Update existing records to have default values
UPDATE `qr_codes` SET `error_correction` = 'H' WHERE `error_correction` IS NULL;
UPDATE `qr_codes` SET `corner_style` = 'square' WHERE `corner_style` IS NULL;
UPDATE `qr_codes` SET `scan_limit` = -1 WHERE `scan_limit` IS NULL;

-- Success message
SELECT 'Enhanced QR features migration completed successfully!' as message;
