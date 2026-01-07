-- Migration: Update mail_aliases table structure
-- Description: Add missing columns to match controller expectations
-- Date: 2026-01-07

-- Add subscriber_id column if it doesn't exist
ALTER TABLE `mail_aliases` 
ADD COLUMN IF NOT EXISTS `subscriber_id` INT UNSIGNED NOT NULL AFTER `id`,
ADD COLUMN IF NOT EXISTS `alias_email` VARCHAR(255) NOT NULL AFTER `source_email`,
ADD COLUMN IF NOT EXISTS `destination_type` ENUM('mailbox', 'external') DEFAULT 'mailbox' AFTER `alias_email`,
ADD COLUMN IF NOT EXISTS `destination_mailbox_id` INT UNSIGNED NULL AFTER `destination_type`,
ADD COLUMN IF NOT EXISTS `destination_email` VARCHAR(255) NULL AFTER `destination_mailbox_id`;

-- Add index for subscriber_id if not exists
ALTER TABLE `mail_aliases` 
ADD INDEX IF NOT EXISTS `idx_subscriber_id` (`subscriber_id`),
ADD INDEX IF NOT EXISTS `idx_alias_email` (`alias_email`);

-- Add foreign key for subscriber_id if not exists
ALTER TABLE `mail_aliases`
ADD CONSTRAINT `fk_aliases_subscriber` FOREIGN KEY IF NOT EXISTS (`subscriber_id`) REFERENCES `mail_subscribers`(`id`) ON DELETE CASCADE;

-- Update existing records to populate subscriber_id from domain
UPDATE `mail_aliases` a
JOIN `mail_domains` d ON a.domain_id = d.id
SET a.subscriber_id = d.subscriber_id
WHERE a.subscriber_id IS NULL OR a.subscriber_id = 0;

-- Populate alias_email from source_email if empty
UPDATE `mail_aliases`
SET alias_email = source_email
WHERE alias_email IS NULL OR alias_email = '';

-- Populate destination_email from destination_emails if empty
UPDATE `mail_aliases`
SET destination_email = SUBSTRING_INDEX(destination_emails, ',', 1)
WHERE destination_email IS NULL OR destination_email = '';
