-- DevZone Migration: Create prefixed tables in main database
-- Generated: 2026-04-06
-- Description: DevZone developer collaboration & project management tables (devzone_ prefix)

-- Boards (Kanban project boards)
CREATE TABLE IF NOT EXISTS `devzone_boards` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL COMMENT 'Owner',
    `name` VARCHAR(120) NOT NULL,
    `description` TEXT NULL,
    `color` VARCHAR(7) DEFAULT '#00f0ff',
    `is_private` TINYINT(1) DEFAULT 0,
    `status` ENUM('active','archived') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Board columns (e.g. To Do, In Progress, Done)
CREATE TABLE IF NOT EXISTS `devzone_columns` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `board_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(80) NOT NULL,
    `position` SMALLINT UNSIGNED DEFAULT 0,
    `color` VARCHAR(7) DEFAULT NULL,
    `wip_limit` SMALLINT UNSIGNED DEFAULT NULL COMMENT 'Work-in-progress limit',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`board_id`) REFERENCES `devzone_boards`(`id`) ON DELETE CASCADE,
    INDEX `idx_board_id` (`board_id`),
    INDEX `idx_position` (`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tasks / cards
CREATE TABLE IF NOT EXISTS `devzone_tasks` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `board_id` INT UNSIGNED NOT NULL,
    `column_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL COMMENT 'Creator',
    `assignee_id` INT UNSIGNED NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `priority` ENUM('low','medium','high','critical') DEFAULT 'medium',
    `labels` JSON NULL,
    `due_date` DATE NULL,
    `position` INT UNSIGNED DEFAULT 0,
    `is_archived` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`board_id`)  REFERENCES `devzone_boards`(`id`)  ON DELETE CASCADE,
    FOREIGN KEY (`column_id`) REFERENCES `devzone_columns`(`id`) ON DELETE CASCADE,
    INDEX `idx_board_id` (`board_id`),
    INDEX `idx_column_id` (`column_id`),
    INDEX `idx_assignee` (`assignee_id`),
    INDEX `idx_due_date` (`due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Task comments
CREATE TABLE IF NOT EXISTS `devzone_comments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `task_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `content` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`task_id`) REFERENCES `devzone_tasks`(`id`) ON DELETE CASCADE,
    INDEX `idx_task_id` (`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Board members / collaborators
CREATE TABLE IF NOT EXISTS `devzone_members` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `board_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `role` ENUM('viewer','member','admin') DEFAULT 'member',
    `joined_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_board_user` (`board_id`,`user_id`),
    FOREIGN KEY (`board_id`) REFERENCES `devzone_boards`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Module settings
CREATE TABLE IF NOT EXISTS `devzone_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL UNIQUE,
    `default_board_color` VARCHAR(7) DEFAULT '#00f0ff',
    `email_notifications` TINYINT(1) DEFAULT 1,
    `task_reminders` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
