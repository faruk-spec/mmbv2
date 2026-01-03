-- Add settings columns to user_profiles table
-- This migration adds columns for theme and notification preferences
-- Note: IF NOT EXISTS is not supported in MariaDB ALTER TABLE, so we check first

-- Add theme_preference column
SET @column_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user_profiles' AND COLUMN_NAME = 'theme_preference');
SET @sql = IF(@column_exists = 0, 
    'ALTER TABLE `user_profiles` ADD COLUMN `theme_preference` VARCHAR(20) DEFAULT ''dark'' AFTER `language`', 
    'SELECT ''theme_preference column already exists'' AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add email_notifications column
SET @column_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user_profiles' AND COLUMN_NAME = 'email_notifications');
SET @sql = IF(@column_exists = 0, 
    'ALTER TABLE `user_profiles` ADD COLUMN `email_notifications` TINYINT(1) DEFAULT 1 AFTER `theme_preference`', 
    'SELECT ''email_notifications column already exists'' AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add security_alerts column
SET @column_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user_profiles' AND COLUMN_NAME = 'security_alerts');
SET @sql = IF(@column_exists = 0, 
    'ALTER TABLE `user_profiles` ADD COLUMN `security_alerts` TINYINT(1) DEFAULT 1 AFTER `email_notifications`', 
    'SELECT ''security_alerts column already exists'' AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add product_updates column
SET @column_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user_profiles' AND COLUMN_NAME = 'product_updates');
SET @sql = IF(@column_exists = 0, 
    'ALTER TABLE `user_profiles` ADD COLUMN `product_updates` TINYINT(1) DEFAULT 0 AFTER `security_alerts`', 
    'SELECT ''product_updates column already exists'' AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add display_settings column
SET @column_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user_profiles' AND COLUMN_NAME = 'display_settings');
SET @sql = IF(@column_exists = 0, 
    'ALTER TABLE `user_profiles` ADD COLUMN `display_settings` JSON NULL AFTER `product_updates`', 
    'SELECT ''display_settings column already exists'' AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add project_settings column
SET @column_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user_profiles' AND COLUMN_NAME = 'project_settings');
SET @sql = IF(@column_exists = 0, 
    'ALTER TABLE `user_profiles` ADD COLUMN `project_settings` JSON NULL AFTER `display_settings`', 
    'SELECT ''project_settings column already exists'' AS info');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verification: Show all columns in user_profiles table
SELECT 'User profile settings columns migration completed' AS status;
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'user_profiles' 
AND TABLE_SCHEMA = DATABASE()
AND COLUMN_NAME IN ('theme_preference', 'email_notifications', 'security_alerts', 'product_updates', 'display_settings', 'project_settings')
ORDER BY ORDINAL_POSITION;
