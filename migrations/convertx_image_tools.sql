-- ConvertX Image Tools Settings
-- Migration: convertx_image_tools

CREATE TABLE IF NOT EXISTS `convertx_image_tools_settings` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `setting_key`   VARCHAR(100)    NOT NULL,
    `setting_value` TEXT            NOT NULL DEFAULT '',
    `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `convertx_image_tools_settings` (`setting_key`, `setting_value`) VALUES
    ('upscale_api_key',      ''),
    ('upscale_api_provider', ''),
    ('removebg_api_key',     ''),
    ('removebg_api_provider','iloveapi')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);
