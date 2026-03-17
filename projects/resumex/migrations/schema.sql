-- ResumeX Database Schema
-- Run this on your main application database (same DB as the main app)
-- or create a dedicated database: mmb_resumex

CREATE TABLE IF NOT EXISTS `resumex_resumes` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`        INT UNSIGNED NOT NULL,
    `title`          VARCHAR(255) NOT NULL DEFAULT 'My Resume',
    `template`       VARCHAR(100) NOT NULL DEFAULT 'ocean-blue',
    `resume_data`    LONGTEXT     NULL,
    `theme_settings` LONGTEXT     NULL,
    `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_updated_at` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
