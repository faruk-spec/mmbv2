-- Add show_projects_dropdown_to_user column to navbar_settings
-- This allows admins to control whether non-admin users see the Projects dropdown

ALTER TABLE `navbar_settings`
    ADD COLUMN IF NOT EXISTS `show_projects_dropdown_to_user` TINYINT(1) NOT NULL DEFAULT 1
    AFTER `show_projects_dropdown`;

UPDATE `navbar_settings` SET `show_projects_dropdown_to_user` = 1 WHERE `show_projects_dropdown_to_user` IS NULL;
