-- Migration: Add navbar_sticky column to navbar_settings table
-- This allows admins to control whether the navbar is sticky or not

-- Add navbar_sticky column with default TRUE (enabled)
ALTER TABLE `navbar_settings` 
ADD COLUMN `navbar_sticky` TINYINT(1) DEFAULT 1
AFTER `show_theme_toggle`;

-- Update existing row to have sticky enabled by default
UPDATE `navbar_settings` SET `navbar_sticky` = 1 WHERE `id` = 1;
