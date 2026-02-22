-- Migration: Add admin QR management tables
-- Creates tables for abuse reports, role features and user feature overrides

-- Abuse Reports table
CREATE TABLE IF NOT EXISTS `qr_abuse_reports` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `qr_id` INT UNSIGNED NOT NULL,
    `reporter_id` INT UNSIGNED NULL,
    `reason` VARCHAR(500) NULL,
    `status` ENUM('pending','resolved','dismissed') DEFAULT 'pending',
    `resolved_by` INT UNSIGNED NULL,
    `resolved_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_qr_id` (`qr_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_reporter` (`reporter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Role Feature Permissions table
CREATE TABLE IF NOT EXISTS `qr_role_features` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `role` VARCHAR(50) NOT NULL,
    `feature` VARCHAR(80) NOT NULL,
    `enabled` TINYINT(1) DEFAULT 0,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_role_feature` (`role`, `feature`),
    INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Feature Overrides table
CREATE TABLE IF NOT EXISTS `qr_user_features` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `feature` VARCHAR(80) NOT NULL,
    `enabled` TINYINT(1) DEFAULT 0,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_user_feature` (`user_id`, `feature`),
    INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default role feature permissions
-- User role: static QR only, basic downloads
INSERT IGNORE INTO `qr_role_features` (`role`, `feature`, `enabled`) VALUES
('user','static_qr',1),
('user','dynamic_qr',0),
('user','analytics',0),
('user','bulk_generation',0),
('user','ai_design',0),
('user','password_protection',0),
('user','expiry_date',0),
('user','campaigns',0),
('user','api_access',0),
('user','whitelabel',0),
('user','team_roles',0),
('user','download_png',1),
('user','download_svg',0),
('user','download_pdf',0),
('user','custom_logo',0),
('user','custom_colors',1),
('user','frame_styles',0),
('user','priority_support',0),
('user','export_data',0);

-- Manager role: static + dynamic, analytics, bulk
INSERT IGNORE INTO `qr_role_features` (`role`, `feature`, `enabled`) VALUES
('manager','static_qr',1),
('manager','dynamic_qr',1),
('manager','analytics',1),
('manager','bulk_generation',1),
('manager','ai_design',0),
('manager','password_protection',1),
('manager','expiry_date',1),
('manager','campaigns',1),
('manager','api_access',0),
('manager','whitelabel',0),
('manager','team_roles',0),
('manager','download_png',1),
('manager','download_svg',1),
('manager','download_pdf',0),
('manager','custom_logo',1),
('manager','custom_colors',1),
('manager','frame_styles',1),
('manager','priority_support',0),
('manager','export_data',1);

-- Owner role: all features
INSERT IGNORE INTO `qr_role_features` (`role`, `feature`, `enabled`) VALUES
('owner','static_qr',1),
('owner','dynamic_qr',1),
('owner','analytics',1),
('owner','bulk_generation',1),
('owner','ai_design',1),
('owner','password_protection',1),
('owner','expiry_date',1),
('owner','campaigns',1),
('owner','api_access',1),
('owner','whitelabel',1),
('owner','team_roles',1),
('owner','download_png',1),
('owner','download_svg',1),
('owner','download_pdf',1),
('owner','custom_logo',1),
('owner','custom_colors',1),
('owner','frame_styles',1),
('owner','priority_support',1),
('owner','export_data',1);

-- Admin role: full platform moderation
INSERT IGNORE INTO `qr_role_features` (`role`, `feature`, `enabled`) VALUES
('admin','static_qr',1),
('admin','dynamic_qr',1),
('admin','analytics',1),
('admin','bulk_generation',1),
('admin','ai_design',1),
('admin','password_protection',1),
('admin','expiry_date',1),
('admin','campaigns',1),
('admin','api_access',1),
('admin','whitelabel',1),
('admin','team_roles',1),
('admin','download_png',1),
('admin','download_svg',1),
('admin','download_pdf',1),
('admin','custom_logo',1),
('admin','custom_colors',1),
('admin','frame_styles',1),
('admin','priority_support',1),
('admin','export_data',1);
