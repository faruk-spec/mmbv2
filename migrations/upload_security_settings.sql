-- ============================================================
-- Upload Security Settings Migration
--
-- Inserts the default upload security settings into the
-- `settings` table so they can be managed from the admin
-- Security Center → Scan Settings page (/admin/security/settings).
--
-- Safe to run multiple times (INSERT IGNORE).
-- ============================================================

-- Scan mode: start with 'passive' (log only — never blocks uploads)
-- Switch to 'enforce' AFTER confirming ClamAV is installed and working.
INSERT IGNORE INTO `settings` (`key`, `value`, `type`, `created_at`)
VALUES ('upload_scan_mode', 'passive', 'string', NOW());

-- ClamAV enabled: '0' = off by default (safe to deploy without ClamAV)
-- Set to '1' in Admin → Security → Scan Settings once ClamAV is confirmed installed.
INSERT IGNORE INTO `settings` (`key`, `value`, `type`, `created_at`)
VALUES ('upload_clamav_enabled', '0', 'string', NOW());

-- ClamAV scan command
-- clamscan (standalone) reloads its virus DB on every call — very slow.
-- Switch to 'clamdscan --no-summary --stdout' once you have the clamd daemon running.
INSERT IGNORE INTO `settings` (`key`, `value`, `type`, `created_at`)
VALUES ('upload_clamav_command', 'clamscan --no-summary --stdout', 'string', NOW());

-- Security alert email addresses (comma-separated)
-- Leave empty to send alerts to all users with admin role.
INSERT IGNORE INTO `settings` (`key`, `value`, `type`, `created_at`)
VALUES ('security_alert_emails', '', 'string', NOW());

-- ============================================================
-- HOTFIX: If the previous migration already set 'enforce'/'1',
-- reset to safe defaults to restore upload functionality.
-- Remove these UPDATE lines once the site is confirmed stable.
-- ============================================================
UPDATE `settings` SET `value` = 'passive' WHERE `key` = 'upload_scan_mode' AND `value` = 'enforce';
UPDATE `settings` SET `value` = '0'       WHERE `key` = 'upload_clamav_enabled' AND `value` = '1';

-- ============================================================
-- Ensure the activity_logs table has the columns needed by
-- UploadSecurityMonitor. Each ALTER is run separately so a
-- "Duplicate column" error on one does not block the others.
-- Run each statement individually or use a migration runner
-- that ignores duplicate-column errors (error 1060).
-- ============================================================

-- Add module column (safe to skip if column already exists)
SET @col_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'activity_logs'
      AND COLUMN_NAME  = 'module'
);
SET @sql = IF(@col_exists = 0,
    "ALTER TABLE `activity_logs` ADD COLUMN `module` VARCHAR(100) NULL AFTER `action`",
    "SELECT 'module column already exists' AS info"
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add readable_message column
SET @col_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'activity_logs'
      AND COLUMN_NAME  = 'readable_message'
);
SET @sql = IF(@col_exists = 0,
    "ALTER TABLE `activity_logs` ADD COLUMN `readable_message` VARCHAR(500) NULL",
    "SELECT 'readable_message column already exists' AS info"
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add status column
SET @col_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'activity_logs'
      AND COLUMN_NAME  = 'status'
);
SET @sql = IF(@col_exists = 0,
    "ALTER TABLE `activity_logs` ADD COLUMN `status` ENUM('success','failure','pending') NOT NULL DEFAULT 'success'",
    "SELECT 'status column already exists' AS info"
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add data column (JSON payload from SecureUpload)
SET @col_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'activity_logs'
      AND COLUMN_NAME  = 'data'
);
SET @sql = IF(@col_exists = 0,
    "ALTER TABLE `activity_logs` ADD COLUMN `data` JSON NULL",
    "SELECT 'data column already exists' AS info"
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add composite index for admin/security queries (skip if exists)
SET @idx_exists = (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'activity_logs'
      AND INDEX_NAME   = 'idx_upload_security'
);
SET @sql = IF(@idx_exists = 0,
    "ALTER TABLE `activity_logs` ADD INDEX `idx_upload_security` (`module`, `status`, `created_at`)",
    "SELECT 'idx_upload_security index already exists' AS info"
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
