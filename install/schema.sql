-- MyMultiBranch Database Schema
-- Main Database

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('super_admin', 'admin', 'project_admin', 'user') DEFAULT 'user',
    `status` ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    `email_verified_at` TIMESTAMP NULL,
    `email_verification_token` VARCHAR(64) NULL,
    `two_factor_secret` VARCHAR(255) NULL,
    `two_factor_enabled` TINYINT(1) DEFAULT 0,
    `last_login_at` TIMESTAMP NULL,
    `last_login_ip` VARCHAR(45) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`),
    INDEX `idx_status` (`status`),
    INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User profiles table
CREATE TABLE IF NOT EXISTS `user_profiles` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `avatar` VARCHAR(255) NULL,
    `bio` TEXT NULL,
    `phone` VARCHAR(20) NULL,
    `timezone` VARCHAR(50) DEFAULT 'UTC',
    `language` VARCHAR(10) DEFAULT 'en',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User devices table
CREATE TABLE IF NOT EXISTS `user_devices` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `device_name` VARCHAR(100) NULL,
    `device_type` VARCHAR(50) NULL,
    `browser` VARCHAR(100) NULL,
    `platform` VARCHAR(50) NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `last_active_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User remember tokens table
CREATE TABLE IF NOT EXISTS `user_remember_tokens` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `token` VARCHAR(64) NOT NULL,
    `device_info` JSON NULL,
    `expires_at` TIMESTAMP NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_token` (`token`),
    INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password resets table
CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL,
    `token` VARCHAR(64) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`),
    INDEX `idx_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity logs table
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NULL,
    `action` VARCHAR(100) NOT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(500) NULL,
    `data` JSON NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings table
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    `value` TEXT NULL,
    `type` VARCHAR(20) DEFAULT 'string',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Failed logins table
CREATE TABLE IF NOT EXISTS `failed_logins` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(255) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `attempted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ip` (`ip_address`),
    INDEX `idx_attempted_at` (`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blocked IPs table
CREATE TABLE IF NOT EXISTS `blocked_ips` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ip_address` VARCHAR(45) NOT NULL,
    `reason` VARCHAR(255) NULL,
    `blocked_by` INT UNSIGNED NULL,
    `expires_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`blocked_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_ip` (`ip_address`),
    INDEX `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Project permissions table
CREATE TABLE IF NOT EXISTS `project_permissions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `project_name` VARCHAR(50) NOT NULL,
    `has_access` TINYINT(1) DEFAULT 1,
    `role` VARCHAR(50) DEFAULT 'user',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `idx_user_project` (`user_id`, `project_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT INTO `settings` (`key`, `value`, `type`) VALUES
('site_name', 'MyMultiBranch', 'string'),
('site_description', 'Multi-Project Platform', 'string'),
('maintenance_mode', '0', 'boolean'),
('registration_enabled', '1', 'boolean'),
('contact_email', 'admin@example.com', 'string');

-- Home page content table for hero section and general settings
CREATE TABLE IF NOT EXISTS `home_content` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `section` VARCHAR(50) NOT NULL UNIQUE,
    `title` VARCHAR(255) NULL,
    `subtitle` TEXT NULL,
    `description` TEXT NULL,
    `image_url` VARCHAR(255) NULL,
    `button_text` VARCHAR(100) NULL,
    `button_url` VARCHAR(255) NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_section` (`section`),
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Projects customization table
CREATE TABLE IF NOT EXISTS `home_projects` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `project_key` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `image_url` VARCHAR(255) NULL,
    `icon` VARCHAR(50) NULL,
    `color` VARCHAR(20) NULL,
    `is_enabled` TINYINT(1) DEFAULT 1,
    `sort_order` INT DEFAULT 0,
    `database_name` VARCHAR(50) NULL,
    `url` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_key` (`project_key`),
    INDEX `idx_enabled` (`is_enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default home page content
INSERT INTO `home_content` (`section`, `title`, `subtitle`, `description`, `is_active`, `sort_order`) VALUES
('hero', 'Welcome to Your Platform', 'A powerful multi-project platform', 'A powerful multi-project platform with centralized authentication, unified admin panel, and secure architecture.', 1, 1),
('projects_section', 'Explore Our Super Fast Products', NULL, NULL, 1, 2);

-- Insert default projects
INSERT INTO `home_projects` (`project_key`, `name`, `description`, `icon`, `color`, `is_enabled`, `sort_order`, `database_name`, `url`) VALUES
('codexpro', 'CodeXPro', 'Advanced code editor and IDE platform', 'code', '#00f0ff', 1, 1, 'mmb_codexpro', '/projects/codexpro'),
('devzone', 'DevZone', 'Developer collaboration and project management', 'users', '#ff2ec4', 1, 2, 'mmb_devzone', '/projects/devzone'),
('imgtxt', 'ImgTxt', 'Image to text converter and OCR tool', 'image', '#00ff88', 1, 3, 'mmb_imgtxt', '/projects/imgtxt'),
('proshare', 'ProShare', 'Secure file sharing platform', 'share-2', '#ffaa00', 1, 4, 'mmb_proshare', '/projects/proshare'),
('qr', 'QR Generator', 'QR code generation and management', 'grid', '#9945ff', 1, 5, 'mmb_qr', '/projects/qr'),
('resumex', 'ResumeX', 'Professional resume builder', 'file-text', '#ff6b6b', 1, 6, 'mmb_resumex', '/projects/resumex');

-- Home page statistics table
CREATE TABLE IF NOT EXISTS `home_stats` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `label` VARCHAR(100) NOT NULL,
    `count_value` INT UNSIGNED NOT NULL DEFAULT 0,
    `prefix` VARCHAR(10) NULL,
    `suffix` VARCHAR(10) NULL,
    `icon` VARCHAR(50) NULL,
    `color` VARCHAR(20) DEFAULT '#00f0ff',
    `is_active` TINYINT(1) DEFAULT 1,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_active` (`is_active`),
    INDEX `idx_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Home page timeline table
CREATE TABLE IF NOT EXISTS `home_timeline` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `date_display` VARCHAR(50) NULL,
    `icon` VARCHAR(50) NULL,
    `color` VARCHAR(20) DEFAULT '#00f0ff',
    `is_active` TINYINT(1) DEFAULT 1,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_active` (`is_active`),
    INDEX `idx_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default statistics
INSERT INTO `home_stats` (`label`, `count_value`, `suffix`, `icon`, `color`, `is_active`, `sort_order`) VALUES
('Active Users', 10000, '+', 'users', '#00f0ff', 1, 1),
('Applications', 6, NULL, 'grid', '#ff2ec4', 1, 2),
('Projects Completed', 500, '+', 'check-circle', '#00ff88', 1, 3),
('Uptime', 99, '%', 'activity', '#ffaa00', 1, 4);

-- Insert default timeline items
INSERT INTO `home_timeline` (`title`, `description`, `date_display`, `icon`, `color`, `is_active`, `sort_order`) VALUES
('Platform Launch', 'Launched MyMultiBranch platform with core authentication system', '2024', 'rocket', '#00f0ff', 1, 1),
('Multi-Project Support', 'Added ability to manage multiple applications from single dashboard', '2024 Q2', 'grid', '#ff2ec4', 1, 2),
('Enhanced Security', 'Implemented advanced security features including 2FA and audit logs', '2024 Q3', 'shield', '#00ff88', 1, 3),
('API Integration', 'Released comprehensive REST API for external integrations', '2024 Q4', 'code', '#ffaa00', 1, 4),
('Future Plans', 'AI-powered features and advanced analytics coming soon', 'Coming Soon', 'star', '#9945ff', 1, 5);

-- Home sections table for customizable section headings
CREATE TABLE IF NOT EXISTS `home_sections` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `section_key` VARCHAR(50) NOT NULL UNIQUE,
    `heading` VARCHAR(255) NOT NULL,
    `subheading` TEXT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_active` (`is_active`),
    INDEX `idx_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default section headings
INSERT INTO `home_sections` (`section_key`, `heading`, `subheading`, `is_active`, `sort_order`) VALUES
('stats', 'Our Impact in Numbers', 'Trusted by developers and teams worldwide', 1, 1),
('timeline', 'Our Journey', 'Milestones and achievements that shaped our platform', 1, 2),
('features', '🚀 Platform Features', 'Powerful capabilities across all projects', 1, 3);
