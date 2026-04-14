-- Mail Improvements Migration
-- 1. Add visited_at column to password_resets for single-visit link expiry
-- 2. Grant example: INSERT INTO admin_user_permissions (user_id, permission_key) VALUES (?, 'mail')
--    to give a regular user access to /mail

-- ── password_resets: visited_at ────────────────────────────────────────────
ALTER TABLE `password_resets`
  ADD COLUMN IF NOT EXISTS `visited_at` DATETIME NULL DEFAULT NULL
    COMMENT 'Timestamp when the reset link was first opened; NULL = not yet visited'
  AFTER `created_at`;

-- ── mail_send_log: body_html, cc_email, bcc_email ──────────────────────────
ALTER TABLE `mail_send_log`
  ADD COLUMN IF NOT EXISTS `body_html` LONGTEXT NULL DEFAULT NULL AFTER `subject`,
  ADD COLUMN IF NOT EXISTS `cc_email`  VARCHAR(1000) NULL DEFAULT NULL AFTER `body_html`,
  ADD COLUMN IF NOT EXISTS `bcc_email` VARCHAR(1000) NULL DEFAULT NULL AFTER `cc_email`;

-- ── mail_send_log: index on recipient for autocomplete queries ───────────────
CREATE INDEX IF NOT EXISTS `idx_recipient_user` ON `mail_send_log` (`user_id`, `recipient`(100));

-- ── mail_user_providers: link users to specific mail providers ─────────────
CREATE TABLE IF NOT EXISTS `mail_user_providers` (
  `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`            INT UNSIGNED NOT NULL,
  `provider_config_id` INT UNSIGNED NOT NULL,
  `granted_by`         INT UNSIGNED NULL DEFAULT NULL
                         COMMENT 'Admin user ID who made this assignment',
  `created_at`         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_provider` (`user_id`, `provider_config_id`),
  KEY `user_id` (`user_id`),
  KEY `provider_config_id` (`provider_config_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Maps platform users to specific SMTP/IMAP providers for /mail access';

-- ── Grant mail access to a user (example — replace ? with actual user ID) ──
-- INSERT IGNORE INTO `admin_user_permissions` (`user_id`, `permission_key`) VALUES (?, 'mail');
