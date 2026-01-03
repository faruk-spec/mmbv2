-- Add settings columns to user_profiles table
-- This migration adds columns for theme and notification preferences
-- Simple version without INFORMATION_SCHEMA checks

-- Add theme_preference column
ALTER TABLE `user_profiles` 
ADD COLUMN `theme_preference` VARCHAR(20) DEFAULT 'dark' AFTER `language`;

-- Add email_notifications column
ALTER TABLE `user_profiles` 
ADD COLUMN `email_notifications` TINYINT(1) DEFAULT 1 AFTER `theme_preference`;

-- Add security_alerts column
ALTER TABLE `user_profiles` 
ADD COLUMN `security_alerts` TINYINT(1) DEFAULT 1 AFTER `email_notifications`;

-- Add product_updates column
ALTER TABLE `user_profiles` 
ADD COLUMN `product_updates` TINYINT(1) DEFAULT 0 AFTER `security_alerts`;

-- Add display_settings column
ALTER TABLE `user_profiles` 
ADD COLUMN `display_settings` JSON NULL AFTER `product_updates`;

-- Add project_settings column
ALTER TABLE `user_profiles` 
ADD COLUMN `project_settings` JSON NULL AFTER `display_settings`;

-- Note: If you get "Duplicate column name" errors, it means the columns already exist.
-- You can safely ignore those errors as they indicate the migration was already run.

SELECT 'User profile settings columns migration completed' AS status;
