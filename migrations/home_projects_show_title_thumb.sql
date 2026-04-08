-- Add show_title and thumb_intensity columns to home_projects
-- Required for per-card title visibility and thumbnail opacity controls

ALTER TABLE `home_projects`
    ADD COLUMN IF NOT EXISTS `show_title`      TINYINT(1)         NOT NULL DEFAULT 1  AFTER `is_enabled`,
    ADD COLUMN IF NOT EXISTS `thumb_intensity` TINYINT(3) UNSIGNED NOT NULL DEFAULT 60 AFTER `show_title`;
