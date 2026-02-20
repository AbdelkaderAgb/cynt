-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 12, 2026 at 11:10 AM
-- Server version: 11.4.9-MariaDB-cll-lve
-- PHP Version: 8.3.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `barqvkxs_cyn`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `action` varchar(100) NOT NULL,
  `description` text NOT NULL DEFAULT '',
  `entity_type` varchar(50) NOT NULL DEFAULT '',
  `entity_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `old_values` text NOT NULL DEFAULT '' COMMENT 'JSON of old values',
  `new_values` text NOT NULL DEFAULT '' COMMENT 'JSON of new values',
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text NOT NULL DEFAULT '',
  `session_id` varchar(255) NOT NULL DEFAULT '',
  `request_method` varchar(10) NOT NULL DEFAULT '',
  `request_url` varchar(500) NOT NULL DEFAULT '',
  `severity` enum('debug','info','warning','error','critical') DEFAULT 'info',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `api_keys`
--

CREATE TABLE `api_keys` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `api_key` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT '',
  `is_active` tinyint(1) DEFAULT 1,
  `last_used_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `api_tokens`
--

CREATE TABLE `api_tokens` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `token_name` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `abilities` text NOT NULL DEFAULT '' COMMENT 'JSON array of allowed abilities',
  `last_used_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL DEFAULT current_timestamp(),
  `revoked` tinyint(1) DEFAULT 0,
  `revoked_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

CREATE TABLE `drivers` (
  `id` int(11) UNSIGNED NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `full_name` varchar(200) GENERATED ALWAYS AS (concat(`first_name`,' ',`last_name`)) STORED,
  `email` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(20) NOT NULL,
  `mobile` varchar(20) NOT NULL DEFAULT '',
  `license_no` varchar(50) NOT NULL,
  `license_expiry` date NOT NULL,
  `license_type` varchar(20) NOT NULL DEFAULT '',
  `id_number` varchar(50) NOT NULL DEFAULT '',
  `date_of_birth` date NOT NULL DEFAULT '1970-01-01',
  `address` text NOT NULL DEFAULT '',
  `city` varchar(100) NOT NULL DEFAULT '',
  `emergency_contact` varchar(100) NOT NULL DEFAULT '',
  `emergency_phone` varchar(20) NOT NULL DEFAULT '',
  `hire_date` date NOT NULL DEFAULT '1970-01-01',
  `termination_date` date NOT NULL DEFAULT '1970-01-01',
  `status` enum('active','inactive','on_leave','suspended','terminated') DEFAULT 'active',
  `rating` decimal(2,1) DEFAULT 5.0,
  `total_trips` int(11) DEFAULT 0,
  `languages` varchar(255) NOT NULL DEFAULT '' COMMENT 'Comma-separated languages',
  `photo` varchar(255) NOT NULL DEFAULT '',
  `documents` text NOT NULL DEFAULT '' COMMENT 'JSON array of document paths',
  `notes` text NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `drivers`
--

INSERT INTO `drivers` (`id`, `first_name`, `last_name`, `email`, `phone`, `mobile`, `license_no`, `license_expiry`, `license_type`, `id_number`, `date_of_birth`, `address`, `city`, `emergency_contact`, `emergency_phone`, `hire_date`, `termination_date`, `status`, `rating`, `total_trips`, `languages`, `photo`, `documents`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'Ali', 'Yildiz', '', '+90 532 100 0001', '', 'TR-IST-2020-001', '2027-06-15', 'B', '', '1970-01-01', '', '', '', '', '2022-01-15', '1970-01-01', 'active', 5.0, 0, 'Turkish,English,Arabic', '', '', '', '2025-01-01 06:00:00', '2026-02-12 04:24:59'),
(2, 'Hasan', 'Koc', '', '+90 532 100 0002', '', 'TR-IST-2019-002', '2027-03-20', 'D1', '', '1970-01-01', '', '', '', '', '2021-06-01', '1970-01-01', 'active', 5.0, 0, 'Turkish,English', '', '', '', '2025-01-01 06:00:00', '2026-02-12 04:24:59'),
(3, 'Emre', 'Aksoy', '', '+90 532 100 0003', '', 'TR-IST-2021-003', '2028-01-10', 'B', '', '1970-01-01', '', '', '', '', '2023-03-01', '1970-01-01', 'active', 5.0, 0, 'Turkish,English,Russian', '', '', '', '2025-01-01 06:00:00', '2026-02-12 04:24:59'),
(4, 'Burak', 'Dogan', '', '+90 532 100 0004', '', 'TR-IST-2022-004', '2028-09-05', 'D1', '', '1970-01-01', '', '', '', '', '2023-09-01', '1970-01-01', 'active', 5.0, 0, 'Turkish,Arabic,French', '', '', '', '2025-02-01 06:00:00', '2026-02-12 04:24:59'),
(5, 'Osman', 'Uysal', '', '+90 532 100 0005', '', 'TR-IST-2020-005', '2027-12-01', 'B', '', '1970-01-01', '', '', '', '', '2022-07-01', '1970-01-01', 'active', 5.0, 0, 'Turkish,English', '', '', '', '2025-03-01 06:00:00', '2026-02-12 04:24:59');

-- --------------------------------------------------------

--
-- Table structure for table `email_config`
--

CREATE TABLE `email_config` (
  `id` int(11) UNSIGNED NOT NULL,
  `smtp_host` varchar(255) NOT NULL DEFAULT '',
  `smtp_port` int(11) DEFAULT 587,
  `smtp_username` varchar(255) NOT NULL DEFAULT '',
  `smtp_password` varchar(255) NOT NULL DEFAULT '',
  `from_email` varchar(255) NOT NULL DEFAULT '',
  `from_name` varchar(100) NOT NULL DEFAULT '',
  `enable_notifications` tinyint(1) DEFAULT 1,
  `enable_reminders` tinyint(1) DEFAULT 1,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hotels`
--

CREATE TABLE `hotels` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL,
  `address` varchar(500) NOT NULL DEFAULT '',
  `city` varchar(100) NOT NULL DEFAULT '',
  `country` varchar(100) NOT NULL DEFAULT 'Turkey',
  `stars` tinyint(1) NOT NULL DEFAULT 3,
  `phone` varchar(50) NOT NULL DEFAULT '',
  `email` varchar(150) NOT NULL DEFAULT '',
  `website` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL DEFAULT '',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hotels`
--

INSERT INTO `hotels` (`id`, `name`, `address`, `city`, `country`, `stars`, `phone`, `email`, `website`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Grand Hyatt Istanbul', 'Taskisla Caddesi No:1 Taksim', 'Istanbul', 'Turkiye', 5, '', '', '', '', 'active', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(2, 'Swissotel The Bosphorus', 'Visnezade Mahallesi, Acisu Sokak', 'Istanbul', 'Turkiye', 5, '', '', '', '', 'active', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(3, 'Four Seasons Sultanahmet', 'Tevkifhane Sokak No:1', 'Istanbul', 'Turkiye', 5, '', '', '', '', 'active', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(4, 'Hilton Istanbul Bomonti', 'Silahsor Caddesi No:42', 'Istanbul', 'Turkiye', 5, '', '', '', '', 'active', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(5, 'Sheraton Grand Ankara', 'Noktali Sokak, Kavaklidere', 'Ankara', 'Turkiye', 5, '', '', '', '', 'active', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(6, 'Ankara Hilton', 'Tahran Caddesi No:12', 'Ankara', 'Turkiye', 5, '', '', '', '', 'active', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(7, 'JW Marriott Ankara', 'Kizilirmak Mahallesi, Muhsin Yazicioglu', 'Ankara', 'Turkiye', 5, '', '', '', '', 'active', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(8, 'Rixos Premium Belek', 'Belek Turizm Merkezi', 'Antalya', 'Turkiye', 5, '', '', '', '', 'active', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(9, 'Titanic Deluxe Belek', 'Bogazkent Mevkii', 'Antalya', 'Turkiye', 5, '', '', '', '', 'active', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(10, 'Maxx Royal Belek', 'Acisu Mevkii Belek', 'Antalya', 'Turkiye', 5, '', '', '', '', 'active', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(11, 'IC Hotels Santai', 'Lara Turizm Yolu', 'Antalya', 'Turkiye', 5, '', '', '', '', 'active', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(12, 'Hilton Izmir', 'Gaziosmanpasa Bulvari No:7', 'Izmir', 'Turkiye', 5, '', '', '', '', 'active', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(13, 'Swissotel Grand Efes Izmir', 'Gaziosmanpasa Bulvari No:1', 'Izmir', 'Turkiye', 5, '', '', '', '', 'active', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(14, 'Wyndham Grand Izmir Ozdilek', 'Akdeniz Mahallesi, Gaziemir', 'Izmir', 'Turkiye', 5, '', '', '', '', 'active', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(15, 'Kempinski Hotel Barbaros Bay', 'Gerenkuyu Mevkii, Yaliçiftlik', 'Bodrum', 'Turkiye', 5, '', '', '', '', 'active', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(16, 'The Bodrum EDITION', 'Yalikavak Mahallesi, Comca Mevkii', 'Bodrum', 'Turkiye', 5, '', '', '', '', 'active', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(17, 'Mandarin Oriental Bodrum', 'Cennet Koyu, Gölköy', 'Bodrum', 'Turkiye', 5, '', '', '', '', 'active', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(18, 'Hilton Bodrum Turkbuku', 'Golturkbuku Mahallesi', 'Bodrum', 'Turkiye', 5, '', '', '', '', 'active', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(19, 'Rixos Premium Gocek', 'Gocek Mah. Cumhuriyet Cad.', 'Fethiye', 'Turkiye', 5, '', '', '', '', 'active', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(20, 'Hillside Beach Club', 'Kalemya Koyu', 'Fethiye', 'Turkiye', 5, '', '', '', '', 'active', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(21, 'Divan Cukurhan', 'Namik Kemal Mahallesi', 'Bursa', 'Turkiye', 4, '', '', '', '', 'active', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(22, 'Sheraton Bursa', 'Odunluk Mahallesi, Akpinar', 'Bursa', 'Turkiye', 5, '', '', '', '', 'active', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(23, 'Dedeman Palandoken', 'Palandoken Kayak Merkezi', 'Erzurum', 'Turkiye', 4, '', '', '', '', 'active', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(24, 'Renaissance Polat Erzurum', 'Palandoken Yolu', 'Erzurum', 'Turkiye', 5, '', '', '', '', 'active', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(25, 'Kaya Palazzo Golf Resort', 'Iskele Mevkii', 'Belek', 'Turkiye', 5, '', '', '', '', 'active', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(26, 'Regnum Carya Golf', 'Belek Turizm Merkezi', 'Belek', 'Turkiye', 5, '', '', '', '', 'active', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(27, 'CVK Park Bosphorus Istanbul', 'Gümüşsuyu Mahallesi', 'Istanbul', 'Turkiye', 5, '', '', '', '', 'active', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(28, 'The Ritz-Carlton Istanbul', 'Suzer Plaza, Askerocagi Cad.', 'Istanbul', 'Turkiye', 5, '', '', '', '', 'active', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(29, 'Merit Park Hotel', 'Ozanköy, Girne', 'Kyrenia', 'Turkiye', 4, '', '', '', '', 'active', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(30, 'Concorde Luxury Resort', 'Bafra Turizm Bölgesi', 'Famagusta', 'Turkiye', 5, '', '', '', '', 'active', '2026-02-12 10:00:00', '2026-02-12 10:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `hotel_rooms`
--

CREATE TABLE `hotel_rooms` (
  `id` int(11) UNSIGNED NOT NULL,
  `hotel_id` int(11) UNSIGNED NOT NULL,
  `room_type` varchar(100) NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 2,
  `price_single` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_double` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_triple` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_quad` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_child` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(3) NOT NULL DEFAULT 'USD',
  `board_type` varchar(20) NOT NULL DEFAULT 'BB',
  `season` varchar(30) NOT NULL DEFAULT 'all',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hotel_rooms`
--

INSERT INTO `hotel_rooms` (`id`, `hotel_id`, `room_type`, `capacity`, `price_single`, `price_double`, `price_triple`, `price_quad`, `price_child`, `currency`, `board_type`, `season`, `created_at`, `updated_at`) VALUES
(1, 1, 'Standard', 2, 2500.00, 3200.00, 4000.00, 4500.00, 800.00, 'TRY', 'BB', 'High', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(2, 1, 'Deluxe', 3, 3000.00, 3800.00, 4800.00, 5500.00, 900.00, 'TRY', 'HB', 'High', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(3, 2, 'Standard', 2, 2800.00, 3500.00, 4300.00, 4900.00, 850.00, 'TRY', 'BB', 'Peak', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(4, 3, 'Deluxe', 2, 4500.00, 5500.00, 6800.00, 7500.00, 1200.00, 'TRY', 'BB', 'Peak', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(5, 4, 'Standard', 2, 2000.00, 2600.00, 3200.00, 3700.00, 700.00, 'TRY', 'BB', 'Low', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(6, 5, 'Standard', 2, 1800.00, 2400.00, 3000.00, 3500.00, 650.00, 'TRY', 'BB', 'High', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(7, 6, 'Executive', 2, 2200.00, 2900.00, 3600.00, 4100.00, 750.00, 'TRY', 'HB', 'High', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(8, 7, 'Deluxe', 3, 2100.00, 2800.00, 3500.00, 4000.00, 700.00, 'TRY', 'BB', 'Peak', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(9, 8, 'Standard', 2, 3500.00, 4200.00, 5200.00, 6000.00, 1000.00, 'TRY', 'AI', 'Peak', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(10, 8, 'Family Suite', 4, 5000.00, 6000.00, 7200.00, 8500.00, 1400.00, 'TRY', 'AI', 'Peak', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(11, 9, 'Standard', 2, 3200.00, 3900.00, 4800.00, 5500.00, 950.00, 'TRY', 'AI', 'High', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(12, 10, 'Villa', 4, 8000.00, 10000.00, 12000.00, 14000.00, 2500.00, 'TRY', 'AI', 'Peak', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(13, 11, 'Standard', 2, 2800.00, 3400.00, 4200.00, 4800.00, 900.00, 'TRY', 'AI', 'High', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(14, 12, 'Standard', 2, 1900.00, 2500.00, 3100.00, 3600.00, 680.00, 'TRY', 'BB', 'High', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(15, 13, 'Deluxe', 2, 2200.00, 2900.00, 3600.00, 4200.00, 750.00, 'TRY', 'HB', 'Peak', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(16, 14, 'Standard', 2, 1700.00, 2200.00, 2800.00, 3300.00, 620.00, 'TRY', 'BB', 'Low', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(17, 15, 'Standard', 2, 4000.00, 5000.00, 6200.00, 7000.00, 1200.00, 'TRY', 'HB', 'Peak', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(18, 16, 'Deluxe', 2, 5500.00, 6800.00, 8200.00, 9500.00, 1600.00, 'TRY', 'BB', 'Peak', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(19, 17, 'Premium', 3, 6000.00, 7500.00, 9000.00, 10500.00, 1800.00, 'TRY', 'HB', 'High', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(20, 18, 'Standard', 2, 3500.00, 4300.00, 5300.00, 6100.00, 1100.00, 'TRY', 'AI', 'Peak', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(21, 19, 'Standard', 2, 3800.00, 4600.00, 5600.00, 6400.00, 1150.00, 'TRY', 'AI', 'High', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(22, 20, 'Family Room', 3, 4200.00, 5200.00, 6400.00, 7400.00, 1300.00, 'TRY', 'AI', 'Peak', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(23, 21, 'Standard', 2, 1400.00, 1800.00, 2300.00, 2700.00, 550.00, 'TRY', 'BB', 'High', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(24, 22, 'Deluxe', 2, 1600.00, 2100.00, 2700.00, 3200.00, 600.00, 'TRY', 'BB', 'Low', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(25, 23, 'Standard', 2, 1500.00, 2000.00, 2600.00, 3000.00, 580.00, 'TRY', 'FB', 'Winter', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(26, 24, 'Mountain View', 3, 1800.00, 2400.00, 3100.00, 3600.00, 680.00, 'TRY', 'HB', 'Winter', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(27, 25, 'Garden View', 2, 3300.00, 4000.00, 4900.00, 5600.00, 980.00, 'TRY', 'AI', 'High', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(28, 26, 'Standard', 2, 3600.00, 4400.00, 5400.00, 6200.00, 1080.00, 'TRY', 'AI', 'Peak', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(29, 27, 'Bosphorus View', 2, 3200.00, 4000.00, 4900.00, 5600.00, 950.00, 'TRY', 'BB', 'High', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(30, 28, 'Deluxe', 2, 5000.00, 6200.00, 7600.00, 8800.00, 1500.00, 'TRY', 'BB', 'Peak', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(31, 29, 'Standard', 2, 1300.00, 1700.00, 2200.00, 2600.00, 520.00, 'TRY', 'AI', 'High', '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(32, 30, 'Standard', 2, 2400.00, 3000.00, 3700.00, 4300.00, 820.00, 'TRY', 'AI', 'Peak', '2026-02-12 10:00:00', '2026-02-12 10:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `hotel_vouchers`
--

CREATE TABLE `hotel_vouchers` (
  `id` int(11) UNSIGNED NOT NULL,
  `voucher_no` varchar(60) NOT NULL,
  `guest_name` varchar(200) NOT NULL,
  `hotel_name` varchar(200) NOT NULL,
  `hotel_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `company_name` varchar(120) NOT NULL DEFAULT '',
  `company_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `address` varchar(255) NOT NULL DEFAULT '',
  `telephone` varchar(50) NOT NULL DEFAULT '',
  `partner_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `room_type` varchar(100) NOT NULL DEFAULT '',
  `room_count` int(11) DEFAULT 1,
  `board_type` enum('room_only','bed_breakfast','half_board','full_board','all_inclusive') DEFAULT 'bed_breakfast',
  `transfer_type` enum('without','with_transfer','airport_transfer') DEFAULT 'without',
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `nights` int(11) DEFAULT 1,
  `total_pax` int(11) DEFAULT 0,
  `adults` int(11) DEFAULT 0,
  `children` int(11) DEFAULT 0,
  `infants` int(11) DEFAULT 0,
  `confirmation_no` varchar(100) NOT NULL DEFAULT '',
  `price_per_night` decimal(10,2) DEFAULT 0.00,
  `total_price` decimal(10,2) DEFAULT 0.00,
  `currency` varchar(3) DEFAULT 'USD',
  `customers` text NOT NULL DEFAULT '',
  `special_requests` text NOT NULL DEFAULT '',
  `additional_services` text NOT NULL DEFAULT '' COMMENT 'JSON: tour/transfer as additional services',
  `status` enum('pending','confirmed','checked_in','checked_out','cancelled','no_show') DEFAULT 'pending',
  `payment_status` enum('unpaid','partial','paid','refunded') DEFAULT 'unpaid',
  `notes` text NOT NULL DEFAULT '',
  `created_by` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `updated_by` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hotel_vouchers`
--

INSERT INTO `hotel_vouchers` (`id`, `voucher_no`, `guest_name`, `hotel_name`, `hotel_id`, `company_name`, `company_id`, `address`, `telephone`, `partner_id`, `room_type`, `room_count`, `board_type`, `transfer_type`, `check_in`, `check_out`, `nights`, `total_pax`, `adults`, `children`, `infants`, `confirmation_no`, `price_per_night`, `total_price`, `currency`, `customers`, `special_requests`, `status`, `payment_status`, `notes`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'HV-2602-001', 'Mohamed Benali', 'Grand Star Hotel', 0, 'Atlas Travel Algeria', 1, '', '', 1, 'Deluxe Double', 1, '', 'without', '2026-02-01', '2026-02-04', 3, 2, 2, 0, 0, '', 85.00, 255.00, 'USD', '', '', '', 'unpaid', '', 1, 0, '2026-01-28 07:00:00', '2026-02-12 14:00:31'),
(2, 'HV-2602-002', 'Yacine Boudiaf', 'Sultanahmet Palace Hotel', 0, 'Sahara Tours DZ', 2, '', '', 2, 'Family Suite', 2, '', 'without', '2026-02-03', '2026-02-08', 5, 5, 3, 2, 0, '', 120.00, 1200.00, 'EUR', '', '', '', 'unpaid', '', 1, 0, '2026-02-01 06:00:00', '2026-02-12 14:00:31'),
(3, 'HV-2602-003', 'Nigar Mammadova', 'Taksim Deluxe Suites', 0, 'Baku Premium Travel', 3, '', '', 3, 'Standard Twin', 1, '', 'without', '2026-02-05', '2026-02-10', 5, 2, 2, 0, 0, '', 70.00, 350.00, 'USD', '', '', '', 'unpaid', '', 1, 0, '2026-02-03 08:00:00', '2026-02-12 14:00:31'),
(4, 'HV-2602-004', 'Rashad Aliyev', 'Bosphorus View Hotel', 0, 'Caspian Holidays', 4, '', '', 4, 'Sea View Suite', 2, '', 'without', '2026-02-07', '2026-02-12', 5, 4, 4, 0, 0, '', 150.00, 1500.00, 'USD', '', '', '', 'unpaid', '', 1, 0, '2026-02-05 11:00:00', '2026-02-12 14:00:31'),
(5, 'HV-2602-005', 'Khaled Mansouri', 'Grand Star Hotel', 0, 'Atlas Travel Algeria', 1, '', '', 1, 'Superior Double', 3, '', 'without', '2026-02-10', '2026-02-15', 5, 6, 4, 2, 0, '', 95.00, 1425.00, 'EUR', '', '', '', 'unpaid', '', 1, 0, '2026-02-08 07:00:00', '2026-02-12 14:00:31'),
(6, 'HV-2602-006', 'Turkan Hasanova', 'Taksim Deluxe Suites', 0, 'Baku Premium Travel', 3, '', '', 3, 'Deluxe Double', 1, '', 'without', '2026-02-14', '2026-02-18', 4, 2, 2, 0, 0, '', 85.00, 340.00, 'USD', '', '', 'confirmed', 'unpaid', '', 1, 0, '2026-02-12 07:00:00', '2026-02-12 14:00:31'),
(7, 'HV-2602-007', 'Amir Bouchama', 'Sultanahmet Palace Hotel', 0, 'Sahara Tours DZ', 2, '', '', 2, 'Standard Twin', 2, '', 'without', '2026-02-15', '2026-02-19', 4, 4, 4, 0, 0, '', 100.00, 800.00, 'EUR', '', '', 'confirmed', 'unpaid', '', 1, 0, '2026-02-13 06:00:00', '2026-02-12 14:00:31'),
(8, 'HV-2602-008', 'Seymur Guliyev', 'Bosphorus View Hotel', 0, 'Caspian Holidays', 4, '', '', 4, 'Deluxe Suite', 1, '', 'without', '2026-02-18', '2026-02-22', 4, 2, 2, 0, 0, '', 130.00, 520.00, 'USD', '', '', 'confirmed', 'unpaid', '', 1, 0, '2026-02-16 08:00:00', '2026-02-12 14:00:31'),
(9, 'HV-2602-009', 'Nabil Ferhat', 'Grand Star Hotel', 0, 'Atlas Travel Algeria', 1, '', '', 1, 'Family Room', 2, '', 'without', '2026-02-20', '2026-02-25', 5, 5, 3, 2, 0, '', 110.00, 1100.00, 'USD', '', '', 'pending', 'unpaid', '', 1, 0, '2026-02-18 07:00:00', '2026-02-12 14:00:31'),
(10, 'HV-2602-010', 'Leyla Hajiyeva', 'Taksim Deluxe Suites', 0, 'Caspian Holidays', 4, '', '', 4, 'Standard Double', 1, '', 'without', '2026-02-22', '2026-02-26', 4, 2, 2, 0, 0, '', 65.00, 260.00, 'USD', '', '', 'pending', 'unpaid', '', 1, 0, '2026-02-20 08:00:00', '2026-02-12 14:00:31'),
(11, 'HV-2602-011', 'Rachid Hamadi', 'Sultanahmet Palace Hotel', 0, 'Sahara Tours DZ', 2, '', '', 2, 'Superior Suite', 1, '', 'without', '2026-02-25', '2026-02-28', 3, 3, 2, 1, 0, '', 140.00, 420.00, 'EUR', '', '', 'pending', 'unpaid', '', 1, 0, '2026-02-23 06:00:00', '2026-02-12 14:00:31'),
(12, 'HV-2602-012', 'Can Yildirim', 'Bosphorus View Hotel', 0, 'Anatolian Voyages', 5, '', '', 5, 'Standard Twin', 2, '', 'without', '2026-02-27', '2026-03-02', 3, 4, 4, 0, 0, '', 75.00, 450.00, 'TRY', '', '', 'pending', 'unpaid', '', 2, 0, '2026-02-25 07:00:00', '2026-02-12 14:00:31'),
(13, 'HV-2603-001', 'Samir Larbi', 'Grand Star Hotel', 0, 'Atlas Travel Algeria', 1, '', '', 1, 'Deluxe Double', 2, '', 'without', '2026-03-01', '2026-03-05', 4, 4, 4, 0, 0, '', 90.00, 720.00, 'USD', '', '', 'confirmed', 'unpaid', '', 1, 0, '2026-02-27 07:00:00', '2026-02-12 14:00:31'),
(14, 'HV-2603-002', 'Nadir Boumediene', 'Sultanahmet Palace Hotel', 0, 'Sahara Tours DZ', 2, '', '', 2, 'Family Suite', 1, '', 'without', '2026-03-03', '2026-03-08', 5, 4, 2, 2, 0, '', 130.00, 650.00, 'EUR', '', '', 'confirmed', 'unpaid', '', 1, 0, '2026-03-01 06:00:00', '2026-02-12 14:00:31'),
(15, 'HV-2603-003', 'Elchin Karimov', 'Taksim Deluxe Suites', 0, 'Baku Premium Travel', 3, '', '', 3, 'Superior Double', 1, '', 'without', '2026-03-05', '2026-03-09', 4, 2, 2, 0, 0, '', 80.00, 320.00, 'USD', '', '', 'confirmed', 'unpaid', '', 1, 0, '2026-03-03 07:00:00', '2026-02-12 14:00:31'),
(16, 'HV-2603-004', 'Vugar Ismayilov', 'Bosphorus View Hotel', 0, 'Caspian Holidays', 4, '', '', 4, 'Sea View Suite', 1, '', 'without', '2026-03-08', '2026-03-13', 5, 2, 2, 0, 0, '', 155.00, 775.00, 'USD', '', '', 'pending', 'unpaid', '', 1, 0, '2026-03-06 08:00:00', '2026-02-12 14:00:31'),
(17, 'HV-2603-005', 'Ibrahim Cherif', 'Grand Star Hotel', 0, 'Atlas Travel Algeria', 1, '', '', 1, 'Standard Twin', 3, '', 'without', '2026-03-10', '2026-03-15', 5, 6, 5, 1, 0, '', 75.00, 1125.00, 'USD', '', '', 'pending', 'unpaid', '', 1, 0, '2026-03-08 07:00:00', '2026-02-12 14:00:31'),
(18, 'HV-2603-006', 'Fatima Ziani', 'Sultanahmet Palace Hotel', 0, 'Sahara Tours DZ', 2, '', '', 2, 'Deluxe Double', 2, '', 'without', '2026-03-14', '2026-03-18', 4, 4, 4, 0, 0, '', 115.00, 920.00, 'EUR', '', '', 'pending', 'unpaid', '', 1, 0, '2026-03-12 06:00:00', '2026-02-12 14:00:31'),
(19, 'HV-2603-007', 'Kamran Askerov', 'Taksim Deluxe Suites', 0, 'Baku Premium Travel', 3, '', '', 3, 'Superior Suite', 1, '', 'without', '2026-03-18', '2026-03-22', 4, 2, 2, 0, 0, '', 100.00, 400.00, 'USD', '', '', 'pending', 'unpaid', '', 1, 0, '2026-03-16 07:00:00', '2026-02-12 14:00:31'),
(20, 'HV-2603-008', 'Mehmet Can', 'Cappadocia Cave Lodge', 0, 'Anatolian Voyages', 5, '', '', 5, 'Cave Room Deluxe', 2, '', 'without', '2026-03-20', '2026-03-23', 3, 4, 4, 0, 0, '', 180.00, 1080.00, 'TRY', '', '', 'pending', 'unpaid', '', 2, 0, '2026-03-18 07:00:00', '2026-02-12 14:00:31'),
(21, 'HV-2603-009', 'Djamila Messaoud', 'Grand Star Hotel', 0, 'Sahara Tours DZ', 2, '', '', 2, 'Family Room', 1, '', 'without', '2026-03-25', '2026-03-29', 4, 3, 2, 1, 0, '', 105.00, 420.00, 'EUR', '', '', 'pending', 'unpaid', '', 1, 0, '2026-03-23 06:00:00', '2026-02-12 14:00:31'),
(22, 'HV-2603-010', 'Aysel Mammadli', 'Bosphorus View Hotel', 0, 'Caspian Holidays', 4, '', '', 4, 'Standard Double', 2, '', 'without', '2026-03-28', '2026-03-31', 3, 4, 4, 0, 0, '', 80.00, 480.00, 'USD', '', '', 'pending', 'unpaid', '', 1, 0, '2026-03-26 08:00:00', '2026-02-12 14:00:31');

-- --------------------------------------------------------

--
-- Table structure for table `voucher_services`
-- Links existing tours and transfers to hotel vouchers (Guest Program)
--
CREATE TABLE `voucher_services` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `voucher_id` int(11) UNSIGNED NOT NULL,
  `service_type` enum('tour','transfer') NOT NULL,
  `reference_id` int(11) UNSIGNED NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_voucher_services_voucher` (`voucher_id`),
  KEY `idx_voucher_services_reference` (`service_type`,`reference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) UNSIGNED NOT NULL,
  `invoice_no` varchar(60) NOT NULL,
  `company_name` varchar(120) NOT NULL,
  `company_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `partner_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `invoice_date` date NOT NULL,
  `due_date` date NOT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tax_rate` decimal(5,2) DEFAULT 0.00,
  `tax_amount` decimal(12,2) DEFAULT 0.00,
  `discount` decimal(12,2) DEFAULT 0.00,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(12,2) DEFAULT 0.00,
  `currency` varchar(3) DEFAULT 'USD',
  `status` enum('draft','sent','paid','overdue','cancelled','partial','pending') DEFAULT 'draft',
  `payment_method` varchar(50) NOT NULL DEFAULT '',
  `payment_date` date NOT NULL DEFAULT '1970-01-01',
  `notes` text NOT NULL DEFAULT '',
  `type` varchar(20) NOT NULL DEFAULT 'general',
  `terms` text NOT NULL DEFAULT '',
  `file_path` varchar(255) NOT NULL DEFAULT '',
  `sent_at` datetime NOT NULL DEFAULT current_timestamp(),
  `sent_by` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `hotels_json` longtext NOT NULL DEFAULT '[]' COMMENT 'JSON: multi-hotel rooms for hotel invoices',
  `guests_json` longtext NOT NULL DEFAULT '[]' COMMENT 'JSON: guest passenger list for hotel invoices',
  `created_by` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `invoice_no`, `company_name`, `company_id`, `partner_id`, `invoice_date`, `due_date`, `subtotal`, `tax_rate`, `tax_amount`, `discount`, `total_amount`, `paid_amount`, `currency`, `status`, `payment_method`, `payment_date`, `notes`, `type`, `terms`, `file_path`, `sent_at`, `sent_by`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'INV-2602-001', 'Atlas Travel Algeria', 1, 1, '2026-02-04', '2026-03-06', 300.00, 0.00, 0.00, 0.00, 300.00, 300.00, 'USD', 'paid', 'bank_transfer', '2026-02-15', '', 'transfer', '', '', '2026-02-12 13:42:06', 0, 1, '2026-02-04 07:00:00', '2026-02-12 14:02:04'),
(2, 'INV-2602-002', 'Sahara Tours DZ', 2, 2, '2026-02-08', '2026-03-10', 535.00, 0.00, 0.00, 0.00, 535.00, 535.00, 'EUR', 'paid', 'bank_transfer', '2026-02-20', '', 'transfer', '', '', '2026-02-12 13:42:06', 0, 1, '2026-02-08 06:00:00', '2026-02-12 14:02:04'),
(3, 'INV-2602-003', 'Baku Premium Travel', 3, 3, '2026-02-10', '2026-02-25', 450.00, 0.00, 0.00, 0.00, 450.00, 450.00, 'USD', 'paid', 'cash', '2026-02-12', '', 'transfer', '', '', '2026-02-12 13:42:06', 0, 1, '2026-02-10 08:00:00', '2026-02-12 14:02:04'),
(4, 'INV-2602-004', 'Caspian Holidays', 4, 4, '2026-02-12', '2026-02-27', 1860.00, 0.00, 0.00, 0.00, 1860.00, 1860.00, 'USD', 'paid', 'bank_transfer', '2026-02-25', '', 'hotel', '', '', '2026-02-12 13:42:06', 0, 1, '2026-02-12 11:00:00', '2026-02-12 14:02:04'),
(5, 'INV-2602-005', 'Atlas Travel Algeria', 1, 1, '2026-02-15', '2026-03-17', 1620.00, 0.00, 0.00, 0.00, 1620.00, 1620.00, 'EUR', 'paid', 'bank_transfer', '2026-02-28', '', 'hotel', '', '', '2026-02-12 13:42:06', 0, 1, '2026-02-15 07:00:00', '2026-02-12 14:02:04'),
(6, 'INV-2602-006', 'Baku Premium Travel', 3, 3, '2026-02-18', '2026-03-05', 550.00, 0.00, 0.00, 0.00, 550.00, 550.00, 'USD', 'paid', 'bank_transfer', '2026-03-01', '', 'transfer', '', '', '2026-02-12 13:42:06', 0, 1, '2026-02-18 07:00:00', '2026-02-12 14:02:04'),
(7, 'INV-2602-007', 'Sahara Tours DZ', 2, 2, '2026-02-19', '2026-03-21', 1225.00, 0.00, 0.00, 0.00, 1225.00, 1225.00, 'EUR', 'paid', 'bank_transfer', '2026-03-05', '', 'tour', '', '', '2026-02-12 13:42:06', 0, 1, '2026-02-19 06:00:00', '2026-02-12 14:02:04'),
(8, 'INV-2602-008', 'Anatolian Voyages', 5, 5, '2026-02-20', '2026-03-07', 790.00, 0.00, 0.00, 0.00, 790.00, 790.00, 'USD', 'paid', 'cash', '2026-02-28', '', 'hotel', '', '', '2026-02-12 13:42:06', 0, 2, '2026-02-20 06:00:00', '2026-02-12 14:02:04'),
(9, 'INV-2602-009', 'Caspian Holidays', 4, 4, '2026-02-22', '2026-03-24', 730.00, 0.00, 0.00, 0.00, 730.00, 0.00, 'USD', 'sent', '', '1970-01-01', '', 'tour', '', '', '2026-02-12 13:42:06', 0, 1, '2026-02-22 08:00:00', '2026-02-12 14:02:04'),
(10, 'INV-2602-010', 'Atlas Travel Algeria', 1, 1, '2026-02-25', '2026-03-27', 1350.00, 0.00, 0.00, 0.00, 1350.00, 0.00, 'USD', 'sent', '', '1970-01-01', '', 'transfer', '', '', '2026-02-12 13:42:06', 0, 1, '2026-02-25 07:00:00', '2026-02-12 14:02:04'),
(11, 'INV-2602-011', 'Sahara Tours DZ', 2, 2, '2026-02-26', '2026-03-28', 510.00, 0.00, 0.00, 0.00, 510.00, 0.00, 'EUR', 'draft', '', '1970-01-01', '', 'tour', '', '', '2026-02-12 13:42:06', 0, 1, '2026-02-26 06:00:00', '2026-02-12 14:02:04'),
(12, 'INV-2602-012', 'Baku Premium Travel', 3, 3, '2026-02-27', '2026-03-14', 165.00, 0.00, 0.00, 0.00, 165.00, 0.00, 'USD', 'draft', '', '1970-01-01', '', 'hotel', '', '', '2026-02-12 13:42:06', 0, 1, '2026-02-27 07:00:00', '2026-02-12 14:02:04'),
(13, 'INV-2602-013', 'Anatolian Voyages', 5, 5, '2026-02-27', '2026-03-14', 505.00, 0.00, 0.00, 0.00, 505.00, 0.00, 'TRY', 'draft', '', '1970-01-01', '', 'tour', '', '', '2026-02-12 13:42:06', 0, 2, '2026-02-27 11:00:00', '2026-02-12 14:02:04'),
(14, 'INV-2602-014', 'Caspian Holidays', 4, 4, '2026-02-28', '2026-03-15', 265.00, 0.00, 0.00, 0.00, 265.00, 0.00, 'USD', 'overdue', '', '1970-01-01', '', 'hotel', '', '', '2026-02-12 13:42:06', 0, 1, '2026-02-28 08:00:00', '2026-02-12 14:02:04'),
(15, 'INV-2602-015', 'Atlas Travel Algeria', 1, 1, '2026-02-28', '2026-03-15', 545.00, 0.00, 0.00, 0.00, 545.00, 200.00, 'USD', 'partial', 'cash', '2026-03-10', '', 'tour', '', '', '2026-02-12 13:42:06', 0, 1, '2026-02-28 07:00:00', '2026-02-12 14:02:04'),
(16, 'INV-2603-001', 'Atlas Travel Algeria', 1, 1, '2026-03-05', '2026-04-04', 765.00, 0.00, 0.00, 0.00, 765.00, 765.00, 'USD', 'paid', 'bank_transfer', '2026-03-20', '', 'transfer', '', '', '2026-02-12 13:42:06', 0, 1, '2026-03-05 07:00:00', '2026-02-12 14:02:04'),
(17, 'INV-2603-002', 'Sahara Tours DZ', 2, 2, '2026-03-08', '2026-04-07', 705.00, 0.00, 0.00, 0.00, 705.00, 705.00, 'EUR', 'paid', 'bank_transfer', '2026-03-25', '', 'hotel', '', '', '2026-02-12 13:42:06', 0, 1, '2026-03-08 06:00:00', '2026-02-12 14:02:04'),
(18, 'INV-2603-003', 'Baku Premium Travel', 3, 3, '2026-03-09', '2026-03-24', 400.00, 0.00, 0.00, 0.00, 400.00, 400.00, 'USD', 'paid', 'cash', '2026-03-15', '', 'transfer', '', '', '2026-02-12 13:42:06', 0, 1, '2026-03-09 07:00:00', '2026-02-12 14:02:04'),
(19, 'INV-2603-004', 'Caspian Holidays', 4, 4, '2026-03-13', '2026-04-12', 830.00, 0.00, 0.00, 0.00, 830.00, 830.00, 'USD', 'paid', 'bank_transfer', '2026-03-28', '', 'hotel', '', '', '2026-02-12 13:42:06', 0, 1, '2026-03-13 08:00:00', '2026-02-12 14:02:04'),
(20, 'INV-2603-005', 'Anatolian Voyages', 5, 5, '2026-03-06', '2026-03-21', 55.00, 0.00, 0.00, 0.00, 55.00, 55.00, 'TRY', 'paid', 'cash', '2026-03-08', '', 'transfer', '', '', '2026-02-12 13:42:06', 0, 2, '2026-03-06 07:00:00', '2026-02-12 14:02:04'),
(21, 'INV-2603-006', 'Atlas Travel Algeria', 1, 1, '2026-03-15', '2026-04-14', 1515.00, 0.00, 0.00, 0.00, 1515.00, 0.00, 'USD', 'sent', '', '1970-01-01', '', 'tour', '', '', '2026-02-12 13:42:06', 0, 1, '2026-03-15 07:00:00', '2026-02-12 14:02:04'),
(22, 'INV-2603-007', 'Sahara Tours DZ', 2, 2, '2026-03-18', '2026-04-17', 1010.00, 0.00, 0.00, 0.00, 1010.00, 0.00, 'EUR', 'sent', '', '1970-01-01', '', 'tour', '', '', '2026-02-12 13:42:06', 0, 1, '2026-03-18 06:00:00', '2026-02-12 14:02:04'),
(23, 'INV-2603-008', 'Baku Premium Travel', 3, 3, '2026-03-22', '2026-04-06', 620.00, 0.00, 0.00, 0.00, 620.00, 0.00, 'USD', 'draft', '', '1970-01-01', '', 'hotel', '', '', '2026-02-12 13:42:06', 0, 1, '2026-03-22 07:00:00', '2026-02-12 14:02:04'),
(24, 'INV-2603-009', 'Caspian Holidays', 4, 4, '2026-03-28', '2026-04-12', 535.00, 0.00, 0.00, 0.00, 535.00, 0.00, 'USD', 'draft', '', '1970-01-01', '', 'tour', '', '', '2026-02-12 13:42:06', 0, 1, '2026-03-28 08:00:00', '2026-02-12 14:02:04'),
(25, 'INV-2603-010', 'Anatolian Voyages', 5, 5, '2026-03-30', '2026-04-14', 1200.00, 0.00, 0.00, 0.00, 1200.00, 0.00, 'TRY', 'draft', '', '1970-01-01', '', 'tour', '', '', '2026-02-12 13:42:06', 0, 2, '2026-03-30 07:00:00', '2026-02-12 14:02:04'),
(26, 'TI-20260215-0001', 'Atlas Travel Algeria', 1, 1, '2026-02-15', '2026-03-17', 135.00, 0.00, 0.00, 0.00, 135.00, 135.00, 'USD', 'paid', 'bank_transfer', '2026-02-20', 'Transfer: Istanbul Airport (IST) → Grand Star Hotel Sultanahmet | 3 pax', 'transfer', '', '', '2026-02-12 08:52:45', 0, 1, '2026-02-15 13:00:00', '2026-02-12 14:02:04'),
(27, 'TI-20260218-0002', 'Sahara Tours DZ', 2, 2, '2026-02-18', '2026-03-20', 90.00, 0.00, 0.00, 0.00, 90.00, 0.00, 'EUR', 'pending', '', '1970-01-01', 'Transfer: Sabiha Gokcen Airport (SAW) → Sultanahmet Palace Hotel | 5 pax', 'transfer', '', '', '2026-02-12 08:52:45', 0, 1, '2026-02-18 11:00:00', '2026-02-12 14:02:04'),
(28, 'TI-20260220-0003', 'Baku Premium Travel', 3, 3, '2026-02-20', '2026-03-07', 200.00, 0.00, 0.00, 0.00, 200.00, 200.00, 'USD', 'paid', 'cash', '2026-02-20', 'Transfer: Istanbul Airport (IST) → Taksim Deluxe Suites (Round Trip) | 2 pax', 'transfer', '', '', '2026-02-12 08:52:45', 0, 1, '2026-02-20 14:00:00', '2026-02-12 14:02:04'),
(29, 'TI-20260222-0004', 'Caspian Holidays', 4, 4, '2026-02-22', '2026-03-09', 160.00, 0.00, 0.00, 0.00, 160.00, 0.00, 'USD', 'sent', '', '1970-01-01', 'Transfer: Bosphorus View Hotel → Istanbul Airport (IST) | 4 pax', 'transfer', '', '', '2026-02-12 08:52:45', 0, 1, '2026-02-22 15:00:00', '2026-02-12 14:02:04'),
(30, 'TI-20260225-0005', 'Anatolian Voyages', 5, 5, '2026-02-25', '2026-03-12', 320.00, 0.00, 0.00, 0.00, 320.00, 320.00, 'USD', 'paid', 'bank_transfer', '2026-02-28', 'Transfer: Istanbul Airport (IST) → Cappadocia Hotel + Return | 8 pax', 'transfer', '', '', '2026-02-12 08:52:45', 0, 1, '2026-02-25 12:00:00', '2026-02-12 14:02:04'),
(31, 'HI-20260201-0001', 'Atlas Travel Algeria', 1, 1, '2026-02-01', '2026-03-03', 255.00, 0.00, 0.00, 0.00, 255.00, 255.00, 'USD', 'paid', 'bank_transfer', '2026-02-10', 'Hotel: Grand Star Hotel | Deluxe Double 3 nights | Mohamed Benali', 'hotel', '', '', '2026-02-12 08:52:45', 0, 1, '2026-02-01 12:00:00', '2026-02-12 14:02:04'),
(32, 'HI-20260203-0002', 'Sahara Tours DZ', 2, 2, '2026-02-03', '2026-03-05', 1200.00, 0.00, 0.00, 0.00, 1200.00, 1200.00, 'EUR', 'paid', 'bank_transfer', '2026-02-15', 'Hotel: Sultanahmet Palace Hotel | Family Suite x2 5 nights | Yacine Boudiaf', 'hotel', '', '', '2026-02-12 08:52:45', 0, 1, '2026-02-03 11:00:00', '2026-02-12 14:02:04'),
(33, 'HI-20260205-0003', 'Baku Premium Travel', 3, 3, '2026-02-05', '2026-02-20', 350.00, 0.00, 0.00, 0.00, 350.00, 350.00, 'USD', 'paid', 'cash', '2026-02-10', 'Hotel: Taksim Deluxe Suites | Standard Twin 5 nights | Nigar Mammadova', 'hotel', '', '', '2026-02-12 08:52:45', 0, 1, '2026-02-05 13:00:00', '2026-02-12 14:02:04'),
(34, 'HI-20260207-0004', 'Caspian Holidays', 4, 4, '2026-02-07', '2026-02-22', 1500.00, 0.00, 0.00, 0.00, 1500.00, 750.00, 'USD', 'partial', 'bank_transfer', '2026-02-14', 'Hotel: Bosphorus View Hotel | Sea View Suite x2 5 nights | Rashad Aliyev', 'hotel', '', '', '2026-02-12 08:52:45', 0, 1, '2026-02-07 16:00:00', '2026-02-12 14:02:04'),
(35, 'HI-20260210-0005', 'Atlas Travel Algeria', 1, 1, '2026-02-10', '2026-03-12', 1425.00, 0.00, 0.00, 0.00, 1425.00, 0.00, 'EUR', 'pending', '', '1970-01-01', 'Hotel: Grand Star Hotel | Superior Double x3 5 nights | Khaled Mansouri', 'hotel', '', '', '2026-02-12 08:52:45', 0, 1, '2026-02-10 12:00:00', '2026-02-12 14:02:04'),
(36, 'HI-20260214-0006', 'Baku Premium Travel', 3, 3, '2026-02-14', '2026-02-28', 340.00, 0.00, 0.00, 0.00, 340.00, 340.00, 'USD', 'paid', 'cash', '2026-02-18', 'Hotel: Taksim Deluxe Suites | Deluxe Double 4 nights | Turkan Hasanova', 'hotel', '', '', '2026-02-12 08:52:45', 0, 1, '2026-02-14 12:00:00', '2026-02-12 14:02:04'),
(37, 'TRI-20260202-0001', 'Atlas Travel Algeria', 1, 1, '2026-02-02', '2026-03-04', 195.00, 0.00, 0.00, 0.00, 195.00, 195.00, 'USD', 'paid', 'bank_transfer', '2026-02-08', 'Tour: Old City Walking Tour | Sultanahmet | 3 pax', 'tour', '', '', '2026-02-12 08:52:45', 0, 1, '2026-02-02 12:00:00', '2026-02-12 14:02:04'),
(38, 'TRI-20260205-0002', 'Sahara Tours DZ', 2, 2, '2026-02-05', '2026-03-07', 425.00, 0.00, 0.00, 0.00, 425.00, 425.00, 'EUR', 'paid', 'bank_transfer', '2026-02-12', 'Tour: Bosphorus Cruise Tour | 5 pax', 'tour', '', '', '2026-02-12 08:52:45', 0, 1, '2026-02-05 11:00:00', '2026-02-12 14:02:04'),
(39, 'TRI-20260207-0003', 'Baku Premium Travel', 3, 3, '2026-02-07', '2026-02-22', 560.00, 0.00, 0.00, 0.00, 560.00, 560.00, 'USD', 'paid', 'cash', '2026-02-07', 'Tour: Cappadocia Day Trip | 2 pax', 'tour', '', '', '2026-02-12 08:52:45', 0, 1, '2026-02-07 13:00:00', '2026-02-12 14:02:04'),
(40, 'TRI-20260209-0004', 'Caspian Holidays', 4, 4, '2026-02-09', '2026-02-24', 280.00, 0.00, 0.00, 0.00, 280.00, 0.00, 'USD', 'pending', '', '1970-01-01', 'Tour: Princes Islands Tour | 4 pax', 'tour', '', '', '2026-02-12 08:52:45', 0, 1, '2026-02-09 16:00:00', '2026-02-12 14:02:04'),
(41, 'TRI-20260212-0005', 'Atlas Travel Algeria', 1, 1, '2026-02-12', '2026-03-14', 330.00, 0.00, 0.00, 0.00, 330.00, 330.00, 'EUR', 'paid', 'bank_transfer', '2026-02-18', 'Tour: Turkish Bath Experience | 6 pax', 'tour', '', '', '2026-02-12 08:52:45', 0, 1, '2026-02-12 12:00:00', '2026-02-12 14:02:04'),
(42, 'TRI-20260218-0006', 'Anatolian Voyages', 5, 5, '2026-02-18', '2026-03-05', 680.00, 0.00, 0.00, 0.00, 680.00, 0.00, 'USD', 'sent', '', '1970-01-01', 'Tour: Bosphorus Cruise Tour | 8 pax', 'tour', '', '', '2026-02-12 08:52:45', 0, 1, '2026-02-18 11:00:00', '2026-02-12 14:02:04');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` int(11) UNSIGNED NOT NULL,
  `invoice_id` int(11) UNSIGNED NOT NULL,
  `item_type` enum('voucher','tour','service','other') DEFAULT 'voucher',
  `item_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `description` text NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoice_items`
--

INSERT INTO `invoice_items` (`id`, `invoice_id`, `item_type`, `item_id`, `description`, `quantity`, `unit_price`, `total_price`, `created_at`) VALUES
(1, 1, 'voucher', 0, 'Airport Transfer IST to Grand Star Hotel 3 pax', 1, 45.00, 45.00, '2026-02-12 04:24:59'),
(2, 1, 'voucher', 0, 'Transfer Grand Star Hotel to IST Airport 3 pax', 1, 45.00, 45.00, '2026-02-12 04:24:59'),
(3, 1, 'tour', 0, 'Old City Walking Tour 3 pax', 3, 65.00, 195.00, '2026-02-12 04:24:59'),
(4, 1, 'other', 0, 'SIM Card Data Package', 1, 15.00, 15.00, '2026-02-12 04:24:59'),
(5, 2, 'voucher', 0, 'Airport Transfer IST to Sultanahmet Palace 5 pax', 1, 55.00, 55.00, '2026-02-12 04:24:59'),
(6, 2, 'voucher', 0, 'Transfer Sultanahmet Palace to IST Airport 5 pax', 1, 55.00, 55.00, '2026-02-12 04:24:59'),
(7, 2, 'tour', 0, 'Bosphorus Cruise Tour 5 pax', 5, 85.00, 425.00, '2026-02-12 04:24:59'),
(8, 3, 'voucher', 0, 'Airport Transfer IST to Taksim Deluxe 2 pax', 1, 45.00, 45.00, '2026-02-12 04:24:59'),
(9, 3, 'voucher', 0, 'Transfer Taksim to SAW Airport 2 pax', 1, 55.00, 55.00, '2026-02-12 04:24:59'),
(10, 3, 'service', 0, 'Hotel Taksim Deluxe BB 5 nights', 1, 350.00, 350.00, '2026-02-12 04:24:59'),
(11, 4, 'voucher', 0, 'Round-trip Airport Transfer 4 pax', 1, 80.00, 80.00, '2026-02-12 04:24:59'),
(12, 4, 'service', 0, 'Hotel Bosphorus View Sea Suite 5 nights', 1, 1500.00, 1500.00, '2026-02-12 04:24:59'),
(13, 4, 'tour', 0, 'Princes Islands Tour 4 pax', 4, 70.00, 280.00, '2026-02-12 04:24:59'),
(0, 26, 'voucher', 1, 'Airport Transfer IST → Grand Star Hotel | 3 pax', 1, 135.00, 135.00, '2026-02-15 13:00:00'),
(0, 27, 'voucher', 2, 'Airport Transfer SAW → Sultanahmet Palace Hotel | 5 pax', 1, 90.00, 90.00, '2026-02-18 11:00:00'),
(0, 28, 'voucher', 3, 'Round Trip IST ↔ Taksim Deluxe Suites | 2 pax', 1, 200.00, 200.00, '2026-02-20 14:00:00'),
(0, 29, 'voucher', 4, 'Transfer Bosphorus View Hotel → IST Airport | 4 pax', 1, 160.00, 160.00, '2026-02-22 15:00:00'),
(0, 30, 'voucher', 7, 'Multi-stop Transfer IST ↔ Cappadocia Hotel | 8 pax', 1, 320.00, 320.00, '2026-02-25 12:00:00'),
(0, 31, 'voucher', 1, 'Grand Star Hotel - Deluxe Double | 01-04 Feb (3 nights) | 2 pax', 3, 85.00, 255.00, '2026-02-01 12:00:00'),
(0, 32, 'voucher', 2, 'Sultanahmet Palace Hotel - Family Suite x2 | 03-08 Feb (5 nights) | 5 pax', 10, 120.00, 1200.00, '2026-02-03 11:00:00'),
(0, 33, 'voucher', 3, 'Taksim Deluxe Suites - Standard Twin | 05-10 Feb (5 nights) | 2 pax', 5, 70.00, 350.00, '2026-02-05 13:00:00'),
(0, 34, 'voucher', 4, 'Bosphorus View Hotel - Sea View Suite x2 | 07-12 Feb (5 nights) | 4 pax', 10, 150.00, 1500.00, '2026-02-07 16:00:00'),
(0, 35, 'voucher', 5, 'Grand Star Hotel - Superior Double x3 | 10-15 Feb (5 nights) | 6 pax', 15, 95.00, 1425.00, '2026-02-10 12:00:00'),
(0, 36, 'voucher', 6, 'Taksim Deluxe Suites - Deluxe Double | 14-18 Feb (4 nights) | 2 pax', 4, 85.00, 340.00, '2026-02-14 12:00:00'),
(0, 37, 'tour', 1, 'Old City Walking Tour - Sultanahmet | 02 Feb | 3 pax', 3, 65.00, 195.00, '2026-02-02 12:00:00'),
(0, 38, 'tour', 2, 'Bosphorus Cruise Tour | 05 Feb | 5 pax', 5, 85.00, 425.00, '2026-02-05 11:00:00'),
(0, 39, 'tour', 3, 'Cappadocia Day Trip | 07 Feb | 2 pax (incl. flight + guide)', 2, 280.00, 560.00, '2026-02-07 13:00:00'),
(0, 40, 'tour', 4, 'Princes Islands Tour | 09 Feb | 4 pax', 4, 70.00, 280.00, '2026-02-09 16:00:00'),
(0, 41, 'tour', 5, 'Turkish Bath Experience - Sultanahmet | 12 Feb | 6 pax', 6, 55.00, 330.00, '2026-02-12 12:00:00'),
(0, 42, 'tour', 7, 'Bosphorus Cruise Tour | 18 Feb | 8 pax', 8, 85.00, 680.00, '2026-02-18 11:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempts` int(11) DEFAULT 1,
  `last_attempt` timestamp NOT NULL DEFAULT current_timestamp(),
  `locked_until` datetime NOT NULL DEFAULT current_timestamp(),
  `success` tinyint(1) DEFAULT 0,
  `user_agent` text NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error','system') DEFAULT 'info',
  `category` enum('general','booking','invoice','system','reminder','alert') DEFAULT 'general',
  `related_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `related_type` varchar(50) NOT NULL DEFAULT '',
  `action_url` varchar(500) NOT NULL DEFAULT '',
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` datetime NOT NULL DEFAULT current_timestamp(),
  `sent_email` tinyint(1) DEFAULT 0,
  `sent_push` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `category`, `related_id`, `related_type`, `action_url`, `is_read`, `read_at`, `sent_email`, `sent_push`, `created_at`) VALUES
(1, 1, 'New Booking Request', 'Atlas Travel Algeria submitted a new transfer booking for March 15.', 'info', 'booking', 0, '', '', 0, '2026-02-12 13:42:06', 0, 0, '2026-02-11 07:00:00'),
(2, 1, 'Invoice Paid', 'INV-2602-001 from Atlas Travel Algeria marked as paid 300 USD.', 'success', 'invoice', 0, '', '', 1, '2026-02-12 13:42:06', 0, 0, '2026-02-15 11:00:00'),
(3, 1, 'Invoice Paid', 'INV-2602-002 from Sahara Tours DZ paid 535 EUR.', 'success', 'invoice', 0, '', '', 1, '2026-02-12 13:42:06', 0, 0, '2026-02-20 08:00:00'),
(4, 1, 'New Partner Message', 'Baku Premium Travel sent a message regarding March bookings.', 'info', 'general', 0, '', '', 0, '2026-02-12 13:42:06', 0, 0, '2026-02-22 06:30:00'),
(5, 1, 'Overdue Invoice', 'INV-2602-014 from Caspian Holidays is overdue 265 USD.', 'warning', 'invoice', 0, '', '', 0, '2026-02-12 13:42:06', 0, 0, '2026-03-01 05:00:00'),
(6, 1, 'New Booking Request', 'Sahara Tours DZ submitted booking request for 6 pax hotel stay.', 'info', 'booking', 0, '', '', 0, '2026-02-12 13:42:06', 0, 0, '2026-03-05 12:00:00'),
(7, 1, 'Invoice Paid', 'INV-2603-001 from Atlas Travel Algeria paid 765 USD.', 'success', 'invoice', 0, '', '', 1, '2026-02-12 13:42:06', 0, 0, '2026-03-20 07:00:00'),
(8, 1, 'Vehicle Maintenance', 'Vehicle 34 CYN 003 is due for scheduled maintenance.', 'warning', 'system', 0, '', '', 1, '2026-02-12 14:37:24', 0, 0, '2026-03-10 05:00:00'),
(9, 1, 'Invoice Paid', 'INV-2603-004 from Caspian Holidays paid 830 USD.', 'success', 'invoice', 0, '', '', 0, '2026-02-12 13:42:06', 0, 0, '2026-03-28 13:00:00'),
(10, 1, 'New Partner', 'Cappadocia Cave Lodge added as a new hotel partner.', 'info', 'general', 0, '', '', 1, '2026-02-12 13:42:06', 0, 0, '2026-03-01 07:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `partners`
--

CREATE TABLE `partners` (
  `id` int(11) UNSIGNED NOT NULL,
  `company_name` varchar(120) NOT NULL,
  `contact_person` varchar(100) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(20) NOT NULL DEFAULT '',
  `mobile` varchar(20) NOT NULL DEFAULT '',
  `address` text NOT NULL DEFAULT '',
  `city` varchar(100) NOT NULL DEFAULT '',
  `country` varchar(100) NOT NULL DEFAULT '',
  `postal_code` varchar(20) NOT NULL DEFAULT '',
  `website` varchar(255) NOT NULL DEFAULT '',
  `tax_id` varchar(50) NOT NULL DEFAULT '',
  `commission_rate` decimal(5,2) DEFAULT 0.00,
  `credit_limit` decimal(12,2) DEFAULT 0.00,
  `balance` decimal(12,2) DEFAULT 0.00,
  `payment_terms` int(11) DEFAULT 30 COMMENT 'Payment terms in days',
  `partner_type` enum('agency','hotel','supplier','other') DEFAULT 'agency',
  `status` enum('active','inactive','suspended','blacklisted') DEFAULT 'active',
  `notes` text NOT NULL DEFAULT '',
  `contract_file` varchar(255) NOT NULL DEFAULT '',
  `created_by` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `partners`
--

INSERT INTO `partners` (`id`, `company_name`, `contact_person`, `email`, `password`, `phone`, `mobile`, `address`, `city`, `country`, `postal_code`, `website`, `tax_id`, `commission_rate`, `credit_limit`, `balance`, `payment_terms`, `partner_type`, `status`, `notes`, `contract_file`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Atlas Travel Algeria', 'Karim Benali', 'karim@atlastravel.dz', '$2y$10$6bkijPMvJhLv9MMlnHrzdOiFhjb9tGbLSqntVeAN3tryU78Pe1bY2', '+213 555 123 456', '', '', 'Algiers', 'Algeria', '', '', '', 10.00, 0.00, 0.00, 30, 'agency', 'active', '', '', 1, '2025-01-10 07:00:00', '2026-02-12 13:57:16'),
(2, 'Sahara Tours DZ', 'Fatima Boudiaf', 'fatima@saharatours.dz', '$2y$10$g9XkIJYG1CgfsBOm2ZtSnORc3R7aBRAMWlOOQkqiIZhbwVMNAn29i', '+213 555 234 567', '', '', 'Oran', 'Algeria', '', '', '', 12.00, 0.00, 0.00, 30, 'agency', 'active', '', '', 1, '2025-01-15 07:00:00', '2026-02-12 13:57:16'),
(3, 'Baku Premium Travel', 'Eldar Mammadov', 'eldar@bakutravel.az', '$2y$10$ZV44ctDZCTXhvuDvKPa1EuVegbrX1oZXFVuSczkGCT4i4avgWvHlu', '+994 50 123 4567', '', '', 'Baku', 'Azerbaijan', '', '', '', 8.00, 0.00, 0.00, 15, 'agency', 'active', '', '', 1, '2025-02-01 07:00:00', '2026-02-12 13:57:17'),
(4, 'Caspian Holidays', 'Leyla Aliyeva', 'leyla@caspianholidays.az', '$2y$10$SSAhuEVZcMARK0682CSaveKj2QaPh5J/7PsjZfdoBSGTqkuz.vjA.', '+994 50 234 5678', '', '', 'Baku', 'Azerbaijan', '', '', '', 10.00, 0.00, 0.00, 30, 'agency', 'active', '', '', 1, '2025-02-10 07:00:00', '2026-02-12 13:57:17'),
(5, 'Anatolian Voyages', 'Ahmet Demir', 'ahmet@anatolianvoyages.com', '$2y$10$lYjW/mNkhwtXB9s05Y8PLeRfBZ4dlsm/RHPfFkUjTFjzEiOveNHPu', '+90 532 111 2233', '', '', 'Istanbul', 'Turkey', '', '', '', 7.00, 0.00, 0.00, 15, 'agency', 'active', '', '', 1, '2025-03-01 07:00:00', '2026-02-12 13:57:17'),
(6, 'Grand Star Hotel', 'Hakan Ozturk', 'reservations@grandstar.com', '', '+90 212 555 1001', '', '', 'Istanbul', 'Turkey', '', '', '', 5.00, 0.00, 0.00, 30, 'hotel', 'active', '', '', 1, '2025-01-20 07:00:00', '2026-02-12 04:24:59'),
(7, 'Sultanahmet Palace Hotel', 'Elif Sahin', 'booking@sultanahmetpalace.com', '', '+90 212 555 2002', '', '', 'Istanbul', 'Turkey', '', '', '', 5.00, 0.00, 0.00, 30, 'hotel', 'active', '', '', 1, '2025-01-25 07:00:00', '2026-02-12 04:24:59'),
(8, 'Taksim Deluxe Suites', 'Murat Celik', 'info@taksimdeluxe.com', '', '+90 212 555 3003', '', '', 'Istanbul', 'Turkey', '', '', '', 6.00, 0.00, 0.00, 15, 'hotel', 'active', '', '', 1, '2025-02-05 07:00:00', '2026-02-12 04:24:59'),
(9, 'Bosphorus View Hotel', 'Zeynep Arslan', 'res@bosphorusview.com', '', '+90 212 555 4004', '', '', 'Istanbul', 'Turkey', '', '', '', 5.00, 0.00, 0.00, 30, 'hotel', 'active', '', '', 1, '2025-02-15 07:00:00', '2026-02-12 04:24:59'),
(10, 'Cappadocia Cave Lodge', 'Ibrahim Polat', 'stay@cavelodge.com', '', '+90 384 555 5005', '', '', 'Nevsehir', 'Turkey', '', '', '', 8.00, 0.00, 0.00, 30, 'hotel', 'active', '', '', 1, '2025-03-01 07:00:00', '2026-02-12 04:24:59');

-- --------------------------------------------------------

--
-- Table structure for table `partner_booking_requests`
--

CREATE TABLE `partner_booking_requests` (
  `id` int(11) UNSIGNED NOT NULL,
  `partner_id` int(11) UNSIGNED NOT NULL,
  `request_type` varchar(50) NOT NULL DEFAULT 'transfer',
  `details` text NOT NULL COMMENT 'JSON details of the request',
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_notes` text NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `partner_booking_requests`
--

INSERT INTO `partner_booking_requests` (`id`, `partner_id`, `request_type`, `details`, `status`, `admin_notes`, `created_at`, `updated_at`) VALUES
(1, 1, 'transfer', '{\"company_name\":\"Atlas Travel Algeria\",\"guest_name\":\"dklcmxlk\",\"date\":\"2026-02-13\",\"pickup_location\":\"Istanbul\\/nena hotel\",\"destination\":\"soao\",\"hotel_name\":\"\",\"tour_name\":\"\",\"pax\":1,\"notes\":\"xszz\",\"service_id\":1,\"service_name\":\"Airport Pickup (IST)\",\"service_price\":\"45.00\"}', 'pending', '', '2026-02-12 14:19:41', '2026-02-12 14:19:41'),
(2, 1, 'hotel', '{\"company_name\":\"Atlas Travel Algeria\",\"guest_name\":\"ahmet\",\"date\":\"2026-02-13\",\"pickup_location\":\"\",\"destination\":\"\",\"hotel_name\":\"loafi\",\"tour_name\":\"\",\"pax\":1,\"notes\":\"\",\"service_id\":0,\"service_name\":\"\",\"service_price\":\"\",\"check_in\":\"2026-02-13\",\"check_out\":\"2026-02-13\",\"room_type\":\"Standard Single\",\"board_type\":\"half_board\",\"room_count\":1,\"adults\":2,\"children\":1}', 'pending', '', '2026-02-12 15:08:49', '2026-02-12 15:08:49'),
(3, 1, 'hotel', '{\"company_name\":\"Atlas Travel Algeria\",\"guest_name\":\"ahmet\",\"date\":\"2026-02-12\",\"pickup_location\":\"\",\"destination\":\"\",\"hotel_name\":\"Ankara Hilton\",\"tour_name\":\"\",\"pax\":1,\"notes\":\"flight ticket to istanbul\",\"service_id\":0,\"service_name\":\"\",\"service_price\":\"\",\"check_in\":\"2026-02-12\",\"check_out\":\"2026-02-13\",\"room_type\":\"Standard Single\",\"board_type\":\"half_board\",\"room_count\":3,\"adults\":2,\"children\":1}', 'approved', '', '2026-02-12 15:26:13', '2026-02-12 15:29:53');

-- --------------------------------------------------------

--
-- Table structure for table `partner_messages`
--

CREATE TABLE `partner_messages` (
  `id` int(11) UNSIGNED NOT NULL,
  `partner_id` int(11) UNSIGNED NOT NULL,
  `sender_type` enum('admin','partner') NOT NULL,
  `sender_id` int(11) UNSIGNED NOT NULL,
  `subject` varchar(255) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `file_path` varchar(255) NOT NULL DEFAULT '',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `partner_messages`
--

INSERT INTO `partner_messages` (`id`, `partner_id`, `sender_type`, `sender_id`, `subject`, `message`, `file_path`, `is_read`, `created_at`) VALUES
(1, 1, 'partner', 1, 'ankjdakldna', 'sjndxkjlajds', '', 1, '2026-02-12 14:27:13');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `used_at` datetime NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reminder_logs`
--

CREATE TABLE `reminder_logs` (
  `id` int(11) UNSIGNED NOT NULL,
  `voucher_id` int(11) UNSIGNED NOT NULL,
  `reminder_type` varchar(50) NOT NULL,
  `status` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) UNSIGNED NOT NULL,
  `service_type` enum('tour','transfer','hotel','other') DEFAULT 'tour',
  `name` varchar(150) NOT NULL,
  `description` text NOT NULL DEFAULT '',
  `price` decimal(10,2) DEFAULT 0.00,
  `currency` varchar(3) DEFAULT 'USD',
  `unit` varchar(50) DEFAULT 'per_person',
  `details` text NOT NULL DEFAULT '',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `service_type`, `name`, `description`, `price`, `currency`, `unit`, `details`, `status`, `created_at`, `updated_at`) VALUES
(1, 'transfer', 'Airport Pickup (IST)', 'Istanbul Airport to Hotel Transfer', 45.00, 'USD', 'per_vehicle', '', 'active', '2025-01-01 06:00:00', '2026-02-12 04:24:59'),
(2, 'transfer', 'Airport Pickup (SAW)', 'Sabiha Gokcen Airport to Hotel Transfer', 55.00, 'USD', 'per_vehicle', '', 'active', '2025-01-01 06:00:00', '2026-02-12 04:24:59'),
(3, 'transfer', 'VIP Airport Transfer', 'Luxury vehicle airport transfer', 120.00, 'USD', 'per_vehicle', '', 'active', '2025-01-01 06:00:00', '2026-02-12 04:24:59'),
(4, 'transfer', 'Intercity Transfer', 'Istanbul to Bursa/Cappadocia', 250.00, 'USD', 'per_vehicle', '', 'active', '2025-01-01 06:00:00', '2026-02-12 04:24:59'),
(5, 'tour', 'Old City Walking Tour', 'Sultanahmet Blue Mosque Hagia Sophia Grand Bazaar', 65.00, 'USD', 'per_person', '', 'active', '2025-01-01 06:00:00', '2026-02-12 04:24:59'),
(6, 'tour', 'Bosphorus Cruise Tour', 'Full day Bosphorus cruise with lunch', 85.00, 'USD', 'per_person', '', 'active', '2025-01-01 06:00:00', '2026-02-12 04:24:59'),
(7, 'tour', 'Cappadocia Day Trip', 'Full day Cappadocia tour with flight', 280.00, 'USD', 'per_person', '', 'active', '2025-01-01 06:00:00', '2026-02-12 04:24:59'),
(8, 'tour', 'Princes Islands Tour', 'Full day Princes Islands tour with lunch', 70.00, 'USD', 'per_person', '', 'active', '2025-01-01 06:00:00', '2026-02-12 04:24:59'),
(9, 'tour', 'Turkish Bath Experience', 'Traditional hammam experience', 55.00, 'USD', 'per_person', '', 'active', '2025-01-01 06:00:00', '2026-02-12 04:24:59'),
(10, 'other', 'SIM Card Data Package', 'Local Turkish SIM card with 20GB data', 15.00, 'USD', 'per_person', '', 'active', '2025-01-01 06:00:00', '2026-02-12 04:24:59'),
(0, 'hotel', 'Grand Star Hotel — Standard', 'Standard Double Room, Bed & Breakfast', 85.00, 'USD', 'per_night', '', 'active', '2026-02-12 15:02:42', '2026-02-12 15:02:42'),
(0, 'hotel', 'Grand Star Hotel — Deluxe', 'Deluxe Room, Bed & Breakfast', 120.00, 'USD', 'per_night', '', 'active', '2026-02-12 15:02:42', '2026-02-12 15:02:42'),
(0, 'hotel', 'Sultanahmet Palace Hotel — Standard', 'Standard Twin Room, Bed & Breakfast', 100.00, 'EUR', 'per_night', '', 'active', '2026-02-12 15:02:42', '2026-02-12 15:02:42'),
(0, 'hotel', 'Taksim Deluxe Suites — Superior', 'Superior Double, Half Board', 95.00, 'USD', 'per_night', '', 'active', '2026-02-12 15:02:42', '2026-02-12 15:02:42'),
(0, 'hotel', 'Bosphorus View Hotel — Sea View', 'Sea View Suite, Breakfast Included', 150.00, 'USD', 'per_night', '', 'active', '2026-02-12 15:02:42', '2026-02-12 15:02:42'),
(0, 'hotel', 'Grand Star Hotel — Standard', 'Standard Double Room, Bed & Breakfast', 85.00, 'USD', 'per_night', '', 'active', '2026-02-12 15:03:20', '2026-02-12 15:03:20'),
(0, 'hotel', 'Grand Star Hotel — Deluxe', 'Deluxe Room, Bed & Breakfast', 120.00, 'USD', 'per_night', '', 'active', '2026-02-12 15:03:20', '2026-02-12 15:03:20'),
(0, 'hotel', 'Sultanahmet Palace Hotel — Standard', 'Standard Twin Room, Bed & Breakfast', 100.00, 'EUR', 'per_night', '', 'active', '2026-02-12 15:03:20', '2026-02-12 15:03:20'),
(0, 'hotel', 'Taksim Deluxe Suites — Superior', 'Superior Double, Half Board', 95.00, 'USD', 'per_night', '', 'active', '2026-02-12 15:03:20', '2026-02-12 15:03:20'),
(0, 'hotel', 'Bosphorus View Hotel — Sea View', 'Sea View Suite, Breakfast Included', 150.00, 'USD', 'per_night', '', 'active', '2026-02-12 15:03:20', '2026-02-12 15:03:20');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) UNSIGNED NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL DEFAULT '',
  `setting_group` varchar(50) DEFAULT 'general',
  `data_type` enum('string','integer','boolean','json','array') DEFAULT 'string',
  `is_encrypted` tinyint(1) DEFAULT 0,
  `description` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_group`, `data_type`, `is_encrypted`, `description`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'CYN Tourism', 'general', 'string', 0, 'Website name', '2026-02-12 04:23:54', '2026-02-12 04:23:54'),
(2, 'site_email', 'info@cyntourism.com', 'general', 'string', 0, 'Default site email', '2026-02-12 04:23:54', '2026-02-12 04:23:54'),
(3, 'timezone', 'Europe/Istanbul', 'general', 'string', 0, 'System timezone', '2026-02-12 04:23:54', '2026-02-12 04:23:54'),
(4, 'date_format', 'd/m/Y', 'general', 'string', 0, 'Default date format', '2026-02-12 04:23:54', '2026-02-12 04:23:54'),
(5, 'time_format', 'H:i', 'general', 'string', 0, 'Default time format', '2026-02-12 04:23:54', '2026-02-12 04:23:54'),
(6, 'currency', 'USD', 'general', 'string', 0, 'Default currency', '2026-02-12 04:23:54', '2026-02-12 04:23:54'),
(7, 'max_login_attempts', '5', 'security', 'integer', 0, 'Maximum failed login attempts before lockout', '2026-02-12 04:23:54', '2026-02-12 04:23:54'),
(8, 'lockout_duration', '30', 'security', 'integer', 0, 'Account lockout duration in minutes', '2026-02-12 04:23:54', '2026-02-12 04:23:54'),
(9, 'password_min_length', '8', 'security', 'integer', 0, 'Minimum password length', '2026-02-12 04:23:54', '2026-02-12 04:23:54'),
(10, 'session_timeout', '120', 'security', 'integer', 0, 'Session timeout in minutes', '2026-02-12 04:23:54', '2026-02-12 04:23:54'),
(11, 'maintenance_mode', '0', 'system', 'boolean', 0, 'Enable maintenance mode', '2026-02-12 04:23:54', '2026-02-12 04:23:54'),
(12, 'debug_mode', '0', 'system', 'boolean', 0, 'Enable debug mode', '2026-02-12 04:23:54', '2026-02-12 04:23:54');

-- --------------------------------------------------------

--
-- Table structure for table `tours`
--

CREATE TABLE `tours` (
  `id` int(11) UNSIGNED NOT NULL,
  `tour_name` varchar(200) NOT NULL,
  `tour_code` varchar(50) NOT NULL DEFAULT '',
  `description` text NOT NULL DEFAULT '',
  `tour_type` enum('daily','multi_day','private','group') DEFAULT 'daily',
  `destination` varchar(200) NOT NULL DEFAULT '',
  `pickup_location` varchar(255) NOT NULL DEFAULT '',
  `dropoff_location` varchar(255) NOT NULL DEFAULT '',
  `tour_date` date NOT NULL,
  `start_time` time NOT NULL DEFAULT '00:00:00',
  `end_time` time NOT NULL DEFAULT '00:00:00',
  `duration_days` int(11) DEFAULT 1,
  `total_pax` int(11) NOT NULL DEFAULT 0,
  `max_pax` int(11) NOT NULL DEFAULT 0,
  `passengers` text NOT NULL DEFAULT '' COMMENT 'JSON array of passenger details',
  `company_name` varchar(120) NOT NULL DEFAULT '',
  `hotel_name` varchar(200) NOT NULL DEFAULT '',
  `customer_phone` varchar(50) NOT NULL DEFAULT '',
  `adults` int(11) NOT NULL DEFAULT 0,
  `children` int(11) NOT NULL DEFAULT 0,
  `infants` int(11) NOT NULL DEFAULT 0,
  `customers` text NOT NULL DEFAULT '[]',
  `tour_items` text NOT NULL DEFAULT '[]',
  `company_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `partner_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `guide_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `vehicle_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `driver_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `price_per_person` decimal(10,2) DEFAULT 0.00 COMMENT 'Adult price per 1 pax',
  `price_child` decimal(10,2) DEFAULT 0.00 COMMENT 'Child price per 1 pax',
  `price_per_infant` decimal(10,2) DEFAULT 0.00 COMMENT 'Infant price per 1 pax',
  `total_price` decimal(10,2) DEFAULT 0.00,
  `currency` varchar(3) DEFAULT 'USD',
  `includes` text NOT NULL DEFAULT '',
  `excludes` text NOT NULL DEFAULT '',
  `itinerary` text NOT NULL DEFAULT '',
  `special_requests` text NOT NULL DEFAULT '',
  `status` enum('pending','confirmed','in_progress','completed','cancelled') DEFAULT 'pending',
  `payment_status` enum('unpaid','partial','paid','refunded') DEFAULT 'unpaid',
  `notes` text NOT NULL DEFAULT '',
  `created_by` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `updated_by` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tours`
--

INSERT INTO `tours` (`id`, `tour_name`, `tour_code`, `description`, `tour_type`, `destination`, `pickup_location`, `dropoff_location`, `tour_date`, `start_time`, `end_time`, `duration_days`, `total_pax`, `max_pax`, `passengers`, `company_name`, `hotel_name`, `customer_phone`, `adults`, `children`, `infants`, `customers`, `tour_items`, `company_id`, `partner_id`, `guide_id`, `vehicle_id`, `driver_id`, `price_per_person`, `price_child`, `price_per_infant`, `total_price`, `currency`, `includes`, `excludes`, `itinerary`, `special_requests`, `status`, `payment_status`, `notes`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'Old City Walking Tour', 'TOUR-FEB-001', '', 'daily', 'Sultanahmet', 'Grand Star Hotel', 'Grand Star Hotel', '2026-02-02', '09:00:00', '17:00:00', 1, 3, 0, '', 'Atlas Travel Algeria', '', '', 0, 0, 0, '[]', '[]', 1, 1, 0, 0, 0, 65.00, 0.00, 0.00, 195.00, 'USD', '', '', '', '', 'completed', 'unpaid', '', 1, 0, '2026-01-28 07:00:00', '2026-02-12 14:00:31'),
(2, 'Bosphorus Cruise Tour', 'TOUR-FEB-002', '', 'daily', 'Bosphorus', 'Sultanahmet Palace Hotel', 'Sultanahmet Palace Hotel', '2026-02-05', '10:00:00', '18:00:00', 1, 5, 0, '', 'Sahara Tours DZ', '', '', 0, 0, 0, '[]', '[]', 2, 2, 0, 0, 0, 85.00, 0.00, 0.00, 425.00, 'EUR', '', '', '', '', 'completed', 'unpaid', '', 1, 0, '2026-02-01 06:00:00', '2026-02-12 14:00:31'),
(3, 'Cappadocia Day Trip', 'TOUR-FEB-003', '', 'daily', 'Cappadocia', 'Istanbul Airport', 'Istanbul Airport', '2026-02-07', '06:00:00', '22:00:00', 1, 2, 0, '', 'Baku Premium Travel', '', '', 0, 0, 0, '[]', '[]', 3, 3, 0, 0, 0, 280.00, 0.00, 0.00, 560.00, 'USD', '', '', '', '', 'completed', 'unpaid', '', 1, 0, '2026-02-03 08:00:00', '2026-02-12 14:00:31'),
(4, 'Princes Islands Tour', 'TOUR-FEB-004', '', 'daily', 'Princes Islands', 'Bosphorus View Hotel', 'Bosphorus View Hotel', '2026-02-09', '09:30:00', '17:30:00', 1, 4, 0, '', 'Caspian Holidays', '', '', 0, 0, 0, '[]', '[]', 4, 4, 0, 0, 0, 70.00, 0.00, 0.00, 280.00, 'USD', '', '', '', '', 'completed', 'unpaid', '', 1, 0, '2026-02-05 11:00:00', '2026-02-12 14:00:31'),
(5, 'Turkish Bath Experience', 'TOUR-FEB-005', '', 'daily', 'Sultanahmet', 'Grand Star Hotel', 'Grand Star Hotel', '2026-02-12', '14:00:00', '17:00:00', 1, 6, 0, '', 'Atlas Travel Algeria', '', '', 0, 0, 0, '[]', '[]', 1, 1, 0, 0, 0, 55.00, 0.00, 0.00, 330.00, 'EUR', '', '', '', '', 'completed', 'unpaid', '', 1, 0, '2026-02-08 07:00:00', '2026-02-12 14:00:31'),
(6, 'Old City Walking Tour', 'TOUR-FEB-006', '', 'daily', 'Sultanahmet', 'Taksim Deluxe Suites', 'Taksim Deluxe Suites', '2026-02-15', '09:00:00', '17:00:00', 1, 2, 0, '', 'Baku Premium Travel', '', '', 0, 0, 0, '[]', '[]', 3, 3, 0, 0, 0, 65.00, 0.00, 0.00, 130.00, 'USD', '', '', '', '', 'confirmed', 'unpaid', '', 1, 0, '2026-02-12 07:00:00', '2026-02-12 14:00:31'),
(7, 'Bosphorus Cruise Tour', 'TOUR-FEB-007', '', 'daily', 'Bosphorus', 'Grand Star Hotel', 'Grand Star Hotel', '2026-02-18', '10:00:00', '18:00:00', 1, 8, 0, '', 'Anatolian Voyages', '', '', 0, 0, 0, '[]', '[]', 5, 5, 0, 0, 0, 85.00, 0.00, 0.00, 680.00, 'USD', '', '', '', '', 'confirmed', 'unpaid', '', 2, 0, '2026-02-16 06:00:00', '2026-02-12 14:00:31'),
(8, 'Cappadocia Day Trip', 'TOUR-FEB-008', '', 'daily', 'Cappadocia', 'Istanbul Airport', 'Istanbul Airport', '2026-02-22', '06:00:00', '22:00:00', 1, 4, 0, '', 'Sahara Tours DZ', '', '', 0, 0, 0, '[]', '[]', 2, 2, 0, 0, 0, 280.00, 0.00, 0.00, 1120.00, 'USD', '', '', '', '', 'pending', 'unpaid', '', 1, 0, '2026-02-20 06:00:00', '2026-02-12 14:00:31'),
(9, 'Princes Islands Tour', 'TOUR-FEB-009', '', 'daily', 'Princes Islands', 'Taksim Deluxe Suites', 'Taksim Deluxe Suites', '2026-02-25', '09:30:00', '17:30:00', 1, 3, 0, '', 'Caspian Holidays', '', '', 0, 0, 0, '[]', '[]', 4, 4, 0, 0, 0, 70.00, 0.00, 0.00, 210.00, 'USD', '', '', '', '', 'pending', 'unpaid', '', 1, 0, '2026-02-23 08:00:00', '2026-02-12 14:00:31'),
(10, 'Turkish Bath Experience', 'TOUR-FEB-010', '', 'daily', 'Sultanahmet', 'Bosphorus View Hotel', 'Bosphorus View Hotel', '2026-02-27', '14:00:00', '17:00:00', 1, 4, 0, '', 'Anatolian Voyages', '', '', 0, 0, 0, '[]', '[]', 5, 5, 0, 0, 0, 55.00, 0.00, 0.00, 220.00, 'TRY', '', '', '', '', 'pending', 'unpaid', '', 2, 0, '2026-02-25 07:00:00', '2026-02-12 14:00:31'),
(11, 'Old City Walking Tour', 'TOUR-MAR-001', '', 'daily', 'Sultanahmet', 'Grand Star Hotel', 'Grand Star Hotel', '2026-03-02', '09:00:00', '17:00:00', 1, 4, 0, '', 'Atlas Travel Algeria', '', '', 0, 0, 0, '[]', '[]', 1, 1, 0, 0, 0, 65.00, 0.00, 0.00, 260.00, 'USD', '', '', '', '', 'confirmed', 'unpaid', '', 1, 0, '2026-02-27 07:00:00', '2026-02-12 14:00:31'),
(12, 'Bosphorus Cruise Tour', 'TOUR-MAR-002', '', 'daily', 'Bosphorus', 'Sultanahmet Palace Hotel', 'Sultanahmet Palace Hotel', '2026-03-05', '10:00:00', '18:00:00', 1, 4, 0, '', 'Sahara Tours DZ', '', '', 0, 0, 0, '[]', '[]', 2, 2, 0, 0, 0, 85.00, 0.00, 0.00, 340.00, 'EUR', '', '', '', '', 'confirmed', 'unpaid', '', 1, 0, '2026-03-01 06:00:00', '2026-02-12 14:00:31'),
(13, 'Cappadocia Day Trip', 'TOUR-MAR-003', '', 'daily', 'Cappadocia', 'Istanbul Airport', 'Istanbul Airport', '2026-03-08', '06:00:00', '22:00:00', 1, 2, 0, '', 'Baku Premium Travel', '', '', 0, 0, 0, '[]', '[]', 3, 3, 0, 0, 0, 280.00, 0.00, 0.00, 560.00, 'USD', '', '', '', '', 'pending', 'unpaid', '', 1, 0, '2026-03-06 07:00:00', '2026-02-12 14:00:31'),
(14, 'Princes Islands Tour', 'TOUR-MAR-004', '', 'daily', 'Princes Islands', 'Bosphorus View Hotel', 'Bosphorus View Hotel', '2026-03-12', '09:30:00', '17:30:00', 1, 6, 0, '', 'Caspian Holidays', '', '', 0, 0, 0, '[]', '[]', 4, 4, 0, 0, 0, 70.00, 0.00, 0.00, 420.00, 'USD', '', '', '', '', 'pending', 'unpaid', '', 1, 0, '2026-03-10 08:00:00', '2026-02-12 14:00:31'),
(15, 'Old City Walking Tour', 'TOUR-MAR-005', '', 'daily', 'Sultanahmet', 'Grand Star Hotel', 'Grand Star Hotel', '2026-03-15', '09:00:00', '17:00:00', 1, 6, 0, '', 'Atlas Travel Algeria', '', '', 0, 0, 0, '[]', '[]', 1, 1, 0, 0, 0, 65.00, 0.00, 0.00, 390.00, 'USD', '', '', '', '', 'pending', 'unpaid', '', 1, 0, '2026-03-13 07:00:00', '2026-02-12 14:00:31'),
(16, 'Turkish Bath Experience', 'TOUR-MAR-006', '', 'daily', 'Sultanahmet', 'Taksim Deluxe Suites', 'Taksim Deluxe Suites', '2026-03-20', '14:00:00', '17:00:00', 1, 4, 0, '', 'Baku Premium Travel', '', '', 0, 0, 0, '[]', '[]', 3, 3, 0, 0, 0, 55.00, 220.00, 'USD', '', '', '', '', 'pending', 'unpaid', '', 1, 0, '2026-03-18 07:00:00', '2026-02-12 14:00:31'),
(17, 'Bosphorus Cruise Tour', 'TOUR-MAR-007', '', 'daily', 'Bosphorus', 'Grand Star Hotel', 'Grand Star Hotel', '2026-03-25', '10:00:00', '18:00:00', 1, 3, 0, '', 'Sahara Tours DZ', '', '', 0, 0, 0, '[]', '[]', 2, 2, 0, 0, 0, 85.00, 255.00, 'EUR', '', '', '', '', 'pending', 'unpaid', '', 1, 0, '2026-03-23 06:00:00', '2026-02-12 14:00:31'),
(18, 'Cappadocia Day Trip', 'TOUR-MAR-008', '', 'daily', 'Cappadocia', 'Istanbul Airport', 'Istanbul Airport', '2026-03-30', '06:00:00', '22:00:00', 1, 4, 0, '', 'Anatolian Voyages', '', '', 0, 0, 0, '[]', '[]', 5, 5, 0, 0, 0, 280.00, 1120.00, 'TRY', '', '', '', '', 'pending', 'unpaid', '', 2, 0, '2026-03-28 07:00:00', '2026-02-12 14:00:31');

-- --------------------------------------------------------

--
-- Table structure for table `tour_assignments`
--

CREATE TABLE `tour_assignments` (
  `id` int(11) UNSIGNED NOT NULL,
  `tour_id` int(11) NOT NULL,
  `guide_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `vehicle_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `driver_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `assignment_date` date NOT NULL,
  `start_time` time NOT NULL DEFAULT '00:00:00',
  `end_time` time NOT NULL DEFAULT '00:00:00',
  `status` enum('scheduled','in_progress','completed','cancelled') DEFAULT 'scheduled',
  `notes` text NOT NULL DEFAULT '',
  `created_by` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tour_guides`
--

CREATE TABLE `tour_guides` (
  `id` int(11) UNSIGNED NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `full_name` varchar(200) GENERATED ALWAYS AS (concat(`first_name`,' ',`last_name`)) STORED,
  `email` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(20) NOT NULL,
  `mobile` varchar(20) NOT NULL DEFAULT '',
  `license_no` varchar(50) NOT NULL DEFAULT '',
  `license_expiry` date NOT NULL DEFAULT '1970-01-01',
  `id_number` varchar(50) NOT NULL DEFAULT '',
  `date_of_birth` date NOT NULL DEFAULT '1970-01-01',
  `address` text NOT NULL DEFAULT '',
  `city` varchar(100) NOT NULL DEFAULT '',
  `languages` varchar(255) NOT NULL COMMENT 'Comma-separated languages',
  `specializations` varchar(255) NOT NULL DEFAULT '' COMMENT 'Comma-separated specializations',
  `experience_years` int(11) DEFAULT 0,
  `daily_rate` decimal(10,2) DEFAULT 0.00,
  `currency` varchar(3) DEFAULT 'USD',
  `hire_date` date NOT NULL DEFAULT '1970-01-01',
  `termination_date` date NOT NULL DEFAULT '1970-01-01',
  `status` enum('active','inactive','on_leave','suspended','terminated') DEFAULT 'active',
  `rating` decimal(2,1) DEFAULT 5.0,
  `total_tours` int(11) DEFAULT 0,
  `photo` varchar(255) NOT NULL DEFAULT '',
  `documents` text NOT NULL DEFAULT '' COMMENT 'JSON array of document paths',
  `bio` text NOT NULL DEFAULT '',
  `notes` text NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tour_guides`
--

INSERT INTO `tour_guides` (`id`, `first_name`, `last_name`, `email`, `phone`, `mobile`, `license_no`, `license_expiry`, `id_number`, `date_of_birth`, `address`, `city`, `languages`, `specializations`, `experience_years`, `daily_rate`, `currency`, `hire_date`, `termination_date`, `status`, `rating`, `total_tours`, `photo`, `documents`, `bio`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'Deniz', 'Ozkan', '', '+90 532 200 0001', '', 'GUIDE-IST-001', '2027-12-31', '', '1970-01-01', '', '', 'Turkish,English,Arabic,French', 'Historical Tours,Cultural Tours', 8, 150.00, 'USD', '1970-01-01', '1970-01-01', 'active', 5.0, 0, '', '', '', '', '2025-01-01 06:00:00', '2026-02-12 04:24:59'),
(2, 'Selin', 'Aydin', '', '+90 532 200 0002', '', 'GUIDE-IST-002', '2027-06-30', '', '1970-01-01', '', '', 'Turkish,English,Russian', 'Bosphorus Tours,Shopping Tours', 5, 120.00, 'USD', '1970-01-01', '1970-01-01', 'active', 5.0, 0, '', '', '', '', '2025-01-01 06:00:00', '2026-02-12 04:24:59'),
(3, 'Yusuf', 'Erdem', '', '+90 532 200 0003', '', 'GUIDE-IST-003', '2028-03-15', '', '1970-01-01', '', '', 'Turkish,English,Arabic,Azerbaijani', 'Historical Tours,Religious Tours', 10, 180.00, 'USD', '1970-01-01', '1970-01-01', 'active', 5.0, 0, '', '', '', '', '2025-01-01 06:00:00', '2026-02-12 04:24:59');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','manager','operator','viewer') DEFAULT 'viewer',
  `status` enum('active','inactive','suspended','pending') DEFAULT 'pending',
  `email_verified` tinyint(1) DEFAULT 0,
  `email_verified_at` datetime NOT NULL DEFAULT current_timestamp(),
  `last_login` datetime NOT NULL DEFAULT current_timestamp(),
  `last_login_ip` varchar(45) NOT NULL DEFAULT '',
  `login_count` int(11) DEFAULT 0,
  `failed_login_attempts` int(11) DEFAULT 0,
  `locked_until` datetime NOT NULL DEFAULT current_timestamp(),
  `password_changed_at` datetime NOT NULL DEFAULT current_timestamp(),
  `remember_token` varchar(255) NOT NULL DEFAULT '',
  `remember_token_expires` datetime NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) NOT NULL DEFAULT '',
  `reset_token_expires` datetime NOT NULL DEFAULT current_timestamp(),
  `two_factor_secret` varchar(255) NOT NULL DEFAULT '',
  `two_factor_enabled` tinyint(1) DEFAULT 0,
  `profile_image` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(20) NOT NULL DEFAULT '',
  `full_name` varchar(200) GENERATED ALWAYS AS (concat(`first_name`,' ',`last_name`)) STORED,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `role`, `status`, `email_verified`, `email_verified_at`, `last_login`, `last_login_ip`, `login_count`, `failed_login_attempts`, `locked_until`, `password_changed_at`, `remember_token`, `remember_token_expires`, `reset_token`, `reset_token_expires`, `two_factor_secret`, `two_factor_enabled`, `profile_image`, `phone`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'System', 'Administrator', 'admin@cyntourism.com', '$2y$10$DRu7..oIH26BG9adzqEaBun.yYsB6zbrtTDEEGWkYm/shn.Ba8SLK', 'admin', 'active', 1, '2026-02-12 04:00:46', '2026-02-12 15:04:25', '', 4, 0, '2026-02-12 13:42:06', '2026-02-12 13:42:06', '', '2026-02-12 13:42:06', '', '2026-02-12 13:42:06', '', 0, '', '', '2026-02-12 04:00:46', '2026-02-12 15:04:25', '2026-02-12 13:42:06'),
(3, 'Mehmet', 'Yilmaz', 'mehmet@cyntourism.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', 'active', 1, '2026-02-12 13:42:06', '2026-02-12 13:42:06', '', 0, 0, '2026-02-12 13:42:06', '2026-02-12 13:42:06', '', '2026-02-12 13:42:06', '', '2026-02-12 13:42:06', '', 0, '', '', '2025-01-15 06:00:00', '2026-02-12 04:08:09', '2026-02-12 13:42:06'),
(4, 'Ayse', 'Kaya', 'ayse@cyntourism.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'operator', 'active', 1, '2026-02-12 13:42:06', '2026-02-12 13:42:06', '', 0, 0, '2026-02-12 13:42:06', '2026-02-12 13:42:06', '', '2026-02-12 13:42:06', '', '2026-02-12 13:42:06', '', 0, '', '', '2025-02-01 06:00:00', '2026-02-12 04:08:09', '2026-02-12 13:42:06');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) UNSIGNED NOT NULL,
  `plate_number` varchar(20) NOT NULL,
  `make` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `year` int(4) NOT NULL DEFAULT 0,
  `color` varchar(30) NOT NULL DEFAULT '',
  `capacity` int(11) NOT NULL DEFAULT 4,
  `luggage_capacity` int(11) DEFAULT 2,
  `vehicle_type` enum('sedan','suv','van','minibus','bus','luxury','other') DEFAULT 'sedan',
  `fuel_type` enum('gasoline','diesel','electric','hybrid') DEFAULT 'gasoline',
  `insurance_expiry` date NOT NULL DEFAULT '1970-01-01',
  `registration_expiry` date NOT NULL DEFAULT '1970-01-01',
  `mileage` int(11) DEFAULT 0,
  `status` enum('available','in_use','maintenance','out_of_service','retired') DEFAULT 'available',
  `last_maintenance` date NOT NULL DEFAULT '1970-01-01',
  `next_maintenance` date NOT NULL DEFAULT '1970-01-01',
  `driver_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `image` varchar(255) NOT NULL DEFAULT '',
  `notes` text NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `plate_number`, `make`, `model`, `year`, `color`, `capacity`, `luggage_capacity`, `vehicle_type`, `fuel_type`, `insurance_expiry`, `registration_expiry`, `mileage`, `status`, `last_maintenance`, `next_maintenance`, `driver_id`, `image`, `notes`, `created_at`, `updated_at`) VALUES
(1, '34 CYN 001', 'Mercedes-Benz', 'Vito Tourer', 2023, 'Black', 8, 8, 'van', 'gasoline', '1970-01-01', '1970-01-01', 0, 'available', '1970-01-01', '1970-01-01', 0, '', '', '2025-01-01 06:00:00', '2026-02-12 04:24:59'),
(2, '34 CYN 002', 'Mercedes-Benz', 'E-Class', 2024, 'White', 4, 3, 'sedan', 'gasoline', '1970-01-01', '1970-01-01', 0, 'available', '1970-01-01', '1970-01-01', 0, '', '', '2025-01-01 06:00:00', '2026-02-12 04:24:59'),
(3, '34 CYN 003', 'Mercedes-Benz', 'Sprinter', 2022, 'Silver', 16, 16, 'minibus', 'gasoline', '1970-01-01', '1970-01-01', 0, 'available', '1970-01-01', '1970-01-01', 0, '', '', '2025-01-01 06:00:00', '2026-02-12 04:24:59'),
(4, '34 CYN 004', 'Volkswagen', 'Caravelle', 2023, 'Black', 9, 9, 'van', 'gasoline', '1970-01-01', '1970-01-01', 0, 'available', '1970-01-01', '1970-01-01', 0, '', '', '2025-02-01 06:00:00', '2026-02-12 04:24:59'),
(5, '34 CYN 005', 'BMW', '7 Series', 2024, 'Black', 4, 2, 'luxury', 'gasoline', '1970-01-01', '1970-01-01', 0, 'available', '1970-01-01', '1970-01-01', 0, '', '', '2025-02-01 06:00:00', '2026-02-12 04:24:59'),
(6, '34 CYN 006', 'Toyota', 'Coaster', 2021, 'White', 25, 25, 'bus', 'gasoline', '1970-01-01', '1970-01-01', 0, 'available', '1970-01-01', '1970-01-01', 0, '', '', '2025-03-01 06:00:00', '2026-02-12 04:24:59');

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `id` int(11) UNSIGNED NOT NULL,
  `voucher_no` varchar(60) NOT NULL,
  `company_name` varchar(120) NOT NULL,
  `company_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `partner_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `hotel_name` varchar(120) NOT NULL,
  `pickup_location` varchar(255) NOT NULL,
  `dropoff_location` varchar(255) NOT NULL,
  `pickup_date` date NOT NULL,
  `pickup_time` time NOT NULL,
  `return_date` date NOT NULL DEFAULT '1970-01-01',
  `return_time` time NOT NULL DEFAULT '00:00:00',
  `transfer_type` enum('one_way','round_trip','multi_stop') DEFAULT 'one_way',
  `total_pax` int(11) NOT NULL DEFAULT 0,
  `passengers` text NOT NULL DEFAULT '' COMMENT 'JSON array of passenger names',
  `flight_number` varchar(50) NOT NULL DEFAULT '',
  `flight_arrival_time` time NOT NULL DEFAULT '00:00:00',
  `vehicle_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `driver_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `guide_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `special_requests` text NOT NULL DEFAULT '',
  `price` decimal(10,2) DEFAULT 0.00,
  `currency` varchar(3) DEFAULT 'USD',
  `status` enum('pending','confirmed','completed','cancelled','no_show') DEFAULT 'pending',
  `payment_status` enum('unpaid','partial','paid','refunded') DEFAULT 'unpaid',
  `notes` text NOT NULL DEFAULT '',
  `created_by` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `updated_by` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vouchers`
--

INSERT INTO `vouchers` (`id`, `voucher_no`, `company_name`, `company_id`, `partner_id`, `hotel_name`, `pickup_location`, `dropoff_location`, `pickup_date`, `pickup_time`, `return_date`, `return_time`, `transfer_type`, `total_pax`, `passengers`, `flight_number`, `flight_arrival_time`, `vehicle_id`, `driver_id`, `guide_id`, `special_requests`, `price`, `currency`, `status`, `payment_status`, `notes`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'TRF-2602-001', 'Atlas Travel Algeria', 1, 1, 'Grand Star Hotel', 'Istanbul Airport (IST)', 'Grand Star Hotel Sultanahmet', '2026-02-01', '14:30:00', '1970-01-01', '00:00:00', 'one_way', 3, '', 'TK-653', '00:00:00', 1, 1, 0, '', 45.00, 'USD', 'completed', 'paid', '', 1, 0, '2026-01-28 07:00:00', '2026-02-12 14:00:31'),
(2, 'TRF-2602-002', 'Atlas Travel Algeria', 1, 1, 'Grand Star Hotel', 'Grand Star Hotel Sultanahmet', 'Istanbul Airport (IST)', '2026-02-04', '08:00:00', '1970-01-01', '00:00:00', 'one_way', 3, '', 'TK-654', '00:00:00', 2, 1, 0, '', 45.00, 'USD', 'completed', 'paid', '', 1, 0, '2026-01-28 07:05:00', '2026-02-12 14:00:31'),
(3, 'TRF-2602-003', 'Sahara Tours DZ', 2, 2, 'Sultanahmet Palace Hotel', 'Istanbul Airport (IST)', 'Sultanahmet Palace Hotel', '2026-02-03', '19:45:00', '1970-01-01', '00:00:00', 'one_way', 5, '', 'AH-1070', '00:00:00', 1, 2, 0, '', 55.00, 'USD', 'completed', 'paid', '', 1, 0, '2026-02-01 06:00:00', '2026-02-12 14:00:31'),
(4, 'TRF-2602-004', 'Sahara Tours DZ', 2, 2, 'Sultanahmet Palace Hotel', 'Sultanahmet Palace Hotel', 'Istanbul Airport (IST)', '2026-02-08', '06:30:00', '1970-01-01', '00:00:00', 'one_way', 5, '', 'AH-1071', '00:00:00', 3, 2, 0, '', 55.00, 'USD', 'completed', 'paid', '', 1, 0, '2026-02-01 06:05:00', '2026-02-12 14:00:31'),
(5, 'TRF-2602-005', 'Baku Premium Travel', 3, 3, 'Taksim Deluxe Suites', 'Istanbul Airport (IST)', 'Taksim Deluxe Suites', '2026-02-05', '22:10:00', '1970-01-01', '00:00:00', 'one_way', 2, '', 'J2-8072', '00:00:00', 2, 3, 0, '', 45.00, 'USD', 'completed', 'paid', '', 1, 0, '2026-02-03 08:00:00', '2026-02-12 14:00:31'),
(6, 'TRF-2602-006', 'Baku Premium Travel', 3, 3, 'Taksim Deluxe Suites', 'Taksim Deluxe Suites', 'Sabiha Gokcen Airport (SAW)', '2026-02-10', '10:00:00', '1970-01-01', '00:00:00', 'one_way', 2, '', 'J2-8073', '00:00:00', 2, 3, 0, '', 55.00, 'USD', 'completed', 'paid', '', 1, 0, '2026-02-03 08:05:00', '2026-02-12 14:00:31'),
(7, 'TRF-2602-007', 'Caspian Holidays', 4, 4, 'Bosphorus View Hotel', 'Istanbul Airport (IST)', 'Bosphorus View Hotel', '2026-02-07', '16:20:00', '1970-01-01', '00:00:00', 'round_trip', 4, '', 'J2-8074', '00:00:00', 4, 4, 0, '', 80.00, 'USD', 'completed', 'paid', '', 1, 0, '2026-02-05 11:00:00', '2026-02-12 14:00:31'),
(8, 'TRF-2602-008', 'Atlas Travel Algeria', 1, 1, 'Grand Star Hotel', 'Istanbul Airport (IST)', 'Grand Star Hotel Sultanahmet', '2026-02-10', '15:00:00', '1970-01-01', '00:00:00', 'one_way', 6, '', 'TK-653', '00:00:00', 3, 1, 0, '', 55.00, 'EUR', 'completed', 'paid', '', 1, 0, '2026-02-08 07:00:00', '2026-02-12 14:00:31'),
(9, 'TRF-2602-009', 'Anatolian Voyages', 5, 5, 'Taksim Deluxe Suites', 'Istanbul Airport (IST)', 'Taksim Deluxe Suites', '2026-02-11', '11:30:00', '1970-01-01', '00:00:00', 'one_way', 2, '', 'PC-401', '00:00:00', 2, 3, 0, '', 45.00, 'USD', 'completed', 'paid', '', 1, 0, '2026-02-09 07:00:00', '2026-02-12 14:00:31'),
(10, 'TRF-2602-010', 'Sahara Tours DZ', 2, 2, 'Grand Star Hotel', 'Sabiha Gokcen Airport (SAW)', 'Grand Star Hotel Sultanahmet', '2026-02-12', '21:00:00', '1970-01-01', '00:00:00', 'one_way', 4, '', 'AH-1074', '00:00:00', 1, 2, 0, '', 55.00, 'EUR', 'completed', 'paid', '', 1, 0, '2026-02-10 06:00:00', '2026-02-12 14:00:31'),
(11, 'TRF-2602-011', 'Baku Premium Travel', 3, 3, 'Sultanahmet Palace Hotel', 'Istanbul Airport (IST)', 'Sultanahmet Palace Hotel', '2026-02-14', '13:15:00', '1970-01-01', '00:00:00', 'round_trip', 2, '', 'J2-8076', '00:00:00', 5, 1, 0, '', 80.00, 'USD', 'completed', 'paid', '', 1, 0, '2026-02-12 07:00:00', '2026-02-12 14:00:31'),
(12, 'TRF-2602-012', 'Caspian Holidays', 4, 4, 'Taksim Deluxe Suites', 'Istanbul Airport (IST)', 'Taksim Deluxe Suites', '2026-02-15', '09:45:00', '1970-01-01', '00:00:00', 'one_way', 3, '', 'J2-8078', '00:00:00', 4, 4, 0, '', 45.00, 'USD', 'completed', 'paid', '', 2, 0, '2026-02-13 08:00:00', '2026-02-12 14:00:31'),
(13, 'TRF-2602-013', 'Atlas Travel Algeria', 1, 1, 'Bosphorus View Hotel', 'Istanbul Airport (IST)', 'Bosphorus View Hotel', '2026-02-17', '17:30:00', '1970-01-01', '00:00:00', 'one_way', 4, '', 'TK-655', '00:00:00', 1, 1, 0, '', 45.00, 'USD', 'completed', 'paid', '', 1, 0, '2026-02-15 07:00:00', '2026-02-12 14:00:31'),
(14, 'TRF-2602-014', 'Anatolian Voyages', 5, 5, 'Grand Star Hotel', 'Istanbul Airport (IST)', 'Grand Star Hotel', '2026-02-18', '20:00:00', '1970-01-01', '00:00:00', 'one_way', 8, '', 'TK-789', '00:00:00', 3, 2, 0, '', 65.00, 'USD', 'completed', 'paid', '', 2, 0, '2026-02-16 06:00:00', '2026-02-12 14:00:31'),
(15, 'TRF-2602-015', 'Sahara Tours DZ', 2, 2, 'Taksim Deluxe Suites', 'Istanbul Airport (IST)', 'Taksim Deluxe Suites', '2026-02-19', '23:00:00', '1970-01-01', '00:00:00', 'one_way', 3, '', 'AH-1076', '00:00:00', 1, 3, 0, '', 45.00, 'EUR', 'completed', 'paid', '', 1, 0, '2026-02-17 06:00:00', '2026-02-12 14:00:31'),
(16, 'TRF-2602-016', 'Baku Premium Travel', 3, 3, 'Grand Star Hotel', 'Istanbul Airport (IST)', 'Grand Star Hotel', '2026-02-20', '12:00:00', '1970-01-01', '00:00:00', 'one_way', 2, '', 'J2-8080', '00:00:00', 2, 1, 0, '', 45.00, 'USD', 'confirmed', 'paid', '', 1, 0, '2026-02-18 07:00:00', '2026-02-12 14:00:31'),
(17, 'TRF-2602-017', 'Caspian Holidays', 4, 4, 'Sultanahmet Palace Hotel', 'Istanbul Airport (IST)', 'Sultanahmet Palace Hotel', '2026-02-21', '14:50:00', '1970-01-01', '00:00:00', 'round_trip', 6, '', 'J2-8082', '00:00:00', 3, 4, 0, '', 90.00, 'USD', 'confirmed', 'paid', '', 1, 0, '2026-02-19 08:00:00', '2026-02-12 14:00:31'),
(18, 'TRF-2602-018', 'Atlas Travel Algeria', 1, 1, 'Taksim Deluxe Suites', 'Istanbul Airport (IST)', 'Taksim Deluxe Suites', '2026-02-22', '10:30:00', '1970-01-01', '00:00:00', 'one_way', 5, '', 'TK-653', '00:00:00', 1, 1, 0, '', 55.00, 'USD', 'confirmed', 'unpaid', '', 1, 0, '2026-02-20 07:00:00', '2026-02-12 14:00:31'),
(19, 'TRF-2602-019', 'Sahara Tours DZ', 2, 2, 'Bosphorus View Hotel', 'Istanbul Airport (IST)', 'Bosphorus View Hotel', '2026-02-24', '18:00:00', '1970-01-01', '00:00:00', 'one_way', 4, '', 'AH-1078', '00:00:00', 4, 2, 0, '', 45.00, 'EUR', 'pending', 'unpaid', '', 1, 0, '2026-02-22 06:00:00', '2026-02-12 14:00:31'),
(20, 'TRF-2602-020', 'Anatolian Voyages', 5, 5, 'Sultanahmet Palace Hotel', 'Sabiha Gokcen Airport (SAW)', 'Sultanahmet Palace Hotel', '2026-02-25', '22:30:00', '1970-01-01', '00:00:00', 'one_way', 3, '', 'PC-403', '00:00:00', 2, 3, 0, '', 55.00, 'TRY', 'pending', 'unpaid', '', 2, 0, '2026-02-23 11:00:00', '2026-02-12 14:00:31'),
(21, 'TRF-2602-021', 'Atlas Travel Algeria', 1, 1, 'Grand Star Hotel', 'Grand Star Hotel', 'Bursa (Intercity)', '2026-02-26', '08:00:00', '1970-01-01', '00:00:00', 'one_way', 4, '', '', '00:00:00', 3, 4, 0, '', 250.00, 'USD', 'pending', 'unpaid', '', 1, 0, '2026-02-24 07:00:00', '2026-02-12 14:00:31'),
(22, 'TRF-2602-022', 'Baku Premium Travel', 3, 3, 'Taksim Deluxe Suites', 'Istanbul Airport (IST)', 'Taksim Deluxe Suites', '2026-02-27', '16:00:00', '1970-01-01', '00:00:00', 'one_way', 2, '', 'J2-8084', '00:00:00', 5, 1, 0, '', 120.00, 'USD', 'pending', 'unpaid', '', 1, 0, '2026-02-25 07:00:00', '2026-02-12 14:00:31'),
(23, 'TRF-2602-023', 'Caspian Holidays', 4, 4, 'Grand Star Hotel', 'Istanbul Airport (IST)', 'Grand Star Hotel', '2026-02-28', '11:00:00', '1970-01-01', '00:00:00', 'one_way', 5, '', 'J2-8086', '00:00:00', 1, 2, 0, '', 55.00, 'USD', 'pending', 'unpaid', '', 1, 0, '2026-02-26 08:00:00', '2026-02-12 14:00:31'),
(24, 'TRF-2602-024', 'Sahara Tours DZ', 2, 2, 'Sultanahmet Palace Hotel', 'Istanbul Airport (IST)', 'Sultanahmet Palace Hotel', '2026-02-28', '20:15:00', '1970-01-01', '00:00:00', 'round_trip', 7, '', 'AH-1080', '00:00:00', 3, 4, 0, '', 90.00, 'EUR', 'pending', 'unpaid', '', 1, 0, '2026-02-26 06:00:00', '2026-02-12 14:00:31'),
(25, 'TRF-2602-025', 'Atlas Travel Algeria', 1, 1, 'Bosphorus View Hotel', 'Istanbul Airport (IST)', 'Bosphorus View Hotel', '2026-02-28', '23:45:00', '1970-01-01', '00:00:00', 'one_way', 3, '', 'TK-657', '00:00:00', 2, 1, 0, '', 45.00, 'USD', 'pending', 'unpaid', '', 1, 0, '2026-02-27 07:00:00', '2026-02-12 14:00:31'),
(26, 'TRF-2603-001', 'Atlas Travel Algeria', 1, 1, 'Grand Star Hotel', 'Istanbul Airport (IST)', 'Grand Star Hotel', '2026-03-01', '14:00:00', '1970-01-01', '00:00:00', 'one_way', 4, '', 'TK-653', '00:00:00', 1, 1, 0, '', 45.00, 'USD', 'confirmed', 'paid', '', 1, 0, '2026-02-27 07:00:00', '2026-02-12 14:00:31'),
(27, 'TRF-2603-002', 'Sahara Tours DZ', 2, 2, 'Taksim Deluxe Suites', 'Istanbul Airport (IST)', 'Taksim Deluxe Suites', '2026-03-02', '19:00:00', '1970-01-01', '00:00:00', 'one_way', 5, '', 'AH-1082', '00:00:00', 3, 2, 0, '', 55.00, 'EUR', 'confirmed', 'paid', '', 1, 0, '2026-02-28 06:00:00', '2026-02-12 14:00:31'),
(28, 'TRF-2603-003', 'Baku Premium Travel', 3, 3, 'Bosphorus View Hotel', 'Istanbul Airport (IST)', 'Bosphorus View Hotel', '2026-03-03', '22:30:00', '1970-01-01', '00:00:00', 'round_trip', 2, '', 'J2-8088', '00:00:00', 5, 3, 0, '', 80.00, 'USD', 'confirmed', 'paid', '', 1, 0, '2026-03-01 07:00:00', '2026-02-12 14:00:31'),
(29, 'TRF-2603-004', 'Caspian Holidays', 4, 4, 'Sultanahmet Palace Hotel', 'Istanbul Airport (IST)', 'Sultanahmet Palace Hotel', '2026-03-05', '10:30:00', '1970-01-01', '00:00:00', 'one_way', 6, '', 'J2-8090', '00:00:00', 4, 4, 0, '', 55.00, 'USD', 'confirmed', 'paid', '', 1, 0, '2026-03-03 08:00:00', '2026-02-12 14:00:31'),
(30, 'TRF-2603-005', 'Anatolian Voyages', 5, 5, 'Grand Star Hotel', 'Sabiha Gokcen Airport (SAW)', 'Grand Star Hotel', '2026-03-06', '15:20:00', '1970-01-01', '00:00:00', 'one_way', 3, '', 'PC-405', '00:00:00', 2, 1, 0, '', 55.00, 'TRY', 'confirmed', 'paid', '', 2, 0, '2026-03-04 07:00:00', '2026-02-12 14:00:31'),
(31, 'TRF-2603-006', 'Atlas Travel Algeria', 1, 1, 'Taksim Deluxe Suites', 'Istanbul Airport (IST)', 'Taksim Deluxe Suites', '2026-03-08', '13:00:00', '1970-01-01', '00:00:00', 'one_way', 8, '', 'TK-659', '00:00:00', 3, 2, 0, '', 65.00, 'USD', 'confirmed', 'unpaid', '', 1, 0, '2026-03-06 07:00:00', '2026-02-12 14:00:31'),
(32, 'TRF-2603-007', 'Sahara Tours DZ', 2, 2, 'Grand Star Hotel', 'Istanbul Airport (IST)', 'Grand Star Hotel', '2026-03-10', '20:45:00', '1970-01-01', '00:00:00', 'one_way', 4, '', 'AH-1084', '00:00:00', 1, 3, 0, '', 45.00, 'EUR', 'confirmed', 'unpaid', '', 1, 0, '2026-03-08 06:00:00', '2026-02-12 14:00:31'),
(33, 'TRF-2603-008', 'Baku Premium Travel', 3, 3, 'Sultanahmet Palace Hotel', 'Istanbul Airport (IST)', 'Sultanahmet Palace Hotel', '2026-03-12', '11:00:00', '1970-01-01', '00:00:00', 'round_trip', 2, '', 'J2-8092', '00:00:00', 5, 1, 0, '', 80.00, 'USD', 'pending', 'unpaid', '', 1, 0, '2026-03-10 07:00:00', '2026-02-12 14:00:31'),
(34, 'TRF-2603-009', 'Caspian Holidays', 4, 4, 'Taksim Deluxe Suites', 'Istanbul Airport (IST)', 'Taksim Deluxe Suites', '2026-03-14', '17:30:00', '1970-01-01', '00:00:00', 'one_way', 5, '', 'J2-8094', '00:00:00', 4, 4, 0, '', 55.00, 'USD', 'pending', 'unpaid', '', 1, 0, '2026-03-12 08:00:00', '2026-02-12 14:00:31'),
(35, 'TRF-2603-010', 'Atlas Travel Algeria', 1, 1, 'Bosphorus View Hotel', 'Istanbul Airport (IST)', 'Bosphorus View Hotel', '2026-03-15', '09:00:00', '1970-01-01', '00:00:00', 'one_way', 3, '', 'TK-661', '00:00:00', 2, 1, 0, '', 45.00, 'USD', 'pending', 'unpaid', '', 1, 0, '2026-03-13 07:00:00', '2026-02-12 14:00:31'),
(36, 'TRF-2603-011', 'Anatolian Voyages', 5, 5, 'Grand Star Hotel', 'Istanbul Airport (IST)', 'Grand Star Hotel', '2026-03-16', '21:00:00', '1970-01-01', '00:00:00', 'one_way', 4, '', 'TK-790', '00:00:00', 1, 2, 0, '', 45.00, 'USD', 'pending', 'unpaid', '', 2, 0, '2026-03-14 06:00:00', '2026-02-12 14:00:31'),
(37, 'TRF-2603-012', 'Sahara Tours DZ', 2, 2, 'Sultanahmet Palace Hotel', 'Istanbul Airport (IST)', 'Sultanahmet Palace Hotel', '2026-03-18', '18:00:00', '1970-01-01', '00:00:00', 'round_trip', 6, '', 'AH-1086', '00:00:00', 3, 3, 0, '', 90.00, 'EUR', 'pending', 'unpaid', '', 1, 0, '2026-03-16 06:00:00', '2026-02-12 14:00:31'),
(38, 'TRF-2603-013', 'Baku Premium Travel', 3, 3, 'Taksim Deluxe Suites', 'Istanbul Airport (IST)', 'Taksim Deluxe Suites', '2026-03-20', '12:30:00', '1970-01-01', '00:00:00', 'one_way', 2, '', 'J2-8096', '00:00:00', 5, 1, 0, '', 120.00, 'USD', 'pending', 'unpaid', '', 1, 0, '2026-03-18 07:00:00', '2026-02-12 14:00:31'),
(39, 'TRF-2603-014', 'Caspian Holidays', 4, 4, 'Grand Star Hotel', 'Istanbul Airport (IST)', 'Grand Star Hotel', '2026-03-22', '16:00:00', '1970-01-01', '00:00:00', 'one_way', 4, '', 'J2-8098', '00:00:00', 4, 4, 0, '', 55.00, 'USD', 'pending', 'unpaid', '', 1, 0, '2026-03-20 08:00:00', '2026-02-12 14:00:31'),
(40, 'TRF-2603-015', 'Atlas Travel Algeria', 1, 1, 'Sultanahmet Palace Hotel', 'Istanbul Airport (IST)', 'Sultanahmet Palace Hotel', '2026-03-24', '14:30:00', '1970-01-01', '00:00:00', 'one_way', 5, '', 'TK-663', '00:00:00', 1, 1, 0, '', 55.00, 'USD', 'pending', 'unpaid', '', 1, 0, '2026-03-22 07:00:00', '2026-02-12 14:00:31'),
(41, 'TRF-2603-016', 'Sahara Tours DZ', 2, 2, 'Taksim Deluxe Suites', 'Istanbul Airport (IST)', 'Taksim Deluxe Suites', '2026-03-25', '23:00:00', '1970-01-01', '00:00:00', 'one_way', 3, '', 'AH-1088', '00:00:00', 2, 2, 0, '', 45.00, 'EUR', 'pending', 'unpaid', '', 1, 0, '2026-03-23 06:00:00', '2026-02-12 14:00:31'),
(42, 'TRF-2603-017', 'Anatolian Voyages', 5, 5, 'Bosphorus View Hotel', 'Istanbul Airport (IST)', 'Bosphorus View Hotel', '2026-03-27', '10:00:00', '1970-01-01', '00:00:00', 'round_trip', 4, '', 'PC-407', '00:00:00', 4, 3, 0, '', 80.00, 'TRY', 'pending', 'unpaid', '', 2, 0, '2026-03-25 07:00:00', '2026-02-12 14:00:31'),
(43, 'TRF-2603-018', 'Baku Premium Travel', 3, 3, 'Grand Star Hotel', 'Istanbul Airport (IST)', 'Grand Star Hotel', '2026-03-28', '19:15:00', '1970-01-01', '00:00:00', 'one_way', 2, '', 'J2-8100', '00:00:00', 5, 1, 0, '', 45.00, 'USD', 'pending', 'unpaid', '', 1, 0, '2026-03-26 07:00:00', '2026-02-12 14:00:31'),
(44, 'TRF-2603-019', 'Atlas Travel Algeria', 1, 1, 'Taksim Deluxe Suites', 'Istanbul Airport (IST)', 'Taksim Deluxe Suites', '2026-03-30', '15:00:00', '1970-01-01', '00:00:00', 'one_way', 7, '', 'TK-665', '00:00:00', 3, 2, 0, '', 65.00, 'USD', 'pending', 'unpaid', '', 1, 0, '2026-03-28 07:00:00', '2026-02-12 14:00:31'),
(45, 'TRF-2603-020', 'Caspian Holidays', 4, 4, 'Sultanahmet Palace Hotel', 'Istanbul Airport (IST)', 'Sultanahmet Palace Hotel', '2026-03-31', '08:00:00', '1970-01-01', '00:00:00', 'one_way', 4, '', 'J2-8102', '00:00:00', 1, 4, 0, '', 55.00, 'USD', 'pending', 'unpaid', '', 1, 0, '2026-03-29 08:00:00', '2026-02-12 14:00:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `action` (`action`),
  ADD KEY `entity_type` (`entity_type`),
  ADD KEY `ip_address` (`ip_address`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `severity` (`severity`);

--
-- Indexes for table `api_keys`
--
ALTER TABLE `api_keys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `api_key` (`api_key`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `api_tokens`
--
ALTER TABLE `api_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `expires_at` (`expires_at`);

--
-- Indexes for table `hotels`
--
ALTER TABLE `hotels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hotels_name` (`name`),
  ADD KEY `idx_hotels_city` (`city`),
  ADD KEY `idx_hotels_stars` (`stars`),
  ADD KEY `idx_hotels_status` (`status`);

--
-- Indexes for table `hotel_rooms`
--
ALTER TABLE `hotel_rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hotel_rooms_hotel` (`hotel_id`),
  ADD KEY `idx_hotel_rooms_type` (`room_type`);

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_config`
--
ALTER TABLE `email_config`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hotel_vouchers`
--
ALTER TABLE `hotel_vouchers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hotel_vouchers_partner` (`partner_id`),
  ADD KEY `idx_hotel_vouchers_company_id` (`company_id`),
  ADD KEY `idx_hotel_vouchers_status` (`status`),
  ADD KEY `idx_hotel_vouchers_checkin` (`check_in`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoices_type` (`type`),
  ADD KEY `idx_invoices_partner` (`partner_id`),
  ADD KEY `idx_invoices_status` (`status`),
  ADD KEY `idx_invoices_company_id` (`company_id`),
  ADD KEY `idx_invoices_date` (`invoice_date`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice_items_invoice` (`invoice_id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user` (`user_id`),
  ADD KEY `idx_notifications_read` (`is_read`);

--
-- Indexes for table `partner_booking_requests`
--
ALTER TABLE `partner_booking_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pbr_partner` (`partner_id`),
  ADD KEY `idx_pbr_status` (`status`);

--
-- Indexes for table `partner_messages`
--
ALTER TABLE `partner_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pm_partner` (`partner_id`),
  ADD KEY `idx_pm_read` (`is_read`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reminder_logs`
--
ALTER TABLE `reminder_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `tours`
--
ALTER TABLE `tours`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tours_partner` (`partner_id`),
  ADD KEY `idx_tours_company_id` (`company_id`),
  ADD KEY `idx_tours_status` (`status`),
  ADD KEY `idx_tours_date` (`tour_date`);

--
-- Indexes for table `tour_assignments`
--
ALTER TABLE `tour_assignments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tour_guides`
--
ALTER TABLE `tour_guides`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plate_number` (`plate_number`);

--
-- Indexes for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_vouchers_partner` (`partner_id`),
  ADD KEY `idx_vouchers_company_id` (`company_id`),
  ADD KEY `idx_vouchers_status` (`status`),
  ADD KEY `idx_vouchers_date` (`pickup_date`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `api_keys`
--
ALTER TABLE `api_keys`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `api_tokens`
--
ALTER TABLE `api_tokens`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `drivers`
--
ALTER TABLE `drivers`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `email_config`
--
ALTER TABLE `email_config`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hotels`
--
ALTER TABLE `hotels`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `hotel_rooms`
--
ALTER TABLE `hotel_rooms`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `hotel_vouchers`
--
ALTER TABLE `hotel_vouchers`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `partner_booking_requests`
--
ALTER TABLE `partner_booking_requests`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `partner_messages`
--
ALTER TABLE `partner_messages`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reminder_logs`
--
ALTER TABLE `reminder_logs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tours`
--
ALTER TABLE `tours`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `tour_assignments`
--
ALTER TABLE `tour_assignments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tour_guides`
--
ALTER TABLE `tour_guides`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `hotel_rooms`
--
ALTER TABLE `hotel_rooms`
  ADD CONSTRAINT `fk_hotel_rooms_hotel` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
