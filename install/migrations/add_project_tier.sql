-- Add tier column to home_projects table
ALTER TABLE `home_projects` 
ADD COLUMN `tier` ENUM('free', 'freemium', 'enterprise') DEFAULT 'free' 
AFTER `color`;

-- Update existing projects with tiers
UPDATE `home_projects` SET `tier` = 'free' WHERE `project_key` IN ('qr', 'resumex');
UPDATE `home_projects` SET `tier` = 'freemium' WHERE `project_key` IN ('imgtxt', 'proshare');
UPDATE `home_projects` SET `tier` = 'enterprise' WHERE `project_key` IN ('codexpro', 'devzone');
