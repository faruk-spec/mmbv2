-- Home Page Customization Tables
-- This migration adds tables for customizable home page content

-- Home page content table for hero section and general settings
CREATE TABLE IF NOT EXISTS `home_content` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `section` VARCHAR(50) NOT NULL UNIQUE,
    `title` VARCHAR(255) NULL,
    `subtitle` TEXT NULL,
    `description` TEXT NULL,
    `image_url` VARCHAR(255) NULL,
    `button_text` VARCHAR(100) NULL,
    `button_url` VARCHAR(255) NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_section` (`section`),
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Projects customization table
CREATE TABLE IF NOT EXISTS `home_projects` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `project_key` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `image_url` VARCHAR(255) NULL,
    `icon` VARCHAR(50) NULL,
    `color` VARCHAR(20) NULL,
    `is_enabled` TINYINT(1) DEFAULT 1,
    `sort_order` INT DEFAULT 0,
    `database_name` VARCHAR(50) NULL,
    `url` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_key` (`project_key`),
    INDEX `idx_enabled` (`is_enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default home page content
INSERT INTO `home_content` (`section`, `title`, `subtitle`, `description`, `is_active`, `sort_order`) VALUES
('hero', 'Welcome to Your Platform', 'A powerful multi-project platform', 'A powerful multi-project platform with centralized authentication, unified admin panel, and secure architecture.', 1, 1),
('projects_section', 'Explore Our Super Fast Products', NULL, NULL, 1, 2);

-- Insert default projects from existing config
INSERT INTO `home_projects` (`project_key`, `name`, `description`, `icon`, `color`, `is_enabled`, `sort_order`, `database_name`, `url`) VALUES
('codexpro', 'CodeXPro', 'Advanced code editor and IDE platform', 'code', '#00f0ff', 1, 1, 'mmb_codexpro', '/projects/codexpro'),
('devzone', 'DevZone', 'Developer collaboration and project management', 'users', '#ff2ec4', 1, 2, 'mmb_devzone', '/projects/devzone'),
('imgtxt', 'ImgTxt', 'Image to text converter and OCR tool', 'image', '#00ff88', 1, 3, 'mmb_imgtxt', '/projects/imgtxt'),
('proshare', 'ProShare', 'Secure file sharing platform', 'share-2', '#ffaa00', 1, 4, 'mmb_proshare', '/projects/proshare'),
('qr', 'QR Generator', 'QR code generation and management', 'grid', '#9945ff', 1, 5, 'mmb_qr', '/projects/qr'),
('resumex', 'ResumeX', 'Professional resume builder', 'file-text', '#ff6b6b', 1, 6, 'mmb_resumex', '/projects/resumex'),
('sheetdocs', 'SheetDocs', 'Collaborative spreadsheet and document editor', 'file-spreadsheet', '#00d4aa', 1, 7, 'sheetdocs', '/projects/sheetdocs');
