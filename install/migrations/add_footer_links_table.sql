-- Migration: Create footer_links table for custom footer link management
-- This table stores custom footer links for the homepage footer and the
-- default dashboard/project footer, managed via Admin > Settings.

CREATE TABLE IF NOT EXISTS `footer_links` (
    `id`          INT          UNSIGNED NOT NULL AUTO_INCREMENT,
    `area`        ENUM('home','default') NOT NULL DEFAULT 'default'
                  COMMENT 'home = homepage footer; default = dashboard/project footer',
    `label`       VARCHAR(120) NOT NULL,
    `url`         VARCHAR(255) NOT NULL,
    `sort_order`  SMALLINT     NOT NULL DEFAULT 0,
    `is_enabled`  TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_footer_links_area_sort` (`area`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
