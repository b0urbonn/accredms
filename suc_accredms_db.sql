-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 19, 2026 at 07:55 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `suc_accredms_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `accreditor_evaluations`
--

CREATE TABLE `accreditor_evaluations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subfolder_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `rating` decimal(3,1) NOT NULL,
  `compliance_result` enum('complied','partially_complied','not_complied') DEFAULT NULL,
  `evaluation` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `log_name` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `event` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `causer_type` varchar(255) DEFAULT NULL,
  `causer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`properties`)),
  `batch_uuid` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `additional_document_requests`
--

CREATE TABLE `additional_document_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subfolder_id` bigint(20) UNSIGNED NOT NULL,
  `requested_by` bigint(20) UNSIGNED NOT NULL,
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `requested_documents` text DEFAULT NULL,
  `remarks` text NOT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('open','resubmitted','fulfilled','cancelled') NOT NULL DEFAULT 'open',
  `fulfilled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `areas`
--

CREATE TABLE `areas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive','submission_ready') DEFAULT 'active',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `areas`
--

INSERT INTO `areas` (`id`, `code`, `name`, `description`, `status`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'AREA-I', 'Vision, Mission, Goals and Objectives', 'The institution’s vision, mission, goals, and objectives, including their dissemination, acceptability, and alignment with institutional and program directions.', 'active', 1, '2026-08-12 04:00:14', '2026-08-14 05:06:49', NULL),
(2, 'AREA-II', 'Faculty', 'The qualifications, competencies, professional development, workload, performance, and other faculty-related requirements.', 'active', 1, '2026-08-12 04:00:14', '2026-08-12 04:00:14', NULL),
(3, 'AREA-III', 'Curriculum and Instruction', 'Curriculum design, instructional processes, teaching-learning activities, assessment, and other academic processes.', 'active', 1, '2026-08-12 04:00:14', '2026-08-12 04:00:14', NULL),
(4, 'AREA-IV', 'Support to Students', 'Student admission, retention, services, development, welfare, participation, and achievement.', 'active', 1, '2026-08-12 04:00:14', '2026-08-12 04:00:14', NULL),
(5, 'AREA-V', 'Research', 'Research policies, programs, activities, productivity, utilization of research results, and research development.', 'active', 1, '2026-08-12 04:00:14', '2026-08-12 04:00:14', NULL),
(6, 'AREA-VI', 'Extension and Community Involvement', 'Extension programs, community engagement, partnerships, services, and the impact of extension activities.', 'active', 1, '2026-08-12 04:00:14', '2026-08-12 04:00:14', NULL),
(7, 'AREA-VII', 'Library', 'Library resources, services, facilities, personnel, information resources, and support for teaching, learning, and research.', 'active', 1, '2026-08-12 04:00:14', '2026-08-12 04:00:14', NULL),
(8, 'AREA-VIII', 'Physical Plant and Facilities', 'Adequacy, accessibility, safety, maintenance, and management of physical facilities and infrastructure.', 'active', 1, '2026-08-12 04:00:14', '2026-08-12 04:00:14', NULL),
(9, 'AREA-IX', 'Laboratories', 'Adequacy, functionality, safety, maintenance, equipment, resources, and management of laboratories.', 'active', 1, '2026-08-12 04:00:14', '2026-08-12 04:00:14', NULL),
(10, 'AREA-X', 'Administration', 'Institutional governance, leadership, management, administrative policies, planning, and organizational effectiveness.', 'active', 1, '2026-08-12 04:00:14', '2026-08-12 04:00:14', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `area_user`
--

CREATE TABLE `area_user` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `area_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `assignment_role` enum('handler','co-handler','member','accreditor') NOT NULL,
  `assigned_by` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `area_user`
--

INSERT INTO `area_user` (`id`, `area_id`, `user_id`, `assignment_role`, `assigned_by`, `assigned_at`, `created_at`, `updated_at`) VALUES
(1, 1, 4, 'handler', 1, '2026-08-13 05:45:18', '2026-08-11 20:34:22', '2026-08-13 05:45:18'),
(2, 1, 11, 'member', 1, '2026-08-13 05:45:18', '2026-08-11 20:34:22', '2026-08-13 05:45:18'),
(3, 1, 10, 'member', 1, '2026-08-13 05:45:18', '2026-08-11 20:34:22', '2026-08-13 05:45:18'),
(4, 1, 9, 'member', 1, '2026-08-13 05:45:18', '2026-08-11 20:34:22', '2026-08-13 05:45:18'),
(5, 1, 8, 'member', 1, '2026-08-13 05:45:18', '2026-08-11 20:34:22', '2026-08-13 05:45:18'),
(6, 1, 6, 'member', 1, '2026-08-13 05:45:18', '2026-08-11 20:34:22', '2026-08-13 05:45:18'),
(7, 1, 7, 'member', 1, '2026-08-13 05:45:18', '2026-08-11 20:34:22', '2026-08-13 05:45:18'),
(8, 2, 12, 'handler', 1, '2026-08-11 20:35:24', '2026-08-11 20:35:24', '2026-08-11 20:35:24'),
(9, 2, 13, 'co-handler', 1, '2026-08-11 20:35:24', '2026-08-11 20:35:24', '2026-08-11 20:35:24'),
(10, 2, 14, 'member', 1, '2026-08-11 20:35:24', '2026-08-11 20:35:24', '2026-08-11 20:35:24'),
(11, 2, 15, 'member', 1, '2026-08-11 20:35:24', '2026-08-11 20:35:24', '2026-08-11 20:35:24'),
(12, 2, 18, 'member', 1, '2026-08-11 20:35:24', '2026-08-11 20:35:24', '2026-08-11 20:35:24'),
(13, 2, 16, 'member', 1, '2026-08-11 20:35:24', '2026-08-11 20:35:24', '2026-08-11 20:35:24'),
(14, 2, 17, 'member', 1, '2026-08-11 20:35:24', '2026-08-11 20:35:24', '2026-08-11 20:35:24'),
(15, 3, 12, 'handler', 1, '2026-08-11 20:36:37', '2026-08-11 20:36:37', '2026-08-11 20:36:37'),
(16, 3, 19, 'co-handler', 1, '2026-08-11 20:36:37', '2026-08-11 20:36:37', '2026-08-11 20:36:37'),
(17, 3, 23, 'member', 1, '2026-08-11 20:36:37', '2026-08-11 20:36:37', '2026-08-11 20:36:37'),
(18, 3, 22, 'member', 1, '2026-08-11 20:36:37', '2026-08-11 20:36:37', '2026-08-11 20:36:37'),
(19, 3, 24, 'member', 1, '2026-08-11 20:36:37', '2026-08-11 20:36:37', '2026-08-11 20:36:37'),
(20, 3, 21, 'member', 1, '2026-08-11 20:36:37', '2026-08-11 20:36:37', '2026-08-11 20:36:37'),
(21, 3, 20, 'member', 1, '2026-08-11 20:36:37', '2026-08-11 20:36:37', '2026-08-11 20:36:37'),
(22, 4, 25, 'handler', 1, '2026-08-11 20:37:55', '2026-08-11 20:37:55', '2026-08-11 20:37:55'),
(23, 4, 26, 'co-handler', 1, '2026-08-11 20:37:55', '2026-08-11 20:37:55', '2026-08-11 20:37:55'),
(24, 4, 23, 'member', 1, '2026-08-11 20:37:55', '2026-08-11 20:37:55', '2026-08-11 20:37:55'),
(25, 4, 18, 'member', 1, '2026-08-11 20:37:55', '2026-08-11 20:37:55', '2026-08-11 20:37:55'),
(26, 4, 28, 'member', 1, '2026-08-11 20:37:55', '2026-08-11 20:37:55', '2026-08-11 20:37:55'),
(27, 4, 29, 'member', 1, '2026-08-11 20:37:55', '2026-08-11 20:37:55', '2026-08-11 20:37:55'),
(28, 4, 27, 'member', 1, '2026-08-11 20:37:55', '2026-08-11 20:37:55', '2026-08-11 20:37:55'),
(29, 5, 30, 'handler', 1, '2026-08-11 20:49:35', '2026-08-11 20:49:35', '2026-08-11 20:49:35'),
(30, 5, 31, 'co-handler', 1, '2026-08-11 20:49:35', '2026-08-11 20:49:35', '2026-08-11 20:49:35'),
(31, 5, 32, 'co-handler', 1, '2026-08-11 20:49:35', '2026-08-11 20:49:35', '2026-08-11 20:49:35'),
(32, 5, 34, 'member', 1, '2026-08-11 20:49:35', '2026-08-11 20:49:35', '2026-08-11 20:49:35'),
(33, 5, 35, 'member', 1, '2026-08-11 20:49:35', '2026-08-11 20:49:35', '2026-08-11 20:49:35'),
(34, 5, 36, 'member', 1, '2026-08-11 20:49:35', '2026-08-11 20:49:35', '2026-08-11 20:49:35'),
(35, 5, 33, 'member', 1, '2026-08-11 20:49:35', '2026-08-11 20:49:35', '2026-08-11 20:49:35'),
(36, 5, 17, 'member', 1, '2026-08-11 20:49:35', '2026-08-11 20:49:35', '2026-08-11 20:49:35'),
(37, 5, 27, 'member', 1, '2026-08-11 20:49:35', '2026-08-11 20:49:35', '2026-08-11 20:49:35'),
(38, 6, 37, 'handler', 1, '2026-08-11 20:50:40', '2026-08-11 20:50:40', '2026-08-11 20:50:40'),
(39, 6, 27, 'co-handler', 1, '2026-08-11 20:50:40', '2026-08-11 20:50:40', '2026-08-11 20:50:40'),
(40, 6, 5, 'member', 1, '2026-08-11 20:50:40', '2026-08-11 20:50:40', '2026-08-11 20:50:40'),
(41, 6, 31, 'member', 1, '2026-08-11 20:50:40', '2026-08-11 20:50:40', '2026-08-11 20:50:40'),
(42, 6, 4, 'member', 1, '2026-08-11 20:50:40', '2026-08-11 20:50:40', '2026-08-11 20:50:40'),
(43, 6, 39, 'member', 1, '2026-08-11 20:50:40', '2026-08-11 20:50:40', '2026-08-11 20:50:40'),
(44, 6, 38, 'member', 1, '2026-08-11 20:50:40', '2026-08-11 20:50:40', '2026-08-11 20:50:40'),
(45, 6, 40, 'member', 1, '2026-08-11 20:50:40', '2026-08-11 20:50:40', '2026-08-11 20:50:40'),
(46, 7, 41, 'handler', 1, '2026-08-11 20:51:33', '2026-08-11 20:51:33', '2026-08-11 20:51:33'),
(47, 7, 42, 'co-handler', 1, '2026-08-11 20:51:33', '2026-08-11 20:51:33', '2026-08-11 20:51:33'),
(48, 7, 14, 'member', 1, '2026-08-11 20:51:33', '2026-08-11 20:51:33', '2026-08-11 20:51:33'),
(49, 7, 5, 'member', 1, '2026-08-11 20:51:33', '2026-08-11 20:51:33', '2026-08-11 20:51:33'),
(50, 7, 43, 'member', 1, '2026-08-11 20:51:33', '2026-08-11 20:51:33', '2026-08-11 20:51:33'),
(51, 7, 44, 'member', 1, '2026-08-11 20:51:33', '2026-08-11 20:51:33', '2026-08-11 20:51:33'),
(52, 8, 45, 'handler', 1, '2026-08-11 20:52:40', '2026-08-11 20:52:40', '2026-08-11 20:52:40'),
(53, 8, 5, 'co-handler', 1, '2026-08-11 20:52:40', '2026-08-11 20:52:40', '2026-08-11 20:52:40'),
(54, 8, 46, 'member', 1, '2026-08-11 20:52:40', '2026-08-11 20:52:40', '2026-08-11 20:52:40'),
(55, 8, 50, 'member', 1, '2026-08-11 20:52:40', '2026-08-11 20:52:40', '2026-08-11 20:52:40'),
(56, 8, 47, 'member', 1, '2026-08-11 20:52:40', '2026-08-11 20:52:40', '2026-08-11 20:52:40'),
(57, 8, 49, 'member', 1, '2026-08-11 20:52:40', '2026-08-11 20:52:40', '2026-08-11 20:52:40'),
(58, 8, 48, 'member', 1, '2026-08-11 20:52:40', '2026-08-11 20:52:40', '2026-08-11 20:52:40'),
(59, 9, 51, 'handler', 1, '2026-08-11 20:53:38', '2026-08-11 20:53:38', '2026-08-11 20:53:38'),
(60, 9, 33, 'co-handler', 1, '2026-08-11 20:53:38', '2026-08-11 20:53:38', '2026-08-11 20:53:38'),
(61, 9, 46, 'member', 1, '2026-08-11 20:53:38', '2026-08-11 20:53:38', '2026-08-11 20:53:38'),
(62, 9, 4, 'member', 1, '2026-08-11 20:53:38', '2026-08-11 20:53:38', '2026-08-11 20:53:38'),
(63, 9, 53, 'member', 1, '2026-08-11 20:53:38', '2026-08-11 20:53:38', '2026-08-11 20:53:38'),
(64, 9, 52, 'member', 1, '2026-08-11 20:53:38', '2026-08-11 20:53:38', '2026-08-11 20:53:38'),
(65, 10, 54, 'handler', 1, '2026-08-11 20:54:58', '2026-08-11 20:54:58', '2026-08-11 20:54:58'),
(66, 10, 32, 'co-handler', 1, '2026-08-11 20:54:58', '2026-08-11 20:54:58', '2026-08-11 20:54:58'),
(67, 10, 59, 'member', 1, '2026-08-11 20:54:58', '2026-08-11 20:54:58', '2026-08-11 20:54:58'),
(68, 10, 8, 'member', 1, '2026-08-11 20:54:58', '2026-08-11 20:54:58', '2026-08-11 20:54:58'),
(69, 10, 20, 'member', 1, '2026-08-11 20:54:58', '2026-08-11 20:54:58', '2026-08-11 20:54:58'),
(70, 10, 58, 'member', 1, '2026-08-11 20:54:58', '2026-08-11 20:54:58', '2026-08-11 20:54:58'),
(71, 10, 55, 'member', 1, '2026-08-11 20:54:58', '2026-08-11 20:54:58', '2026-08-11 20:54:58'),
(72, 10, 6, 'member', 1, '2026-08-11 20:54:58', '2026-08-11 20:54:58', '2026-08-11 20:54:58'),
(73, 10, 56, 'member', 1, '2026-08-11 20:54:58', '2026-08-11 20:54:58', '2026-08-11 20:54:58'),
(74, 10, 48, 'member', 1, '2026-08-11 20:54:58', '2026-08-11 20:54:58', '2026-08-11 20:54:58'),
(75, 10, 57, 'member', 1, '2026-08-11 20:54:58', '2026-08-11 20:54:58', '2026-08-11 20:54:58'),
(76, 1, 3, 'accreditor', 1, '2026-08-13 05:45:18', '2026-08-13 05:45:18', '2026-08-13 05:45:18');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `auditable_type` varchar(150) DEFAULT NULL,
  `auditable_id` bigint(20) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `auditable_type`, `auditable_id`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'toggle_submission', 'App\\Models\\Area', 1, 'Area AREA-I was MARKED AS SUBMISSION-READY by CICS ACCREDITATION ADMIN', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-14 05:06:48'),
(2, 1, 'toggle_submission', 'App\\Models\\Area', 1, 'Area AREA-I was REOPENED FOR EDITING by CICS ACCREDITATION ADMIN', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-14 05:06:49'),
(3, 1, 'generate_report', 'App\\Models\\Area', 1, 'Generated official compliance report for Area AREA-I', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-14 05:12:11'),
(4, 4, 'login', 'App\\Models\\User', 4, 'User JEREMIE R. ROBLES logged in.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-15 04:56:32'),
(5, 4, 'create_parameter', 'App\\Models\\Parameter', 1, 'Created Parameter A under Area AREA-I', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-15 04:56:52'),
(6, 4, 'create_subfolder', 'App\\Models\\Subfolder', 1, 'Created statement sub-item \'S.1\' under Parameter A', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-15 04:59:01'),
(7, 4, 'update_subfolder', 'App\\Models\\Subfolder', 1, 'Updated statement sub-item \'The College/Academic Unit\'s faculty, personnel, students and other stakeholders (cooperating agencies, linkages, alumni, industry sector and other concerned groups) participate in the formulation, review and/or revision of the VMGO.\'', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-15 05:01:23'),
(8, 4, 'create_subfolder', 'App\\Models\\Subfolder', 2, 'Created statement sub-item \'S.1.1\' under S.1 under Parameter A', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-15 05:01:58'),
(9, 4, 'update_subfolder', 'App\\Models\\Subfolder', 1, 'Updated statement sub-item \'The College/Academic Unit\'s faculty, dsdsdsdspersonnel, students and other stakeholders (cooperating agencies, linkages, alumni, industry sector and other concerned groups) participate in the formulation, review and/or revision of the VMGO.\'', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-15 05:45:03'),
(10, 4, 'batch_create_subfolder', 'App\\Models\\ParameterCategory', 1, 'Batch created 3 statement sub-items under Parameter A - System Input and Process', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-15 05:50:22'),
(11, 1, 'login', 'App\\Models\\User', 1, 'User CICS ACCREDITATION ADMIN logged in.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 19:10:19'),
(12, 1, 'upload', 'App\\Models\\Document', 1, 'Uploaded PDF file \'S.1.pdf\' (2.93 MB)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 19:10:55'),
(13, 1, 'view', 'App\\Models\\Document', 1, 'Streamed PDF document \'S.1.pdf\'', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 19:11:02'),
(14, 1, 'view', 'App\\Models\\Document', 1, 'Streamed PDF document \'S.1.pdf\'', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 19:11:03'),
(15, 1, 'view', 'App\\Models\\Document', 1, 'Streamed PDF document \'S.1.pdf\'', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 19:14:08'),
(16, 1, 'view', 'App\\Models\\Document', 1, 'Streamed PDF document \'S.1.pdf\'', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 19:14:09');

-- --------------------------------------------------------

--
-- Table structure for table `board_reviews`
--

CREATE TABLE `board_reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `area_id` bigint(20) UNSIGNED DEFAULT NULL,
  `resolution_number` varchar(255) DEFAULT NULL,
  `review_title` varchar(255) NOT NULL,
  `program` varchar(255) DEFAULT NULL,
  `survey_visit` varchar(255) DEFAULT NULL,
  `board_decision` varchar(255) NOT NULL DEFAULT 'under_board_review',
  `validity_period` varchar(255) DEFAULT NULL,
  `board_remarks` text DEFAULT NULL,
  `conditions_set` text DEFAULT NULL,
  `reviewed_date` date DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'under_review',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('suc-accreditation-dms-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:14:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:12:\"manage-areas\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:17:\"manage-parameters\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:12:\"assign-areas\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:12:\"manage-users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:15:\"view-audit-logs\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:18:\"configure-settings\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:19:\"view-assigned-areas\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:17:\"create-subfolders\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:16:\"upload-documents\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:18:\"compress-documents\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:16:\"delete-documents\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:17:\"preview-documents\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:18:\"download-documents\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:11:\"add-remarks\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}}s:5:\"roles\";a:3:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:5:\"admin\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:7:\"faculty\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:10:\"accreditor\";s:1:\"c\";s:3:\"web\";}}}', 1786717678),
('suc-accreditation-dms-cache-statement-submission:1:13e515e0-f6c5-41c6-9efe-8144a658f772', 'b:1;', 1786630984),
('suc-accreditation-dms-cache-statement-submission:1:270278d5-1339-47a2-bbbb-7bbe4a5e1fc7', 'b:1;', 1786628603),
('suc-accreditation-dms-cache-statement-submission:1:2b50c1ec-6f6f-4503-926c-d497da4254f5', 'b:1;', 1786712169),
('suc-accreditation-dms-cache-statement-submission:1:2d88ad84-c813-407a-b566-0591aaee3ba3', 'b:1;', 1786636102),
('suc-accreditation-dms-cache-statement-submission:1:374aa700-5099-4f20-9adc-f6df980f263b', 'b:1;', 1786628633),
('suc-accreditation-dms-cache-statement-submission:1:47886356-78b3-4a49-8612-dca9c4e0b419', 'b:1;', 1786711220),
('suc-accreditation-dms-cache-statement-submission:1:49a8f35e-3b5b-45f7-9550-b4a1fec47605', 'b:1;', 1786631263),
('suc-accreditation-dms-cache-statement-submission:1:4fbbab77-2c40-4c3b-b6d2-44db1f858b23', 'b:1;', 1786628732),
('suc-accreditation-dms-cache-statement-submission:1:6b59fbbf-705f-4775-b919-4623563c4e2d', 'b:1;', 1786628590),
('suc-accreditation-dms-cache-statement-submission:1:a14d642e-dff2-4dc9-a4a9-5a67ac4696f9', 'b:1;', 1786635132),
('suc-accreditation-dms-cache-statement-submission:1:bcc2cfb4-151e-444e-8c8e-df72d11e8e47', 'b:1;', 1786628413),
('suc-accreditation-dms-cache-statement-submission:1:cbd18986-03bc-498e-9fd1-ab4b70eb13b3', 'b:1;', 1786628653),
('suc-accreditation-dms-cache-statement-submission:1:e086c34f-d894-4f1c-ae5d-c6f553acddf8', 'b:1;', 1786635150),
('suc-accreditation-dms-cache-statement-submission:4:7f760ee1-a323-49d1-a876-d47e92a97379', 'b:1;', 1786799518),
('suc-accreditation-dms-cache-statement-submission:4:cb3df69d-ce62-40dc-8c2f-cf2472fe91c7', 'b:1;', 1786688906),
('suc-accreditation-dms-cache-statement-submission:4:f4b97ddf-1991-469a-ae94-f8a658929d64', 'b:1;', 1786799341);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `sort_order` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'System Input and Process', 'system-input-and-process', 1, '2026-08-07 05:54:30', '2026-08-07 05:54:30'),
(2, 'Outcomes', 'outcomes', 3, '2026-08-07 05:54:30', '2026-08-07 05:54:30'),
(3, 'Implementation', 'implementation', 2, '2026-08-07 05:54:30', '2026-08-07 05:54:30');

-- --------------------------------------------------------

--
-- Table structure for table `compliance_evidences`
--

CREATE TABLE `compliance_evidences` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `compliance_recommendation_id` bigint(20) UNSIGNED NOT NULL,
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `original_filename` varchar(255) NOT NULL,
  `stored_filename` varchar(255) NOT NULL,
  `disk` varchar(50) NOT NULL DEFAULT 'local_private',
  `file_path` varchar(500) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_size_bytes` bigint(20) UNSIGNED NOT NULL,
  `checksum_sha256` varchar(64) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `compliance_recommendations`
--

CREATE TABLE `compliance_recommendations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `compliance_report_id` bigint(20) UNSIGNED NOT NULL,
  `recommendation` text NOT NULL,
  `action_taken` text DEFAULT NULL,
  `compliance_percentage` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `compliance_reports`
--

CREATE TABLE `compliance_reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `area_id` bigint(20) UNSIGNED NOT NULL,
  `program` varchar(255) DEFAULT NULL,
  `survey_visit` varchar(255) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'uploaded',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `copc_files`
--

CREATE TABLE `copc_files` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `singleton_key` varchar(20) NOT NULL DEFAULT 'current',
  `stored_filename` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `disk` varchar(50) NOT NULL DEFAULT 'local_private',
  `file_path` varchar(500) NOT NULL,
  `mime_type` varchar(100) NOT NULL DEFAULT 'application/pdf',
  `file_size_bytes` bigint(20) UNSIGNED NOT NULL,
  `checksum_sha256` varchar(64) NOT NULL,
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subfolder_id` bigint(20) UNSIGNED NOT NULL,
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `original_filename` varchar(255) NOT NULL,
  `stored_filename` varchar(255) NOT NULL,
  `disk` varchar(50) NOT NULL DEFAULT 'local_private',
  `file_path` varchar(500) NOT NULL,
  `mime_type` varchar(100) NOT NULL DEFAULT 'application/pdf',
  `file_size_bytes` bigint(20) NOT NULL,
  `original_size_bytes` bigint(20) DEFAULT NULL,
  `is_compressed` tinyint(1) NOT NULL DEFAULT 0,
  `compression_status` enum('none','pending','processing','done','failed') NOT NULL DEFAULT 'none',
  `checksum_sha256` varchar(64) NOT NULL,
  `version` smallint(6) NOT NULL DEFAULT 1,
  `status` enum('active','archived') NOT NULL DEFAULT 'active',
  `covered_evidences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`covered_evidences`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `subfolder_id`, `uploaded_by`, `original_filename`, `stored_filename`, `disk`, `file_path`, `mime_type`, `file_size_bytes`, `original_size_bytes`, `is_compressed`, `compression_status`, `checksum_sha256`, `version`, `status`, `covered_evidences`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'S.1.pdf', '37b54423-9a03-459a-b381-7f5481bf3521.pdf', 'local_private', 'documents/1/1/1/1/37b54423-9a03-459a-b381-7f5481bf3521.pdf', 'application/pdf', 3067342, 3067342, 0, 'none', '4575f07022a1fd1bb519c16765a0953eceb3d4281e000c13d71f56adb479cf82', 1, 'active', '[]', '2026-08-17 19:10:55', '2026-08-17 19:10:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `document_remarks`
--

CREATE TABLE `document_remarks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `document_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `remark` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_subfolder`
--

CREATE TABLE `document_subfolder` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `document_id` bigint(20) UNSIGNED NOT NULL,
  `subfolder_id` bigint(20) UNSIGNED NOT NULL,
  `covered_evidences` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_versions`
--

CREATE TABLE `document_versions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `document_id` bigint(20) UNSIGNED NOT NULL,
  `version` smallint(6) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size_bytes` bigint(20) NOT NULL,
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `document_versions`
--

INSERT INTO `document_versions` (`id`, `document_id`, `version`, `file_path`, `file_size_bytes`, `uploaded_by`, `created_at`) VALUES
(1, 1, 1, 'documents/1/1/1/1/37b54423-9a03-459a-b381-7f5481bf3521.pdf', 3067342, 1, '2026-08-17 19:10:55');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `failed_jobs`
--

INSERT INTO `failed_jobs` (`id`, `uuid`, `connection`, `queue`, `payload`, `exception`, `failed_at`) VALUES
(1, '96cdd64b-c063-42ed-9e52-d3c6ab6e5431', 'database', 'default', '{\"uuid\":\"96cdd64b-c063-42ed-9e52-d3c6ab6e5431\",\"displayName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"command\":\"O:23:\\\"App\\\\Jobs\\\\CompressPdfJob\\\":1:{s:8:\\\"document\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Document\\\";s:2:\\\"id\\\";i:23;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\",\"batchId\":null},\"createdAt\":1786195572,\"delay\":null}', 'Illuminate\\Database\\Eloquent\\ModelNotFoundException: No query results for model [App\\Models\\Document]. in C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Builder.php:780\nStack trace:\n#0 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\SerializesAndRestoresModelIdentifiers.php(112): Illuminate\\Database\\Eloquent\\Builder->firstOrFail()\n#1 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\SerializesAndRestoresModelIdentifiers.php(63): App\\Jobs\\CompressPdfJob->restoreModel(Object(Illuminate\\Contracts\\Database\\ModelIdentifier))\n#2 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\SerializesModels.php(97): App\\Jobs\\CompressPdfJob->getRestoredPropertyValue(Object(Illuminate\\Contracts\\Database\\ModelIdentifier))\n#3 [internal function]: App\\Jobs\\CompressPdfJob->__unserialize(Array)\n#4 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(97): unserialize(\'O:23:\"App\\\\Jobs\\\\...\')\n#5 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(64): Illuminate\\Queue\\CallQueuedHandler->getCommand(Array)\n#6 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#7 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(504): Illuminate\\Queue\\Jobs\\Job->fire()\n#8 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(454): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#9 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(212): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#10 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(149): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#11 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(132): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#12 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#13 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#14 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#15 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#16 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#17 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#18 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#19 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#20 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#21 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#22 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#23 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#24 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#25 C:\\xampp\\htdocs\\ACCREDMS\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#26 {main}', '2026-08-10 01:17:00'),
(2, '22c2c359-bb86-41db-aa16-4024bfe7a268', 'database', 'default', '{\"uuid\":\"22c2c359-bb86-41db-aa16-4024bfe7a268\",\"displayName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"command\":\"O:23:\\\"App\\\\Jobs\\\\CompressPdfJob\\\":1:{s:8:\\\"document\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Document\\\";s:2:\\\"id\\\";i:24;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\",\"batchId\":null},\"createdAt\":1786195605,\"delay\":null}', 'Illuminate\\Database\\Eloquent\\ModelNotFoundException: No query results for model [App\\Models\\Document]. in C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Builder.php:780\nStack trace:\n#0 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\SerializesAndRestoresModelIdentifiers.php(112): Illuminate\\Database\\Eloquent\\Builder->firstOrFail()\n#1 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\SerializesAndRestoresModelIdentifiers.php(63): App\\Jobs\\CompressPdfJob->restoreModel(Object(Illuminate\\Contracts\\Database\\ModelIdentifier))\n#2 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\SerializesModels.php(97): App\\Jobs\\CompressPdfJob->getRestoredPropertyValue(Object(Illuminate\\Contracts\\Database\\ModelIdentifier))\n#3 [internal function]: App\\Jobs\\CompressPdfJob->__unserialize(Array)\n#4 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(97): unserialize(\'O:23:\"App\\\\Jobs\\\\...\')\n#5 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(64): Illuminate\\Queue\\CallQueuedHandler->getCommand(Array)\n#6 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#7 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(504): Illuminate\\Queue\\Jobs\\Job->fire()\n#8 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(454): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#9 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(212): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#10 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(149): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#11 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(132): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#12 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#13 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#14 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#15 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#16 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#17 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#18 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#19 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#20 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#21 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#22 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#23 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#24 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#25 C:\\xampp\\htdocs\\ACCREDMS\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#26 {main}', '2026-08-10 01:17:00'),
(3, 'bd2e0036-186d-4fd9-8ff6-ad03d2231a02', 'database', 'default', '{\"uuid\":\"bd2e0036-186d-4fd9-8ff6-ad03d2231a02\",\"displayName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"command\":\"O:23:\\\"App\\\\Jobs\\\\CompressPdfJob\\\":1:{s:8:\\\"document\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Document\\\";s:2:\\\"id\\\";i:25;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\",\"batchId\":null},\"createdAt\":1786195893,\"delay\":null}', 'Illuminate\\Database\\Eloquent\\ModelNotFoundException: No query results for model [App\\Models\\Document]. in C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Builder.php:780\nStack trace:\n#0 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\SerializesAndRestoresModelIdentifiers.php(112): Illuminate\\Database\\Eloquent\\Builder->firstOrFail()\n#1 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\SerializesAndRestoresModelIdentifiers.php(63): App\\Jobs\\CompressPdfJob->restoreModel(Object(Illuminate\\Contracts\\Database\\ModelIdentifier))\n#2 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\SerializesModels.php(97): App\\Jobs\\CompressPdfJob->getRestoredPropertyValue(Object(Illuminate\\Contracts\\Database\\ModelIdentifier))\n#3 [internal function]: App\\Jobs\\CompressPdfJob->__unserialize(Array)\n#4 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(97): unserialize(\'O:23:\"App\\\\Jobs\\\\...\')\n#5 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(64): Illuminate\\Queue\\CallQueuedHandler->getCommand(Array)\n#6 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#7 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(504): Illuminate\\Queue\\Jobs\\Job->fire()\n#8 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(454): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#9 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(212): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#10 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(149): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#11 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(132): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#12 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#13 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#14 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#15 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#16 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#17 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#18 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#19 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#20 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#21 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#22 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#23 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#24 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#25 C:\\xampp\\htdocs\\ACCREDMS\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#26 {main}', '2026-08-10 01:17:00'),
(4, '4bbde472-9414-454a-b7b7-dfad8963f2dc', 'database', 'default', '{\"uuid\":\"4bbde472-9414-454a-b7b7-dfad8963f2dc\",\"displayName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"command\":\"O:23:\\\"App\\\\Jobs\\\\CompressPdfJob\\\":1:{s:8:\\\"document\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Document\\\";s:2:\\\"id\\\";i:26;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\",\"batchId\":null},\"createdAt\":1786196305,\"delay\":null}', 'Illuminate\\Database\\Eloquent\\ModelNotFoundException: No query results for model [App\\Models\\Document]. in C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Builder.php:780\nStack trace:\n#0 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\SerializesAndRestoresModelIdentifiers.php(112): Illuminate\\Database\\Eloquent\\Builder->firstOrFail()\n#1 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\SerializesAndRestoresModelIdentifiers.php(63): App\\Jobs\\CompressPdfJob->restoreModel(Object(Illuminate\\Contracts\\Database\\ModelIdentifier))\n#2 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\SerializesModels.php(97): App\\Jobs\\CompressPdfJob->getRestoredPropertyValue(Object(Illuminate\\Contracts\\Database\\ModelIdentifier))\n#3 [internal function]: App\\Jobs\\CompressPdfJob->__unserialize(Array)\n#4 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(97): unserialize(\'O:23:\"App\\\\Jobs\\\\...\')\n#5 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(64): Illuminate\\Queue\\CallQueuedHandler->getCommand(Array)\n#6 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#7 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(504): Illuminate\\Queue\\Jobs\\Job->fire()\n#8 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(454): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#9 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(212): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#10 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(149): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#11 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(132): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#12 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#13 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#14 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#15 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#16 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#17 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#18 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#19 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#20 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#21 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#22 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#23 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#24 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#25 C:\\xampp\\htdocs\\ACCREDMS\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#26 {main}', '2026-08-10 01:17:00'),
(5, '2b102a3d-f103-44df-9790-f455e21afb1b', 'database', 'default', '{\"uuid\":\"2b102a3d-f103-44df-9790-f455e21afb1b\",\"displayName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"command\":\"O:23:\\\"App\\\\Jobs\\\\CompressPdfJob\\\":1:{s:8:\\\"document\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Document\\\";s:2:\\\"id\\\";i:27;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\",\"batchId\":null},\"createdAt\":1786196436,\"delay\":null}', 'Illuminate\\Database\\Eloquent\\ModelNotFoundException: No query results for model [App\\Models\\Document]. in C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Builder.php:780\nStack trace:\n#0 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\SerializesAndRestoresModelIdentifiers.php(112): Illuminate\\Database\\Eloquent\\Builder->firstOrFail()\n#1 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\SerializesAndRestoresModelIdentifiers.php(63): App\\Jobs\\CompressPdfJob->restoreModel(Object(Illuminate\\Contracts\\Database\\ModelIdentifier))\n#2 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\SerializesModels.php(97): App\\Jobs\\CompressPdfJob->getRestoredPropertyValue(Object(Illuminate\\Contracts\\Database\\ModelIdentifier))\n#3 [internal function]: App\\Jobs\\CompressPdfJob->__unserialize(Array)\n#4 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(97): unserialize(\'O:23:\"App\\\\Jobs\\\\...\')\n#5 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(64): Illuminate\\Queue\\CallQueuedHandler->getCommand(Array)\n#6 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#7 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(504): Illuminate\\Queue\\Jobs\\Job->fire()\n#8 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(454): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#9 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(212): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#10 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(149): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#11 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(132): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#12 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#13 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#14 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#15 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#16 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#17 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#18 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#19 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#20 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#21 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#22 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#23 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#24 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#25 C:\\xampp\\htdocs\\ACCREDMS\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#26 {main}', '2026-08-10 01:17:00'),
(6, '963e2637-971e-4d59-a6c9-0d5bd42c2e4c', 'database', 'default', '{\"uuid\":\"963e2637-971e-4d59-a6c9-0d5bd42c2e4c\",\"displayName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"command\":\"O:23:\\\"App\\\\Jobs\\\\CompressPdfJob\\\":1:{s:8:\\\"document\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Document\\\";s:2:\\\"id\\\";i:28;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\",\"batchId\":null},\"createdAt\":1786197548,\"delay\":null}', 'Illuminate\\Database\\Eloquent\\ModelNotFoundException: No query results for model [App\\Models\\Document]. in C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Builder.php:780\nStack trace:\n#0 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\SerializesAndRestoresModelIdentifiers.php(112): Illuminate\\Database\\Eloquent\\Builder->firstOrFail()\n#1 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\SerializesAndRestoresModelIdentifiers.php(63): App\\Jobs\\CompressPdfJob->restoreModel(Object(Illuminate\\Contracts\\Database\\ModelIdentifier))\n#2 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\SerializesModels.php(97): App\\Jobs\\CompressPdfJob->getRestoredPropertyValue(Object(Illuminate\\Contracts\\Database\\ModelIdentifier))\n#3 [internal function]: App\\Jobs\\CompressPdfJob->__unserialize(Array)\n#4 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(97): unserialize(\'O:23:\"App\\\\Jobs\\\\...\')\n#5 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(64): Illuminate\\Queue\\CallQueuedHandler->getCommand(Array)\n#6 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#7 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(504): Illuminate\\Queue\\Jobs\\Job->fire()\n#8 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(454): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#9 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(212): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#10 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(149): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#11 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(132): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#12 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#13 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#14 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#15 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#16 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#17 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#18 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#19 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#20 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#21 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#22 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#23 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#24 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#25 C:\\xampp\\htdocs\\ACCREDMS\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#26 {main}', '2026-08-10 01:17:00'),
(7, 'c829b402-4d30-4cbe-a839-f7e499c9ba5b', 'database', 'default', '{\"uuid\":\"c829b402-4d30-4cbe-a839-f7e499c9ba5b\",\"displayName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"command\":\"O:23:\\\"App\\\\Jobs\\\\CompressPdfJob\\\":1:{s:8:\\\"document\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Document\\\";s:2:\\\"id\\\";i:29;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\",\"batchId\":null},\"createdAt\":1786197567,\"delay\":null}', 'Illuminate\\Database\\Eloquent\\ModelNotFoundException: No query results for model [App\\Models\\Document]. in C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Builder.php:780\nStack trace:\n#0 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\SerializesAndRestoresModelIdentifiers.php(112): Illuminate\\Database\\Eloquent\\Builder->firstOrFail()\n#1 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\SerializesAndRestoresModelIdentifiers.php(63): App\\Jobs\\CompressPdfJob->restoreModel(Object(Illuminate\\Contracts\\Database\\ModelIdentifier))\n#2 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\SerializesModels.php(97): App\\Jobs\\CompressPdfJob->getRestoredPropertyValue(Object(Illuminate\\Contracts\\Database\\ModelIdentifier))\n#3 [internal function]: App\\Jobs\\CompressPdfJob->__unserialize(Array)\n#4 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(97): unserialize(\'O:23:\"App\\\\Jobs\\\\...\')\n#5 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(64): Illuminate\\Queue\\CallQueuedHandler->getCommand(Array)\n#6 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#7 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(504): Illuminate\\Queue\\Jobs\\Job->fire()\n#8 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(454): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#9 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(212): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#10 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(149): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#11 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(132): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#12 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#13 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#14 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#15 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#16 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#17 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#18 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#19 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#20 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#21 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#22 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#23 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#24 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#25 C:\\xampp\\htdocs\\ACCREDMS\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#26 {main}', '2026-08-10 01:17:00');
INSERT INTO `failed_jobs` (`id`, `uuid`, `connection`, `queue`, `payload`, `exception`, `failed_at`) VALUES
(8, 'd62a2dbe-bfb0-45c8-88cf-c82f08187f34', 'database', 'default', '{\"uuid\":\"d62a2dbe-bfb0-45c8-88cf-c82f08187f34\",\"displayName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"command\":\"O:23:\\\"App\\\\Jobs\\\\CompressPdfJob\\\":1:{s:8:\\\"document\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Document\\\";s:2:\\\"id\\\";i:34;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\",\"batchId\":null},\"createdAt\":1786201171,\"delay\":null}', 'Illuminate\\Database\\Eloquent\\ModelNotFoundException: No query results for model [App\\Models\\Document]. in C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Builder.php:780\nStack trace:\n#0 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\SerializesAndRestoresModelIdentifiers.php(112): Illuminate\\Database\\Eloquent\\Builder->firstOrFail()\n#1 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\SerializesAndRestoresModelIdentifiers.php(63): App\\Jobs\\CompressPdfJob->restoreModel(Object(Illuminate\\Contracts\\Database\\ModelIdentifier))\n#2 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\SerializesModels.php(97): App\\Jobs\\CompressPdfJob->getRestoredPropertyValue(Object(Illuminate\\Contracts\\Database\\ModelIdentifier))\n#3 [internal function]: App\\Jobs\\CompressPdfJob->__unserialize(Array)\n#4 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(97): unserialize(\'O:23:\"App\\\\Jobs\\\\...\')\n#5 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(64): Illuminate\\Queue\\CallQueuedHandler->getCommand(Array)\n#6 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#7 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(504): Illuminate\\Queue\\Jobs\\Job->fire()\n#8 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(454): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#9 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(212): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#10 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(149): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#11 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(132): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#12 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#13 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#14 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#15 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#16 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#17 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#18 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#19 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#20 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#21 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#22 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#23 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#24 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#25 C:\\xampp\\htdocs\\ACCREDMS\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#26 {main}', '2026-08-10 01:17:03'),
(9, 'faf2b5f6-ff3b-4ab7-8082-a7d21a8c1cb6', 'database', 'default', '{\"uuid\":\"faf2b5f6-ff3b-4ab7-8082-a7d21a8c1cb6\",\"displayName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"command\":\"O:23:\\\"App\\\\Jobs\\\\CompressPdfJob\\\":1:{s:8:\\\"document\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Document\\\";s:2:\\\"id\\\";i:39;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\",\"batchId\":null},\"createdAt\":1786269229,\"delay\":null}', 'Illuminate\\Database\\Eloquent\\ModelNotFoundException: No query results for model [App\\Models\\Document]. in C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Builder.php:780\nStack trace:\n#0 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\SerializesAndRestoresModelIdentifiers.php(112): Illuminate\\Database\\Eloquent\\Builder->firstOrFail()\n#1 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\SerializesAndRestoresModelIdentifiers.php(63): App\\Jobs\\CompressPdfJob->restoreModel(Object(Illuminate\\Contracts\\Database\\ModelIdentifier))\n#2 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\SerializesModels.php(97): App\\Jobs\\CompressPdfJob->getRestoredPropertyValue(Object(Illuminate\\Contracts\\Database\\ModelIdentifier))\n#3 [internal function]: App\\Jobs\\CompressPdfJob->__unserialize(Array)\n#4 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(97): unserialize(\'O:23:\"App\\\\Jobs\\\\...\')\n#5 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(64): Illuminate\\Queue\\CallQueuedHandler->getCommand(Array)\n#6 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#7 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(504): Illuminate\\Queue\\Jobs\\Job->fire()\n#8 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(454): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#9 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(212): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#10 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(149): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#11 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(132): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#12 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#13 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#14 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#15 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#16 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#17 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#18 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#19 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#20 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#21 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#22 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#23 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#24 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#25 C:\\xampp\\htdocs\\ACCREDMS\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#26 {main}', '2026-08-10 01:17:07'),
(10, '66028361-4d44-4b18-8a9c-21e2c328fa42', 'database', 'default', '{\"uuid\":\"66028361-4d44-4b18-8a9c-21e2c328fa42\",\"displayName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"command\":\"O:23:\\\"App\\\\Jobs\\\\CompressPdfJob\\\":1:{s:8:\\\"document\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Document\\\";s:2:\\\"id\\\";i:44;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\",\"batchId\":null},\"createdAt\":1786270247,\"delay\":null}', 'Illuminate\\Database\\Eloquent\\ModelNotFoundException: No query results for model [App\\Models\\Document]. in C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Eloquent\\Builder.php:780\nStack trace:\n#0 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\SerializesAndRestoresModelIdentifiers.php(112): Illuminate\\Database\\Eloquent\\Builder->firstOrFail()\n#1 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\SerializesAndRestoresModelIdentifiers.php(63): App\\Jobs\\CompressPdfJob->restoreModel(Object(Illuminate\\Contracts\\Database\\ModelIdentifier))\n#2 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\SerializesModels.php(97): App\\Jobs\\CompressPdfJob->getRestoredPropertyValue(Object(Illuminate\\Contracts\\Database\\ModelIdentifier))\n#3 [internal function]: App\\Jobs\\CompressPdfJob->__unserialize(Array)\n#4 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(97): unserialize(\'O:23:\"App\\\\Jobs\\\\...\')\n#5 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(64): Illuminate\\Queue\\CallQueuedHandler->getCommand(Array)\n#6 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#7 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(504): Illuminate\\Queue\\Jobs\\Job->fire()\n#8 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(454): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#9 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(212): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#10 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(149): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#11 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(132): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#12 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#13 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(43): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#14 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#15 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#16 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(799): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#17 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#18 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Command\\Command.php(341): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#19 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#20 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Application.php(1117): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#21 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Application.php(356): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#22 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\symfony\\console\\Application.php(195): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#23 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(198): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#24 C:\\xampp\\htdocs\\ACCREDMS\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Application.php(1235): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#25 C:\\xampp\\htdocs\\ACCREDMS\\artisan(16): Illuminate\\Foundation\\Application->handleCommand(Object(Symfony\\Component\\Console\\Input\\ArgvInput))\n#26 {main}', '2026-08-10 01:17:11');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(34, 'default', '{\"uuid\":\"6963e2c1-e12f-4af2-8883-8be9bf472499\",\"displayName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"command\":\"O:23:\\\"App\\\\Jobs\\\\CompressPdfJob\\\":1:{s:8:\\\"document\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Document\\\";s:2:\\\"id\\\";i:12;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\",\"batchId\":null},\"createdAt\":1786677896,\"delay\":null}', 0, NULL, 1786677896, 1786677896),
(35, 'default', '{\"uuid\":\"e9f17e21-800e-4f21-9f66-4bae10757d5f\",\"displayName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"command\":\"O:23:\\\"App\\\\Jobs\\\\CompressPdfJob\\\":1:{s:8:\\\"document\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Document\\\";s:2:\\\"id\\\";i:13;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\",\"batchId\":null},\"createdAt\":1786684281,\"delay\":null}', 0, NULL, 1786684281, 1786684281),
(36, 'default', '{\"uuid\":\"db24838f-9aa1-43f2-a639-d1ea86f0df66\",\"displayName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"command\":\"O:23:\\\"App\\\\Jobs\\\\CompressPdfJob\\\":1:{s:8:\\\"document\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Document\\\";s:2:\\\"id\\\";i:14;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\",\"batchId\":null},\"createdAt\":1786684966,\"delay\":null}', 0, NULL, 1786684966, 1786684966),
(37, 'default', '{\"uuid\":\"ec5ec189-8bb0-433d-9183-8c9d75ce62fb\",\"displayName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"command\":\"O:23:\\\"App\\\\Jobs\\\\CompressPdfJob\\\":1:{s:8:\\\"document\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Document\\\";s:2:\\\"id\\\";i:16;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\",\"batchId\":null},\"createdAt\":1786685374,\"delay\":null}', 0, NULL, 1786685374, 1786685374),
(38, 'default', '{\"uuid\":\"bada56c5-1e63-4439-a931-daed1acc505b\",\"displayName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"command\":\"O:23:\\\"App\\\\Jobs\\\\CompressPdfJob\\\":1:{s:8:\\\"document\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Document\\\";s:2:\\\"id\\\";i:17;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\",\"batchId\":null},\"createdAt\":1786685406,\"delay\":null}', 0, NULL, 1786685406, 1786685406),
(39, 'default', '{\"uuid\":\"1f0777a8-ea7d-42f8-80db-fac3f1b38888\",\"displayName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"command\":\"O:23:\\\"App\\\\Jobs\\\\CompressPdfJob\\\":1:{s:8:\\\"document\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Document\\\";s:2:\\\"id\\\";i:21;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\",\"batchId\":null},\"createdAt\":1786686352,\"delay\":null}', 0, NULL, 1786686352, 1786686352),
(40, 'default', '{\"uuid\":\"ace1b831-2ed6-4b30-a173-56739708f010\",\"displayName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"command\":\"O:23:\\\"App\\\\Jobs\\\\CompressPdfJob\\\":1:{s:8:\\\"document\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Document\\\";s:2:\\\"id\\\";i:23;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\",\"batchId\":null},\"createdAt\":1786686378,\"delay\":null}', 0, NULL, 1786686378, 1786686378),
(41, 'default', '{\"uuid\":\"60455f37-11b6-4361-ba3e-b7c72ddbaef8\",\"displayName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"command\":\"O:23:\\\"App\\\\Jobs\\\\CompressPdfJob\\\":1:{s:8:\\\"document\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Document\\\";s:2:\\\"id\\\";i:24;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\",\"batchId\":null},\"createdAt\":1786686648,\"delay\":null}', 0, NULL, 1786686648, 1786686648),
(42, 'default', '{\"uuid\":\"8a16bfc8-7236-4fae-810b-2c3319277c0f\",\"displayName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"command\":\"O:23:\\\"App\\\\Jobs\\\\CompressPdfJob\\\":1:{s:8:\\\"document\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Document\\\";s:2:\\\"id\\\";i:27;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\",\"batchId\":null},\"createdAt\":1786688230,\"delay\":null}', 0, NULL, 1786688230, 1786688230),
(43, 'default', '{\"uuid\":\"d0462c7e-317b-431a-9ca3-c20f60ba443a\",\"displayName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"command\":\"O:23:\\\"App\\\\Jobs\\\\CompressPdfJob\\\":1:{s:8:\\\"document\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Document\\\";s:2:\\\"id\\\";i:28;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\",\"batchId\":null},\"createdAt\":1786688262,\"delay\":null}', 0, NULL, 1786688262, 1786688262),
(44, 'default', '{\"uuid\":\"6b13422a-d54c-4db1-b8c6-265c221b0e64\",\"displayName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"command\":\"O:23:\\\"App\\\\Jobs\\\\CompressPdfJob\\\":1:{s:8:\\\"document\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Document\\\";s:2:\\\"id\\\";i:29;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\",\"batchId\":null},\"createdAt\":1786688888,\"delay\":null}', 0, NULL, 1786688888, 1786688888),
(45, 'default', '{\"uuid\":\"2562be5f-89aa-486f-972c-f1b4cd805645\",\"displayName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"command\":\"O:23:\\\"App\\\\Jobs\\\\CompressPdfJob\\\":1:{s:8:\\\"document\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Document\\\";s:2:\\\"id\\\";i:32;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\",\"batchId\":null},\"createdAt\":1786688926,\"delay\":null}', 0, NULL, 1786688926, 1786688926),
(46, 'default', '{\"uuid\":\"ac6d0723-0104-48da-8cff-e8ce493606e2\",\"displayName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"command\":\"O:23:\\\"App\\\\Jobs\\\\CompressPdfJob\\\":1:{s:8:\\\"document\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Document\\\";s:2:\\\"id\\\";i:33;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\",\"batchId\":null},\"createdAt\":1786689805,\"delay\":null}', 0, NULL, 1786689805, 1786689805),
(47, 'default', '{\"uuid\":\"e8002445-93ad-46a3-92e3-2c027e4dedfe\",\"displayName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"command\":\"O:23:\\\"App\\\\Jobs\\\\CompressPdfJob\\\":1:{s:8:\\\"document\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Document\\\";s:2:\\\"id\\\";i:39;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\",\"batchId\":null},\"createdAt\":1786690324,\"delay\":null}', 0, NULL, 1786690324, 1786690324),
(48, 'default', '{\"uuid\":\"866c252c-d3d6-419f-9da8-cb0985dd7ab3\",\"displayName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\CompressPdfJob\",\"command\":\"O:23:\\\"App\\\\Jobs\\\\CompressPdfJob\\\":1:{s:8:\\\"document\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Document\\\";s:2:\\\"id\\\";i:45;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\",\"batchId\":null},\"createdAt\":1786710332,\"delay\":null}', 0, NULL, 1786710332, 1786710332);

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_08_07_000001_create_accredms_tables', 1),
(5, '2026_08_07_135303_create_permission_tables', 1),
(6, '2026_08_07_135304_create_activity_log_table', 1),
(7, '2026_08_07_135305_add_event_column_to_activity_log_table', 1),
(8, '2026_08_07_135306_add_batch_uuid_column_to_activity_log_table', 1),
(9, '2026_08_08_000001_reorder_accreditation_categories', 2),
(10, '2026_08_08_000002_purge_deleted_empty_areas', 3),
(11, '2026_08_08_000003_restore_deleted_areas_with_parameters', 4),
(12, '2026_08_08_000004_enforce_unique_active_statement_codes', 5),
(13, '2026_08_08_000001_create_accreditor_evaluations_table', 6),
(14, '2026_08_10_000001_add_evidence_status_to_subfolders_table', 7),
(15, '2026_08_10_000002_add_review_status_to_subfolders_table', 8),
(16, '2026_08_10_000003_create_additional_document_requests_table', 8),
(17, '2026_08_10_000004_add_compliance_result_to_accreditor_evaluations_table', 8),
(18, '2026_08_10_000005_create_notifications_table', 8),
(19, '2026_08_10_000006_backfill_review_status_for_existing_evidence', 9),
(20, '2026_08_10_000007_create_supplemental_evidence_reviews_table', 10),
(21, '2026_08_11_000001_create_compliance_report_tables', 11),
(22, '2026_08_11_000002_create_program_performance_compliance_files_table', 12),
(23, '2026_08_11_000003_create_copc_files_table', 13),
(24, '2026_08_12_000001_add_co_handler_to_area_user_table', 14),
(25, '2026_08_14_000001_add_completed_checklist_items_to_subfolders_table', 15),
(26, '2026_08_14_000002_add_covered_evidences_to_documents_table', 16),
(27, '2026_08_14_000003_create_technical_reports_and_board_reviews_tables', 17),
(28, '2026_08_14_000004_create_technical_review_approvals_table', 18),
(29, '2026_08_15_000001_expand_audit_logs_and_subfolders_column_lengths', 19),
(30, '2026_08_15_000002_create_document_subfolder_table', 20);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 2),
(2, 'App\\Models\\User', 4),
(2, 'App\\Models\\User', 5),
(2, 'App\\Models\\User', 6),
(2, 'App\\Models\\User', 7),
(2, 'App\\Models\\User', 8),
(2, 'App\\Models\\User', 9),
(2, 'App\\Models\\User', 10),
(2, 'App\\Models\\User', 11),
(2, 'App\\Models\\User', 12),
(2, 'App\\Models\\User', 13),
(2, 'App\\Models\\User', 14),
(2, 'App\\Models\\User', 15),
(2, 'App\\Models\\User', 16),
(2, 'App\\Models\\User', 17),
(2, 'App\\Models\\User', 18),
(2, 'App\\Models\\User', 19),
(2, 'App\\Models\\User', 20),
(2, 'App\\Models\\User', 21),
(2, 'App\\Models\\User', 22),
(2, 'App\\Models\\User', 23),
(2, 'App\\Models\\User', 24),
(2, 'App\\Models\\User', 25),
(2, 'App\\Models\\User', 26),
(2, 'App\\Models\\User', 27),
(2, 'App\\Models\\User', 28),
(2, 'App\\Models\\User', 29),
(2, 'App\\Models\\User', 30),
(2, 'App\\Models\\User', 31),
(2, 'App\\Models\\User', 32),
(2, 'App\\Models\\User', 33),
(2, 'App\\Models\\User', 34),
(2, 'App\\Models\\User', 35),
(2, 'App\\Models\\User', 36),
(2, 'App\\Models\\User', 37),
(2, 'App\\Models\\User', 38),
(2, 'App\\Models\\User', 39),
(2, 'App\\Models\\User', 40),
(2, 'App\\Models\\User', 41),
(2, 'App\\Models\\User', 42),
(2, 'App\\Models\\User', 43),
(2, 'App\\Models\\User', 44),
(2, 'App\\Models\\User', 45),
(2, 'App\\Models\\User', 46),
(2, 'App\\Models\\User', 47),
(2, 'App\\Models\\User', 48),
(2, 'App\\Models\\User', 49),
(2, 'App\\Models\\User', 50),
(2, 'App\\Models\\User', 51),
(2, 'App\\Models\\User', 52),
(2, 'App\\Models\\User', 53),
(2, 'App\\Models\\User', 54),
(2, 'App\\Models\\User', 55),
(2, 'App\\Models\\User', 56),
(2, 'App\\Models\\User', 57),
(2, 'App\\Models\\User', 58),
(2, 'App\\Models\\User', 59),
(3, 'App\\Models\\User', 3),
(3, 'App\\Models\\User', 60);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parameters`
--

CREATE TABLE `parameters` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `area_id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(20) NOT NULL,
  `title` text NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` smallint(6) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `parameters`
--

INSERT INTO `parameters` (`id`, `area_id`, `code`, `title`, `description`, `sort_order`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'A', 'DSD', NULL, 0, 'active', '2026-08-15 04:56:52', '2026-08-15 04:56:52', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `parameter_categories`
--

CREATE TABLE `parameter_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `parameter_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `parameter_categories`
--

INSERT INTO `parameter_categories` (`id`, `parameter_id`, `category_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2026-08-15 04:56:52', '2026-08-15 04:56:52'),
(2, 1, 3, '2026-08-15 04:56:52', '2026-08-15 04:56:52'),
(3, 1, 2, '2026-08-15 04:56:52', '2026-08-15 04:56:52');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'manage-areas', 'web', '2026-08-07 05:54:30', '2026-08-07 05:54:30'),
(2, 'manage-parameters', 'web', '2026-08-07 05:54:30', '2026-08-07 05:54:30'),
(3, 'assign-areas', 'web', '2026-08-07 05:54:30', '2026-08-07 05:54:30'),
(4, 'manage-users', 'web', '2026-08-07 05:54:30', '2026-08-07 05:54:30'),
(5, 'view-audit-logs', 'web', '2026-08-07 05:54:30', '2026-08-07 05:54:30'),
(6, 'configure-settings', 'web', '2026-08-07 05:54:30', '2026-08-07 05:54:30'),
(7, 'view-assigned-areas', 'web', '2026-08-07 05:54:30', '2026-08-07 05:54:30'),
(8, 'create-subfolders', 'web', '2026-08-07 05:54:30', '2026-08-07 05:54:30'),
(9, 'upload-documents', 'web', '2026-08-07 05:54:30', '2026-08-07 05:54:30'),
(10, 'compress-documents', 'web', '2026-08-07 05:54:30', '2026-08-07 05:54:30'),
(11, 'delete-documents', 'web', '2026-08-07 05:54:30', '2026-08-07 05:54:30'),
(12, 'preview-documents', 'web', '2026-08-07 05:54:30', '2026-08-07 05:54:30'),
(13, 'download-documents', 'web', '2026-08-07 05:54:30', '2026-08-07 05:54:30'),
(14, 'add-remarks', 'web', '2026-08-07 05:54:30', '2026-08-07 05:54:30');

-- --------------------------------------------------------

--
-- Table structure for table `program_performance_compliance_files`
--

CREATE TABLE `program_performance_compliance_files` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `area_id` bigint(20) UNSIGNED NOT NULL,
  `stored_filename` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `disk` varchar(50) NOT NULL DEFAULT 'local_private',
  `file_path` varchar(500) NOT NULL,
  `mime_type` varchar(100) NOT NULL DEFAULT 'application/pdf',
  `file_size_bytes` bigint(20) UNSIGNED NOT NULL,
  `checksum_sha256` varchar(64) NOT NULL,
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'web', '2026-08-07 05:54:30', '2026-08-07 05:54:30'),
(2, 'faculty', 'web', '2026-08-07 05:54:30', '2026-08-07 05:54:30'),
(3, 'accreditor', 'web', '2026-08-07 05:54:30', '2026-08-07 05:54:30');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(7, 2),
(7, 3),
(8, 1),
(8, 2),
(9, 1),
(9, 2),
(10, 1),
(10, 2),
(11, 1),
(11, 2),
(12, 1),
(12, 2),
(12, 3),
(13, 1),
(13, 2),
(14, 1),
(14, 3);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('1hMaswWRgiFkEA6iJJ9WzowIY3klozvy8syJLWoO', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoib2xHS0dYck1UajJ0cWhlWWRKd0JzdTZKb3J4V2VDRk9lRkRFbFNkdiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDI6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9ub3RpZmljYXRpb25zL3VucmVhZCI7czo1OiJyb3V0ZSI7czoyMDoibm90aWZpY2F0aW9ucy51bnJlYWQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1787023426),
('kyQHcBM3rRfUubtGRDSmDioOz0nPwd15aycmBB7I', 4, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiRmJLQ1FINE9SZDNaQno2QnpNeWhtNXRhN0hwUWFUYm5SRTZlaThxMSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDI6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9ub3RpZmljYXRpb25zL3VucmVhZCI7czo1OiJyb3V0ZSI7czoyMDoibm90aWZpY2F0aW9ucy51bnJlYWQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo0O30=', 1786807309);

-- --------------------------------------------------------

--
-- Table structure for table `subfolders`
--

CREATE TABLE `subfolders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `parameter_category_id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `code` varchar(50) DEFAULT NULL,
  `active_code` varchar(50) GENERATED ALWAYS AS (case when `deleted_at` is null then `code` else NULL end) STORED,
  `name` text NOT NULL,
  `documents_needed` text DEFAULT NULL,
  `completed_checklist_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`completed_checklist_items`)),
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('active','archived') NOT NULL DEFAULT 'active',
  `evidence_status` enum('draft','submitted','under_review','needs_revision','approved') NOT NULL DEFAULT 'draft',
  `review_status` enum('no_evidence','under_review','additional_documents_requested','resubmitted','evaluated') NOT NULL DEFAULT 'no_evidence',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subfolders`
--

INSERT INTO `subfolders` (`id`, `parameter_category_id`, `parent_id`, `code`, `name`, `documents_needed`, `completed_checklist_items`, `created_by`, `status`, `evidence_status`, `review_status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, NULL, 'S.1', 'The College/Academic Unit\'s faculty, dsdsdsdspersonnel, students and other stakeholders (cooperating agencies, linkages, alumni, industry sector and other concerned groups) participate in the formulation, review and/or revision of the VMGO.', 'SASASAS', '[]', 4, 'active', 'draft', 'under_review', '2026-08-15 04:59:01', '2026-08-17 19:10:55', NULL),
(2, 1, 1, 'S.1.1', 'The College/Academic Unit\'s faculty, personnel, students and other stakeholders (cooperating agencies, linkages, alumni, industry sector and other concerned groups) participate in the formulation, review and/or revision of the VMGO.', 'SASAS', NULL, 4, 'active', 'draft', 'no_evidence', '2026-08-15 05:01:58', '2026-08-15 05:01:58', NULL),
(3, 1, 1, 'S.1.2', 'dsdsd', 'dsd', NULL, 4, 'active', 'draft', 'no_evidence', '2026-08-15 05:50:22', '2026-08-15 05:50:22', NULL),
(4, 1, 1, 'S.1.3', 'dsds', 'dsd', NULL, 4, 'active', 'draft', 'no_evidence', '2026-08-15 05:50:22', '2026-08-15 05:50:22', NULL),
(5, 1, 1, 'S.1.4', 'ds', 'as', NULL, 4, 'active', 'draft', 'no_evidence', '2026-08-15 05:50:22', '2026-08-15 05:50:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `supplemental_evidence_reviews`
--

CREATE TABLE `supplemental_evidence_reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `document_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `result` enum('accepted','needs_revision') NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `technical_reports`
--

CREATE TABLE `technical_reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `area_id` bigint(20) UNSIGNED DEFAULT NULL,
  `report_number` varchar(255) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `program` varchar(255) DEFAULT NULL,
  `survey_visit` varchar(255) DEFAULT NULL,
  `summary_findings` text DEFAULT NULL,
  `technical_evaluation` text DEFAULT NULL,
  `strengths` text DEFAULT NULL,
  `areas_for_improvement` text DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  `overall_score` decimal(4,2) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'draft',
  `prepared_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `technical_review_approvals`
--

CREATE TABLE `technical_review_approvals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `area_id` bigint(20) UNSIGNED DEFAULT NULL,
  `category` varchar(255) NOT NULL DEFAULT 'general',
  `stored_filename` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `disk` varchar(255) NOT NULL DEFAULT 'local_private',
  `mime_type` varchar(255) DEFAULT NULL,
  `file_size_bytes` bigint(20) NOT NULL DEFAULT 0,
  `checksum_sha256` varchar(255) DEFAULT NULL,
  `uploaded_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` varchar(50) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `avatar_path` varchar(255) DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `employee_id`, `name`, `email`, `email_verified_at`, `password`, `role_id`, `status`, `avatar_path`, `last_login_at`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'CICS-0001', 'CICS ACCREDITATION ADMIN', 'admin@cics.marsu.edu.ph', NULL, '$2y$12$0jAwqr6czNynMdtytycn4.YSQH1rCEQQQgR7J4KpShSQnW.iojtHu', 1, 'active', NULL, '2026-08-17 19:10:17', NULL, '2026-08-07 05:54:31', '2026-08-17 19:10:17', NULL),
(2, 'FAC-001', 'Prof. Juan Dela Cruz', 'faculty@cics.marsu.edu.ph', NULL, '$2y$12$67XMhRVmaGc.zzkP0T0ykOLpYNR0ta4Xyf.OSeTRBSJArinJ49Mzq', 2, 'active', NULL, '2026-08-11 18:05:10', NULL, '2026-08-07 05:54:31', '2026-08-11 20:29:23', '2026-08-11 20:29:23'),
(3, 'ACC-001', 'Dr. Maria Santos (AACCUP Accreditor)', 'accreditor@cics.marsu.edu.ph', NULL, '$2y$12$RGyjszNEK87.7cfGi0lT5.FUezYfnmCyn8o81XmHi0iWKuSy7ANHu', 3, 'active', NULL, '2026-08-14 03:39:33', NULL, '2026-08-07 05:54:31', '2026-08-14 03:39:33', NULL),
(4, '2023-0370', 'JEREMIE R. ROBLES', 'robles.jeremie@marsu.edu.ph', NULL, '$2y$12$2TBTs.yEgz2zZ89rHLh5Pu3uC7Qmn0OANL9l4rP3W2odJV04Qjge6', 2, 'active', NULL, '2026-08-15 04:56:30', NULL, '2026-08-07 21:54:31', '2026-08-15 04:56:30', NULL),
(5, '2023-0303', 'ALFRED FLORES', 'alfredflores@gmail.com', NULL, '$2y$12$NaJNg/8aCt1lZcPG9ws9He/EKYZfdXbK4ztDXat6inXtMqjiQX0yO', 2, 'active', NULL, '2026-08-10 01:10:53', NULL, '2026-08-07 22:00:33', '2026-08-10 01:10:53', NULL),
(6, 'ACC-003', 'Lady May Logatoc', 'LadyLogatoc@gmail.com', NULL, '$2y$12$qXLspkuyNjFe4he4IRT4jedEcXnMLLeqSD93n9JV4qD4S1COCCF7G', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(7, 'ACC-004', 'Roche M. Zoleta', 'RocheZoleta@gmail.com', NULL, '$2y$12$LIs2IK.tEnlN8ZBWUQ1kv./Fp8AO9xau2fE3RoJBO/z1gkWbzDB32', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(8, 'ACC-005', 'Jethro L. Magcamit', 'JethroMagcamit@gmail.com', NULL, '$2y$12$sCSxrmhrPwur58hEszQZPuJu/7cgaeohThiXEs/J5DcfH1efhRa.O', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(9, 'ACC-006', 'Jayvi M. Mandalihan', 'JayviMandalihan@gmail.com', NULL, '$2y$12$L0Hxcgg7mb/jpX1mMoSfH.kyECzqb1hT.ww.OxzHm6DDcOJZu.YFW', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(10, 'ACC-007', 'Christopher J. Rebistual', 'ChristopherRebistual@gmail.com', NULL, '$2y$12$3rw1XiMurB4occF2CfOpOePSEXzCptiAczkzmi.oycD4K6/usBTsm', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(11, 'ACC-008', 'Charissa May R. Fernandez', 'CharissaFernandez@gmail.com', NULL, '$2y$12$RyESazpiSG2u6Cm9ah96u.XoXsvAoEmyz2IV2rBZIoQhGM.De1AJq', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(12, 'ACC-009', 'Loriebenn B. Madrino', 'LoriebennMadrino@gmail.com', NULL, '$2y$12$xwfAP3GMtN5YO9Bnrk4I.uVdcUd214l9Ip1fhnZVo8X5vdigYkgC2', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(13, 'ACC-010', 'Blanchie L. Nieva', 'BlanchieNieva@gmail.com', NULL, '$2y$12$lgtQLZgXZVepPT1WDWnxju.3SYvfzzQZDBHR6U0AkEmdaWowqON0q', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(14, 'ACC-011', 'Aldrin R. Rey', 'AldrinRey@gmail.com', NULL, '$2y$12$Og2s8yfxWZ5dtg34QQZZ.OaUUCuNFANTS8wUnY4KFqdiV87xLcEuS', 2, 'active', NULL, '2026-08-11 20:30:37', NULL, '2026-08-12 04:13:02', '2026-08-11 20:30:44', NULL),
(15, 'ACC-012', 'Art Jervin L. Magcamit', 'ArtMagcamit@gmail.com', NULL, '$2y$12$ZwKjsFSGLtfey6Pc05jr.OmY/yVqy7nP9ojErbNnpE1W6L6h4YQvm', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(16, 'ACC-013', 'Kierven A. Villaruel', 'KiervenVillaruel@gmail.com', NULL, '$2y$12$glOTqA9hg1HI.ZmdprBszuDXUsIjwn7dHJPEqjz7mE/1lUz8XN3WC', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(17, 'ACC-014', 'Vanessa Buiza', 'VanessaBuiza@gmail.com', NULL, '$2y$12$fhiOt.MSCbfgfa/CaVAyt.brW7zmMy/zgpQEWgcoWwDAj433G6ihW', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(18, 'ACC-015', 'Ivy Joy N. Aguilflor', 'IvyAguilflor@gmail.com', NULL, '$2y$12$E4Ju8Tsr5r3QDAMJ5plk3.5wQzDRiRYkZpqRPS1DZSueOYNjMbqUS', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(19, 'ACC-016', 'Beneden M. Logmao', 'BenedenLogmao@gmail.com', NULL, '$2y$12$/43KyQLYgXfZFsNvJUQeHeCshYuuma6pNvDf5AjI4VTRW7KE9nxzi', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(20, 'ACC-017', 'Joefel N. Pabelona', 'JoefelPabelona@gmail.com', NULL, '$2y$12$W75m5TRSZkqXJP9.5kAcXOtqFsvqjQyK.cqFTqJMRIlJOBwYTijhW', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(21, 'ACC-018', 'Jet Francis Q. Podaca', 'JetPodaca@gmail.com', NULL, '$2y$12$Z9ctAcMKkN3CkCd2PDoMMectSYroYxlbPtVfOtfE2HqYUe1BbELUq', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(22, 'ACC-019', 'Doreena Joy C. Borja', 'DoreenaBorja@gmail.com', NULL, '$2y$12$rOC13z382andwF9i0Y8FZO9LBeoEYhWH7k9zajTOyM.dOYSGAa/q2', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(23, 'ACC-020', 'Arvin Cesar R. Pernia', 'ArvinPernia@gmail.com', NULL, '$2y$12$z0DWeJzpR/25r/96YfIeCejwrmNZxHopQkUiPi9.9Xt4CpYYP7bS.', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(24, 'ACC-021', 'Iveelyn L. Fellizar', 'IveelynFellizar@gmail.com', NULL, '$2y$12$rp11yiKDJoD.ZY/rXF6V3eTt8VmYpAowgQBNViISJzxxXPi7qTnFa', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(25, 'ACC-022', 'Marvin P. Plata', 'MarvinPlata@gmail.com', NULL, '$2y$12$zGT/mGCrVv87gFSzk2wThe6L/fTa5RNM6L6.e.I/FvcTxnXVXHrYG', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(26, 'ACC-023', 'Ken Mark M. Palatino', 'KenPalatino@gmail.com', NULL, '$2y$12$Vpl/GIpUtSl9dAlaAl0nguarpmS/rcXgj5QgccgjjpESFlPMTu5YC', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(27, 'ACC-024', 'Wilmer M. Pascual', 'WilmerPascual@gmail.com', NULL, '$2y$12$qXmlHxIjBg9ozv67.gAINOUN0DtsLP0jXi73kTiA.tklxAoFE5v/m', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(28, 'ACC-025', 'Jellian T. Ricafrente', 'JellianRicafrente@gmail.com', NULL, '$2y$12$XGrwmNS0BcDVtauIN6pqauB56.3renL94u1Gdye.89fyovUbTaJXe', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(29, 'ACC-026', 'Maria Jeanne P. Estrella', 'MariaEstrella@gmail.com', NULL, '$2y$12$ZC9NU1TFV5RTnxy5etSfYenlCARfe8qUoE0b4Vg8TMDkaNpKuV.CK', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(30, 'ACC-027', 'Evangeline B. Mandia', 'EvangelineMandia@gmail.com', NULL, '$2y$12$Uo2m4TxT4We9bODgAF7hnOOY2UAPSW9.6VHeZyHFPe07UFe6DMQA2', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(31, 'ACC-028', 'Christine May L. Nabos', 'ChristineNabos@gmail.com', NULL, '$2y$12$XJ5Y2M30/Epa7qVnQTtGmuK/j7GDlXzFX5/mfiVtp/a1djNSmL2.q', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(32, 'ACC-029', 'Randell R. Reginio', 'RandellReginio@gmail.com', NULL, '$2y$12$FAwzsCQkko.Froj9mhKAIeiucP1K6TrXCknVXiaajI0HnfG9e8Hkq', 2, 'active', NULL, '2026-08-11 20:55:23', NULL, '2026-08-12 04:13:02', '2026-08-11 20:55:23', NULL),
(33, 'ACC-030', 'Raphael Dale R. Ogbac', 'RaphaelOgbac@gmail.com', NULL, '$2y$12$5yfO5xAsGjrZ3sBMfhmFH.cmNfKH8o2Gs27qW/8CfMHbRI0H58iCG', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(34, 'ACC-031', 'Ella Joy S. Marbello', 'EllaMarbello@gmail.com', NULL, '$2y$12$wlsqaeQzGW8jQKGO2wvfUu2mQqqkR/n9ahWOqoOMLCJ17rLZdvNle', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(35, 'ACC-032', 'Eunice G. De Luna-Malinao', 'EuniceLunaMalinao@gmail.com', NULL, '$2y$12$gQiqBf3HmRrZfK1Fj7gj0.UD6Jv7AEl9gDB3r7aIUrv5451oVhsAO', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(36, 'ACC-033', 'Kenneth L. Omipling', 'KennethOmipling@gmail.com', NULL, '$2y$12$zzwdsUKbKAlXp6PEYRBeN.YbO5W93CxSMDbHKZoyoQmTNMn9Gpode', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(37, 'ACC-034', 'Doreen R. Mascareñas', 'DoreenMascarenas@gmail.com', NULL, '$2y$12$2tHHEQBPV4kl9eazPrPUZuflNgxLhTXUjVPV5AL1eXsbr48BR2b6G', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(38, 'ACC-035', 'Maria Nessa M. Solomon', 'MariaSolomon@gmail.com', NULL, '$2y$12$3xP/FZAdyIW6PZg8XMkSI.0fvTwLuy3UrRLOANujXCpWjviMkmWHG', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(39, 'ACC-037', 'Joshua M. Relatores', 'JoshuaRelatores@gmail.com', NULL, '$2y$12$89/ktpki48yufauRKsG0aebrzOJvZyECnd7vA1C2hjJqYxmNXKgVa', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(40, 'ACC-038', 'Mark Carlo Valenzuela', 'MarkValenzuela@gmail.com', NULL, '$2y$12$rsX9.ddoAzDyDacdLX6YNeI0GHhkrY2QlvKJnrx7SZn9gu1e51N3a', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(41, 'ACC-039', 'Arlene M. Ruale', 'ArleneRuale@gmail.com', NULL, '$2y$12$oz.xqYu1z37UAiQkti/3jOy6tthuOTJX.4mY.FVsepjrwpNHjb7.O', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(42, 'ACC-040', 'Marian Kristazel Antolin', 'MarianAntolin@gmail.com', NULL, '$2y$12$4te5/.3E63DT8MPl2JqO9uX9a5Z3AKscnr7rvpuoOnF3yzQ.tk1Wq', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(43, 'ACC-041', 'Coney Marie O. Rejano', 'ConeyRejano@gmail.com', NULL, '$2y$12$S0G0yVTUUZmNIUObFGb1p.pFVHJv50gtemEX.hBTKEMZIiUbnr3/y', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(44, 'ACC-042', 'Recella M. Ortega', 'RecellaOrtega@gmail.com', NULL, '$2y$12$dHk05C.mkVhtjR5MEk6gWuJz6jKR37AlqEHwQgYvy9z11BCTTKSfu', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(45, 'ACC-043', 'Maynard M. Muhi', 'MaynardMuhi@gmail.com', NULL, '$2y$12$s.Z1CLyfX4gj2LMF5vgR6eMQsmGI5aMT2LIMdEpwFCPvr0s4atbcy', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(46, 'ACC-044', 'Alfonso Q. Reyneso', 'AlfonsoReyneso@gmail.com', NULL, '$2y$12$BNv4y/iyD4mYzzICIrK7yuBfgW5WtSWdnQTR6BzmzKM7Sya3KFjLC', 2, 'active', NULL, '2026-08-11 20:30:07', NULL, '2026-08-12 04:13:02', '2026-08-11 20:30:07', NULL),
(47, 'ACC-045', 'Joan Jean P. Revilla', 'JoanRevilla@gmail.com', NULL, '$2y$12$n4al8Kj6SUjtfdLYRgjekeuJmLbTJArTKnwbvhYBbKQ/28QztLLX2', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(48, 'ACC-046', 'Sanver Andrew Mapacpac', 'SanverMapacpac@gmail.com', NULL, '$2y$12$qbMBcZpgstfkR0w/HpVg1.pKnHOD4ge6sfRzVpad74xGmftrs7eq.', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(49, 'ACC-047', 'Melvin M. Carpio', 'MelvinCarpio@gmail.com', NULL, '$2y$12$pvVPlcvbKnQ5Fn3Q7A4f1OMEU4fPswvPy5rWQeFbAyA0E.qxmOYz.', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(50, 'ACC-048', 'Amelito R. Zulueta', 'AmelitoZulueta@gmail.com', NULL, '$2y$12$WwUoVv.kyBMfU6bNOnVvA.xWuVj1R7luRcAly4l8OYNEr6BMWQTtu', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(51, 'ACC-049', 'Jeffrey M. Marapia', 'JeffreyMarapia@gmail.com', NULL, '$2y$12$/XYE46oJ2Z551UxMZ3t3u.dve0s0k29GD8VuIQxmwNNo90xD9Aq/O', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(52, 'ACC-050', 'Randel Bien J. Constantino', 'RandelConstantino@gmail.com', NULL, '$2y$12$HMWsh9FJg7y8cGg.fTzHcuzlx8PkWLgovF/a4Bnw7z9U.Qke3Zppm', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(53, 'ACC-051', 'Jonex M. Ornedo', 'JonexOrnedo@gmail.com', NULL, '$2y$12$1fxZNOwshHFK1FcaXITPZewWyDTxrSBzYPDLW3LXCA0Fp07aYlq9G', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(54, 'ACC-052', 'Ronjie Mar L. Malinao', 'RonjieMalinao@gmail.com', NULL, '$2y$12$KKy9uzAbl.vh3LVKD.bEXen/Nsk0mLWi2kzSNBiMJ6K35sMS/5v4e', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(55, 'ACC-053', 'Khristine L. Palmiery', 'KhristinePalmiery@gmail.com', NULL, '$2y$12$6jHVz9WZzt9ms7lBoECaK.Mphrl/VK/uEnaGuj8vE5sL3ugeZv6EW', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(56, 'ACC-054', 'Ryan Riuz', 'RyanRiuz@gmail.com', NULL, '$2y$12$fn.ZM1LWC22YX1scJOYv4.h.ZoT4OX7K6PU6fu9eIJU3XXT79F4Mq', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(57, 'ACC-055', 'Villi Dane M. Go', 'VilliGo@gmail.com', NULL, '$2y$12$LdywRYUYdE5ZKAy0hS9eP..qIfvBf5MKJ/DZLIXi7Y2FcNXlX52pe', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(58, 'ACC-056', 'Kevin H. Jasmin', 'KevinJasmin@gmail.com', NULL, '$2y$12$6gBUmujE9JhA.I/5yr.e5ObB9ySeQtjp8cq1OnHvm16bxuIC2s5Xe', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(59, 'ACC-057', 'Janebeth R. Nagutom', 'JanebethNagutom@gmail.com', NULL, '$2y$12$qeWrSA6.zIoKkL5RBg15Bu.g1fD/1.6IaYoCRFAU54vdlmVYxHdzC', 2, 'active', NULL, NULL, NULL, '2026-08-12 04:13:02', '2026-08-12 04:13:02', NULL),
(60, 'FAC-0000', 'JUAN DELA CRUZ', 'Juandelacruz@gmail.com', NULL, '$2y$12$PSrjw88XL7BbCq//gSne7OX3flfI5PrMtLmjqtW8CWls.KctQGS3i', 3, 'active', NULL, '2026-08-11 20:32:13', NULL, '2026-08-11 20:31:55', '2026-08-11 20:32:13', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accreditor_evaluations`
--
ALTER TABLE `accreditor_evaluations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `accreditor_evaluation_unique` (`subfolder_id`,`user_id`),
  ADD KEY `accreditor_evaluations_user_id_foreign` (`user_id`);

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject` (`subject_type`,`subject_id`),
  ADD KEY `causer` (`causer_type`,`causer_id`),
  ADD KEY `activity_log_log_name_index` (`log_name`);

--
-- Indexes for table `additional_document_requests`
--
ALTER TABLE `additional_document_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `additional_document_requests_subfolder_id_foreign` (`subfolder_id`),
  ADD KEY `additional_document_requests_requested_by_foreign` (`requested_by`),
  ADD KEY `additional_document_requests_assigned_to_foreign` (`assigned_to`);

--
-- Indexes for table `areas`
--
ALTER TABLE `areas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `areas_code_unique` (`code`),
  ADD KEY `areas_created_by_foreign` (`created_by`);

--
-- Indexes for table `area_user`
--
ALTER TABLE `area_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `area_user_role_unique` (`area_id`,`user_id`,`assignment_role`),
  ADD KEY `area_user_user_id_foreign` (`user_id`),
  ADD KEY `area_user_assigned_by_foreign` (`assigned_by`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audit_logs_user_id_foreign` (`user_id`);

--
-- Indexes for table `board_reviews`
--
ALTER TABLE `board_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `board_reviews_area_id_foreign` (`area_id`),
  ADD KEY `board_reviews_created_by_foreign` (`created_by`),
  ADD KEY `board_reviews_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_name_unique` (`name`),
  ADD UNIQUE KEY `categories_slug_unique` (`slug`);

--
-- Indexes for table `compliance_evidences`
--
ALTER TABLE `compliance_evidences`
  ADD PRIMARY KEY (`id`),
  ADD KEY `compliance_evidences_compliance_recommendation_id_foreign` (`compliance_recommendation_id`),
  ADD KEY `compliance_evidences_uploaded_by_foreign` (`uploaded_by`);

--
-- Indexes for table `compliance_recommendations`
--
ALTER TABLE `compliance_recommendations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `compliance_recommendations_compliance_report_id_foreign` (`compliance_report_id`);

--
-- Indexes for table `compliance_reports`
--
ALTER TABLE `compliance_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `compliance_reports_area_id_foreign` (`area_id`),
  ADD KEY `compliance_reports_created_by_foreign` (`created_by`),
  ADD KEY `compliance_reports_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `copc_files`
--
ALTER TABLE `copc_files`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `copc_files_singleton_key_unique` (`singleton_key`),
  ADD KEY `copc_files_uploaded_by_foreign` (`uploaded_by`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documents_subfolder_id_foreign` (`subfolder_id`),
  ADD KEY `documents_uploaded_by_foreign` (`uploaded_by`);

--
-- Indexes for table `document_remarks`
--
ALTER TABLE `document_remarks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `document_remarks_document_id_foreign` (`document_id`),
  ADD KEY `document_remarks_user_id_foreign` (`user_id`);

--
-- Indexes for table `document_subfolder`
--
ALTER TABLE `document_subfolder`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `document_subfolder_document_id_subfolder_id_unique` (`document_id`,`subfolder_id`),
  ADD KEY `document_subfolder_subfolder_id_foreign` (`subfolder_id`),
  ADD KEY `document_subfolder_created_by_foreign` (`created_by`);

--
-- Indexes for table `document_versions`
--
ALTER TABLE `document_versions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `document_versions_document_id_foreign` (`document_id`),
  ADD KEY `document_versions_uploaded_by_foreign` (`uploaded_by`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `parameters`
--
ALTER TABLE `parameters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parameters_area_id_foreign` (`area_id`);

--
-- Indexes for table `parameter_categories`
--
ALTER TABLE `parameter_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `param_cat_unique` (`parameter_id`,`category_id`),
  ADD KEY `parameter_categories_category_id_foreign` (`category_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `program_performance_compliance_files`
--
ALTER TABLE `program_performance_compliance_files`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `program_performance_compliance_files_area_id_unique` (`area_id`),
  ADD KEY `program_performance_compliance_files_uploaded_by_foreign` (`uploaded_by`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `subfolders`
--
ALTER TABLE `subfolders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subfolders_active_code_unique` (`parameter_category_id`,`active_code`),
  ADD KEY `subfolders_created_by_foreign` (`created_by`),
  ADD KEY `fk_subfolder_parent` (`parent_id`);

--
-- Indexes for table `supplemental_evidence_reviews`
--
ALTER TABLE `supplemental_evidence_reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `supplemental_evidence_reviews_document_id_user_id_unique` (`document_id`,`user_id`),
  ADD KEY `supplemental_evidence_reviews_user_id_foreign` (`user_id`);

--
-- Indexes for table `technical_reports`
--
ALTER TABLE `technical_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `technical_reports_area_id_foreign` (`area_id`),
  ADD KEY `technical_reports_prepared_by_foreign` (`prepared_by`),
  ADD KEY `technical_reports_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `technical_review_approvals`
--
ALTER TABLE `technical_review_approvals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `technical_review_approvals_area_id_foreign` (`area_id`),
  ADD KEY `technical_review_approvals_uploaded_by_foreign` (`uploaded_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_employee_id_unique` (`employee_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accreditor_evaluations`
--
ALTER TABLE `accreditor_evaluations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `additional_document_requests`
--
ALTER TABLE `additional_document_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `areas`
--
ALTER TABLE `areas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `area_user`
--
ALTER TABLE `area_user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `board_reviews`
--
ALTER TABLE `board_reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `compliance_evidences`
--
ALTER TABLE `compliance_evidences`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `compliance_recommendations`
--
ALTER TABLE `compliance_recommendations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `compliance_reports`
--
ALTER TABLE `compliance_reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `copc_files`
--
ALTER TABLE `copc_files`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `document_remarks`
--
ALTER TABLE `document_remarks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `document_subfolder`
--
ALTER TABLE `document_subfolder`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `document_versions`
--
ALTER TABLE `document_versions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `parameters`
--
ALTER TABLE `parameters`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `parameter_categories`
--
ALTER TABLE `parameter_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `program_performance_compliance_files`
--
ALTER TABLE `program_performance_compliance_files`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `subfolders`
--
ALTER TABLE `subfolders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `supplemental_evidence_reviews`
--
ALTER TABLE `supplemental_evidence_reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `technical_reports`
--
ALTER TABLE `technical_reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `technical_review_approvals`
--
ALTER TABLE `technical_review_approvals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accreditor_evaluations`
--
ALTER TABLE `accreditor_evaluations`
  ADD CONSTRAINT `accreditor_evaluations_subfolder_id_foreign` FOREIGN KEY (`subfolder_id`) REFERENCES `subfolders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `accreditor_evaluations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `additional_document_requests`
--
ALTER TABLE `additional_document_requests`
  ADD CONSTRAINT `additional_document_requests_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `additional_document_requests_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `additional_document_requests_subfolder_id_foreign` FOREIGN KEY (`subfolder_id`) REFERENCES `subfolders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `areas`
--
ALTER TABLE `areas`
  ADD CONSTRAINT `areas_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `area_user`
--
ALTER TABLE `area_user`
  ADD CONSTRAINT `area_user_area_id_foreign` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `area_user_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `area_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `board_reviews`
--
ALTER TABLE `board_reviews`
  ADD CONSTRAINT `board_reviews_area_id_foreign` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `board_reviews_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `board_reviews_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `compliance_evidences`
--
ALTER TABLE `compliance_evidences`
  ADD CONSTRAINT `compliance_evidences_compliance_recommendation_id_foreign` FOREIGN KEY (`compliance_recommendation_id`) REFERENCES `compliance_recommendations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `compliance_evidences_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `compliance_recommendations`
--
ALTER TABLE `compliance_recommendations`
  ADD CONSTRAINT `compliance_recommendations_compliance_report_id_foreign` FOREIGN KEY (`compliance_report_id`) REFERENCES `compliance_reports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `compliance_reports`
--
ALTER TABLE `compliance_reports`
  ADD CONSTRAINT `compliance_reports_area_id_foreign` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `compliance_reports_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `compliance_reports_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `copc_files`
--
ALTER TABLE `copc_files`
  ADD CONSTRAINT `copc_files_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_subfolder_id_foreign` FOREIGN KEY (`subfolder_id`) REFERENCES `subfolders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `documents_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `document_remarks`
--
ALTER TABLE `document_remarks`
  ADD CONSTRAINT `document_remarks_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `document_remarks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_subfolder`
--
ALTER TABLE `document_subfolder`
  ADD CONSTRAINT `document_subfolder_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `document_subfolder_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `document_subfolder_subfolder_id_foreign` FOREIGN KEY (`subfolder_id`) REFERENCES `subfolders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_versions`
--
ALTER TABLE `document_versions`
  ADD CONSTRAINT `document_versions_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `document_versions_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `parameters`
--
ALTER TABLE `parameters`
  ADD CONSTRAINT `parameters_area_id_foreign` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `parameter_categories`
--
ALTER TABLE `parameter_categories`
  ADD CONSTRAINT `parameter_categories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `parameter_categories_parameter_id_foreign` FOREIGN KEY (`parameter_id`) REFERENCES `parameters` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `program_performance_compliance_files`
--
ALTER TABLE `program_performance_compliance_files`
  ADD CONSTRAINT `program_performance_compliance_files_area_id_foreign` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `program_performance_compliance_files_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subfolders`
--
ALTER TABLE `subfolders`
  ADD CONSTRAINT `fk_subfolder_parent` FOREIGN KEY (`parent_id`) REFERENCES `subfolders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subfolders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `subfolders_parameter_category_id_foreign` FOREIGN KEY (`parameter_category_id`) REFERENCES `parameter_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `supplemental_evidence_reviews`
--
ALTER TABLE `supplemental_evidence_reviews`
  ADD CONSTRAINT `supplemental_evidence_reviews_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `supplemental_evidence_reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `technical_reports`
--
ALTER TABLE `technical_reports`
  ADD CONSTRAINT `technical_reports_area_id_foreign` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `technical_reports_prepared_by_foreign` FOREIGN KEY (`prepared_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `technical_reports_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `technical_review_approvals`
--
ALTER TABLE `technical_review_approvals`
  ADD CONSTRAINT `technical_review_approvals_area_id_foreign` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `technical_review_approvals_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
