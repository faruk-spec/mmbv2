-- Migration: api_keys + convertx_jobs JSON column fixes
-- Run this against the main application database.
--
-- This migration is idempotent (safe to run multiple times).

SET NAMES utf8mb4;

-- ------------------------------------------------------------------ --
--  API Keys table                                                       --
--  Required for ConvertX API-key generation feature.                   --
-- ------------------------------------------------------------------ --
CREATE TABLE IF NOT EXISTS `api_keys` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`    INT UNSIGNED  NOT NULL,
    `api_key`    VARCHAR(100)  NOT NULL UNIQUE,
    `is_active`  TINYINT(1)    NOT NULL DEFAULT 1,
    `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_api_key` (`api_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------------ --
--  Fix: convertx_jobs — change JSON columns to LONGTEXT                --
--  MariaDB auto-generates a CHECK(json_valid()) constraint on JSON     --
--  columns.  Storing NULL via PDO may trigger the constraint in some   --
--  MariaDB versions.  LONGTEXT avoids the implicit CHECK constraint    --
--  while still storing all JSON data correctly.                        --
-- ------------------------------------------------------------------ --

-- ai_result: most likely to receive NULL or complex/binary-derived values
ALTER TABLE `convertx_jobs`
    MODIFY COLUMN `ai_result` LONGTEXT DEFAULT NULL;

-- options and ai_tasks: also JSON in the original schema
ALTER TABLE `convertx_jobs`
    MODIFY COLUMN `options`   LONGTEXT DEFAULT NULL,
    MODIFY COLUMN `ai_tasks`  LONGTEXT DEFAULT NULL;


-- ------------------------------------------------------------------ --
--  ConvertX User Settings (in case missing from earlier installs)      --
-- ------------------------------------------------------------------ --
CREATE TABLE IF NOT EXISTS `convertx_user_settings` (
    `id`                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`             INT UNSIGNED  NOT NULL UNIQUE,
    `default_quality`     TINYINT UNSIGNED NOT NULL DEFAULT 85,
    `default_dpi`         SMALLINT UNSIGNED NOT NULL DEFAULT 150,
    `notify_on_complete`  TINYINT(1) NOT NULL DEFAULT 0,
    `updated_at`          DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,

    INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
