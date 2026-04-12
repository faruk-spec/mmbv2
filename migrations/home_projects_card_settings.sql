-- Add per-card display settings to home_projects
ALTER TABLE `home_projects`
    ADD COLUMN IF NOT EXISTS `show_title`      TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = show project title on card, 0 = hide',
    ADD COLUMN IF NOT EXISTS `thumb_intensity` TINYINT UNSIGNED NOT NULL DEFAULT 60 COMMENT 'Thumbnail image opacity 0-100';
