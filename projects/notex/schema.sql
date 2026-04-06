-- NoteX Project Database Schema
-- Run this against the main application database (same DB as the main app).

CREATE TABLE IF NOT EXISTS `notex_notes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL DEFAULT 'Untitled Note',
    `content` LONGTEXT NULL,
    `folder_id` INT UNSIGNED NULL,
    `color` VARCHAR(20) DEFAULT '#ffd700',
    `is_pinned` TINYINT(1) DEFAULT 0,
    `is_archived` TINYINT(1) DEFAULT 0,
    `is_encrypted` TINYINT(1) DEFAULT 0,
    `share_token` VARCHAR(64) NULL UNIQUE,
    `share_access` ENUM('view', 'edit') DEFAULT 'view',
    `share_expires_at` TIMESTAMP NULL,
    `status` ENUM('active', 'trashed') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_folder_id` (`folder_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_is_pinned` (`is_pinned`),
    FULLTEXT KEY `ft_search` (`title`, `content`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `notex_folders` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `color` VARCHAR(20) DEFAULT '#ffd700',
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `notex_tags` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(50) NOT NULL,
    `color` VARCHAR(20) DEFAULT '#ffd700',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_user_tag` (`user_id`, `name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `notex_tag_map` (
    `note_id` INT UNSIGNED NOT NULL,
    `tag_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`note_id`, `tag_id`),
    FOREIGN KEY (`note_id`) REFERENCES `notex_notes`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`tag_id`) REFERENCES `notex_tags`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `notex_versions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `note_id` INT UNSIGNED NOT NULL,
    `content` LONGTEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`note_id`) REFERENCES `notex_notes`(`id`) ON DELETE CASCADE,
    INDEX `idx_note_id` (`note_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `notex_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL UNIQUE,
    `default_color` VARCHAR(20) DEFAULT '#ffd700',
    `auto_save` TINYINT(1) DEFAULT 1,
    `theme` ENUM('dark', 'light') DEFAULT 'dark',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
