-- ============================================================
-- ResumeX: Custom Templates table
-- Created: 2026-03-26
-- ============================================================
--
-- Stores metadata for admin-uploaded resume template files.
-- The actual PHP template files live in:
--   storage/uploads/resumex/templates/<file_name>
--
-- Run this migration once (the application also creates the
-- table automatically via TemplateModel::ensureTable()).
-- ============================================================

CREATE TABLE IF NOT EXISTS `resumex_templates` (
    `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `key`           VARCHAR(100)     NOT NULL COMMENT 'Unique template slug (a-z, 0-9, hyphens)',
    `name`          VARCHAR(255)     NOT NULL COMMENT 'Display name shown in the template picker',
    `category`      VARCHAR(100)     NOT NULL DEFAULT 'custom' COMMENT 'professional | academic | dark | light | creative | custom',
    `template_type` VARCHAR(10)      NOT NULL DEFAULT 'preset' COMMENT 'preset = PHP returning array; full = complete renderer PHP',
    `file_name`     VARCHAR(255)     NOT NULL DEFAULT '' COMMENT 'Stored PHP file name inside the templates storage dir',
    `uploaded_by`   INT UNSIGNED     NOT NULL COMMENT 'User ID of the admin who uploaded the template',
    `is_active`     TINYINT(1)       NOT NULL DEFAULT 1 COMMENT '1 = visible to users, 0 = hidden',
    `is_override`   TINYINT(1)       NOT NULL DEFAULT 0 COMMENT '1 = overrides a built-in template with the same key',
    `preview_image` VARCHAR(500)     DEFAULT NULL COMMENT 'URL/path to a preview screenshot',
    `display_bg`    VARCHAR(20)      DEFAULT NULL COMMENT 'Thumbnail background colour for the template picker card',
    `display_pri`   VARCHAR(20)      DEFAULT NULL COMMENT 'Thumbnail accent colour for the template picker card',
    `created_at`    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migration: add columns that may be absent on older installs (safe to run on existing tables)
ALTER TABLE `resumex_templates` ADD COLUMN IF NOT EXISTS `template_type` VARCHAR(10)  NOT NULL DEFAULT 'preset';
ALTER TABLE `resumex_templates` ADD COLUMN IF NOT EXISTS `is_override`   TINYINT(1)   NOT NULL DEFAULT 0;
ALTER TABLE `resumex_templates` ADD COLUMN IF NOT EXISTS `preview_image` VARCHAR(500) DEFAULT NULL;
ALTER TABLE `resumex_templates` ADD COLUMN IF NOT EXISTS `display_bg`    VARCHAR(20)  DEFAULT NULL;
ALTER TABLE `resumex_templates` ADD COLUMN IF NOT EXISTS `display_pri`   VARCHAR(20)  DEFAULT NULL;
