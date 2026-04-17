-- Theme Settings table for multi-theme system
-- Stores the active theme, custom overrides, and per-user preferences

CREATE TABLE IF NOT EXISTS `theme_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT DEFAULT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default theme settings
INSERT INTO `theme_settings` (`setting_key`, `setting_value`) VALUES
    ('active_theme', 'default'),
    ('default_mode', 'dark'),
    ('custom_overrides', NULL)
ON DUPLICATE KEY UPDATE `setting_key` = `setting_key`;

-- Add ui_theme column to user_profiles if it doesn't exist
-- This stores the user's selected UI theme (default, soft, corporate, neon)
ALTER TABLE `user_profiles`
    ADD COLUMN IF NOT EXISTS `ui_theme` VARCHAR(30) DEFAULT NULL AFTER `theme_preference`;
