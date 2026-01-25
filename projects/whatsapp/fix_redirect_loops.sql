-- Fix Redirect Loops - Diagnostic and Repair Script
-- Run this on your MAIN database (testuser, mmbtech, etc.)
-- NOT on mmb_whatsapp database

-- ============================================
-- Step 1: Check if home_projects table exists
-- ============================================

-- Create table if it doesn't exist
CREATE TABLE IF NOT EXISTS `home_projects` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `project_key` VARCHAR(100) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `icon` VARCHAR(100) DEFAULT NULL,
    `color` VARCHAR(20) DEFAULT NULL,
    `is_enabled` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT(11) NOT NULL DEFAULT 0,
    `database_name` VARCHAR(100) DEFAULT NULL,
    `url` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `project_key` (`project_key`),
    KEY `idx_is_enabled` (`is_enabled`),
    KEY `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Step 2: Register WhatsApp Project
-- ============================================

INSERT INTO `home_projects` (
    `project_key`, 
    `name`, 
    `description`, 
    `icon`, 
    `color`, 
    `is_enabled`, 
    `sort_order`, 
    `database_name`, 
    `url`
) VALUES (
    'whatsapp',
    'WhatsApp API',
    'WhatsApp API automation and messaging platform with subscription management',
    'message-circle',
    '#25D366',
    1,
    70,
    'mmb_whatsapp',
    '/projects/whatsapp'
) ON DUPLICATE KEY UPDATE
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `icon` = VALUES(`icon`),
    `color` = VALUES(`color`),
    `is_enabled` = 1,  -- Force enable
    `database_name` = VALUES(`database_name`),
    `url` = VALUES(`url`);

-- ============================================
-- Step 3: Show Current Status
-- ============================================

SELECT '=======================================' as '';
SELECT 'CURRENT PROJECTS STATUS' as '';
SELECT '=======================================' as '';

SELECT 
    project_key as 'Project Key',
    name as 'Project Name',
    CASE 
        WHEN is_enabled = 1 THEN 'ENABLED ✓'
        ELSE 'DISABLED ✗'
    END as 'Status',
    url as 'URL',
    database_name as 'Database'
FROM home_projects
ORDER BY sort_order;

SELECT '=======================================' as '';
SELECT 'WHATSAPP PROJECT DETAILS' as '';
SELECT '=======================================' as '';

SELECT * FROM home_projects WHERE project_key = 'whatsapp';

-- ============================================
-- Verification Queries (Run these manually)
-- ============================================

SELECT '=======================================' as '';
SELECT 'VERIFICATION QUERIES' as '';
SELECT '=======================================' as '';
SELECT 'Run these queries to verify the fix:' as '';
SELECT '' as '';
SELECT '1. Check table exists:' as '';
SELECT '   SHOW TABLES LIKE "home_projects";' as '';
SELECT '' as '';
SELECT '2. Check WhatsApp is registered:' as '';
SELECT '   SELECT * FROM home_projects WHERE project_key = "whatsapp";' as '';
SELECT '' as '';
SELECT '3. Check all projects:' as '';
SELECT '   SELECT project_key, name, is_enabled, url FROM home_projects;' as '';
SELECT '' as '';
SELECT '4. Enable a project if disabled:' as '';
SELECT '   UPDATE home_projects SET is_enabled = 1 WHERE project_key = "project_name";' as '';
SELECT '=======================================' as '';
