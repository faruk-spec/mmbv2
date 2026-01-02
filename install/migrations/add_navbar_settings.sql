-- Navbar Customization Migration
-- This migration adds tables for customizable navbar settings

-- Navbar Settings Table
CREATE TABLE IF NOT EXISTS `navbar_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `logo_type` ENUM('text', 'image') DEFAULT 'text',
    `logo_text` VARCHAR(100) NULL,
    `logo_image_url` VARCHAR(255) NULL,
    `show_home_link` TINYINT(1) DEFAULT 1,
    `show_dashboard_link` TINYINT(1) DEFAULT 1,
    `show_profile_link` TINYINT(1) DEFAULT 1,
    `show_admin_link` TINYINT(1) DEFAULT 1,
    `show_projects_dropdown` TINYINT(1) DEFAULT 1,
    `show_theme_toggle` TINYINT(1) DEFAULT 1,
    `default_theme` ENUM('dark', 'light') DEFAULT 'dark',
    `navbar_bg_color` VARCHAR(20) DEFAULT '#06060a',
    `navbar_text_color` VARCHAR(20) DEFAULT '#e8eefc',
    `navbar_border_color` VARCHAR(20) DEFAULT '#1a1a2e',
    `custom_css` TEXT NULL,
    `custom_links` JSON NULL COMMENT 'Array of custom menu links {title, url, icon, position}',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default navbar settings
INSERT INTO `navbar_settings` (`logo_type`, `logo_text`, `show_home_link`, `show_dashboard_link`, `show_profile_link`, `show_admin_link`) VALUES
('text', 'MyMultiBranch', 1, 1, 1, 1)
ON DUPLICATE KEY UPDATE
    logo_text = VALUES(logo_text);
