-- Hero banner slides: multiple images with optional click-through links
CREATE TABLE IF NOT EXISTS `home_hero_slides` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `image_url` VARCHAR(500) NOT NULL,
    `link_url`  VARCHAR(500) NULL,
    `sort_order` INT DEFAULT 0,
    `is_active`  TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_active_order` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
