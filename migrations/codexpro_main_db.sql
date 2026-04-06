-- =============================================================================
-- CodeXPro: prefix-based migration to main database
-- Generated: 2026-04-06  (built from old_databases_codexpro.sql dump)
-- Source DB : codexpro  (MariaDB 10.11.10)
-- Target    : main application database
-- Prefix    : codexpro_
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
-- codexpro_projects
--
CREATE TABLE IF NOT EXISTS `codexpro_projects` (
  `id`           int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`      int(10) UNSIGNED NOT NULL,
  `name`         varchar(100)     NOT NULL,
  `description`  text             DEFAULT NULL,
  `language`     varchar(50)      DEFAULT 'html',
  `html_content` longtext         DEFAULT NULL,
  `css_content`  longtext         DEFAULT NULL,
  `js_content`   longtext         DEFAULT NULL,
  `visibility`   enum('private','public') DEFAULT 'private',
  `views`        int(10) UNSIGNED DEFAULT 0,
  `likes`        int(10) UNSIGNED DEFAULT 0,
  `created_at`   timestamp NULL   DEFAULT current_timestamp(),
  `updated_at`   timestamp NULL   DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id`   (`user_id`),
  KEY `idx_visibility`(`visibility`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- codexpro_project_shares
--
CREATE TABLE IF NOT EXISTS `codexpro_project_shares` (
  `id`                   int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id`           int(10) UNSIGNED NOT NULL,
  `shared_with_user_id`  int(10) UNSIGNED DEFAULT NULL,
  `share_token`          varchar(100)     DEFAULT NULL,
  `can_edit`             tinyint(1)       DEFAULT 0,
  `expires_at`           timestamp NULL   DEFAULT NULL,
  `created_at`           timestamp NULL   DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_project_id`  (`project_id`),
  KEY `idx_share_token` (`share_token`),
  CONSTRAINT `codexpro_project_shares_ibfk_1`
    FOREIGN KEY (`project_id`) REFERENCES `codexpro_projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- codexpro_snippets
--
CREATE TABLE IF NOT EXISTS `codexpro_snippets` (
  `id`          int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     int(10) UNSIGNED NOT NULL,
  `title`       varchar(100)     NOT NULL,
  `description` text             DEFAULT NULL,
  `code`        longtext         NOT NULL,
  `language`    varchar(50)      DEFAULT 'javascript',
  `tags`        varchar(255)     DEFAULT NULL,
  `is_public`   tinyint(1)       DEFAULT 0,
  `views`       int(10) UNSIGNED DEFAULT 0,
  `likes`       int(10) UNSIGNED DEFAULT 0,
  `created_at`  timestamp NULL   DEFAULT current_timestamp(),
  `updated_at`  timestamp NULL   DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id`  (`user_id`),
  KEY `idx_language` (`language`),
  KEY `idx_is_public`(`is_public`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- codexpro_templates
--
CREATE TABLE IF NOT EXISTS `codexpro_templates` (
  `id`           int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`         varchar(100)     NOT NULL,
  `description`  text             DEFAULT NULL,
  `html_content` longtext         DEFAULT NULL,
  `css_content`  longtext         DEFAULT NULL,
  `js_content`   longtext         DEFAULT NULL,
  `category`     varchar(50)      DEFAULT 'general',
  `is_active`    tinyint(1)       DEFAULT 1,
  `created_at`   timestamp NULL   DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_category`  (`category`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- codexpro_user_settings
--
CREATE TABLE IF NOT EXISTS `codexpro_user_settings` (
  `id`           int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`      int(10) UNSIGNED NOT NULL,
  `theme`        varchar(50)      DEFAULT 'dark',
  `font_size`    int(11)          DEFAULT 14,
  `tab_size`     int(11)          DEFAULT 2,
  `auto_save`    tinyint(1)       DEFAULT 1,
  `auto_preview` tinyint(1)       DEFAULT 1,
  `key_bindings` varchar(50)      DEFAULT 'default',
  `created_at`   timestamp NULL   DEFAULT current_timestamp(),
  `updated_at`   timestamp NULL   DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id`     (`user_id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- codexpro_settings
--
CREATE TABLE IF NOT EXISTS `codexpro_settings` (
  `id`          int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `key`         varchar(100)     NOT NULL,
  `value`       text             DEFAULT NULL,
  `type`        enum('string','integer','boolean','json') DEFAULT 'string',
  `description` text             DEFAULT NULL,
  `is_system`   tinyint(1)       DEFAULT 1,
  `created_at`  timestamp NULL   DEFAULT current_timestamp(),
  `updated_at`  timestamp NULL   DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `key`     (`key`),
  KEY `idx_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- codexpro_activity_logs
--
CREATE TABLE IF NOT EXISTS `codexpro_activity_logs` (
  `id`            int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`       int(10) UNSIGNED DEFAULT NULL,
  `action`        varchar(100)     NOT NULL,
  `resource_type` varchar(50)      NOT NULL,
  `resource_id`   int(10) UNSIGNED DEFAULT NULL,
  `description`   text             DEFAULT NULL,
  `ip_address`    varchar(45)      DEFAULT NULL,
  `user_agent`    varchar(500)     DEFAULT NULL,
  `created_at`    timestamp NULL   DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id`       (`user_id`),
  KEY `idx_action`        (`action`),
  KEY `idx_resource_type` (`resource_type`),
  KEY `idx_created_at`    (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- codexpro_project_files  (multi-file support)
--
CREATE TABLE IF NOT EXISTS `codexpro_project_files` (
  `id`           int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id`   int(10) UNSIGNED NOT NULL,
  `file_path`    varchar(255)     NOT NULL,
  `file_content` longtext         NOT NULL,
  `created_at`   timestamp NULL   DEFAULT current_timestamp(),
  `updated_at`   timestamp NULL   DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_project_id` (`project_id`),
  KEY `idx_file_path`  (`project_id`, `file_path`),
  CONSTRAINT `codexpro_project_files_ibfk_1`
    FOREIGN KEY (`project_id`) REFERENCES `codexpro_projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- codexpro_project_folders  (multi-file support)
--
CREATE TABLE IF NOT EXISTS `codexpro_project_folders` (
  `id`          int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id`  int(10) UNSIGNED NOT NULL,
  `folder_path` varchar(255)     NOT NULL,
  `created_at`  timestamp NULL   DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_project_folder` (`project_id`, `folder_path`),
  CONSTRAINT `codexpro_project_folders_ibfk_1`
    FOREIGN KEY (`project_id`) REFERENCES `codexpro_projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- codexpro_user_templates
--
CREATE TABLE IF NOT EXISTS `codexpro_user_templates` (
  `id`          int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     int(10) UNSIGNED NOT NULL,
  `name`        varchar(255)     NOT NULL,
  `description` text             DEFAULT NULL,
  `category`    varchar(50)      NOT NULL,
  `is_public`   tinyint(1)       DEFAULT 0,
  `downloads`   int(10) UNSIGNED DEFAULT 0,
  `created_at`  timestamp NULL   DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id`  (`user_id`),
  KEY `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- codexpro_template_files
--
CREATE TABLE IF NOT EXISTS `codexpro_template_files` (
  `id`           int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_id`  int(10) UNSIGNED NOT NULL,
  `file_path`    varchar(255)     NOT NULL,
  `file_content` longtext         NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `codexpro_template_files_ibfk_1`
    FOREIGN KEY (`template_id`) REFERENCES `codexpro_user_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 2. SEED / EXISTING DATA  (literal rows from the old DB dump)
-- ---------------------------------------------------------------------------

--
-- codexpro_projects  (real records from old codexpro DB)
--
INSERT IGNORE INTO `codexpro_projects`
  (`id`,`user_id`,`name`,`description`,`language`,`html_content`,`css_content`,`js_content`,
   `visibility`,`views`,`likes`,`created_at`,`updated_at`)
VALUES
(5,3,'HTML5 Boilerplate','Basic HTML5 structure','html',
 '<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Document</title>\n    <link rel=\"stylesheet\" href=\"style.css\">\n</head>\n<body>\n    <h1>Hello World</h1>\n    <script src=\"script.js\"></script>\n</body>\n</html>',
 '* {\n    margin: 0;\n    padding: 0;\n    box-sizing: border-box;\n}\n\nbody {\n    font-family: Arial, sans-serif;\n    line-height: 1.6;\n    padding: 20px;\n}',
 'console.log(\"Hello World!\");',
 'private',0,0,'2026-01-01 07:44:29',NULL),
(6,3,'React App','Basic React application setup','html',
 '<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>React App</title>\n    <script crossorigin src=\"https://unpkg.com/react@18/umd/react.production.min.js\"></script>\n    <script crossorigin src=\"https://unpkg.com/react-dom@18/umd/react-dom.production.min.js\"></script>\n    <script src=\"https://unpkg.com/@babel/standalone/babel.min.js\"></script>\n</head>\n<body>\n    <div id=\"root\"></div>\n    <script type=\"text/babel\" src=\"app.js\"></script>\n</body>\n</html>',
 '',
 'function App() {\n    const [count, setCount] = React.useState(0);\n    \n    return (\n        <div style={{ padding: \"20px\" }}>\n            <h1>React Counter</h1>\n            <p>Count: {count}</p>\n            <button onClick={() => setCount(count + 1)}>\n                Increment\n            </button>\n        </div>\n    );\n}\n\nReactDOM.render(<App />, document.getElementById(\"root\"));',
 'private',0,0,'2026-01-01 07:44:32',NULL);

--
-- codexpro_snippets
--
INSERT IGNORE INTO `codexpro_snippets`
  (`id`,`user_id`,`title`,`description`,`code`,`language`,`tags`,`is_public`,`views`,`likes`,`created_at`,`updated_at`)
VALUES
(1,1,'test','','test','php','',0,24,0,'2025-12-04 13:41:27','2025-12-05 15:43:05'),
(2,3,'gffbb','gbgb','gbgfbgfbgbgb','html','',0,1,0,'2026-01-03 02:46:24','2026-01-03 02:46:24');

--
-- codexpro_settings
--
INSERT IGNORE INTO `codexpro_settings`
  (`id`,`key`,`value`,`type`,`description`,`is_system`,`created_at`,`updated_at`)
VALUES
(1,'max_project_size',    '10485760','integer','Maximum project size in bytes (10MB)', 1,'2025-12-03 12:45:53',NULL),
(2,'max_projects_per_user','50',     'integer','Maximum projects per user',            1,'2025-12-03 12:45:53',NULL),
(3,'auto_save_interval',  '30',      'integer','Auto-save interval in seconds',        1,'2025-12-03 12:45:53',NULL),
(4,'default_theme',       'dark',    'string', 'Default editor theme',                 1,'2025-12-03 12:45:53',NULL),
(5,'enable_auto_save',    '1',       'boolean','Enable auto-save feature',             1,'2025-12-03 12:45:53',NULL),
(6,'enable_auto_preview', '1',       'boolean','Enable auto-preview feature',          1,'2025-12-03 12:45:53',NULL),
(7,'enable_exports',      '1',       'boolean','Enable project exports',               1,'2025-12-03 12:45:53',NULL);

--
-- codexpro_user_settings
--
INSERT IGNORE INTO `codexpro_user_settings`
  (`id`,`user_id`,`theme`,`font_size`,`tab_size`,`auto_save`,`auto_preview`,`key_bindings`,`created_at`,`updated_at`)
VALUES
(1,1,'dark',14,2,1,1,'default','2025-12-04 13:20:35',NULL),
(2,3,'dark',14,2,1,1,'default','2025-12-05 22:12:32',NULL);

-- ---------------------------------------------------------------------------
-- 3. LIVE MIGRATION (Option A)
--    Uncomment if the old `codexpro` DB is accessible on the same server.
--    Adjust the source database name if it differs from `codexpro`.
-- ---------------------------------------------------------------------------

/*
INSERT IGNORE INTO `codexpro_projects`          SELECT * FROM `codexpro`.`projects`;
INSERT IGNORE INTO `codexpro_project_shares`    SELECT * FROM `codexpro`.`project_shares`;
INSERT IGNORE INTO `codexpro_snippets`          SELECT * FROM `codexpro`.`snippets`;
INSERT IGNORE INTO `codexpro_templates`         SELECT * FROM `codexpro`.`templates`;
INSERT IGNORE INTO `codexpro_user_settings`     SELECT * FROM `codexpro`.`user_settings`;
INSERT IGNORE INTO `codexpro_settings`          SELECT * FROM `codexpro`.`settings`;
INSERT IGNORE INTO `codexpro_activity_logs`     SELECT * FROM `codexpro`.`activity_logs`;
*/
