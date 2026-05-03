-- ================================================================
-- Platform-wide Universal Plans Migration
-- Covers multiple applications in a single subscription
-- ================================================================

-- Universal platform plans (one plan, many apps)
CREATE TABLE IF NOT EXISTS `platform_plans` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) UNIQUE NOT NULL,
    `description` TEXT NULL,
    `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `billing_cycle` ENUM('monthly','yearly','lifetime') DEFAULT 'monthly',
    `color` VARCHAR(7) DEFAULT '#9945ff' COMMENT 'Accent colour for UI',
    -- JSON array of project keys included: ["qr","whatsapp","proshare",...]
    `included_apps` JSON NULL,
    -- Per-app feature/limit overrides: {"qr":{...},"whatsapp":{...}}
    `app_features` JSON NULL,
    `status` ENUM('active','inactive') DEFAULT 'active',
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_status` (`status`),
    INDEX `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Universal plans that bundle multiple applications';

-- User subscriptions to platform plans
CREATE TABLE IF NOT EXISTS `platform_user_subscriptions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `plan_id` INT UNSIGNED NOT NULL,
    `status` ENUM('active','cancelled','expired','trial') DEFAULT 'active',
    `started_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at` TIMESTAMP NULL,
    `cancelled_at` TIMESTAMP NULL,
    `assigned_by` INT UNSIGNED NULL COMMENT 'Admin user ID who assigned the plan',
    `notes` VARCHAR(500) NULL,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`plan_id`) REFERENCES `platform_plans`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_plan_id` (`plan_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='User subscriptions to platform-wide plans';

-- Seed sample plans (idempotent via INSERT IGNORE)
INSERT IGNORE INTO `platform_plans`
    (`name`,`slug`,`description`,`price`,`billing_cycle`,`color`,`included_apps`,`app_features`,`sort_order`)
VALUES
(
    'Starter Bundle',
    'starter-bundle',
    'Essential tools for individuals — includes QR Generator & ProShare.',
    9.99,
    'monthly',
    '#00f0ff',
    '["qr","proshare"]',
    '{"qr":{"max_static_qr":50,"max_dynamic_qr":5,"analytics":true,"password_protection":true},"proshare":{"max_storage_mb":500,"max_file_size_mb":50}}',
    1
),
(
    'Pro Bundle',
    'pro-bundle',
    'Professional suite — QR Generator, WhatsApp API & ProShare with expanded limits.',
    29.99,
    'monthly',
    '#9945ff',
    '["qr","whatsapp","proshare"]',
    '{"qr":{"max_static_qr":-1,"max_dynamic_qr":-1,"analytics":true,"bulk_generation":true,"password_protection":true,"expiry_date":true,"campaigns":true},"whatsapp":{"max_sessions":3,"max_messages_per_day":500},"proshare":{"max_storage_mb":5000,"max_file_size_mb":200}}',
    2
),
(
    'Business Bundle',
    'business-bundle',
    'Full platform access — all applications with unlimited usage and priority support.',
    79.99,
    'monthly',
    '#ff2ec4',
    '["qr","whatsapp","proshare","codexpro","imgtxt","resumex"]',
    '{"qr":{"max_static_qr":-1,"max_dynamic_qr":-1,"analytics":true,"bulk_generation":true,"ai_design":true,"password_protection":true,"expiry_date":true,"campaigns":true,"api_access":true,"whitelabel":true,"priority_support":true},"whatsapp":{"max_sessions":-1,"max_messages_per_day":-1},"proshare":{"max_storage_mb":-1,"max_file_size_mb":-1}}',
    3
);
