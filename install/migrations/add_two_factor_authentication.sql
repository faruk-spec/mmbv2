-- Two-Factor Authentication Migration
-- This migration adds 2FA (TOTP) support with backup codes

-- Add 2FA columns to users table if they don't exist
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `two_factor_secret` VARCHAR(255) NULL COMMENT 'Base32-encoded TOTP secret';
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `two_factor_enabled` TINYINT(1) DEFAULT 0 COMMENT 'Whether 2FA is enabled';
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `two_factor_backup_codes` TEXT NULL COMMENT 'JSON array of hashed backup codes';
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `two_factor_enabled_at` TIMESTAMP NULL COMMENT 'When 2FA was enabled';

-- Add index for 2FA enabled users
ALTER TABLE `users` ADD INDEX IF NOT EXISTS `idx_two_factor_enabled` (`two_factor_enabled`);
