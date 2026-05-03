-- Migration: admin_user_permissions
-- Stores granular admin panel permissions for individual users.
-- Each row grants a specific permission_key to a user.
-- Permissions are checked in controllers and used to show/hide sidebar items.

CREATE TABLE IF NOT EXISTS `admin_user_permissions` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`         INT UNSIGNED NOT NULL COMMENT 'Target user receiving the permission',
    `permission_key`  VARCHAR(100) NOT NULL  COMMENT 'Permission identifier, e.g. "qr", "users", "settings"',
    `granted_by`      INT UNSIGNED NULL      COMMENT 'Admin user who granted this permission',
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_user_perm` (`user_id`, `permission_key`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_permission_key` (`permission_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
