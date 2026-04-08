-- Add thumb_intensity column to home_projects
-- Required for per-card thumbnail opacity controls

ALTER TABLE `home_projects`
    ADD COLUMN IF NOT EXISTS `thumb_intensity` TINYINT(3) UNSIGNED NOT NULL DEFAULT 60 AFTER `is_enabled`;
