-- MyMultiBranch Database Schema
-- Main Database

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` VARCHAR(100) NOT NULL DEFAULT 'user',
    `app_access` JSON NULL DEFAULT NULL COMMENT 'JSON array of allowed app slugs. NULL = unrestricted.',
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
    `user_name` VARCHAR(255) NULL COMMENT 'Denormalized user display name for fast access',
    `action` VARCHAR(100) NOT NULL,
    `module` VARCHAR(100) NULL,
    `tenant_id` INT UNSIGNED NULL,
    `resource_type` VARCHAR(100) NULL,
    `resource_id` VARCHAR(100) NULL,
    `entity_name` VARCHAR(255) NULL COMMENT 'Human-readable name of the entity being acted on',
    `user_role` VARCHAR(50) NULL,
    `old_values` JSON NULL,
    `new_values` JSON NULL,
    `changes` JSON NULL COMMENT 'Field-level diff: {"field":{"old":"x","new":"y"}}',
    `readable_message` VARCHAR(500) NULL,
    `request_id` VARCHAR(64) NULL,
    `device` VARCHAR(100) NULL,
    `browser` VARCHAR(100) NULL,
    `status` ENUM('success','failure','pending') NOT NULL DEFAULT 'success',
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(500) NULL,
    `data` JSON NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_user_name` (`user_name`(100)),
    INDEX `idx_action` (`action`),
    INDEX `idx_module` (`module`),
    INDEX `idx_tenant_id` (`tenant_id`),
    INDEX `idx_resource` (`resource_type`, `resource_id`(50)),
    INDEX `idx_entity_name` (`entity_name`(100)),
    INDEX `idx_user_role` (`user_role`),
    INDEX `idx_status` (`status`),
    INDEX `idx_request_id` (`request_id`),
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
('proshare', 'ProShare', 'Secure file sharing platform', 'share-2', '#ffaa00', 1, 3, 'mmb_proshare', '/projects/proshare'),
('qr', 'QR Generator', 'QR code generation and management', 'grid', '#9945ff', 1, 4, 'mmb_qr', '/projects/qr'),
('resumex', 'ResumeX', 'Professional resume builder', 'file-text', '#ff6b6b', 1, 5, 'mmb_resumex', '/projects/resumex');

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

-- Admin user permissions (granular admin panel access)
CREATE TABLE IF NOT EXISTS `admin_user_permissions` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`         INT UNSIGNED NOT NULL,
    `permission_key`  VARCHAR(100) NOT NULL,
    `granted_by`      INT UNSIGNED NULL,
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_user_perm` (`user_id`, `permission_key`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_permission_key` (`permission_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ResumeX resume builder
CREATE TABLE IF NOT EXISTS `resumex_resumes` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`        INT UNSIGNED NOT NULL,
    `title`          VARCHAR(255) NOT NULL DEFAULT 'My Resume',
    `template`       VARCHAR(100) NOT NULL DEFAULT 'ocean-blue',
    `resume_data`    LONGTEXT     NULL,
    `theme_settings` LONGTEXT     NULL,
    `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_updated_at` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CardX ID Card Generator
CREATE TABLE IF NOT EXISTS `idcard_cards` (
    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`        INT UNSIGNED NOT NULL,
    `template_key`   VARCHAR(50)  NOT NULL DEFAULT 'corporate',
    `card_number`    VARCHAR(50)  NOT NULL,
    `card_data`      JSON         NOT NULL COMMENT 'All field values (name, designation, etc.)',
    `design`         JSON         NULL     COMMENT 'Custom colours / fonts / layout overrides',
    `photo_path`     VARCHAR(500) NULL     COMMENT 'Uploaded profile photo path',
    `logo_path`      VARCHAR(500) NULL     COMMENT 'Uploaded organisation logo path',
    `ai_prompt`      TEXT         NULL     COMMENT 'AI prompt used to generate suggestions',
    `ai_suggestions` JSON         NULL     COMMENT 'Cached AI design suggestions',
    `bulk_job_id`    INT UNSIGNED NULL,
    `status`         ENUM('draft','generated') DEFAULT 'generated',
    `created_at`     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP    NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_ic_user`    (`user_id`),
    INDEX `idx_ic_tpl`     (`template_key`),
    INDEX `idx_ic_created` (`created_at`),
    INDEX `idx_ic_bulk`    (`bulk_job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `idcard_settings` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `setting_key`   VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT         NULL,
    `created_at`    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP    NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_ics_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `idcard_bulk_jobs` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`       INT UNSIGNED NOT NULL,
    `template_key`  VARCHAR(50)  NOT NULL DEFAULT 'corporate',
    `total_rows`    INT UNSIGNED NOT NULL DEFAULT 0,
    `completed`     INT UNSIGNED NOT NULL DEFAULT 0,
    `failed`        INT UNSIGNED NOT NULL DEFAULT 0,
    `status`        ENUM('pending','processing','done','error') DEFAULT 'pending',
    `created_at`    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP    NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_ibj_user`    (`user_id`),
    INDEX `idx_ibj_status`  (`status`),
    INDEX `idx_ibj_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
