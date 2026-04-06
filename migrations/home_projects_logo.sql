-- Add logo_url column to home_projects for square logo upload
ALTER TABLE `home_projects`
    ADD COLUMN IF NOT EXISTS `logo_url` VARCHAR(255) NULL AFTER `image_url`;
