-- CodeXPro user settings – extra columns for enhanced editor configuration
-- Run once; IF NOT EXISTS guards make it idempotent

ALTER TABLE `codexpro_user_settings`
    ADD COLUMN IF NOT EXISTS `font_family`       VARCHAR(80)  DEFAULT 'JetBrains Mono' AFTER `font_size`,
    ADD COLUMN IF NOT EXISTS `word_wrap`          TINYINT(1)   DEFAULT 0 AFTER `auto_preview`,
    ADD COLUMN IF NOT EXISTS `line_numbers`       TINYINT(1)   DEFAULT 1 AFTER `word_wrap`,
    ADD COLUMN IF NOT EXISTS `bracket_matching`   TINYINT(1)   DEFAULT 1 AFTER `line_numbers`,
    ADD COLUMN IF NOT EXISTS `auto_indent`        TINYINT(1)   DEFAULT 1 AFTER `bracket_matching`,
    ADD COLUMN IF NOT EXISTS `indent_guides`      TINYINT(1)   DEFAULT 1 AFTER `auto_indent`,
    ADD COLUMN IF NOT EXISTS `highlight_line`     TINYINT(1)   DEFAULT 1 AFTER `indent_guides`,
    ADD COLUMN IF NOT EXISTS `show_minimap`       TINYINT(1)   DEFAULT 0 AFTER `highlight_line`;
