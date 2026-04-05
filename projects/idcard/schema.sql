-- =============================================================================
-- CardX – ID Card Generator  |  Standalone Database Schema
-- =============================================================================
-- Run this file ONCE on your MySQL/MariaDB database to create all CardX tables.
-- All three tables below are required:
--   • idcard_cards     — stores every generated ID card
--   • idcard_settings  — stores admin settings INCLUDING the OpenAI API key
--                        (required before /admin/projects/idcard/ai-settings can save)
--   • idcard_bulk_jobs — tracks CSV bulk-generation jobs
--
-- NOTE: The model's ensureTables() will also auto-create these on first use,
--       so running this file manually is optional for new installs — but it is
--       the recommended way to prepare the database before first use.
-- =============================================================================

CREATE TABLE IF NOT EXISTS `idcard_cards` (
    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`        INT UNSIGNED NOT NULL,
    `template_key`   VARCHAR(50)  NOT NULL DEFAULT 'corporate',
    `card_number`    VARCHAR(50)  NOT NULL,
    `card_data`      JSON         NOT NULL COMMENT 'All field values (name, designation, etc.)',
    `design`         JSON         NULL     COMMENT 'Custom colours / fonts / layout overrides',
    `photo_path`     VARCHAR(500) NULL     COMMENT 'Uploaded profile photo path',
    `logo_path`      VARCHAR(500) NULL     COMMENT 'Uploaded organisation logo path',
    `ai_prompt`      TEXT         NULL     COMMENT 'AI prompt used to generate suggestions',
    `ai_suggestions` JSON         NULL     COMMENT 'Cached AI design suggestions',
    `bulk_job_id`    INT UNSIGNED NULL     COMMENT 'Set when card was created via bulk job',
    `status`         ENUM('draft','generated') DEFAULT 'generated',
    `created_at`     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP    NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_ic_user`    (`user_id`),
    INDEX `idx_ic_tpl`     (`template_key`),
    INDEX `idx_ic_created` (`created_at`),
    INDEX `idx_ic_bulk`    (`bulk_job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- idcard_settings stores ALL CardX settings, including:
--   idcard_ai_enabled, idcard_openai_api_key, idcard_openai_model,
--   idcard_ai_daily_limit, idcard_pro_templates, idcard_pro_styles, admin_config
-- This table MUST exist before saving anything in /admin/projects/idcard/ai-settings.
CREATE TABLE IF NOT EXISTS `idcard_settings` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `setting_key`   VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT         NULL,
    `created_at`    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP    NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_ics_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `idcard_bulk_jobs` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`       INT UNSIGNED NOT NULL,
    `template_key`  VARCHAR(50)  NOT NULL DEFAULT 'corporate',
    `total_rows`    INT UNSIGNED NOT NULL DEFAULT 0,
    `completed`     INT UNSIGNED NOT NULL DEFAULT 0,
    `failed`        INT UNSIGNED NOT NULL DEFAULT 0,
    `status`        ENUM('pending','processing','done','error') DEFAULT 'pending',
    `created_at`    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP    NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_ibj_user`    (`user_id`),
    INDEX `idx_ibj_status`  (`status`),
    INDEX `idx_ibj_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
