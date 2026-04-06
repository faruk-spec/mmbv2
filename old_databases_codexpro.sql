-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 06, 2026 at 12:48 PM
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
-- Database: `codexpro`
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

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `language` varchar(50) DEFAULT 'html',
  `html_content` longtext DEFAULT NULL,
  `css_content` longtext DEFAULT NULL,
  `js_content` longtext DEFAULT NULL,
  `visibility` enum('private','public') DEFAULT 'private',
  `views` int(10) UNSIGNED DEFAULT 0,
  `likes` int(10) UNSIGNED DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `user_id`, `name`, `description`, `language`, `html_content`, `css_content`, `js_content`, `visibility`, `views`, `likes`, `created_at`, `updated_at`) VALUES
(5, 3, 'HTML5 Boilerplate', 'Basic HTML5 structure', 'html', '<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Document</title>\n    <link rel=\"stylesheet\" href=\"style.css\">\n</head>\n<body>\n    <h1>Hello World</h1>\n    <script src=\"script.js\"></script>\n</body>\n</html>', '* {\n    margin: 0;\n    padding: 0;\n    box-sizing: border-box;\n}\n\nbody {\n    font-family: Arial, sans-serif;\n    line-height: 1.6;\n    padding: 20px;\n}', 'console.log(\"Hello World!\");', 'private', 0, 0, '2026-01-01 07:44:29', NULL),
(6, 3, 'React App', 'Basic React application setup', 'html', '<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>React App</title>\n    <script crossorigin src=\"https://unpkg.com/react@18/umd/react.production.min.js\"></script>\n    <script crossorigin src=\"https://unpkg.com/react-dom@18/umd/react-dom.production.min.js\"></script>\n    <script src=\"https://unpkg.com/@babel/standalone/babel.min.js\"></script>\n</head>\n<body>\n    <div id=\"root\"></div>\n    <script type=\"text/babel\" src=\"app.js\"></script>\n</body>\n</html>', '', 'function App() {\n    const [count, setCount] = React.useState(0);\n    \n    return (\n        <div style={{ padding: \"20px\" }}>\n            <h1>React Counter</h1>\n            <p>Count: {count}</p>\n            <button onClick={() => setCount(count + 1)}>\n                Increment\n            </button>\n        </div>\n    );\n}\n\nReactDOM.render(<App />, document.getElementById(\"root\"));', 'private', 0, 0, '2026-01-01 07:44:32', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `project_shares`
--

CREATE TABLE `project_shares` (
  `id` int(10) UNSIGNED NOT NULL,
  `project_id` int(10) UNSIGNED NOT NULL,
  `shared_with_user_id` int(10) UNSIGNED DEFAULT NULL,
  `share_token` varchar(100) DEFAULT NULL,
  `can_edit` tinyint(1) DEFAULT 0,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, 'max_project_size', '10485760', 'integer', 'Maximum project size in bytes (10MB)', 1, '2025-12-03 12:45:53', NULL),
(2, 'max_projects_per_user', '50', 'integer', 'Maximum projects per user', 1, '2025-12-03 12:45:53', NULL),
(3, 'auto_save_interval', '30', 'integer', 'Auto-save interval in seconds', 1, '2025-12-03 12:45:53', NULL),
(4, 'default_theme', 'dark', 'string', 'Default editor theme', 1, '2025-12-03 12:45:53', NULL),
(5, 'enable_auto_save', '1', 'boolean', 'Enable auto-save feature', 1, '2025-12-03 12:45:53', NULL),
(6, 'enable_auto_preview', '1', 'boolean', 'Enable auto-preview feature', 1, '2025-12-03 12:45:53', NULL),
(7, 'enable_exports', '1', 'boolean', 'Enable project exports', 1, '2025-12-03 12:45:53', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `snippets`
--

CREATE TABLE `snippets` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `code` longtext NOT NULL,
  `language` varchar(50) DEFAULT 'javascript',
  `tags` varchar(255) DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `views` int(10) UNSIGNED DEFAULT 0,
  `likes` int(10) UNSIGNED DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `snippets`
--

INSERT INTO `snippets` (`id`, `user_id`, `title`, `description`, `code`, `language`, `tags`, `is_public`, `views`, `likes`, `created_at`, `updated_at`) VALUES
(1, 1, 'test', '', 'test', 'php', '', 0, 24, 0, '2025-12-04 13:41:27', '2025-12-05 15:43:05'),
(2, 3, 'gffbb', 'gbgb', 'gbgfbgfbgbgb', 'html', '', 0, 1, 0, '2026-01-03 02:46:24', '2026-01-03 02:46:24');

-- --------------------------------------------------------

--
-- Table structure for table `templates`
--

CREATE TABLE `templates` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `html_content` longtext DEFAULT NULL,
  `css_content` longtext DEFAULT NULL,
  `js_content` longtext DEFAULT NULL,
  `category` varchar(50) DEFAULT 'general',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_settings`
--

CREATE TABLE `user_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `theme` varchar(50) DEFAULT 'dark',
  `font_size` int(11) DEFAULT 14,
  `tab_size` int(11) DEFAULT 2,
  `auto_save` tinyint(1) DEFAULT 1,
  `auto_preview` tinyint(1) DEFAULT 1,
  `key_bindings` varchar(50) DEFAULT 'default',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_settings`
--

INSERT INTO `user_settings` (`id`, `user_id`, `theme`, `font_size`, `tab_size`, `auto_save`, `auto_preview`, `key_bindings`, `created_at`, `updated_at`) VALUES
(1, 1, 'dark', 14, 2, 1, 1, 'default', '2025-12-04 13:20:35', NULL),
(2, 3, 'dark', 14, 2, 1, 1, 'default', '2025-12-05 22:12:32', NULL);

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
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_visibility` (`visibility`);

--
-- Indexes for table `project_shares`
--
ALTER TABLE `project_shares`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_project_id` (`project_id`),
  ADD KEY `idx_share_token` (`share_token`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`),
  ADD KEY `idx_key` (`key`);

--
-- Indexes for table `snippets`
--
ALTER TABLE `snippets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_language` (`language`),
  ADD KEY `idx_is_public` (`is_public`);

--
-- Indexes for table `templates`
--
ALTER TABLE `templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_is_active` (`is_active`);

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `project_shares`
--
ALTER TABLE `project_shares`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `snippets`
--
ALTER TABLE `snippets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `templates`
--
ALTER TABLE `templates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_settings`
--
ALTER TABLE `user_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `project_shares`
--
ALTER TABLE `project_shares`
  ADD CONSTRAINT `project_shares_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
