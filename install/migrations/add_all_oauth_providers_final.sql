-- =============================================================================
-- Final OAuth Providers Migration
-- Adds GitHub, Apple, and Microsoft OAuth support.
-- Safe to run on any installation (all statements are idempotent).
-- Run this if you have NOT already run add_github_apple_oauth_providers.sql.
-- =============================================================================

-- ---------------------------------------------------------------------------
-- 1. User ID columns for each provider
-- ---------------------------------------------------------------------------
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `google_id`    VARCHAR(255) NULL;
ALTER TABLE `users` ADD UNIQUE INDEX IF NOT EXISTS `idx_google_id`    (`google_id`);

ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `github_id`    VARCHAR(255) NULL;
ALTER TABLE `users` ADD UNIQUE INDEX IF NOT EXISTS `idx_github_id`    (`github_id`);

ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `apple_id`     VARCHAR(255) NULL;
ALTER TABLE `users` ADD UNIQUE INDEX IF NOT EXISTS `idx_apple_id`     (`apple_id`);

ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `microsoft_id` VARCHAR(255) NULL;
ALTER TABLE `users` ADD UNIQUE INDEX IF NOT EXISTS `idx_microsoft_id` (`microsoft_id`);

-- ---------------------------------------------------------------------------
-- 2. Seed / update OAuth providers (all disabled by default)
-- ---------------------------------------------------------------------------
INSERT INTO `oauth_providers` (`name`, `display_name`, `scopes`, `is_enabled`, `config`)
VALUES
    ('google', 'Google', 'openid email profile', 0, JSON_OBJECT(
        'auth_url',      'https://accounts.google.com/o/oauth2/v2/auth',
        'token_url',     'https://oauth2.googleapis.com/token',
        'userinfo_url',  'https://www.googleapis.com/oauth2/v2/userinfo'
    )),
    ('github', 'GitHub', 'read:user user:email', 0, JSON_OBJECT(
        'auth_url',            'https://github.com/login/oauth/authorize',
        'token_url',           'https://github.com/login/oauth/access_token',
        'userinfo_url',        'https://api.github.com/user',
        'userinfo_email_url',  'https://api.github.com/user/emails'
    )),
    ('apple', 'Apple', 'name email', 0, JSON_OBJECT(
        'auth_url',     'https://appleid.apple.com/auth/authorize',
        'token_url',    'https://appleid.apple.com/auth/token',
        'userinfo_url', ''
    )),
    ('microsoft', 'Microsoft', 'openid email profile User.Read', 0, JSON_OBJECT(
        'auth_url',     'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
        'token_url',    'https://login.microsoftonline.com/common/oauth2/v2.0/token',
        'userinfo_url', 'https://graph.microsoft.com/v1.0/me'
    ))
ON DUPLICATE KEY UPDATE
    display_name = VALUES(display_name),
    scopes       = VALUES(scopes),
    config       = VALUES(config);
-- NOTE: is_enabled is intentionally NOT updated so existing admin settings are preserved.

-- ---------------------------------------------------------------------------
-- 3. Expand login_history.login_method enum with all new OAuth methods
-- ---------------------------------------------------------------------------
ALTER TABLE `login_history`
    MODIFY COLUMN `login_method`
        ENUM('email_password','google_oauth','github_oauth','apple_oauth','microsoft_oauth','remember_token','2fa')
        DEFAULT 'email_password';
