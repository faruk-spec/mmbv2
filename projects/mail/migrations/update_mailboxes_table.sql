-- Migration: Update mail_mailboxes table
-- Description: Add signature column for email signatures
-- Date: 2026-01-07

ALTER TABLE `mail_mailboxes` 
ADD COLUMN IF NOT EXISTS `signature` TEXT NULL COMMENT 'Email signature' AFTER `display_name`;
