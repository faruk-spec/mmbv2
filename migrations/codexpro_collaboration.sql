-- CodeXPro Enterprise Collaboration & Version History
-- Run once; IF NOT EXISTS / ADD COLUMN IF NOT EXISTS guards make it idempotent

-- ─────────────────────────────────────────────────────────────────
-- 1. Project version snapshots (version history)
-- ─────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `codexpro_project_versions` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `project_id`   INT UNSIGNED NOT NULL,
    `user_id`      INT UNSIGNED NOT NULL,
    `version_num`  SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    `label`        VARCHAR(120) NULL,          -- optional human label, e.g. "Before refactor"
    `html_content` LONGTEXT NULL,
    `css_content`  LONGTEXT NULL,
    `js_content`   LONGTEXT NULL,
    `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_project_versions` (`project_id`, `version_num`),
    INDEX `idx_project_created`  (`project_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────
-- 2. Collaboration sessions / presence table
-- ─────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `codexpro_collab_sessions` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT UNSIGNED NOT NULL,
    `user_id`    INT UNSIGNED NOT NULL,
    `color`      VARCHAR(7)  NOT NULL DEFAULT '#00f0ff', -- avatar color
    `cursor_line` SMALLINT UNSIGNED DEFAULT 0,
    `cursor_ch`   SMALLINT UNSIGNED DEFAULT 0,
    `active_tab`  VARCHAR(4) DEFAULT 'html',
    `last_seen`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uniq_project_user` (`project_id`, `user_id`),
    INDEX `idx_project_active` (`project_id`, `last_seen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────
-- 3. Change-buffer for near-real-time SSE delivery
-- ─────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `codexpro_collab_changes` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT UNSIGNED NOT NULL,
    `user_id`    INT UNSIGNED NOT NULL,
    `type`       ENUM('html','css','js','cursor','meta') NOT NULL DEFAULT 'html',
    `payload`    MEDIUMTEXT NOT NULL,           -- JSON payload
    `seq`        INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP(3) DEFAULT CURRENT_TIMESTAMP(3),
    INDEX `idx_project_seq`    (`project_id`, `seq`),
    INDEX `idx_project_recent` (`project_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────
-- 4. Add version tracking to projects table
-- ─────────────────────────────────────────────────────────────────
ALTER TABLE `codexpro_projects`
    ADD COLUMN IF NOT EXISTS `version`      INT UNSIGNED DEFAULT 1 AFTER `js_content`,
    ADD COLUMN IF NOT EXISTS `collab_token` VARCHAR(64)  DEFAULT NULL AFTER `version`;

-- ─────────────────────────────────────────────────────────────────
-- 5. Auto-cleanup event (purge changes older than 15 min)
-- ─────────────────────────────────────────────────────────────────
-- NOTE: Enable the event scheduler on your DB: SET GLOBAL event_scheduler = ON;
-- The application also purges on each SSE request.
