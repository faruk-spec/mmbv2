-- Migration: Update mail_domains table
-- Description: Add missing columns for domain management
-- Date: 2026-01-07

ALTER TABLE `mail_domains` 
ADD COLUMN IF NOT EXISTS `description` TEXT NULL AFTER `domain_name`,
ADD COLUMN IF NOT EXISTS `catch_all_email` VARCHAR(255) NULL AFTER `catch_all_enabled`,
ADD COLUMN IF NOT EXISTS `dkim_private_key` TEXT NULL AFTER `catch_all_mailbox_id`,
ADD COLUMN IF NOT EXISTS `dkim_public_key` TEXT NULL AFTER `dkim_private_key`;

-- Update last_verified_at column in DNS records if missing
ALTER TABLE `mail_dns_records`
ADD COLUMN IF NOT EXISTS `last_verified_at` TIMESTAMP NULL AFTER `verified_at`;
