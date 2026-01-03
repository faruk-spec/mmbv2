-- Add timezone and date/time format settings
-- This migration adds system timezone configuration

-- Insert timezone settings if they don't exist
INSERT INTO `settings` (`key`, `value`, `type`, `created_at`)
SELECT 'system_timezone', 'UTC', 'string', NOW()
WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `key` = 'system_timezone');

INSERT INTO `settings` (`key`, `value`, `type`, `created_at`)
SELECT 'date_format', 'M d, Y', 'string', NOW()
WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `key` = 'date_format');

INSERT INTO `settings` (`key`, `value`, `type`, `created_at`)
SELECT 'time_format', 'g:i A', 'string', NOW()
WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `key` = 'time_format');
