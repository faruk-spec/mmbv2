-- Add settings columns to user_profiles table
-- This migration adds columns for theme and notification preferences

-- Add theme_preference column if it doesn't exist
ALTER TABLE `user_profiles` 
ADD COLUMN IF NOT EXISTS `theme_preference` VARCHAR(20) DEFAULT 'dark' AFTER `language`;

-- Add notification preference columns if they don't exist
ALTER TABLE `user_profiles` 
ADD COLUMN IF NOT EXISTS `email_notifications` TINYINT(1) DEFAULT 1 AFTER `theme_preference`,
ADD COLUMN IF NOT EXISTS `security_alerts` TINYINT(1) DEFAULT 1 AFTER `email_notifications`,
ADD COLUMN IF NOT EXISTS `product_updates` TINYINT(1) DEFAULT 0 AFTER `security_alerts`;

-- Add display settings column if it doesn't exist
ALTER TABLE `user_profiles` 
ADD COLUMN IF NOT EXISTS `display_settings` JSON NULL AFTER `product_updates`;

-- Add project settings column if it doesn't exist
ALTER TABLE `user_profiles` 
ADD COLUMN IF NOT EXISTS `project_settings` JSON NULL AFTER `display_settings`;

-- Verification query
SELECT 'User profile settings columns added successfully' AS status;
SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'user_profiles' 
AND TABLE_SCHEMA = DATABASE()
ORDER BY ORDINAL_POSITION;
