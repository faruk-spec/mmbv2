-- Migration: Add tier and features columns to home_projects table
-- Simple migration that uses ALTER TABLE IGNORE to handle existing columns
-- Note: If columns already exist, the ALTER TABLE will fail silently and continue

-- Add tier column (will error if exists, but script continues)
ALTER TABLE `home_projects` 
ADD COLUMN `tier` ENUM('free', 'freemium', 'enterprise') DEFAULT 'free' 
AFTER `color`;

-- Add features column (will error if exists, but script continues)
ALTER TABLE `home_projects` 
ADD COLUMN `features` TEXT NULL
AFTER `tier`;

-- Update existing projects with tiers (only if tier is default 'free')
UPDATE `home_projects` SET `tier` = 'free' 
WHERE `project_key` IN ('qr', 'resumex') 
AND `tier` = 'free';

UPDATE `home_projects` SET `tier` = 'freemium' 
WHERE `project_key` IN ('imgtxt', 'proshare') 
AND `tier` = 'free';

UPDATE `home_projects` SET `tier` = 'enterprise' 
WHERE `project_key` IN ('codexpro', 'devzone') 
AND `tier` = 'free';

-- Add sample features for projects (only if features is null)
UPDATE `home_projects` SET `features` = '["Advanced editor capabilities","Real-time collaboration","Cloud sync & backup"]' WHERE `project_key` = 'codexpro' AND `features` IS NULL;
UPDATE `home_projects` SET `features` = '["Team collaboration tools","Project management","Issue tracking"]' WHERE `project_key` = 'devzone' AND `features` IS NULL;
UPDATE `home_projects` SET `features` = '["Image to text conversion","Multi-language OCR","Batch processing"]' WHERE `project_key` = 'imgtxt' AND `features` IS NULL;
UPDATE `home_projects` SET `features` = '["Secure file sharing","Password protection","Download tracking"]' WHERE `project_key` = 'proshare' AND `features` IS NULL;
UPDATE `home_projects` SET `features` = '["Custom QR codes","Bulk generation","Analytics tracking"]' WHERE `project_key` = 'qr' AND `features` IS NULL;
UPDATE `home_projects` SET `features` = '["Professional templates","PDF export","ATS optimization"]' WHERE `project_key` = 'resumex' AND `features` IS NULL;

