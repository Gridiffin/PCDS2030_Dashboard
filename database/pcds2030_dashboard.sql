-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 19, 2025 at 02:23 AM
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
-- Database: `pcds2030_dashboard`
--

-- --------------------------------------------------------

--
-- Table structure for table `agencies`
--

CREATE TABLE `agencies` (
  `AgencyID` int(10) NOT NULL,
  `AgencyName` varchar(255) NOT NULL,
  `Description` varchar(255) DEFAULT NULL,
  `SectorID` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `agencies`
--

INSERT INTO `agencies` (`AgencyID`, `AgencyName`, `Description`, `SectorID`) VALUES
(1, 'Main Agency', 'Default Agency Description', NULL),
(2, 'Forestry Department', 'National department responsible for managing forest resources', 1),
(3, 'Department of Agriculture', 'Agency responsible for agricultural development and food security', 2),
(4, 'Land Development Authority', 'Authority for land use planning and management', 3),
(5, 'Water Resources Management', 'Agency for water conservation and management', 4),
(6, 'Biodiversity Conservation Agency', 'Responsible for conservation of flora and fauna', 5);

-- --------------------------------------------------------

--
-- Table structure for table `custommetrics`
--

CREATE TABLE `custommetrics` (
  `MetricID` int(10) NOT NULL,
  `AgencyID` int(10) DEFAULT NULL,
  `MetricName` varchar(255) NOT NULL,
  `MetricKey` varchar(100) NOT NULL,
  `DataType` varchar(50) NOT NULL,
  `Unit` varchar(50) DEFAULT NULL,
  `IsRequired` tinyint(1) DEFAULT 0,
  `Description` text DEFAULT NULL,
  `SortOrder` int(3) DEFAULT 0,
  `DateCreated` timestamp NOT NULL DEFAULT current_timestamp(),
  `SectorID` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `custommetrics`
--

INSERT INTO `custommetrics` (`MetricID`, `AgencyID`, `MetricName`, `MetricKey`, `DataType`, `Unit`, `IsRequired`, `Description`, `SortOrder`, `DateCreated`, `SectorID`) VALUES
(6, 1, 'Timber Export Value', 'timber_export_value', 'currency', 'USD', 1, 'Total value of timber exports for the reporting period', 0, '2025-03-19 00:58:02', 1),
(7, 1, 'Reforestation Area', 'reforestation_area', 'number', 'hectares', 1, 'Total area reforested during the reporting period', 0, '2025-03-19 00:58:02', 1),
(8, 1, 'Sustainable Forestry Compliance', 'sustainable_forestry_compliance', 'percentage', '%', 0, 'Percentage of logging operations compliant with sustainable forestry practices', 0, '2025-03-19 00:58:02', 1),
(9, 2, 'Crop Production Volume', 'crop_production_volume', 'number', 'tonnes', 1, 'Total crop production for the reporting period', 0, '2025-03-19 00:58:02', 2),
(10, 2, 'Agricultural Export Value', 'agricultural_export_value', 'currency', 'USD', 1, 'Value of agricultural exports for the reporting period', 0, '2025-03-19 00:58:02', 2),
(11, 2, 'Sustainable Farming Adoption', 'sustainable_farming_adoption', 'percentage', '%', 0, 'Percentage of farmers adopting sustainable farming practices', 0, '2025-03-19 00:58:02', 2),
(12, 3, 'Land Allocated for Development', 'land_allocated_development', 'number', 'hectares', 1, 'Total land area allocated for development projects', 0, '2025-03-19 00:58:02', 3),
(13, 3, 'Development Permits Issued', 'development_permits', 'number', 'permits', 1, 'Number of development permits issued during the reporting period', 0, '2025-03-19 00:58:02', 3),
(14, 3, 'Green Space Preservation', 'green_space_preservation', 'percentage', '%', 0, 'Percentage of development areas allocated to green spaces', 0, '2025-03-19 00:58:02', 3),
(15, 4, 'Water Quality Index', 'water_quality_index', 'number', 'index value', 1, 'Measured water quality index for key waterways', 0, '2025-03-19 00:58:02', 4),
(16, 4, 'Water Conservation Projects', 'water_conservation_projects', 'number', 'projects', 0, 'Number of active water conservation projects', 0, '2025-03-19 00:58:02', 4),
(17, 4, 'Sustainable Water Usage', 'sustainable_water_usage', 'percentage', '%', 1, 'Percentage of water usage meeting sustainability targets', 0, '2025-03-19 00:58:02', 4),
(18, 5, 'Protected Species Count', 'protected_species_count', 'number', 'species', 1, 'Number of protected species under conservation programs', 0, '2025-03-19 00:58:02', 5),
(19, 5, 'Conservation Area', 'conservation_area', 'number', 'hectares', 1, 'Total area under conservation management', 0, '2025-03-19 00:58:02', 5),
(20, 5, 'Biodiversity Index', 'biodiversity_index', 'number', 'index value', 0, 'Measured biodiversity index for protected areas', 0, '2025-03-19 00:58:02', 5);

-- --------------------------------------------------------

--
-- Table structure for table `generatedreports`
--

CREATE TABLE `generatedreports` (
  `GeneratedReportID` int(10) NOT NULL,
  `Quarter` int(10) DEFAULT NULL,
  `GenerationDate` date DEFAULT NULL,
  `FilePath` varchar(255) DEFAULT NULL,
  `Status` varchar(255) DEFAULT NULL,
  `AgencyID` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`log_id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `timestamp`) VALUES
(1, 2, 'logout', 'auth', NULL, NULL, '::1', '2025-03-14 03:41:45'),
(2, 1, 'login', 'auth', NULL, NULL, '::1', '2025-03-14 03:45:01'),
(3, 1, 'login', 'auth', NULL, NULL, '::1', '2025-03-14 03:45:34'),
(4, 1, 'logout', 'auth', NULL, NULL, '::1', '2025-03-14 03:45:37'),
(5, 2, 'login', 'auth', NULL, NULL, '::1', '2025-03-14 03:45:43'),
(6, 1, 'login', 'auth', NULL, NULL, '::1', '2025-03-14 04:04:29'),
(7, 1, 'logout', 'auth', NULL, NULL, '::1', '2025-03-14 04:04:36'),
(8, 1, 'login', 'auth', NULL, NULL, '::1', '2025-03-14 04:09:30'),
(9, 1, 'create', 'user', 3, 'Created user: user 1', '::1', '2025-03-14 07:03:04'),
(10, 1, 'create', 'user', 4, 'Created user: admin 1', '::1', '2025-03-14 07:07:25'),
(11, 1, 'delete_user', 'user', 4, 'Deleted user: admin 1 (ID: 4)', '::1', '2025-03-14 07:14:33'),
(12, 1, 'delete_user', 'user', 3, 'Deleted user: user 1 (ID: 3)', '::1', '2025-03-14 07:14:58'),
(13, 1, 'create', 'user', 5, 'Created user: milize', '::1', '2025-03-14 07:19:00'),
(14, 1, 'delete_user', 'user', 5, 'Deleted user: milize (ID: 5)', '::1', '2025-03-14 07:19:07'),
(15, 1, 'create', 'user', 6, 'Created user: user 1', '::1', '2025-03-14 07:22:09'),
(16, 1, 'delete_user', 'user', 6, 'Deleted user: user 1 (ID: 6)', '::1', '2025-03-14 07:22:15'),
(17, 1, 'create', 'user', 7, 'Created user: admin1', '::1', '2025-03-14 07:22:34'),
(18, 1, 'delete_user', 'user', 7, 'Deleted user: admin1 (ID: 7)', '::1', '2025-03-14 07:23:13'),
(19, 1, 'create', 'user', 8, 'Created user: admin 1', '::1', '2025-03-14 07:25:01'),
(20, 1, 'delete_user', 'user', 8, 'Deleted user: admin 1 (ID: 8)', '::1', '2025-03-14 07:25:06'),
(21, 1, 'logout', 'auth', NULL, NULL, '::1', '2025-03-14 07:25:30'),
(22, 2, 'login', 'auth', NULL, NULL, '::1', '2025-03-14 07:25:38'),
(23, 2, 'logout', 'auth', NULL, NULL, '::1', '2025-03-14 07:31:35'),
(24, 1, 'login', 'auth', NULL, NULL, '::1', '2025-03-14 07:31:47'),
(25, 1, 'logout', 'auth', NULL, NULL, '::1', '2025-03-14 07:32:07'),
(26, 1, 'login', 'auth', NULL, NULL, '::1', '2025-03-14 07:32:12'),
(27, 1, 'create', 'user', 9, 'Created user: admin 1', '::1', '2025-03-14 07:32:41'),
(28, 1, 'delete_user', 'user', 9, 'Deleted user: admin 1 (ID: 9)', '::1', '2025-03-14 07:32:44'),
(29, 1, 'logout', 'auth', NULL, NULL, '::1', '2025-03-14 07:32:50'),
(30, 2, 'login', 'auth', NULL, NULL, '::1', '2025-03-14 07:32:59'),
(31, 1, 'login', 'auth', NULL, NULL, '::1', '2025-03-14 08:08:13'),
(32, 1, 'logout', 'auth', NULL, NULL, '::1', '2025-03-14 08:08:23'),
(33, 1, 'login', 'auth', NULL, NULL, '::1', '2025-03-14 08:08:29'),
(34, 1, 'create', 'user', 10, 'Created user: admin 1', '::1', '2025-03-14 08:08:50'),
(35, 1, 'delete_user', 'user', 10, 'Deleted user: admin 1 (ID: 10)', '::1', '2025-03-14 08:08:54'),
(36, 1, 'logout', 'auth', NULL, NULL, '::1', '2025-03-14 08:09:13'),
(37, 2, 'login', 'auth', NULL, NULL, '::1', '2025-03-14 08:09:22'),
(38, 2, 'login', NULL, NULL, NULL, '::1', '2025-03-16 13:18:59'),
(39, 1, 'login', NULL, NULL, NULL, '::1', '2025-03-16 13:19:15'),
(40, 2, 'login', NULL, NULL, NULL, '::1', '2025-03-16 13:19:48'),
(41, 1, 'login', NULL, NULL, NULL, '::1', '2025-03-16 13:23:16'),
(42, 2, 'login', NULL, NULL, NULL, '::1', '2025-03-16 13:43:00'),
(43, 1, 'login', NULL, NULL, NULL, '::1', '2025-03-16 13:43:09'),
(44, 1, 'login', NULL, NULL, NULL, '::1', '2025-03-16 13:43:48'),
(45, 2, 'login', NULL, NULL, NULL, '::1', '2025-03-16 13:55:41'),
(46, 1, 'login', NULL, NULL, NULL, '::1', '2025-03-16 13:58:36'),
(47, 2, 'login', NULL, NULL, NULL, '::1', '2025-03-16 14:00:01'),
(48, 2, 'login', NULL, NULL, NULL, '::1', '2025-03-16 14:18:52'),
(49, 1, 'login', NULL, NULL, NULL, '::1', '2025-03-17 00:39:53'),
(50, 1, 'login', NULL, NULL, NULL, '::1', '2025-03-17 00:44:28'),
(51, 2, 'login', NULL, NULL, NULL, '::1', '2025-03-17 01:22:02'),
(52, 1, 'login', NULL, NULL, NULL, '::1', '2025-03-17 01:24:10'),
(53, 1, 'create', 'user', 14, 'Created user: user 1', '::1', '2025-03-17 01:51:53'),
(54, 1, 'create', 'user', 15, 'Created user: user 2', '::1', '2025-03-17 01:58:29'),
(55, 1, 'delete', 'user', 15, 'Deleted user: user 2', '::1', '2025-03-17 02:04:12'),
(56, 1, 'create', 'user', 16, 'Created user: admin 1', '::1', '2025-03-17 02:04:34'),
(57, 1, 'delete', 'user', 16, 'Deleted user: admin 1', '::1', '2025-03-17 02:04:40'),
(58, 1, 'delete', 'user', 14, 'Deleted user: user 1', '::1', '2025-03-17 02:04:43'),
(59, 1, 'create', 'user', 17, 'Created user: user1', '::1', '2025-03-17 02:11:36'),
(60, 17, 'login', NULL, NULL, NULL, '::1', '2025-03-17 02:11:49'),
(61, 1, 'login', NULL, NULL, NULL, '::1', '2025-03-17 02:24:38'),
(62, 2, 'login', NULL, NULL, NULL, '::1', '2025-03-17 02:25:11'),
(63, 2, 'login', NULL, NULL, NULL, '::1', '2025-03-17 02:27:49'),
(64, 2, 'login', NULL, NULL, NULL, '::1', '2025-03-17 02:28:23'),
(65, 2, 'login', NULL, NULL, NULL, '::1', '2025-03-17 02:28:59'),
(66, 2, 'login', NULL, NULL, NULL, '::1', '2025-03-17 02:29:57'),
(67, 2, 'login', NULL, NULL, NULL, '::1', '2025-03-17 02:31:05'),
(68, 2, 'login', NULL, NULL, NULL, '::1', '2025-03-17 02:31:31'),
(69, 1, 'login', NULL, NULL, NULL, '::1', '2025-03-17 02:38:39'),
(70, 1, 'login', NULL, NULL, NULL, '::1', '2025-03-17 02:43:17'),
(71, 1, 'login', NULL, NULL, NULL, '::1', '2025-03-17 02:54:41'),
(72, 2, 'login', NULL, NULL, NULL, '::1', '2025-03-17 03:08:25'),
(73, 2, 'submit_metric', 'metric', 1, 'Metric data for default - p1 details (QQ4 2024)', '::1', '2025-03-17 05:49:28'),
(74, 2, 'login', NULL, NULL, NULL, '::1', '2025-03-17 05:57:13'),
(75, 2, 'submit_metric', 'metric', 2, 'Metric data for governance - p2 details (QQ1 2025)', '::1', '2025-03-17 06:15:18'),
(76, 2, 'draft_metric', 'metric', 3, 'Metric data for governance -  (Q )', '::1', '2025-03-17 06:16:48'),
(77, 2, 'draft_metric', 'metric', 4, 'Metric data for governance - abc (QQ1 2024)', '::1', '2025-03-17 06:17:11'),
(78, 2, 'delete_metric', 'metric', 2, 'Deleted submission ID: 2', '::1', '2025-03-17 06:32:07'),
(79, 2, 'delete_metric', 'metric', 3, 'Deleted submission ID: 3', '::1', '2025-03-17 06:32:26'),
(80, 2, 'delete_metric', 'metric', 4, 'Deleted submission ID: 4', '::1', '2025-03-17 06:39:29'),
(81, 2, 'delete_metric', 'metric', 1, 'Deleted submission ID: 1', '::1', '2025-03-17 06:41:48'),
(82, 2, 'draft_metric', 'metric', 5, 'Metric data for governance - p1 (QQ1 2024)', '::1', '2025-03-17 06:57:53'),
(83, 2, 'draft_metric', 'metric', 6, 'Metric data for governance -  (QQ1 2024)', '::1', '2025-03-17 06:58:04'),
(84, 2, 'delete_metric', 'metric', 6, 'Deleted submission ID: 6', '::1', '2025-03-17 06:58:10'),
(85, 2, 'delete_metric', 'metric', 5, 'Deleted submission ID: 5', '::1', '2025-03-17 06:59:55'),
(86, 2, 'draft_metric', 'metric', 7, 'Metric data for governance - p1 (QQ1 2024)', '::1', '2025-03-17 07:04:39'),
(87, 2, 'draft_metric', 'metric', 8, 'Metric data for governance -  (QQ1 2024)', '::1', '2025-03-17 07:12:21'),
(88, 2, 'draft_metric', 'metric', 9, 'Metric data for governance - - Cumulative 7 million trees planted (20,000ha are (QQ4 2024)', '::1', '2025-03-17 07:14:31'),
(89, 2, 'submit_metric', 'metric', 10, 'Metric data for governance - p1 (QQ2 2024)', '::1', '2025-03-17 07:17:54'),
(90, 2, 'submit_metric', 'metric', 11, 'Metric data for governance - p1 (QQ1 2024)', '::1', '2025-03-17 07:18:20'),
(91, 2, 'submit_metric', 'metric', 12, 'Metric data for governance - p2 (QQ1 2024)', '::1', '2025-03-17 07:26:13'),
(92, 2, 'draft_metric', 'metric', 13, 'Metric data for governance - abc (QQ4 2024)', '::1', '2025-03-17 07:31:15'),
(93, 2, 'delete_metric', 'metric', 8, 'Deleted submission ID: 8', '::1', '2025-03-17 07:37:51'),
(94, 2, 'delete_metric', 'metric', 9, 'Deleted submission ID: 9', '::1', '2025-03-17 07:37:52'),
(95, 2, 'delete_metric', 'metric', 13, 'Deleted submission ID: 13', '::1', '2025-03-17 07:37:54'),
(96, 2, 'delete_metric', 'metric', 7, 'Deleted submission ID: 7', '::1', '2025-03-17 07:37:55'),
(97, 2, 'submit_metric', 'metric', 14, 'Metric data for governance - abc (QQ1 2024)', '::1', '2025-03-17 07:38:33'),
(98, 2, 'draft_metric', 'metric', 15, 'Metric data for governance - abc (QQ1 2024)', '::1', '2025-03-17 07:39:08'),
(99, 2, 'submit_metric', 'metric', 16, 'Metric data for governance - abc (QQ1 2024)', '::1', '2025-03-17 07:39:28'),
(100, 2, 'delete_metric', 'metric', 15, 'Deleted submission ID: 15', '::1', '2025-03-17 07:40:18'),
(101, 2, 'delete_metric', 'metric', 16, 'Deleted submission ID: 16', '::1', '2025-03-17 07:40:28'),
(102, 2, 'submit_metric', 'metric', 17, 'Metric data for governance - dasda (QQ1 2024)', '::1', '2025-03-17 07:56:54'),
(103, 2, 'delete_metric', 'metric', 17, 'Deleted submission ID: 17', '::1', '2025-03-17 07:57:01'),
(104, 2, 'delete_metric', 'metric', 10, 'Deleted submission ID: 10', '::1', '2025-03-17 07:57:02'),
(105, 2, 'delete_metric', 'metric', 14, 'Deleted submission ID: 14', '::1', '2025-03-17 07:57:04'),
(106, 2, 'delete_metric', 'metric', 12, 'Deleted submission ID: 12', '::1', '2025-03-17 07:57:05'),
(107, 2, 'delete_metric', 'metric', 11, 'Deleted submission ID: 11', '::1', '2025-03-17 07:57:06'),
(108, 2, 'draft_metric', 'metric', 18, 'Metric data for governance -  (Q 2024)', '::1', '2025-03-17 07:57:27'),
(109, 2, 'submit_metric', 'metric', 19, 'Metric data for governance - dsadsa (QQ1 2024)', '::1', '2025-03-17 07:57:44'),
(110, 2, 'delete_metric', 'metric', 19, 'Deleted submission ID: 19', '::1', '2025-03-17 08:08:46'),
(111, 1, 'login', NULL, NULL, NULL, '::1', '2025-03-18 00:04:25'),
(112, 1, 'login', NULL, NULL, NULL, '::1', '2025-03-18 00:42:57'),
(113, 1, 'login', NULL, NULL, NULL, '::1', '2025-03-18 00:48:20'),
(114, 1, 'login', NULL, NULL, NULL, '::1', '2025-03-18 01:26:50'),
(115, 2, 'login', NULL, NULL, NULL, '::1', '2025-03-18 01:38:20'),
(116, 2, 'submit_metric', 'metric', 23, 'Metric data for governance - afas (QQ3 2026)', '::1', '2025-03-18 02:17:01'),
(117, 1, 'login', NULL, NULL, NULL, '::1', '2025-03-18 02:43:43'),
(118, 2, 'login', NULL, NULL, NULL, '::1', '2025-03-18 02:49:34'),
(119, 2, 'login', NULL, NULL, NULL, '::1', '2025-03-18 02:58:35'),
(120, 2, 'submit_metric', 'metric', 24, 'Metric data for governance - ada (QQ1 2025)', '::1', '2025-03-18 05:44:35'),
(121, 2, 'submit_metrics_report', 'metrics_report', 25, 'Custom metrics report for Q1 2024', '::1', '2025-03-18 05:45:11'),
(122, 1, 'submit_metrics_report', 'metrics_report', 26, 'Custom metrics report for Q1 2024', '::1', '2025-03-18 06:12:57'),
(123, 1, 'submit_metrics_report', 'metrics_report', 27, 'Custom metrics report for Q1 2025', '::1', '2025-03-18 06:13:29'),
(124, 2, 'submit_metrics_report', 'metrics_report', 28, 'Custom metrics report for Q1 2024', '::1', '2025-03-18 06:14:07'),
(125, 2, 'submit_metrics_report', 'metrics_report', 29, 'Custom metrics report for Q1 2024', '::1', '2025-03-18 06:22:24'),
(126, 2, 'submit_metrics_report', 'metrics_report', 30, 'Custom metrics report for Q2 2024', '::1', '2025-03-18 06:25:52'),
(127, 2, 'submit_metrics_report', 'metrics_report', 31, 'Custom metrics report for Q2 2027', '::1', '2025-03-18 06:32:53'),
(128, 2, 'delete_metrics_report', 'metrics_report', 31, 'Deleted custom metrics report for Q2 2027', '::1', '2025-03-18 06:36:06'),
(129, 2, 'delete_metrics_report', 'metrics_report', 27, 'Deleted custom metrics report for Q1 2025', '::1', '2025-03-18 06:36:07'),
(130, 2, 'submit_metrics_report', 'metrics_report', 32, 'Custom metrics report for Q2 2028', '::1', '2025-03-18 06:36:22'),
(131, 2, 'delete_metrics_report', 'metrics_report', 26, 'Deleted custom metrics report for Q1 2024', '::1', '2025-03-18 06:49:58'),
(132, 2, 'submit_single_metric_report', 'single_metric_report', 33, 'Single metric report for Timber Export Value - Q2 2024', '::1', '2025-03-18 07:27:25'),
(133, 2, 'submit_single_metric_report', 'single_metric_report', 34, 'Single metric report for metric3 - Q2 2026', '::1', '2025-03-18 07:28:09'),
(134, 2, 'login', NULL, NULL, NULL, '::1', '2025-03-18 08:07:56'),
(135, 1, 'login', NULL, NULL, NULL, '::1', '2025-03-18 08:09:14'),
(136, 2, 'login', NULL, NULL, NULL, '::1', '2025-03-18 08:10:03'),
(137, 2, 'login', NULL, NULL, NULL, '127.0.0.1', '2025-03-19 00:01:48'),
(138, 2, 'submit_metric', 'metric', 35, 'Metric data for governance - ihui (QQ3 2030)', '127.0.0.1', '2025-03-19 00:05:02'),
(139, 1, 'login', NULL, NULL, NULL, '127.0.0.1', '2025-03-19 00:10:51'),
(140, 2, 'login', NULL, NULL, NULL, '127.0.0.1', '2025-03-19 00:12:07'),
(141, 2, 'login', NULL, NULL, NULL, '::1', '2025-03-19 00:21:47'),
(142, 2, 'login', NULL, NULL, NULL, '127.0.0.1', '2025-03-19 00:22:30'),
(143, 2, 'login', NULL, NULL, NULL, '127.0.0.1', '2025-03-19 00:24:07'),
(144, 2, 'login', NULL, NULL, NULL, '127.0.0.1', '2025-03-19 00:24:40');

-- --------------------------------------------------------

--
-- Table structure for table `metrics`
--

CREATE TABLE `metrics` (
  `MetricID` int(10) NOT NULL,
  `MetricType` varchar(255) DEFAULT NULL,
  `Data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`Data`)),
  `Quarter` varchar(10) DEFAULT NULL,
  `Year` year(4) DEFAULT NULL,
  `AgencyID` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `metrics`
--

INSERT INTO `metrics` (`MetricID`, `MetricType`, `Data`, `Quarter`, `Year`, `AgencyID`) VALUES
(21, 'test_metric', '{\"programId\":\"test_prog_1742263588\",\"programName\":\"Test Program\",\"target\":{\"indicator\":\"Test Indicator\",\"description\":\"Test Description\"},\"status\":\"draft\",\"lastUpdated\":\"2025-03-18 03:06:28\",\"submittedBy\":\"Debug Tool\",\"userId\":0}', 'Q1', '2025', 1),
(22, 'test_metric', '{\"programId\":\"test_prog_1742263591\",\"programName\":\"Test Program\",\"target\":{\"indicator\":\"Test Indicator\",\"description\":\"Test Description\"},\"status\":\"draft\",\"lastUpdated\":\"2025-03-18 03:06:31\",\"submittedBy\":\"Debug Tool\",\"userId\":0}', 'Q1', '2025', 1),
(23, 'governance', '{\"programId\":\"new_1742264221885\",\"programName\":\"afas\",\"programDescription\":\"afa\",\"target\":{\"indicator\":\"afas\",\"value\":\"\",\"unit\":\"\",\"deadline\":\"2025-03-11\",\"description\":\"afas\"},\"status\":\"submitted\",\"lastUpdated\":\"2025-03-18 03:17:01\",\"submittedBy\":\"user\",\"userId\":2}', 'Q3', '2026', 1),
(24, 'Government', '{\"programId\":\"new_1742276675807\",\"programName\":\"adas\",\"programDescription\":\"asda\",\"target\":{\"indicator\":\"ada\",\"value\":\"\",\"unit\":\"\",\"deadline\":\"2025-03-11\",\"description\":\"ada\"},\"status\":\"submitted\",\"customMetrics\":null,\"lastUpdated\":\"2025-03-18 06:44:35\",\"submittedBy\":\"user\",\"userId\":2}', 'Q1', '2025', 1),
(25, 'custom_metrics_report', '{\"year\":\"2024\",\"quarter\":\"Q1\",\"reportDate\":\"2025-03-18\",\"notes\":\"\",\"metricsData\":{\"timber_export_value\":\"700\"},\"isDraft\":false,\"lastUpdated\":\"2025-03-18 06:45:11\",\"submittedBy\":\"user\",\"userId\":2}', 'Q1', '2024', 1),
(28, 'custom_metrics_report', '{\"year\":\"2024\",\"quarter\":\"Q1\",\"reportDate\":\"2025-03-18\",\"notes\":\"abc\",\"metricsData\":{\"timber_export_value\":\"900\"},\"isDraft\":false,\"lastUpdated\":\"2025-03-18 07:14:07\",\"submittedBy\":\"user\",\"userId\":2}', 'Q1', '2024', 1),
(29, 'custom_metrics_report', '{\"year\":\"2024\",\"quarter\":\"Q1\",\"reportDate\":\"2025-03-18\",\"notes\":\"abc\",\"metricsData\":{\"timber_export_value\":\"900\"},\"isDraft\":false,\"lastUpdated\":\"2025-03-18 07:22:24\",\"submittedBy\":\"user\",\"userId\":2}', 'Q1', '2024', 1),
(30, 'custom_metrics_report', '{\"year\":\"2024\",\"quarter\":\"Q2\",\"reportDate\":\"2025-03-18\",\"notes\":\"abc\",\"metricsData\":{\"timber_export_value\":\"900\"},\"isDraft\":false,\"lastUpdated\":\"2025-03-18 07:25:52\",\"submittedBy\":\"user\",\"userId\":2}', 'Q2', '2024', 1),
(32, 'custom_metrics_report', '{\"year\":\"2028\",\"quarter\":\"Q2\",\"reportDate\":\"2025-03-18\",\"notes\":\"2323\",\"metricsData\":{\"timber_export_value\":\"3232\"},\"isDraft\":false,\"lastUpdated\":\"2025-03-18 07:36:22\",\"submittedBy\":\"user\",\"userId\":2}', 'Q2', '2028', 1),
(33, 'single_custom_metric', '{\"year\":\"2024\",\"quarter\":\"Q2\",\"reportDate\":\"2025-03-18\",\"notes\":\"assfsa\",\"metricsData\":{\"timber_export_value\":\"63763\"},\"isDraft\":false,\"lastUpdated\":\"2025-03-18 08:27:25\",\"submittedBy\":\"user\",\"userId\":2,\"metricId\":\"3\",\"metricName\":\"Timber Export Value\"}', 'Q2', '2024', 1),
(34, 'single_custom_metric', '{\"year\":\"2026\",\"quarter\":\"Q2\",\"reportDate\":\"2025-03-18\",\"notes\":\"afhfbaf\",\"metricsData\":{\"metric3\":\"89\"},\"isDraft\":false,\"lastUpdated\":\"2025-03-18 08:28:09\",\"submittedBy\":\"user\",\"userId\":2,\"metricId\":\"5\",\"metricName\":\"metric3\"}', 'Q2', '2026', 1),
(35, 'Government', '{\"programId\":\"new_1742342702231\",\"programName\":\"yiu\",\"programDescription\":\"yui\",\"target\":{\"indicator\":\"ihui\",\"value\":\"\",\"unit\":\"\",\"deadline\":\"2025-03-12\",\"description\":\"ihui\"},\"status\":\"submitted\",\"customMetrics\":null,\"lastUpdated\":\"2025-03-19 01:05:02\",\"submittedBy\":\"user\",\"userId\":2}', 'Q3', '2030', 1);

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `ReportID` int(10) NOT NULL,
  `AgencyID` int(10) DEFAULT NULL,
  `SubmittedByUserID` int(10) DEFAULT NULL,
  `Quarter` int(10) DEFAULT NULL,
  `SubmissionDate` date DEFAULT NULL,
  `Status` varchar(255) DEFAULT NULL,
  `Metrics` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`Metrics`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `RoleID` int(10) NOT NULL,
  `RoleName` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`RoleID`, `RoleName`) VALUES
(1, 'Admin'),
(2, 'User');

-- --------------------------------------------------------

--
-- Table structure for table `sectors`
--

CREATE TABLE `sectors` (
  `SectorID` int(10) NOT NULL,
  `SectorName` varchar(100) NOT NULL,
  `Description` text DEFAULT NULL,
  `SortOrder` int(3) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sectors`
--

INSERT INTO `sectors` (`SectorID`, `SectorName`, `Description`, `SortOrder`) VALUES
(1, 'Forestry', 'Forest management and timber industry', 1),
(2, 'Agriculture', 'Agricultural activities and farming', 2),
(3, 'Land Management', 'Land use planning and management', 3),
(4, 'Water Resources', 'Water conservation and management', 4),
(5, 'Biodiversity', 'Protection of species and ecosystems', 5);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `UserID` int(10) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `RoleID` int(10) DEFAULT NULL,
  `AgencyID` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`UserID`, `username`, `password`, `RoleID`, `AgencyID`) VALUES
(1, 'admin', '$2y$10$aA2mCgXBnKW4/J1W0pZXNe/Gi66v/QDa3Mm8GCC9QMPrT1ptQx1Vm', 1, 1),
(2, 'user', '$2y$10$FzzmROxI75bjd9bM/D60aeFG/TQXFZOIfYmD3f2k6vnIBlOWEPgqK', 2, 1),
(17, 'user1', '$2y$10$TcCqfJG/I1NMDm5cx4jj.uKo8W3legQFWEI41HZj5SZ6qxNqmoxsa', 2, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agencies`
--
ALTER TABLE `agencies`
  ADD PRIMARY KEY (`AgencyID`),
  ADD KEY `idx_agency_sector` (`SectorID`);

--
-- Indexes for table `custommetrics`
--
ALTER TABLE `custommetrics`
  ADD PRIMARY KEY (`MetricID`),
  ADD KEY `AgencyID` (`AgencyID`),
  ADD KEY `idx_custom_metrics_sector` (`SectorID`);

--
-- Indexes for table `generatedreports`
--
ALTER TABLE `generatedreports`
  ADD PRIMARY KEY (`GeneratedReportID`),
  ADD KEY `AgencyID` (`AgencyID`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `metrics`
--
ALTER TABLE `metrics`
  ADD PRIMARY KEY (`MetricID`),
  ADD KEY `AgencyID` (`AgencyID`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`ReportID`),
  ADD KEY `AgencyID` (`AgencyID`),
  ADD KEY `SubmittedByUserID` (`SubmittedByUserID`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`RoleID`),
  ADD UNIQUE KEY `RoleName` (`RoleName`);

--
-- Indexes for table `sectors`
--
ALTER TABLE `sectors`
  ADD PRIMARY KEY (`SectorID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `RoleID` (`RoleID`),
  ADD KEY `AgencyID` (`AgencyID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agencies`
--
ALTER TABLE `agencies`
  MODIFY `AgencyID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `custommetrics`
--
ALTER TABLE `custommetrics`
  MODIFY `MetricID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `generatedreports`
--
ALTER TABLE `generatedreports`
  MODIFY `GeneratedReportID` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;

--
-- AUTO_INCREMENT for table `metrics`
--
ALTER TABLE `metrics`
  MODIFY `MetricID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `ReportID` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `RoleID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sectors`
--
ALTER TABLE `sectors`
  MODIFY `SectorID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `agencies`
--
ALTER TABLE `agencies`
  ADD CONSTRAINT `fk_agency_sector` FOREIGN KEY (`SectorID`) REFERENCES `sectors` (`SectorID`) ON DELETE SET NULL;

--
-- Constraints for table `custommetrics`
--
ALTER TABLE `custommetrics`
  ADD CONSTRAINT `custommetrics_ibfk_1` FOREIGN KEY (`AgencyID`) REFERENCES `agencies` (`AgencyID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sector_id` FOREIGN KEY (`SectorID`) REFERENCES `sectors` (`SectorID`) ON DELETE SET NULL;

--
-- Constraints for table `generatedreports`
--
ALTER TABLE `generatedreports`
  ADD CONSTRAINT `generatedreports_ibfk_1` FOREIGN KEY (`AgencyID`) REFERENCES `agencies` (`AgencyID`) ON DELETE SET NULL;

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`UserID`) ON DELETE SET NULL;

--
-- Constraints for table `metrics`
--
ALTER TABLE `metrics`
  ADD CONSTRAINT `metrics_ibfk_1` FOREIGN KEY (`AgencyID`) REFERENCES `agencies` (`AgencyID`) ON DELETE SET NULL;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`AgencyID`) REFERENCES `agencies` (`AgencyID`) ON DELETE SET NULL,
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`SubmittedByUserID`) REFERENCES `users` (`UserID`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`RoleID`) REFERENCES `roles` (`RoleID`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`AgencyID`) REFERENCES `agencies` (`AgencyID`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
