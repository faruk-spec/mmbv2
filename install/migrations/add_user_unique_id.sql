-- Migration: Add user_unique_id to users table and create ws_auth_tokens table
-- Run this migration once to add the user_unique_id field to existing users.

-- Add user_unique_id column after id
ALTER TABLE users ADD COLUMN `user_unique_id` VARCHAR(36) NULL AFTER `id`;

-- Populate existing rows with UUID values
-- MySQL UUID() generates UUID v1 (timestamp-based). New users created via
-- PHP will receive UUID v4. Both are valid unique identifiers for this column.
UPDATE users SET user_unique_id = UUID() WHERE user_unique_id IS NULL;

-- Add unique index
ALTER TABLE users ADD UNIQUE INDEX `idx_user_unique_id` (`user_unique_id`);

-- WebSocket / SSE auth tokens table
CREATE TABLE IF NOT EXISTS `ws_auth_tokens` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`    INT NOT NULL,
    `token`      VARCHAR(64) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE INDEX `idx_token`   (`token`),
    INDEX        `idx_user_id` (`user_id`),
    INDEX        `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
