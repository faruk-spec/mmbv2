-- =============================================================================
-- ProShare: prefix-based migration to main database
-- Generated: 2026-04-06  (built from old_databases_proshare.sql dump)
-- Source DB : proshare  (MariaDB 10.11.10)
-- Target    : main application database
-- Prefix    : proshare_
--
-- USAGE
--   Run this file against the MAIN database (e.g. mmb / mymultibranch).
--   All tables are created with IF NOT EXISTS – safe to re-run.
--
-- DATA MIGRATION (two options – pick one)
--   Option A – Old DB still accessible from the same MySQL server:
--     Uncomment the "Live migration" INSERT … SELECT block at the bottom.
--   Option B – Old DB is only available as this dump:
--     The literal INSERT rows below carry the real data from the dump.
-- =============================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ---------------------------------------------------------------------------
-- 1. TABLE DEFINITIONS  (exact structure from old separate DB)
-- ---------------------------------------------------------------------------

--
-- proshare_files
--
CREATE TABLE IF NOT EXISTS `proshare_files` (
  `id`             int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`        int(10) UNSIGNED DEFAULT NULL COMMENT 'NULL for anonymous uploads',
  `short_code`     varchar(10)      NOT NULL,
  `original_name`  varchar(255)     NOT NULL,
  `filename`       varchar(255)     NOT NULL,
  `path`           varchar(500)     NOT NULL,
  `size`           bigint(20) UNSIGNED NOT NULL,
  `mime_type`      varchar(100)     NOT NULL,
  `password`       varchar(255)     DEFAULT NULL,
  `downloads`      int(10) UNSIGNED DEFAULT 0,
  `max_downloads`  int(10) UNSIGNED DEFAULT NULL,
  `expires_at`     timestamp        NULL DEFAULT NULL,
  `is_public`      tinyint(1)       DEFAULT 1,
  `is_encrypted`   tinyint(1)       DEFAULT 0,
  `encryption_key` varchar(255)     DEFAULT NULL,
  `self_destruct`  tinyint(1)       DEFAULT 0,
  `checksum`       varchar(64)      DEFAULT NULL COMMENT 'SHA-256 hash for integrity',
  `is_compressed`  tinyint(1)       DEFAULT 0,
  `status`         enum('active','expired','deleted','reported') DEFAULT 'active',
  `created_at`     timestamp        NULL DEFAULT current_timestamp(),
  `updated_at`     timestamp        NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `short_code` (`short_code`),
  KEY `idx_user_id`    (`user_id`),
  KEY `idx_short_code` (`short_code`),
  KEY `idx_status`     (`status`),
  KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- proshare_file_downloads
--
CREATE TABLE IF NOT EXISTS `proshare_file_downloads` (
  `id`            int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `file_id`       int(10) UNSIGNED NOT NULL,
  `ip_address`    varchar(45)  DEFAULT NULL,
  `user_agent`    varchar(500) DEFAULT NULL,
  `referer`       varchar(500) DEFAULT NULL,
  `downloaded_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_file_id`       (`file_id`),
  KEY `idx_downloaded_at` (`downloaded_at`),
  CONSTRAINT `proshare_file_downloads_ibfk_1`
    FOREIGN KEY (`file_id`) REFERENCES `proshare_files` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- proshare_folders
--
CREATE TABLE IF NOT EXISTS `proshare_folders` (
  `id`         int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    int(10) UNSIGNED NOT NULL,
  `name`       varchar(100) NOT NULL,
  `short_code` varchar(10)  NOT NULL,
  `password`   varchar(255) DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_public`  tinyint(1)   DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `short_code`  (`short_code`),
  KEY `idx_user_id`    (`user_id`),
  KEY `idx_short_code` (`short_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- proshare_file_folders
--
CREATE TABLE IF NOT EXISTS `proshare_file_folders` (
  `file_id`   int(10) UNSIGNED NOT NULL,
  `folder_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`file_id`, `folder_id`),
  KEY `folder_id` (`folder_id`),
  CONSTRAINT `proshare_file_folders_ibfk_1`
    FOREIGN KEY (`file_id`)   REFERENCES `proshare_files`   (`id`) ON DELETE CASCADE,
  CONSTRAINT `proshare_file_folders_ibfk_2`
    FOREIGN KEY (`folder_id`) REFERENCES `proshare_folders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- proshare_text_shares
--
CREATE TABLE IF NOT EXISTS `proshare_text_shares` (
  `id`           int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`      int(10) UNSIGNED DEFAULT NULL,
  `short_code`   varchar(10)  NOT NULL,
  `title`        varchar(255) DEFAULT NULL,
  `content`      longtext     NOT NULL,
  `is_encrypted` tinyint(1)   DEFAULT 0,
  `password`     varchar(255) DEFAULT NULL,
  `views`        int(10) UNSIGNED DEFAULT 0,
  `max_views`    int(10) UNSIGNED DEFAULT NULL,
  `expires_at`   timestamp NULL DEFAULT NULL,
  `self_destruct` tinyint(1)  DEFAULT 0,
  `status`       enum('active','expired','deleted') DEFAULT 'active',
  `created_at`   timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `short_code`  (`short_code`),
  KEY `idx_user_id`    (`user_id`),
  KEY `idx_short_code` (`short_code`),
  KEY `idx_status`     (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- proshare_messages
--
CREATE TABLE IF NOT EXISTS `proshare_messages` (
  `id`           int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `room_id`      varchar(50)  NOT NULL,
  `user_id`      int(10) UNSIGNED DEFAULT NULL,
  `username`     varchar(100) NOT NULL,
  `message`      text         NOT NULL,
  `is_encrypted` tinyint(1)   DEFAULT 0,
  `expires_at`   timestamp NULL DEFAULT NULL,
  `created_at`   timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_room_id`    (`room_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- proshare_chat_rooms
--
CREATE TABLE IF NOT EXISTS `proshare_chat_rooms` (
  `id`          int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `room_code`   varchar(50)  NOT NULL,
  `name`        varchar(100) NOT NULL,
  `creator_id`  int(10) UNSIGNED DEFAULT NULL,
  `password`    varchar(255) DEFAULT NULL,
  `max_users`   int(11)      DEFAULT 10,
  `is_private`  tinyint(1)   DEFAULT 0,
  `expires_at`  timestamp NULL DEFAULT NULL,
  `created_at`  timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_code`     (`room_code`),
  KEY `idx_room_code` (`room_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- proshare_link_access
--
CREATE TABLE IF NOT EXISTS `proshare_link_access` (
  `id`           int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `file_id`      int(10) UNSIGNED DEFAULT NULL,
  `text_id`      int(10) UNSIGNED DEFAULT NULL,
  `email`        varchar(255) NOT NULL,
  `can_download` tinyint(1)   DEFAULT 1,
  `access_count` int(10) UNSIGNED DEFAULT 0,
  `max_access`   int(10) UNSIGNED DEFAULT NULL,
  `created_at`   timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_file_id` (`file_id`),
  KEY `idx_text_id` (`text_id`),
  CONSTRAINT `proshare_link_access_ibfk_1`
    FOREIGN KEY (`file_id`) REFERENCES `proshare_files`       (`id`) ON DELETE CASCADE,
  CONSTRAINT `proshare_link_access_ibfk_2`
    FOREIGN KEY (`text_id`) REFERENCES `proshare_text_shares` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- proshare_notifications
--
CREATE TABLE IF NOT EXISTS `proshare_notifications` (
  `id`         int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    int(10) UNSIGNED NOT NULL,
  `type`       enum('download','expiry_warning','security_alert','upload_complete') NOT NULL,
  `message`    text         NOT NULL,
  `related_id` int(10) UNSIGNED DEFAULT NULL,
  `is_read`    tinyint(1)   DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_is_read`  (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- proshare_audit_logs
--
CREATE TABLE IF NOT EXISTS `proshare_audit_logs` (
  `id`            int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`       int(10) UNSIGNED DEFAULT NULL,
  `action`        varchar(100) NOT NULL,
  `resource_type` varchar(50)  NOT NULL,
  `resource_id`   int(10) UNSIGNED DEFAULT NULL,
  `ip_address`    varchar(45)  DEFAULT NULL,
  `user_agent`    varchar(500) DEFAULT NULL,
  `details`       longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL
                  CHECK (json_valid(`details`)),
  `created_at`    timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id`    (`user_id`),
  KEY `idx_action`     (`action`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- proshare_activity_logs
--
CREATE TABLE IF NOT EXISTS `proshare_activity_logs` (
  `id`            int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`       int(10) UNSIGNED DEFAULT NULL,
  `action`        varchar(100) NOT NULL,
  `resource_type` varchar(50)  NOT NULL,
  `resource_id`   int(10) UNSIGNED DEFAULT NULL,
  `description`   text         DEFAULT NULL,
  `ip_address`    varchar(45)  DEFAULT NULL,
  `user_agent`    varchar(500) DEFAULT NULL,
  `created_at`    timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id`       (`user_id`),
  KEY `idx_action`        (`action`),
  KEY `idx_resource_type` (`resource_type`),
  KEY `idx_created_at`    (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- proshare_user_settings
--
CREATE TABLE IF NOT EXISTS `proshare_user_settings` (
  `id`                  int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`             int(10) UNSIGNED NOT NULL,
  `email_notifications` tinyint(1)   DEFAULT 1,
  `sms_notifications`   tinyint(1)   DEFAULT 0,
  `default_expiry`      int(11)      DEFAULT 24 COMMENT 'hours',
  `auto_delete`         tinyint(1)   DEFAULT 0,
  `enable_encryption`   tinyint(1)   DEFAULT 0,
  `enable_compression`  tinyint(1)   DEFAULT 1,
  `max_file_size`       bigint(20) UNSIGNED DEFAULT 524288000 COMMENT '500MB default',
  `created_at`          timestamp NULL DEFAULT current_timestamp(),
  `updated_at`          timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id`     (`user_id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- proshare_backups
--
CREATE TABLE IF NOT EXISTS `proshare_backups` (
  `id`          int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `file_id`     int(10) UNSIGNED NOT NULL,
  `backup_path` varchar(500)     NOT NULL,
  `backup_size` bigint(20) UNSIGNED NOT NULL,
  `created_at`  timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_file_id` (`file_id`),
  CONSTRAINT `proshare_backups_ibfk_1`
    FOREIGN KEY (`file_id`) REFERENCES `proshare_files` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- proshare_settings
--
CREATE TABLE IF NOT EXISTS `proshare_settings` (
  `id`          int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `key`         varchar(100) NOT NULL,
  `value`       text         DEFAULT NULL,
  `type`        enum('string','integer','boolean','json') DEFAULT 'string',
  `description` text         DEFAULT NULL,
  `is_system`   tinyint(1)   DEFAULT 1,
  `created_at`  timestamp NULL DEFAULT current_timestamp(),
  `updated_at`  timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `key`     (`key`),
  KEY `idx_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 2. SEED / EXISTING DATA  (literal rows from the old DB dump)
-- ---------------------------------------------------------------------------

--
-- proshare_files  (real records from old proshare DB)
--
INSERT IGNORE INTO `proshare_files`
  (`id`,`user_id`,`short_code`,`original_name`,`filename`,`path`,`size`,`mime_type`,
   `password`,`downloads`,`max_downloads`,`expires_at`,`is_public`,`is_encrypted`,
   `encryption_key`,`self_destruct`,`checksum`,`is_compressed`,`status`,`created_at`,`updated_at`)
VALUES
(17,3,'JbmTtm6N','1.png','JbmTtm6N_1765662323.png',
 '/www/wwwroot/test.mymultibranch.com/storage/uploads/proshare/2025/12/JbmTtm6N_1765662323.png',
 598364,'image/jpeg',NULL,1,NULL,'2025-12-20 21:45:23',1,0,NULL,1,
 '835bfa542ecb2d65d3bb55d7fb632a99a1c7fa9935e8fe508a17df75baecc69f',
 0,'deleted','2025-12-13 21:45:23','2025-12-13 21:52:13');

--
-- proshare_file_downloads
--
INSERT IGNORE INTO `proshare_file_downloads`
  (`id`,`file_id`,`ip_address`,`user_agent`,`referer`,`downloaded_at`)
VALUES
(9,17,'2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80',
 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',
 'https://test.mymultibranch.com/projects/proshare/preview/JbmTtm6N',
 '2025-12-13 21:52:13');

--
-- proshare_backups
--
INSERT IGNORE INTO `proshare_backups`
  (`id`,`file_id`,`backup_path`,`backup_size`,`created_at`)
VALUES
(17,17,'/www/wwwroot/test.mymultibranch.com/storage/backups/proshare/2025/12/JbmTtm6N_1765662323.png',
 598364,'2025-12-13 21:45:23');

--
-- proshare_notifications
--
INSERT IGNORE INTO `proshare_notifications`
  (`id`,`user_id`,`type`,`message`,`related_id`,`is_read`,`created_at`)
VALUES
(1, 1,'download','Your file \'airport to hotel cab.jpeg\' was downloaded.',2,0,'2025-12-05 17:47:46'),
(2, 1,'download','Your file \'airport to hotel cab.jpeg\' was downloaded.',2,0,'2025-12-05 17:47:57'),
(3, 1,'download','Your file \'airport to hotel cab.jpeg\' was downloaded.',3,0,'2025-12-05 19:26:15'),
(4, 1,'download','Your file \'Invoice_7341822801.pdf\' was downloaded.',12,0,'2025-12-05 19:30:49'),
(5, 3,'download','Your file \'airport to hotel cab.jpeg\' was downloaded.',13,1,'2025-12-06 11:49:36'),
(6, 3,'download','Your file \'6-oct-food.jpeg\' was downloaded.',15,1,'2025-12-06 12:02:55'),
(7, 3,'download','Your file \'flight-Receipt.pdf\' was downloaded.',14,1,'2025-12-06 12:03:15'),
(8, 3,'download','Your file \'boarding-pass.jpeg\' was downloaded.',16,1,'2025-12-07 21:50:02'),
(9, 3,'download','Your file \'1.png\' was downloaded.',17,1,'2025-12-13 21:52:13'),
(10,3,'download','Your file \'IMG_0459.png\' was downloaded.',18,1,'2026-01-01 05:47:05');

--
-- proshare_text_shares
--
INSERT IGNORE INTO `proshare_text_shares`
  (`id`,`user_id`,`short_code`,`title`,`content`,`is_encrypted`,`password`,
   `views`,`max_views`,`expires_at`,`self_destruct`,`status`,`created_at`)
VALUES
(1,1,'Op6bvAFC','hghghg','gjghjhjhjjhjjnh',0,NULL,0,NULL,'2025-12-05 17:12:24',1,'expired','2025-12-05 16:12:24'),
(2,3,'CMFqrs3W','test share',' cgfdsdfcdfcc',0,
 '$argon2id$v=19$m=65536,t=4,p=1$mWC9qR6fIq9Gy8ED84e2BQ$UJrUMTmwWfhj/oxmPm1nYrY2/mSbjdVWMLGjpKOi6Yo',
 1,NULL,'2025-12-07 12:03:40',0,'expired','2025-12-06 12:03:40');

--
-- proshare_settings
--
INSERT IGNORE INTO `proshare_settings`
  (`id`,`key`,`value`,`type`,`description`,`is_system`,`created_at`,`updated_at`)
VALUES
(1,'max_file_size',       '52428800','integer','Maximum file size in bytes (500MB)',1,'2025-12-03 12:48:11','2026-01-01 05:08:21'),
(2,'default_expiry_hours','24',      'integer','Default link expiry in hours',      1,'2025-12-03 12:48:11',NULL),
(3,'enable_password_protection','1', 'boolean','Enable password protection',        1,'2025-12-03 12:48:11',NULL),
(4,'enable_self_destruct','1',       'boolean','Enable self-destruct feature',      1,'2025-12-03 12:48:11',NULL),
(5,'enable_compression', '1',        'boolean','Enable file compression',           1,'2025-12-03 12:48:11',NULL),
(6,'enable_anonymous_upload','1',    'boolean','Enable anonymous uploads',          1,'2025-12-03 12:48:11',NULL);

--
-- proshare_user_settings
--
INSERT IGNORE INTO `proshare_user_settings`
  (`id`,`user_id`,`email_notifications`,`sms_notifications`,`default_expiry`,
   `auto_delete`,`enable_encryption`,`enable_compression`,`max_file_size`,`created_at`,`updated_at`)
VALUES
(1,1,1,0,168,0,0,1,104857600,'2025-12-04 10:51:42','2025-12-05 17:27:48'),
(2,3,1,0,168,0,0,1,104857600,'2025-12-05 22:13:13','2026-01-03 02:10:47');

--
-- proshare_activity_logs
--
INSERT IGNORE INTO `proshare_activity_logs`
  (`id`,`user_id`,`action`,`resource_type`,`resource_id`,`description`,`ip_address`,`user_agent`,`created_at`)
VALUES
(1,3,'file_upload',  'file',18,'{\"filename\":\"IMG_0459.png\",\"size\":158338}',
 '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea',
 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1',
 '2026-01-01 05:47:00'),
(2,3,'file_download','file',18,'{\"short_code\":\"jLSTkvyj\",\"filename\":\"IMG_0459.png\"}',
 '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea',
 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1',
 '2026-01-01 05:47:05');

--
-- proshare_audit_logs
--
INSERT IGNORE INTO `proshare_audit_logs`
  (`id`,`user_id`,`action`,`resource_type`,`resource_id`,`ip_address`,`user_agent`,`details`,`created_at`)
VALUES
(31,3,'file_upload',  'file',18,'2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea',
 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1',
 '{\"filename\":\"IMG_0459.png\",\"size\":158338}','2026-01-01 05:47:00'),
(32,3,'file_download','file',18,'2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea',
 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1',
 '{\"short_code\":\"jLSTkvyj\",\"filename\":\"IMG_0459.png\"}','2026-01-01 05:47:05');

-- ---------------------------------------------------------------------------
-- 3. LIVE MIGRATION (Option A)
--    Uncomment if the old `proshare` DB is accessible on the same server.
--    Adjust the source database name if it differs from `proshare`.
-- ---------------------------------------------------------------------------

/*
INSERT IGNORE INTO `proshare_files`             SELECT * FROM `proshare`.`files`;
INSERT IGNORE INTO `proshare_file_downloads`    SELECT * FROM `proshare`.`file_downloads`;
INSERT IGNORE INTO `proshare_folders`           SELECT * FROM `proshare`.`folders`;
INSERT IGNORE INTO `proshare_file_folders`      SELECT * FROM `proshare`.`file_folders`;
INSERT IGNORE INTO `proshare_text_shares`       SELECT * FROM `proshare`.`text_shares`;
INSERT IGNORE INTO `proshare_messages`          SELECT * FROM `proshare`.`messages`;
INSERT IGNORE INTO `proshare_chat_rooms`        SELECT * FROM `proshare`.`chat_rooms`;
INSERT IGNORE INTO `proshare_link_access`       SELECT * FROM `proshare`.`link_access`;
INSERT IGNORE INTO `proshare_notifications`     SELECT * FROM `proshare`.`notifications`;
INSERT IGNORE INTO `proshare_audit_logs`        SELECT * FROM `proshare`.`audit_logs`;
INSERT IGNORE INTO `proshare_activity_logs`     SELECT * FROM `proshare`.`activity_logs`;
INSERT IGNORE INTO `proshare_user_settings`     SELECT * FROM `proshare`.`user_settings`;
INSERT IGNORE INTO `proshare_backups`           SELECT * FROM `proshare`.`backups`;
INSERT IGNORE INTO `proshare_settings`          SELECT * FROM `proshare`.`settings`;
*/
