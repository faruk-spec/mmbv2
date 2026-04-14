-- Mail Improvements Migration
-- 1. Add visited_at column to password_resets for single-visit link expiry
-- 2. Grant example: INSERT INTO admin_user_permissions (user_id, permission_key) VALUES (?, 'mail')
--    to give a regular user access to /mail

-- ── password_resets: visited_at ────────────────────────────────────────────
ALTER TABLE `password_resets`
  ADD COLUMN IF NOT EXISTS `visited_at` DATETIME NULL DEFAULT NULL
    COMMENT 'Timestamp when the reset link was first opened; NULL = not yet visited'
  AFTER `created_at`;

-- ── mail_send_log: index on recipient for autocomplete queries ───────────────
CREATE INDEX IF NOT EXISTS `idx_recipient_user` ON `mail_send_log` (`user_id`, `recipient`(100));

-- ── Grant mail access to a user (example — replace ? with actual user ID) ──
-- INSERT IGNORE INTO `admin_user_permissions` (`user_id`, `permission_key`) VALUES (?, 'mail');
