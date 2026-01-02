-- Home Page Statistics and Timeline Tables
-- This migration adds tables for animated statistics counts and timeline features

-- Statistics/Counts table for animated numbers section
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

-- Timeline items table for company milestones/process steps
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
