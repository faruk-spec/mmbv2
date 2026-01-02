-- ImgTxt Project Database Schema
-- Database: mmb_imgtxt

-- OCR Jobs table
CREATE TABLE IF NOT EXISTS `ocr_jobs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NULL,
    `original_filename` VARCHAR(255) NOT NULL,
    `stored_filename` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `file_size` BIGINT UNSIGNED NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `status` ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    `extracted_text` LONGTEXT NULL,
    `confidence` DECIMAL(5,2) NULL,
    `language` VARCHAR(10) DEFAULT 'eng',
    `processing_time` INT NULL COMMENT 'in seconds',
    `error_message` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Batch processing table
CREATE TABLE IF NOT EXISTS `batch_jobs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NULL,
    `name` VARCHAR(100) NOT NULL,
    `total_files` INT UNSIGNED DEFAULT 0,
    `completed_files` INT UNSIGNED DEFAULT 0,
    `failed_files` INT UNSIGNED DEFAULT 0,
    `status` ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Batch job files relationship
CREATE TABLE IF NOT EXISTS `batch_job_files` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `batch_id` INT UNSIGNED NOT NULL,
    `ocr_job_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`batch_id`) REFERENCES `batch_jobs`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`ocr_job_id`) REFERENCES `ocr_jobs`(`id`) ON DELETE CASCADE,
    INDEX `idx_batch_id` (`batch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User settings
CREATE TABLE IF NOT EXISTS `user_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL UNIQUE,
    `default_language` VARCHAR(10) DEFAULT 'eng',
    `auto_download` TINYINT(1) DEFAULT 0,
    `output_format` ENUM('txt', 'pdf', 'json') DEFAULT 'txt',
    `keep_history` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API usage tracking (for admin)
CREATE TABLE IF NOT EXISTS `usage_stats` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NULL,
    `date` DATE NOT NULL,
    `total_jobs` INT UNSIGNED DEFAULT 0,
    `successful_jobs` INT UNSIGNED DEFAULT 0,
    `failed_jobs` INT UNSIGNED DEFAULT 0,
    `total_pages` INT UNSIGNED DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `idx_user_date` (`user_id`, `date`),
    INDEX `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Saved templates for common OCR tasks
CREATE TABLE IF NOT EXISTS `templates` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NULL,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `language` VARCHAR(10) DEFAULT 'eng',
    `preprocessing_options` JSON NULL,
    `is_public` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_is_public` (`is_public`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings table for project configuration
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    `value` TEXT NULL,
    `type` ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    `description` TEXT NULL,
    `is_system` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT INTO `settings` (`key`, `value`, `type`, `description`, `is_system`) VALUES
('max_file_size', '10485760', 'integer', 'Maximum file size in bytes (10MB)', 1),
('max_batch_size', '10', 'integer', 'Maximum files in batch', 1),
('ocr_engine', 'tesseract', 'string', 'OCR engine to use', 1),
('default_language', 'eng', 'string', 'Default OCR language', 1),
('enable_batch_processing', '1', 'boolean', 'Enable batch processing', 1),
('enable_multi_language', '1', 'boolean', 'Enable multi-language support', 1);

-- Activity logs for admin actions
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NULL,
    `action` VARCHAR(100) NOT NULL,
    `resource_type` VARCHAR(50) NOT NULL,
    `resource_id` INT UNSIGNED NULL,
    `description` TEXT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(500) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_resource_type` (`resource_type`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
