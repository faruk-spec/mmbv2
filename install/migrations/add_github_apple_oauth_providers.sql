-- Add GitHub and Apple OAuth providers, login methods, and user ID columns

-- Add provider-specific user ID columns
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `github_id` VARCHAR(255) NULL;
ALTER TABLE `users` ADD UNIQUE INDEX IF NOT EXISTS `idx_github_id` (`github_id`);
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `apple_id` VARCHAR(255) NULL;
ALTER TABLE `users` ADD UNIQUE INDEX IF NOT EXISTS `idx_apple_id` (`apple_id`);

-- Insert GitHub and Apple provider defaults
INSERT INTO `oauth_providers` (`name`, `display_name`, `scopes`, `is_enabled`, `config`) VALUES
('github', 'GitHub', 'read:user user:email', 0, JSON_OBJECT(
    'auth_url', 'https://github.com/login/oauth/authorize',
    'token_url', 'https://github.com/login/oauth/access_token',
    'userinfo_url', 'https://api.github.com/user',
    'userinfo_email_url', 'https://api.github.com/user/emails'
)),
('apple', 'Apple', 'name email', 0, JSON_OBJECT(
    'auth_url', 'https://appleid.apple.com/auth/authorize',
    'token_url', 'https://appleid.apple.com/auth/token',
    'userinfo_url', ''
))
ON DUPLICATE KEY UPDATE display_name = VALUES(display_name), scopes = VALUES(scopes), config = VALUES(config);

-- Expand login method enum to include new OAuth methods
ALTER TABLE `login_history`
MODIFY COLUMN `login_method` ENUM('email_password', 'google_oauth', 'github_oauth', 'apple_oauth', 'remember_token', '2fa') DEFAULT 'email_password';
