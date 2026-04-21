-- ============================================================
-- Upload Security Settings Migration
--
-- Inserts the default upload security settings into the
-- `settings` table so they can be managed from the admin
-- Security Center → Scan Settings page (/admin/security/settings).
--
-- Safe to run multiple times (INSERT IGNORE).
-- ============================================================

-- Scan mode: 'enforce' blocks infected/unscanned files; 'passive' logs only
INSERT IGNORE INTO `settings` (`key`, `value`, `type`, `created_at`)
VALUES ('upload_scan_mode', 'enforce', 'string', NOW());

-- ClamAV enabled: '1' = on, '0' = off
INSERT IGNORE INTO `settings` (`key`, `value`, `type`, `created_at`)
VALUES ('upload_clamav_enabled', '1', 'string', NOW());

-- ClamAV scan command
-- clamscan (standalone) works without the ClamAV daemon.
-- Switch to 'clamdscan --no-summary --stdout' if you run clamd for faster scans.
INSERT IGNORE INTO `settings` (`key`, `value`, `type`, `created_at`)
VALUES ('upload_clamav_command', 'clamscan --no-summary --stdout', 'string', NOW());

-- Security alert email addresses (comma-separated)
-- Leave empty to send alerts to all users with admin role.
INSERT IGNORE INTO `settings` (`key`, `value`, `type`, `created_at`)
VALUES ('security_alert_emails', '', 'string', NOW());

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
