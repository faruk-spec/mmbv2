-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 06, 2026 at 12:46 PM
-- Server version: 10.11.10-MariaDB-log
-- PHP Version: 8.3.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `proshare`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `resource_type` varchar(50) NOT NULL,
  `resource_id` int(10) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `resource_type`, `resource_id`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 3, 'file_upload', 'file', 18, '{\"filename\":\"IMG_0459.png\",\"size\":158338}', '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', '2026-01-01 05:47:00'),
(2, 3, 'file_download', 'file', 18, '{\"short_code\":\"jLSTkvyj\",\"filename\":\"IMG_0459.png\"}', '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', '2026-01-01 05:47:05');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `resource_type` varchar(50) NOT NULL,
  `resource_id` int(10) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `resource_type`, `resource_id`, `ip_address`, `user_agent`, `details`, `created_at`) VALUES
(31, 3, 'file_upload', 'file', 18, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', '{\"filename\":\"IMG_0459.png\",\"size\":158338}', '2026-01-01 05:47:00'),
(32, 3, 'file_download', 'file', 18, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', '{\"short_code\":\"jLSTkvyj\",\"filename\":\"IMG_0459.png\"}', '2026-01-01 05:47:05');

-- --------------------------------------------------------

--
-- Table structure for table `backups`
--

CREATE TABLE `backups` (
  `id` int(10) UNSIGNED NOT NULL,
  `file_id` int(10) UNSIGNED NOT NULL,
  `backup_path` varchar(500) NOT NULL,
  `backup_size` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `backups`
--

INSERT INTO `backups` (`id`, `file_id`, `backup_path`, `backup_size`, `created_at`) VALUES
(17, 17, '/www/wwwroot/test.mymultibranch.com/storage/backups/proshare/2025/12/JbmTtm6N_1765662323.png', 598364, '2025-12-13 21:45:23');

-- --------------------------------------------------------

--
-- Table structure for table `chat_rooms`
--

CREATE TABLE `chat_rooms` (
  `id` int(10) UNSIGNED NOT NULL,
  `room_code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `creator_id` int(10) UNSIGNED DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `max_users` int(11) DEFAULT 10,
  `is_private` tinyint(1) DEFAULT 0,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'NULL for anonymous uploads',
  `short_code` varchar(10) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `path` varchar(500) NOT NULL,
  `size` bigint(20) UNSIGNED NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `downloads` int(10) UNSIGNED DEFAULT 0,
  `max_downloads` int(10) UNSIGNED DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 1,
  `is_encrypted` tinyint(1) DEFAULT 0,
  `encryption_key` varchar(255) DEFAULT NULL,
  `self_destruct` tinyint(1) DEFAULT 0,
  `checksum` varchar(64) DEFAULT NULL COMMENT 'SHA-256 hash for integrity',
  `is_compressed` tinyint(1) DEFAULT 0,
  `status` enum('active','expired','deleted','reported') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `files`
--

INSERT INTO `files` (`id`, `user_id`, `short_code`, `original_name`, `filename`, `path`, `size`, `mime_type`, `password`, `downloads`, `max_downloads`, `expires_at`, `is_public`, `is_encrypted`, `encryption_key`, `self_destruct`, `checksum`, `is_compressed`, `status`, `created_at`, `updated_at`) VALUES
(17, 3, 'JbmTtm6N', '1.png', 'JbmTtm6N_1765662323.png', '/www/wwwroot/test.mymultibranch.com/storage/uploads/proshare/2025/12/JbmTtm6N_1765662323.png', 598364, 'image/jpeg', NULL, 1, NULL, '2025-12-20 21:45:23', 1, 0, NULL, 1, '835bfa542ecb2d65d3bb55d7fb632a99a1c7fa9935e8fe508a17df75baecc69f', 0, 'deleted', '2025-12-13 21:45:23', '2025-12-13 21:52:13');

-- --------------------------------------------------------

--
-- Table structure for table `file_downloads`
--

CREATE TABLE `file_downloads` (
  `id` int(10) UNSIGNED NOT NULL,
  `file_id` int(10) UNSIGNED NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `referer` varchar(500) DEFAULT NULL,
  `downloaded_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `file_downloads`
--

INSERT INTO `file_downloads` (`id`, `file_id`, `ip_address`, `user_agent`, `referer`, `downloaded_at`) VALUES
(9, 17, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'https://test.mymultibranch.com/projects/proshare/preview/JbmTtm6N', '2025-12-13 21:52:13');

-- --------------------------------------------------------

--
-- Table structure for table `file_folders`
--

CREATE TABLE `file_folders` (
  `file_id` int(10) UNSIGNED NOT NULL,
  `folder_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `folders`
--

CREATE TABLE `folders` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `short_code` varchar(10) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `link_access`
--

CREATE TABLE `link_access` (
  `id` int(10) UNSIGNED NOT NULL,
  `file_id` int(10) UNSIGNED DEFAULT NULL,
  `text_id` int(10) UNSIGNED DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `can_download` tinyint(1) DEFAULT 1,
  `access_count` int(10) UNSIGNED DEFAULT 0,
  `max_access` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `room_id` varchar(50) NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `is_encrypted` tinyint(1) DEFAULT 0,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `type` enum('download','expiry_warning','security_alert','upload_complete') NOT NULL,
  `message` text NOT NULL,
  `related_id` int(10) UNSIGNED DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `message`, `related_id`, `is_read`, `created_at`) VALUES
(1, 1, 'download', 'Your file \'airport to hotel cab.jpeg\' was downloaded.', 2, 0, '2025-12-05 17:47:46'),
(2, 1, 'download', 'Your file \'airport to hotel cab.jpeg\' was downloaded.', 2, 0, '2025-12-05 17:47:57'),
(3, 1, 'download', 'Your file \'airport to hotel cab.jpeg\' was downloaded.', 3, 0, '2025-12-05 19:26:15'),
(4, 1, 'download', 'Your file \'Invoice_7341822801.pdf\' was downloaded.', 12, 0, '2025-12-05 19:30:49'),
(5, 3, 'download', 'Your file \'airport to hotel cab.jpeg\' was downloaded.', 13, 1, '2025-12-06 11:49:36'),
(6, 3, 'download', 'Your file \'6-oct-food.jpeg\' was downloaded.', 15, 1, '2025-12-06 12:02:55'),
(7, 3, 'download', 'Your file \'flight-Receipt.pdf\' was downloaded.', 14, 1, '2025-12-06 12:03:15'),
(8, 3, 'download', 'Your file \'boarding-pass.jpeg\' was downloaded.', 16, 1, '2025-12-07 21:50:02'),
(9, 3, 'download', 'Your file \'1.png\' was downloaded.', 17, 1, '2025-12-13 21:52:13'),
(10, 3, 'download', 'Your file \'IMG_0459.png\' was downloaded.', 18, 1, '2026-01-01 05:47:05');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `type` enum('string','integer','boolean','json') DEFAULT 'string',
  `description` text DEFAULT NULL,
  `is_system` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `type`, `description`, `is_system`, `created_at`, `updated_at`) VALUES
(1, 'max_file_size', '52428800', 'integer', 'Maximum file size in bytes (500MB)', 1, '2025-12-03 12:48:11', '2026-01-01 05:08:21'),
(2, 'default_expiry_hours', '24', 'integer', 'Default link expiry in hours', 1, '2025-12-03 12:48:11', NULL),
(3, 'enable_password_protection', '1', 'boolean', 'Enable password protection', 1, '2025-12-03 12:48:11', NULL),
(4, 'enable_self_destruct', '1', 'boolean', 'Enable self-destruct feature', 1, '2025-12-03 12:48:11', NULL),
(5, 'enable_compression', '1', 'boolean', 'Enable file compression', 1, '2025-12-03 12:48:11', NULL),
(6, 'enable_anonymous_upload', '1', 'boolean', 'Enable anonymous uploads', 1, '2025-12-03 12:48:11', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `text_shares`
--

CREATE TABLE `text_shares` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `short_code` varchar(10) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` longtext NOT NULL,
  `is_encrypted` tinyint(1) DEFAULT 0,
  `password` varchar(255) DEFAULT NULL,
  `views` int(10) UNSIGNED DEFAULT 0,
  `max_views` int(10) UNSIGNED DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `self_destruct` tinyint(1) DEFAULT 0,
  `status` enum('active','expired','deleted') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `text_shares`
--

INSERT INTO `text_shares` (`id`, `user_id`, `short_code`, `title`, `content`, `is_encrypted`, `password`, `views`, `max_views`, `expires_at`, `self_destruct`, `status`, `created_at`) VALUES
(1, 1, 'Op6bvAFC', 'hghghg', 'gjghjhjhjjhjjnh', 0, NULL, 0, NULL, '2025-12-05 17:12:24', 1, 'expired', '2025-12-05 16:12:24'),
(2, 3, 'CMFqrs3W', 'test share', ' cgfdsdfcdfcc', 0, '$argon2id$v=19$m=65536,t=4,p=1$mWC9qR6fIq9Gy8ED84e2BQ$UJrUMTmwWfhj/oxmPm1nYrY2/mSbjdVWMLGjpKOi6Yo', 1, NULL, '2025-12-07 12:03:40', 0, 'expired', '2025-12-06 12:03:40');

-- --------------------------------------------------------

--
-- Table structure for table `user_settings`
--

CREATE TABLE `user_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `email_notifications` tinyint(1) DEFAULT 1,
  `sms_notifications` tinyint(1) DEFAULT 0,
  `default_expiry` int(11) DEFAULT 24 COMMENT 'hours',
  `auto_delete` tinyint(1) DEFAULT 0,
  `enable_encryption` tinyint(1) DEFAULT 0,
  `enable_compression` tinyint(1) DEFAULT 1,
  `max_file_size` bigint(20) UNSIGNED DEFAULT 524288000 COMMENT '500MB default',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_settings`
--

INSERT INTO `user_settings` (`id`, `user_id`, `email_notifications`, `sms_notifications`, `default_expiry`, `auto_delete`, `enable_encryption`, `enable_compression`, `max_file_size`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 0, 168, 0, 0, 1, 104857600, '2025-12-04 10:51:42', '2025-12-05 17:27:48'),
(2, 3, 1, 0, 168, 0, 0, 1, 104857600, '2025-12-05 22:13:13', '2026-01-03 02:10:47');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_resource_type` (`resource_type`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `backups`
--
ALTER TABLE `backups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_file_id` (`file_id`);

--
-- Indexes for table `chat_rooms`
--
ALTER TABLE `chat_rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_code` (`room_code`),
  ADD KEY `idx_room_code` (`room_code`);

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `short_code` (`short_code`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_short_code` (`short_code`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `file_downloads`
--
ALTER TABLE `file_downloads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_file_id` (`file_id`),
  ADD KEY `idx_downloaded_at` (`downloaded_at`);

--
-- Indexes for table `file_folders`
--
ALTER TABLE `file_folders`
  ADD PRIMARY KEY (`file_id`,`folder_id`),
  ADD KEY `folder_id` (`folder_id`);

--
-- Indexes for table `folders`
--
ALTER TABLE `folders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `short_code` (`short_code`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_short_code` (`short_code`);

--
-- Indexes for table `link_access`
--
ALTER TABLE `link_access`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_file_id` (`file_id`),
  ADD KEY `idx_text_id` (`text_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_room_id` (`room_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_is_read` (`is_read`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`),
  ADD KEY `idx_key` (`key`);

--
-- Indexes for table `text_shares`
--
ALTER TABLE `text_shares`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `short_code` (`short_code`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_short_code` (`short_code`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `backups`
--
ALTER TABLE `backups`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `chat_rooms`
--
ALTER TABLE `chat_rooms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `file_downloads`
--
ALTER TABLE `file_downloads`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `folders`
--
ALTER TABLE `folders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `link_access`
--
ALTER TABLE `link_access`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `text_shares`
--
ALTER TABLE `text_shares`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_settings`
--
ALTER TABLE `user_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `backups`
--
ALTER TABLE `backups`
  ADD CONSTRAINT `backups_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `file_downloads`
--
ALTER TABLE `file_downloads`
  ADD CONSTRAINT `file_downloads_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `file_folders`
--
ALTER TABLE `file_folders`
  ADD CONSTRAINT `file_folders_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `file_folders_ibfk_2` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `link_access`
--
ALTER TABLE `link_access`
  ADD CONSTRAINT `link_access_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `link_access_ibfk_2` FOREIGN KEY (`text_id`) REFERENCES `text_shares` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
