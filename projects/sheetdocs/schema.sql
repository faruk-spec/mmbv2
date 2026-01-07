-- SheetDocs Project Database Schema
-- Database: sheetdocs

-- Documents table (for text documents)
CREATE TABLE IF NOT EXISTS `sheet_documents` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL DEFAULT 'Untitled Document',
    `content` LONGTEXT NULL,
    `type` ENUM('document', 'sheet') DEFAULT 'document',
    `visibility` ENUM('private', 'shared', 'public') DEFAULT 'private',
    `is_template` TINYINT(1) DEFAULT 0,
    `template_category` VARCHAR(50) NULL,
    `views` INT UNSIGNED DEFAULT 0,
    `last_edited_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_type` (`type`),
    INDEX `idx_visibility` (`visibility`),
    INDEX `idx_is_template` (`is_template`),
    INDEX `idx_updated_at` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sheets table (for spreadsheets)
CREATE TABLE IF NOT EXISTS `sheet_sheets` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `document_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL DEFAULT 'Sheet1',
    `order_index` INT UNSIGNED DEFAULT 0,
    `row_count` INT UNSIGNED DEFAULT 100,
    `col_count` INT UNSIGNED DEFAULT 26,
    `is_hidden` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`document_id`) REFERENCES `sheet_documents`(`id`) ON DELETE CASCADE,
    INDEX `idx_document_id` (`document_id`),
    INDEX `idx_order_index` (`order_index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sheet cells table (for individual cell data)
CREATE TABLE IF NOT EXISTS `sheet_cells` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `sheet_id` INT UNSIGNED NOT NULL,
    `row_index` INT UNSIGNED NOT NULL,
    `col_index` INT UNSIGNED NOT NULL,
    `value` TEXT NULL,
    `formula` TEXT NULL,
    `style` JSON NULL COMMENT 'Cell styling: font, color, alignment, etc.',
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`sheet_id`) REFERENCES `sheet_sheets`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_cell` (`sheet_id`, `row_index`, `col_index`),
    INDEX `idx_sheet_id` (`sheet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Document shares table (for collaboration)
CREATE TABLE IF NOT EXISTS `sheet_document_shares` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `document_id` INT UNSIGNED NOT NULL,
    `shared_with_user_id` INT UNSIGNED NULL COMMENT 'NULL for public links',
    `shared_by_user_id` INT UNSIGNED NOT NULL,
    `permission` ENUM('view', 'comment', 'edit') DEFAULT 'view',
    `share_token` VARCHAR(64) NULL COMMENT 'For anonymous/public sharing',
    `expires_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`document_id`) REFERENCES `sheet_documents`(`id`) ON DELETE CASCADE,
    INDEX `idx_document_id` (`document_id`),
    INDEX `idx_shared_with_user_id` (`shared_with_user_id`),
    INDEX `idx_share_token` (`share_token`),
    UNIQUE KEY `unique_user_share` (`document_id`, `shared_with_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Document versions table (version history - paid feature)
CREATE TABLE IF NOT EXISTS `sheet_document_versions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `document_id` INT UNSIGNED NOT NULL,
    `version_number` INT UNSIGNED NOT NULL,
    `content` LONGTEXT NOT NULL,
    `changed_by_user_id` INT UNSIGNED NOT NULL,
    `change_summary` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`document_id`) REFERENCES `sheet_documents`(`id`) ON DELETE CASCADE,
    INDEX `idx_document_id` (`document_id`),
    INDEX `idx_version_number` (`version_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comments table
CREATE TABLE IF NOT EXISTS `sheet_comments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `document_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `content` TEXT NOT NULL,
    `position` JSON NULL COMMENT 'Position in document for inline comments',
    `resolved` TINYINT(1) DEFAULT 0,
    `parent_id` INT UNSIGNED NULL COMMENT 'For threaded comments',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`document_id`) REFERENCES `sheet_documents`(`id`) ON DELETE CASCADE,
    INDEX `idx_document_id` (`document_id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User subscriptions table
CREATE TABLE IF NOT EXISTS `sheet_user_subscriptions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL UNIQUE,
    `plan` ENUM('free', 'paid') DEFAULT 'free',
    `status` ENUM('active', 'cancelled', 'expired', 'trial') DEFAULT 'active',
    `billing_cycle` ENUM('monthly', 'annual') NULL,
    `trial_ends_at` TIMESTAMP NULL,
    `current_period_start` TIMESTAMP NULL,
    `current_period_end` TIMESTAMP NULL,
    `cancelled_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_plan` (`plan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Usage statistics table
CREATE TABLE IF NOT EXISTS `sheet_usage_stats` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `document_count` INT UNSIGNED DEFAULT 0,
    `sheet_count` INT UNSIGNED DEFAULT 0,
    `storage_used` BIGINT UNSIGNED DEFAULT 0 COMMENT 'in bytes',
    `api_calls_today` INT UNSIGNED DEFAULT 0,
    `last_api_call` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_user_stats` (`user_id`),
    INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity logs table
CREATE TABLE IF NOT EXISTS `sheet_activity_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `document_id` INT UNSIGNED NULL,
    `action` VARCHAR(50) NOT NULL COMMENT 'create, edit, delete, share, export, etc.',
    `details` JSON NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`document_id`) REFERENCES `sheet_documents`(`id`) ON DELETE SET NULL,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_document_id` (`document_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Document templates table
CREATE TABLE IF NOT EXISTS `sheet_templates` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `content` LONGTEXT NOT NULL,
    `type` ENUM('document', 'sheet') DEFAULT 'document',
    `category` VARCHAR(50) NOT NULL,
    `tier` ENUM('free', 'paid') DEFAULT 'free',
    `preview_image` VARCHAR(255) NULL,
    `usage_count` INT UNSIGNED DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_type` (`type`),
    INDEX `idx_category` (`category`),
    INDEX `idx_tier` (`tier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default free templates
INSERT INTO `sheet_templates` (`title`, `description`, `content`, `type`, `category`, `tier`, `usage_count`) VALUES
('Blank Document', 'Start with a blank document', '', 'document', 'basic', 'free', 0),
('Blank Spreadsheet', 'Start with a blank spreadsheet', '', 'sheet', 'basic', 'free', 0),
('Meeting Notes', 'Template for meeting notes', '<h1>Meeting Notes</h1><p><strong>Date:</strong></p><p><strong>Attendees:</strong></p><h2>Agenda</h2><ul><li></li></ul><h2>Discussion</h2><p></p><h2>Action Items</h2><ul><li></li></ul>', 'document', 'productivity', 'free', 0),
('Budget Tracker', 'Simple budget tracking spreadsheet', '{"sheets":[{"name":"Budget","cells":[{"row":0,"col":0,"value":"Category"},{"row":0,"col":1,"value":"Budgeted"},{"row":0,"col":2,"value":"Actual"},{"row":0,"col":3,"value":"Difference"}]}]}', 'sheet', 'finance', 'free', 0);

-- Settings table
CREATE TABLE IF NOT EXISTS `sheet_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) UNIQUE NOT NULL,
    `value` TEXT NULL,
    `type` ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT INTO `sheet_settings` (`key`, `value`, `type`) VALUES
('max_free_documents', '5', 'integer'),
('max_free_sheets', '5', 'integer'),
('max_free_storage', '10485760', 'integer'),
('enable_public_sharing', '1', 'boolean'),
('enable_export', '1', 'boolean'),
('default_sheet_rows', '100', 'integer'),
('default_sheet_cols', '26', 'integer');
