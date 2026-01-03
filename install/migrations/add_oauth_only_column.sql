-- Add oauth_only column to users table
-- This migration adds a column to track users who only have OAuth login methods

-- Add oauth_only column to track OAuth-only users (who haven't set their own password)
ALTER TABLE `users` 
ADD COLUMN `oauth_only` TINYINT(1) DEFAULT 0 COMMENT 'User only has OAuth login (no manual password set)';

-- Add index for performance
ALTER TABLE `users` 
ADD INDEX `idx_oauth_only` (`oauth_only`);

-- Note: Existing users who signed up with Google will need admin intervention
-- or they should be prompted to set a password in their security settings
