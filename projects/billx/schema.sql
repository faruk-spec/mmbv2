-- BillX Project Database Schema
-- Database: mmb_billx

CREATE TABLE IF NOT EXISTS `billx_bills` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `bill_type` VARCHAR(50) NOT NULL,
    `bill_number` VARCHAR(50) NOT NULL,
    `bill_date` DATE NOT NULL,
    `from_name` VARCHAR(255) NOT NULL,
    `from_address` TEXT NULL,
    `from_phone` VARCHAR(50) NULL,
    `from_email` VARCHAR(255) NULL,
    `to_name` VARCHAR(255) NOT NULL,
    `to_address` TEXT NULL,
    `to_phone` VARCHAR(50) NULL,
    `to_email` VARCHAR(255) NULL,
    `items` JSON NOT NULL,
    `subtotal` DECIMAL(12,2) DEFAULT 0.00,
    `tax_percent` DECIMAL(5,2) DEFAULT 0.00,
    `tax_amount` DECIMAL(12,2) DEFAULT 0.00,
    `discount_amount` DECIMAL(12,2) DEFAULT 0.00,
    `total_amount` DECIMAL(12,2) DEFAULT 0.00,
    `notes` TEXT NULL,
    `currency` VARCHAR(10) DEFAULT 'INR',
    `template_data` JSON NULL,
    `status` ENUM('draft', 'generated') DEFAULT 'generated',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_bill_type` (`bill_type`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
