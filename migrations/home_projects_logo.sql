-- Add logo_url column to home_projects for square logo upload
-- Compatible with MySQL 5.7+
ALTER TABLE `home_projects`
    ADD COLUMN `logo_url` VARCHAR(255) NULL AFTER `image_url`;
