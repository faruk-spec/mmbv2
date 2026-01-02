-- Add tier column to home_projects table
ALTER TABLE `home_projects` 
ADD COLUMN `tier` ENUM('free', 'freemium', 'enterprise') DEFAULT 'free' 
AFTER `color`;

-- Add features column to store JSON array of features
ALTER TABLE `home_projects` 
ADD COLUMN `features` TEXT NULL
AFTER `tier`;

-- Update existing projects with tiers
UPDATE `home_projects` SET `tier` = 'free' WHERE `project_key` IN ('qr', 'resumex');
UPDATE `home_projects` SET `tier` = 'freemium' WHERE `project_key` IN ('imgtxt', 'proshare');
UPDATE `home_projects` SET `tier` = 'enterprise' WHERE `project_key` IN ('codexpro', 'devzone');

-- Add sample features for projects
UPDATE `home_projects` SET `features` = '["Advanced editor capabilities","Real-time collaboration","Cloud sync & backup"]' WHERE `project_key` = 'codexpro';
UPDATE `home_projects` SET `features` = '["Team collaboration tools","Project management","Issue tracking"]' WHERE `project_key` = 'devzone';
UPDATE `home_projects` SET `features` = '["Image to text conversion","Multi-language OCR","Batch processing"]' WHERE `project_key` = 'imgtxt';
UPDATE `home_projects` SET `features` = '["Secure file sharing","Password protection","Download tracking"]' WHERE `project_key` = 'proshare';
UPDATE `home_projects` SET `features` = '["Custom QR codes","Bulk generation","Analytics tracking"]' WHERE `project_key` = 'qr';
UPDATE `home_projects` SET `features` = '["Professional templates","PDF export","ATS optimization"]' WHERE `project_key` = 'resumex';

