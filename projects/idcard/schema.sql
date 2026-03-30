-- CardX – ID Card Generator Database Schema
-- Tables are auto-created by the model on first use.

CREATE TABLE IF NOT EXISTS `idcard_cards` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`       INT UNSIGNED NOT NULL,
    `template_key`  VARCHAR(50)  NOT NULL DEFAULT 'corporate',
    `card_number`   VARCHAR(50)  NOT NULL,
    `card_data`     JSON         NOT NULL COMMENT 'All field values (name, designation, etc.)',
    `design`        JSON         NULL     COMMENT 'Custom colours / fonts / layout overrides',
    `photo_path`    VARCHAR(500) NULL     COMMENT 'Uploaded profile photo path',
    `logo_path`     VARCHAR(500) NULL     COMMENT 'Uploaded organisation logo path',
    `ai_prompt`     TEXT         NULL     COMMENT 'AI prompt used to generate suggestions',
    `ai_suggestions` JSON        NULL     COMMENT 'Cached AI design suggestions',
    `status`        ENUM('draft','generated') DEFAULT 'generated',
    `created_at`    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP    NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_id`      (`user_id`),
    INDEX `idx_template_key` (`template_key`),
    INDEX `idx_created_at`   (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `idcard_settings` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `setting_key`   VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT         NULL,
    `created_at`    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP    NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_idcard_settings_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
