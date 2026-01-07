-- Migration: Create mail_billing_history table
-- Description: Stores billing and transaction history for mail subscribers
-- Date: 2026-01-07

CREATE TABLE IF NOT EXISTS `mail_billing_history` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `subscriber_id` INT UNSIGNED NOT NULL,
    `subscription_id` INT UNSIGNED NULL,
    `transaction_type` ENUM('payment', 'upgrade', 'downgrade', 'refund', 'credit', 'adjustment') DEFAULT 'payment',
    `amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `currency` VARCHAR(3) DEFAULT 'USD',
    `payment_method` VARCHAR(50) NULL COMMENT 'stripe, razorpay, cashfree, etc',
    `payment_status` ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'completed',
    `transaction_id` VARCHAR(255) NULL COMMENT 'External payment gateway transaction ID',
    `invoice_number` VARCHAR(100) NULL,
    `description` TEXT NULL,
    `metadata` JSON NULL COMMENT 'Additional transaction details',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`subscriber_id`) REFERENCES `mail_subscribers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`subscription_id`) REFERENCES `mail_subscriptions`(`id`) ON DELETE SET NULL,
    
    INDEX `idx_subscriber_id` (`subscriber_id`),
    INDEX `idx_subscription_id` (`subscription_id`),
    INDEX `idx_transaction_type` (`transaction_type`),
    INDEX `idx_payment_status` (`payment_status`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
