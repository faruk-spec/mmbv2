-- Add universal thumbnail intensity settings to home_content (projects_section row)
ALTER TABLE `home_content`
    ADD COLUMN IF NOT EXISTS `global_thumb_intensity`    TINYINT UNSIGNED NOT NULL DEFAULT 60  COMMENT 'Universal thumbnail opacity 0-100',
    ADD COLUMN IF NOT EXISTS `override_thumb_intensity`  TINYINT(1)       NOT NULL DEFAULT 0   COMMENT '1 = apply global intensity to all project cards, ignoring per-card value';
