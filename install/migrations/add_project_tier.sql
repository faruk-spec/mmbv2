-- Migration: Add tier and features columns to home_projects table
-- Safe migration that checks for existing columns before adding

-- Add tier column if it doesn't exist
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'home_projects'
AND COLUMN_NAME = 'tier';

SET @query = IF(@col_exists = 0,
    'ALTER TABLE `home_projects` ADD COLUMN `tier` ENUM(''free'', ''freemium'', ''enterprise'') DEFAULT ''free'' AFTER `color`',
    'SELECT "Column tier already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add features column if it doesn't exist
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'home_projects'
AND COLUMN_NAME = 'features';

SET @query = IF(@col_exists = 0,
    'ALTER TABLE `home_projects` ADD COLUMN `features` TEXT NULL AFTER `tier`',
    'SELECT "Column features already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update existing projects with tiers (only if tier is default or null)
UPDATE `home_projects` SET `tier` = 'free' WHERE `project_key` IN ('qr', 'resumex') AND (`tier` IS NULL OR `tier` = 'free');
UPDATE `home_projects` SET `tier` = 'freemium' WHERE `project_key` IN ('imgtxt', 'proshare') AND (`tier` IS NULL OR `tier` = 'free');
UPDATE `home_projects` SET `tier` = 'enterprise' WHERE `project_key` IN ('codexpro', 'devzone') AND (`tier` IS NULL OR `tier` = 'free');

-- Add sample features for projects (only if features is null)
UPDATE `home_projects` SET `features` = '["Advanced editor capabilities","Real-time collaboration","Cloud sync & backup"]' WHERE `project_key` = 'codexpro' AND `features` IS NULL;
UPDATE `home_projects` SET `features` = '["Team collaboration tools","Project management","Issue tracking"]' WHERE `project_key` = 'devzone' AND `features` IS NULL;
UPDATE `home_projects` SET `features` = '["Image to text conversion","Multi-language OCR","Batch processing"]' WHERE `project_key` = 'imgtxt' AND `features` IS NULL;
UPDATE `home_projects` SET `features` = '["Secure file sharing","Password protection","Download tracking"]' WHERE `project_key` = 'proshare' AND `features` IS NULL;
UPDATE `home_projects` SET `features` = '["Custom QR codes","Bulk generation","Analytics tracking"]' WHERE `project_key` = 'qr' AND `features` IS NULL;
UPDATE `home_projects` SET `features` = '["Professional templates","PDF export","ATS optimization"]' WHERE `project_key` = 'resumex' AND `features` IS NULL;

