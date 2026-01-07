-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 07, 2026 at 02:28 PM
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
-- Database: `testuser`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `ip_address`, `user_agent`, `data`, `created_at`) VALUES
(1, 1, 'login', '106.215.136.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.136.103\"}', '2025-12-02 22:22:37'),
(2, 1, 'logout', '106.215.136.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-02 22:24:42'),
(3, 1, 'login', '106.215.136.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.136.103\"}', '2025-12-02 22:35:06'),
(4, 1, 'login', '106.215.136.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.136.103\"}', '2025-12-02 22:35:07'),
(5, 1, 'profile_updated', '106.215.136.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-02 23:03:55'),
(6, 1, 'logout', '106.215.136.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-03 00:06:30'),
(7, 1, 'login', '106.215.136.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.136.103\"}', '2025-12-03 00:08:58'),
(8, 1, 'login', '106.215.140.94', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.94\"}', '2025-12-03 01:09:27'),
(9, 1, 'logout', '106.215.140.94', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-03 01:17:35'),
(10, 1, 'login', '106.215.140.94', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.94\"}', '2025-12-03 01:19:10'),
(11, 1, 'logout', '106.215.140.94', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-03 01:22:28'),
(12, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.20\"}', '2025-12-03 10:46:42'),
(13, 1, 'logout', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-03 12:50:19'),
(14, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.20\"}', '2025-12-03 13:17:17'),
(15, 1, 'logout', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-03 13:30:52'),
(16, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.20\"}', '2025-12-03 14:17:57'),
(17, 1, 'logout', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-03 14:22:04'),
(18, 2, 'register', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"email\":\"testuser@testuser.testuser\"}', '2025-12-03 14:22:36'),
(19, 2, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.20\"}', '2025-12-03 14:22:44'),
(20, 2, '2fa_enabled', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-03 14:23:47'),
(21, 2, '2fa_disabled', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-03 14:24:08'),
(22, 2, 'logout', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-03 14:38:13'),
(23, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.20\"}', '2025-12-03 14:38:30'),
(24, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '{\"ip\":\"106.215.140.20\"}', '2025-12-03 15:18:32'),
(25, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.20\"}', '2025-12-03 15:18:48'),
(26, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.20\"}', '2025-12-03 15:36:58'),
(27, 1, 'login', '2401:4900:8fc2:dcf8:5806:be3f:c5ca:16', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.148 Mobile/15E148 Safari/604.1', '{\"ip\":\"2401:4900:8fc2:dcf8:5806:be3f:c5ca:16\"}', '2025-12-03 16:19:03'),
(28, 1, 'logout', '136.226.251.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-03 21:11:53'),
(29, 2, 'login', '136.226.251.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"136.226.251.10\"}', '2025-12-03 21:12:48'),
(30, 2, 'logout', '136.226.251.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-03 21:12:59'),
(31, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.20\"}', '2025-12-03 21:16:31'),
(32, 1, 'logout', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-04 01:04:11'),
(33, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.20\"}', '2025-12-04 01:04:44'),
(34, 1, 'logout', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-04 08:28:31'),
(35, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.20\"}', '2025-12-04 08:28:58'),
(36, 1, 'logout', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-04 09:32:19'),
(37, 2, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.20\"}', '2025-12-04 09:33:06'),
(38, 2, 'logout', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-04 09:56:56'),
(39, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.20\"}', '2025-12-04 09:57:13'),
(40, 1, 'logout', '2401:4900:8fc2:dcf8:f1cd:d9b:bef2:7250', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-04 13:20:03'),
(41, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.20\"}', '2025-12-04 13:20:21'),
(42, 1, 'logout', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-04 13:38:32'),
(43, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.20\"}', '2025-12-04 13:38:48'),
(44, 1, 'logout', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-04 13:39:48'),
(45, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.20\"}', '2025-12-04 13:40:26'),
(46, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '{\"ip\":\"106.215.140.20\"}', '2025-12-04 13:48:46'),
(47, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.20\"}', '2025-12-04 13:49:46'),
(48, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.20\"}', '2025-12-04 13:54:38'),
(49, 1, 'logout', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-04 15:00:51'),
(50, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.20\"}', '2025-12-04 15:01:11'),
(51, 1, 'logout', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-04 15:26:49'),
(52, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.20\"}', '2025-12-04 15:27:48'),
(53, 1, 'logout', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-04 15:47:08'),
(54, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.20\"}', '2025-12-04 15:47:29'),
(55, 1, 'login', '2401:4900:8fc2:dcf8:a915:aa97:8e0a:d618', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.148 Mobile/15E148 Safari/604.1', '{\"ip\":\"2401:4900:8fc2:dcf8:a915:aa97:8e0a:d618\"}', '2025-12-04 16:02:20'),
(56, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '{\"ip\":\"106.215.140.20\"}', '2025-12-04 16:04:00'),
(57, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.20\"}', '2025-12-04 16:09:22'),
(58, 1, 'login', '2401:4900:8fc2:dcf8:a915:aa97:8e0a:d618', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '{\"ip\":\"2401:4900:8fc2:dcf8:a915:aa97:8e0a:d618\"}', '2025-12-04 16:10:49'),
(59, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.20\"}', '2025-12-04 16:11:48'),
(60, 1, 'logout', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-04 18:17:05'),
(61, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.20\"}', '2025-12-04 18:17:20'),
(62, 1, 'logout', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-04 18:35:01'),
(63, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.20\"}', '2025-12-04 18:35:16'),
(64, 1, 'logout', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-04 19:09:49'),
(65, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.20\"}', '2025-12-04 19:22:27'),
(66, 1, 'project_toggled', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"project\":\"devzone\"}', '2025-12-04 19:33:53'),
(67, 1, 'project_toggled', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"project\":\"devzone\"}', '2025-12-04 19:33:57'),
(68, 1, 'project_settings_updated', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"project\":\"devzone\"}', '2025-12-04 19:34:06'),
(69, 1, 'project_toggled', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"project\":\"devzone\"}', '2025-12-04 19:34:11'),
(70, 1, 'logout', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-04 19:41:58'),
(71, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.20\"}', '2025-12-04 19:42:12'),
(72, 1, 'logout', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-04 19:52:13'),
(73, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.20\"}', '2025-12-04 19:52:39'),
(74, 1, 'logout', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-04 20:14:15'),
(75, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.20\"}', '2025-12-04 20:14:40'),
(76, 1, 'login', '2401:4900:8fc2:dcf8:a915:aa97:8e0a:d618', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.148 Mobile/15E148 Safari/604.1', '{\"ip\":\"2401:4900:8fc2:dcf8:a915:aa97:8e0a:d618\"}', '2025-12-04 20:17:12'),
(77, 1, 'logout', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-04 20:23:26'),
(78, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.20\"}', '2025-12-04 20:23:52'),
(79, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '{\"ip\":\"106.215.140.20\"}', '2025-12-04 20:25:05'),
(80, 1, 'logout', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-04 20:50:53'),
(81, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.20\"}', '2025-12-04 20:51:59'),
(82, 1, 'login', '2401:4900:8fc2:dcf8:a915:aa97:8e0a:d618', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '{\"ip\":\"2401:4900:8fc2:dcf8:a915:aa97:8e0a:d618\"}', '2025-12-04 20:58:30'),
(83, 1, 'login', '2401:4900:8fc2:dcf8:a915:aa97:8e0a:d618', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '{\"ip\":\"2401:4900:8fc2:dcf8:a915:aa97:8e0a:d618\"}', '2025-12-04 20:58:35'),
(84, 1, 'logout', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-04 22:31:15'),
(85, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.20\"}', '2025-12-04 22:31:36'),
(86, 1, 'logout', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-04 23:10:24'),
(87, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.20\"}', '2025-12-04 23:15:36'),
(88, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '{\"ip\":\"106.215.140.20\"}', '2025-12-04 23:16:05'),
(89, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.148 Mobile/15E148 Safari/604.1', '{\"ip\":\"106.215.140.20\"}', '2025-12-04 23:28:03'),
(90, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.20\"}', '2025-12-04 23:40:46'),
(91, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.20\"}', '2025-12-05 00:57:03'),
(92, 1, 'login', '2401:4900:8fc2:dcf8:6557:7a8:3913:2819', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.148 Mobile/15E148 Safari/604.1', '{\"ip\":\"2401:4900:8fc2:dcf8:6557:7a8:3913:2819\"}', '2025-12-05 00:57:34'),
(93, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '{\"ip\":\"106.215.140.20\"}', '2025-12-05 00:59:12'),
(94, 1, 'login', '106.215.140.20', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '{\"ip\":\"106.215.140.20\"}', '2025-12-05 00:59:18'),
(95, 1, 'login', '106.215.143.179', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.143.179\"}', '2025-12-05 13:36:10'),
(96, 1, 'logout', '106.215.143.179', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-05 14:52:03'),
(97, 1, 'login', '106.215.143.179', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.143.179\"}', '2025-12-05 14:52:19'),
(98, 1, 'logout', '106.215.143.179', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-05 14:59:24'),
(99, 1, 'login', '106.215.143.179', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.143.179\"}', '2025-12-05 15:03:41'),
(100, 1, 'logout', '106.215.143.179', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-05 15:32:51'),
(101, 1, 'login', '106.215.143.179', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.143.179\"}', '2025-12-05 15:33:10'),
(102, 1, 'logout', '106.215.143.179', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-05 15:55:19'),
(103, 1, 'login', '106.215.143.179', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.143.179\"}', '2025-12-05 15:55:57'),
(104, 1, 'login', '2401:4900:8fc2:dcf8:7df9:f311:7075:ee77', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '{\"ip\":\"2401:4900:8fc2:dcf8:7df9:f311:7075:ee77\"}', '2025-12-05 15:57:57'),
(105, 1, 'login', '2401:4900:8fc2:dcf8:7df9:f311:7075:ee77', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '{\"ip\":\"2401:4900:8fc2:dcf8:7df9:f311:7075:ee77\"}', '2025-12-05 15:58:02'),
(106, 1, 'logout', '106.215.143.179', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-05 16:07:55'),
(107, 1, 'login', '106.215.143.179', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.143.179\"}', '2025-12-05 16:08:06'),
(108, 1, 'logout', '106.215.143.179', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-05 16:51:26'),
(109, 1, 'login', '106.215.143.179', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.143.179\"}', '2025-12-05 16:51:38'),
(110, 1, 'logout', '106.215.143.179', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-05 17:12:07'),
(111, 1, 'login', '106.215.143.179', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.143.179\"}', '2025-12-05 17:12:28'),
(112, 1, 'logout', '106.215.143.179', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-05 19:07:31'),
(113, 1, 'login', '106.215.143.179', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.143.179\"}', '2025-12-05 19:07:58'),
(114, 1, 'logout', '106.215.140.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-05 22:08:59'),
(115, 3, 'login', '106.215.140.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.193\"}', '2025-12-05 22:09:17'),
(116, 3, 'logout', '106.215.140.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-05 22:11:33'),
(117, 3, 'login', '106.215.140.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.193\"}', '2025-12-05 22:12:20'),
(118, 3, 'logout', '106.215.140.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-05 22:50:50'),
(119, 3, 'login', '106.215.140.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.193\"}', '2025-12-05 22:51:02'),
(120, 3, 'logout', '106.215.140.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-05 23:12:36'),
(121, 3, 'login', '106.215.140.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.193\"}', '2025-12-05 23:12:53'),
(122, 3, 'user_updated', '106.215.140.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"user_id\":1}', '2025-12-05 23:16:07'),
(123, 3, 'logout', '106.215.140.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-05 23:16:16'),
(124, 1, 'login', '106.215.140.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.193\"}', '2025-12-05 23:16:27'),
(125, 1, 'logout', '106.215.140.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-05 23:16:52'),
(126, 3, 'login', '106.215.140.193', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.140.193\"}', '2025-12-05 23:17:03'),
(127, 1, 'login', '2401:4900:3dc1:b633:4846:d6ee:365:ad42', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.92 Mobile/15E148 Safari/604.1', '{\"ip\":\"2401:4900:3dc1:b633:4846:d6ee:365:ad42\"}', '2025-12-05 23:18:57'),
(128, 1, 'login', '2401:4900:3dc1:b633:4846:d6ee:365:ad42', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.92 Mobile/15E148 Safari/604.1', '{\"ip\":\"2401:4900:3dc1:b633:4846:d6ee:365:ad42\"}', '2025-12-05 23:20:56'),
(129, 3, 'login', '2401:4900:8fc2:3377:f19a:3aa4:f091:a416', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:3377:f19a:3aa4:f091:a416\"}', '2025-12-06 11:47:29'),
(130, 3, 'login', '2401:4900:8fc2:3377:f19a:3aa4:f091:a416', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:3377:f19a:3aa4:f091:a416\"}', '2025-12-06 11:53:52'),
(131, 3, 'logout', '2401:4900:8fc2:3377:f19a:3aa4:f091:a416', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-06 12:25:07'),
(132, 3, 'login', '2401:4900:8fc2:3377:f19a:3aa4:f091:a416', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:3377:f19a:3aa4:f091:a416\"}', '2025-12-06 12:26:01'),
(133, 3, 'login', '2401:4900:8fc2:3377:f19a:3aa4:f091:a416', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:3377:f19a:3aa4:f091:a416\"}', '2025-12-06 12:26:03'),
(134, 3, 'login', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc1:6d3e:c1be:6618:3997:6be4\"}', '2025-12-07 20:27:51'),
(135, 3, 'logout', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-07 20:31:56'),
(136, 3, 'login', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc1:6d3e:c1be:6618:3997:6be4\"}', '2025-12-07 21:21:21'),
(137, 3, 'logout', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-07 21:53:15'),
(138, 3, 'login', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc1:6d3e:c1be:6618:3997:6be4\"}', '2025-12-07 21:53:42'),
(139, 3, 'logout', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-07 21:54:45'),
(140, 3, 'login', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc1:6d3e:c1be:6618:3997:6be4\"}', '2025-12-07 22:31:15'),
(141, 3, 'hero_section_updated', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-07 22:32:04'),
(142, 3, 'project_updated', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"project_id\":\"1\"}', '2025-12-07 22:32:51'),
(143, 3, 'project_updated', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"project_id\":\"6\"}', '2025-12-07 22:35:02'),
(144, 3, 'project_updated', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"project_id\":\"6\"}', '2025-12-07 22:35:11'),
(145, 3, 'navbar_settings_updated', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-07 22:59:08'),
(146, 3, 'logout', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-07 23:01:35'),
(147, 3, 'login', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc1:6d3e:c1be:6618:3997:6be4\"}', '2025-12-07 23:01:44'),
(148, 3, 'project_updated', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"project_id\":\"1\"}', '2025-12-07 23:06:28'),
(149, 3, 'navbar_settings_updated', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-07 23:13:56'),
(150, 3, 'project_updated', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"project_id\":\"1\"}', '2025-12-07 23:32:05'),
(151, 3, 'login', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc1:6d3e:c1be:6618:3997:6be4\"}', '2025-12-08 00:27:38'),
(152, 1, 'login', '106.215.137.219', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.92 Mobile/15E148 Safari/604.1', '{\"ip\":\"106.215.137.219\"}', '2025-12-08 00:29:32'),
(153, 3, 'logout', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-08 01:55:09'),
(154, 3, 'login', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc1:6d3e:c1be:6618:3997:6be4\"}', '2025-12-08 01:55:30'),
(155, 3, 'login', '2401:4900:8fc1:6d3e:ed48:7df4:2229:516d', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.92 Mobile/15E148 Safari/604.1', '{\"ip\":\"2401:4900:8fc1:6d3e:ed48:7df4:2229:516d\"}', '2025-12-08 02:00:56'),
(156, 3, 'logout', '106.215.137.219', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-08 02:04:15'),
(157, 3, 'login', '106.215.137.219', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"106.215.137.219\"}', '2025-12-08 02:04:40'),
(158, 3, 'logout', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-08 11:36:00'),
(159, 3, 'login', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc1:6d3e:c1be:6618:3997:6be4\"}', '2025-12-08 11:36:17'),
(160, 3, 'qr_generated', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"type\":\"url\"}', '2025-12-08 11:37:13'),
(161, 3, 'qr_generated', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"type\":\"phone\"}', '2025-12-08 11:37:36'),
(162, 3, 'profile_updated', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-08 11:55:26'),
(163, 3, 'project_updated', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"project_id\":\"6\"}', '2025-12-08 12:13:18'),
(164, 3, 'project_updated', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"project_id\":\"6\"}', '2025-12-08 12:14:12'),
(165, 3, 'project_updated', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"project_id\":\"6\"}', '2025-12-08 12:14:36'),
(166, 3, 'project_updated', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"project_id\":\"1\"}', '2025-12-08 12:14:56'),
(167, 3, 'project_updated', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"project_id\":\"1\"}', '2025-12-08 12:15:26'),
(168, 3, 'project_updated', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"project_id\":\"6\"}', '2025-12-08 12:16:05'),
(169, 3, 'hero_section_updated', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-08 12:17:22'),
(170, 4, 'register', '2401:4900:8fc1:6d3e:a0a6:3432:687c:6474', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.92 Mobile/15E148 Safari/604.1', '{\"email\":\"testuser@gmail.com\"}', '2025-12-08 13:02:42'),
(171, 4, 'login', '2401:4900:8fc1:6d3e:a0a6:3432:687c:6474', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.92 Mobile/15E148 Safari/604.1', '{\"ip\":\"2401:4900:8fc1:6d3e:a0a6:3432:687c:6474\"}', '2025-12-08 13:02:54'),
(172, 3, 'logout', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-08 13:05:25'),
(173, 3, 'login', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc1:6d3e:c1be:6618:3997:6be4\"}', '2025-12-08 13:05:48'),
(174, 3, 'logout', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-08 13:34:16'),
(175, 3, 'login', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc1:6d3e:c1be:6618:3997:6be4\"}', '2025-12-08 13:34:47'),
(176, 3, 'logout', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-08 14:32:27'),
(177, 3, 'login', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc1:6d3e:c1be:6618:3997:6be4\"}', '2025-12-08 14:32:47'),
(178, 3, 'logout', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-08 15:13:22'),
(179, 3, 'login', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc1:6d3e:c1be:6618:3997:6be4\"}', '2025-12-08 15:13:48'),
(180, 3, 'logout', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '[]', '2025-12-08 19:35:28'),
(181, 3, 'login', '2401:4900:8fc1:6d3e:c1be:6618:3997:6be4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc1:6d3e:c1be:6618:3997:6be4\"}', '2025-12-08 19:54:43'),
(182, 3, 'login', '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e\"}', '2025-12-10 18:39:47'),
(183, 3, 'login', '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80\"}', '2025-12-13 21:29:23'),
(184, 3, 'projects_section_updated', '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-13 21:30:31'),
(185, 3, 'imgtxt_settings_updated', '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"max_file_size\":20,\"batch_size\":5,\"ocr_engine\":\"tesseract_legacy\",\"default_language\":\"eng\",\"batch_processing_enabled\":1,\"multi_language_enabled\":1}', '2025-12-13 21:57:30'),
(186, 3, 'imgtxt_settings_updated', '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"max_file_size\":20,\"batch_size\":5,\"ocr_engine\":\"tesseract\",\"default_language\":\"eng\",\"batch_processing_enabled\":1,\"multi_language_enabled\":1}', '2025-12-13 21:57:40'),
(187, 3, 'profile_updated', '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-13 22:08:43'),
(188, 3, 'profile_updated', '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-13 22:08:52'),
(189, 3, 'profile_updated', '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '[]', '2025-12-13 22:09:03'),
(190, 3, 'login', '2401:4900:8fc1:2c2:1014:8b40:5410:1e68', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc1:2c2:1014:8b40:5410:1e68\"}', '2025-12-14 22:17:07'),
(191, 3, 'login', '2401:4900:8fc2:2323:d969:a72:ae9d:747c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:2323:d969:a72:ae9d:747c\"}', '2025-12-27 01:42:01'),
(192, 3, 'qr_generated', '2401:4900:8fc2:2323:d969:a72:ae9d:747c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"type\":\"url\"}', '2025-12-27 01:42:30'),
(193, 3, 'login', '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc0:5367:4932:b3d9:6795:4ae0\"}', '2025-12-28 00:12:00'),
(194, 3, 'login', '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc0:5367:4932:b3d9:6795:4ae0\"}', '2025-12-29 04:03:12'),
(195, 3, 'hero_section_updated', '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2025-12-29 04:03:41'),
(196, 3, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf\"}', '2026-01-01 05:02:11'),
(197, 3, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-01 05:07:16'),
(198, 3, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf\"}', '2026-01-01 05:07:35'),
(199, 3, 'login', '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', '{\"ip\":\"2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea\"}', '2026-01-01 05:36:33'),
(200, 3, 'login', '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', '{\"ip\":\"2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea\"}', '2026-01-01 05:42:02'),
(201, 3, 'login', '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', '{\"ip\":\"2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea\"}', '2026-01-01 05:42:35'),
(202, 3, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-01 07:24:28'),
(203, 3, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf\"}', '2026-01-01 07:26:28'),
(204, 3, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf\"}', '2026-01-01 08:06:08'),
(205, 3, 'maintenance_mode_toggled', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"enabled\":true}', '2026-01-01 08:44:48'),
(206, 3, 'maintenance_mode_toggled', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"enabled\":false}', '2026-01-01 08:45:04'),
(207, 3, 'maintenance_mode_toggled', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"enabled\":true}', '2026-01-01 08:45:08'),
(208, 3, 'logout', '2401:4900:8fc2:d46d:10ff:99bd:1db8:c498', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', '[]', '2026-01-01 08:45:29'),
(209, 3, 'maintenance_mode_toggled', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"enabled\":false}', '2026-01-01 08:45:39'),
(210, 3, 'maintenance_mode_toggled', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"enabled\":true}', '2026-01-01 09:01:11'),
(211, 3, 'maintenance_mode_toggled', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"enabled\":false}', '2026-01-01 09:08:53'),
(212, 3, 'maintenance_mode_toggled', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '{\"enabled\":true}', '2026-01-01 09:18:00'),
(213, 3, 'maintenance_mode_toggled', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"enabled\":false}', '2026-01-01 09:30:19'),
(214, 3, 'maintenance_settings_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-01 09:31:09'),
(215, 3, 'maintenance_mode_toggled', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"enabled\":true}', '2026-01-01 09:31:17'),
(216, 3, 'maintenance_settings_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-01 09:32:11'),
(217, 3, 'maintenance_settings_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-01 09:35:19'),
(218, 3, 'maintenance_settings_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-01 09:36:58'),
(219, 3, 'maintenance_settings_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-01 09:37:12'),
(220, 3, 'maintenance_settings_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-01 09:37:23'),
(221, 3, 'maintenance_settings_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-01 09:55:28'),
(222, 3, 'maintenance_mode_toggled', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"enabled\":false}', '2026-01-01 09:59:11'),
(223, 3, 'maintenance_mode_toggled', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"enabled\":true}', '2026-01-01 09:59:12'),
(224, 3, 'maintenance_mode_toggled', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"enabled\":false}', '2026-01-01 09:59:13'),
(225, 3, 'navbar_settings_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-01 11:42:16'),
(226, 3, 'navbar_settings_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-01 11:42:28'),
(227, 3, 'hero_section_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-01 11:55:29');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `ip_address`, `user_agent`, `data`, `created_at`) VALUES
(228, 3, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf\"}', '2026-01-01 16:37:18'),
(229, 3, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-02 00:05:20'),
(230, 3, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf\"}', '2026-01-02 00:06:12'),
(231, 3, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-02 03:09:05'),
(232, 3, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf\"}', '2026-01-02 03:09:57'),
(233, 3, 'navbar_settings_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"message\":\"Updated navbar settings\"}', '2026-01-02 03:21:46'),
(234, 3, 'navbar_settings_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"message\":\"Updated navbar settings\"}', '2026-01-02 03:22:03'),
(235, 3, 'navbar_settings_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"message\":\"Updated navbar settings\"}', '2026-01-02 03:31:57'),
(236, 3, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-02 03:32:17'),
(237, 3, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf\"}', '2026-01-02 03:32:50'),
(238, 3, 'navbar_settings_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"message\":\"Updated navbar settings\"}', '2026-01-02 03:55:23'),
(239, 3, 'navbar_settings_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"message\":\"Updated navbar settings\"}', '2026-01-02 03:55:52'),
(240, 3, 'navbar_settings_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"message\":\"Updated navbar settings\"}', '2026-01-02 03:56:41'),
(241, 3, 'navbar_settings_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"message\":\"Updated navbar settings\"}', '2026-01-02 04:04:54'),
(242, 3, 'navbar_settings_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"message\":\"Updated navbar settings\"}', '2026-01-02 04:14:33'),
(243, 3, 'hero_section_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-02 05:06:34'),
(244, 3, 'hero_section_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-02 05:06:57'),
(245, 3, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-02 05:16:17'),
(246, 3, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf\"}', '2026-01-02 05:29:23'),
(247, 3, 'project_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"project_id\":\"6\"}', '2026-01-02 05:29:38'),
(248, 3, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-02 07:47:33'),
(249, 3, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf\"}', '2026-01-02 07:47:48'),
(250, 3, 'project_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"project_id\":\"6\"}', '2026-01-02 07:49:31'),
(251, 3, 'navbar_settings_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"message\":\"Updated navbar settings\"}', '2026-01-02 08:00:11'),
(252, 3, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-02 08:06:16'),
(253, 3, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf\"}', '2026-01-02 08:59:31'),
(254, 3, 'hero_section_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-02 09:01:22'),
(255, 3, 'project_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"project_id\":\"6\"}', '2026-01-02 09:05:57'),
(256, 3, 'hero_section_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-02 09:16:47'),
(257, 3, 'hero_section_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-02 09:36:25'),
(258, 3, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-02 09:50:10'),
(259, 3, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf\"}', '2026-01-02 09:50:34'),
(260, 3, 'hero_section_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-02 09:55:00'),
(261, 3, 'navbar_settings_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"message\":\"Updated navbar settings\"}', '2026-01-02 10:28:13'),
(262, 3, 'navbar_settings_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"message\":\"Updated navbar settings\"}', '2026-01-02 10:28:42'),
(263, 3, 'navbar_settings_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"message\":\"Updated navbar settings\"}', '2026-01-02 11:25:06'),
(264, 3, 'navbar_settings_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"message\":\"Updated navbar settings\"}', '2026-01-02 11:36:39'),
(265, 3, 'navbar_settings_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"message\":\"Updated navbar settings\"}', '2026-01-02 11:37:10'),
(266, 3, 'maintenance_mode_toggled', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"enabled\":true}', '2026-01-02 11:39:37'),
(267, 3, 'maintenance_settings_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-02 11:40:00'),
(268, 3, 'maintenance_mode_toggled', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"enabled\":false}', '2026-01-02 11:40:42'),
(269, 3, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-02 12:47:55'),
(270, 3, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf\"}', '2026-01-02 12:49:26'),
(271, 3, 'profile_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-02 13:33:50'),
(272, 3, 'project_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"project_id\":\"2\"}', '2026-01-02 14:15:46'),
(273, 3, 'project_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"project_id\":\"1\"}', '2026-01-02 14:15:55'),
(274, 3, 'project_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"project_id\":\"1\"}', '2026-01-02 14:16:19'),
(275, 3, 'project_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"project_id\":\"1\"}', '2026-01-02 14:16:51'),
(276, 3, 'project_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"project_id\":\"3\"}', '2026-01-02 14:17:08'),
(277, 4, 'login', '2401:4900:8fc2:d46d:fdaf:5439:7931:256b', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', '{\"ip\":\"2401:4900:8fc2:d46d:fdaf:5439:7931:256b\"}', '2026-01-02 15:07:41'),
(278, 4, 'login', '2401:4900:8fc2:d46d:fdaf:5439:7931:256b', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', '{\"method\":\"email_password\",\"remember\":false}', '2026-01-02 15:07:42'),
(279, 3, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf\"}', '2026-01-03 02:05:48'),
(280, 3, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"email_password\",\"remember\":false}', '2026-01-03 02:05:48'),
(281, 3, 'profile_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 02:17:54'),
(282, 3, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 04:44:43'),
(283, 3, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 04:44:43'),
(284, 3, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf\"}', '2026-01-03 04:45:00'),
(285, 3, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"email_password\",\"remember\":false}', '2026-01-03 04:45:01'),
(286, 3, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 05:17:31'),
(287, 3, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 05:17:31'),
(288, 3, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"logout\"}', '2026-01-03 05:17:31'),
(289, 3, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf\"}', '2026-01-03 05:18:52'),
(290, 3, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"email_password\",\"remember\":false}', '2026-01-03 05:18:53'),
(291, 3, 'oauth_provider_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"provider_id\":1}', '2026-01-03 05:47:12'),
(292, 3, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 05:47:35'),
(293, 3, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 05:47:35'),
(294, 3, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"logout\"}', '2026-01-03 05:47:35'),
(295, 3, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf\"}', '2026-01-03 05:49:20'),
(296, 3, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"email_password\",\"remember\":false}', '2026-01-03 05:49:20'),
(297, NULL, 'registration', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"google_oauth\",\"email\":\"farukahmed8565@gmail.com\"}', '2026-01-03 05:57:44'),
(298, NULL, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"google_oauth\",\"email\":\"farukahmed8565@gmail.com\"}', '2026-01-03 05:57:44'),
(299, NULL, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 05:57:50'),
(300, NULL, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 05:57:50'),
(301, NULL, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"logout\"}', '2026-01-03 05:57:50'),
(302, NULL, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"google_oauth\",\"email\":\"farukahmed8565@gmail.com\"}', '2026-01-03 05:58:02'),
(303, NULL, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 05:58:42'),
(304, NULL, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 05:58:42'),
(305, NULL, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"logout\"}', '2026-01-03 05:58:42'),
(306, 3, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf\"}', '2026-01-03 05:59:02'),
(307, 3, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"email_password\",\"remember\":false}', '2026-01-03 05:59:02'),
(308, 3, 'session_revoked', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"session_id\":2}', '2026-01-03 06:24:22'),
(309, 3, 'session_settings_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 07:39:34'),
(310, 3, 'session_settings_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 07:39:44'),
(311, 3, '2fa_enabled', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 08:36:13'),
(312, 3, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 08:38:42'),
(313, 3, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 08:38:42'),
(314, 3, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"logout\"}', '2026-01-03 08:38:42'),
(315, 3, '2fa_verification_failed', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 08:39:24'),
(316, 3, '2fa_verification_failed', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 08:39:34'),
(317, 3, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf\"}', '2026-01-03 08:39:39'),
(318, 3, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"2fa\",\"remember\":false}', '2026-01-03 08:39:40'),
(319, 3, '2fa_disabled', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 08:40:28'),
(320, NULL, 'login', '2401:4900:8fc2:d46d:5c2:1895:1c86:997', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', '{\"method\":\"google_oauth\",\"email\":\"farukahmed8565@gmail.com\"}', '2026-01-03 08:43:45'),
(321, NULL, 'google_oauth_revoked', '2401:4900:8fc2:d46d:5c2:1895:1c86:997', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', '[]', '2026-01-03 08:45:59'),
(322, NULL, 'profile_updated', '2401:4900:8fc2:d46d:5c2:1895:1c86:997', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', '[]', '2026-01-03 08:47:12'),
(323, 3, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 09:07:38'),
(324, 3, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 09:07:38'),
(325, 3, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"logout\"}', '2026-01-03 09:07:38'),
(326, NULL, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"google_oauth\",\"email\":\"farukahmed8565@gmail.com\"}', '2026-01-03 09:07:56'),
(327, NULL, 'google_oauth_revoked', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 09:08:08'),
(328, NULL, 'session_revoked', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"session_id\":7}', '2026-01-03 09:08:29'),
(329, NULL, 'theme_changed', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"theme\":\"dark\"}', '2026-01-03 09:10:10'),
(330, NULL, 'notification_preferences_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 09:10:36'),
(331, NULL, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"google_oauth\",\"email\":\"farukahmed8565@gmail.com\"}', '2026-01-03 09:31:47'),
(332, NULL, 'google_oauth_revoked', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 09:32:37'),
(336, NULL, 'registration', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"google_oauth\",\"email\":\"farukahmed8565@gmail.com\"}', '2026-01-03 09:35:31'),
(337, NULL, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"google_oauth\",\"email\":\"farukahmed8565@gmail.com\"}', '2026-01-03 09:35:31'),
(338, NULL, 'google_oauth_revoked', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 09:35:59'),
(339, NULL, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"google_oauth\",\"email\":\"farukahmed8565@gmail.com\"}', '2026-01-03 09:40:18'),
(340, NULL, 'session_revoked', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"session_id\":10}', '2026-01-03 09:43:00'),
(341, NULL, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 09:50:02'),
(342, NULL, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 09:50:02'),
(343, NULL, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"logout\"}', '2026-01-03 09:50:02'),
(344, 7, 'registration', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"google_oauth\",\"email\":\"farukahmed8565@gmail.com\"}', '2026-01-03 09:50:38'),
(345, 7, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"google_oauth\",\"email\":\"farukahmed8565@gmail.com\"}', '2026-01-03 09:50:38'),
(346, 7, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 09:55:29'),
(347, 7, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 09:55:29'),
(348, 7, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"logout\"}', '2026-01-03 09:55:29'),
(349, 7, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"google_oauth\",\"email\":\"farukahmed8565@gmail.com\"}', '2026-01-03 10:01:51'),
(350, 7, 'password_set', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"oauth_to_standard\"}', '2026-01-03 10:10:36'),
(351, 7, 'project_settings_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"settings\":\"{\\\"default_view\\\":\\\"grid\\\",\\\"auto_save\\\":0}\"}', '2026-01-03 10:11:02'),
(352, 7, 'project_settings_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"settings\":\"{\\\"default_view\\\":\\\"grid\\\",\\\"auto_save\\\":0}\"}', '2026-01-03 10:11:09'),
(353, 7, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 10:11:21'),
(354, 7, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 10:11:21'),
(355, 7, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"logout\"}', '2026-01-03 10:11:22'),
(356, 7, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"google_oauth\",\"email\":\"farukahmed8565@gmail.com\"}', '2026-01-03 10:11:53'),
(357, 7, 'project_settings_updated', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"settings\":\"{\\\"default_view\\\":\\\"grid\\\",\\\"auto_save\\\":0}\"}', '2026-01-03 10:16:55'),
(358, 7, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 11:15:29'),
(359, 7, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 11:15:29'),
(360, 7, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"logout\"}', '2026-01-03 11:15:30'),
(361, 7, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"google_oauth\",\"email\":\"farukahmed8565@gmail.com\"}', '2026-01-03 11:27:01'),
(362, 7, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 12:09:49'),
(363, 7, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 12:09:49'),
(364, 7, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"logout\"}', '2026-01-03 12:09:49'),
(365, 3, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf\"}', '2026-01-03 12:10:11'),
(366, 3, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"email_password\",\"remember\":false}', '2026-01-03 12:10:11'),
(367, 7, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"google_oauth\",\"email\":\"farukahmed8565@gmail.com\"}', '2026-01-03 12:15:03'),
(368, 7, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 12:15:17'),
(369, 7, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-03 12:15:17'),
(370, 7, 'logout', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"logout\"}', '2026-01-03 12:15:17'),
(371, 3, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf\"}', '2026-01-03 12:16:50'),
(372, 3, 'login', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"email_password\",\"remember\":false}', '2026-01-03 12:16:50'),
(373, 7, 'login', '2401:4900:8fc1:920a:29ea:20a:136a:cfe5', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', '{\"method\":\"google_oauth\",\"email\":\"farukahmed8565@gmail.com\"}', '2026-01-04 01:36:19'),
(374, 7, 'logout', '2401:4900:8fc1:920a:29ea:20a:136a:cfe5', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', '[]', '2026-01-04 01:36:24'),
(375, 7, 'logout', '2401:4900:8fc1:920a:29ea:20a:136a:cfe5', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', '[]', '2026-01-04 01:36:24'),
(376, 7, 'logout', '2401:4900:8fc1:920a:29ea:20a:136a:cfe5', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', '{\"reason\":\"logout\"}', '2026-01-04 01:36:24'),
(377, 3, 'login', '106.215.138.141', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', '{\"ip\":\"106.215.138.141\"}', '2026-01-04 01:37:00'),
(378, 3, 'login', '106.215.138.141', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', '{\"method\":\"email_password\",\"remember\":false}', '2026-01-04 01:37:00'),
(379, 3, 'login', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7\"}', '2026-01-04 01:44:03'),
(380, 3, 'login', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"email_password\",\"remember\":false}', '2026-01-04 01:44:04'),
(381, 3, 'logout', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-04 02:14:40'),
(382, 3, 'logout', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-04 02:14:40'),
(383, 3, 'logout', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"logout\"}', '2026-01-04 02:14:40'),
(384, 3, 'login', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7\"}', '2026-01-04 02:15:08'),
(385, 3, 'login', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"email_password\",\"remember\":false}', '2026-01-04 02:15:08'),
(386, 3, 'logout', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-04 02:26:24'),
(387, 3, 'logout', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-04 02:26:24'),
(388, 3, 'logout', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"logout\"}', '2026-01-04 02:26:24'),
(389, 3, 'login', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7\"}', '2026-01-04 02:27:07'),
(390, 3, 'login', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"email_password\",\"remember\":false}', '2026-01-04 02:27:07'),
(391, 3, 'logout', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-04 03:26:14'),
(392, 3, 'logout', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-04 03:26:14'),
(393, 3, 'logout', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"logout\"}', '2026-01-04 03:26:14'),
(394, 3, 'login', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7\"}', '2026-01-04 03:27:31'),
(395, 3, 'login', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"email_password\",\"remember\":false}', '2026-01-04 03:27:31'),
(396, 3, 'logout', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-04 06:11:15'),
(397, 3, 'logout', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-04 06:11:15'),
(398, 3, 'logout', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"logout\"}', '2026-01-04 06:11:15'),
(399, 3, 'login', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7\"}', '2026-01-04 06:11:38'),
(400, 3, 'login', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"email_password\",\"remember\":false}', '2026-01-04 06:11:39'),
(401, 3, 'logout', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-04 06:53:46'),
(402, 3, 'logout', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-04 06:53:46'),
(403, 3, 'logout', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"logout\"}', '2026-01-04 06:53:46'),
(404, 3, 'login', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7\"}', '2026-01-04 07:59:41'),
(405, 3, 'login', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"email_password\",\"remember\":false}', '2026-01-04 07:59:42'),
(406, 3, 'project_toggled', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"project\":\"devzone\"}', '2026-01-04 08:15:14'),
(407, 3, 'login', '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:f585:d4fa:18af:a145:5362\"}', '2026-01-06 01:55:59'),
(408, 3, 'login', '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"email_password\",\"remember\":false}', '2026-01-06 01:55:59'),
(409, 3, 'project_toggled', '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"project\":\"codexpro\"}', '2026-01-06 02:08:21'),
(410, 3, 'project_toggled', '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"project\":\"codexpro\"}', '2026-01-06 02:08:25'),
(411, 7, 'login', '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"google_oauth\",\"email\":\"farukahmed8565@gmail.com\"}', '2026-01-06 13:12:08'),
(412, 7, 'logout', '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-06 13:12:13'),
(413, 7, 'logout', '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-06 13:12:13'),
(414, 7, 'logout', '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"logout\"}', '2026-01-06 13:12:13'),
(415, 3, 'login', '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:f585:d4fa:18af:a145:5362\"}', '2026-01-06 13:13:18'),
(416, 3, 'login', '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"email_password\",\"remember\":false}', '2026-01-06 13:13:19'),
(417, 3, 'user_status_changed', '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"user_id\":7,\"status\":\"inactive\"}', '2026-01-06 13:14:52'),
(418, 3, 'logout', '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-06 13:15:10'),
(419, 3, 'logout', '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-06 13:15:10'),
(420, 3, 'logout', '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"logout\"}', '2026-01-06 13:15:10'),
(421, 3, 'login', '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:f585:d4fa:18af:a145:5362\"}', '2026-01-06 13:16:02'),
(422, 3, 'login', '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"email_password\",\"remember\":false}', '2026-01-06 13:16:03'),
(423, 3, 'user_status_changed', '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"user_id\":7,\"status\":\"active\"}', '2026-01-06 13:16:14'),
(424, 3, 'logout', '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-06 15:09:20'),
(425, 3, 'logout', '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-06 15:09:20'),
(426, 3, 'logout', '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"logout\"}', '2026-01-06 15:09:20'),
(427, 3, 'login', '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:f585:d4fa:18af:a145:5362\"}', '2026-01-06 15:10:14'),
(428, 3, 'login', '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"email_password\",\"remember\":false}', '2026-01-06 15:10:14'),
(429, 3, 'login', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8\"}', '2026-01-07 01:33:43'),
(430, 3, 'login', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"email_password\",\"remember\":false}', '2026-01-07 01:33:43'),
(431, 3, 'logout', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-07 02:58:15'),
(432, 3, 'logout', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-07 02:58:15'),
(433, 3, 'logout', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"logout\"}', '2026-01-07 02:58:15'),
(434, 3, 'login', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8\"}', '2026-01-07 02:58:24'),
(435, 3, 'login', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"email_password\",\"remember\":false}', '2026-01-07 02:58:24'),
(436, 3, 'logout', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-07 03:36:26'),
(437, 3, 'logout', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-07 03:36:26'),
(438, 3, 'logout', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"logout\"}', '2026-01-07 03:36:26'),
(439, 3, 'login', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8\"}', '2026-01-07 03:37:34'),
(440, 3, 'login', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"email_password\",\"remember\":false}', '2026-01-07 03:37:34');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `ip_address`, `user_agent`, `data`, `created_at`) VALUES
(441, 3, 'logout', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-07 04:07:10'),
(442, 3, 'logout', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-07 04:07:10'),
(443, 3, 'logout', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"logout\"}', '2026-01-07 04:07:11'),
(444, 3, 'login', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8\"}', '2026-01-07 04:07:30'),
(445, 3, 'login', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"email_password\",\"remember\":false}', '2026-01-07 04:07:30'),
(446, 3, 'logout', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-07 05:22:15'),
(447, 3, 'logout', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-07 05:22:15'),
(448, 3, 'logout', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"logout\"}', '2026-01-07 05:22:15'),
(449, 3, 'login', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8\"}', '2026-01-07 05:22:29'),
(450, 3, 'login', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"email_password\",\"remember\":false}', '2026-01-07 05:22:29'),
(451, 3, 'logout', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-07 05:44:39'),
(452, 3, 'logout', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', '2026-01-07 05:44:39'),
(453, 3, 'logout', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"logout\"}', '2026-01-07 05:44:39'),
(454, 3, 'login', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"ip\":\"2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8\"}', '2026-01-07 05:45:03'),
(455, 3, 'login', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"method\":\"email_password\",\"remember\":false}', '2026-01-07 05:45:03');

-- --------------------------------------------------------

--
-- Table structure for table `analytics_events`
--

CREATE TABLE `analytics_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project` varchar(50) NOT NULL COMMENT 'proshare, codexpro, imgtxt',
  `resource_type` varchar(50) NOT NULL COMMENT 'file, code, image',
  `resource_id` int(10) UNSIGNED NOT NULL,
  `event_type` varchar(50) NOT NULL COMMENT 'view, download, share, edit',
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `browser` varchar(50) DEFAULT NULL,
  `platform` varchar(50) DEFAULT NULL,
  `country` varchar(2) DEFAULT NULL COMMENT 'ISO country code',
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `analytics_events`
--

INSERT INTO `analytics_events` (`id`, `project`, `resource_type`, `resource_id`, `event_type`, `user_id`, `ip_address`, `user_agent`, `browser`, `platform`, `country`, `metadata`, `created_at`) VALUES
(1, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:17:09\"}', '2025-12-10 19:17:09'),
(2, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:17:12\"}', '2025-12-10 19:17:12'),
(3, 'platform', 'page', 0, 'page_visit', NULL, '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'Chrome', 'Windows', 'US', '{\"page\": \"/\", \"url\": \"/\"}', '2025-12-10 19:14:30'),
(4, 'platform', 'page', 0, 'page_visit', 1, '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'Safari', 'macOS', 'US', '{\"page\": \"/dashboard\", \"url\": \"/dashboard\"}', '2025-12-10 19:16:30'),
(5, 'platform', 'page', 0, 'page_visit', 2, '192.168.1.102', 'Mozilla/5.0 (X11; Linux x86_64)', 'Firefox', 'Linux', 'GB', '{\"page\": \"/projects\", \"url\": \"/projects\"}', '2025-12-10 19:17:30'),
(6, 'platform', 'page', 0, 'page_visit', NULL, '192.168.1.103', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X)', 'Safari', 'iOS', 'CA', '{\"page\": \"/login\", \"url\": \"/login\"}', '2025-12-10 19:18:30'),
(7, 'platform', 'page', 0, 'page_visit', 3, '192.168.1.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:95.0)', 'Edge', 'Windows', 'US', '{\"page\": \"/admin\", \"url\": \"/admin\"}', '2025-12-10 19:19:30'),
(8, 'platform', 'auth', 1, 'user_login', 1, '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'Safari', 'macOS', 'US', '{\"timestamp\": \"2025-12-10 19:09:30\"}', '2025-12-10 19:09:30'),
(9, 'platform', 'auth', 2, 'user_login', 2, '192.168.1.102', 'Mozilla/5.0 (X11; Linux x86_64)', 'Firefox', 'Linux', 'GB', '{\"timestamp\": \"2025-12-10 19:11:30\"}', '2025-12-10 19:11:30'),
(10, 'platform', 'auth', 3, 'user_login', 3, '192.168.1.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:95.0)', 'Edge', 'Windows', 'US', '{\"timestamp\": \"2025-12-10 19:14:30\"}', '2025-12-10 19:14:30'),
(11, 'platform', 'auth', 4, 'user_register', 4, '192.168.1.105', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'Chrome', 'Windows', 'US', '{\"timestamp\": \"2025-12-10 18:49:30\"}', '2025-12-10 18:49:30'),
(12, 'platform', 'user', 1, 'return_visit', 1, '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'Safari', 'macOS', 'US', '{\"days_since_last_visit\": 2, \"timestamp\": \"2025-12-10 18:59:30\"}', '2025-12-10 18:59:30'),
(13, 'platform', 'page', 0, 'page_visit', 1, '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'Safari', 'macOS', 'US', '{\"page\": \"/dashboard\"}', '2025-12-09 21:19:30'),
(14, 'platform', 'page', 0, 'page_visit', 2, '192.168.1.102', 'Mozilla/5.0 (X11; Linux x86_64)', 'Firefox', 'Linux', 'GB', '{\"page\": \"/projects\"}', '2025-12-09 22:19:30'),
(15, 'platform', 'auth', 1, 'user_login', 1, '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'Safari', 'macOS', 'US', '{\"timestamp\": \"2025-12-09 19:19:30\"}', '2025-12-09 19:19:30'),
(16, 'platform', 'auth', 2, 'user_login', 2, '192.168.1.102', 'Mozilla/5.0 (X11; Linux x86_64)', 'Firefox', 'Linux', 'GB', '{\"timestamp\": \"2025-12-09 19:19:30\"}', '2025-12-09 19:19:30'),
(17, 'platform', 'page', 0, 'page_visit', 1, '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'Safari', 'macOS', 'US', '{\"page\": \"/\"}', '2025-12-07 19:19:30'),
(18, 'platform', 'page', 0, 'page_visit', 2, '192.168.1.102', 'Mozilla/5.0 (X11; Linux x86_64)', 'Firefox', 'Linux', 'GB', '{\"page\": \"/dashboard\"}', '2025-12-06 19:19:30'),
(19, 'platform', 'page', 0, 'page_visit', 3, '192.168.1.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:95.0)', 'Edge', 'Windows', 'US', '{\"page\": \"/admin\"}', '2025-12-05 19:19:30'),
(20, 'platform', 'auth', 1, 'user_login', 1, '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'Safari', 'macOS', 'US', '{\"timestamp\": \"2025-12-07 19:19:30\"}', '2025-12-07 19:19:30'),
(21, 'platform', 'auth', 2, 'user_login', 2, '192.168.1.102', 'Mozilla/5.0 (X11; Linux x86_64)', 'Firefox', 'Linux', 'GB', '{\"timestamp\": \"2025-12-06 19:19:30\"}', '2025-12-06 19:19:30'),
(22, 'platform', 'conversion', 0, 'conversion_signup', 4, '192.168.1.105', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'Chrome', 'Windows', 'US', '{\"conversion_type\": \"signup\"}', '2025-12-10 18:49:30'),
(23, 'platform', 'conversion', 0, 'conversion_project_create', 1, '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'Safari', 'macOS', 'US', '{\"conversion_type\": \"project_create\"}', '2025-12-09 19:19:30'),
(24, 'platform', 'page', 0, 'page_visit', NULL, '192.168.1.110', 'Mozilla/5.0 (Android 11; Mobile)', 'Chrome', 'Android', 'IN', '{\"page\": \"/\"}', '2025-12-03 19:19:30'),
(25, 'platform', 'page', 0, 'page_visit', NULL, '192.168.1.111', 'Mozilla/5.0 (iPad; CPU OS 14_0)', 'Safari', 'iOS', 'AU', '{\"page\": \"/\"}', '2025-11-30 19:19:30'),
(26, 'platform', 'page', 0, 'page_visit', NULL, '192.168.1.112', 'Mozilla/5.0 (Windows NT 10.0)', 'Edge', 'Windows', 'DE', '{\"page\": \"/\"}', '2025-11-25 19:19:30'),
(27, 'platform', 'page', 0, 'page_visit', NULL, '192.168.1.113', 'Mozilla/5.0 (X11; Ubuntu)', 'Firefox', 'Linux', 'FR', '{\"page\": \"/\"}', '2025-11-20 19:19:30'),
(28, 'platform', 'page', 0, 'page_visit', NULL, '192.168.1.114', 'Opera/9.80', 'Opera', 'Windows', 'ES', '{\"page\": \"/\"}', '2025-11-15 19:19:30'),
(29, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/overview\",\"url\":\"\\/admin\\/analytics\\/overview\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 19:19:44\"}', '2025-12-10 19:19:44'),
(30, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/events\",\"url\":\"\\/admin\\/analytics\\/events\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/overview\",\"timestamp\":\"2025-12-10 19:20:14\"}', '2025-12-10 19:20:14'),
(31, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/reports\",\"url\":\"\\/admin\\/analytics\\/reports\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/events\",\"timestamp\":\"2025-12-10 19:20:38\"}', '2025-12-10 19:20:38'),
(32, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export\",\"url\":\"\\/admin\\/analytics\\/export\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/reports\",\"timestamp\":\"2025-12-10 19:20:53\"}', '2025-12-10 19:20:53'),
(33, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export\",\"url\":\"\\/admin\\/analytics\\/export\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:20:57\"}', '2025-12-10 19:20:57'),
(34, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:32:09\"}', '2025-12-10 19:32:09'),
(35, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2025-12-10 19:32:13\"}', '2025-12-10 19:32:13'),
(36, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/\",\"url\":\"\\/projects\\/codexpro\\/\",\"referer\":null,\"timestamp\":\"2025-12-10 19:32:15\"}', '2025-12-10 19:32:15'),
(37, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/editor\\/new\",\"url\":\"\\/projects\\/codexpro\\/editor\\/new\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/\",\"timestamp\":\"2025-12-10 19:32:18\"}', '2025-12-10 19:32:18'),
(38, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/editor\\/new\",\"url\":\"\\/projects\\/codexpro\\/editor\\/new\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/\",\"timestamp\":\"2025-12-10 19:32:18\"}', '2025-12-10 19:32:18'),
(39, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export\",\"url\":\"\\/admin\\/analytics\\/export\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/reports\",\"timestamp\":\"2025-12-10 19:33:59\"}', '2025-12-10 19:33:59'),
(40, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:34:01\"}', '2025-12-10 19:34:01'),
(41, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:35:01\"}', '2025-12-10 19:35:01'),
(42, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-10&date_to=2025-12-10&timeframe=minute\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-10&date_to=2025-12-10&timeframe=minute\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:35:25\"}', '2025-12-10 19:35:25'),
(43, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-10&date_to=2025-12-10&timeframe=minute\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-10&date_to=2025-12-10&timeframe=minute\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:35:29\"}', '2025-12-10 19:35:29'),
(44, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-10&date_to=2025-12-10&timeframe=minute\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-10&date_to=2025-12-10&timeframe=minute\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:35:30\"}', '2025-12-10 19:35:30'),
(45, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-10&date_to=2025-12-10&timeframe=minute\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-10&date_to=2025-12-10&timeframe=minute\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:35:31\"}', '2025-12-10 19:35:31'),
(46, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-10&date_to=2025-12-10&timeframe=hour\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-10&date_to=2025-12-10&timeframe=hour\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:35:41\"}', '2025-12-10 19:35:41'),
(47, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-10&date_to=2025-12-10&timeframe=minute\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-10&date_to=2025-12-10&timeframe=minute\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:35:51\"}', '2025-12-10 19:35:51'),
(48, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-10&date_to=2025-12-10&timeframe=minute\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-10&date_to=2025-12-10&timeframe=minute\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:36:01\"}', '2025-12-10 19:36:01'),
(49, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-10&date_to=2025-12-10&timeframe=minute\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-10&date_to=2025-12-10&timeframe=minute\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:36:31\"}', '2025-12-10 19:36:31'),
(50, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-10&date_to=2025-12-10&timeframe=minute\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-10&date_to=2025-12-10&timeframe=minute\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:37:01\"}', '2025-12-10 19:37:01'),
(51, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:37:29\"}', '2025-12-10 19:37:29'),
(52, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:37:32\"}', '2025-12-10 19:37:32'),
(53, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:38:02\"}', '2025-12-10 19:38:02'),
(54, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:38:32\"}', '2025-12-10 19:38:32'),
(55, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:39:01\"}', '2025-12-10 19:39:01'),
(56, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export\",\"url\":\"\\/admin\\/analytics\\/export\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:39:04\"}', '2025-12-10 19:39:04'),
(57, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:39:06\"}', '2025-12-10 19:39:06'),
(58, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:39:37\"}', '2025-12-10 19:39:37'),
(59, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:40:08\"}', '2025-12-10 19:40:08'),
(60, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=save_dashboard\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=save_dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:40:29\"}', '2025-12-10 19:40:29'),
(61, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:41:17\"}', '2025-12-10 19:41:17'),
(62, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:41:47\"}', '2025-12-10 19:41:47'),
(63, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:42:17\"}', '2025-12-10 19:42:17'),
(64, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:42:47\"}', '2025-12-10 19:42:47'),
(65, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:43:17\"}', '2025-12-10 19:43:17'),
(66, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:43:47\"}', '2025-12-10 19:43:47'),
(67, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:44:17\"}', '2025-12-10 19:44:17'),
(68, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:44:53\"}', '2025-12-10 19:44:53'),
(69, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:45:53\"}', '2025-12-10 19:45:53'),
(70, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:46:53\"}', '2025-12-10 19:46:53'),
(71, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:47:53\"}', '2025-12-10 19:47:53'),
(72, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:48:53\"}', '2025-12-10 19:48:53'),
(73, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:49:53\"}', '2025-12-10 19:49:53'),
(74, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:50:53\"}', '2025-12-10 19:50:53'),
(75, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:51:53\"}', '2025-12-10 19:51:53'),
(76, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:52:53\"}', '2025-12-10 19:52:53'),
(77, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:53:53\"}', '2025-12-10 19:53:53'),
(78, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:54:53\"}', '2025-12-10 19:54:53'),
(79, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:55:53\"}', '2025-12-10 19:55:53'),
(80, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:56:53\"}', '2025-12-10 19:56:53'),
(81, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:57:53\"}', '2025-12-10 19:57:53'),
(82, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:58:53\"}', '2025-12-10 19:58:53'),
(83, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 19:59:53\"}', '2025-12-10 19:59:53'),
(84, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 20:00:53\"}', '2025-12-10 20:00:53'),
(85, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 20:01:53\"}', '2025-12-10 20:01:53'),
(86, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 20:02:53\"}', '2025-12-10 20:02:53'),
(87, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 20:03:53\"}', '2025-12-10 20:03:53'),
(88, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 20:04:53\"}', '2025-12-10 20:04:53'),
(89, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 20:05:53\"}', '2025-12-10 20:05:53'),
(90, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 20:06:53\"}', '2025-12-10 20:06:53'),
(91, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export\",\"url\":\"\\/admin\\/analytics\\/export\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 20:07:13\"}', '2025-12-10 20:07:13'),
(92, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 20:07:15\"}', '2025-12-10 20:07:15'),
(93, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 20:07:45\"}', '2025-12-10 20:07:45'),
(94, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 20:08:36\"}', '2025-12-10 20:08:36'),
(95, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 20:08:39\"}', '2025-12-10 20:08:39'),
(96, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 20:08:43\"}', '2025-12-10 20:08:43'),
(97, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 20:08:44\"}', '2025-12-10 20:08:44'),
(98, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 20:08:49\"}', '2025-12-10 20:08:49'),
(99, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/dashboard\",\"url\":\"\\/admin\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 20:09:07\"}', '2025-12-10 20:09:07'),
(100, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/cache\",\"url\":\"\\/admin\\/performance\\/cache\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/api\\/documentation\",\"timestamp\":\"2025-12-10 20:10:27\"}', '2025-12-10 20:10:27'),
(101, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/cache\",\"url\":\"\\/admin\\/performance\\/cache\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/api\\/documentation\",\"timestamp\":\"2025-12-10 20:10:40\"}', '2025-12-10 20:10:40'),
(102, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export\",\"url\":\"\\/admin\\/analytics\\/export\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/cache\",\"timestamp\":\"2025-12-10 20:10:55\"}', '2025-12-10 20:10:55'),
(103, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 20:10:57\"}', '2025-12-10 20:10:57'),
(104, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/security\",\"url\":\"\\/admin\\/security\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 20:11:25\"}', '2025-12-10 20:11:25');
INSERT INTO `analytics_events` (`id`, `project`, `resource_type`, `resource_id`, `event_type`, `user_id`, `ip_address`, `user_agent`, `browser`, `platform`, `country`, `metadata`, `created_at`) VALUES
(105, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export\",\"url\":\"\\/admin\\/analytics\\/export\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/cache\",\"timestamp\":\"2025-12-10 20:11:27\"}', '2025-12-10 20:11:27'),
(106, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-10&date_to=2025-12-10&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-10 20:11:29\"}', '2025-12-10 20:11:29'),
(107, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/security\",\"url\":\"\\/admin\\/security\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/websocket\\/rooms\",\"timestamp\":\"2025-12-10 20:27:42\"}', '2025-12-10 20:27:42'),
(108, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/security\\/blocked-ips\",\"url\":\"\\/admin\\/security\\/blocked-ips\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/security\",\"timestamp\":\"2025-12-10 20:28:00\"}', '2025-12-10 20:28:00'),
(109, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/security\\/stats\",\"url\":\"\\/admin\\/security\\/stats\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/security\",\"timestamp\":\"2025-12-10 20:28:14\"}', '2025-12-10 20:28:14'),
(110, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/security\\/blocked-ips\",\"url\":\"\\/admin\\/security\\/blocked-ips\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/security\",\"timestamp\":\"2025-12-10 20:28:30\"}', '2025-12-10 20:28:30'),
(111, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/security\",\"url\":\"\\/admin\\/security\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/websocket\\/rooms\",\"timestamp\":\"2025-12-10 20:28:32\"}', '2025-12-10 20:28:32'),
(112, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/security\\/failed-logins\",\"url\":\"\\/admin\\/security\\/failed-logins\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/security\",\"timestamp\":\"2025-12-10 20:28:34\"}', '2025-12-10 20:28:34'),
(113, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/security\\/stats\",\"url\":\"\\/admin\\/security\\/stats\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/security\",\"timestamp\":\"2025-12-10 20:29:04\"}', '2025-12-10 20:29:04'),
(114, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/cache\",\"url\":\"\\/admin\\/performance\\/cache\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/security\",\"timestamp\":\"2025-12-10 20:29:19\"}', '2025-12-10 20:29:19'),
(115, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/database\",\"url\":\"\\/admin\\/performance\\/database\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/cache\",\"timestamp\":\"2025-12-10 20:29:24\"}', '2025-12-10 20:29:24'),
(116, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/assets\",\"url\":\"\\/admin\\/performance\\/assets\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/database\",\"timestamp\":\"2025-12-10 20:29:30\"}', '2025-12-10 20:29:30'),
(117, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/assets\",\"timestamp\":\"2025-12-10 20:29:35\"}', '2025-12-10 20:29:35'),
(118, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:30:06\"}', '2025-12-10 20:30:06'),
(119, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:30:38\"}', '2025-12-10 20:30:38'),
(120, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:31:11\"}', '2025-12-10 20:31:11'),
(121, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:31:16\"}', '2025-12-10 20:31:16'),
(122, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:31:48\"}', '2025-12-10 20:31:48'),
(123, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:32:19\"}', '2025-12-10 20:32:19'),
(124, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:32:52\"}', '2025-12-10 20:32:52'),
(125, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:33:24\"}', '2025-12-10 20:33:24'),
(126, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:33:56\"}', '2025-12-10 20:33:56'),
(127, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:34:28\"}', '2025-12-10 20:34:28'),
(128, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:35:00\"}', '2025-12-10 20:35:00'),
(129, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:35:32\"}', '2025-12-10 20:35:32'),
(130, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:36:04\"}', '2025-12-10 20:36:04'),
(131, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:36:37\"}', '2025-12-10 20:36:37'),
(132, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:37:09\"}', '2025-12-10 20:37:09'),
(133, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:37:41\"}', '2025-12-10 20:37:41'),
(134, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:38:13\"}', '2025-12-10 20:38:13'),
(135, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:38:45\"}', '2025-12-10 20:38:45'),
(136, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:39:17\"}', '2025-12-10 20:39:17'),
(137, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:39:49\"}', '2025-12-10 20:39:49'),
(138, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:40:21\"}', '2025-12-10 20:40:21'),
(139, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:40:53\"}', '2025-12-10 20:40:53'),
(140, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:41:25\"}', '2025-12-10 20:41:25'),
(141, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:41:57\"}', '2025-12-10 20:41:57'),
(142, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:42:29\"}', '2025-12-10 20:42:29'),
(143, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:43:01\"}', '2025-12-10 20:43:01'),
(144, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:43:33\"}', '2025-12-10 20:43:33'),
(145, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:44:05\"}', '2025-12-10 20:44:05'),
(146, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:44:37\"}', '2025-12-10 20:44:37'),
(147, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:45:09\"}', '2025-12-10 20:45:09'),
(148, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:45:41\"}', '2025-12-10 20:45:41'),
(149, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:46:13\"}', '2025-12-10 20:46:13'),
(150, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:46:45\"}', '2025-12-10 20:46:45'),
(151, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:47:17\"}', '2025-12-10 20:47:17'),
(152, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:47:49\"}', '2025-12-10 20:47:49'),
(153, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:48:21\"}', '2025-12-10 20:48:21'),
(154, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:48:53\"}', '2025-12-10 20:48:53'),
(155, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:49:25\"}', '2025-12-10 20:49:25'),
(156, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:49:57\"}', '2025-12-10 20:49:57'),
(157, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:50:29\"}', '2025-12-10 20:50:29'),
(158, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:51:02\"}', '2025-12-10 20:51:02'),
(159, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:51:34\"}', '2025-12-10 20:51:34'),
(160, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:52:06\"}', '2025-12-10 20:52:06'),
(161, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:52:39\"}', '2025-12-10 20:52:39'),
(162, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:53:11\"}', '2025-12-10 20:53:11'),
(163, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:53:43\"}', '2025-12-10 20:53:43'),
(164, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:54:15\"}', '2025-12-10 20:54:15'),
(165, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:54:47\"}', '2025-12-10 20:54:47'),
(166, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:55:19\"}', '2025-12-10 20:55:19'),
(167, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:55:51\"}', '2025-12-10 20:55:51'),
(168, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:56:23\"}', '2025-12-10 20:56:23'),
(169, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:56:55\"}', '2025-12-10 20:56:55'),
(170, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:57:27\"}', '2025-12-10 20:57:27'),
(171, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:57:59\"}', '2025-12-10 20:57:59'),
(172, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:58:31\"}', '2025-12-10 20:58:31'),
(173, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:59:03\"}', '2025-12-10 20:59:03'),
(174, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 20:59:35\"}', '2025-12-10 20:59:35'),
(175, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:00:07\"}', '2025-12-10 21:00:07'),
(176, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:00:39\"}', '2025-12-10 21:00:39'),
(177, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:01:11\"}', '2025-12-10 21:01:11'),
(178, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:01:43\"}', '2025-12-10 21:01:43'),
(179, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:02:15\"}', '2025-12-10 21:02:15'),
(180, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:02:47\"}', '2025-12-10 21:02:47'),
(181, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:03:19\"}', '2025-12-10 21:03:19'),
(182, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:03:51\"}', '2025-12-10 21:03:51'),
(183, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:04:23\"}', '2025-12-10 21:04:23'),
(184, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:04:55\"}', '2025-12-10 21:04:55'),
(185, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:05:28\"}', '2025-12-10 21:05:28'),
(186, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:05:59\"}', '2025-12-10 21:05:59'),
(187, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:06:31\"}', '2025-12-10 21:06:31'),
(188, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:07:04\"}', '2025-12-10 21:07:04'),
(189, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:07:37\"}', '2025-12-10 21:07:37'),
(190, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:08:09\"}', '2025-12-10 21:08:09'),
(191, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:08:41\"}', '2025-12-10 21:08:41'),
(192, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:09:12\"}', '2025-12-10 21:09:12'),
(193, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:09:44\"}', '2025-12-10 21:09:44'),
(194, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:10:16\"}', '2025-12-10 21:10:16'),
(195, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:10:49\"}', '2025-12-10 21:10:49'),
(196, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:11:21\"}', '2025-12-10 21:11:21'),
(197, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:11:53\"}', '2025-12-10 21:11:53'),
(198, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:12:25\"}', '2025-12-10 21:12:25'),
(199, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:12:57\"}', '2025-12-10 21:12:57'),
(200, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:13:29\"}', '2025-12-10 21:13:29'),
(201, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:14:01\"}', '2025-12-10 21:14:01'),
(202, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:14:33\"}', '2025-12-10 21:14:33'),
(203, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:15:05\"}', '2025-12-10 21:15:05'),
(204, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:15:37\"}', '2025-12-10 21:15:37'),
(205, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:16:09\"}', '2025-12-10 21:16:09'),
(206, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:16:41\"}', '2025-12-10 21:16:41'),
(207, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:17:13\"}', '2025-12-10 21:17:13'),
(208, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:17:45\"}', '2025-12-10 21:17:45');
INSERT INTO `analytics_events` (`id`, `project`, `resource_type`, `resource_id`, `event_type`, `user_id`, `ip_address`, `user_agent`, `browser`, `platform`, `country`, `metadata`, `created_at`) VALUES
(209, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:18:17\"}', '2025-12-10 21:18:17'),
(210, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:18:49\"}', '2025-12-10 21:18:49'),
(211, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:19:21\"}', '2025-12-10 21:19:21'),
(212, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:19:53\"}', '2025-12-10 21:19:53'),
(213, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:20:25\"}', '2025-12-10 21:20:25'),
(214, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:20:57\"}', '2025-12-10 21:20:57'),
(215, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:21:29\"}', '2025-12-10 21:21:29'),
(216, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:22:01\"}', '2025-12-10 21:22:01'),
(217, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:22:33\"}', '2025-12-10 21:22:33'),
(218, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:23:05\"}', '2025-12-10 21:23:05'),
(219, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:23:37\"}', '2025-12-10 21:23:37'),
(220, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:24:09\"}', '2025-12-10 21:24:09'),
(221, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:24:41\"}', '2025-12-10 21:24:41'),
(222, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:25:13\"}', '2025-12-10 21:25:13'),
(223, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:25:45\"}', '2025-12-10 21:25:45'),
(224, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:26:17\"}', '2025-12-10 21:26:17'),
(225, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:26:49\"}', '2025-12-10 21:26:49'),
(226, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:27:21\"}', '2025-12-10 21:27:21'),
(227, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:27:53\"}', '2025-12-10 21:27:53'),
(228, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:28:25\"}', '2025-12-10 21:28:25'),
(229, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:28:57\"}', '2025-12-10 21:28:57'),
(230, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:29:29\"}', '2025-12-10 21:29:29'),
(231, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:30:01\"}', '2025-12-10 21:30:01'),
(232, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:30:34\"}', '2025-12-10 21:30:34'),
(233, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:31:06\"}', '2025-12-10 21:31:06'),
(234, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:31:38\"}', '2025-12-10 21:31:38'),
(235, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:32:10\"}', '2025-12-10 21:32:10'),
(236, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:32:42\"}', '2025-12-10 21:32:42'),
(237, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:33:14\"}', '2025-12-10 21:33:14'),
(238, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:33:46\"}', '2025-12-10 21:33:46'),
(239, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:34:19\"}', '2025-12-10 21:34:19'),
(240, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:34:50\"}', '2025-12-10 21:34:50'),
(241, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:35:22\"}', '2025-12-10 21:35:22'),
(242, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:35:54\"}', '2025-12-10 21:35:54'),
(243, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:36:26\"}', '2025-12-10 21:36:26'),
(244, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc3:8345:b4bd:55d9:6744:8a4e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2025-12-10 21:36:58\"}', '2025-12-10 21:36:58'),
(245, 'platform', 'page', 0, 'page_visit', NULL, '193.32.126.165', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'Other', 'Windows', 'FR', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-11 03:39:11\"}', '2025-12-11 03:39:11'),
(246, 'platform', 'page', 0, 'page_visit', NULL, '45.131.155.101', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) obsidian/1.5.8 Chrome/120.0.6099.283 Electron/28.2.3 Safari/537.36', 'Chrome', 'Linux', 'JP', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-11 15:18:22\"}', '2025-12-11 15:18:22'),
(247, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.244', NULL, 'Other', 'Other', 'NL', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-12 09:03:28\"}', '2025-12-12 09:03:28'),
(248, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.244', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-12 09:03:29\"}', '2025-12-12 09:03:29'),
(249, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.244', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":null,\"timestamp\":\"2025-12-12 09:03:33\"}', '2025-12-12 09:03:33'),
(250, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.244', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login?redirect=\\/qr\",\"url\":\"\\/login?redirect=\\/qr\",\"referer\":null,\"timestamp\":\"2025-12-12 09:03:33\"}', '2025-12-12 09:03:33'),
(251, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.244', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login?redirect=\\/resumex\",\"url\":\"\\/login?redirect=\\/resumex\",\"referer\":null,\"timestamp\":\"2025-12-12 09:03:33\"}', '2025-12-12 09:03:33'),
(252, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.244', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login?redirect=\\/devzone\",\"url\":\"\\/login?redirect=\\/devzone\",\"referer\":null,\"timestamp\":\"2025-12-12 09:03:33\"}', '2025-12-12 09:03:33'),
(253, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.244', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login?redirect=\\/imgtxt\",\"url\":\"\\/login?redirect=\\/imgtxt\",\"referer\":null,\"timestamp\":\"2025-12-12 09:03:33\"}', '2025-12-12 09:03:33'),
(254, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.244', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login?redirect=\\/codexpro\",\"url\":\"\\/login?redirect=\\/codexpro\",\"referer\":null,\"timestamp\":\"2025-12-12 09:03:33\"}', '2025-12-12 09:03:33'),
(255, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.244', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/register\",\"url\":\"\\/register\",\"referer\":null,\"timestamp\":\"2025-12-12 09:03:34\"}', '2025-12-12 09:03:34'),
(256, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.244', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login?redirect=\\/proshare\",\"url\":\"\\/login?redirect=\\/proshare\",\"referer\":null,\"timestamp\":\"2025-12-12 09:03:34\"}', '2025-12-12 09:03:34'),
(257, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.244', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/forgot-password\",\"url\":\"\\/forgot-password\",\"referer\":null,\"timestamp\":\"2025-12-12 09:03:37\"}', '2025-12-12 09:03:37'),
(258, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.244', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/forgot-password\",\"url\":\"\\/forgot-password\",\"referer\":null,\"timestamp\":\"2025-12-12 09:03:47\"}', '2025-12-12 09:03:47'),
(259, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.244', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/forgot-password\",\"url\":\"\\/forgot-password\",\"referer\":null,\"timestamp\":\"2025-12-12 09:03:53\"}', '2025-12-12 09:03:53'),
(260, 'platform', 'page', 0, 'page_visit', NULL, '205.210.31.25', 'Hello from Palo Alto Networks, find out more about our scans in https://docs-cortex.paloaltonetworks.com/r/1/Cortex-Xpanse/Scanning-activity', 'Other', 'Other', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-12 12:15:48\"}', '2025-12-12 12:15:48'),
(261, 'platform', 'page', 0, 'page_visit', NULL, '74.7.243.208', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GPTBot/1.3; +https://openai.com/gptbot)', 'Other', 'Other', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-12 13:38:40\"}', '2025-12-12 13:38:40'),
(262, 'platform', 'page', 0, 'page_visit', NULL, '205.210.31.34', NULL, 'Other', 'Other', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-12 23:23:52\"}', '2025-12-12 23:23:52'),
(263, 'platform', 'page', 0, 'page_visit', NULL, '87.251.78.131', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.1 Safari/605.1.15', 'Safari', 'macOS', 'RU', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-13 02:59:02\"}', '2025-12-13 02:59:02'),
(264, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-13 21:28:36\"}', '2025-12-13 21:28:36'),
(265, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2025-12-13 21:29:11\"}', '2025-12-13 21:29:11'),
(266, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/login\",\"timestamp\":\"2025-12-13 21:29:22\"}', '2025-12-13 21:29:22'),
(267, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2025-12-13 21:29:23\",\"login_method\":\"standard\"}', '2025-12-13 21:29:23'),
(268, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/login\",\"timestamp\":\"2025-12-13 21:29:24\"}', '2025-12-13 21:29:24'),
(269, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\",\"url\":\"\\/admin\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2025-12-13 21:29:29\"}', '2025-12-13 21:29:29'),
(270, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/home-content\",\"url\":\"\\/admin\\/home-content\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\",\"timestamp\":\"2025-12-13 21:29:41\"}', '2025-12-13 21:29:41'),
(271, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/home-content\\/projects-section\",\"url\":\"\\/admin\\/home-content\\/projects-section\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/home-content\",\"timestamp\":\"2025-12-13 21:30:31\"}', '2025-12-13 21:30:31'),
(272, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/home-content\",\"url\":\"\\/admin\\/home-content\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/home-content\",\"timestamp\":\"2025-12-13 21:30:32\"}', '2025-12-13 21:30:32'),
(273, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/home-content\",\"timestamp\":\"2025-12-13 21:30:38\"}', '2025-12-13 21:30:38'),
(274, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export\",\"url\":\"\\/admin\\/analytics\\/export\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/home-content\",\"timestamp\":\"2025-12-13 21:30:59\"}', '2025-12-13 21:30:59'),
(275, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-13&date_to=2025-12-13&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-13&date_to=2025-12-13&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-13 21:31:01\"}', '2025-12-13 21:31:01'),
(276, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/users\",\"url\":\"\\/admin\\/users\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-13 21:31:22\"}', '2025-12-13 21:31:22'),
(277, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export\",\"url\":\"\\/admin\\/analytics\\/export\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/home-content\",\"timestamp\":\"2025-12-13 21:31:28\"}', '2025-12-13 21:31:28'),
(278, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-13&date_to=2025-12-13&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-13&date_to=2025-12-13&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-13 21:31:29\"}', '2025-12-13 21:31:29'),
(279, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-12&date_to=2025-12-13&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-12&date_to=2025-12-13&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-13 21:31:39\"}', '2025-12-13 21:31:39'),
(280, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-12&date_to=2025-12-13&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-12&date_to=2025-12-13&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-13 21:31:40\"}', '2025-12-13 21:31:40'),
(281, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-12&date_to=2025-12-13&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-12&date_to=2025-12-13&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-13 21:31:59\"}', '2025-12-13 21:31:59'),
(282, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-12&date_to=2025-12-13&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-12&date_to=2025-12-13&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-13 21:32:29\"}', '2025-12-13 21:32:29'),
(283, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-12&date_to=2025-12-13&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-12&date_to=2025-12-13&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-13 21:33:00\"}', '2025-12-13 21:33:00'),
(284, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-12&date_to=2025-12-13&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-12&date_to=2025-12-13&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-13 21:33:29\"}', '2025-12-13 21:33:29'),
(285, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2025-12-13 21:33:54\"}', '2025-12-13 21:33:54'),
(286, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/\",\"url\":\"\\/projects\\/codexpro\\/\",\"referer\":null,\"timestamp\":\"2025-12-13 21:33:57\"}', '2025-12-13 21:33:57'),
(287, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-12&date_to=2025-12-13&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-12&date_to=2025-12-13&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-13 21:33:59\"}', '2025-12-13 21:33:59'),
(288, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/editor\",\"url\":\"\\/projects\\/codexpro\\/editor\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/\",\"timestamp\":\"2025-12-13 21:34:01\"}', '2025-12-13 21:34:01'),
(289, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/editor\",\"url\":\"\\/projects\\/codexpro\\/editor\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/\",\"timestamp\":\"2025-12-13 21:34:01\"}', '2025-12-13 21:34:01'),
(290, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/editor\\/save\",\"url\":\"\\/projects\\/codexpro\\/editor\\/save\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/editor\",\"timestamp\":\"2025-12-13 21:34:04\"}', '2025-12-13 21:34:04'),
(291, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/editor\\/save\",\"url\":\"\\/projects\\/codexpro\\/editor\\/save\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/editor\",\"timestamp\":\"2025-12-13 21:34:04\"}', '2025-12-13 21:34:04'),
(292, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/editor\\/4\",\"url\":\"\\/projects\\/codexpro\\/editor\\/4\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/editor\",\"timestamp\":\"2025-12-13 21:34:04\"}', '2025-12-13 21:34:04'),
(293, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/editor\\/4\",\"url\":\"\\/projects\\/codexpro\\/editor\\/4\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/editor\",\"timestamp\":\"2025-12-13 21:34:04\"}', '2025-12-13 21:34:04'),
(294, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/editor\\/autosave\",\"url\":\"\\/projects\\/codexpro\\/editor\\/autosave\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/editor\\/4\",\"timestamp\":\"2025-12-13 21:34:12\"}', '2025-12-13 21:34:12'),
(295, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/editor\\/autosave\",\"url\":\"\\/projects\\/codexpro\\/editor\\/autosave\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/editor\\/4\",\"timestamp\":\"2025-12-13 21:34:12\"}', '2025-12-13 21:34:12'),
(296, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/editor\",\"url\":\"\\/projects\\/codexpro\\/editor\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/\",\"timestamp\":\"2025-12-13 21:34:29\"}', '2025-12-13 21:34:29'),
(297, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/editor\",\"url\":\"\\/projects\\/codexpro\\/editor\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/\",\"timestamp\":\"2025-12-13 21:34:29\"}', '2025-12-13 21:34:29'),
(298, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-12&date_to=2025-12-13&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-12&date_to=2025-12-13&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-13 21:34:29\"}', '2025-12-13 21:34:29'),
(299, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/dashboard\",\"url\":\"\\/projects\\/codexpro\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/editor\",\"timestamp\":\"2025-12-13 21:34:32\"}', '2025-12-13 21:34:32'),
(300, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/dashboard\",\"url\":\"\\/projects\\/codexpro\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/editor\",\"timestamp\":\"2025-12-13 21:34:32\"}', '2025-12-13 21:34:32'),
(301, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/projects\",\"url\":\"\\/projects\\/codexpro\\/projects\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/dashboard\",\"timestamp\":\"2025-12-13 21:34:41\"}', '2025-12-13 21:34:41'),
(302, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/projects\",\"url\":\"\\/projects\\/codexpro\\/projects\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/dashboard\",\"timestamp\":\"2025-12-13 21:34:41\"}', '2025-12-13 21:34:41'),
(303, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/projects\\/4\",\"url\":\"\\/projects\\/codexpro\\/projects\\/4\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/projects\",\"timestamp\":\"2025-12-13 21:34:46\"}', '2025-12-13 21:34:46'),
(304, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/projects\\/4\",\"url\":\"\\/projects\\/codexpro\\/projects\\/4\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/projects\",\"timestamp\":\"2025-12-13 21:34:46\"}', '2025-12-13 21:34:46'),
(305, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/snippets\",\"url\":\"\\/projects\\/codexpro\\/snippets\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/projects\",\"timestamp\":\"2025-12-13 21:34:49\"}', '2025-12-13 21:34:49'),
(306, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/snippets\",\"url\":\"\\/projects\\/codexpro\\/snippets\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/projects\",\"timestamp\":\"2025-12-13 21:34:49\"}', '2025-12-13 21:34:49'),
(307, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/templates\",\"url\":\"\\/projects\\/codexpro\\/templates\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/snippets\",\"timestamp\":\"2025-12-13 21:34:52\"}', '2025-12-13 21:34:52'),
(308, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/templates\",\"url\":\"\\/projects\\/codexpro\\/templates\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/snippets\",\"timestamp\":\"2025-12-13 21:34:52\"}', '2025-12-13 21:34:52'),
(309, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-12&date_to=2025-12-13&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-12&date_to=2025-12-13&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-13 21:34:59\"}', '2025-12-13 21:34:59'),
(310, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/projects\",\"url\":\"\\/projects\\/codexpro\\/projects\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/dashboard\",\"timestamp\":\"2025-12-13 21:35:02\"}', '2025-12-13 21:35:02'),
(311, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/projects\",\"url\":\"\\/projects\\/codexpro\\/projects\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/dashboard\",\"timestamp\":\"2025-12-13 21:35:02\"}', '2025-12-13 21:35:02'),
(312, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/editor\",\"url\":\"\\/projects\\/codexpro\\/editor\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/\",\"timestamp\":\"2025-12-13 21:35:12\"}', '2025-12-13 21:35:12'),
(313, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/editor\",\"url\":\"\\/projects\\/codexpro\\/editor\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/\",\"timestamp\":\"2025-12-13 21:35:13\"}', '2025-12-13 21:35:13'),
(314, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2025-12-13 21:35:15\"}', '2025-12-13 21:35:15'),
(315, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/\",\"url\":\"\\/projects\\/imgtxt\\/\",\"referer\":null,\"timestamp\":\"2025-12-13 21:35:18\"}', '2025-12-13 21:35:18'),
(316, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/upload\",\"url\":\"\\/projects\\/imgtxt\\/upload\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/\",\"timestamp\":\"2025-12-13 21:35:22\"}', '2025-12-13 21:35:22'),
(317, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/upload\",\"url\":\"\\/projects\\/imgtxt\\/upload\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/\",\"timestamp\":\"2025-12-13 21:35:23\"}', '2025-12-13 21:35:23'),
(318, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/history\",\"url\":\"\\/projects\\/imgtxt\\/history\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/\",\"timestamp\":\"2025-12-13 21:35:27\"}', '2025-12-13 21:35:27');
INSERT INTO `analytics_events` (`id`, `project`, `resource_type`, `resource_id`, `event_type`, `user_id`, `ip_address`, `user_agent`, `browser`, `platform`, `country`, `metadata`, `created_at`) VALUES
(319, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/history\",\"url\":\"\\/projects\\/imgtxt\\/history\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/\",\"timestamp\":\"2025-12-13 21:35:27\"}', '2025-12-13 21:35:27'),
(320, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-12&date_to=2025-12-13&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-12&date_to=2025-12-13&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-13 21:35:29\"}', '2025-12-13 21:35:29'),
(321, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/upload\",\"url\":\"\\/projects\\/imgtxt\\/upload\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/history\",\"timestamp\":\"2025-12-13 21:35:37\"}', '2025-12-13 21:35:37'),
(322, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/upload\",\"url\":\"\\/projects\\/imgtxt\\/upload\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/history\",\"timestamp\":\"2025-12-13 21:35:37\"}', '2025-12-13 21:35:37'),
(323, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/upload\",\"url\":\"\\/projects\\/imgtxt\\/upload\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/upload\",\"timestamp\":\"2025-12-13 21:35:49\"}', '2025-12-13 21:35:49'),
(324, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/upload\",\"url\":\"\\/projects\\/imgtxt\\/upload\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/upload\",\"timestamp\":\"2025-12-13 21:35:49\"}', '2025-12-13 21:35:49'),
(325, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/process\",\"url\":\"\\/projects\\/imgtxt\\/process\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/upload\",\"timestamp\":\"2025-12-13 21:35:50\"}', '2025-12-13 21:35:50'),
(326, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/process\",\"url\":\"\\/projects\\/imgtxt\\/process\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/upload\",\"timestamp\":\"2025-12-13 21:35:50\"}', '2025-12-13 21:35:50'),
(327, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-12&date_to=2025-12-13&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-12&date_to=2025-12-13&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-13 21:35:59\"}', '2025-12-13 21:35:59'),
(328, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/batch\",\"url\":\"\\/projects\\/imgtxt\\/batch\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/upload\",\"timestamp\":\"2025-12-13 21:36:08\"}', '2025-12-13 21:36:08'),
(329, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/batch\",\"url\":\"\\/projects\\/imgtxt\\/batch\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/upload\",\"timestamp\":\"2025-12-13 21:36:09\"}', '2025-12-13 21:36:09'),
(330, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/history\",\"url\":\"\\/projects\\/imgtxt\\/history\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/batch\",\"timestamp\":\"2025-12-13 21:36:11\"}', '2025-12-13 21:36:11'),
(331, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/history\",\"url\":\"\\/projects\\/imgtxt\\/history\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/batch\",\"timestamp\":\"2025-12-13 21:36:11\"}', '2025-12-13 21:36:11'),
(332, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/result\\/13\",\"url\":\"\\/projects\\/imgtxt\\/result\\/13\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/history\",\"timestamp\":\"2025-12-13 21:36:14\"}', '2025-12-13 21:36:14'),
(333, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/result\\/13\",\"url\":\"\\/projects\\/imgtxt\\/result\\/13\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/history\",\"timestamp\":\"2025-12-13 21:36:14\"}', '2025-12-13 21:36:14'),
(334, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/result\\/13\",\"url\":\"\\/projects\\/imgtxt\\/result\\/13\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/history\",\"timestamp\":\"2025-12-13 21:36:20\"}', '2025-12-13 21:36:20'),
(335, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/result\\/13\",\"url\":\"\\/projects\\/imgtxt\\/result\\/13\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/history\",\"timestamp\":\"2025-12-13 21:36:20\"}', '2025-12-13 21:36:20'),
(336, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/settings\",\"url\":\"\\/projects\\/imgtxt\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/history\",\"timestamp\":\"2025-12-13 21:36:25\"}', '2025-12-13 21:36:25'),
(337, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/settings\",\"url\":\"\\/projects\\/imgtxt\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/history\",\"timestamp\":\"2025-12-13 21:36:25\"}', '2025-12-13 21:36:25'),
(338, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/upload\",\"url\":\"\\/projects\\/imgtxt\\/upload\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/history\",\"timestamp\":\"2025-12-13 21:36:33\"}', '2025-12-13 21:36:33'),
(339, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/upload\",\"url\":\"\\/projects\\/imgtxt\\/upload\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/history\",\"timestamp\":\"2025-12-13 21:36:33\"}', '2025-12-13 21:36:33'),
(340, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-12&date_to=2025-12-13&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-12&date_to=2025-12-13&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-13 21:36:34\"}', '2025-12-13 21:36:34'),
(341, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\",\"url\":\"\\/admin\\/projects\\/imgtxt\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-13 21:36:39\"}', '2025-12-13 21:36:39'),
(342, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\",\"url\":\"\\/admin\\/projects\\/imgtxt\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-13 21:36:53\"}', '2025-12-13 21:36:53'),
(343, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/dashboard\",\"url\":\"\\/projects\\/imgtxt\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\",\"timestamp\":\"2025-12-13 21:36:58\"}', '2025-12-13 21:36:58'),
(344, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/dashboard\",\"url\":\"\\/projects\\/imgtxt\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\",\"timestamp\":\"2025-12-13 21:36:59\"}', '2025-12-13 21:36:59'),
(345, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/history\",\"url\":\"\\/projects\\/imgtxt\\/history\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\",\"timestamp\":\"2025-12-13 21:37:08\"}', '2025-12-13 21:37:08'),
(346, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/history\",\"url\":\"\\/projects\\/imgtxt\\/history\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\",\"timestamp\":\"2025-12-13 21:37:08\"}', '2025-12-13 21:37:08'),
(347, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/jobs\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/jobs\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\",\"timestamp\":\"2025-12-13 21:37:12\"}', '2025-12-13 21:37:12'),
(348, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/settings\",\"url\":\"\\/projects\\/imgtxt\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/jobs\",\"timestamp\":\"2025-12-13 21:37:28\"}', '2025-12-13 21:37:28'),
(349, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/settings\",\"url\":\"\\/projects\\/imgtxt\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/jobs\",\"timestamp\":\"2025-12-13 21:37:28\"}', '2025-12-13 21:37:28'),
(350, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/settings\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/jobs\",\"timestamp\":\"2025-12-13 21:37:34\"}', '2025-12-13 21:37:34'),
(351, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/settings\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/settings\",\"timestamp\":\"2025-12-13 21:37:41\"}', '2025-12-13 21:37:41'),
(352, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/settings\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/settings\",\"timestamp\":\"2025-12-13 21:37:41\"}', '2025-12-13 21:37:41'),
(353, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/settings\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/settings\",\"timestamp\":\"2025-12-13 21:38:33\"}', '2025-12-13 21:38:33'),
(354, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/settings\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/settings\",\"timestamp\":\"2025-12-13 21:38:36\"}', '2025-12-13 21:38:36'),
(355, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\",\"url\":\"\\/admin\\/projects\\/imgtxt\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/settings\",\"timestamp\":\"2025-12-13 21:38:47\"}', '2025-12-13 21:38:47'),
(356, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/jobs\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/jobs\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\",\"timestamp\":\"2025-12-13 21:39:48\"}', '2025-12-13 21:39:48'),
(357, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/settings\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/jobs\",\"timestamp\":\"2025-12-13 21:40:28\"}', '2025-12-13 21:40:28'),
(358, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/settings\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/settings\",\"timestamp\":\"2025-12-13 21:40:46\"}', '2025-12-13 21:40:46'),
(359, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/settings\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/settings\",\"timestamp\":\"2025-12-13 21:40:47\"}', '2025-12-13 21:40:47'),
(360, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/settings\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/settings\",\"timestamp\":\"2025-12-13 21:40:51\"}', '2025-12-13 21:40:51'),
(361, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/settings\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/settings\",\"timestamp\":\"2025-12-13 21:41:00\"}', '2025-12-13 21:41:00'),
(362, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/settings\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/settings\",\"timestamp\":\"2025-12-13 21:41:01\"}', '2025-12-13 21:41:01'),
(363, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/languages\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/languages\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/settings\",\"timestamp\":\"2025-12-13 21:41:16\"}', '2025-12-13 21:41:16'),
(364, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/users\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/users\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/languages\",\"timestamp\":\"2025-12-13 21:41:20\"}', '2025-12-13 21:41:20'),
(365, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/activity?user_id=3\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/activity?user_id=3\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/users\",\"timestamp\":\"2025-12-13 21:41:28\"}', '2025-12-13 21:41:28'),
(366, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/jobs?user_id=3\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/jobs?user_id=3\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/users\",\"timestamp\":\"2025-12-13 21:41:37\"}', '2025-12-13 21:41:37'),
(367, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/statistics\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/statistics\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/users\",\"timestamp\":\"2025-12-13 21:42:06\"}', '2025-12-13 21:42:06'),
(368, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/activity\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/activity\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/statistics\",\"timestamp\":\"2025-12-13 21:42:39\"}', '2025-12-13 21:42:39'),
(369, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/history\",\"url\":\"\\/projects\\/imgtxt\\/history\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/\",\"timestamp\":\"2025-12-13 21:44:51\"}', '2025-12-13 21:44:51'),
(370, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/history\",\"url\":\"\\/projects\\/imgtxt\\/history\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/\",\"timestamp\":\"2025-12-13 21:44:51\"}', '2025-12-13 21:44:51'),
(371, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/history\",\"timestamp\":\"2025-12-13 21:44:53\"}', '2025-12-13 21:44:53'),
(372, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/\",\"url\":\"\\/projects\\/proshare\\/\",\"referer\":null,\"timestamp\":\"2025-12-13 21:44:58\"}', '2025-12-13 21:44:58'),
(373, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/preview\\/WHr3rkI7\",\"url\":\"\\/projects\\/proshare\\/preview\\/WHr3rkI7\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/\",\"timestamp\":\"2025-12-13 21:45:01\"}', '2025-12-13 21:45:01'),
(374, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/preview\\/WHr3rkI7\",\"url\":\"\\/projects\\/proshare\\/preview\\/WHr3rkI7\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/\",\"timestamp\":\"2025-12-13 21:45:01\"}', '2025-12-13 21:45:01'),
(375, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/text\",\"url\":\"\\/projects\\/proshare\\/text\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/\",\"timestamp\":\"2025-12-13 21:45:07\"}', '2025-12-13 21:45:07'),
(376, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/text\",\"url\":\"\\/projects\\/proshare\\/text\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/\",\"timestamp\":\"2025-12-13 21:45:08\"}', '2025-12-13 21:45:08'),
(377, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/text\",\"url\":\"\\/projects\\/proshare\\/text\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/\",\"timestamp\":\"2025-12-13 21:45:08\"}', '2025-12-13 21:45:08'),
(378, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/text\",\"url\":\"\\/projects\\/proshare\\/text\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/\",\"timestamp\":\"2025-12-13 21:45:08\"}', '2025-12-13 21:45:08'),
(379, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/upload\",\"url\":\"\\/projects\\/proshare\\/upload\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/text\",\"timestamp\":\"2025-12-13 21:45:09\"}', '2025-12-13 21:45:09'),
(380, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/upload\",\"url\":\"\\/projects\\/proshare\\/upload\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/text\",\"timestamp\":\"2025-12-13 21:45:09\"}', '2025-12-13 21:45:09'),
(381, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/upload\",\"url\":\"\\/projects\\/proshare\\/upload\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/upload\",\"timestamp\":\"2025-12-13 21:45:23\"}', '2025-12-13 21:45:23'),
(382, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/upload\",\"url\":\"\\/projects\\/proshare\\/upload\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/upload\",\"timestamp\":\"2025-12-13 21:45:23\"}', '2025-12-13 21:45:23'),
(383, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/dashboard\",\"url\":\"\\/projects\\/proshare\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/upload\",\"timestamp\":\"2025-12-13 21:45:26\"}', '2025-12-13 21:45:26'),
(384, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/dashboard\",\"url\":\"\\/projects\\/proshare\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/upload\",\"timestamp\":\"2025-12-13 21:45:26\"}', '2025-12-13 21:45:26'),
(385, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/user-dashboard\",\"url\":\"\\/admin\\/projects\\/proshare\\/user-dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/activity\",\"timestamp\":\"2025-12-13 21:45:36\"}', '2025-12-13 21:45:36'),
(386, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/user-activity\",\"url\":\"\\/admin\\/projects\\/proshare\\/user-activity\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/user-dashboard\",\"timestamp\":\"2025-12-13 21:45:42\"}', '2025-12-13 21:45:42'),
(387, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/user-files\",\"url\":\"\\/admin\\/projects\\/proshare\\/user-files\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/user-activity\",\"timestamp\":\"2025-12-13 21:46:11\"}', '2025-12-13 21:46:11'),
(388, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/user-files?user_id=3\",\"url\":\"\\/admin\\/projects\\/proshare\\/user-files?user_id=3\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/user-files\",\"timestamp\":\"2025-12-13 21:46:17\"}', '2025-12-13 21:46:17'),
(389, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\",\"url\":\"\\/admin\\/projects\\/proshare\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/user-files?user_id=3\",\"timestamp\":\"2025-12-13 21:46:29\"}', '2025-12-13 21:46:29'),
(390, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/settings\",\"url\":\"\\/admin\\/projects\\/proshare\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\",\"timestamp\":\"2025-12-13 21:46:43\"}', '2025-12-13 21:46:43'),
(391, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/settings\",\"url\":\"\\/admin\\/projects\\/proshare\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/settings\",\"timestamp\":\"2025-12-13 21:46:53\"}', '2025-12-13 21:46:53'),
(392, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/settings\",\"url\":\"\\/admin\\/projects\\/proshare\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/settings\",\"timestamp\":\"2025-12-13 21:46:54\"}', '2025-12-13 21:46:54'),
(393, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/user-logs\",\"url\":\"\\/admin\\/projects\\/proshare\\/user-logs\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/settings\",\"timestamp\":\"2025-12-13 21:47:26\"}', '2025-12-13 21:47:26'),
(394, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/user-logs\",\"url\":\"\\/admin\\/projects\\/proshare\\/user-logs\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/settings\",\"timestamp\":\"2025-12-13 21:47:26\"}', '2025-12-13 21:47:26'),
(395, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/sessions\",\"url\":\"\\/admin\\/projects\\/proshare\\/sessions\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/user-logs\",\"timestamp\":\"2025-12-13 21:47:38\"}', '2025-12-13 21:47:38'),
(396, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/files\",\"url\":\"\\/admin\\/projects\\/proshare\\/files\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/sessions\",\"timestamp\":\"2025-12-13 21:47:41\"}', '2025-12-13 21:47:41'),
(397, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/files?status=active\",\"url\":\"\\/admin\\/projects\\/proshare\\/files?status=active\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/files\",\"timestamp\":\"2025-12-13 21:47:50\"}', '2025-12-13 21:47:50'),
(398, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/files?status=all\",\"url\":\"\\/admin\\/projects\\/proshare\\/files?status=all\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/files?status=active\",\"timestamp\":\"2025-12-13 21:47:54\"}', '2025-12-13 21:47:54'),
(399, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/\",\"url\":\"\\/admin\\/projects\\/proshare\\/\",\"referer\":null,\"timestamp\":\"2025-12-13 21:48:03\"}', '2025-12-13 21:48:03'),
(400, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/files\",\"url\":\"\\/admin\\/projects\\/proshare\\/files\",\"referer\":null,\"timestamp\":\"2025-12-13 21:48:08\"}', '2025-12-13 21:48:08'),
(401, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/files?status=all\",\"url\":\"\\/admin\\/projects\\/proshare\\/files?status=all\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/files?status=active\",\"timestamp\":\"2025-12-13 21:48:18\"}', '2025-12-13 21:48:18'),
(402, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/file-activity\",\"url\":\"\\/admin\\/projects\\/proshare\\/file-activity\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/files?status=all\",\"timestamp\":\"2025-12-13 21:48:21\"}', '2025-12-13 21:48:21'),
(403, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/file-activity\",\"url\":\"\\/admin\\/projects\\/proshare\\/file-activity\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/files?status=all\",\"timestamp\":\"2025-12-13 21:48:50\"}', '2025-12-13 21:48:50'),
(404, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/texts\",\"url\":\"\\/admin\\/projects\\/proshare\\/texts\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/file-activity\",\"timestamp\":\"2025-12-13 21:49:24\"}', '2025-12-13 21:49:24'),
(405, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/security\",\"url\":\"\\/admin\\/projects\\/proshare\\/security\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/texts\",\"timestamp\":\"2025-12-13 21:49:40\"}', '2025-12-13 21:49:40'),
(406, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/server-health\",\"url\":\"\\/admin\\/projects\\/proshare\\/server-health\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/security\",\"timestamp\":\"2025-12-13 21:49:51\"}', '2025-12-13 21:49:51'),
(407, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/storage\",\"url\":\"\\/admin\\/projects\\/proshare\\/storage\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/server-health\",\"timestamp\":\"2025-12-13 21:49:54\"}', '2025-12-13 21:49:54'),
(408, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/audit-trail\",\"url\":\"\\/admin\\/projects\\/proshare\\/audit-trail\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/storage\",\"timestamp\":\"2025-12-13 21:49:58\"}', '2025-12-13 21:49:58'),
(409, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/notifications\",\"url\":\"\\/admin\\/projects\\/proshare\\/notifications\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/storage\",\"timestamp\":\"2025-12-13 21:50:25\"}', '2025-12-13 21:50:25'),
(410, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/analytics\",\"url\":\"\\/admin\\/projects\\/proshare\\/analytics\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/notifications\",\"timestamp\":\"2025-12-13 21:50:34\"}', '2025-12-13 21:50:34'),
(411, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/analytics\",\"url\":\"\\/admin\\/projects\\/proshare\\/analytics\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/notifications\",\"timestamp\":\"2025-12-13 21:51:29\"}', '2025-12-13 21:51:29'),
(412, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/preview\\/JbmTtm6N\",\"url\":\"\\/projects\\/proshare\\/preview\\/JbmTtm6N\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/dashboard\",\"timestamp\":\"2025-12-13 21:52:12\"}', '2025-12-13 21:52:12'),
(413, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/preview\\/JbmTtm6N\",\"url\":\"\\/projects\\/proshare\\/preview\\/JbmTtm6N\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/dashboard\",\"timestamp\":\"2025-12-13 21:52:12\"}', '2025-12-13 21:52:12'),
(414, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/download\\/JbmTtm6N\",\"url\":\"\\/projects\\/proshare\\/download\\/JbmTtm6N\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/preview\\/JbmTtm6N\",\"timestamp\":\"2025-12-13 21:52:13\"}', '2025-12-13 21:52:13'),
(415, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/download\\/JbmTtm6N\",\"url\":\"\\/projects\\/proshare\\/download\\/JbmTtm6N\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/preview\\/JbmTtm6N\",\"timestamp\":\"2025-12-13 21:52:13\"}', '2025-12-13 21:52:13'),
(416, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/preview\\/JbmTtm6N\",\"url\":\"\\/projects\\/proshare\\/preview\\/JbmTtm6N\",\"referer\":null,\"timestamp\":\"2025-12-13 21:52:19\"}', '2025-12-13 21:52:19'),
(417, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/preview\\/JbmTtm6N\",\"url\":\"\\/projects\\/proshare\\/preview\\/JbmTtm6N\",\"referer\":null,\"timestamp\":\"2025-12-13 21:52:19\"}', '2025-12-13 21:52:19'),
(418, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/download\\/JbmTtm6N\",\"url\":\"\\/projects\\/proshare\\/download\\/JbmTtm6N\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/preview\\/JbmTtm6N\",\"timestamp\":\"2025-12-13 21:53:28\"}', '2025-12-13 21:53:28'),
(419, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/download\\/JbmTtm6N\",\"url\":\"\\/projects\\/proshare\\/download\\/JbmTtm6N\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/preview\\/JbmTtm6N\",\"timestamp\":\"2025-12-13 21:53:28\"}', '2025-12-13 21:53:28'),
(420, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/dashboard\",\"url\":\"\\/projects\\/proshare\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/upload\",\"timestamp\":\"2025-12-13 21:53:34\"}', '2025-12-13 21:53:34'),
(421, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/dashboard\",\"url\":\"\\/projects\\/proshare\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/upload\",\"timestamp\":\"2025-12-13 21:53:34\"}', '2025-12-13 21:53:34');
INSERT INTO `analytics_events` (`id`, `project`, `resource_type`, `resource_id`, `event_type`, `user_id`, `ip_address`, `user_agent`, `browser`, `platform`, `country`, `metadata`, `created_at`) VALUES
(422, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/text\",\"url\":\"\\/projects\\/proshare\\/text\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/dashboard\",\"timestamp\":\"2025-12-13 21:53:40\"}', '2025-12-13 21:53:40'),
(423, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/text\",\"url\":\"\\/projects\\/proshare\\/text\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/dashboard\",\"timestamp\":\"2025-12-13 21:53:40\"}', '2025-12-13 21:53:40'),
(424, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/files\",\"url\":\"\\/projects\\/proshare\\/files\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/text\",\"timestamp\":\"2025-12-13 21:53:44\"}', '2025-12-13 21:53:44'),
(425, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/files\",\"url\":\"\\/projects\\/proshare\\/files\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/text\",\"timestamp\":\"2025-12-13 21:53:44\"}', '2025-12-13 21:53:44'),
(426, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/dashboard\",\"url\":\"\\/projects\\/proshare\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/files\",\"timestamp\":\"2025-12-13 21:53:53\"}', '2025-12-13 21:53:53'),
(427, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/dashboard\",\"url\":\"\\/projects\\/proshare\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/files\",\"timestamp\":\"2025-12-13 21:53:53\"}', '2025-12-13 21:53:53'),
(428, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/t\\/CMFqrs3W\",\"url\":\"\\/t\\/CMFqrs3W\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/dashboard\",\"timestamp\":\"2025-12-13 21:53:56\"}', '2025-12-13 21:53:56'),
(429, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/files\",\"url\":\"\\/projects\\/proshare\\/files\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/text\",\"timestamp\":\"2025-12-13 21:57:01\"}', '2025-12-13 21:57:01'),
(430, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/files\",\"url\":\"\\/projects\\/proshare\\/files\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/text\",\"timestamp\":\"2025-12-13 21:57:01\"}', '2025-12-13 21:57:01'),
(431, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/files\",\"timestamp\":\"2025-12-13 21:57:02\"}', '2025-12-13 21:57:02'),
(432, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/files\",\"timestamp\":\"2025-12-13 21:57:02\"}', '2025-12-13 21:57:02'),
(433, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/files\",\"timestamp\":\"2025-12-13 21:57:05\"}', '2025-12-13 21:57:05'),
(434, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/\",\"url\":\"\\/projects\\/imgtxt\\/\",\"referer\":null,\"timestamp\":\"2025-12-13 21:57:07\"}', '2025-12-13 21:57:07'),
(435, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/settings\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/analytics\",\"timestamp\":\"2025-12-13 21:57:25\"}', '2025-12-13 21:57:25'),
(436, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/settings\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/settings\",\"timestamp\":\"2025-12-13 21:57:30\"}', '2025-12-13 21:57:30'),
(437, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/settings\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/settings\",\"timestamp\":\"2025-12-13 21:57:31\"}', '2025-12-13 21:57:31'),
(438, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/settings\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/settings\",\"timestamp\":\"2025-12-13 21:57:35\"}', '2025-12-13 21:57:35'),
(439, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/settings\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/settings\",\"timestamp\":\"2025-12-13 21:57:40\"}', '2025-12-13 21:57:40'),
(440, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/settings\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/settings\",\"timestamp\":\"2025-12-13 21:57:40\"}', '2025-12-13 21:57:40'),
(441, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/\",\"referer\":null,\"timestamp\":\"2025-12-13 21:57:49\"}', '2025-12-13 21:57:49'),
(442, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/jobs\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/jobs\",\"referer\":null,\"timestamp\":\"2025-12-13 21:58:58\"}', '2025-12-13 21:58:58'),
(443, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/activity?job_id=13\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/activity?job_id=13\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/jobs\",\"timestamp\":\"2025-12-13 21:59:02\"}', '2025-12-13 21:59:02'),
(444, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/activity?job_id=13\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/activity?job_id=13\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/jobs\",\"timestamp\":\"2025-12-13 21:59:09\"}', '2025-12-13 21:59:09'),
(445, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/activity?job_id=9\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/activity?job_id=9\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/jobs\",\"timestamp\":\"2025-12-13 21:59:26\"}', '2025-12-13 21:59:26'),
(446, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/jobs\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/jobs\",\"referer\":null,\"timestamp\":\"2025-12-13 21:59:28\"}', '2025-12-13 21:59:28'),
(447, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/activity\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/activity\",\"referer\":null,\"timestamp\":\"2025-12-13 21:59:52\"}', '2025-12-13 21:59:52'),
(448, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/activity\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/activity\",\"referer\":null,\"timestamp\":\"2025-12-13 22:00:13\"}', '2025-12-13 22:00:13'),
(449, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/jobs?user_id=3\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/jobs?user_id=3\",\"referer\":null,\"timestamp\":\"2025-12-13 22:00:33\"}', '2025-12-13 22:00:33'),
(450, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/activity?job_id=13\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/activity?job_id=13\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/jobs?user_id=3\",\"timestamp\":\"2025-12-13 22:00:37\"}', '2025-12-13 22:00:37'),
(451, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/statistics\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/statistics\",\"referer\":null,\"timestamp\":\"2025-12-13 22:02:07\"}', '2025-12-13 22:02:07'),
(452, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/\",\"timestamp\":\"2025-12-13 22:03:31\"}', '2025-12-13 22:03:31'),
(453, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2025-12-13 22:03:44\"}', '2025-12-13 22:03:44'),
(454, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2025-12-13 22:06:36\"}', '2025-12-13 22:06:36'),
(455, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2025-12-13 22:07:21\"}', '2025-12-13 22:07:21'),
(456, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2025-12-13 22:08:26\"}', '2025-12-13 22:08:26'),
(457, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/profile\",\"url\":\"\\/profile\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2025-12-13 22:08:31\"}', '2025-12-13 22:08:31'),
(458, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/profile\",\"url\":\"\\/profile\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/profile\",\"timestamp\":\"2025-12-13 22:08:42\"}', '2025-12-13 22:08:42'),
(459, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/profile\",\"url\":\"\\/profile\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/profile\",\"timestamp\":\"2025-12-13 22:08:43\"}', '2025-12-13 22:08:43'),
(460, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/profile\",\"url\":\"\\/profile\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/profile\",\"timestamp\":\"2025-12-13 22:08:46\"}', '2025-12-13 22:08:46'),
(461, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/profile\",\"url\":\"\\/profile\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/profile\",\"timestamp\":\"2025-12-13 22:08:52\"}', '2025-12-13 22:08:52'),
(462, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/profile\",\"url\":\"\\/profile\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/profile\",\"timestamp\":\"2025-12-13 22:08:53\"}', '2025-12-13 22:08:53'),
(463, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/profile\",\"url\":\"\\/profile\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/profile\",\"timestamp\":\"2025-12-13 22:09:02\"}', '2025-12-13 22:09:02'),
(464, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/profile\",\"url\":\"\\/profile\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/profile\",\"timestamp\":\"2025-12-13 22:09:03\"}', '2025-12-13 22:09:03'),
(465, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/profile\",\"url\":\"\\/profile\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/profile\",\"timestamp\":\"2025-12-13 22:09:11\"}', '2025-12-13 22:09:11'),
(466, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/activity\",\"url\":\"\\/activity\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/profile\",\"timestamp\":\"2025-12-13 22:09:18\"}', '2025-12-13 22:09:18'),
(467, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/profile\",\"url\":\"\\/profile\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/profile\",\"timestamp\":\"2025-12-13 22:10:04\"}', '2025-12-13 22:10:04'),
(468, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/profile\",\"url\":\"\\/profile\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/profile\",\"timestamp\":\"2025-12-13 22:10:07\"}', '2025-12-13 22:10:07'),
(469, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/profile\",\"url\":\"\\/profile\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2025-12-13 22:10:09\"}', '2025-12-13 22:10:09'),
(470, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/security\",\"url\":\"\\/security\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2025-12-13 22:10:13\"}', '2025-12-13 22:10:13'),
(471, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/activity\",\"url\":\"\\/activity\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2025-12-13 22:10:17\"}', '2025-12-13 22:10:17'),
(472, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/activity\",\"url\":\"\\/activity\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2025-12-13 22:10:30\"}', '2025-12-13 22:10:30'),
(473, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/\",\"url\":\"\\/projects\\/imgtxt\\/\",\"referer\":null,\"timestamp\":\"2025-12-13 22:10:44\"}', '2025-12-13 22:10:44'),
(474, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2025-12-13 22:10:47\"}', '2025-12-13 22:10:47'),
(475, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2025-12-13 22:15:32\"}', '2025-12-13 22:15:32'),
(476, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/statistics\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/statistics\",\"referer\":null,\"timestamp\":\"2025-12-13 22:17:09\"}', '2025-12-13 22:17:09'),
(477, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/users\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/users\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/statistics\",\"timestamp\":\"2025-12-13 22:17:29\"}', '2025-12-13 22:17:29'),
(478, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/activity\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/activity\",\"referer\":null,\"timestamp\":\"2025-12-13 22:17:52\"}', '2025-12-13 22:17:52'),
(479, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/jobs?user_id=3\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/jobs?user_id=3\",\"referer\":null,\"timestamp\":\"2025-12-13 22:18:42\"}', '2025-12-13 22:18:42'),
(480, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/activity?job_id=13\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/activity?job_id=13\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/jobs?user_id=3\",\"timestamp\":\"2025-12-13 22:18:49\"}', '2025-12-13 22:18:49'),
(481, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/jobs\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/jobs\",\"referer\":null,\"timestamp\":\"2025-12-13 22:19:07\"}', '2025-12-13 22:19:07'),
(482, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/activity?job_id=13\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/activity?job_id=13\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/jobs\",\"timestamp\":\"2025-12-13 22:19:10\"}', '2025-12-13 22:19:10'),
(483, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/activity?job_id=13\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/activity?job_id=13\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/jobs\",\"timestamp\":\"2025-12-13 22:24:50\"}', '2025-12-13 22:24:50'),
(484, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/settings\",\"url\":\"\\/admin\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/activity?job_id=13\",\"timestamp\":\"2025-12-13 22:33:11\"}', '2025-12-13 22:33:11'),
(485, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\\/activity?job_id=13\",\"url\":\"\\/admin\\/projects\\/imgtxt\\/activity?job_id=13\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/jobs\",\"timestamp\":\"2025-12-13 22:33:15\"}', '2025-12-13 22:33:15'),
(486, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/home-content\",\"url\":\"\\/admin\\/home-content\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/imgtxt\\/activity?job_id=13\",\"timestamp\":\"2025-12-13 22:33:20\"}', '2025-12-13 22:33:20'),
(487, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/settings\\/maintenance\",\"url\":\"\\/admin\\/settings\\/maintenance\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/home-content\",\"timestamp\":\"2025-12-13 22:33:35\"}', '2025-12-13 22:33:35'),
(488, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/settings\\/features\",\"url\":\"\\/admin\\/settings\\/features\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/home-content\",\"timestamp\":\"2025-12-13 22:33:44\"}', '2025-12-13 22:33:44'),
(489, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:3d9f:4424:1c5c:e7ef:6a80', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-13 23:58:36\"}', '2025-12-13 23:58:36'),
(490, 'platform', 'page', 0, 'page_visit', NULL, '173.239.240.28', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', 'Chrome', 'Linux', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-14 14:49:37\"}', '2025-12-14 14:49:37'),
(491, 'platform', 'page', 0, 'page_visit', NULL, '74.7.241.51', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GPTBot/1.3; +https://openai.com/gptbot)', 'Other', 'Other', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-14 20:18:17\"}', '2025-12-14 20:18:17'),
(492, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc1:2c2:1014:8b40:5410:1e68', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-14 22:16:42\"}', '2025-12-14 22:16:42'),
(493, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc1:2c2:1014:8b40:5410:1e68', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2025-12-14 22:16:46\"}', '2025-12-14 22:16:46'),
(494, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc1:2c2:1014:8b40:5410:1e68', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/login\",\"timestamp\":\"2025-12-14 22:17:07\"}', '2025-12-14 22:17:07'),
(495, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc1:2c2:1014:8b40:5410:1e68', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2025-12-14 22:17:07\",\"login_method\":\"standard\"}', '2025-12-14 22:17:07'),
(496, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc1:2c2:1014:8b40:5410:1e68', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/login\",\"timestamp\":\"2025-12-14 22:17:08\"}', '2025-12-14 22:17:08'),
(497, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc1:2c2:1014:8b40:5410:1e68', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2025-12-14 22:17:30\"}', '2025-12-14 22:17:30'),
(498, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc1:2c2:1014:8b40:5410:1e68', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2025-12-14 22:17:31\"}', '2025-12-14 22:17:31'),
(499, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc1:2c2:1014:8b40:5410:1e68', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2025-12-14 22:22:43\"}', '2025-12-14 22:22:43'),
(500, 'platform', 'page', 0, 'page_visit', NULL, '205.210.31.180', NULL, 'Other', 'Other', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-15 04:00:27\"}', '2025-12-15 04:00:27'),
(501, 'platform', 'page', 0, 'page_visit', NULL, '45.131.155.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', 'Edge', 'macOS', 'JP', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-15 08:00:30\"}', '2025-12-15 08:00:30'),
(502, 'platform', 'page', 0, 'page_visit', NULL, '45.131.155.101', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'Chrome', 'Linux', 'JP', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-15 10:21:55\"}', '2025-12-15 10:21:55'),
(503, 'platform', 'page', 0, 'page_visit', NULL, '205.210.31.168', NULL, 'Other', 'Other', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-15 14:05:25\"}', '2025-12-15 14:05:25'),
(504, 'platform', 'page', 0, 'page_visit', NULL, '45.131.155.102', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) obsidian/1.5.8 Chrome/120.0.6099.283 Electron/28.2.3 Safari/537.36', 'Chrome', 'Linux', 'JP', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-16 11:57:41\"}', '2025-12-16 11:57:41'),
(505, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:3d37:2ee:34a5:b0a0:eab5:e49e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-16 14:04:12\"}', '2025-12-16 14:04:12'),
(506, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:3d37:2ee:34a5:b0a0:eab5:e49e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2025-12-16 14:04:27\"}', '2025-12-16 14:04:27'),
(507, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:3d37:2ee:34a5:b0a0:eab5:e49e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2025-12-16 14:04:41\"}', '2025-12-16 14:04:41'),
(508, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:3d37:2ee:34a5:b0a0:eab5:e49e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2025-12-16 14:04:41\"}', '2025-12-16 14:04:41'),
(509, 'platform', 'page', 0, 'page_visit', NULL, '18.219.233.85', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36 Assetnote/1.0.0', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-16 15:04:07\"}', '2025-12-16 15:04:07'),
(510, 'platform', 'page', 0, 'page_visit', NULL, '18.222.48.171', 'curl/8.3.0', 'Other', 'Other', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-16 20:29:11\"}', '2025-12-16 20:29:11'),
(511, 'platform', 'page', 0, 'page_visit', NULL, '2a14:7c1::2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.3', 'Chrome', 'Windows', 'NL', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-17 05:02:56\"}', '2025-12-17 05:02:56'),
(512, 'platform', 'page', 0, 'page_visit', NULL, '3.138.185.30', 'cypex.ai/scanning Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Chrome/126.0.0.0 Safari/537.36', 'Chrome', 'macOS', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-17 05:10:48\"}', '2025-12-17 05:10:48'),
(513, 'platform', 'page', 0, 'page_visit', NULL, '3.138.185.30', 'cypex.ai/scanning Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Chrome/126.0.0.0 Safari/537.36', 'Chrome', 'macOS', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-17 05:40:53\"}', '2025-12-17 05:40:53'),
(514, 'platform', 'page', 0, 'page_visit', NULL, '3.138.185.30', 'cypex.ai/scanning Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Chrome/126.0.0.0 Safari/537.36', 'Chrome', 'macOS', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-17 05:40:53\"}', '2025-12-17 05:40:53'),
(515, 'platform', 'page', 0, 'page_visit', NULL, '74.7.227.153', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GPTBot/1.3; +https://openai.com/gptbot)', 'Other', 'Other', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-17 11:03:10\"}', '2025-12-17 11:03:10'),
(516, 'platform', 'page', 0, 'page_visit', NULL, '44.234.84.130', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36 Assetnote/1.0.0', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-18 01:47:30\"}', '2025-12-18 01:47:30'),
(517, 'platform', 'page', 0, 'page_visit', NULL, '45.131.155.102', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Safari/605.1.15', 'Safari', 'macOS', 'JP', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-18 09:28:56\"}', '2025-12-18 09:28:56'),
(518, 'platform', 'page', 0, 'page_visit', NULL, '45.131.155.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.7204.93 Safari/537.36', 'Chrome', 'Windows', 'JP', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-18 10:51:42\"}', '2025-12-18 10:51:42'),
(519, 'platform', 'page', 0, 'page_visit', NULL, '45.131.155.102', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36', 'Chrome', 'Linux', 'JP', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-18 16:00:05\"}', '2025-12-18 16:00:05'),
(520, 'platform', 'page', 0, 'page_visit', NULL, '45.138.16.119', NULL, 'Other', 'Other', 'XX', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-18 16:18:58\"}', '2025-12-18 16:18:58'),
(521, 'platform', 'page', 0, 'page_visit', NULL, '170.64.205.70', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'AU', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-18 20:03:18\"}', '2025-12-18 20:03:18'),
(522, 'platform', 'page', 0, 'page_visit', NULL, '170.64.205.70', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'AU', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-18 20:03:20\"}', '2025-12-18 20:03:20'),
(523, 'platform', 'page', 0, 'page_visit', NULL, '74.7.242.25', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GPTBot/1.3; +https://openai.com/gptbot)', 'Other', 'Other', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-19 05:38:13\"}', '2025-12-19 05:38:13'),
(524, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.160', NULL, 'Other', 'Other', 'NL', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-19 12:42:28\"}', '2025-12-19 12:42:28'),
(525, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.160', NULL, 'Other', 'Other', 'NL', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-19 12:42:30\"}', '2025-12-19 12:42:30'),
(526, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.160', NULL, 'Other', 'Other', 'NL', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-19 12:42:49\"}', '2025-12-19 12:42:49'),
(527, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.160', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-19 12:42:51\"}', '2025-12-19 12:42:51'),
(528, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.160', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/register\",\"url\":\"\\/register\",\"referer\":null,\"timestamp\":\"2025-12-19 12:42:52\"}', '2025-12-19 12:42:52'),
(529, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.160', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":null,\"timestamp\":\"2025-12-19 12:42:56\"}', '2025-12-19 12:42:56'),
(530, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.160', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login?redirect=\\/imgtxt\",\"url\":\"\\/login?redirect=\\/imgtxt\",\"referer\":null,\"timestamp\":\"2025-12-19 12:42:56\"}', '2025-12-19 12:42:56'),
(531, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.160', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login?redirect=\\/qr\",\"url\":\"\\/login?redirect=\\/qr\",\"referer\":null,\"timestamp\":\"2025-12-19 12:42:56\"}', '2025-12-19 12:42:56'),
(532, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.160', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login?redirect=\\/codexpro\",\"url\":\"\\/login?redirect=\\/codexpro\",\"referer\":null,\"timestamp\":\"2025-12-19 12:42:56\"}', '2025-12-19 12:42:56'),
(533, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.160', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login?redirect=\\/devzone\",\"url\":\"\\/login?redirect=\\/devzone\",\"referer\":null,\"timestamp\":\"2025-12-19 12:42:56\"}', '2025-12-19 12:42:56'),
(534, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.160', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login?redirect=\\/proshare\",\"url\":\"\\/login?redirect=\\/proshare\",\"referer\":null,\"timestamp\":\"2025-12-19 12:42:56\"}', '2025-12-19 12:42:56'),
(535, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.160', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login?redirect=\\/resumex\",\"url\":\"\\/login?redirect=\\/resumex\",\"referer\":null,\"timestamp\":\"2025-12-19 12:42:56\"}', '2025-12-19 12:42:56'),
(536, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.160', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login?redirect=\\/qr\",\"url\":\"\\/login?redirect=\\/qr\",\"referer\":null,\"timestamp\":\"2025-12-19 12:43:02\"}', '2025-12-19 12:43:02'),
(537, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.160', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login?redirect=\\/imgtxt\",\"url\":\"\\/login?redirect=\\/imgtxt\",\"referer\":null,\"timestamp\":\"2025-12-19 12:43:02\"}', '2025-12-19 12:43:02'),
(538, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.160', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login?redirect=\\/devzone\",\"url\":\"\\/login?redirect=\\/devzone\",\"referer\":null,\"timestamp\":\"2025-12-19 12:43:02\"}', '2025-12-19 12:43:02'),
(539, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.160', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":null,\"timestamp\":\"2025-12-19 12:43:02\"}', '2025-12-19 12:43:02'),
(540, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.160', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login?redirect=\\/proshare\",\"url\":\"\\/login?redirect=\\/proshare\",\"referer\":null,\"timestamp\":\"2025-12-19 12:43:02\"}', '2025-12-19 12:43:02'),
(541, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.160', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login?redirect=\\/resumex\",\"url\":\"\\/login?redirect=\\/resumex\",\"referer\":null,\"timestamp\":\"2025-12-19 12:43:02\"}', '2025-12-19 12:43:02'),
(542, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.160', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login?redirect=\\/codexpro\",\"url\":\"\\/login?redirect=\\/codexpro\",\"referer\":null,\"timestamp\":\"2025-12-19 12:43:02\"}', '2025-12-19 12:43:02'),
(543, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.160', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":null,\"timestamp\":\"2025-12-19 12:43:07\"}', '2025-12-19 12:43:07'),
(544, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.160', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login?redirect=\\/qr\",\"url\":\"\\/login?redirect=\\/qr\",\"referer\":null,\"timestamp\":\"2025-12-19 12:43:07\"}', '2025-12-19 12:43:07'),
(545, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.160', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login?redirect=\\/devzone\",\"url\":\"\\/login?redirect=\\/devzone\",\"referer\":null,\"timestamp\":\"2025-12-19 12:43:07\"}', '2025-12-19 12:43:07'),
(546, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.160', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login?redirect=\\/imgtxt\",\"url\":\"\\/login?redirect=\\/imgtxt\",\"referer\":null,\"timestamp\":\"2025-12-19 12:43:07\"}', '2025-12-19 12:43:07'),
(547, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.160', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login?redirect=\\/proshare\",\"url\":\"\\/login?redirect=\\/proshare\",\"referer\":null,\"timestamp\":\"2025-12-19 12:43:07\"}', '2025-12-19 12:43:07'),
(548, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.160', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login?redirect=\\/resumex\",\"url\":\"\\/login?redirect=\\/resumex\",\"referer\":null,\"timestamp\":\"2025-12-19 12:43:07\"}', '2025-12-19 12:43:07'),
(549, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.160', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login?redirect=\\/codexpro\",\"url\":\"\\/login?redirect=\\/codexpro\",\"referer\":null,\"timestamp\":\"2025-12-19 12:43:07\"}', '2025-12-19 12:43:07'),
(550, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.160', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/?phpinfo=1\",\"url\":\"\\/?phpinfo=1\",\"referer\":null,\"timestamp\":\"2025-12-19 12:46:37\"}', '2025-12-19 12:46:37'),
(551, 'platform', 'page', 0, 'page_visit', NULL, '195.24.236.147', NULL, 'Other', 'Other', 'NL', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-19 21:01:46\"}', '2025-12-19 21:01:46');
INSERT INTO `analytics_events` (`id`, `project`, `resource_type`, `resource_id`, `event_type`, `user_id`, `ip_address`, `user_agent`, `browser`, `platform`, `country`, `metadata`, `created_at`) VALUES
(552, 'platform', 'page', 0, 'page_visit', NULL, '165.227.142.210', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'Chrome', 'Linux', 'DE', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-20 06:55:55\"}', '2025-12-20 06:55:55'),
(553, 'platform', 'page', 0, 'page_visit', NULL, '165.227.142.210', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'Chrome', 'Linux', 'DE', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-20 06:55:56\"}', '2025-12-20 06:55:56'),
(554, 'platform', 'page', 0, 'page_visit', NULL, '193.26.115.28', NULL, 'Other', 'Other', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-20 14:13:32\"}', '2025-12-20 14:13:32'),
(555, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc3:8869:8dca:b93e:966e:5f0e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-20 15:25:04\"}', '2025-12-20 15:25:04'),
(556, 'platform', 'page', 0, 'page_visit', NULL, '205.210.31.183', NULL, 'Other', 'Other', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-21 01:16:19\"}', '2025-12-21 01:16:19'),
(557, 'platform', 'page', 0, 'page_visit', NULL, '74.7.242.25', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GPTBot/1.3; +https://openai.com/gptbot)', 'Other', 'Other', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-21 12:09:44\"}', '2025-12-21 12:09:44'),
(558, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc0:efdd:79c3:358a:c7a2:ee4f', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-21 17:28:53\"}', '2025-12-21 17:28:53'),
(559, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc0:efdd:452b:8073:d590:6cc1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-21 17:34:34\"}', '2025-12-21 17:34:34'),
(560, 'platform', 'page', 0, 'page_visit', NULL, '161.97.175.20', NULL, 'Other', 'Other', 'FR', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-22 11:47:30\"}', '2025-12-22 11:47:30'),
(561, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc3:ee7b:6dcd:5ebe:76b8:f08a', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-22 15:38:13\"}', '2025-12-22 15:38:13'),
(562, 'platform', 'page', 0, 'page_visit', NULL, '2a14:7c1::2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.3', 'Chrome', 'Windows', 'NL', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-23 06:01:47\"}', '2025-12-23 06:01:47'),
(563, 'platform', 'page', 0, 'page_visit', NULL, '3.146.111.124', 'cypex.ai/scanning Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Chrome/126.0.0.0 Safari/537.36', 'Chrome', 'macOS', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-23 12:44:47\"}', '2025-12-23 12:44:47'),
(564, 'platform', 'page', 0, 'page_visit', NULL, '3.146.111.124', 'cypex.ai/scanning Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Chrome/126.0.0.0 Safari/537.36', 'Chrome', 'macOS', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-23 12:44:48\"}', '2025-12-23 12:44:48'),
(565, 'platform', 'page', 0, 'page_visit', NULL, '62.60.131.162', 'Go-http-client/1.1', 'Other', 'Other', 'IR', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-23 13:09:43\"}', '2025-12-23 13:09:43'),
(566, 'platform', 'page', 0, 'page_visit', NULL, '74.7.242.25', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GPTBot/1.3; +https://openai.com/gptbot)', 'Other', 'Other', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-23 18:07:56\"}', '2025-12-23 18:07:56'),
(567, 'platform', 'page', 0, 'page_visit', NULL, '213.209.159.151', 'Go-http-client/1.1', 'Other', 'Other', 'DE', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-24 07:17:29\"}', '2025-12-24 07:17:29'),
(568, 'platform', 'page', 0, 'page_visit', NULL, '213.209.159.151', 'Go-http-client/1.1', 'Other', 'Other', 'DE', '{\"page\":\"\\/?phpinfo=1\",\"url\":\"\\/?phpinfo=1\",\"referer\":null,\"timestamp\":\"2025-12-24 07:17:34\"}', '2025-12-24 07:17:34'),
(569, 'platform', 'page', 0, 'page_visit', NULL, '213.209.159.151', 'Go-http-client/1.1', 'Other', 'Other', 'DE', '{\"page\":\"\\/login?pp=enable&pp=env\",\"url\":\"\\/login?pp=enable&pp=env\",\"referer\":null,\"timestamp\":\"2025-12-24 07:17:35\"}', '2025-12-24 07:17:35'),
(570, 'platform', 'page', 0, 'page_visit', NULL, '213.209.159.151', 'Go-http-client/1.1', 'Other', 'Other', 'DE', '{\"page\":\"\\/?pp=env&pp=env\",\"url\":\"\\/?pp=env&pp=env\",\"referer\":null,\"timestamp\":\"2025-12-24 07:17:35\"}', '2025-12-24 07:17:35'),
(571, 'platform', 'page', 0, 'page_visit', NULL, '213.209.159.151', 'Go-http-client/1.1', 'Other', 'Other', 'DE', '{\"page\":\"\\/?pp=enable&pp=env\",\"url\":\"\\/?pp=enable&pp=env\",\"referer\":null,\"timestamp\":\"2025-12-24 07:17:35\"}', '2025-12-24 07:17:35'),
(572, 'platform', 'page', 0, 'page_visit', NULL, '213.209.159.151', 'Go-http-client/1.1', 'Other', 'Other', 'DE', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-24 07:17:35\"}', '2025-12-24 07:17:36'),
(573, 'platform', 'page', 0, 'page_visit', NULL, '213.209.159.151', 'Go-http-client/1.1', 'Other', 'Other', 'DE', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"http:\\/\\/test.mymultibranch.com\\/install\\/index.php\",\"timestamp\":\"2025-12-24 07:17:40\"}', '2025-12-24 07:17:40'),
(574, 'platform', 'page', 0, 'page_visit', NULL, '34.90.235.227', 'Scrapy/2.13.4 (+https://scrapy.org)', 'Other', 'Other', 'NL', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-24 07:38:56\"}', '2025-12-24 07:38:56'),
(575, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc3:ee7b:4cbc:fe6a:b6d4:2934', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-24 17:19:05\"}', '2025-12-24 17:19:05'),
(576, 'platform', 'page', 0, 'page_visit', NULL, '23.22.102.169', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36', 'Chrome', 'macOS', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-24 17:25:02\"}', '2025-12-24 17:25:02'),
(577, 'platform', 'page', 0, 'page_visit', NULL, '208.84.101.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-24 21:25:31\"}', '2025-12-24 21:25:31'),
(578, 'platform', 'page', 0, 'page_visit', NULL, '208.84.101.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-24 21:25:31\"}', '2025-12-24 21:25:31'),
(579, 'platform', 'page', 0, 'page_visit', NULL, '208.84.101.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-24 21:25:33\"}', '2025-12-24 21:25:33'),
(580, 'platform', 'page', 0, 'page_visit', NULL, '45.131.155.100', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'Chrome', 'macOS', 'JP', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-25 03:37:26\"}', '2025-12-25 03:37:26'),
(581, 'platform', 'page', 0, 'page_visit', NULL, '45.131.155.100', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36', 'Chrome', 'macOS', 'JP', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-25 09:48:14\"}', '2025-12-25 09:48:14'),
(582, 'platform', 'page', 0, 'page_visit', NULL, '208.84.101.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-25 18:17:24\"}', '2025-12-25 18:17:24'),
(583, 'platform', 'page', 0, 'page_visit', NULL, '208.84.101.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-25 18:17:25\"}', '2025-12-25 18:17:25'),
(584, 'platform', 'page', 0, 'page_visit', NULL, '208.84.101.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-25 18:17:27\"}', '2025-12-25 18:17:27'),
(585, 'platform', 'page', 0, 'page_visit', NULL, '45.131.155.101', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'Chrome', 'Linux', 'JP', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-25 21:28:59\"}', '2025-12-25 21:28:59'),
(586, 'platform', 'page', 0, 'page_visit', NULL, '208.84.101.251', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-26 01:59:33\"}', '2025-12-26 01:59:33'),
(587, 'platform', 'page', 0, 'page_visit', NULL, '208.84.101.251', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-26 01:59:34\"}', '2025-12-26 01:59:34'),
(588, 'platform', 'page', 0, 'page_visit', NULL, '208.84.101.251', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-26 01:59:36\"}', '2025-12-26 01:59:36'),
(589, 'platform', 'page', 0, 'page_visit', NULL, '45.88.186.197', NULL, 'Other', 'Other', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-26 10:10:34\"}', '2025-12-26 10:10:34'),
(590, 'platform', 'page', 0, 'page_visit', NULL, '142.248.80.123', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-26 11:55:00\"}', '2025-12-26 11:55:00'),
(591, 'platform', 'page', 0, 'page_visit', NULL, '142.248.80.123', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-26 11:55:01\"}', '2025-12-26 11:55:01'),
(592, 'platform', 'page', 0, 'page_visit', NULL, '142.248.80.123', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-26 11:55:02\"}', '2025-12-26 11:55:02'),
(593, 'platform', 'page', 0, 'page_visit', NULL, '45.88.186.148', NULL, 'Other', 'Other', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-26 13:46:16\"}', '2025-12-26 13:46:16'),
(594, 'platform', 'page', 0, 'page_visit', NULL, '208.84.101.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-26 13:49:56\"}', '2025-12-26 13:49:56'),
(595, 'platform', 'page', 0, 'page_visit', NULL, '208.84.101.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-26 13:49:57\"}', '2025-12-26 13:49:57'),
(596, 'platform', 'page', 0, 'page_visit', NULL, '208.84.101.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-26 13:49:59\"}', '2025-12-26 13:49:59'),
(597, 'platform', 'page', 0, 'page_visit', NULL, '208.84.101.251', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-27 01:25:24\"}', '2025-12-27 01:25:24'),
(598, 'platform', 'page', 0, 'page_visit', NULL, '208.84.101.251', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-27 01:25:25\"}', '2025-12-27 01:25:25'),
(599, 'platform', 'page', 0, 'page_visit', NULL, '208.84.101.251', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-27 01:25:27\"}', '2025-12-27 01:25:27'),
(600, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:2323:d969:a72:ae9d:747c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-27 01:40:31\"}', '2025-12-27 01:40:31'),
(601, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:2323:d969:a72:ae9d:747c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2025-12-27 01:41:46\"}', '2025-12-27 01:41:46'),
(602, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:2323:d969:a72:ae9d:747c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/login\",\"timestamp\":\"2025-12-27 01:42:01\"}', '2025-12-27 01:42:01'),
(603, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:2323:d969:a72:ae9d:747c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2025-12-27 01:42:01\",\"login_method\":\"standard\"}', '2025-12-27 01:42:01'),
(604, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:2323:d969:a72:ae9d:747c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/login\",\"timestamp\":\"2025-12-27 01:42:02\"}', '2025-12-27 01:42:02'),
(605, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:2323:d969:a72:ae9d:747c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/qr\\/generate\",\"url\":\"\\/projects\\/qr\\/generate\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/qr\\/\",\"timestamp\":\"2025-12-27 01:42:14\"}', '2025-12-27 01:42:14'),
(606, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:2323:d969:a72:ae9d:747c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/qr\\/generate\",\"url\":\"\\/projects\\/qr\\/generate\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/qr\\/generate\",\"timestamp\":\"2025-12-27 01:42:30\"}', '2025-12-27 01:42:30'),
(607, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:2323:d969:a72:ae9d:747c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/qr\\/generate\",\"url\":\"\\/projects\\/qr\\/generate\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/qr\\/generate\",\"timestamp\":\"2025-12-27 01:42:31\"}', '2025-12-27 01:42:31'),
(608, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:2323:d969:a72:ae9d:747c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/qr\\/history\",\"url\":\"\\/projects\\/qr\\/history\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/qr\\/\",\"timestamp\":\"2025-12-27 01:42:47\"}', '2025-12-27 01:42:47'),
(609, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:2323:d969:a72:ae9d:747c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/qr\\/\",\"timestamp\":\"2025-12-27 01:43:10\"}', '2025-12-27 01:43:10'),
(610, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:2323:d969:a72:ae9d:747c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/\",\"url\":\"\\/projects\\/proshare\\/\",\"referer\":null,\"timestamp\":\"2025-12-27 01:43:21\"}', '2025-12-27 01:43:21'),
(611, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:2323:d969:a72:ae9d:747c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/text\",\"url\":\"\\/projects\\/proshare\\/text\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/\",\"timestamp\":\"2025-12-27 01:43:27\"}', '2025-12-27 01:43:27'),
(612, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:2323:d969:a72:ae9d:747c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/text\",\"url\":\"\\/projects\\/proshare\\/text\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/\",\"timestamp\":\"2025-12-27 01:43:27\"}', '2025-12-27 01:43:27'),
(613, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:2323:d969:a72:ae9d:747c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/\",\"url\":\"\\/projects\\/imgtxt\\/\",\"referer\":null,\"timestamp\":\"2025-12-27 01:43:38\"}', '2025-12-27 01:43:38'),
(614, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:2323:d969:a72:ae9d:747c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/upload\",\"url\":\"\\/projects\\/imgtxt\\/upload\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/\",\"timestamp\":\"2025-12-27 01:43:41\"}', '2025-12-27 01:43:41'),
(615, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:2323:d969:a72:ae9d:747c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/upload\",\"url\":\"\\/projects\\/imgtxt\\/upload\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/\",\"timestamp\":\"2025-12-27 01:43:41\"}', '2025-12-27 01:43:41'),
(616, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:2323:d969:a72:ae9d:747c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/upload\",\"url\":\"\\/projects\\/imgtxt\\/upload\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/\",\"timestamp\":\"2025-12-27 01:43:41\"}', '2025-12-27 01:43:41'),
(617, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:2323:d969:a72:ae9d:747c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/upload\",\"url\":\"\\/projects\\/imgtxt\\/upload\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/\",\"timestamp\":\"2025-12-27 01:43:41\"}', '2025-12-27 01:43:41'),
(618, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:2323:d969:a72:ae9d:747c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/upload\",\"url\":\"\\/projects\\/imgtxt\\/upload\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/upload\",\"timestamp\":\"2025-12-27 01:43:49\"}', '2025-12-27 01:43:49'),
(619, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:2323:d969:a72:ae9d:747c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/upload\",\"url\":\"\\/projects\\/imgtxt\\/upload\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/upload\",\"timestamp\":\"2025-12-27 01:43:49\"}', '2025-12-27 01:43:49'),
(620, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:2323:d969:a72:ae9d:747c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/upload\",\"url\":\"\\/projects\\/imgtxt\\/upload\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/upload\",\"timestamp\":\"2025-12-27 01:43:59\"}', '2025-12-27 01:43:59'),
(621, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:2323:d969:a72:ae9d:747c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/upload\",\"url\":\"\\/projects\\/imgtxt\\/upload\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/upload\",\"timestamp\":\"2025-12-27 01:43:59\"}', '2025-12-27 01:43:59'),
(622, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:2323:d969:a72:ae9d:747c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/process\",\"url\":\"\\/projects\\/imgtxt\\/process\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/upload\",\"timestamp\":\"2025-12-27 01:44:00\"}', '2025-12-27 01:44:00'),
(623, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:2323:d969:a72:ae9d:747c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/process\",\"url\":\"\\/projects\\/imgtxt\\/process\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/upload\",\"timestamp\":\"2025-12-27 01:44:00\"}', '2025-12-27 01:44:00'),
(624, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:2323:d969:a72:ae9d:747c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/history\",\"url\":\"\\/projects\\/imgtxt\\/history\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/upload\",\"timestamp\":\"2025-12-27 01:44:21\"}', '2025-12-27 01:44:21'),
(625, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:2323:d969:a72:ae9d:747c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/history\",\"url\":\"\\/projects\\/imgtxt\\/history\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/upload\",\"timestamp\":\"2025-12-27 01:44:21\"}', '2025-12-27 01:44:21'),
(626, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:2323:d969:a72:ae9d:747c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/settings\",\"url\":\"\\/projects\\/imgtxt\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/history\",\"timestamp\":\"2025-12-27 01:44:23\"}', '2025-12-27 01:44:23'),
(627, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:2323:d969:a72:ae9d:747c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/settings\",\"url\":\"\\/projects\\/imgtxt\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/history\",\"timestamp\":\"2025-12-27 01:44:23\"}', '2025-12-27 01:44:23'),
(628, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:2323:d969:a72:ae9d:747c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/imgtxt\\/settings\",\"timestamp\":\"2025-12-27 01:46:34\"}', '2025-12-27 01:46:34'),
(629, 'platform', 'page', 0, 'page_visit', NULL, '142.248.80.123', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-27 11:30:35\"}', '2025-12-27 11:30:35'),
(630, 'platform', 'page', 0, 'page_visit', NULL, '142.248.80.123', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-27 11:30:36\"}', '2025-12-27 11:30:36'),
(631, 'platform', 'page', 0, 'page_visit', NULL, '142.248.80.123', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-27 11:30:38\"}', '2025-12-27 11:30:38'),
(632, 'platform', 'page', 0, 'page_visit', NULL, '195.24.236.147', NULL, 'Other', 'Other', 'NL', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-27 22:00:27\"}', '2025-12-27 22:00:27'),
(633, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-28 00:01:27\"}', '2025-12-28 00:01:27'),
(634, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2025-12-28 00:10:37\"}', '2025-12-28 00:10:37'),
(635, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/login\",\"timestamp\":\"2025-12-28 00:10:56\"}', '2025-12-28 00:10:56'),
(636, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2025-12-28 00:11:42\"}', '2025-12-28 00:11:42'),
(637, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/login\",\"timestamp\":\"2025-12-28 00:11:59\"}', '2025-12-28 00:11:59'),
(638, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2025-12-28 00:12:00\",\"login_method\":\"standard\"}', '2025-12-28 00:12:00'),
(639, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/login\",\"timestamp\":\"2025-12-28 00:12:01\"}', '2025-12-28 00:12:01'),
(640, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\",\"url\":\"\\/admin\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2025-12-28 00:12:05\"}', '2025-12-28 00:12:05'),
(641, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export\",\"url\":\"\\/admin\\/analytics\\/export\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\",\"timestamp\":\"2025-12-28 00:12:23\"}', '2025-12-28 00:12:23'),
(642, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-28&date_to=2025-12-28&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-28&date_to=2025-12-28&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-28 00:12:24\"}', '2025-12-28 00:12:24'),
(643, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-28&date_to=2025-12-28&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-11-28&date_to=2025-12-28&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-28 00:12:54\"}', '2025-12-28 00:12:54'),
(644, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/users\",\"url\":\"\\/admin\\/users\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2025-12-28 00:13:00\"}', '2025-12-28 00:13:00'),
(645, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/users\\/4\\/edit\",\"url\":\"\\/admin\\/users\\/4\\/edit\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/users\",\"timestamp\":\"2025-12-28 00:13:16\"}', '2025-12-28 00:13:16'),
(646, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/users\",\"url\":\"\\/admin\\/users\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/users\\/4\\/edit\",\"timestamp\":\"2025-12-28 00:13:25\"}', '2025-12-28 00:13:25'),
(647, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/users\",\"timestamp\":\"2025-12-28 00:13:28\"}', '2025-12-28 00:13:28'),
(648, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2025-12-28 00:13:30\"}', '2025-12-28 00:13:30'),
(649, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\",\"url\":\"\\/admin\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2025-12-28 00:13:36\"}', '2025-12-28 00:13:36'),
(650, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/codexpro\",\"url\":\"\\/admin\\/projects\\/codexpro\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\",\"timestamp\":\"2025-12-28 00:13:52\"}', '2025-12-28 00:13:52'),
(651, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/codexpro\",\"url\":\"\\/admin\\/projects\\/codexpro\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\",\"timestamp\":\"2025-12-28 00:13:58\"}', '2025-12-28 00:13:58'),
(652, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/codexpro\\/settings\",\"url\":\"\\/admin\\/projects\\/codexpro\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\",\"timestamp\":\"2025-12-28 00:14:01\"}', '2025-12-28 00:14:01'),
(653, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/codexpro\\/users\",\"url\":\"\\/admin\\/projects\\/codexpro\\/users\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\",\"timestamp\":\"2025-12-28 00:14:04\"}', '2025-12-28 00:14:04'),
(654, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/codexpro\\/templates\",\"url\":\"\\/admin\\/projects\\/codexpro\\/templates\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\",\"timestamp\":\"2025-12-28 00:14:07\"}', '2025-12-28 00:14:07'),
(655, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/dashboard\",\"url\":\"\\/projects\\/imgtxt\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\",\"timestamp\":\"2025-12-28 00:14:13\"}', '2025-12-28 00:14:13'),
(656, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/dashboard\",\"url\":\"\\/projects\\/imgtxt\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\",\"timestamp\":\"2025-12-28 00:14:13\"}', '2025-12-28 00:14:13'),
(657, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/batch\",\"url\":\"\\/projects\\/imgtxt\\/batch\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\",\"timestamp\":\"2025-12-28 00:14:17\"}', '2025-12-28 00:14:17'),
(658, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/batch\",\"url\":\"\\/projects\\/imgtxt\\/batch\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\",\"timestamp\":\"2025-12-28 00:14:17\"}', '2025-12-28 00:14:17'),
(659, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/\",\"url\":\"\\/projects\\/codexpro\\/\",\"referer\":null,\"timestamp\":\"2025-12-28 00:14:22\"}', '2025-12-28 00:14:22'),
(660, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/editor\",\"url\":\"\\/projects\\/codexpro\\/editor\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/\",\"timestamp\":\"2025-12-28 00:14:27\"}', '2025-12-28 00:14:27'),
(661, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/editor\",\"url\":\"\\/projects\\/codexpro\\/editor\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/\",\"timestamp\":\"2025-12-28 00:14:27\"}', '2025-12-28 00:14:27'),
(662, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/dashboard\",\"url\":\"\\/projects\\/codexpro\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/editor\",\"timestamp\":\"2025-12-28 00:14:39\"}', '2025-12-28 00:14:39'),
(663, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/dashboard\",\"url\":\"\\/projects\\/codexpro\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/editor\",\"timestamp\":\"2025-12-28 00:14:39\"}', '2025-12-28 00:14:39'),
(664, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/\",\"url\":\"\\/projects\\/codexpro\\/\",\"referer\":null,\"timestamp\":\"2025-12-28 00:20:59\"}', '2025-12-28 00:20:59'),
(665, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/\",\"url\":\"\\/projects\\/codexpro\\/\",\"referer\":null,\"timestamp\":\"2025-12-28 00:20:59\"}', '2025-12-28 00:20:59'),
(666, 'platform', 'page', 0, 'page_visit', NULL, '208.84.101.251', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-28 01:23:11\"}', '2025-12-28 01:23:11'),
(667, 'platform', 'page', 0, 'page_visit', NULL, '208.84.101.251', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-28 01:23:12\"}', '2025-12-28 01:23:12'),
(668, 'platform', 'page', 0, 'page_visit', NULL, '208.84.101.251', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-28 01:23:14\"}', '2025-12-28 01:23:14'),
(669, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/login?return=%2Fprojects%2Fcodexpro%2F\",\"url\":\"\\/login?return=%2Fprojects%2Fcodexpro%2F\",\"referer\":null,\"timestamp\":\"2025-12-28 05:53:40\"}', '2025-12-28 05:53:40'),
(670, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":null,\"timestamp\":\"2025-12-28 05:53:42\"}', '2025-12-28 05:53:42'),
(671, 'platform', 'page', 0, 'page_visit', NULL, '74.7.242.4', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GPTBot/1.3; +https://openai.com/gptbot)', 'Other', 'Other', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-28 06:49:15\"}', '2025-12-28 06:49:15'),
(672, 'platform', 'page', 0, 'page_visit', NULL, '142.248.80.123', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-28 10:55:33\"}', '2025-12-28 10:55:33'),
(673, 'platform', 'page', 0, 'page_visit', NULL, '142.248.80.123', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-28 10:55:34\"}', '2025-12-28 10:55:34'),
(674, 'platform', 'page', 0, 'page_visit', NULL, '142.248.80.123', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-28 10:55:36\"}', '2025-12-28 10:55:36'),
(675, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\",\"url\":\"\\/admin\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2025-12-28 11:49:45\"}', '2025-12-28 11:49:45'),
(676, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/logs\",\"url\":\"\\/admin\\/logs\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\",\"timestamp\":\"2025-12-28 11:49:51\"}', '2025-12-28 11:49:51'),
(677, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\",\"url\":\"\\/admin\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2025-12-28 11:49:52\"}', '2025-12-28 11:49:52'),
(678, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-29 04:01:51\"}', '2025-12-29 04:01:51'),
(679, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2025-12-29 04:02:59\"}', '2025-12-29 04:02:59'),
(680, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/login\",\"timestamp\":\"2025-12-29 04:03:11\"}', '2025-12-29 04:03:11'),
(681, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2025-12-29 04:03:12\",\"login_method\":\"standard\"}', '2025-12-29 04:03:12'),
(682, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/login\",\"timestamp\":\"2025-12-29 04:03:12\"}', '2025-12-29 04:03:12'),
(683, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\",\"url\":\"\\/admin\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2025-12-29 04:03:15\"}', '2025-12-29 04:03:15'),
(684, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/home-content\",\"url\":\"\\/admin\\/home-content\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\",\"timestamp\":\"2025-12-29 04:03:23\"}', '2025-12-29 04:03:23');
INSERT INTO `analytics_events` (`id`, `project`, `resource_type`, `resource_id`, `event_type`, `user_id`, `ip_address`, `user_agent`, `browser`, `platform`, `country`, `metadata`, `created_at`) VALUES
(685, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/home-content\\/hero\",\"url\":\"\\/admin\\/home-content\\/hero\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/home-content\",\"timestamp\":\"2025-12-29 04:03:41\"}', '2025-12-29 04:03:41'),
(686, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/home-content\",\"url\":\"\\/admin\\/home-content\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/home-content\",\"timestamp\":\"2025-12-29 04:03:42\"}', '2025-12-29 04:03:42'),
(687, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc0:5367:4932:b3d9:6795:4ae0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/home-content\",\"timestamp\":\"2025-12-29 04:03:45\"}', '2025-12-29 04:03:45'),
(688, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.174', 'Mozilla/5.0', 'Other', 'Other', 'NL', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-29 04:24:41\"}', '2025-12-29 04:24:41'),
(689, 'platform', 'page', 0, 'page_visit', NULL, '2a14:7c1::2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.3', 'Chrome', 'Windows', 'NL', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-29 04:29:38\"}', '2025-12-29 04:29:38'),
(690, 'platform', 'page', 0, 'page_visit', NULL, '18.224.192.118', 'cypex.ai/scanning Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Chrome/126.0.0.0 Safari/537.36', 'Chrome', 'macOS', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-29 06:49:09\"}', '2025-12-29 06:49:09'),
(691, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.95', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.2 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'NL', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 04:11:21\"}', '2025-12-30 04:11:21'),
(692, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.95', 'Mozilla/5.0 (X11; CrOS x86_64 14816.131.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36', 'Chrome', 'Other', 'NL', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 04:11:21\"}', '2025-12-30 04:11:21'),
(693, 'platform', 'page', 0, 'page_visit', NULL, '159.89.174.87', NULL, 'Other', 'Other', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 05:00:57\"}', '2025-12-30 05:00:57'),
(694, 'platform', 'page', 0, 'page_visit', NULL, '209.38.208.202', NULL, 'Other', 'Other', 'DE', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 05:00:57\"}', '2025-12-30 05:00:57'),
(695, 'platform', 'page', 0, 'page_visit', NULL, '165.227.173.41', NULL, 'Other', 'Other', 'DE', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 05:00:57\"}', '2025-12-30 05:00:57'),
(696, 'platform', 'page', 0, 'page_visit', NULL, '138.197.191.87', NULL, 'Other', 'Other', 'DE', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 05:00:57\"}', '2025-12-30 05:00:57'),
(697, 'platform', 'page', 0, 'page_visit', NULL, '2a03:b0c0:2:d0::1733:8001', NULL, 'Other', 'Other', 'NL', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 05:00:57\"}', '2025-12-30 05:00:57'),
(698, 'platform', 'page', 0, 'page_visit', NULL, '2a03:b0c0:2:d0::1773:1001', NULL, 'Other', 'Other', 'NL', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 05:00:57\"}', '2025-12-30 05:00:57'),
(699, 'platform', 'page', 0, 'page_visit', NULL, '2604:a880:cad:d0::d9d:e001', NULL, 'Other', 'Other', 'CA', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 05:00:57\"}', '2025-12-30 05:00:57'),
(700, 'platform', 'page', 0, 'page_visit', NULL, '2a03:b0c0:3:d0::fe3:3001', NULL, 'Other', 'Other', 'DE', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 05:00:57\"}', '2025-12-30 05:00:57'),
(701, 'platform', 'page', 0, 'page_visit', NULL, '159.89.174.87', 'Mozilla/5.0 (l9scan/2.0.736313e28383e21323e2430313; +https://leakix.net)', 'Other', 'Other', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 05:00:58\"}', '2025-12-30 05:00:58'),
(702, 'platform', 'page', 0, 'page_visit', NULL, '209.38.208.202', 'Mozilla/5.0 (l9scan/2.0.730313e2135313e27363e2237313; +https://leakix.net)', 'Other', 'Other', 'DE', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 05:00:58\"}', '2025-12-30 05:00:58'),
(703, 'platform', 'page', 0, 'page_visit', NULL, '2604:a880:cad:d0::d9d:e001', 'Mozilla/5.0 (l9scan/2.0.73168353a353138363a3a303330333a303037343a363036323; +https://leakix.net)', 'Other', 'Other', 'CA', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 05:00:58\"}', '2025-12-30 05:00:58'),
(704, 'platform', 'page', 0, 'page_visit', NULL, '2a03:b0c0:3:d0::fe3:3001', 'Mozilla/5.0 (l9scan/2.0.26637393a333433616a3a313330333a303037343a363036323; +https://leakix.net)', 'Other', 'Other', 'DE', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 05:00:58\"}', '2025-12-30 05:00:58'),
(705, 'platform', 'page', 0, 'page_visit', NULL, '103.196.9.22', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 05:01:23\"}', '2025-12-30 05:01:23'),
(706, 'platform', 'page', 0, 'page_visit', NULL, '103.4.251.46', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36', 'Chrome', 'Linux', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 05:01:24\"}', '2025-12-30 05:01:24'),
(707, 'platform', 'page', 0, 'page_visit', NULL, '103.196.9.22', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36', 'Chrome', 'macOS', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 05:01:26\"}', '2025-12-30 05:01:26'),
(708, 'platform', 'page', 0, 'page_visit', NULL, '159.89.174.87', 'Mozilla/5.0 (l9scan/2.0.736313e28383e21323e2430313; +https://leakix.net)', 'Other', 'Other', 'IN', '{\"page\":\"\\/?rest_route=\\/wp\\/v2\\/users\\/\",\"url\":\"\\/?rest_route=\\/wp\\/v2\\/users\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 05:01:31\"}', '2025-12-30 05:01:31'),
(709, 'platform', 'page', 0, 'page_visit', NULL, '209.38.208.202', 'Mozilla/5.0 (l9scan/2.0.730313e2135313e27363e2237313; +https://leakix.net)', 'Other', 'Other', 'DE', '{\"page\":\"\\/?rest_route=\\/wp\\/v2\\/users\\/\",\"url\":\"\\/?rest_route=\\/wp\\/v2\\/users\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 05:01:33\"}', '2025-12-30 05:01:33'),
(710, 'platform', 'page', 0, 'page_visit', NULL, '103.196.9.22', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36', 'Chrome', 'macOS', 'US', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":null,\"timestamp\":\"2025-12-30 05:01:33\"}', '2025-12-30 05:01:33'),
(711, 'platform', 'page', 0, 'page_visit', NULL, '103.196.9.22', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36', 'Chrome', 'macOS', 'US', '{\"page\":\"\\/register\",\"url\":\"\\/register\",\"referer\":null,\"timestamp\":\"2025-12-30 05:01:34\"}', '2025-12-30 05:01:34'),
(712, 'platform', 'page', 0, 'page_visit', NULL, '103.196.9.22', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36', 'Chrome', 'macOS', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 05:01:35\"}', '2025-12-30 05:01:35'),
(713, 'platform', 'page', 0, 'page_visit', NULL, '103.196.9.22', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36', 'Chrome', 'macOS', 'US', '{\"page\":\"\\/forgot-password\",\"url\":\"\\/forgot-password\",\"referer\":null,\"timestamp\":\"2025-12-30 05:01:39\"}', '2025-12-30 05:01:39'),
(714, 'platform', 'page', 0, 'page_visit', NULL, '104.197.69.115', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36', 'Chrome', 'Linux', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 05:02:27\"}', '2025-12-30 05:02:27'),
(715, 'platform', 'page', 0, 'page_visit', NULL, '205.169.39.8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.5938.132 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 05:02:35\"}', '2025-12-30 05:02:35'),
(716, 'platform', 'page', 0, 'page_visit', NULL, '2001:bc8:1201:734:da5e:d3ff:fe49:9fec', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.3', 'Chrome', 'Linux', 'FR', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 05:02:37\"}', '2025-12-30 05:02:37'),
(717, 'platform', 'page', 0, 'page_visit', NULL, '2001:bc8:1201:734:da5e:d3ff:fe49:9fec', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.3', 'Chrome', 'Linux', 'FR', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 05:02:37\"}', '2025-12-30 05:02:37'),
(718, 'platform', 'page', 0, 'page_visit', NULL, '205.169.39.213', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.61 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 05:02:37\"}', '2025-12-30 05:02:37'),
(719, 'platform', 'page', 0, 'page_visit', NULL, '205.169.39.213', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.79 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 05:02:45\"}', '2025-12-30 05:02:45'),
(720, 'platform', 'page', 0, 'page_visit', NULL, '104.197.69.115', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36', 'Chrome', 'Linux', 'US', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":\"http:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2025-12-30 05:02:58\"}', '2025-12-30 05:02:58'),
(721, 'platform', 'page', 0, 'page_visit', NULL, '91.231.89.39', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:134.0) Gecko/20100101 Firefox/134.0', 'Firefox', 'Linux', 'FR', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 05:27:57\"}', '2025-12-30 05:27:57'),
(722, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:e6b2:d417:6365:4156:e455', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/home-content\",\"timestamp\":\"2025-12-30 05:49:57\"}', '2025-12-30 05:49:57'),
(723, 'platform', 'page', 0, 'page_visit', NULL, '103.4.250.198', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 06:03:38\"}', '2025-12-30 06:03:38'),
(724, 'platform', 'page', 0, 'page_visit', NULL, '103.196.9.115', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36', 'Chrome', 'macOS', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 06:03:39\"}', '2025-12-30 06:03:39'),
(725, 'platform', 'page', 0, 'page_visit', NULL, '103.196.9.115', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 06:03:47\"}', '2025-12-30 06:03:47'),
(726, 'platform', 'page', 0, 'page_visit', NULL, '103.196.9.115', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 06:04:02\"}', '2025-12-30 06:04:02'),
(727, 'platform', 'page', 0, 'page_visit', NULL, '103.196.9.115', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":null,\"timestamp\":\"2025-12-30 06:04:02\"}', '2025-12-30 06:04:02'),
(728, 'platform', 'page', 0, 'page_visit', NULL, '103.196.9.115', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/register\",\"url\":\"\\/register\",\"referer\":null,\"timestamp\":\"2025-12-30 06:04:03\"}', '2025-12-30 06:04:03'),
(729, 'platform', 'page', 0, 'page_visit', NULL, '213.35.110.52', 'Mozilla/5.0 (X11; Linux x86_64) Gecko/20100101 Firefox/117.0', 'Firefox', 'Linux', 'SG', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 06:23:56\"}', '2025-12-30 06:23:56'),
(730, 'platform', 'page', 0, 'page_visit', NULL, '213.35.110.52', 'Go-http-client/1.1', 'Other', 'Other', 'SG', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 06:23:58\"}', '2025-12-30 06:23:58'),
(731, 'platform', 'page', 0, 'page_visit', NULL, '213.35.110.52', 'Mozilla/5.0 (Android 13; Mobile; rv:117.0) Gecko/117.0 Firefox/117.0', 'Firefox', 'Android', 'SG', '{\"page\":\"\\/admin\",\"url\":\"\\/admin\",\"referer\":null,\"timestamp\":\"2025-12-30 06:24:04\"}', '2025-12-30 06:24:04'),
(732, 'platform', 'page', 0, 'page_visit', NULL, '213.35.110.52', 'Mozilla/5.0 (Android 13; Mobile; rv:117.0) Gecko/117.0 Firefox/117.0', 'Firefox', 'Android', 'SG', '{\"page\":\"\\/login?return=%2Fadmin\",\"url\":\"\\/login?return=%2Fadmin\",\"referer\":\"http:\\/\\/test.mymultibranch.com\\/admin\",\"timestamp\":\"2025-12-30 06:24:04\"}', '2025-12-30 06:24:04'),
(733, 'platform', 'page', 0, 'page_visit', NULL, '213.35.110.52', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 13_5_1) AppleWebKit/605.1.15 Safari/605.1.15', 'Safari', 'macOS', 'SG', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":null,\"timestamp\":\"2025-12-30 06:24:05\"}', '2025-12-30 06:24:05'),
(734, 'platform', 'page', 0, 'page_visit', NULL, '213.35.110.52', 'Mozilla/5.0 (X11; Linux x86_64) Gecko/20100101 Firefox/117.0', 'Firefox', 'Linux', 'SG', '{\"page\":\"\\/register\",\"url\":\"\\/register\",\"referer\":null,\"timestamp\":\"2025-12-30 06:24:05\"}', '2025-12-30 06:24:05'),
(735, 'platform', 'page', 0, 'page_visit', NULL, '213.35.110.52', 'Mozilla/5.0 (iPad; CPU OS 17_4 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148', 'Other', 'macOS', 'SG', '{\"page\":\"\\/admin\",\"url\":\"\\/admin\",\"referer\":null,\"timestamp\":\"2025-12-30 06:24:06\"}', '2025-12-30 06:24:06'),
(736, 'platform', 'page', 0, 'page_visit', NULL, '213.35.110.52', 'Mozilla/5.0 (iPad; CPU OS 17_4 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148', 'Other', 'macOS', 'SG', '{\"page\":\"\\/login?return=%2Fadmin\",\"url\":\"\\/login?return=%2Fadmin\",\"referer\":\"http:\\/\\/test.mymultibranch.com\\/admin\",\"timestamp\":\"2025-12-30 06:24:06\"}', '2025-12-30 06:24:06'),
(737, 'platform', 'page', 0, 'page_visit', NULL, '213.35.110.52', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 13_5_1) AppleWebKit/605.1.15 Safari/605.1.15', 'Safari', 'macOS', 'SG', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":null,\"timestamp\":\"2025-12-30 06:24:07\"}', '2025-12-30 06:24:07'),
(738, 'platform', 'page', 0, 'page_visit', NULL, '188.214.125.54', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'Chrome', 'Linux', 'PH', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 06:25:14\"}', '2025-12-30 06:25:14'),
(739, 'platform', 'page', 0, 'page_visit', NULL, '216.73.216.26', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; ClaudeBot/1.0; +claudebot@anthropic.com)', 'Other', 'Other', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 06:39:51\"}', '2025-12-30 06:39:51'),
(740, 'platform', 'page', 0, 'page_visit', NULL, '74.7.242.4', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GPTBot/1.3; +https://openai.com/gptbot)', 'Other', 'Other', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 06:58:47\"}', '2025-12-30 06:58:47'),
(741, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.143', NULL, 'Other', 'Other', 'NL', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 07:42:43\"}', '2025-12-30 07:42:43'),
(742, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.143', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'Other', 'Windows', 'NL', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 07:42:45\"}', '2025-12-30 07:42:45'),
(743, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.143', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 07:42:46\"}', '2025-12-30 07:42:46'),
(744, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.143', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":null,\"timestamp\":\"2025-12-30 07:42:46\"}', '2025-12-30 07:42:46'),
(745, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.143', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login?redirect=\\/devzone\",\"url\":\"\\/login?redirect=\\/devzone\",\"referer\":null,\"timestamp\":\"2025-12-30 07:42:51\"}', '2025-12-30 07:42:51'),
(746, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.143', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login?redirect=\\/proshare\",\"url\":\"\\/login?redirect=\\/proshare\",\"referer\":null,\"timestamp\":\"2025-12-30 07:42:51\"}', '2025-12-30 07:42:51'),
(747, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.143', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/register\",\"url\":\"\\/register\",\"referer\":null,\"timestamp\":\"2025-12-30 07:42:51\"}', '2025-12-30 07:42:51'),
(748, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.143', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login?redirect=\\/resumex\",\"url\":\"\\/login?redirect=\\/resumex\",\"referer\":null,\"timestamp\":\"2025-12-30 07:42:51\"}', '2025-12-30 07:42:51'),
(749, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.143', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/forgot-password\",\"url\":\"\\/forgot-password\",\"referer\":null,\"timestamp\":\"2025-12-30 07:42:51\"}', '2025-12-30 07:42:51'),
(750, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.143', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login?redirect=\\/imgtxt\",\"url\":\"\\/login?redirect=\\/imgtxt\",\"referer\":null,\"timestamp\":\"2025-12-30 07:42:51\"}', '2025-12-30 07:42:51'),
(751, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.143', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login?redirect=\\/codexpro\",\"url\":\"\\/login?redirect=\\/codexpro\",\"referer\":null,\"timestamp\":\"2025-12-30 07:42:51\"}', '2025-12-30 07:42:51'),
(752, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.143', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login?redirect=\\/qr\",\"url\":\"\\/login?redirect=\\/qr\",\"referer\":null,\"timestamp\":\"2025-12-30 07:42:51\"}', '2025-12-30 07:42:51'),
(753, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.143', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login?redirect=\\/resumex\",\"url\":\"\\/login?redirect=\\/resumex\",\"referer\":null,\"timestamp\":\"2025-12-30 07:42:56\"}', '2025-12-30 07:42:56'),
(754, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.143', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/login?redirect=\\/qr\",\"url\":\"\\/login?redirect=\\/qr\",\"referer\":null,\"timestamp\":\"2025-12-30 07:42:56\"}', '2025-12-30 07:42:56'),
(755, 'platform', 'page', 0, 'page_visit', NULL, '45.148.10.143', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0', 'Firefox', 'Windows', 'NL', '{\"page\":\"\\/?phpinfo=1\",\"url\":\"\\/?phpinfo=1\",\"referer\":null,\"timestamp\":\"2025-12-30 07:46:16\"}', '2025-12-30 07:46:16'),
(756, 'platform', 'page', 0, 'page_visit', NULL, '216.73.216.26', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; ClaudeBot/1.0; +claudebot@anthropic.com)', 'Other', 'Other', 'US', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":null,\"timestamp\":\"2025-12-30 08:32:21\"}', '2025-12-30 08:32:21'),
(757, 'platform', 'page', 0, 'page_visit', NULL, '216.73.216.26', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; ClaudeBot/1.0; +claudebot@anthropic.com)', 'Other', 'Other', 'US', '{\"page\":\"\\/register\",\"url\":\"\\/register\",\"referer\":null,\"timestamp\":\"2025-12-30 08:36:47\"}', '2025-12-30 08:36:47'),
(758, 'platform', 'page', 0, 'page_visit', NULL, '216.73.216.26', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; ClaudeBot/1.0; +claudebot@anthropic.com)', 'Other', 'Other', 'US', '{\"page\":\"\\/forgot-password\",\"url\":\"\\/forgot-password\",\"referer\":null,\"timestamp\":\"2025-12-30 10:27:12\"}', '2025-12-30 10:27:12'),
(759, 'platform', 'page', 0, 'page_visit', NULL, '98.94.166.171', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 16:17:36\"}', '2025-12-30 16:17:36'),
(760, 'platform', 'page', 0, 'page_visit', NULL, '98.94.166.171', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 16:29:30\"}', '2025-12-30 16:29:30'),
(761, 'platform', 'page', 0, 'page_visit', NULL, '136.110.4.40', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0 Safari/537.36', 'Chrome', 'Windows', 'SG', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 17:43:28\"}', '2025-12-30 17:43:28'),
(762, 'platform', 'page', 0, 'page_visit', NULL, '34.172.213.196', 'Mozilla/5.0 (compatible; CMS-Checker/1.0; +https://example.com)', 'Other', 'Other', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 17:44:52\"}', '2025-12-30 17:44:52'),
(763, 'platform', 'page', 0, 'page_visit', NULL, '23.234.70.170', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 18:31:29\"}', '2025-12-30 18:31:29'),
(764, 'platform', 'page', 0, 'page_visit', NULL, '142.248.80.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 20:28:23\"}', '2025-12-30 20:28:23'),
(765, 'platform', 'page', 0, 'page_visit', NULL, '142.248.80.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 20:28:24\"}', '2025-12-30 20:28:24'),
(766, 'platform', 'page', 0, 'page_visit', NULL, '142.248.80.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-30 20:28:26\"}', '2025-12-30 20:28:26'),
(767, 'platform', 'page', 0, 'page_visit', NULL, '157.230.209.124', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Linux', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-31 01:55:47\"}', '2025-12-31 01:55:47'),
(768, 'platform', 'page', 0, 'page_visit', NULL, '157.230.209.124', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'Chrome', 'Linux', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-31 01:55:49\"}', '2025-12-31 01:55:49'),
(769, 'platform', 'page', 0, 'page_visit', NULL, '195.178.110.132', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36', 'Chrome', 'Linux', 'AD', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-31 05:45:52\"}', '2025-12-31 05:45:52'),
(770, 'platform', 'page', 0, 'page_visit', NULL, '142.248.80.192', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-31 10:43:42\"}', '2025-12-31 10:43:42'),
(771, 'platform', 'page', 0, 'page_visit', NULL, '142.248.80.192', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-31 10:43:43\"}', '2025-12-31 10:43:43'),
(772, 'platform', 'page', 0, 'page_visit', NULL, '142.248.80.192', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-31 10:43:44\"}', '2025-12-31 10:43:44'),
(773, 'platform', 'page', 0, 'page_visit', NULL, '142.248.80.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-31 20:06:19\"}', '2025-12-31 20:06:19'),
(774, 'platform', 'page', 0, 'page_visit', NULL, '142.248.80.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-31 20:06:20\"}', '2025-12-31 20:06:20'),
(775, 'platform', 'page', 0, 'page_visit', NULL, '142.248.80.88', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36', 'Chrome', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-31 20:06:22\"}', '2025-12-31 20:06:22'),
(776, 'platform', 'page', 0, 'page_visit', NULL, '54.183.255.65', 'Mozilla/5.0 (Windows; U; MSIE 6.1; Macintosh; .NET CLR 1.2.26304; Intel Mac OS X 10_4_0)', 'IE', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/www.google.com\\/\",\"timestamp\":\"2025-12-31 20:34:44\"}', '2025-12-31 20:34:44'),
(777, 'platform', 'page', 0, 'page_visit', NULL, '54.183.255.65', 'Mozilla/5.0 (Windows; U; MSIE 6.1; Macintosh; .NET CLR 1.2.26304; Intel Mac OS X 10_4_0)', 'IE', 'Windows', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/www.google.com\\/\",\"timestamp\":\"2025-12-31 20:36:31\"}', '2025-12-31 20:36:31'),
(778, 'platform', 'page', 0, 'page_visit', NULL, '172.70.24.16', 'Cloudflare-SSLDetector', 'Other', 'Other', 'US', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-31 22:13:33\"}', '2025-12-31 22:13:33'),
(779, 'platform', 'page', 0, 'page_visit', NULL, '195.178.110.155', 'Mozilla/5.0', 'Other', 'Other', 'AD', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-31 22:19:44\"}', '2025-12-31 22:19:44'),
(780, 'platform', 'page', 0, 'page_visit', NULL, '106.215.141.143', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/601.2.4 (KHTML, like Gecko) Version/9.0.1 Safari/601.2.4 facebookexternalhit/1.1 Facebot Twitterbot/1.0', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2025-12-31 23:30:30\"}', '2025-12-31 23:30:30'),
(781, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2026-01-01 05:01:55\"}', '2026-01-01 05:01:55'),
(782, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2026-01-01 05:01:58\"}', '2026-01-01 05:01:58'),
(783, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/login\",\"timestamp\":\"2026-01-01 05:02:10\"}', '2026-01-01 05:02:10'),
(784, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-01 05:02:11\",\"login_method\":\"standard\"}', '2026-01-01 05:02:11'),
(785, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/login\",\"timestamp\":\"2026-01-01 05:02:12\"}', '2026-01-01 05:02:12'),
(786, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\",\"url\":\"\\/admin\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2026-01-01 05:03:29\"}', '2026-01-01 05:03:29'),
(787, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/user-logs\",\"url\":\"\\/admin\\/projects\\/proshare\\/user-logs\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\",\"timestamp\":\"2026-01-01 05:03:34\"}', '2026-01-01 05:03:34'),
(788, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/user-activity\",\"url\":\"\\/admin\\/projects\\/proshare\\/user-activity\",\"referer\":null,\"timestamp\":\"2026-01-01 05:04:24\"}', '2026-01-01 05:04:24'),
(789, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/user-activity\",\"url\":\"\\/admin\\/projects\\/proshare\\/user-activity\",\"referer\":null,\"timestamp\":\"2026-01-01 05:04:38\"}', '2026-01-01 05:04:38'),
(790, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/user-logs\",\"url\":\"\\/admin\\/projects\\/proshare\\/user-logs\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/user-activity\",\"timestamp\":\"2026-01-01 05:05:09\"}', '2026-01-01 05:05:09'),
(791, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/user-logs\",\"url\":\"\\/admin\\/projects\\/proshare\\/user-logs\",\"referer\":null,\"timestamp\":\"2026-01-01 05:06:57\"}', '2026-01-01 05:06:57'),
(792, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/user-logs\",\"url\":\"\\/admin\\/projects\\/proshare\\/user-logs\",\"referer\":null,\"timestamp\":\"2026-01-01 05:07:00\"}', '2026-01-01 05:07:00'),
(793, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/logout\",\"url\":\"\\/logout\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/logout\",\"timestamp\":\"2026-01-01 05:07:16\"}', '2026-01-01 05:07:16'),
(794, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/logout\",\"timestamp\":\"2026-01-01 05:07:17\"}', '2026-01-01 05:07:17'),
(795, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2026-01-01 05:07:20\"}', '2026-01-01 05:07:20'),
(796, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/logout\",\"timestamp\":\"2026-01-01 05:07:20\"}', '2026-01-01 05:07:20'),
(797, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2026-01-01 05:07:24\"}', '2026-01-01 05:07:24'),
(798, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/login\",\"timestamp\":\"2026-01-01 05:07:35\"}', '2026-01-01 05:07:35'),
(799, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-01 05:07:36\",\"login_method\":\"standard\"}', '2026-01-01 05:07:36'),
(800, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/login\",\"timestamp\":\"2026-01-01 05:07:36\"}', '2026-01-01 05:07:36'),
(801, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\",\"url\":\"\\/admin\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2026-01-01 05:07:38\"}', '2026-01-01 05:07:38'),
(802, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/user-logs\",\"url\":\"\\/admin\\/projects\\/proshare\\/user-logs\",\"referer\":null,\"timestamp\":\"2026-01-01 05:07:50\"}', '2026-01-01 05:07:50'),
(803, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/settings\",\"url\":\"\\/admin\\/projects\\/proshare\\/settings\",\"referer\":null,\"timestamp\":\"2026-01-01 05:08:09\"}', '2026-01-01 05:08:09'),
(804, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/settings\",\"url\":\"\\/admin\\/projects\\/proshare\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/settings\",\"timestamp\":\"2026-01-01 05:08:21\"}', '2026-01-01 05:08:21'),
(805, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/settings\",\"url\":\"\\/admin\\/projects\\/proshare\\/settings\",\"referer\":null,\"timestamp\":\"2026-01-01 05:08:24\"}', '2026-01-01 05:08:24'),
(806, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/files?status=all\",\"url\":\"\\/admin\\/projects\\/proshare\\/files?status=all\",\"referer\":null,\"timestamp\":\"2026-01-01 05:08:38\"}', '2026-01-01 05:08:38'),
(807, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/preview\\/JbmTtm6N\",\"url\":\"\\/projects\\/proshare\\/preview\\/JbmTtm6N\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/files?status=all\",\"timestamp\":\"2026-01-01 05:08:41\"}', '2026-01-01 05:08:41'),
(808, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/preview\\/JbmTtm6N\",\"url\":\"\\/projects\\/proshare\\/preview\\/JbmTtm6N\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/files?status=all\",\"timestamp\":\"2026-01-01 05:08:41\"}', '2026-01-01 05:08:41'),
(809, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/file-activity\",\"url\":\"\\/admin\\/projects\\/proshare\\/file-activity\",\"referer\":null,\"timestamp\":\"2026-01-01 05:08:53\"}', '2026-01-01 05:08:53'),
(810, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/file-activity?action=upload\",\"url\":\"\\/admin\\/projects\\/proshare\\/file-activity?action=upload\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/file-activity\",\"timestamp\":\"2026-01-01 05:08:55\"}', '2026-01-01 05:08:55'),
(811, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/file-activity?action=all\",\"url\":\"\\/admin\\/projects\\/proshare\\/file-activity?action=all\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/file-activity?action=upload\",\"timestamp\":\"2026-01-01 05:08:57\"}', '2026-01-01 05:08:57'),
(812, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/audit-trail\",\"url\":\"\\/admin\\/projects\\/proshare\\/audit-trail\",\"referer\":null,\"timestamp\":\"2026-01-01 05:09:08\"}', '2026-01-01 05:09:08'),
(813, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/analytics\",\"url\":\"\\/admin\\/projects\\/proshare\\/analytics\",\"referer\":null,\"timestamp\":\"2026-01-01 05:09:32\"}', '2026-01-01 05:09:32'),
(814, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/analytics\",\"url\":\"\\/admin\\/projects\\/proshare\\/analytics\",\"referer\":null,\"timestamp\":\"2026-01-01 05:35:32\"}', '2026-01-01 05:35:32'),
(815, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/audit-trail\",\"url\":\"\\/admin\\/projects\\/proshare\\/audit-trail\",\"referer\":null,\"timestamp\":\"2026-01-01 05:35:35\"}', '2026-01-01 05:35:35'),
(816, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2026-01-01 05:36:03\"}', '2026-01-01 05:36:03'),
(817, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2026-01-01 05:36:04\"}', '2026-01-01 05:36:04'),
(818, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2026-01-01 05:36:12\"}', '2026-01-01 05:36:12'),
(819, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/login\",\"timestamp\":\"2026-01-01 05:36:33\"}', '2026-01-01 05:36:33'),
(820, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"timestamp\":\"2026-01-01 05:36:33\",\"login_method\":\"standard\"}', '2026-01-01 05:36:33'),
(821, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/login\",\"timestamp\":\"2026-01-01 05:36:34\"}', '2026-01-01 05:36:34'),
(822, 'platform', 'page', 0, 'page_visit', 3, '106.215.141.143', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/login?return=%2Fprojects%2Fproshare%2F\",\"url\":\"\\/login?return=%2Fprojects%2Fproshare%2F\",\"referer\":null,\"timestamp\":\"2026-01-01 05:36:43\"}', '2026-01-01 05:36:43'),
(823, 'platform', 'page', 0, 'page_visit', 3, '106.215.141.143', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":null,\"timestamp\":\"2026-01-01 05:36:43\"}', '2026-01-01 05:36:43'),
(824, 'platform', 'page', 0, 'page_visit', 3, '106.215.141.143', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/login?return=%2Fprojects%2Fproshare%2F\",\"url\":\"\\/login?return=%2Fprojects%2Fproshare%2F\",\"referer\":\"http:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2026-01-01 05:36:50\"}', '2026-01-01 05:36:50'),
(825, 'platform', 'page', 0, 'page_visit', 3, '106.215.141.143', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"http:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2026-01-01 05:36:51\"}', '2026-01-01 05:36:51'),
(826, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/user-dashboard\",\"url\":\"\\/admin\\/projects\\/proshare\\/user-dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/audit-trail\",\"timestamp\":\"2026-01-01 05:37:01\"}', '2026-01-01 05:37:01');
INSERT INTO `analytics_events` (`id`, `project`, `resource_type`, `resource_id`, `event_type`, `user_id`, `ip_address`, `user_agent`, `browser`, `platform`, `country`, `metadata`, `created_at`) VALUES
(827, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/user-files?user_id=1\",\"url\":\"\\/admin\\/projects\\/proshare\\/user-files?user_id=1\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/user-dashboard\",\"timestamp\":\"2026-01-01 05:39:15\"}', '2026-01-01 05:39:15'),
(828, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/user-files?user_id=1\",\"timestamp\":\"2026-01-01 05:39:25\"}', '2026-01-01 05:39:25'),
(829, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2026-01-01 05:39:28\"}', '2026-01-01 05:39:28'),
(830, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/\",\"url\":\"\\/projects\\/proshare\\/\",\"referer\":null,\"timestamp\":\"2026-01-01 05:39:31\"}', '2026-01-01 05:39:31'),
(831, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/\",\"url\":\"\\/projects\\/proshare\\/\",\"referer\":null,\"timestamp\":\"2026-01-01 05:39:34\"}', '2026-01-01 05:39:34'),
(832, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/dashboard\",\"url\":\"\\/projects\\/proshare\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/\",\"timestamp\":\"2026-01-01 05:39:40\"}', '2026-01-01 05:39:40'),
(833, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/dashboard\",\"url\":\"\\/projects\\/proshare\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/\",\"timestamp\":\"2026-01-01 05:39:40\"}', '2026-01-01 05:39:40'),
(834, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/dashboard\",\"timestamp\":\"2026-01-01 05:39:46\"}', '2026-01-01 05:39:46'),
(835, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/dashboard\",\"timestamp\":\"2026-01-01 05:39:50\"}', '2026-01-01 05:39:50'),
(836, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/\",\"url\":\"\\/projects\\/proshare\\/\",\"referer\":null,\"timestamp\":\"2026-01-01 05:39:52\"}', '2026-01-01 05:39:52'),
(837, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/login?return=%2Fprojects%2Fproshare%2F\",\"url\":\"\\/login?return=%2Fprojects%2Fproshare%2F\",\"referer\":\"http:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2026-01-01 05:40:03\"}', '2026-01-01 05:40:03'),
(838, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"http:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2026-01-01 05:40:04\"}', '2026-01-01 05:40:04'),
(839, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/login?return=%2Fprojects%2Fproshare%2F\",\"url\":\"\\/login?return=%2Fprojects%2Fproshare%2F\",\"referer\":null,\"timestamp\":\"2026-01-01 05:40:14\"}', '2026-01-01 05:40:14'),
(840, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":null,\"timestamp\":\"2026-01-01 05:40:15\"}', '2026-01-01 05:40:15'),
(841, 'platform', 'page', 0, 'page_visit', 3, '106.215.141.143', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/login?return=%2Fprojects%2Fimgtxt%2F\",\"url\":\"\\/login?return=%2Fprojects%2Fimgtxt%2F\",\"referer\":\"http:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2026-01-01 05:40:50\"}', '2026-01-01 05:40:50'),
(842, 'platform', 'page', 0, 'page_visit', 3, '106.215.141.143', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"http:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2026-01-01 05:40:51\"}', '2026-01-01 05:40:51'),
(843, 'platform', 'page', 0, 'page_visit', 3, '106.215.141.143', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"http:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2026-01-01 05:40:54\"}', '2026-01-01 05:40:54'),
(844, 'platform', 'page', 0, 'page_visit', 3, '106.215.141.143', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/login?redirect=\\/proshare\",\"url\":\"\\/login?redirect=\\/proshare\",\"referer\":\"http:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2026-01-01 05:41:00\"}', '2026-01-01 05:41:00'),
(845, 'platform', 'page', 0, 'page_visit', 3, '106.215.141.143', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"http:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2026-01-01 05:41:01\"}', '2026-01-01 05:41:01'),
(846, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2026-01-01 05:41:22\"}', '2026-01-01 05:41:22'),
(847, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/register\",\"url\":\"\\/register\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2026-01-01 05:41:24\"}', '2026-01-01 05:41:24'),
(848, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/register\",\"timestamp\":\"2026-01-01 05:41:28\"}', '2026-01-01 05:41:28'),
(849, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/login\",\"timestamp\":\"2026-01-01 05:41:40\"}', '2026-01-01 05:41:40'),
(850, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/register\",\"timestamp\":\"2026-01-01 05:42:05\"}', '2026-01-01 05:42:05'),
(851, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/\",\"url\":\"\\/projects\\/proshare\\/\",\"referer\":null,\"timestamp\":\"2026-01-01 05:42:14\"}', '2026-01-01 05:42:14'),
(852, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"timestamp\":\"2026-01-01 05:42:21\",\"login_method\":\"standard\"}', '2026-01-01 05:42:21'),
(853, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/login\",\"timestamp\":\"2026-01-01 05:42:31\"}', '2026-01-01 05:42:31'),
(854, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"timestamp\":\"2026-01-01 05:42:45\",\"login_method\":\"standard\"}', '2026-01-01 05:42:45'),
(855, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/login\",\"timestamp\":\"2026-01-01 05:42:49\"}', '2026-01-01 05:42:49'),
(856, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/login\",\"timestamp\":\"2026-01-01 05:43:00\"}', '2026-01-01 05:43:00'),
(857, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/login?return=%2Fprojects%2Fproshare%2F\",\"url\":\"\\/login?return=%2Fprojects%2Fproshare%2F\",\"referer\":null,\"timestamp\":\"2026-01-01 05:43:03\"}', '2026-01-01 05:43:03'),
(858, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":null,\"timestamp\":\"2026-01-01 05:43:04\"}', '2026-01-01 05:43:04'),
(859, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/dashboard\",\"timestamp\":\"2026-01-01 05:43:13\"}', '2026-01-01 05:43:13'),
(860, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\",\"url\":\"\\/admin\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2026-01-01 05:43:17\"}', '2026-01-01 05:43:17'),
(861, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/user-logs\",\"url\":\"\\/admin\\/projects\\/proshare\\/user-logs\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\",\"timestamp\":\"2026-01-01 05:43:24\"}', '2026-01-01 05:43:24'),
(862, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/admin\",\"url\":\"\\/admin\",\"referer\":\"http:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2026-01-01 05:45:09\"}', '2026-01-01 05:45:09'),
(863, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/admin\\/projects\",\"url\":\"\\/admin\\/projects\",\"referer\":\"http:\\/\\/test.mymultibranch.com\\/admin\",\"timestamp\":\"2026-01-01 05:45:18\"}', '2026-01-01 05:45:18'),
(864, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/admin\",\"url\":\"\\/admin\",\"referer\":\"http:\\/\\/test.mymultibranch.com\\/admin\\/projects\",\"timestamp\":\"2026-01-01 05:45:26\"}', '2026-01-01 05:45:26'),
(865, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/user-dashboard\",\"url\":\"\\/admin\\/projects\\/proshare\\/user-dashboard\",\"referer\":\"http:\\/\\/test.mymultibranch.com\\/admin\",\"timestamp\":\"2026-01-01 05:45:41\"}', '2026-01-01 05:45:41'),
(866, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/user-activity?user_id=1\",\"url\":\"\\/admin\\/projects\\/proshare\\/user-activity?user_id=1\",\"referer\":\"http:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/user-dashboard\",\"timestamp\":\"2026-01-01 05:45:45\"}', '2026-01-01 05:45:45'),
(867, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/projects\\/proshare\\/\",\"url\":\"\\/projects\\/proshare\\/\",\"referer\":null,\"timestamp\":\"2026-01-01 05:46:12\"}', '2026-01-01 05:46:12'),
(868, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/projects\\/proshare\\/upload\",\"url\":\"\\/projects\\/proshare\\/upload\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/\",\"timestamp\":\"2026-01-01 05:46:15\"}', '2026-01-01 05:46:15'),
(869, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/projects\\/proshare\\/upload\",\"url\":\"\\/projects\\/proshare\\/upload\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/\",\"timestamp\":\"2026-01-01 05:46:16\"}', '2026-01-01 05:46:16'),
(870, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/projects\\/proshare\\/upload\",\"url\":\"\\/projects\\/proshare\\/upload\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/upload\",\"timestamp\":\"2026-01-01 05:46:59\"}', '2026-01-01 05:46:59'),
(871, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/projects\\/proshare\\/upload\",\"url\":\"\\/projects\\/proshare\\/upload\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/upload\",\"timestamp\":\"2026-01-01 05:46:59\"}', '2026-01-01 05:46:59'),
(872, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/s\\/jLSTkvyj\",\"url\":\"\\/s\\/jLSTkvyj\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/upload\",\"timestamp\":\"2026-01-01 05:47:04\"}', '2026-01-01 05:47:04'),
(873, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/projects\\/proshare\\/dashboard\",\"url\":\"\\/projects\\/proshare\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/upload\",\"timestamp\":\"2026-01-01 05:47:15\"}', '2026-01-01 05:47:15'),
(874, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/projects\\/proshare\\/dashboard\",\"url\":\"\\/projects\\/proshare\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/upload\",\"timestamp\":\"2026-01-01 05:47:25\"}', '2026-01-01 05:47:25'),
(875, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/projects\\/proshare\\/preview\\/jLSTkvyj\",\"url\":\"\\/projects\\/proshare\\/preview\\/jLSTkvyj\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/dashboard\",\"timestamp\":\"2026-01-01 05:47:40\"}', '2026-01-01 05:47:40'),
(876, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/projects\\/proshare\\/preview\\/jLSTkvyj\",\"url\":\"\\/projects\\/proshare\\/preview\\/jLSTkvyj\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/dashboard\",\"timestamp\":\"2026-01-01 05:47:42\"}', '2026-01-01 05:47:42'),
(877, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/projects\\/proshare\\/upload\",\"url\":\"\\/projects\\/proshare\\/upload\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/\",\"timestamp\":\"2026-01-01 05:47:42\"}', '2026-01-01 05:47:42'),
(878, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:8d7b:b296:c1fd:c1ea', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/projects\\/proshare\\/upload\",\"url\":\"\\/projects\\/proshare\\/upload\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/\",\"timestamp\":\"2026-01-01 05:47:42\"}', '2026-01-01 05:47:42'),
(879, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/dashboard\",\"url\":\"\\/admin\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/user-logs\",\"timestamp\":\"2026-01-01 05:49:13\"}', '2026-01-01 05:49:13'),
(880, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/a\",\"timestamp\":\"2026-01-01 05:49:19\"}', '2026-01-01 05:49:19'),
(881, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2026-01-01 05:49:21\"}', '2026-01-01 05:49:21'),
(882, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/\",\"url\":\"\\/projects\\/proshare\\/\",\"referer\":null,\"timestamp\":\"2026-01-01 05:49:24\"}', '2026-01-01 05:49:24'),
(883, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\",\"url\":\"\\/admin\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2026-01-01 05:52:28\"}', '2026-01-01 05:52:28'),
(884, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\",\"timestamp\":\"2026-01-01 05:55:09\"}', '2026-01-01 05:55:09'),
(885, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\",\"timestamp\":\"2026-01-01 06:27:05\"}', '2026-01-01 06:27:05'),
(886, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\",\"url\":\"\\/admin\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2026-01-01 06:27:08\"}', '2026-01-01 06:27:08'),
(887, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/database-setup\",\"url\":\"\\/admin\\/projects\\/database-setup\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\",\"timestamp\":\"2026-01-01 06:27:14\"}', '2026-01-01 06:27:14'),
(888, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\",\"url\":\"\\/admin\\/projects\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\",\"timestamp\":\"2026-01-01 06:27:17\"}', '2026-01-01 06:27:17'),
(889, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/users\",\"url\":\"\\/admin\\/users\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\",\"timestamp\":\"2026-01-01 06:27:22\"}', '2026-01-01 06:27:22'),
(890, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/codexpro\",\"url\":\"\\/admin\\/projects\\/codexpro\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\",\"timestamp\":\"2026-01-01 06:27:32\"}', '2026-01-01 06:27:32'),
(891, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/history\",\"url\":\"\\/projects\\/imgtxt\\/history\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\",\"timestamp\":\"2026-01-01 06:27:36\"}', '2026-01-01 06:27:36'),
(892, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/history\",\"url\":\"\\/projects\\/imgtxt\\/history\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\",\"timestamp\":\"2026-01-01 06:27:36\"}', '2026-01-01 06:27:36'),
(893, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/overview\",\"url\":\"\\/admin\\/analytics\\/overview\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\",\"timestamp\":\"2026-01-01 06:27:42\"}', '2026-01-01 06:27:42'),
(894, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/overview\",\"url\":\"\\/admin\\/analytics\\/overview\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/overview\",\"timestamp\":\"2026-01-01 06:28:14\"}', '2026-01-01 06:28:14'),
(895, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/overview\",\"url\":\"\\/admin\\/analytics\\/overview\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/overview\",\"timestamp\":\"2026-01-01 06:28:46\"}', '2026-01-01 06:28:46'),
(896, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/dashboard\",\"url\":\"\\/admin\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/overview\",\"timestamp\":\"2026-01-01 06:29:15\"}', '2026-01-01 06:29:15'),
(897, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/overview\",\"url\":\"\\/admin\\/analytics\\/overview\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/overview\",\"timestamp\":\"2026-01-01 06:29:22\"}', '2026-01-01 06:29:22'),
(898, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/overview\",\"url\":\"\\/admin\\/analytics\\/overview\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/overview\",\"timestamp\":\"2026-01-01 06:29:25\"}', '2026-01-01 06:29:25'),
(899, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/overview\",\"url\":\"\\/admin\\/analytics\\/overview\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/overview\",\"timestamp\":\"2026-01-01 06:29:56\"}', '2026-01-01 06:29:56'),
(900, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/overview\",\"url\":\"\\/admin\\/analytics\\/overview\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/overview\",\"timestamp\":\"2026-01-01 06:30:29\"}', '2026-01-01 06:30:29'),
(901, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/overview\",\"url\":\"\\/admin\\/analytics\\/overview\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/overview\",\"timestamp\":\"2026-01-01 06:31:00\"}', '2026-01-01 06:31:00'),
(902, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/overview\",\"url\":\"\\/admin\\/analytics\\/overview\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/overview\",\"timestamp\":\"2026-01-01 06:31:33\"}', '2026-01-01 06:31:33'),
(903, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/overview\",\"url\":\"\\/admin\\/analytics\\/overview\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/overview\",\"timestamp\":\"2026-01-01 06:32:05\"}', '2026-01-01 06:32:05'),
(904, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/overview\",\"timestamp\":\"2026-01-01 06:32:32\"}', '2026-01-01 06:32:32'),
(905, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/overview\",\"url\":\"\\/admin\\/analytics\\/overview\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/overview\",\"timestamp\":\"2026-01-01 06:32:37\"}', '2026-01-01 06:32:37'),
(906, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/overview\",\"url\":\"\\/admin\\/analytics\\/overview\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/overview\",\"timestamp\":\"2026-01-01 06:33:09\"}', '2026-01-01 06:33:09'),
(907, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/overview\",\"url\":\"\\/admin\\/analytics\\/overview\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/overview\",\"timestamp\":\"2026-01-01 06:33:41\"}', '2026-01-01 06:33:41'),
(908, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/overview\",\"url\":\"\\/admin\\/analytics\\/overview\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/overview\",\"timestamp\":\"2026-01-01 06:34:14\"}', '2026-01-01 06:34:14'),
(909, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/overview\",\"url\":\"\\/admin\\/analytics\\/overview\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/overview\",\"timestamp\":\"2026-01-01 06:34:48\"}', '2026-01-01 06:34:48'),
(910, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\",\"url\":\"\\/admin\\/projects\\/proshare\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/overview\",\"timestamp\":\"2026-01-01 06:35:09\"}', '2026-01-01 06:35:09'),
(911, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/database-setup\",\"url\":\"\\/admin\\/projects\\/database-setup\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\",\"timestamp\":\"2026-01-01 07:22:43\"}', '2026-01-01 07:22:43'),
(912, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/database-setup\",\"url\":\"\\/admin\\/projects\\/database-setup\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\",\"timestamp\":\"2026-01-01 07:22:47\"}', '2026-01-01 07:22:47'),
(913, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/database-setup\",\"url\":\"\\/admin\\/projects\\/database-setup\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\",\"timestamp\":\"2026-01-01 07:24:01\"}', '2026-01-01 07:24:01'),
(914, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/auth\\/logout\",\"timestamp\":\"2026-01-01 07:24:20\"}', '2026-01-01 07:24:20'),
(915, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\",\"url\":\"\\/admin\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2026-01-01 07:24:24\"}', '2026-01-01 07:24:24'),
(916, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2026-01-01 07:24:24\"}', '2026-01-01 07:24:24'),
(917, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/logout\",\"url\":\"\\/logout\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2026-01-01 07:24:28\"}', '2026-01-01 07:24:28'),
(918, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2026-01-01 07:24:28\"}', '2026-01-01 07:24:28'),
(919, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2026-01-01 07:24:30\"}', '2026-01-01 07:24:30'),
(920, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2026-01-01 07:24:33\"}', '2026-01-01 07:24:33'),
(921, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2026-01-01 07:25:42\"}', '2026-01-01 07:25:42'),
(922, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2026-01-01 07:25:46\"}', '2026-01-01 07:25:46'),
(923, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/login\",\"timestamp\":\"2026-01-01 07:26:26\"}', '2026-01-01 07:26:26'),
(924, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-01 07:26:28\",\"login_method\":\"standard\"}', '2026-01-01 07:26:28'),
(925, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/login\",\"timestamp\":\"2026-01-01 07:26:28\"}', '2026-01-01 07:26:28'),
(926, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\",\"url\":\"\\/admin\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2026-01-01 07:26:32\"}', '2026-01-01 07:26:32'),
(927, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/users\",\"url\":\"\\/admin\\/users\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\",\"timestamp\":\"2026-01-01 07:26:42\"}', '2026-01-01 07:26:42'),
(928, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/logs\",\"url\":\"\\/admin\\/logs\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\",\"timestamp\":\"2026-01-01 07:26:50\"}', '2026-01-01 07:26:50'),
(929, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/navbar\",\"url\":\"\\/admin\\/navbar\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\",\"timestamp\":\"2026-01-01 07:26:54\"}', '2026-01-01 07:26:54'),
(930, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/navbar\",\"url\":\"\\/admin\\/navbar\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\",\"timestamp\":\"2026-01-01 07:38:16\"}', '2026-01-01 07:38:16'),
(931, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/database-setup\",\"url\":\"\\/admin\\/projects\\/database-setup\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/navbar\",\"timestamp\":\"2026-01-01 07:38:22\"}', '2026-01-01 07:38:22'),
(932, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\",\"timestamp\":\"2026-01-01 07:40:37\"}', '2026-01-01 07:40:37'),
(933, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2026-01-01 07:40:39\"}', '2026-01-01 07:40:39'),
(934, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/proshare\\/\",\"url\":\"\\/projects\\/proshare\\/\",\"referer\":null,\"timestamp\":\"2026-01-01 07:40:42\"}', '2026-01-01 07:40:42'),
(935, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export\",\"url\":\"\\/admin\\/analytics\\/export\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\",\"timestamp\":\"2026-01-01 07:40:59\"}', '2026-01-01 07:40:59'),
(936, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-02&date_to=2026-01-01&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-02&date_to=2026-01-01&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2026-01-01 07:41:00\"}', '2026-01-01 07:41:00'),
(937, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-02&date_to=2026-01-01&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-02&date_to=2026-01-01&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2026-01-01 07:41:30\"}', '2026-01-01 07:41:30'),
(938, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-02&date_to=2026-01-01&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-02&date_to=2026-01-01&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2026-01-01 07:42:01\"}', '2026-01-01 07:42:01');
INSERT INTO `analytics_events` (`id`, `project`, `resource_type`, `resource_id`, `event_type`, `user_id`, `ip_address`, `user_agent`, `browser`, `platform`, `country`, `metadata`, `created_at`) VALUES
(939, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-02&date_to=2026-01-01&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-02&date_to=2026-01-01&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2026-01-01 07:42:31\"}', '2026-01-01 07:42:31'),
(940, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-02&date_to=2026-01-01&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-02&date_to=2026-01-01&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2026-01-01 07:43:01\"}', '2026-01-01 07:43:01'),
(941, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-02&date_to=2026-01-01&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-02&date_to=2026-01-01&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2026-01-01 07:43:31\"}', '2026-01-01 07:43:31'),
(942, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/database-setup\",\"url\":\"\\/admin\\/projects\\/database-setup\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2026-01-01 07:43:33\"}', '2026-01-01 07:43:33'),
(943, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/database-setup\\/codexpro\",\"url\":\"\\/admin\\/projects\\/database-setup\\/codexpro\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\",\"timestamp\":\"2026-01-01 07:43:36\"}', '2026-01-01 07:43:36'),
(944, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/database-setup\\/imgtxt\",\"url\":\"\\/admin\\/projects\\/database-setup\\/imgtxt\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\",\"timestamp\":\"2026-01-01 07:43:41\"}', '2026-01-01 07:43:41'),
(945, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/database-setup\\/proshare\",\"url\":\"\\/admin\\/projects\\/database-setup\\/proshare\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\",\"timestamp\":\"2026-01-01 07:43:47\"}', '2026-01-01 07:43:47'),
(946, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/codexpro\",\"url\":\"\\/admin\\/projects\\/codexpro\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\",\"timestamp\":\"2026-01-01 07:43:51\"}', '2026-01-01 07:43:51'),
(947, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/codexpro\\/templates\",\"url\":\"\\/admin\\/projects\\/codexpro\\/templates\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\",\"timestamp\":\"2026-01-01 07:43:54\"}', '2026-01-01 07:43:54'),
(948, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\",\"url\":\"\\/admin\\/projects\\/imgtxt\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/codexpro\\/templates\",\"timestamp\":\"2026-01-01 07:44:05\"}', '2026-01-01 07:44:05'),
(949, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/dashboard\",\"url\":\"\\/projects\\/imgtxt\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/codexpro\\/templates\",\"timestamp\":\"2026-01-01 07:44:07\"}', '2026-01-01 07:44:07'),
(950, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/dashboard\",\"url\":\"\\/projects\\/imgtxt\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/codexpro\\/templates\",\"timestamp\":\"2026-01-01 07:44:07\"}', '2026-01-01 07:44:07'),
(951, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/imgtxt\",\"url\":\"\\/admin\\/projects\\/imgtxt\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/codexpro\\/templates\",\"timestamp\":\"2026-01-01 07:44:10\"}', '2026-01-01 07:44:10'),
(952, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/upload\",\"url\":\"\\/projects\\/imgtxt\\/upload\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/codexpro\\/templates\",\"timestamp\":\"2026-01-01 07:44:13\"}', '2026-01-01 07:44:13'),
(953, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/imgtxt\\/upload\",\"url\":\"\\/projects\\/imgtxt\\/upload\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/codexpro\\/templates\",\"timestamp\":\"2026-01-01 07:44:13\"}', '2026-01-01 07:44:13'),
(954, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/\",\"url\":\"\\/projects\\/codexpro\\/\",\"referer\":null,\"timestamp\":\"2026-01-01 07:44:23\"}', '2026-01-01 07:44:23'),
(955, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/templates\",\"url\":\"\\/projects\\/codexpro\\/templates\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/\",\"timestamp\":\"2026-01-01 07:44:25\"}', '2026-01-01 07:44:25'),
(956, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/templates\",\"url\":\"\\/projects\\/codexpro\\/templates\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/\",\"timestamp\":\"2026-01-01 07:44:25\"}', '2026-01-01 07:44:25'),
(957, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/editor\\/5\",\"url\":\"\\/projects\\/codexpro\\/editor\\/5\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/templates\",\"timestamp\":\"2026-01-01 07:44:29\"}', '2026-01-01 07:44:29'),
(958, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/editor\\/5\",\"url\":\"\\/projects\\/codexpro\\/editor\\/5\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/templates\",\"timestamp\":\"2026-01-01 07:44:29\"}', '2026-01-01 07:44:29'),
(959, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/editor\\/script.js\",\"url\":\"\\/projects\\/codexpro\\/editor\\/script.js\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/editor\\/5\",\"timestamp\":\"2026-01-01 07:44:30\"}', '2026-01-01 07:44:30'),
(960, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/editor\\/script.js\",\"url\":\"\\/projects\\/codexpro\\/editor\\/script.js\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/editor\\/5\",\"timestamp\":\"2026-01-01 07:44:30\"}', '2026-01-01 07:44:30'),
(961, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/editor\\/style.css\",\"url\":\"\\/projects\\/codexpro\\/editor\\/style.css\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/editor\\/5\",\"timestamp\":\"2026-01-01 07:44:30\"}', '2026-01-01 07:44:30'),
(962, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/editor\\/style.css\",\"url\":\"\\/projects\\/codexpro\\/editor\\/style.css\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/editor\\/5\",\"timestamp\":\"2026-01-01 07:44:30\"}', '2026-01-01 07:44:30'),
(963, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/templates\",\"url\":\"\\/projects\\/codexpro\\/templates\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/\",\"timestamp\":\"2026-01-01 07:44:31\"}', '2026-01-01 07:44:31'),
(964, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/templates\",\"url\":\"\\/projects\\/codexpro\\/templates\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/\",\"timestamp\":\"2026-01-01 07:44:31\"}', '2026-01-01 07:44:31'),
(965, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/editor\\/6\",\"url\":\"\\/projects\\/codexpro\\/editor\\/6\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/templates\",\"timestamp\":\"2026-01-01 07:44:33\"}', '2026-01-01 07:44:33'),
(966, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/editor\\/6\",\"url\":\"\\/projects\\/codexpro\\/editor\\/6\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/templates\",\"timestamp\":\"2026-01-01 07:44:33\"}', '2026-01-01 07:44:33'),
(967, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/templates\",\"url\":\"\\/projects\\/codexpro\\/templates\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/\",\"timestamp\":\"2026-01-01 07:44:34\"}', '2026-01-01 07:44:34'),
(968, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/templates\",\"url\":\"\\/projects\\/codexpro\\/templates\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/\",\"timestamp\":\"2026-01-01 07:44:34\"}', '2026-01-01 07:44:34'),
(969, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/editor\\/app.js\",\"url\":\"\\/projects\\/codexpro\\/editor\\/app.js\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/editor\\/6\",\"timestamp\":\"2026-01-01 07:44:34\"}', '2026-01-01 07:44:34'),
(970, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/editor\\/app.js\",\"url\":\"\\/projects\\/codexpro\\/editor\\/app.js\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/editor\\/6\",\"timestamp\":\"2026-01-01 07:44:34\"}', '2026-01-01 07:44:34'),
(971, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/editor\\/7\",\"url\":\"\\/projects\\/codexpro\\/editor\\/7\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/templates\",\"timestamp\":\"2026-01-01 07:44:36\"}', '2026-01-01 07:44:36'),
(972, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/editor\\/7\",\"url\":\"\\/projects\\/codexpro\\/editor\\/7\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/templates\",\"timestamp\":\"2026-01-01 07:44:36\"}', '2026-01-01 07:44:36'),
(973, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/templates\",\"url\":\"\\/projects\\/codexpro\\/templates\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/\",\"timestamp\":\"2026-01-01 07:44:38\"}', '2026-01-01 07:44:38'),
(974, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/templates\",\"url\":\"\\/projects\\/codexpro\\/templates\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/codexpro\\/\",\"timestamp\":\"2026-01-01 07:44:38\"}', '2026-01-01 07:44:38'),
(975, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/projects\\/codexpro\\/\",\"url\":\"\\/projects\\/codexpro\\/\",\"referer\":null,\"timestamp\":\"2026-01-01 08:05:15\"}', '2026-01-01 08:05:15'),
(976, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/codexpro\\/templates\",\"url\":\"\\/admin\\/projects\\/codexpro\\/templates\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\",\"timestamp\":\"2026-01-01 08:05:20\"}', '2026-01-01 08:05:20'),
(977, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/database-setup\",\"url\":\"\\/admin\\/projects\\/database-setup\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2026-01-01 08:05:20\"}', '2026-01-01 08:05:20'),
(978, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/login?return=%2Fadmin%2Fprojects%2Fdatabase-setup\",\"url\":\"\\/login?return=%2Fadmin%2Fprojects%2Fdatabase-setup\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2026-01-01 08:05:27\"}', '2026-01-01 08:05:27'),
(979, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/login\",\"url\":\"\\/login\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/login?return=%2Fadmin%2Fprojects%2Fdatabase-setup\",\"timestamp\":\"2026-01-01 08:05:40\"}', '2026-01-01 08:05:40'),
(980, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-01 08:06:09\",\"login_method\":\"standard\"}', '2026-01-01 08:06:09'),
(981, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/login?return=%2Fadmin%2Fprojects%2Fdatabase-setup\",\"timestamp\":\"2026-01-01 08:06:09\"}', '2026-01-01 08:06:09'),
(982, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\",\"url\":\"\\/admin\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2026-01-01 08:06:12\"}', '2026-01-01 08:06:12'),
(983, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/codexpro\",\"url\":\"\\/admin\\/projects\\/codexpro\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\",\"timestamp\":\"2026-01-01 08:06:28\"}', '2026-01-01 08:06:28'),
(984, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/database-setup\",\"url\":\"\\/admin\\/projects\\/database-setup\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\",\"timestamp\":\"2026-01-01 08:06:31\"}', '2026-01-01 08:06:31'),
(985, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export\",\"url\":\"\\/admin\\/analytics\\/export\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\",\"timestamp\":\"2026-01-01 08:06:48\"}', '2026-01-01 08:06:48'),
(986, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-02&date_to=2026-01-01&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-02&date_to=2026-01-01&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2026-01-01 08:06:50\"}', '2026-01-01 08:06:50'),
(987, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export\",\"url\":\"\\/admin\\/analytics\\/export\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\",\"timestamp\":\"2026-01-01 08:06:56\"}', '2026-01-01 08:06:56'),
(988, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-02&date_to=2026-01-01&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-02&date_to=2026-01-01&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2026-01-01 08:06:57\"}', '2026-01-01 08:06:57'),
(989, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export\",\"url\":\"\\/admin\\/analytics\\/export\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\",\"timestamp\":\"2026-01-01 08:06:59\"}', '2026-01-01 08:06:59'),
(990, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-02&date_to=2026-01-01&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-02&date_to=2026-01-01&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2026-01-01 08:07:00\"}', '2026-01-01 08:07:00'),
(991, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-02&date_to=2026-01-01&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-02&date_to=2026-01-01&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2026-01-01 08:13:32\"}', '2026-01-01 08:13:32'),
(992, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-02&date_to=2026-01-01&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-02&date_to=2026-01-01&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2026-01-01 08:13:37\"}', '2026-01-01 08:13:37'),
(993, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-31&date_to=2026-01-01&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-31&date_to=2026-01-01&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2026-01-01 08:13:37\"}', '2026-01-01 08:13:37'),
(994, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/users\",\"url\":\"\\/admin\\/users\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2026-01-01 08:13:59\"}', '2026-01-01 08:13:59'),
(995, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export\",\"url\":\"\\/admin\\/analytics\\/export\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\",\"timestamp\":\"2026-01-01 08:14:23\"}', '2026-01-01 08:14:23'),
(996, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-02&date_to=2026-01-01&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-02&date_to=2026-01-01&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2026-01-01 08:14:24\"}', '2026-01-01 08:14:24'),
(997, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-31&date_to=2026-01-01&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-31&date_to=2026-01-01&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2026-01-01 08:14:55\"}', '2026-01-01 08:14:55'),
(998, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-10-03&date_to=2026-01-01&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-10-03&date_to=2026-01-01&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2026-01-01 08:15:07\"}', '2026-01-01 08:15:07'),
(999, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-10-03&date_to=2026-01-01&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-10-03&date_to=2026-01-01&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2026-01-01 08:15:09\"}', '2026-01-01 08:15:09'),
(1000, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-25&date_to=2026-01-01&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-25&date_to=2026-01-01&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2026-01-01 08:15:14\"}', '2026-01-01 08:15:14'),
(1001, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-25&date_to=2026-01-01&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-25&date_to=2026-01-01&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2026-01-01 08:15:16\"}', '2026-01-01 08:15:16'),
(1002, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-25&date_to=2026-01-01&timeframe=day\",\"url\":\"\\/admin\\/analytics\\/export?ajax=1&action=stats&date_from=2025-12-25&date_to=2026-01-01&timeframe=day\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2026-01-01 08:15:24\"}', '2026-01-01 08:15:24'),
(1003, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/dashboard\",\"url\":\"\\/admin\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/export\",\"timestamp\":\"2026-01-01 08:15:26\"}', '2026-01-01 08:15:26'),
(1004, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\",\"url\":\"\\/admin\\/projects\\/proshare\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/dashboard\",\"timestamp\":\"2026-01-01 08:15:35\"}', '2026-01-01 08:15:35'),
(1005, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/dashboard\",\"timestamp\":\"2026-01-01 08:16:07\"}', '2026-01-01 08:16:07'),
(1006, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/settings\",\"url\":\"\\/admin\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/dashboard\",\"timestamp\":\"2026-01-01 08:16:14\"}', '2026-01-01 08:16:14'),
(1007, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/settings\",\"url\":\"\\/admin\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/dashboard\",\"timestamp\":\"2026-01-01 08:16:25\"}', '2026-01-01 08:16:25'),
(1008, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/home-content\",\"url\":\"\\/admin\\/home-content\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/dashboard\",\"timestamp\":\"2026-01-01 08:16:29\"}', '2026-01-01 08:16:29'),
(1009, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/settings\",\"url\":\"\\/admin\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/home-content\",\"timestamp\":\"2026-01-01 08:16:51\"}', '2026-01-01 08:16:51'),
(1010, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/navbar\",\"url\":\"\\/admin\\/navbar\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/home-content\",\"timestamp\":\"2026-01-01 08:17:04\"}', '2026-01-01 08:17:04'),
(1011, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/settings\\/maintenance\",\"url\":\"\\/admin\\/settings\\/maintenance\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/navbar\",\"timestamp\":\"2026-01-01 08:17:08\"}', '2026-01-01 08:17:08'),
(1012, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/settings\\/features\",\"url\":\"\\/admin\\/settings\\/features\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/navbar\",\"timestamp\":\"2026-01-01 08:17:21\"}', '2026-01-01 08:17:21'),
(1013, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/cache\",\"url\":\"\\/admin\\/performance\\/cache\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/settings\\/features\",\"timestamp\":\"2026-01-01 08:18:25\"}', '2026-01-01 08:18:25'),
(1014, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/assets\",\"url\":\"\\/admin\\/performance\\/assets\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/cache\",\"timestamp\":\"2026-01-01 08:19:15\"}', '2026-01-01 08:19:15'),
(1015, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/database\",\"url\":\"\\/admin\\/performance\\/database\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/assets\",\"timestamp\":\"2026-01-01 08:19:19\"}', '2026-01-01 08:19:19'),
(1016, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/database\",\"timestamp\":\"2026-01-01 08:19:30\"}', '2026-01-01 08:19:30'),
(1017, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/performance\\/monitoring\",\"url\":\"\\/admin\\/performance\\/monitoring\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2026-01-01 08:20:01\"}', '2026-01-01 08:20:01'),
(1018, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/email\\/queue\",\"url\":\"\\/admin\\/email\\/queue\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2026-01-01 08:20:10\"}', '2026-01-01 08:20:10'),
(1019, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/analytics\\/overview\",\"url\":\"\\/admin\\/analytics\\/overview\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/performance\\/monitoring\",\"timestamp\":\"2026-01-01 08:20:13\"}', '2026-01-01 08:20:13'),
(1020, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/proshare\\/user-dashboard\",\"url\":\"\\/admin\\/projects\\/proshare\\/user-dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/analytics\\/overview\",\"timestamp\":\"2026-01-01 08:20:21\"}', '2026-01-01 08:20:21'),
(1021, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/codexpro\",\"url\":\"\\/admin\\/projects\\/codexpro\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/user-dashboard\",\"timestamp\":\"2026-01-01 08:20:25\"}', '2026-01-01 08:20:25'),
(1022, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/codexpro\\/settings\",\"url\":\"\\/admin\\/projects\\/codexpro\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/user-dashboard\",\"timestamp\":\"2026-01-01 08:20:27\"}', '2026-01-01 08:20:27'),
(1023, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/codexpro\\/settings\",\"url\":\"\\/admin\\/projects\\/codexpro\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/user-dashboard\",\"timestamp\":\"2026-01-01 08:44:09\"}', '2026-01-01 08:44:09'),
(1024, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/settings\\/maintenance\",\"url\":\"\\/admin\\/settings\\/maintenance\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/codexpro\\/settings\",\"timestamp\":\"2026-01-01 08:44:37\"}', '2026-01-01 08:44:37'),
(1025, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/settings\\/maintenance\",\"url\":\"\\/admin\\/settings\\/maintenance\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/settings\\/maintenance\",\"timestamp\":\"2026-01-01 08:44:47\"}', '2026-01-01 08:44:47'),
(1026, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/settings\\/maintenance\",\"url\":\"\\/admin\\/settings\\/maintenance\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/settings\\/maintenance\",\"timestamp\":\"2026-01-01 08:44:48\"}', '2026-01-01 08:44:48'),
(1027, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/settings\\/maintenance\",\"timestamp\":\"2026-01-01 08:44:55\"}', '2026-01-01 08:44:55'),
(1028, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/settings\\/maintenance\",\"timestamp\":\"2026-01-01 08:44:59\"}', '2026-01-01 08:44:59'),
(1029, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/settings\\/maintenance\",\"url\":\"\\/admin\\/settings\\/maintenance\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/settings\\/maintenance\",\"timestamp\":\"2026-01-01 08:45:04\"}', '2026-01-01 08:45:04'),
(1030, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/settings\\/maintenance\",\"url\":\"\\/admin\\/settings\\/maintenance\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/settings\\/maintenance\",\"timestamp\":\"2026-01-01 08:45:04\"}', '2026-01-01 08:45:04'),
(1031, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/settings\\/maintenance\",\"url\":\"\\/admin\\/settings\\/maintenance\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/settings\\/maintenance\",\"timestamp\":\"2026-01-01 08:45:07\"}', '2026-01-01 08:45:07'),
(1032, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/settings\\/maintenance\",\"url\":\"\\/admin\\/settings\\/maintenance\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/settings\\/maintenance\",\"timestamp\":\"2026-01-01 08:45:08\"}', '2026-01-01 08:45:08'),
(1033, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:10ff:99bd:1db8:c498', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/projects\\/proshare\\/upload\",\"url\":\"\\/projects\\/proshare\\/upload\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/\",\"timestamp\":\"2026-01-01 08:45:18\"}', '2026-01-01 08:45:18'),
(1034, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:10ff:99bd:1db8:c498', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/projects\\/proshare\\/upload\",\"url\":\"\\/projects\\/proshare\\/upload\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/\",\"timestamp\":\"2026-01-01 08:45:18\"}', '2026-01-01 08:45:18'),
(1035, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:10ff:99bd:1db8:c498', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/projects\\/proshare\\/upload\",\"url\":\"\\/projects\\/proshare\\/upload\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/\",\"timestamp\":\"2026-01-01 08:45:19\"}', '2026-01-01 08:45:19'),
(1036, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:10ff:99bd:1db8:c498', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/projects\\/proshare\\/upload\",\"url\":\"\\/projects\\/proshare\\/upload\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/projects\\/proshare\\/\",\"timestamp\":\"2026-01-01 08:45:19\"}', '2026-01-01 08:45:19'),
(1037, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:10ff:99bd:1db8:c498', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":null,\"timestamp\":\"2026-01-01 08:45:24\"}', '2026-01-01 08:45:24'),
(1038, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:10ff:99bd:1db8:c498', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/logout\",\"url\":\"\\/logout\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2026-01-01 08:45:29\"}', '2026-01-01 08:45:29'),
(1039, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:d46d:10ff:99bd:1db8:c498', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2026-01-01 08:45:30\"}', '2026-01-01 08:45:30'),
(1040, 'platform', 'page', 0, 'page_visit', NULL, '2401:4900:8fc2:d46d:10ff:99bd:1db8:c498', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2026-01-01 08:45:33\"}', '2026-01-01 08:45:33');
INSERT INTO `analytics_events` (`id`, `project`, `resource_type`, `resource_id`, `event_type`, `user_id`, `ip_address`, `user_agent`, `browser`, `platform`, `country`, `metadata`, `created_at`) VALUES
(1041, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/settings\\/maintenance\",\"url\":\"\\/admin\\/settings\\/maintenance\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/settings\\/maintenance\",\"timestamp\":\"2026-01-01 08:45:39\"}', '2026-01-01 08:45:39'),
(1042, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/settings\\/maintenance\",\"url\":\"\\/admin\\/settings\\/maintenance\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/settings\\/maintenance\",\"timestamp\":\"2026-01-01 08:45:40\"}', '2026-01-01 08:45:40'),
(1043, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/settings\\/maintenance\",\"url\":\"\\/admin\\/settings\\/maintenance\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/settings\\/maintenance\",\"timestamp\":\"2026-01-01 08:45:43\"}', '2026-01-01 08:45:43'),
(1044, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/settings\\/maintenance\",\"url\":\"\\/admin\\/settings\\/maintenance\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/settings\\/maintenance\",\"timestamp\":\"2026-01-01 08:45:45\"}', '2026-01-01 08:45:45'),
(1045, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/settings\\/maintenance\",\"url\":\"\\/admin\\/settings\\/maintenance\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/settings\\/maintenance\",\"timestamp\":\"2026-01-01 08:45:48\"}', '2026-01-01 08:45:48'),
(1046, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/settings\\/maintenance\",\"url\":\"\\/admin\\/settings\\/maintenance\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/codexpro\\/settings\",\"timestamp\":\"2026-01-01 08:45:51\"}', '2026-01-01 08:45:51'),
(1047, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/codexpro\\/settings\",\"url\":\"\\/admin\\/projects\\/codexpro\\/settings\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/proshare\\/user-dashboard\",\"timestamp\":\"2026-01-01 08:45:53\"}', '2026-01-01 08:45:53'),
(1048, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/dashboard\",\"url\":\"\\/admin\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/codexpro\\/settings\",\"timestamp\":\"2026-01-01 08:46:31\"}', '2026-01-01 08:46:31'),
(1049, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/navbar\",\"url\":\"\\/admin\\/navbar\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/codexpro\\/settings\",\"timestamp\":\"2026-01-01 08:46:46\"}', '2026-01-01 08:46:46'),
(1050, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/settings\",\"url\":\"\\/admin\\/settings\",\"referer\":null,\"timestamp\":\"2026-01-01 08:46:58\"}', '2026-01-01 08:46:58'),
(1051, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/settings\\/maintenance\",\"url\":\"\\/admin\\/settings\\/maintenance\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/settings\",\"timestamp\":\"2026-01-01 08:47:02\"}', '2026-01-01 08:47:02'),
(1052, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/database-setup\",\"url\":\"\\/admin\\/projects\\/database-setup\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/settings\\/maintenance\",\"timestamp\":\"2026-01-01 08:48:13\"}', '2026-01-01 08:48:13'),
(1053, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-01 16:37:18\",\"login_method\":\"standard\"}', '2026-01-01 16:37:18'),
(1054, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-02 00:06:12\",\"login_method\":\"standard\"}', '2026-01-02 00:06:12'),
(1055, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-02 03:09:58\",\"login_method\":\"standard\"}', '2026-01-02 03:09:58'),
(1056, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-02 03:32:50\",\"login_method\":\"standard\"}', '2026-01-02 03:32:50'),
(1057, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-02 05:29:24\",\"login_method\":\"standard\"}', '2026-01-02 05:29:24'),
(1058, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2026-01-02 07:05:09\"}', '2026-01-02 07:05:11'),
(1059, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2026-01-02 07:05:14\"}', '2026-01-02 07:05:14'),
(1060, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/login?return=%2Fprojects%2Fproshare%2F\",\"url\":\"\\/login?return=%2Fprojects%2Fproshare%2F\",\"referer\":null,\"timestamp\":\"2026-01-02 07:05:22\"}', '2026-01-02 07:05:22'),
(1061, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":null,\"timestamp\":\"2026-01-02 07:05:22\"}', '2026-01-02 07:05:22'),
(1062, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/login?return=%2Fprojects%2Fproshare%2F\",\"url\":\"\\/login?return=%2Fprojects%2Fproshare%2F\",\"referer\":null,\"timestamp\":\"2026-01-02 07:05:29\"}', '2026-01-02 07:05:29'),
(1063, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":null,\"timestamp\":\"2026-01-02 07:05:30\"}', '2026-01-02 07:05:30'),
(1064, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/login?return=%2Fprojects%2Fproshare%2F\",\"url\":\"\\/login?return=%2Fprojects%2Fproshare%2F\",\"referer\":\"http:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2026-01-02 07:05:34\"}', '2026-01-02 07:05:34'),
(1065, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"http:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2026-01-02 07:05:35\"}', '2026-01-02 07:05:35'),
(1066, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"http:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2026-01-02 07:06:29\"}', '2026-01-02 07:06:29'),
(1067, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2026-01-02 07:19:08\"}', '2026-01-02 07:19:08'),
(1068, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/\",\"timestamp\":\"2026-01-02 07:19:11\"}', '2026-01-02 07:19:11'),
(1069, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/login?return=%2Fprojects%2Fproshare%2F\",\"url\":\"\\/login?return=%2Fprojects%2Fproshare%2F\",\"referer\":null,\"timestamp\":\"2026-01-02 07:22:42\"}', '2026-01-02 07:22:42'),
(1070, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/dashboard\",\"url\":\"\\/dashboard\",\"referer\":null,\"timestamp\":\"2026-01-02 07:22:43\"}', '2026-01-02 07:22:43'),
(1071, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\",\"url\":\"\\/admin\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/dashboard\",\"timestamp\":\"2026-01-02 07:22:46\"}', '2026-01-02 07:22:46'),
(1072, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/database-setup\",\"url\":\"\\/admin\\/projects\\/database-setup\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\",\"timestamp\":\"2026-01-02 07:22:50\"}', '2026-01-02 07:22:50'),
(1073, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/database-setup\\/codexpro\",\"url\":\"\\/admin\\/projects\\/database-setup\\/codexpro\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\",\"timestamp\":\"2026-01-02 07:22:54\"}', '2026-01-02 07:22:54'),
(1074, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/database-setup\\/save\",\"url\":\"\\/admin\\/projects\\/database-setup\\/save\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\\/codexpro\",\"timestamp\":\"2026-01-02 07:23:00\"}', '2026-01-02 07:23:00'),
(1075, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/database-setup\",\"url\":\"\\/admin\\/projects\\/database-setup\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\\/codexpro\",\"timestamp\":\"2026-01-02 07:23:01\"}', '2026-01-02 07:23:01'),
(1076, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/database-setup\\/codexpro\",\"url\":\"\\/admin\\/projects\\/database-setup\\/codexpro\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\",\"timestamp\":\"2026-01-02 07:23:53\"}', '2026-01-02 07:23:53'),
(1077, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/database-setup\\/save\",\"url\":\"\\/admin\\/projects\\/database-setup\\/save\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\\/codexpro\",\"timestamp\":\"2026-01-02 07:23:59\"}', '2026-01-02 07:23:59'),
(1078, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/database-setup\",\"url\":\"\\/admin\\/projects\\/database-setup\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\\/codexpro\",\"timestamp\":\"2026-01-02 07:24:00\"}', '2026-01-02 07:24:00'),
(1079, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/database-setup\\/imgtxt\",\"url\":\"\\/admin\\/projects\\/database-setup\\/imgtxt\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\",\"timestamp\":\"2026-01-02 07:24:02\"}', '2026-01-02 07:24:02'),
(1080, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/database-setup\\/imgtxt\",\"url\":\"\\/admin\\/projects\\/database-setup\\/imgtxt\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\",\"timestamp\":\"2026-01-02 07:24:11\"}', '2026-01-02 07:24:11'),
(1081, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/database-setup\\/save\",\"url\":\"\\/admin\\/projects\\/database-setup\\/save\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\\/imgtxt\",\"timestamp\":\"2026-01-02 07:24:18\"}', '2026-01-02 07:24:18'),
(1082, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/database-setup\",\"url\":\"\\/admin\\/projects\\/database-setup\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\\/imgtxt\",\"timestamp\":\"2026-01-02 07:24:19\"}', '2026-01-02 07:24:19'),
(1083, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/database-setup\\/proshare\",\"url\":\"\\/admin\\/projects\\/database-setup\\/proshare\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\",\"timestamp\":\"2026-01-02 07:24:23\"}', '2026-01-02 07:24:23'),
(1084, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/database-setup\\/save\",\"url\":\"\\/admin\\/projects\\/database-setup\\/save\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\\/proshare\",\"timestamp\":\"2026-01-02 07:24:28\"}', '2026-01-02 07:24:28'),
(1085, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/admin\\/projects\\/database-setup\",\"url\":\"\\/admin\\/projects\\/database-setup\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\\/proshare\",\"timestamp\":\"2026-01-02 07:24:29\"}', '2026-01-02 07:24:29'),
(1086, 'platform', 'page', 0, 'page_visit', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"page\":\"\\/\",\"url\":\"\\/\",\"referer\":\"https:\\/\\/test.mymultibranch.com\\/admin\\/projects\\/database-setup\",\"timestamp\":\"2026-01-02 07:24:31\"}', '2026-01-02 07:24:31'),
(1087, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-02 07:47:49\",\"login_method\":\"standard\"}', '2026-01-02 07:47:49'),
(1088, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-02 08:59:31\",\"login_method\":\"standard\"}', '2026-01-02 08:59:31'),
(1089, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-02 09:50:34\",\"login_method\":\"standard\"}', '2026-01-02 09:50:34'),
(1090, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-02 12:49:26\",\"login_method\":\"standard\"}', '2026-01-02 12:49:26'),
(1091, 'platform', 'auth', 4, 'user_login', 4, '2401:4900:8fc2:d46d:fdaf:5439:7931:256b', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"timestamp\":\"2026-01-02 15:07:41\",\"login_method\":\"standard\"}', '2026-01-02 15:07:41'),
(1092, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-03 02:05:48\",\"login_method\":\"standard\"}', '2026-01-03 02:05:48'),
(1093, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-03 04:45:01\",\"login_method\":\"standard\"}', '2026-01-03 04:45:01'),
(1094, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-03 05:18:53\",\"login_method\":\"standard\"}', '2026-01-03 05:18:53'),
(1095, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-03 05:49:20\",\"login_method\":\"standard\"}', '2026-01-03 05:49:20'),
(1096, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-03 05:59:02\",\"login_method\":\"standard\"}', '2026-01-03 05:59:02'),
(1097, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-03 08:39:39\",\"login_method\":\"standard\"}', '2026-01-03 08:39:39'),
(1098, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-03 12:10:11\",\"login_method\":\"standard\"}', '2026-01-03 12:10:11'),
(1099, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-03 12:16:50\",\"login_method\":\"standard\"}', '2026-01-03 12:16:50'),
(1100, 'platform', 'auth', 3, 'user_login', 3, '106.215.138.141', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'Safari', 'macOS', 'IN', '{\"timestamp\":\"2026-01-04 01:37:00\",\"login_method\":\"standard\"}', '2026-01-04 01:37:00'),
(1101, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-04 01:44:04\",\"login_method\":\"standard\"}', '2026-01-04 01:44:04'),
(1102, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-04 02:15:08\",\"login_method\":\"standard\"}', '2026-01-04 02:15:08'),
(1103, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-04 02:27:07\",\"login_method\":\"standard\"}', '2026-01-04 02:27:07'),
(1104, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-04 03:27:31\",\"login_method\":\"standard\"}', '2026-01-04 03:27:31'),
(1105, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-04 06:11:39\",\"login_method\":\"standard\"}', '2026-01-04 06:11:39'),
(1106, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-04 07:59:41\",\"login_method\":\"standard\"}', '2026-01-04 07:59:41'),
(1107, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-06 01:55:59\",\"login_method\":\"standard\"}', '2026-01-06 01:55:59'),
(1108, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-06 13:13:19\",\"login_method\":\"standard\"}', '2026-01-06 13:13:19'),
(1109, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-06 13:16:03\",\"login_method\":\"standard\"}', '2026-01-06 13:16:03'),
(1110, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-06 15:10:14\",\"login_method\":\"standard\"}', '2026-01-06 15:10:14'),
(1111, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-07 01:33:43\",\"login_method\":\"standard\"}', '2026-01-07 01:33:43'),
(1112, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-07 02:58:24\",\"login_method\":\"standard\"}', '2026-01-07 02:58:24'),
(1113, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-07 03:37:34\",\"login_method\":\"standard\"}', '2026-01-07 03:37:34'),
(1114, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-07 04:07:30\",\"login_method\":\"standard\"}', '2026-01-07 04:07:30'),
(1115, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-07 05:22:29\",\"login_method\":\"standard\"}', '2026-01-07 05:22:29'),
(1116, 'platform', 'auth', 3, 'user_login', 3, '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Chrome', 'Windows', 'IN', '{\"timestamp\":\"2026-01-07 05:45:03\",\"login_method\":\"standard\"}', '2026-01-07 05:45:03');

-- --------------------------------------------------------

--
-- Table structure for table `api_keys`
--

CREATE TABLE `api_keys` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `api_key` varchar(100) NOT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`permissions`)),
  `is_active` tinyint(1) DEFAULT 1,
  `expires_at` timestamp NULL DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `revoked_at` timestamp NULL DEFAULT NULL,
  `request_count` int(10) UNSIGNED DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `api_request_logs`
--

CREATE TABLE `api_request_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `api_key_id` int(10) UNSIGNED NOT NULL,
  `endpoint` varchar(255) NOT NULL,
  `method` varchar(10) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `status_code` int(11) NOT NULL,
  `response_time` int(11) NOT NULL COMMENT 'Milliseconds',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blocked_ips`
--

CREATE TABLE `blocked_ips` (
  `id` int(10) UNSIGNED NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `blocked_by` int(10) UNSIGNED DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `custom_short_links`
--

CREATE TABLE `custom_short_links` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `project` varchar(50) NOT NULL,
  `resource_type` varchar(50) NOT NULL,
  `resource_id` int(10) UNSIGNED NOT NULL,
  `custom_slug` varchar(100) NOT NULL,
  `original_slug` varchar(100) NOT NULL,
  `clicks` int(10) UNSIGNED DEFAULT 0,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_queue`
--

CREATE TABLE `email_queue` (
  `id` int(10) UNSIGNED NOT NULL,
  `to_email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` longtext NOT NULL,
  `cc` varchar(512) DEFAULT NULL,
  `bcc` varchar(512) DEFAULT NULL,
  `reply_to` varchar(255) DEFAULT NULL,
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attachments`)),
  `priority` tinyint(4) DEFAULT 5,
  `attempts` int(11) DEFAULT 0,
  `max_attempts` int(11) DEFAULT 3,
  `status` enum('pending','processing','sent','failed') DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_logins`
--

CREATE TABLE `failed_logins` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempted_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `failed_logins`
--

INSERT INTO `failed_logins` (`id`, `username`, `ip_address`, `attempted_at`) VALUES
(1, 'testuser@te.testuserstuser', '136.226.251.10', '2025-12-03 21:12:26'),
(2, '-testuser@testuser.testuser', '106.215.140.20', '2025-12-04 09:32:57'),
(3, 'testuser@gmail.com', '2401:4900:8fc1:6d3e:a0a6:3432:687c:6474', '2025-12-08 13:01:58');

-- --------------------------------------------------------

--
-- Table structure for table `feature_flags`
--

CREATE TABLE `feature_flags` (
  `id` int(10) UNSIGNED NOT NULL,
  `feature_name` varchar(100) NOT NULL,
  `is_enabled` tinyint(1) DEFAULT 0,
  `description` text DEFAULT NULL,
  `rollout_percentage` int(11) DEFAULT 0 COMMENT '0-100',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `feature_flags`
--

INSERT INTO `feature_flags` (`id`, `feature_name`, `is_enabled`, `description`, `rollout_percentage`, `created_at`, `updated_at`) VALUES
(1, 'realtime_collaboration', 1, 'Enable real-time collaboration in CodeXPro', 0, '2025-12-03 20:23:53', '2025-12-03 20:23:53'),
(2, 'batch_ocr_processing', 1, 'Enable batch OCR processing in ImgTxt', 0, '2025-12-03 20:23:53', '2025-12-03 20:23:53'),
(3, 'advanced_sharing', 1, 'Enable advanced sharing features in ProShare', 0, '2025-12-03 20:23:53', '2026-01-01 12:12:32'),
(4, 'api_access', 1, 'Enable REST API access', 0, '2025-12-03 20:23:53', '2025-12-04 08:26:07'),
(5, 'email_notifications', 1, 'Enable email notifications', 0, '2025-12-03 20:23:53', '2025-12-03 20:23:53'),
(6, 'analytics_tracking', 1, 'Enable analytics and usage tracking', 0, '2025-12-03 20:23:53', '2025-12-03 20:23:53'),
(7, 'custom_templates', 1, 'Enable custom code templates', 0, '2025-12-03 20:23:53', '2025-12-03 20:23:53'),
(8, 'table_detection', 1, 'Enable table detection in OCR', 0, '2025-12-03 20:23:53', '2025-12-03 20:23:53'),
(9, 'pdf_processing', 1, 'Enable multi-page PDF processing', 0, '2025-12-03 20:23:53', '2025-12-03 20:23:53');

-- --------------------------------------------------------

--
-- Table structure for table `home_content`
--

CREATE TABLE `home_content` (
  `id` int(10) UNSIGNED NOT NULL,
  `section` varchar(50) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `subtitle` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `button_text` varchar(100) DEFAULT NULL,
  `button_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `home_content`
--

INSERT INTO `home_content` (`id`, `section`, `title`, `subtitle`, `description`, `image_url`, `button_text`, `button_url`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'hero', 'Welcome to MyMultiBranch', 'Explore Our Digital Universe Eco-System', 'A modern, Step into your all-in-one digital launchpad fast, intuitive, and visually stunning. Designed with a sleek, futuristic interface, it brings all your favorite tools together in one seamless, easy-to-use space. Click any card to preview or visit directly', '/public/uploads/home/hero_695795f45bb1a_1767347700.png', NULL, NULL, 1, 1, '2025-12-07 22:28:46', '2026-01-02 09:55:00'),
(2, 'projects_section', 'Explore Our Lightining Fast Tools', NULL, NULL, NULL, NULL, NULL, 1, 2, '2025-12-07 22:28:46', '2025-12-13 21:30:31');

-- --------------------------------------------------------

--
-- Table structure for table `home_projects`
--

CREATE TABLE `home_projects` (
  `id` int(10) UNSIGNED NOT NULL,
  `project_key` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `tier` enum('free','freemium','enterprise') DEFAULT 'free',
  `features` text DEFAULT NULL,
  `is_enabled` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `database_name` varchar(50) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `home_projects`
--

INSERT INTO `home_projects` (`id`, `project_key`, `name`, `description`, `image_url`, `icon`, `color`, `tier`, `features`, `is_enabled`, `sort_order`, `database_name`, `url`, `created_at`, `updated_at`) VALUES
(1, 'codexpro', 'CodeXPro', 'Advanced code editor and IDE platform', '', 'code', '#00f0ff', 'free', '[\"Advanced editor capabilities\",\"Real-time collaboration\",\"Cloud sync & backup\"]', 1, 1, 'mmb_codexpro', '/projects/codexpro', '2025-12-07 22:28:46', '2026-01-02 14:16:49'),
(2, 'devzone', 'DevZone', 'Developer collaboration and project management', '', 'users', '#ff2ec4', 'free', '[\"Team collaboration tools\",\"Project management\",\"Issue tracking\"]', 1, 2, 'mmb_devzone', '/projects/devzone', '2025-12-07 22:28:46', '2026-01-02 14:15:46'),
(3, 'imgtxt', 'ImgTxt', 'Image to text converter and OCR tool', '', 'image', '#00ff88', 'free', '[\"Image to text conversion\",\"Multi-language OCR\",\"Batch processing\"]', 1, 3, 'mmb_imgtxt', '/projects/imgtxt', '2025-12-07 22:28:46', '2026-01-02 14:17:08'),
(4, 'proshare', 'ProShare', 'Secure file sharing platform', NULL, 'share-2', '#ffaa00', 'freemium', '[\"Secure file sharing\",\"Password protection\",\"Download tracking\"]', 1, 4, 'mmb_proshare', '/projects/proshare', '2025-12-07 22:28:46', '2026-01-02 11:19:07'),
(5, 'qr', 'QR Generator', 'QR code generation and management', NULL, 'grid', '#9945ff', 'free', '[\"Custom QR codes\",\"Bulk generation\",\"Analytics tracking\"]', 1, 5, 'mmb_qr', '/projects/qr', '2025-12-07 22:28:46', '2026-01-02 11:19:07'),
(6, 'resumex', 'ResumeX', 'Professional resume builder', '', 'file-text', '#ff6b6b', 'free', '[\"Professional templates\",\"PDF export\",\"ATS optimization\"]', 0, 6, 'mmb_resumex', '/projects/resumex', '2025-12-07 22:28:46', '2026-01-02 11:19:07');

-- --------------------------------------------------------

--
-- Table structure for table `home_sections`
--

CREATE TABLE `home_sections` (
  `id` int(11) NOT NULL,
  `section_key` varchar(50) NOT NULL,
  `heading` varchar(255) NOT NULL,
  `subheading` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `home_sections`
--

INSERT INTO `home_sections` (`id`, `section_key`, `heading`, `subheading`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'stats', 'Our Impact in Numbers', 'Trusted by developers and teams worldwide', 1, 1, '2025-12-08 13:04:31', '2025-12-08 13:04:31'),
(2, 'timeline', 'Our Journey', 'Milestones and achievements that shaped our platform', 1, 2, '2025-12-08 13:04:31', '2026-01-02 07:34:40'),
(3, 'features', 'Platform Features', 'Powerful capabilities across all projects', 1, 3, '2025-12-08 13:04:31', '2026-01-02 09:03:34');

-- --------------------------------------------------------

--
-- Table structure for table `home_stats`
--

CREATE TABLE `home_stats` (
  `id` int(10) UNSIGNED NOT NULL,
  `label` varchar(100) NOT NULL,
  `count_value` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `prefix` varchar(10) DEFAULT NULL,
  `suffix` varchar(10) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `color` varchar(20) DEFAULT '#00f0ff',
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `home_stats`
--

INSERT INTO `home_stats` (`id`, `label`, `count_value`, `prefix`, `suffix`, `icon`, `color`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Active Users', 10000, '', '+', 'users', '#00f0ff', 1, 1, '2025-12-08 12:36:00', '2026-01-02 05:08:17'),
(2, 'Applications', 6, NULL, NULL, 'grid', '#ff2ec4', 1, 2, '2025-12-08 12:36:00', NULL),
(3, 'Projects Completed', 500, NULL, '+', 'check-circle', '#00ff88', 1, 3, '2025-12-08 12:36:00', NULL),
(4, 'Uptime', 99, '', '%', 'activity', '#ffaa00', 1, 4, '2025-12-08 12:36:00', '2025-12-08 15:16:07');

-- --------------------------------------------------------

--
-- Table structure for table `home_timeline`
--

CREATE TABLE `home_timeline` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `date_display` varchar(50) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `color` varchar(20) DEFAULT '#00f0ff',
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `home_timeline`
--

INSERT INTO `home_timeline` (`id`, `title`, `description`, `date_display`, `icon`, `color`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Platform Launch', 'Launched MyMultiBranch platform with core authentication system', '2024', 'rocket', '#00f0ff', 1, 1, '2025-12-08 12:36:00', NULL),
(2, 'Multi-Project Support', 'Added ability to manage multiple applications from single dashboard', '2024 Q2', 'grid', '#ff2ec4', 1, 2, '2025-12-08 12:36:00', NULL),
(3, 'Enhanced Security', 'Implemented advanced security features including 2FA and audit logs', '2024 Q3', 'shield', '#00ff88', 1, 3, '2025-12-08 12:36:00', NULL),
(4, 'API Integration', 'Released comprehensive REST API for external integrations', '2024 Q4', 'code', '#ffaa00', 1, 4, '2025-12-08 12:36:00', NULL),
(5, 'Future Plans', 'AI-powered features and advanced analytics coming soon', 'Coming Soon', 'star', '#9945ff', 1, 5, '2025-12-08 12:36:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `login_history`
--

CREATE TABLE `login_history` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `login_method` enum('email_password','google_oauth','remember_token','2fa') DEFAULT 'email_password',
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `status` enum('success','failed','blocked') DEFAULT 'success',
  `failure_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `login_history`
--

INSERT INTO `login_history` (`id`, `user_id`, `email`, `login_method`, `ip_address`, `user_agent`, `status`, `failure_reason`, `created_at`) VALUES
(1, 3, 'admin@mymultibranch.com', 'email_password', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', NULL, '2026-01-03 05:18:53'),
(2, 3, 'admin@mymultibranch.com', 'email_password', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', NULL, '2026-01-03 05:49:20'),
(3, NULL, 'farukahmed8565@gmail.com', 'google_oauth', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', NULL, '2026-01-03 05:57:44'),
(4, NULL, 'farukahmed8565@gmail.com', 'google_oauth', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', NULL, '2026-01-03 05:58:02'),
(5, 3, 'admin@mymultibranch.com', 'email_password', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', NULL, '2026-01-03 05:59:02'),
(6, 3, 'admin@mymultibranch.com', 'email_password', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', NULL, '2026-01-03 08:39:39'),
(7, NULL, 'farukahmed8565@gmail.com', 'google_oauth', '2401:4900:8fc2:d46d:5c2:1895:1c86:997', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'success', NULL, '2026-01-03 08:43:45'),
(8, NULL, 'farukahmed8565@gmail.com', 'google_oauth', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', NULL, '2026-01-03 09:07:56'),
(9, NULL, 'farukahmed8565@gmail.com', 'google_oauth', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', NULL, '2026-01-03 09:31:47'),
(10, NULL, 'farukahmed8565@gmail.com', 'google_oauth', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', NULL, '2026-01-03 09:35:31'),
(11, NULL, 'farukahmed8565@gmail.com', 'google_oauth', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', NULL, '2026-01-03 09:40:17'),
(12, 7, 'farukahmed8565@gmail.com', 'google_oauth', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', NULL, '2026-01-03 09:50:38'),
(13, 7, 'farukahmed8565@gmail.com', 'google_oauth', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', NULL, '2026-01-03 10:01:51'),
(14, 7, 'farukahmed8565@gmail.com', 'google_oauth', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', NULL, '2026-01-03 10:11:53'),
(15, 7, 'farukahmed8565@gmail.com', 'google_oauth', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', NULL, '2026-01-03 11:27:01'),
(16, 3, 'admin@mymultibranch.com', 'email_password', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', NULL, '2026-01-03 12:10:11'),
(17, 7, 'farukahmed8565@gmail.com', 'google_oauth', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', NULL, '2026-01-03 12:15:03'),
(18, 3, 'admin@mymultibranch.com', 'email_password', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', NULL, '2026-01-03 12:16:50'),
(19, 7, 'farukahmed8565@gmail.com', 'google_oauth', '2401:4900:8fc1:920a:29ea:20a:136a:cfe5', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'success', NULL, '2026-01-04 01:36:19'),
(20, 3, 'admin@mymultibranch.com', 'email_password', '106.215.138.141', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', 'success', NULL, '2026-01-04 01:37:00'),
(21, 3, 'admin@mymultibranch.com', 'email_password', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', NULL, '2026-01-04 01:44:03'),
(22, 3, 'admin@mymultibranch.com', 'email_password', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', NULL, '2026-01-04 02:15:08'),
(23, 3, 'admin@mymultibranch.com', 'email_password', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', NULL, '2026-01-04 02:27:07'),
(24, 3, 'admin@mymultibranch.com', 'email_password', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', NULL, '2026-01-04 03:27:31'),
(25, 3, 'admin@mymultibranch.com', 'email_password', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', NULL, '2026-01-04 06:11:39'),
(26, 3, 'admin@mymultibranch.com', 'email_password', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', NULL, '2026-01-04 07:59:41'),
(27, 3, 'admin@mymultibranch.com', 'email_password', '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', NULL, '2026-01-06 01:55:59'),
(28, 7, 'farukahmed8565@gmail.com', 'google_oauth', '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', NULL, '2026-01-06 13:12:00'),
(29, 3, 'admin@mymultibranch.com', 'email_password', '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', NULL, '2026-01-06 13:13:18'),
(30, 3, 'admin@mymultibranch.com', 'email_password', '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', NULL, '2026-01-06 13:16:02'),
(31, 3, 'admin@mymultibranch.com', 'email_password', '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', NULL, '2026-01-06 15:10:14'),
(32, 3, 'admin@mymultibranch.com', 'email_password', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', NULL, '2026-01-07 01:33:43'),
(33, 3, 'admin@mymultibranch.com', 'email_password', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', NULL, '2026-01-07 02:58:24'),
(34, 3, 'admin@mymultibranch.com', 'email_password', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', NULL, '2026-01-07 03:37:34'),
(35, 3, 'admin@mymultibranch.com', 'email_password', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', NULL, '2026-01-07 04:07:30'),
(36, 3, 'admin@mymultibranch.com', 'email_password', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', NULL, '2026-01-07 05:22:29'),
(37, 3, 'admin@mymultibranch.com', 'email_password', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'success', NULL, '2026-01-07 05:45:03');

-- --------------------------------------------------------

--
-- Table structure for table `mail_abuse_reports`
--

CREATE TABLE `mail_abuse_reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reporter_email` varchar(255) DEFAULT NULL,
  `reported_mailbox_id` int(10) UNSIGNED DEFAULT NULL,
  `reported_domain_id` int(10) UNSIGNED DEFAULT NULL,
  `report_type` enum('spam','phishing','malware','harassment','other') NOT NULL,
  `report_description` text NOT NULL,
  `evidence` text DEFAULT NULL COMMENT 'JSON evidence data',
  `status` enum('pending','investigating','resolved','dismissed') DEFAULT 'pending',
  `action_taken` text DEFAULT NULL,
  `handled_by_admin_id` int(10) UNSIGNED DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_admin_actions`
--

CREATE TABLE `mail_admin_actions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_user_id` int(10) UNSIGNED NOT NULL COMMENT 'MMB user ID of admin',
  `action_type` varchar(50) NOT NULL,
  `target_type` varchar(50) DEFAULT NULL COMMENT 'subscriber, domain, mailbox, etc.',
  `target_id` int(10) UNSIGNED DEFAULT NULL,
  `action_description` text NOT NULL,
  `metadata` text DEFAULT NULL COMMENT 'JSON additional data',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mail_admin_actions`
--

INSERT INTO `mail_admin_actions` (`id`, `admin_user_id`, `action_type`, `target_type`, `target_id`, `action_description`, `metadata`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 3, 'update_plan', 'plan', 13, 'Updated plan: Free', NULL, '172.70.108.93', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 08:48:59'),
(2, 3, 'set_universal_currency', 'system', 0, 'Set universal currency to: INR', NULL, '172.70.108.92', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 08:49:11'),
(3, 3, 'set_universal_currency', 'system', 0, 'Set universal currency to: INR', NULL, '162.159.122.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 02:00:01'),
(4, 3, 'update_plan', 'plan', 13, 'Updated plan: Free', NULL, '172.68.234.179', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 02:06:08'),
(5, 3, 'update_plan', 'plan', 13, 'Updated plan: Free', NULL, '172.68.234.179', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 02:06:19'),
(6, 3, 'set_universal_currency', 'system', 0, 'Set universal currency to: INR', NULL, '162.158.23.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 02:17:12'),
(7, 3, 'update_plan', 'plan', 2, 'Updated plan: Starter', NULL, '172.70.108.92', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 13:19:53'),
(8, 3, 'update_plan', 'plan', 3, 'Updated plan: Business', NULL, '162.159.122.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 13:21:03'),
(9, 3, 'update_plan', 'plan', 4, 'Updated plan: Developer', NULL, '172.70.108.92', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 15:34:37'),
(10, 3, 'suspend_subscriber', 'subscriber', 1, 'Suspended subscriber. Reason: test-sespend', NULL, '172.70.108.93', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 15:41:41'),
(11, 3, 'activate_subscriber', 'subscriber', 1, 'Activated subscriber', NULL, '172.70.108.92', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 15:42:04'),
(12, 3, 'override_plan', 'subscriber', 1, 'Changed plan to ID 4. Reason: ', NULL, '172.70.108.92', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 02:59:32'),
(13, 3, 'delete_subscriber', 'subscriber', 1, 'Deleted subscriber: test company', NULL, '172.68.234.179', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 04:15:06'),
(14, 3, 'delete_subscriber', 'subscriber', 2, 'Deleted subscriber: testuser', NULL, '172.70.108.93', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 05:58:59');

-- --------------------------------------------------------

--
-- Table structure for table `mail_aliases`
--

CREATE TABLE `mail_aliases` (
  `id` int(10) UNSIGNED NOT NULL,
  `subscriber_id` int(10) UNSIGNED NOT NULL,
  `domain_id` int(10) UNSIGNED NOT NULL,
  `source_email` varchar(255) NOT NULL,
  `alias_email` varchar(255) NOT NULL,
  `destination_type` enum('mailbox','external') DEFAULT 'mailbox',
  `destination_mailbox_id` int(10) UNSIGNED DEFAULT NULL,
  `destination_email` varchar(255) DEFAULT NULL,
  `destination_emails` text NOT NULL COMMENT 'Comma-separated list of destination emails',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_api_keys`
--

CREATE TABLE `mail_api_keys` (
  `id` int(10) UNSIGNED NOT NULL,
  `subscriber_id` int(10) UNSIGNED NOT NULL,
  `mailbox_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'NULL for subscriber-level keys',
  `key_name` varchar(100) NOT NULL,
  `api_key` varchar(64) NOT NULL,
  `api_secret` varchar(255) NOT NULL COMMENT 'Hashed secret',
  `permissions` text DEFAULT NULL COMMENT 'JSON permissions array',
  `rate_limit_per_minute` int(10) UNSIGNED DEFAULT 60,
  `rate_limit_per_day` int(10) UNSIGNED DEFAULT 10000,
  `is_active` tinyint(1) DEFAULT 1,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_api_usage_logs`
--

CREATE TABLE `mail_api_usage_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `api_key_id` int(10) UNSIGNED NOT NULL,
  `endpoint` varchar(255) NOT NULL,
  `method` varchar(10) NOT NULL,
  `status_code` int(10) UNSIGNED NOT NULL,
  `response_time_ms` int(10) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `request_payload` text DEFAULT NULL,
  `response_payload` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_attachments`
--

CREATE TABLE `mail_attachments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `message_id` bigint(20) UNSIGNED DEFAULT NULL,
  `queue_id` bigint(20) UNSIGNED DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(10) UNSIGNED NOT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_auto_responders`
--

CREATE TABLE `mail_auto_responders` (
  `id` int(10) UNSIGNED NOT NULL,
  `mailbox_id` int(10) UNSIGNED NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_billing_history`
--

CREATE TABLE `mail_billing_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subscriber_id` int(10) UNSIGNED NOT NULL,
  `subscription_id` int(10) UNSIGNED DEFAULT NULL,
  `transaction_type` enum('payment','upgrade','downgrade','refund','credit','adjustment') DEFAULT 'payment',
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(3) DEFAULT 'USD',
  `payment_method` varchar(50) DEFAULT NULL COMMENT 'stripe, razorpay, cashfree, etc',
  `payment_status` enum('pending','completed','failed','refunded') DEFAULT 'completed',
  `transaction_id` varchar(255) DEFAULT NULL COMMENT 'External payment gateway transaction ID',
  `invoice_number` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Additional transaction details' CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_contacts`
--

CREATE TABLE `mail_contacts` (
  `id` int(10) UNSIGNED NOT NULL,
  `mailbox_id` int(10) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_favorite` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_dns_records`
--

CREATE TABLE `mail_dns_records` (
  `id` int(10) UNSIGNED NOT NULL,
  `domain_id` int(10) UNSIGNED NOT NULL,
  `record_type` enum('MX','TXT','CNAME','A','AAAA','SPF','DKIM','DMARC') NOT NULL,
  `record_name` varchar(255) NOT NULL,
  `record_value` text NOT NULL,
  `priority` int(10) UNSIGNED DEFAULT NULL COMMENT 'For MX records',
  `ttl` int(10) UNSIGNED DEFAULT 3600,
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `verified_at` timestamp NULL DEFAULT NULL,
  `last_verified_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_domains`
--

CREATE TABLE `mail_domains` (
  `id` int(10) UNSIGNED NOT NULL,
  `subscriber_id` int(10) UNSIGNED NOT NULL COMMENT 'Owner subscriber',
  `domain_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(64) DEFAULT NULL,
  `verification_method` enum('txt','cname','mx') DEFAULT 'txt',
  `is_active` tinyint(1) DEFAULT 1,
  `ssl_enabled` tinyint(1) DEFAULT 0,
  `ssl_certificate` text DEFAULT NULL,
  `ssl_private_key` text DEFAULT NULL,
  `catch_all_enabled` tinyint(1) DEFAULT 0,
  `catch_all_email` varchar(255) DEFAULT NULL,
  `catch_all_mailbox_id` int(10) UNSIGNED DEFAULT NULL,
  `dkim_private_key` text DEFAULT NULL,
  `dkim_public_key` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `verified_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_email_templates`
--

CREATE TABLE `mail_email_templates` (
  `id` int(10) UNSIGNED NOT NULL,
  `mailbox_id` int(10) UNSIGNED NOT NULL,
  `template_name` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `is_html` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_feature_access`
--

CREATE TABLE `mail_feature_access` (
  `id` int(10) UNSIGNED NOT NULL,
  `subscriber_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Subscriber level override',
  `mailbox_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'User level override',
  `feature_key` varchar(50) NOT NULL,
  `is_enabled` tinyint(1) DEFAULT 1,
  `override_by_admin` tinyint(1) DEFAULT 0,
  `override_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_folders`
--

CREATE TABLE `mail_folders` (
  `id` int(10) UNSIGNED NOT NULL,
  `mailbox_id` int(10) UNSIGNED NOT NULL,
  `folder_name` varchar(100) NOT NULL,
  `folder_type` enum('inbox','sent','drafts','trash','spam','archive','custom') NOT NULL,
  `parent_folder_id` int(10) UNSIGNED DEFAULT NULL,
  `message_count` int(10) UNSIGNED DEFAULT 0,
  `unread_count` int(10) UNSIGNED DEFAULT 0,
  `sort_order` int(10) UNSIGNED DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_invoices`
--

CREATE TABLE `mail_invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subscriber_id` int(10) UNSIGNED NOT NULL,
  `subscription_id` int(10) UNSIGNED DEFAULT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'USD',
  `status` enum('draft','sent','paid','overdue','cancelled') DEFAULT 'draft',
  `due_date` date DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `stripe_invoice_id` varchar(255) DEFAULT NULL,
  `invoice_pdf_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_logs`
--

CREATE TABLE `mail_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `mailbox_id` int(10) UNSIGNED DEFAULT NULL,
  `message_id` bigint(20) UNSIGNED DEFAULT NULL,
  `log_type` enum('send','receive','bounce','spam','error') NOT NULL,
  `status` enum('pending','sent','delivered','failed','bounced','rejected') NOT NULL,
  `from_email` varchar(255) NOT NULL,
  `to_email` varchar(255) NOT NULL,
  `subject` varchar(500) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `smtp_response` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_mailboxes`
--

CREATE TABLE `mail_mailboxes` (
  `id` int(10) UNSIGNED NOT NULL,
  `subscriber_id` int(10) UNSIGNED NOT NULL COMMENT 'Owner subscriber',
  `domain_id` int(10) UNSIGNED NOT NULL,
  `mmb_user_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Linked MMB user (optional for invited users)',
  `email` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'Hashed password for SMTP/IMAP auth',
  `display_name` varchar(255) DEFAULT NULL,
  `signature` text DEFAULT NULL COMMENT 'Email signature',
  `role_type` enum('subscriber_owner','domain_admin','end_user') DEFAULT 'end_user',
  `added_by_user_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Subscriber who added this mailbox',
  `storage_quota` bigint(20) UNSIGNED DEFAULT 1073741824 COMMENT 'Bytes, default 1GB',
  `storage_used` bigint(20) UNSIGNED DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `receive_enabled` tinyint(1) DEFAULT 1,
  `send_enabled` tinyint(1) DEFAULT 1,
  `daily_send_limit` int(10) UNSIGNED DEFAULT 300,
  `daily_send_count` int(10) UNSIGNED DEFAULT 0,
  `last_send_reset` date DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_mail_attachments`
--

CREATE TABLE `mail_mail_attachments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `message_id` bigint(20) UNSIGNED NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `size` int(10) UNSIGNED NOT NULL,
  `storage_path` varchar(500) NOT NULL,
  `content_id` varchar(255) DEFAULT NULL COMMENT 'For inline attachments',
  `is_inline` tinyint(1) DEFAULT 0,
  `checksum` varchar(64) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_mail_filters`
--

CREATE TABLE `mail_mail_filters` (
  `id` int(10) UNSIGNED NOT NULL,
  `mailbox_id` int(10) UNSIGNED NOT NULL,
  `filter_name` varchar(100) NOT NULL,
  `filter_type` enum('auto_reply','forward','move','delete','mark','spam') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `priority` int(10) UNSIGNED DEFAULT 0,
  `conditions` text NOT NULL COMMENT 'JSON conditions',
  `actions` text NOT NULL COMMENT 'JSON actions',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_mail_lists`
--

CREATE TABLE `mail_mail_lists` (
  `id` int(10) UNSIGNED NOT NULL,
  `mailbox_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'NULL for global lists',
  `list_type` enum('blacklist','whitelist') NOT NULL,
  `email_pattern` varchar(255) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_mail_messages`
--

CREATE TABLE `mail_mail_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `mailbox_id` int(10) UNSIGNED NOT NULL,
  `folder_id` int(10) UNSIGNED NOT NULL,
  `message_id` varchar(255) NOT NULL COMMENT 'RFC822 Message-ID',
  `in_reply_to` varchar(255) DEFAULT NULL,
  `references` text DEFAULT NULL,
  `from_email` varchar(255) NOT NULL,
  `from_name` varchar(255) DEFAULT NULL,
  `to_emails` text NOT NULL COMMENT 'JSON array',
  `cc_emails` text DEFAULT NULL COMMENT 'JSON array',
  `bcc_emails` text DEFAULT NULL COMMENT 'JSON array',
  `reply_to` varchar(255) DEFAULT NULL,
  `subject` varchar(500) DEFAULT NULL,
  `body_text` longtext DEFAULT NULL,
  `body_html` longtext DEFAULT NULL,
  `headers` longtext DEFAULT NULL COMMENT 'Full email headers',
  `raw_message` longtext DEFAULT NULL COMMENT 'Complete raw email',
  `size` int(10) UNSIGNED DEFAULT 0 COMMENT 'Message size in bytes',
  `has_attachments` tinyint(1) DEFAULT 0,
  `is_read` tinyint(1) DEFAULT 0,
  `is_starred` tinyint(1) DEFAULT 0,
  `is_draft` tinyint(1) DEFAULT 0,
  `is_spam` tinyint(1) DEFAULT 0,
  `spam_score` decimal(5,2) DEFAULT 0.00,
  `priority` enum('high','normal','low') DEFAULT 'normal',
  `received_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_mail_queue`
--

CREATE TABLE `mail_mail_queue` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `mailbox_id` int(10) UNSIGNED NOT NULL,
  `from_email` varchar(255) NOT NULL,
  `to_emails` text NOT NULL COMMENT 'JSON array',
  `cc_emails` text DEFAULT NULL COMMENT 'JSON array',
  `bcc_emails` text DEFAULT NULL COMMENT 'JSON array',
  `subject` varchar(500) DEFAULT NULL,
  `body_text` longtext DEFAULT NULL,
  `body_html` longtext DEFAULT NULL,
  `attachments` text DEFAULT NULL COMMENT 'JSON array of attachment paths',
  `priority` enum('high','normal','low') DEFAULT 'normal',
  `status` enum('pending','processing','sent','failed') DEFAULT 'pending',
  `attempts` int(10) UNSIGNED DEFAULT 0,
  `max_attempts` int(10) UNSIGNED DEFAULT 3,
  `error_message` text DEFAULT NULL,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_mail_sessions`
--

CREATE TABLE `mail_mail_sessions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `mailbox_id` int(10) UNSIGNED NOT NULL,
  `session_token` varchar(64) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `last_activity` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_mail_statistics`
--

CREATE TABLE `mail_mail_statistics` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `domain_id` int(10) UNSIGNED DEFAULT NULL,
  `mailbox_id` int(10) UNSIGNED DEFAULT NULL,
  `stat_date` date NOT NULL,
  `emails_sent` int(10) UNSIGNED DEFAULT 0,
  `emails_received` int(10) UNSIGNED DEFAULT 0,
  `emails_bounced` int(10) UNSIGNED DEFAULT 0,
  `emails_spam` int(10) UNSIGNED DEFAULT 0,
  `storage_used` bigint(20) UNSIGNED DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_messages`
--

CREATE TABLE `mail_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `mailbox_id` int(10) UNSIGNED NOT NULL,
  `folder_id` int(10) UNSIGNED NOT NULL,
  `message_type` enum('received','sent','draft') DEFAULT 'received',
  `message_id` varchar(255) DEFAULT NULL COMMENT 'RFC 822 Message-ID',
  `in_reply_to` varchar(255) DEFAULT NULL,
  `from_email` varchar(255) NOT NULL,
  `from_name` varchar(255) DEFAULT NULL,
  `to_email` text NOT NULL,
  `cc_email` text DEFAULT NULL,
  `bcc_email` text DEFAULT NULL,
  `subject` varchar(500) DEFAULT NULL,
  `body_text` longtext DEFAULT NULL,
  `body_html` longtext DEFAULT NULL,
  `size` int(10) UNSIGNED DEFAULT 0,
  `is_read` tinyint(1) DEFAULT 0,
  `is_starred` tinyint(1) DEFAULT 0,
  `is_flagged` tinyint(1) DEFAULT 0,
  `priority` enum('low','normal','high') DEFAULT 'normal',
  `read_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `received_at` timestamp NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_payments`
--

CREATE TABLE `mail_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subscriber_id` int(10) UNSIGNED NOT NULL,
  `subscription_id` int(10) UNSIGNED DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'USD',
  `status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `stripe_payment_id` varchar(255) DEFAULT NULL,
  `stripe_invoice_id` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_plan_features`
--

CREATE TABLE `mail_plan_features` (
  `id` int(10) UNSIGNED NOT NULL,
  `plan_id` int(10) UNSIGNED NOT NULL,
  `feature_key` varchar(50) NOT NULL,
  `feature_name` varchar(100) NOT NULL,
  `is_enabled` tinyint(1) DEFAULT 0,
  `feature_value` varchar(255) DEFAULT NULL COMMENT 'For numeric/string limits',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mail_plan_features`
--

INSERT INTO `mail_plan_features` (`id`, `plan_id`, `feature_key`, `feature_name`, `is_enabled`, `feature_value`, `created_at`) VALUES
(11, 2, 'webmail', 'Webmail Access', 1, NULL, '2026-01-03 12:13:17'),
(12, 2, 'smtp', 'SMTP Access', 1, NULL, '2026-01-03 12:13:17'),
(13, 2, 'imap', 'IMAP/POP3 Access', 1, NULL, '2026-01-03 12:13:17'),
(14, 2, 'api', 'API Access', 0, NULL, '2026-01-03 12:13:17'),
(15, 2, 'domain', 'Custom Domain', 1, NULL, '2026-01-03 12:13:17'),
(16, 2, 'alias', 'Email Aliases', 1, NULL, '2026-01-03 12:13:17'),
(17, 2, '2fa', 'Two-Factor Authentication', 1, NULL, '2026-01-03 12:13:17'),
(18, 2, 'threads', 'Threaded Conversations', 1, NULL, '2026-01-03 12:13:17'),
(19, 2, 'scheduled_send', 'Scheduled Sending', 1, NULL, '2026-01-03 12:13:17'),
(20, 2, 'read_receipts', 'Read Receipts', 1, NULL, '2026-01-03 12:13:17'),
(21, 3, 'webmail', 'Webmail Access', 1, NULL, '2026-01-03 12:13:17'),
(22, 3, 'smtp', 'SMTP Access', 1, NULL, '2026-01-03 12:13:17'),
(23, 3, 'imap', 'IMAP/POP3 Access', 1, NULL, '2026-01-03 12:13:17'),
(24, 3, 'api', 'API Access', 1, NULL, '2026-01-03 12:13:17'),
(25, 3, 'domain', 'Custom Domain', 1, NULL, '2026-01-03 12:13:17'),
(26, 3, 'alias', 'Email Aliases', 1, NULL, '2026-01-03 12:13:17'),
(27, 3, '2fa', 'Two-Factor Authentication', 1, NULL, '2026-01-03 12:13:17'),
(28, 3, 'threads', 'Threaded Conversations', 1, NULL, '2026-01-03 12:13:17'),
(29, 3, 'scheduled_send', 'Scheduled Sending', 1, NULL, '2026-01-03 12:13:17'),
(30, 3, 'read_receipts', 'Read Receipts', 1, NULL, '2026-01-03 12:13:17'),
(31, 4, 'webmail', 'Webmail Access', 1, NULL, '2026-01-03 12:13:17'),
(32, 4, 'smtp', 'SMTP Access', 1, NULL, '2026-01-03 12:13:17'),
(33, 4, 'imap', 'IMAP/POP3 Access', 1, NULL, '2026-01-03 12:13:17'),
(34, 4, 'api', 'API Access', 1, NULL, '2026-01-03 12:13:17'),
(35, 4, 'domain', 'Custom Domain', 1, NULL, '2026-01-03 12:13:17'),
(36, 4, 'alias', 'Email Aliases', 1, NULL, '2026-01-03 12:13:17'),
(37, 4, '2fa', 'Two-Factor Authentication', 1, NULL, '2026-01-03 12:13:17'),
(38, 4, 'threads', 'Threaded Conversations', 1, NULL, '2026-01-03 12:13:17'),
(39, 4, 'scheduled_send', 'Scheduled Sending', 1, NULL, '2026-01-03 12:13:17'),
(40, 4, 'read_receipts', 'Read Receipts', 1, NULL, '2026-01-03 12:13:17');

-- --------------------------------------------------------

--
-- Table structure for table `mail_queue`
--

CREATE TABLE `mail_queue` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `mailbox_id` int(10) UNSIGNED DEFAULT NULL,
  `from_email` varchar(255) NOT NULL,
  `from_name` varchar(255) DEFAULT NULL,
  `to_email` text NOT NULL,
  `cc_email` text DEFAULT NULL,
  `bcc_email` text DEFAULT NULL,
  `reply_to_email` varchar(255) DEFAULT NULL,
  `reply_to_message_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subject` varchar(500) DEFAULT NULL,
  `body_html` longtext DEFAULT NULL,
  `body_text` longtext DEFAULT NULL,
  `status` enum('pending','processing','sent','failed','cancelled') DEFAULT 'pending',
  `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
  `attempts` int(10) UNSIGNED DEFAULT 0,
  `max_attempts` int(10) UNSIGNED DEFAULT 3,
  `error_message` text DEFAULT NULL,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `failed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_smtp_credentials`
--

CREATE TABLE `mail_smtp_credentials` (
  `id` int(10) UNSIGNED NOT NULL,
  `mailbox_id` int(10) UNSIGNED NOT NULL,
  `credential_name` varchar(100) NOT NULL,
  `smtp_username` varchar(255) NOT NULL,
  `smtp_password` varchar(255) NOT NULL COMMENT 'Hashed password',
  `smtp_host` varchar(255) NOT NULL,
  `smtp_port` int(10) UNSIGNED DEFAULT 587,
  `encryption_type` enum('tls','ssl','none') DEFAULT 'tls',
  `is_active` tinyint(1) DEFAULT 1,
  `rate_limit_per_hour` int(10) UNSIGNED DEFAULT 100,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_subscribers`
--

CREATE TABLE `mail_subscribers` (
  `id` int(10) UNSIGNED NOT NULL,
  `mmb_user_id` int(10) UNSIGNED NOT NULL COMMENT 'Reference to main MMB users table - the buyer',
  `account_name` varchar(255) NOT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `billing_email` varchar(255) DEFAULT NULL,
  `billing_address` text DEFAULT NULL,
  `status` enum('active','suspended','cancelled','grace_period') DEFAULT 'active',
  `suspension_reason` text DEFAULT NULL,
  `stripe_customer_id` varchar(255) DEFAULT NULL,
  `users_count` int(10) UNSIGNED DEFAULT 0 COMMENT 'Current number of users added',
  `can_add_users` tinyint(1) DEFAULT 1 COMMENT 'Can subscriber add more users',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `suspended_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mail_subscribers`
--

INSERT INTO `mail_subscribers` (`id`, `mmb_user_id`, `account_name`, `company_name`, `billing_email`, `billing_address`, `status`, `suspension_reason`, `stripe_customer_id`, `users_count`, `can_add_users`, `created_at`, `updated_at`, `suspended_at`) VALUES
(3, 3, 'test_subscription', NULL, 'admin@mymultibranch.com', NULL, 'active', NULL, NULL, 0, 1, '2026-01-07 06:00:02', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `mail_subscriptions`
--

CREATE TABLE `mail_subscriptions` (
  `id` int(10) UNSIGNED NOT NULL,
  `subscriber_id` int(10) UNSIGNED NOT NULL,
  `plan_id` int(10) UNSIGNED NOT NULL,
  `status` enum('active','cancelled','expired','past_due','trialing') DEFAULT 'active',
  `billing_cycle` enum('monthly','yearly') DEFAULT 'monthly',
  `current_period_start` timestamp NULL DEFAULT NULL,
  `current_period_end` timestamp NULL DEFAULT NULL,
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `stripe_subscription_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mail_subscriptions`
--

INSERT INTO `mail_subscriptions` (`id`, `subscriber_id`, `plan_id`, `status`, `billing_cycle`, `current_period_start`, `current_period_end`, `trial_ends_at`, `cancelled_at`, `stripe_subscription_id`, `created_at`, `updated_at`) VALUES
(3, 3, 3, 'active', 'yearly', '2026-01-07 06:00:02', '2027-01-07 06:00:02', NULL, NULL, NULL, '2026-01-07 06:00:02', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `mail_subscription_plans`
--

CREATE TABLE `mail_subscription_plans` (
  `id` int(10) UNSIGNED NOT NULL,
  `plan_name` varchar(100) NOT NULL,
  `plan_slug` varchar(50) NOT NULL,
  `plan_type` enum('free','paid') DEFAULT 'free',
  `price_monthly` decimal(10,2) DEFAULT 0.00,
  `price_yearly` decimal(10,2) DEFAULT 0.00,
  `currency` varchar(3) DEFAULT 'USD' COMMENT 'Currency code (USD, EUR, GBP, etc.)',
  `max_users` int(10) UNSIGNED DEFAULT 1 COMMENT 'Max mailbox users',
  `storage_per_user_gb` int(10) UNSIGNED DEFAULT 1 COMMENT 'GB per user',
  `daily_send_limit` int(10) UNSIGNED DEFAULT 100,
  `max_attachment_size_mb` int(10) UNSIGNED DEFAULT 10,
  `max_domains` int(10) UNSIGNED DEFAULT 1,
  `max_aliases` int(10) UNSIGNED DEFAULT 5,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(10) UNSIGNED DEFAULT 0,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mail_subscription_plans`
--

INSERT INTO `mail_subscription_plans` (`id`, `plan_name`, `plan_slug`, `plan_type`, `price_monthly`, `price_yearly`, `currency`, `max_users`, `storage_per_user_gb`, `daily_send_limit`, `max_attachment_size_mb`, `max_domains`, `max_aliases`, `is_active`, `sort_order`, `description`, `created_at`, `updated_at`) VALUES
(2, 'Starter', 'starter', 'paid', 99.00, 999.00, 'INR', 5, 5, 500, 25, 3, 25, 1, 2, 'Great for small teams', '2026-01-03 12:13:17', '2026-01-06 13:19:53'),
(3, 'Business', 'business', 'paid', 299.99, 2999.00, 'INR', 25, 25, 2000, 50, 10, 100, 1, 3, 'For growing businesses', '2026-01-03 12:13:17', '2026-01-06 13:21:03'),
(4, 'Developer', 'developer', 'paid', 4999.99, 499.00, 'INR', 100, 50, 10000, 100, 50, 500, 1, 4, 'Full API access for developers', '2026-01-03 12:13:17', '2026-01-06 15:34:37'),
(13, 'Free', 'free', 'free', 0.00, 0.00, 'INR', 1, 1, 100, 10, 1, 5, 1, 1, 'Free plan with basic email features', '2026-01-04 02:38:41', '2026-01-06 02:17:12');

-- --------------------------------------------------------

--
-- Table structure for table `mail_system_settings`
--

CREATE TABLE `mail_system_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('string','integer','boolean','json') DEFAULT 'string',
  `description` text DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0 COMMENT 'Can non-admins see this?',
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_usage_logs`
--

CREATE TABLE `mail_usage_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subscriber_id` int(10) UNSIGNED NOT NULL,
  `log_date` date NOT NULL,
  `emails_sent` int(10) UNSIGNED DEFAULT 0,
  `emails_received` int(10) UNSIGNED DEFAULT 0,
  `api_calls` int(10) UNSIGNED DEFAULT 0,
  `storage_used_bytes` bigint(20) UNSIGNED DEFAULT 0,
  `bandwidth_used_bytes` bigint(20) UNSIGNED DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_user_invitations`
--

CREATE TABLE `mail_user_invitations` (
  `id` int(10) UNSIGNED NOT NULL,
  `subscriber_id` int(10) UNSIGNED NOT NULL,
  `invited_by_user_id` int(10) UNSIGNED NOT NULL COMMENT 'Subscriber who sent invitation',
  `email` varchar(255) NOT NULL,
  `role_type` enum('domain_admin','end_user') DEFAULT 'end_user',
  `invitation_token` varchar(64) NOT NULL,
  `status` enum('pending','accepted','expired','cancelled') DEFAULT 'pending',
  `expires_at` timestamp NOT NULL,
  `accepted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_user_roles`
--

CREATE TABLE `mail_user_roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `mmb_user_id` int(10) UNSIGNED NOT NULL COMMENT 'Reference to main MMB users',
  `subscriber_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'NULL for platform super admin only',
  `role_type` enum('platform_super_admin','subscriber_owner','domain_admin','end_user') NOT NULL,
  `permissions` text DEFAULT NULL COMMENT 'JSON permissions override',
  `is_owner` tinyint(1) DEFAULT 0 COMMENT '1 if subscriber owner who bought the subscription',
  `invited_by_user_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Who invited/added this user',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_webhooks`
--

CREATE TABLE `mail_webhooks` (
  `id` int(10) UNSIGNED NOT NULL,
  `subscriber_id` int(10) UNSIGNED NOT NULL,
  `webhook_url` varchar(500) NOT NULL,
  `webhook_secret` varchar(255) NOT NULL,
  `events` text NOT NULL COMMENT 'JSON array of subscribed events',
  `is_active` tinyint(1) DEFAULT 1,
  `last_triggered_at` timestamp NULL DEFAULT NULL,
  `failure_count` int(10) UNSIGNED DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_webhook_deliveries`
--

CREATE TABLE `mail_webhook_deliveries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `webhook_id` int(10) UNSIGNED NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `payload` text NOT NULL COMMENT 'JSON payload',
  `status_code` int(10) UNSIGNED DEFAULT NULL,
  `response_body` text DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `failed_at` timestamp NULL DEFAULT NULL,
  `failure_reason` text DEFAULT NULL,
  `retry_count` int(10) UNSIGNED DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `navbar_settings`
--

CREATE TABLE `navbar_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `logo_type` enum('text','image') DEFAULT 'text',
  `logo_text` varchar(100) DEFAULT NULL,
  `logo_image_url` varchar(255) DEFAULT NULL,
  `show_home_link` tinyint(1) DEFAULT 1,
  `show_dashboard_link` tinyint(1) DEFAULT 1,
  `show_profile_link` tinyint(1) DEFAULT 1,
  `show_admin_link` tinyint(1) DEFAULT 1,
  `show_projects_dropdown` tinyint(1) DEFAULT 1,
  `show_theme_toggle` tinyint(1) DEFAULT 1,
  `navbar_sticky` tinyint(1) DEFAULT 1,
  `default_theme` enum('dark','light') DEFAULT 'dark',
  `navbar_bg_color` varchar(20) DEFAULT '#06060a',
  `navbar_text_color` varchar(20) DEFAULT '#e8eefc',
  `navbar_border_color` varchar(20) DEFAULT '#1a1a2e',
  `custom_css` text DEFAULT NULL,
  `custom_links` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_links`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `navbar_settings`
--

INSERT INTO `navbar_settings` (`id`, `logo_type`, `logo_text`, `logo_image_url`, `show_home_link`, `show_dashboard_link`, `show_profile_link`, `show_admin_link`, `show_projects_dropdown`, `show_theme_toggle`, `navbar_sticky`, `default_theme`, `navbar_bg_color`, `navbar_text_color`, `navbar_border_color`, `custom_css`, `custom_links`, `created_at`, `updated_at`) VALUES
(1, 'text', 'MyMultiBranch', '', 1, 1, 1, 1, 1, 1, 1, 'dark', '#000000', '#ffffff', '#ffffff', '', '[{\"title\":\"Services\",\"url\":\"#\",\"icon\":\"\",\"position\":0,\"is_dropdown\":true,\"dropdown_items\":[{\"title\":\"test\",\"url\":\"#\",\"icon\":\"\"}]}]', '2025-12-07 22:57:59', '2026-01-03 09:10:10');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_preferences`
--

CREATE TABLE `notification_preferences` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `type` varchar(50) NOT NULL,
  `email_enabled` tinyint(1) DEFAULT 1,
  `sms_enabled` tinyint(1) DEFAULT 0,
  `push_enabled` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_providers`
--

CREATE TABLE `oauth_providers` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `client_id` varchar(255) DEFAULT NULL,
  `client_secret` varchar(255) DEFAULT NULL,
  `redirect_uri` varchar(255) DEFAULT NULL,
  `scopes` text DEFAULT NULL,
  `is_enabled` tinyint(1) DEFAULT 0,
  `config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`config`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oauth_providers`
--

INSERT INTO `oauth_providers` (`id`, `name`, `display_name`, `client_id`, `client_secret`, `redirect_uri`, `scopes`, `is_enabled`, `config`, `created_at`, `updated_at`) VALUES
(1, 'google', 'Google', 'hidden', 'hidden', 'https://test.mymultibranch.com/auth/google/callback', 'openid email profile', 1, '{\"auth_url\": \"https://accounts.google.com/o/oauth2/v2/auth\", \"token_url\": \"https://oauth2.googleapis.com/token\", \"userinfo_url\": \"https://www.googleapis.com/oauth2/v2/userinfo\"}', '2026-01-03 05:16:33', '2026-01-03 05:47:12');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_user_connections`
--

CREATE TABLE `oauth_user_connections` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `provider_id` int(10) UNSIGNED NOT NULL,
  `provider_user_id` varchar(255) NOT NULL,
  `provider_email` varchar(255) DEFAULT NULL,
  `provider_name` varchar(255) DEFAULT NULL,
  `provider_avatar` varchar(500) DEFAULT NULL,
  `access_token` text DEFAULT NULL,
  `refresh_token` text DEFAULT NULL,
  `token_expires_at` timestamp NULL DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oauth_user_connections`
--

INSERT INTO `oauth_user_connections` (`id`, `user_id`, `provider_id`, `provider_user_id`, `provider_email`, `provider_name`, `provider_avatar`, `access_token`, `refresh_token`, `token_expires_at`, `last_used_at`, `created_at`, `updated_at`) VALUES
(6, 7, 1, '108294226219551297545', 'farukahmed8565@gmail.com', 'Faruque Ahmed', 'https://lh3.googleusercontent.com/a/ACg8ocLJbANaLwySQsWOgnluyL8sj8noUH2Per2bboiD83xYJbOVyDQM=s96-c', 'ya29.a0Aa7pCA94S54CPyO8Q8ZIrUALF6wTLdQSne95NpD0wWoKVZ_HIj9JZ5ZvJuI4A5I7LS28quQqnzdw5td33MvVVMTjGVe934P3Pc13kQvdjgK8y1zGxZ_DYRcXvl25LMMCP5zfyPe-aqssRBOpn0gUJ-PAQ2ynpu4UzvY3y7vba6VYzrHJGE2sSzqZCdjcGsx8OhWJXawaCgYKAVUSARESFQHGX2Mi4opb3ZsbHfZEK8GVw6ofXA0206', '1//0gmJyFetAPbSTCgYIARAAGBASNwF-L9IrtZrXCtASsltVMYSgUtGmStTfWxZklEXTKwN6arygvXd3DVaAFZht-mHyuzxV7nSqR1s', '2026-01-06 14:15:47', '2026-01-06 13:15:48', '2026-01-03 09:50:38', '2026-01-06 13:15:48');

-- --------------------------------------------------------

--
-- Table structure for table `ocr_batch_files`
--

CREATE TABLE `ocr_batch_files` (
  `id` int(10) UNSIGNED NOT NULL,
  `job_id` int(10) UNSIGNED NOT NULL,
  `file_path` varchar(512) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `status` enum('pending','processing','completed','failed') DEFAULT 'pending',
  `result_text` longtext DEFAULT NULL,
  `confidence` decimal(5,2) DEFAULT NULL COMMENT 'OCR confidence 0-100',
  `has_tables` tinyint(1) DEFAULT 0,
  `tables_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tables_data`)),
  `processing_time` int(10) UNSIGNED DEFAULT NULL COMMENT 'Seconds',
  `error_message` text DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ocr_batch_jobs`
--

CREATE TABLE `ocr_batch_jobs` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `total_files` int(11) NOT NULL,
  `processed_files` int(11) DEFAULT 0,
  `successful_files` int(11) DEFAULT 0,
  `failed_files` int(11) DEFAULT 0,
  `status` enum('pending','processing','completed','completed_with_errors','failed') DEFAULT 'pending',
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'OCR options: language, preprocess, etc' CHECK (json_valid(`options`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ocr_history`
--

CREATE TABLE `ocr_history` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `file_path` varchar(512) NOT NULL,
  `file_type` varchar(50) NOT NULL COMMENT 'image/jpeg, application/pdf, etc',
  `language` varchar(10) NOT NULL DEFAULT 'eng',
  `confidence` decimal(5,2) DEFAULT NULL,
  `has_tables` tinyint(1) DEFAULT 0,
  `page_count` int(11) DEFAULT 1,
  `preprocessing_applied` tinyint(1) DEFAULT 0,
  `processing_time` int(10) UNSIGNED DEFAULT NULL COMMENT 'Seconds',
  `result_size` int(10) UNSIGNED DEFAULT NULL COMMENT 'Bytes',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_permissions`
--

CREATE TABLE `project_permissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `project_name` varchar(50) NOT NULL,
  `has_access` tinyint(1) DEFAULT 1,
  `role` varchar(50) DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `type` varchar(20) DEFAULT 'string',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `type`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'MyMultiBranch', 'string', '2025-12-02 21:51:02', '2026-01-03 06:01:55'),
(2, 'site_description', 'Multi-Project Platform', 'string', '2025-12-02 21:51:02', '2026-01-03 06:01:55'),
(3, 'maintenance_mode', '', 'boolean', '2025-12-02 21:51:02', '2026-01-03 06:01:55'),
(4, 'registration_enabled', '1', 'boolean', '2025-12-02 21:51:02', '2026-01-03 06:01:55'),
(5, 'contact_email', 'Support@mymultibranch.com', 'string', '2025-12-02 21:51:02', '2026-01-03 06:01:55'),
(16, 'maintenance_title', 'We\'ll Be Back Soon!', 'string', '2026-01-01 09:30:06', '2026-01-02 11:39:59'),
(17, 'maintenance_message', 'We\'re currently performing scheduled maintenance to improve your experience. Please check back in a few minutes.', 'string', '2026-01-01 09:30:06', '2026-01-02 11:39:59'),
(18, 'maintenance_show_countdown', '1', 'string', '2026-01-01 09:30:06', '2026-01-02 11:40:00'),
(19, 'maintenance_end_time', '2026-01-31T15:01', 'string', '2026-01-01 09:30:06', '2026-01-02 11:40:00'),
(20, 'maintenance_custom_html', '', 'string', '2026-01-01 09:30:06', '2026-01-02 11:40:00'),
(57, 'maintenance_contact_email', 'support@mymultibranch.com', 'string', '2026-01-01 09:55:28', '2026-01-02 11:40:00'),
(64, 'system_timezone', 'UTC', 'string', '2026-01-03 07:39:13', NULL),
(65, 'date_format', 'M d, Y', 'string', '2026-01-03 07:39:13', NULL),
(66, 'time_format', 'g:i A', 'string', '2026-01-03 07:39:13', NULL),
(67, 'default_session_timeout', '120', 'integer', '2026-01-03 07:39:13', '2026-01-03 07:39:43'),
(68, 'remember_me_duration', '30', 'integer', '2026-01-03 07:39:13', '2026-01-03 07:39:43'),
(69, 'max_concurrent_sessions', '5', 'integer', '2026-01-03 07:39:13', '2026-01-03 07:39:43'),
(70, 'auto_logout_enabled', '1', 'boolean', '2026-01-03 07:39:13', '2026-01-03 07:39:43'),
(71, 'session_ip_validation', '0', 'boolean', '2026-01-03 07:39:13', '2026-01-03 07:39:44'),
(72, 'max_failed_login_attempts', '5', 'integer', '2026-01-03 07:39:13', NULL),
(73, 'account_lockout_duration', '15', 'integer', '2026-01-03 07:39:13', NULL),
(74, 'password_min_length', '8', 'integer', '2026-01-03 07:39:13', NULL),
(75, 'require_email_verification', '0', 'boolean', '2026-01-03 07:39:13', NULL),
(76, 'force_password_change', '0', 'boolean', '2026-01-03 07:39:13', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `setting_type` varchar(20) DEFAULT 'string' COMMENT 'string, json, boolean, integer',
  `description` text DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `updated_at`) VALUES
(1, 'websocket_enabled', 'true', 'boolean', 'Enable WebSocket real-time features', '2025-12-03 20:23:53'),
(2, 'websocket_host', '0.0.0.0', 'string', 'WebSocket server host', '2025-12-03 20:23:53'),
(3, 'websocket_port', '8080', 'integer', 'WebSocket server port', '2025-12-03 20:23:53'),
(4, 'websocket_url', 'ws://localhost:8080', 'string', 'WebSocket client connection URL', '2025-12-03 20:23:53'),
(5, 'cache_enabled', 'true', 'boolean', 'Enable file-based caching', '2025-12-03 20:23:53'),
(6, 'cache_default_ttl', '3600', 'integer', 'Default cache TTL in seconds', '2025-12-03 20:23:53'),
(7, 'cache_driver', 'file', 'string', 'Cache driver: file, redis, memcached', '2025-12-03 20:23:53'),
(8, 'email_queue_enabled', 'true', 'boolean', 'Enable email queue', '2025-12-03 20:23:53'),
(9, 'email_queue_batch_size', '50', 'integer', 'Number of emails to process per batch', '2025-12-03 20:23:53'),
(10, 'email_queue_retry_attempts', '3', 'integer', 'Max retry attempts for failed emails', '2025-12-03 20:23:53'),
(11, 'api_enabled', 'true', 'boolean', 'Enable REST API', '2025-12-03 20:23:53'),
(12, 'api_rate_limit_minute', '60', 'integer', 'API requests per minute', '2025-12-03 20:23:53'),
(13, 'api_rate_limit_hour', '1000', 'integer', 'API requests per hour', '2025-12-03 20:23:53'),
(14, 'api_rate_limit_day', '10000', 'integer', 'API requests per day', '2025-12-03 20:23:53'),
(15, 'analytics_enabled', 'true', 'boolean', 'Enable analytics tracking', '2025-12-03 20:23:53'),
(16, 'analytics_retention_days', '90', 'integer', 'Days to retain analytics data', '2025-12-03 20:23:53');

-- --------------------------------------------------------

--
-- Table structure for table `template_files`
--

CREATE TABLE `template_files` (
  `id` int(10) UNSIGNED NOT NULL,
  `template_id` int(10) UNSIGNED NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_content` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','project_admin','user') DEFAULT 'user',
  `status` enum('active','inactive','banned') DEFAULT 'active',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `email_verification_token` varchar(64) DEFAULT NULL,
  `two_factor_secret` varchar(255) DEFAULT NULL,
  `two_factor_enabled` tinyint(1) DEFAULT 0,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `google_id` varchar(255) DEFAULT NULL,
  `session_timeout_minutes` int(11) DEFAULT 120,
  `two_factor_backup_codes` text DEFAULT NULL COMMENT 'JSON array of hashed backup codes',
  `two_factor_enabled_at` timestamp NULL DEFAULT NULL COMMENT 'When 2FA was enabled',
  `oauth_only` tinyint(1) DEFAULT 0 COMMENT 'User only has OAuth login (no manual password set)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `status`, `email_verified_at`, `email_verification_token`, `two_factor_secret`, `two_factor_enabled`, `last_login_at`, `last_login_ip`, `created_at`, `updated_at`, `google_id`, `session_timeout_minutes`, `two_factor_backup_codes`, `two_factor_enabled_at`, `oauth_only`) VALUES
(1, 'SuperAdmin', 'admin2@mymultibranch.com', '$argon2id$v=19$m=65536,t=4,p=1$kMvFi6UfX8DW3+Qyo+/Nvg$2eJBr/iA6mlrdwAv7X+BKsdgMbSsdvSGozbgdt0banA', 'project_admin', 'active', NULL, NULL, NULL, 0, '2025-12-08 00:29:32', '106.215.137.219', '2025-12-02 21:51:28', '2025-12-08 00:29:32', NULL, 120, NULL, NULL, 0),
(2, 'testuser', 'testuser@testuser.testuser', '$argon2id$v=19$m=65536,t=4,p=1$AGfNUL8XhFIKe75Po0Z51g$9t0ss9S9mMWPzU1G83mu0lFx6WKHInyR9jJXId/5EEc', 'user', 'active', NULL, NULL, NULL, 0, '2025-12-04 09:33:06', '106.215.140.20', '2025-12-03 14:22:36', '2025-12-04 09:33:06', NULL, 120, NULL, NULL, 0),
(3, 'SuperAdmin', 'admin@mymultibranch.com', '$argon2id$v=19$m=65536,t=4,p=1$WxcvK83+9JE9bUQe0zUuGw$VzsUODlLUe35S7bmz0+idOoTEUKJ6h6Bq081rLdqFCU', 'super_admin', 'active', NULL, NULL, NULL, 0, '2026-01-07 05:45:03', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', '2025-12-05 21:41:01', '2026-01-07 05:45:03', NULL, 120, NULL, NULL, 0),
(4, 'Testuser2', 'testuser@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$fEWvh6jvcdPvrPByAsFDJQ$OpJgxgTbhIfIouIVhN/mLwQEGgyH8/4s/PePDnRshiQ', 'user', 'active', NULL, NULL, NULL, 0, '2026-01-02 15:07:41', '2401:4900:8fc2:d46d:fdaf:5439:7931:256b', '2025-12-08 13:02:42', '2026-01-02 15:07:41', NULL, 120, NULL, NULL, 0),
(7, 'Faruque Ahmed', 'farukahmed8565@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$CcMvn795U2RdN/sQaAaFEw$52zr90/mSg4lwherBhXVlEON47PP+RofZFhzOh3R7/w', 'user', 'active', '2026-01-03 09:50:38', NULL, NULL, 0, '2026-01-06 13:12:02', '2401:4900:8fc2:f585:d4fa:18af:a145:5362', '2026-01-03 09:50:38', '2026-01-06 13:16:14', '108294226219551297545', 120, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_devices`
--

CREATE TABLE `user_devices` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `device_name` varchar(100) DEFAULT NULL,
  `device_type` varchar(50) DEFAULT NULL,
  `browser` varchar(100) DEFAULT NULL,
  `platform` varchar(50) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `last_active_at` timestamp NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `timezone` varchar(50) DEFAULT 'UTC',
  `language` varchar(10) DEFAULT 'en',
  `theme_preference` varchar(20) DEFAULT 'dark',
  `email_notifications` tinyint(1) DEFAULT 1,
  `security_alerts` tinyint(1) DEFAULT 1,
  `product_updates` tinyint(1) DEFAULT 0,
  `display_settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`display_settings`)),
  `project_settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`project_settings`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`id`, `user_id`, `avatar`, `bio`, `phone`, `timezone`, `language`, `theme_preference`, `email_notifications`, `security_alerts`, `product_updates`, `display_settings`, `project_settings`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, '', '8811881962', 'UTC', 'en', 'dark', 1, 1, 0, NULL, NULL, '2025-12-02 21:51:28', '2025-12-02 23:03:55'),
(2, 2, NULL, NULL, NULL, 'UTC', 'en', 'dark', 1, 1, 0, NULL, NULL, '2025-12-03 14:22:36', NULL),
(3, 3, '69587c52b1ed3_Screenshot2026-01-02132258.png', '', '', 'UTC', 'en', 'dark', 1, 1, 0, NULL, NULL, '2025-12-05 21:41:01', '2026-01-03 02:17:54'),
(4, 4, NULL, NULL, NULL, 'UTC', 'en', 'dark', 1, 1, 0, NULL, NULL, '2025-12-08 13:02:42', NULL),
(7, 7, 'https://lh3.googleusercontent.com/a/ACg8ocLJbANaLwySQsWOgnluyL8sj8noUH2Per2bboiD83xYJbOVyDQM=s96-c', NULL, NULL, 'UTC', 'en', 'dark', 1, 1, 0, NULL, '{\"default_view\":\"grid\",\"auto_save\":0}', '2026-01-03 09:50:38', '2026-01-03 10:16:55');

-- --------------------------------------------------------

--
-- Table structure for table `user_remember_tokens`
--

CREATE TABLE `user_remember_tokens` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `token` varchar(64) NOT NULL,
  `device_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`device_info`)),
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_remember_tokens`
--

INSERT INTO `user_remember_tokens` (`id`, `user_id`, `token`, `device_info`, `expires_at`, `created_at`) VALUES
(1, 1, 'f0393d6ab8a6301116ea6a77a9d3d7dc1de447b08fd4ec819854a8e4fefb1290', '{\"browser\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36\",\"ip\":\"106.215.140.20\"}', '2026-01-02 13:17:17', '2025-12-03 13:17:17');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `session_id` varchar(128) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `device_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`device_info`)),
  `last_activity_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expires_at` timestamp NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `session_id`, `ip_address`, `user_agent`, `device_info`, `last_activity_at`, `expires_at`, `is_active`, `created_at`) VALUES
(1, 3, 'dfc0ed8al02q6vp2jscb99bp5e', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"browser\":\"Chrome\",\"platform\":\"Windows\",\"device\":\"Desktop\"}', '2026-01-03 05:47:35', '2026-01-03 07:47:28', 0, '2026-01-03 05:18:52'),
(2, 3, 'sdffei38itbpi26ccor5j5fd95', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"browser\":\"Chrome\",\"platform\":\"Windows\",\"device\":\"Desktop\"}', '2026-01-03 06:24:22', '2026-01-03 07:57:35', 0, '2026-01-03 05:49:20'),
(5, 3, 'lpauuqimb5nvoe6hlkkob85s35', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"browser\":\"Chrome\",\"platform\":\"Windows\",\"device\":\"Desktop\"}', '2026-01-03 06:25:11', '2026-01-03 08:25:11', 1, '2026-01-03 05:59:02'),
(6, 3, '0ednivnqrb6vco8cq8va2e7o6g', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"browser\":\"Chrome\",\"platform\":\"Windows\",\"device\":\"Desktop\"}', '2026-01-03 09:07:38', '2026-01-03 10:55:24', 0, '2026-01-03 08:39:39'),
(12, 7, 'vo1dqeqij49tmogq63i63vva1l', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"browser\":\"Chrome\",\"platform\":\"Windows\",\"device\":\"Desktop\"}', '2026-01-03 09:55:29', '2026-01-03 11:55:21', 0, '2026-01-03 09:50:38'),
(13, 7, 'm72q6gmp6qf2iamovlrp1ihnss', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"browser\":\"Chrome\",\"platform\":\"Windows\",\"device\":\"Desktop\"}', '2026-01-03 10:11:21', '2026-01-03 12:11:10', 0, '2026-01-03 10:01:51'),
(14, 7, '34rohqc1ug6aaksvh0kh7v9f16', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"browser\":\"Chrome\",\"platform\":\"Windows\",\"device\":\"Desktop\"}', '2026-01-03 10:17:31', '2026-01-03 12:17:31', 1, '2026-01-03 10:11:53'),
(15, 7, 'tsg9h7cp6f2deptpk4sbsoim7s', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"browser\":\"Chrome\",\"platform\":\"Windows\",\"device\":\"Desktop\"}', '2026-01-03 11:29:13', '2026-01-03 13:29:13', 1, '2026-01-03 11:27:01'),
(16, 3, '3ea2psj5d44tvmp8uji6gnsit0', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"browser\":\"Chrome\",\"platform\":\"Windows\",\"device\":\"Desktop\"}', '2026-01-03 12:14:55', '2026-01-03 14:14:55', 1, '2026-01-03 12:10:11'),
(17, 7, '3lhntstq82ruo1cmfejr9cud40', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"browser\":\"Chrome\",\"platform\":\"Windows\",\"device\":\"Desktop\"}', '2026-01-03 12:15:17', '2026-01-03 14:15:03', 0, '2026-01-03 12:15:03'),
(18, 3, 'f0dhnma3mfslmi26000gtk0l6m', '2401:4900:8fc2:d46d:4cfe:cfa2:85be:a0bf', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"browser\":\"Chrome\",\"platform\":\"Windows\",\"device\":\"Desktop\"}', '2026-01-03 12:19:42', '2026-01-03 14:19:42', 1, '2026-01-03 12:16:50'),
(19, 7, '94v9vm2hg9gblj8ke08jeep1bc', '2401:4900:8fc1:920a:29ea:20a:136a:cfe5', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', '{\"browser\":\"Safari\",\"platform\":\"MacOS\",\"device\":\"Mobile\"}', '2026-01-04 01:36:24', '2026-01-04 03:36:19', 0, '2026-01-04 01:36:19'),
(20, 3, 'q01b1c4ccebuclvojup8dnbvsa', '106.215.138.141', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/143.0.7499.151 Mobile/15E148 Safari/604.1', '{\"browser\":\"Safari\",\"platform\":\"MacOS\",\"device\":\"Mobile\"}', '2026-01-04 01:37:52', '2026-01-04 03:37:52', 1, '2026-01-04 01:37:00'),
(21, 3, '632f7puao1lckjlnot9576htcf', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"browser\":\"Chrome\",\"platform\":\"Windows\",\"device\":\"Desktop\"}', '2026-01-04 02:08:39', '2026-01-04 04:08:39', 1, '2026-01-04 01:44:03'),
(22, 3, '24pe20cis43v8vf6h29h0kq5ot', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"browser\":\"Chrome\",\"platform\":\"Windows\",\"device\":\"Desktop\"}', '2026-01-04 02:26:24', '2026-01-04 04:26:14', 0, '2026-01-04 02:15:08'),
(23, 3, 'cshveuk2poqvihfs5voim26dm9', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"browser\":\"Chrome\",\"platform\":\"Windows\",\"device\":\"Desktop\"}', '2026-01-04 02:27:21', '2026-01-04 04:27:21', 1, '2026-01-04 02:27:07'),
(24, 3, 'hd7hmtlpvq6483mece2bcidm2d', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"browser\":\"Chrome\",\"platform\":\"Windows\",\"device\":\"Desktop\"}', '2026-01-04 03:33:34', '2026-01-04 05:33:34', 1, '2026-01-04 03:27:31'),
(25, 3, 'ffvebl3naqgn56kqprl1cr7onf', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"browser\":\"Chrome\",\"platform\":\"Windows\",\"device\":\"Desktop\"}', '2026-01-04 06:12:58', '2026-01-04 08:12:58', 1, '2026-01-04 06:11:38'),
(26, 3, '92nsgb1r1gldbb5d8tbu56rl1e', '2401:4900:8fc1:920a:2546:f9d5:bc0f:16a7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"browser\":\"Chrome\",\"platform\":\"Windows\",\"device\":\"Desktop\"}', '2026-01-04 08:15:16', '2026-01-04 10:15:16', 1, '2026-01-04 07:59:41'),
(27, 3, 'qi6fbmt3tpllqncidhmcnhba7e', '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"browser\":\"Chrome\",\"platform\":\"Windows\",\"device\":\"Desktop\"}', '2026-01-06 02:19:31', '2026-01-06 04:19:31', 1, '2026-01-06 01:55:59'),
(28, 7, '0nam771e4f34c6rei1jbe2a6qg', '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"browser\":\"Chrome\",\"platform\":\"Windows\",\"device\":\"Desktop\"}', '2026-01-06 13:12:13', '2026-01-06 15:12:09', 0, '2026-01-06 13:12:00'),
(29, 3, 'j2a08cnjk1d3runj9pns2t9re9', '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"browser\":\"Chrome\",\"platform\":\"Windows\",\"device\":\"Desktop\"}', '2026-01-06 13:15:10', '2026-01-06 15:14:53', 0, '2026-01-06 13:13:16'),
(30, 3, 'u0c1eihj1v5fiumo3auahif64l', '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"browser\":\"Chrome\",\"platform\":\"Windows\",\"device\":\"Desktop\"}', '2026-01-06 13:42:02', '2026-01-06 15:42:02', 1, '2026-01-06 13:16:02'),
(31, 3, 'ekhqbau6pc7jcqvvstfhdrt3uj', '2401:4900:8fc2:f585:d4fa:18af:a145:5362', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"browser\":\"Chrome\",\"platform\":\"Windows\",\"device\":\"Desktop\"}', '2026-01-06 15:40:13', '2026-01-06 17:40:13', 1, '2026-01-06 15:10:14'),
(32, 3, '6tq5ms2c5f6rh3s8ov9qh37qdv', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"browser\":\"Chrome\",\"platform\":\"Windows\",\"device\":\"Desktop\"}', '2026-01-07 01:44:14', '2026-01-07 03:44:14', 1, '2026-01-07 01:33:43'),
(33, 3, 'lv22fbpapvqse936j9lvso6153', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"browser\":\"Chrome\",\"platform\":\"Windows\",\"device\":\"Desktop\"}', '2026-01-07 03:01:45', '2026-01-07 05:01:45', 1, '2026-01-07 02:58:24'),
(34, 3, 'os27j3luhj68afaor9t6c1tik0', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"browser\":\"Chrome\",\"platform\":\"Windows\",\"device\":\"Desktop\"}', '2026-01-07 04:07:10', '2026-01-07 06:07:02', 0, '2026-01-07 03:37:34'),
(35, 3, '6gfpk8r5i5rh78f1c8147hdiir', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"browser\":\"Chrome\",\"platform\":\"Windows\",\"device\":\"Desktop\"}', '2026-01-07 04:19:57', '2026-01-07 06:19:57', 1, '2026-01-07 04:07:30'),
(36, 3, 'h59s6uqlmm6amhulgrruu3mule', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"browser\":\"Chrome\",\"platform\":\"Windows\",\"device\":\"Desktop\"}', '2026-01-07 05:44:39', '2026-01-07 07:44:34', 0, '2026-01-07 05:22:29'),
(37, 3, '1l6dr42amd6f8tu680qu2q0ggu', '2401:4900:8fc2:f585:6478:d7a3:a1bb:9cc8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"browser\":\"Chrome\",\"platform\":\"Windows\",\"device\":\"Desktop\"}', '2026-01-07 06:09:21', '2026-01-07 08:09:21', 1, '2026-01-07 05:45:03');

-- --------------------------------------------------------

--
-- Table structure for table `user_templates`
--

CREATE TABLE `user_templates` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) NOT NULL DEFAULT 'custom',
  `project_type` varchar(50) NOT NULL DEFAULT 'codexpro',
  `is_public` tinyint(1) DEFAULT 0,
  `downloads` int(10) UNSIGNED DEFAULT 0,
  `rating` decimal(3,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `analytics_events`
--
ALTER TABLE `analytics_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_project_resource` (`project`,`resource_type`,`resource_id`),
  ADD KEY `idx_event_type` (`event_type`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_project_created` (`project`,`created_at`);

--
-- Indexes for table `api_keys`
--
ALTER TABLE `api_keys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `api_key` (`api_key`),
  ADD KEY `idx_api_key` (`api_key`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `api_request_logs`
--
ALTER TABLE `api_request_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_api_key_id` (`api_key_id`),
  ADD KEY `idx_endpoint` (`endpoint`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_status_created` (`status_code`,`created_at`);

--
-- Indexes for table `blocked_ips`
--
ALTER TABLE `blocked_ips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `blocked_by` (`blocked_by`),
  ADD KEY `idx_ip` (`ip_address`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `custom_short_links`
--
ALTER TABLE `custom_short_links`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `custom_slug` (`custom_slug`),
  ADD KEY `idx_custom_slug` (`custom_slug`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_resource` (`project`,`resource_type`,`resource_id`);

--
-- Indexes for table `email_queue`
--
ALTER TABLE `email_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_scheduled` (`scheduled_at`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `failed_logins`
--
ALTER TABLE `failed_logins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ip` (`ip_address`),
  ADD KEY `idx_attempted_at` (`attempted_at`);

--
-- Indexes for table `feature_flags`
--
ALTER TABLE `feature_flags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `feature_name` (`feature_name`),
  ADD KEY `idx_feature` (`feature_name`),
  ADD KEY `idx_enabled` (`is_enabled`);

--
-- Indexes for table `home_content`
--
ALTER TABLE `home_content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `section` (`section`),
  ADD KEY `idx_section` (`section`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `home_projects`
--
ALTER TABLE `home_projects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `project_key` (`project_key`),
  ADD KEY `idx_key` (`project_key`),
  ADD KEY `idx_enabled` (`is_enabled`);

--
-- Indexes for table `home_sections`
--
ALTER TABLE `home_sections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `section_key` (`section_key`);

--
-- Indexes for table `home_stats`
--
ALTER TABLE `home_stats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_sort` (`sort_order`);

--
-- Indexes for table `home_timeline`
--
ALTER TABLE `home_timeline`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_sort` (`sort_order`);

--
-- Indexes for table `login_history`
--
ALTER TABLE `login_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `mail_abuse_reports`
--
ALTER TABLE `mail_abuse_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reported_mailbox_id` (`reported_mailbox_id`),
  ADD KEY `idx_reported_domain_id` (`reported_domain_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `mail_admin_actions`
--
ALTER TABLE `mail_admin_actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_user_id` (`admin_user_id`),
  ADD KEY `idx_action_type` (`action_type`),
  ADD KEY `idx_target_type` (`target_type`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `mail_aliases`
--
ALTER TABLE `mail_aliases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_domain_id` (`domain_id`),
  ADD KEY `idx_source_email` (`source_email`),
  ADD KEY `idx_subscriber_id` (`subscriber_id`),
  ADD KEY `idx_alias_email` (`alias_email`);

--
-- Indexes for table `mail_api_keys`
--
ALTER TABLE `mail_api_keys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `api_key` (`api_key`),
  ADD KEY `idx_subscriber_id` (`subscriber_id`),
  ADD KEY `idx_mailbox_id` (`mailbox_id`),
  ADD KEY `idx_api_key` (`api_key`);

--
-- Indexes for table `mail_api_usage_logs`
--
ALTER TABLE `mail_api_usage_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_api_key_id` (`api_key_id`),
  ADD KEY `idx_endpoint` (`endpoint`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `mail_attachments`
--
ALTER TABLE `mail_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_message_id` (`message_id`),
  ADD KEY `idx_queue_id` (`queue_id`);

--
-- Indexes for table `mail_auto_responders`
--
ALTER TABLE `mail_auto_responders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mailbox_id` (`mailbox_id`);

--
-- Indexes for table `mail_billing_history`
--
ALTER TABLE `mail_billing_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_subscriber_id` (`subscriber_id`),
  ADD KEY `idx_subscription_id` (`subscription_id`),
  ADD KEY `idx_transaction_type` (`transaction_type`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `mail_contacts`
--
ALTER TABLE `mail_contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mailbox_id` (`mailbox_id`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `mail_dns_records`
--
ALTER TABLE `mail_dns_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_domain_id` (`domain_id`),
  ADD KEY `idx_record_type` (`record_type`);

--
-- Indexes for table `mail_domains`
--
ALTER TABLE `mail_domains`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `domain_name` (`domain_name`),
  ADD KEY `idx_subscriber_id` (`subscriber_id`),
  ADD KEY `idx_domain_name` (`domain_name`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_is_verified` (`is_verified`);

--
-- Indexes for table `mail_email_templates`
--
ALTER TABLE `mail_email_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mailbox_id` (`mailbox_id`);

--
-- Indexes for table `mail_feature_access`
--
ALTER TABLE `mail_feature_access`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_subscriber_id` (`subscriber_id`),
  ADD KEY `idx_mailbox_id` (`mailbox_id`),
  ADD KEY `idx_feature_key` (`feature_key`);

--
-- Indexes for table `mail_folders`
--
ALTER TABLE `mail_folders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_mailbox_folder` (`mailbox_id`,`folder_name`),
  ADD KEY `parent_folder_id` (`parent_folder_id`),
  ADD KEY `idx_mailbox_id` (`mailbox_id`),
  ADD KEY `idx_folder_type` (`folder_type`);

--
-- Indexes for table `mail_invoices`
--
ALTER TABLE `mail_invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `subscription_id` (`subscription_id`),
  ADD KEY `idx_subscriber_id` (`subscriber_id`),
  ADD KEY `idx_invoice_number` (`invoice_number`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `mail_logs`
--
ALTER TABLE `mail_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mailbox_id` (`mailbox_id`),
  ADD KEY `idx_message_id` (`message_id`),
  ADD KEY `idx_log_type` (`log_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `mail_mailboxes`
--
ALTER TABLE `mail_mailboxes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_subscriber_id` (`subscriber_id`),
  ADD KEY `idx_domain_id` (`domain_id`),
  ADD KEY `idx_mmb_user_id` (`mmb_user_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_role_type` (`role_type`);

--
-- Indexes for table `mail_mail_attachments`
--
ALTER TABLE `mail_mail_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_message_id` (`message_id`);

--
-- Indexes for table `mail_mail_filters`
--
ALTER TABLE `mail_mail_filters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mailbox_id` (`mailbox_id`),
  ADD KEY `idx_filter_type` (`filter_type`);

--
-- Indexes for table `mail_mail_lists`
--
ALTER TABLE `mail_mail_lists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mailbox_id` (`mailbox_id`),
  ADD KEY `idx_list_type` (`list_type`);

--
-- Indexes for table `mail_mail_messages`
--
ALTER TABLE `mail_mail_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mailbox_id` (`mailbox_id`),
  ADD KEY `idx_folder_id` (`folder_id`),
  ADD KEY `idx_message_id` (`message_id`),
  ADD KEY `idx_from_email` (`from_email`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_is_spam` (`is_spam`),
  ADD KEY `idx_received_at` (`received_at`);
ALTER TABLE `mail_mail_messages` ADD FULLTEXT KEY `idx_search` (`subject`,`body_text`);

--
-- Indexes for table `mail_mail_queue`
--
ALTER TABLE `mail_mail_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mailbox_id` (`mailbox_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_scheduled_at` (`scheduled_at`);

--
-- Indexes for table `mail_mail_sessions`
--
ALTER TABLE `mail_mail_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_token` (`session_token`),
  ADD KEY `idx_mailbox_id` (`mailbox_id`),
  ADD KEY `idx_session_token` (`session_token`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `mail_mail_statistics`
--
ALTER TABLE `mail_mail_statistics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_domain_date` (`domain_id`,`stat_date`),
  ADD UNIQUE KEY `unique_mailbox_date` (`mailbox_id`,`stat_date`),
  ADD KEY `idx_domain_id` (`domain_id`),
  ADD KEY `idx_mailbox_id` (`mailbox_id`),
  ADD KEY `idx_stat_date` (`stat_date`);

--
-- Indexes for table `mail_messages`
--
ALTER TABLE `mail_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mailbox_id` (`mailbox_id`),
  ADD KEY `idx_folder_id` (`folder_id`),
  ADD KEY `idx_message_id` (`message_id`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_received_at` (`received_at`);
ALTER TABLE `mail_messages` ADD FULLTEXT KEY `ft_search` (`subject`,`body_text`);

--
-- Indexes for table `mail_payments`
--
ALTER TABLE `mail_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_subscriber_id` (`subscriber_id`),
  ADD KEY `idx_subscription_id` (`subscription_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_stripe_payment_id` (`stripe_payment_id`);

--
-- Indexes for table `mail_plan_features`
--
ALTER TABLE `mail_plan_features`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_plan_feature` (`plan_id`,`feature_key`),
  ADD KEY `idx_plan_id` (`plan_id`),
  ADD KEY `idx_feature_key` (`feature_key`);

--
-- Indexes for table `mail_queue`
--
ALTER TABLE `mail_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mailbox_id` (`mailbox_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_scheduled_at` (`scheduled_at`);

--
-- Indexes for table `mail_smtp_credentials`
--
ALTER TABLE `mail_smtp_credentials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `smtp_username` (`smtp_username`),
  ADD KEY `idx_mailbox_id` (`mailbox_id`),
  ADD KEY `idx_smtp_username` (`smtp_username`);

--
-- Indexes for table `mail_subscribers`
--
ALTER TABLE `mail_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mmb_user_id` (`mmb_user_id`),
  ADD KEY `idx_mmb_user_id` (`mmb_user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_stripe_customer_id` (`stripe_customer_id`);

--
-- Indexes for table `mail_subscriptions`
--
ALTER TABLE `mail_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_subscriber_id` (`subscriber_id`),
  ADD KEY `idx_plan_id` (`plan_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_stripe_subscription_id` (`stripe_subscription_id`);

--
-- Indexes for table `mail_subscription_plans`
--
ALTER TABLE `mail_subscription_plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plan_name` (`plan_name`),
  ADD UNIQUE KEY `plan_slug` (`plan_slug`),
  ADD KEY `idx_plan_slug` (`plan_slug`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `mail_system_settings`
--
ALTER TABLE `mail_system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_setting_key` (`setting_key`);

--
-- Indexes for table `mail_usage_logs`
--
ALTER TABLE `mail_usage_logs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_subscriber_date` (`subscriber_id`,`log_date`),
  ADD KEY `idx_subscriber_id` (`subscriber_id`),
  ADD KEY `idx_log_date` (`log_date`);

--
-- Indexes for table `mail_user_invitations`
--
ALTER TABLE `mail_user_invitations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invitation_token` (`invitation_token`),
  ADD KEY `idx_subscriber_id` (`subscriber_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_invitation_token` (`invitation_token`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `mail_user_roles`
--
ALTER TABLE `mail_user_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_subscriber` (`mmb_user_id`,`subscriber_id`),
  ADD KEY `idx_mmb_user_id` (`mmb_user_id`),
  ADD KEY `idx_subscriber_id` (`subscriber_id`),
  ADD KEY `idx_role_type` (`role_type`),
  ADD KEY `idx_is_owner` (`is_owner`);

--
-- Indexes for table `mail_webhooks`
--
ALTER TABLE `mail_webhooks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_subscriber_id` (`subscriber_id`);

--
-- Indexes for table `mail_webhook_deliveries`
--
ALTER TABLE `mail_webhook_deliveries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_webhook_id` (`webhook_id`),
  ADD KEY `idx_event_type` (`event_type`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `navbar_settings`
--
ALTER TABLE `navbar_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_user_read_created` (`user_id`,`is_read`,`created_at`);

--
-- Indexes for table `notification_preferences`
--
ALTER TABLE `notification_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_user_type` (`user_id`,`type`);

--
-- Indexes for table `oauth_providers`
--
ALTER TABLE `oauth_providers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_enabled` (`is_enabled`);

--
-- Indexes for table `oauth_user_connections`
--
ALTER TABLE `oauth_user_connections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_provider_user` (`provider_id`,`provider_user_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `ocr_batch_files`
--
ALTER TABLE `ocr_batch_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_job_id` (`job_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `ocr_batch_jobs`
--
ALTER TABLE `ocr_batch_jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `ocr_history`
--
ALTER TABLE `ocr_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_language` (`language`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_user_created` (`user_id`,`created_at`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_token` (`token`);

--
-- Indexes for table `project_permissions`
--
ALTER TABLE `project_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_user_project` (`user_id`,`project_name`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`),
  ADD KEY `idx_key` (`key`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_key` (`setting_key`);

--
-- Indexes for table `template_files`
--
ALTER TABLE `template_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_template_id` (`template_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `idx_google_id` (`google_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_two_factor_enabled` (`two_factor_enabled`),
  ADD KEY `idx_oauth_only` (`oauth_only`);

--
-- Indexes for table `user_devices`
--
ALTER TABLE `user_devices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `user_remember_tokens`
--
ALTER TABLE `user_remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_id` (`session_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_session_id` (`session_id`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `user_templates`
--
ALTER TABLE `user_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_public` (`is_public`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=456;

--
-- AUTO_INCREMENT for table `analytics_events`
--
ALTER TABLE `analytics_events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1117;

--
-- AUTO_INCREMENT for table `api_keys`
--
ALTER TABLE `api_keys`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `api_request_logs`
--
ALTER TABLE `api_request_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blocked_ips`
--
ALTER TABLE `blocked_ips`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `custom_short_links`
--
ALTER TABLE `custom_short_links`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_queue`
--
ALTER TABLE `email_queue`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_logins`
--
ALTER TABLE `failed_logins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `feature_flags`
--
ALTER TABLE `feature_flags`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `home_content`
--
ALTER TABLE `home_content`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `home_projects`
--
ALTER TABLE `home_projects`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `home_sections`
--
ALTER TABLE `home_sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `home_stats`
--
ALTER TABLE `home_stats`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `home_timeline`
--
ALTER TABLE `home_timeline`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `login_history`
--
ALTER TABLE `login_history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `mail_abuse_reports`
--
ALTER TABLE `mail_abuse_reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail_admin_actions`
--
ALTER TABLE `mail_admin_actions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `mail_aliases`
--
ALTER TABLE `mail_aliases`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail_api_keys`
--
ALTER TABLE `mail_api_keys`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail_api_usage_logs`
--
ALTER TABLE `mail_api_usage_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail_attachments`
--
ALTER TABLE `mail_attachments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail_auto_responders`
--
ALTER TABLE `mail_auto_responders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail_billing_history`
--
ALTER TABLE `mail_billing_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail_contacts`
--
ALTER TABLE `mail_contacts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail_dns_records`
--
ALTER TABLE `mail_dns_records`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail_domains`
--
ALTER TABLE `mail_domains`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail_email_templates`
--
ALTER TABLE `mail_email_templates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail_feature_access`
--
ALTER TABLE `mail_feature_access`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail_folders`
--
ALTER TABLE `mail_folders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail_invoices`
--
ALTER TABLE `mail_invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail_logs`
--
ALTER TABLE `mail_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail_mailboxes`
--
ALTER TABLE `mail_mailboxes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail_mail_attachments`
--
ALTER TABLE `mail_mail_attachments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail_mail_filters`
--
ALTER TABLE `mail_mail_filters`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail_mail_lists`
--
ALTER TABLE `mail_mail_lists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail_mail_messages`
--
ALTER TABLE `mail_mail_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail_mail_queue`
--
ALTER TABLE `mail_mail_queue`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail_mail_sessions`
--
ALTER TABLE `mail_mail_sessions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail_mail_statistics`
--
ALTER TABLE `mail_mail_statistics`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail_messages`
--
ALTER TABLE `mail_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail_payments`
--
ALTER TABLE `mail_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail_plan_features`
--
ALTER TABLE `mail_plan_features`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=161;

--
-- AUTO_INCREMENT for table `mail_queue`
--
ALTER TABLE `mail_queue`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail_smtp_credentials`
--
ALTER TABLE `mail_smtp_credentials`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail_subscribers`
--
ALTER TABLE `mail_subscribers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `mail_subscriptions`
--
ALTER TABLE `mail_subscriptions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `mail_subscription_plans`
--
ALTER TABLE `mail_subscription_plans`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `mail_system_settings`
--
ALTER TABLE `mail_system_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail_usage_logs`
--
ALTER TABLE `mail_usage_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail_user_invitations`
--
ALTER TABLE `mail_user_invitations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail_user_roles`
--
ALTER TABLE `mail_user_roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail_webhooks`
--
ALTER TABLE `mail_webhooks`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mail_webhook_deliveries`
--
ALTER TABLE `mail_webhook_deliveries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `navbar_settings`
--
ALTER TABLE `navbar_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_preferences`
--
ALTER TABLE `notification_preferences`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oauth_providers`
--
ALTER TABLE `oauth_providers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `oauth_user_connections`
--
ALTER TABLE `oauth_user_connections`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `ocr_batch_files`
--
ALTER TABLE `ocr_batch_files`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ocr_batch_jobs`
--
ALTER TABLE `ocr_batch_jobs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ocr_history`
--
ALTER TABLE `ocr_history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_permissions`
--
ALTER TABLE `project_permissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `template_files`
--
ALTER TABLE `template_files`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user_devices`
--
ALTER TABLE `user_devices`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user_remember_tokens`
--
ALTER TABLE `user_remember_tokens`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `user_templates`
--
ALTER TABLE `user_templates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `blocked_ips`
--
ALTER TABLE `blocked_ips`
  ADD CONSTRAINT `blocked_ips_ibfk_1` FOREIGN KEY (`blocked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `login_history`
--
ALTER TABLE `login_history`
  ADD CONSTRAINT `login_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `mail_abuse_reports`
--
ALTER TABLE `mail_abuse_reports`
  ADD CONSTRAINT `mail_abuse_reports_ibfk_1` FOREIGN KEY (`reported_mailbox_id`) REFERENCES `mail_mailboxes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `mail_abuse_reports_ibfk_2` FOREIGN KEY (`reported_domain_id`) REFERENCES `mail_domains` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `mail_aliases`
--
ALTER TABLE `mail_aliases`
  ADD CONSTRAINT `fk_aliases_subscriber` FOREIGN KEY (`subscriber_id`) REFERENCES `mail_subscribers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mail_aliases_ibfk_1` FOREIGN KEY (`domain_id`) REFERENCES `mail_domains` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mail_api_keys`
--
ALTER TABLE `mail_api_keys`
  ADD CONSTRAINT `mail_api_keys_ibfk_1` FOREIGN KEY (`subscriber_id`) REFERENCES `mail_subscribers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mail_api_keys_ibfk_2` FOREIGN KEY (`mailbox_id`) REFERENCES `mail_mailboxes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mail_api_usage_logs`
--
ALTER TABLE `mail_api_usage_logs`
  ADD CONSTRAINT `mail_api_usage_logs_ibfk_1` FOREIGN KEY (`api_key_id`) REFERENCES `mail_api_keys` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mail_auto_responders`
--
ALTER TABLE `mail_auto_responders`
  ADD CONSTRAINT `mail_auto_responders_ibfk_1` FOREIGN KEY (`mailbox_id`) REFERENCES `mail_mailboxes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mail_billing_history`
--
ALTER TABLE `mail_billing_history`
  ADD CONSTRAINT `mail_billing_history_ibfk_1` FOREIGN KEY (`subscriber_id`) REFERENCES `mail_subscribers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mail_billing_history_ibfk_2` FOREIGN KEY (`subscription_id`) REFERENCES `mail_subscriptions` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `mail_contacts`
--
ALTER TABLE `mail_contacts`
  ADD CONSTRAINT `mail_contacts_ibfk_1` FOREIGN KEY (`mailbox_id`) REFERENCES `mail_mailboxes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mail_dns_records`
--
ALTER TABLE `mail_dns_records`
  ADD CONSTRAINT `mail_dns_records_ibfk_1` FOREIGN KEY (`domain_id`) REFERENCES `mail_domains` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mail_domains`
--
ALTER TABLE `mail_domains`
  ADD CONSTRAINT `mail_domains_ibfk_1` FOREIGN KEY (`subscriber_id`) REFERENCES `mail_subscribers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mail_email_templates`
--
ALTER TABLE `mail_email_templates`
  ADD CONSTRAINT `mail_email_templates_ibfk_1` FOREIGN KEY (`mailbox_id`) REFERENCES `mail_mailboxes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mail_feature_access`
--
ALTER TABLE `mail_feature_access`
  ADD CONSTRAINT `mail_feature_access_ibfk_1` FOREIGN KEY (`subscriber_id`) REFERENCES `mail_subscribers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mail_folders`
--
ALTER TABLE `mail_folders`
  ADD CONSTRAINT `mail_folders_ibfk_1` FOREIGN KEY (`mailbox_id`) REFERENCES `mail_mailboxes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mail_folders_ibfk_2` FOREIGN KEY (`parent_folder_id`) REFERENCES `mail_folders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mail_invoices`
--
ALTER TABLE `mail_invoices`
  ADD CONSTRAINT `mail_invoices_ibfk_1` FOREIGN KEY (`subscriber_id`) REFERENCES `mail_subscribers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mail_invoices_ibfk_2` FOREIGN KEY (`subscription_id`) REFERENCES `mail_subscriptions` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `mail_logs`
--
ALTER TABLE `mail_logs`
  ADD CONSTRAINT `mail_logs_ibfk_1` FOREIGN KEY (`mailbox_id`) REFERENCES `mail_mailboxes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `mail_logs_ibfk_2` FOREIGN KEY (`message_id`) REFERENCES `mail_mail_messages` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `mail_mailboxes`
--
ALTER TABLE `mail_mailboxes`
  ADD CONSTRAINT `mail_mailboxes_ibfk_1` FOREIGN KEY (`subscriber_id`) REFERENCES `mail_subscribers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mail_mailboxes_ibfk_2` FOREIGN KEY (`domain_id`) REFERENCES `mail_domains` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mail_mail_attachments`
--
ALTER TABLE `mail_mail_attachments`
  ADD CONSTRAINT `mail_mail_attachments_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `mail_mail_messages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mail_mail_filters`
--
ALTER TABLE `mail_mail_filters`
  ADD CONSTRAINT `mail_mail_filters_ibfk_1` FOREIGN KEY (`mailbox_id`) REFERENCES `mail_mailboxes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mail_mail_lists`
--
ALTER TABLE `mail_mail_lists`
  ADD CONSTRAINT `mail_mail_lists_ibfk_1` FOREIGN KEY (`mailbox_id`) REFERENCES `mail_mailboxes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mail_mail_messages`
--
ALTER TABLE `mail_mail_messages`
  ADD CONSTRAINT `mail_mail_messages_ibfk_1` FOREIGN KEY (`mailbox_id`) REFERENCES `mail_mailboxes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mail_mail_messages_ibfk_2` FOREIGN KEY (`folder_id`) REFERENCES `mail_folders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mail_mail_queue`
--
ALTER TABLE `mail_mail_queue`
  ADD CONSTRAINT `mail_mail_queue_ibfk_1` FOREIGN KEY (`mailbox_id`) REFERENCES `mail_mailboxes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mail_mail_sessions`
--
ALTER TABLE `mail_mail_sessions`
  ADD CONSTRAINT `mail_mail_sessions_ibfk_1` FOREIGN KEY (`mailbox_id`) REFERENCES `mail_mailboxes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mail_mail_statistics`
--
ALTER TABLE `mail_mail_statistics`
  ADD CONSTRAINT `mail_mail_statistics_ibfk_1` FOREIGN KEY (`domain_id`) REFERENCES `mail_domains` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mail_mail_statistics_ibfk_2` FOREIGN KEY (`mailbox_id`) REFERENCES `mail_mailboxes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mail_messages`
--
ALTER TABLE `mail_messages`
  ADD CONSTRAINT `mail_messages_ibfk_1` FOREIGN KEY (`mailbox_id`) REFERENCES `mail_mailboxes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mail_messages_ibfk_2` FOREIGN KEY (`folder_id`) REFERENCES `mail_folders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mail_payments`
--
ALTER TABLE `mail_payments`
  ADD CONSTRAINT `mail_payments_ibfk_1` FOREIGN KEY (`subscriber_id`) REFERENCES `mail_subscribers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mail_payments_ibfk_2` FOREIGN KEY (`subscription_id`) REFERENCES `mail_subscriptions` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `mail_plan_features`
--
ALTER TABLE `mail_plan_features`
  ADD CONSTRAINT `mail_plan_features_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `mail_subscription_plans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mail_queue`
--
ALTER TABLE `mail_queue`
  ADD CONSTRAINT `mail_queue_ibfk_1` FOREIGN KEY (`mailbox_id`) REFERENCES `mail_mailboxes` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `mail_smtp_credentials`
--
ALTER TABLE `mail_smtp_credentials`
  ADD CONSTRAINT `mail_smtp_credentials_ibfk_1` FOREIGN KEY (`mailbox_id`) REFERENCES `mail_mailboxes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mail_subscriptions`
--
ALTER TABLE `mail_subscriptions`
  ADD CONSTRAINT `mail_subscriptions_ibfk_1` FOREIGN KEY (`subscriber_id`) REFERENCES `mail_subscribers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mail_subscriptions_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `mail_subscription_plans` (`id`);

--
-- Constraints for table `mail_usage_logs`
--
ALTER TABLE `mail_usage_logs`
  ADD CONSTRAINT `mail_usage_logs_ibfk_1` FOREIGN KEY (`subscriber_id`) REFERENCES `mail_subscribers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mail_user_invitations`
--
ALTER TABLE `mail_user_invitations`
  ADD CONSTRAINT `mail_user_invitations_ibfk_1` FOREIGN KEY (`subscriber_id`) REFERENCES `mail_subscribers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mail_user_roles`
--
ALTER TABLE `mail_user_roles`
  ADD CONSTRAINT `mail_user_roles_ibfk_1` FOREIGN KEY (`subscriber_id`) REFERENCES `mail_subscribers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mail_webhooks`
--
ALTER TABLE `mail_webhooks`
  ADD CONSTRAINT `mail_webhooks_ibfk_1` FOREIGN KEY (`subscriber_id`) REFERENCES `mail_subscribers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mail_webhook_deliveries`
--
ALTER TABLE `mail_webhook_deliveries`
  ADD CONSTRAINT `mail_webhook_deliveries_ibfk_1` FOREIGN KEY (`webhook_id`) REFERENCES `mail_webhooks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `oauth_user_connections`
--
ALTER TABLE `oauth_user_connections`
  ADD CONSTRAINT `oauth_user_connections_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `oauth_user_connections_ibfk_2` FOREIGN KEY (`provider_id`) REFERENCES `oauth_providers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ocr_batch_files`
--
ALTER TABLE `ocr_batch_files`
  ADD CONSTRAINT `ocr_batch_files_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `ocr_batch_jobs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_permissions`
--
ALTER TABLE `project_permissions`
  ADD CONSTRAINT `project_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `template_files`
--
ALTER TABLE `template_files`
  ADD CONSTRAINT `template_files_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `user_templates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_devices`
--
ALTER TABLE `user_devices`
  ADD CONSTRAINT `user_devices_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_remember_tokens`
--
ALTER TABLE `user_remember_tokens`
  ADD CONSTRAINT `user_remember_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
