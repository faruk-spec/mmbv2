-- FormX v2: Time-limited forms + version history + device tracking
-- Run after formx.sql

-- ── Expiry on forms ──────────────────────────────────────────────────────────
ALTER TABLE `formx_forms`
    ADD COLUMN IF NOT EXISTS `expires_at` DATETIME NULL
        COMMENT 'NULL = never expires; set to expire form at this UTC datetime'
    AFTER `submissions_count`;

-- ── Version history ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `formx_form_versions` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `form_id`     INT UNSIGNED NOT NULL,
    `user_id`     INT UNSIGNED NULL,
    `title`       VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `fields`      JSON NOT NULL,
    `settings`    JSON NOT NULL,
    `status`      ENUM('active','inactive','draft') NOT NULL DEFAULT 'draft',
    `note`        VARCHAR(255) NULL COMMENT 'Optional change note',
    `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_form_id` (`form_id`),
    CONSTRAINT `fk_fxv_form`
        FOREIGN KEY (`form_id`) REFERENCES `formx_forms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Device / browser tracking on submissions ─────────────────────────────────
ALTER TABLE `formx_submissions`
    ADD COLUMN IF NOT EXISTS `device`  VARCHAR(50)  NULL AFTER `user_agent`,
    ADD COLUMN IF NOT EXISTS `browser` VARCHAR(100) NULL AFTER `device`;
