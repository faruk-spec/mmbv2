-- ============================================================
-- Activity Logs Enhanced Migration
-- Adds centralized audit trail columns to activity_logs table
-- Run this once against the main database.
-- ============================================================

-- Add module (which app/service originated the event)
ALTER TABLE `activity_logs`
    ADD COLUMN IF NOT EXISTS `module` VARCHAR(100) NULL AFTER `action`,
    ADD COLUMN IF NOT EXISTS `tenant_id` INT UNSIGNED NULL AFTER `module`,
    ADD COLUMN IF NOT EXISTS `resource_type` VARCHAR(100) NULL AFTER `tenant_id`,
    ADD COLUMN IF NOT EXISTS `resource_id` VARCHAR(100) NULL AFTER `resource_type`,
    ADD COLUMN IF NOT EXISTS `user_role` VARCHAR(50) NULL AFTER `resource_id`,
    ADD COLUMN IF NOT EXISTS `old_values` JSON NULL AFTER `user_role`,
    ADD COLUMN IF NOT EXISTS `new_values` JSON NULL AFTER `old_values`,
    ADD COLUMN IF NOT EXISTS `readable_message` VARCHAR(500) NULL AFTER `new_values`,
    ADD COLUMN IF NOT EXISTS `request_id` VARCHAR(64) NULL AFTER `readable_message`,
    ADD COLUMN IF NOT EXISTS `device` VARCHAR(100) NULL AFTER `request_id`,
    ADD COLUMN IF NOT EXISTS `browser` VARCHAR(100) NULL AFTER `device`,
    ADD COLUMN IF NOT EXISTS `status` ENUM('success','failure','pending') NOT NULL DEFAULT 'success' AFTER `browser`;

-- Add performance indexes for common filter queries
ALTER TABLE `activity_logs`
    ADD INDEX IF NOT EXISTS `idx_module` (`module`),
    ADD INDEX IF NOT EXISTS `idx_tenant_id` (`tenant_id`),
    ADD INDEX IF NOT EXISTS `idx_resource` (`resource_type`, `resource_id`(50)),
    ADD INDEX IF NOT EXISTS `idx_user_role` (`user_role`),
    ADD INDEX IF NOT EXISTS `idx_status` (`status`),
    ADD INDEX IF NOT EXISTS `idx_request_id` (`request_id`);
