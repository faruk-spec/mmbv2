-- Shared subscription billing migration
-- Adds reusable payment fields, invoice branding settings, and plan policy columns.

CREATE TABLE IF NOT EXISTS `subscription_payments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `app_key` VARCHAR(32) NOT NULL,
    `plan_id` INT UNSIGNED NOT NULL,
    `subscription_id` INT UNSIGNED NULL,
    `plan_name` VARCHAR(120) NOT NULL,
    `billing_cycle` VARCHAR(20) NULL,
    `gateway` VARCHAR(20) NOT NULL DEFAULT 'manual',
    `status` ENUM('pending','verification_pending','paid','failed','cancelled') NOT NULL DEFAULT 'pending',
    `amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `currency` VARCHAR(10) NOT NULL DEFAULT 'USD',
    `invoice_no` VARCHAR(40) NOT NULL,
    `reference` VARCHAR(80) NOT NULL,
    `provider_order_id` VARCHAR(120) NULL,
    `provider_payment_session_id` VARCHAR(255) NULL,
    `payment_url` TEXT NULL,
    `payment_payload` TEXT NULL,
    `metadata_json` LONGTEXT NULL,
    `admin_notes` TEXT NULL,
    `refund_status` ENUM('none','requested','approved','rejected','refunded') NOT NULL DEFAULT 'none',
    `refund_requested_at` TIMESTAMP NULL,
    `refund_decided_at` TIMESTAMP NULL,
    `cancel_requested_at` TIMESTAMP NULL,
    `paid_at` TIMESTAMP NULL,
    `expires_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uniq_invoice_no` (`invoice_no`),
    UNIQUE KEY `uniq_reference` (`reference`),
    KEY `idx_user_app` (`user_id`, `app_key`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `platform_plans` ADD COLUMN IF NOT EXISTS `currency` VARCHAR(3) NOT NULL DEFAULT 'USD' AFTER `price`;
ALTER TABLE `platform_plans` ADD COLUMN IF NOT EXISTS `cancel_days` INT NOT NULL DEFAULT 0 AFTER `billing_cycle`;
ALTER TABLE `platform_plans` ADD COLUMN IF NOT EXISTS `refund_days` INT NOT NULL DEFAULT 0 AFTER `cancel_days`;

ALTER TABLE `resumex_subscription_plans` ADD COLUMN IF NOT EXISTS `cancel_days` INT NOT NULL DEFAULT 0 AFTER `billing_cycle`;
ALTER TABLE `resumex_subscription_plans` ADD COLUMN IF NOT EXISTS `refund_days` INT NOT NULL DEFAULT 0 AFTER `cancel_days`;

ALTER TABLE `qr_subscription_plans` ADD COLUMN IF NOT EXISTS `currency` VARCHAR(3) NOT NULL DEFAULT 'USD' AFTER `price`;
ALTER TABLE `qr_subscription_plans` ADD COLUMN IF NOT EXISTS `cancel_days` INT NOT NULL DEFAULT 0 AFTER `billing_cycle`;
ALTER TABLE `qr_subscription_plans` ADD COLUMN IF NOT EXISTS `refund_days` INT NOT NULL DEFAULT 0 AFTER `cancel_days`;

ALTER TABLE `convertx_subscription_plans` ADD COLUMN IF NOT EXISTS `currency` VARCHAR(3) NOT NULL DEFAULT 'USD' AFTER `price`;
ALTER TABLE `convertx_subscription_plans` ADD COLUMN IF NOT EXISTS `cancel_days` INT NOT NULL DEFAULT 0 AFTER `billing_cycle`;
ALTER TABLE `convertx_subscription_plans` ADD COLUMN IF NOT EXISTS `refund_days` INT NOT NULL DEFAULT 0 AFTER `cancel_days`;
ALTER TABLE `convertx_subscription_plans` ADD COLUMN IF NOT EXISTS `contact_sale_url` VARCHAR(500) NULL DEFAULT NULL;

ALTER TABLE `whatsapp_subscription_plans` ADD COLUMN IF NOT EXISTS `slug` VARCHAR(80) NULL AFTER `name`;
ALTER TABLE `whatsapp_subscription_plans` ADD COLUMN IF NOT EXISTS `cancel_days` INT NOT NULL DEFAULT 0 AFTER `duration_days`;
ALTER TABLE `whatsapp_subscription_plans` ADD COLUMN IF NOT EXISTS `refund_days` INT NOT NULL DEFAULT 0 AFTER `cancel_days`;
ALTER TABLE `whatsapp_subscriptions` ADD COLUMN IF NOT EXISTS `plan_id` INT NULL AFTER `user_id`;

INSERT INTO `settings` (`key`, `value`, `type`)
VALUES
    ('invoice_company_name', 'MMB Platform', 'string'),
    ('invoice_company_email', '', 'string'),
    ('invoice_company_phone', '', 'string'),
    ('invoice_company_address', '', 'string'),
    ('invoice_logo', '', 'string'),
    ('invoice_prefix', 'INV', 'string'),
    ('invoice_accent_color', '#0077cc', 'string'),
    ('invoice_footer_note', 'Thank you for using our platform.', 'string'),
    ('invoice_terms', 'This is a computer-generated invoice. No signature required.', 'string')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);
