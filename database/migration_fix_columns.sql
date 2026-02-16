-- ============================================================
-- CYN Tourism — Migration: Add ALL Missing Columns & Tables
-- 
-- EVIDENCE: Server logs show these columns/tables are missing:
--   - hotel_vouchers: address, telephone, company_id, partner_id, etc.
--   - tours: hotel_name, customer_phone, adults, children, infants, customers, tour_items
--   - invoices: company_id, partner_id, type, terms, file_path, etc.
--   - vouchers: company_id, partner_id, payment_status, etc.
--   - partner_booking_requests: missing AUTO_INCREMENT
--   - partner_messages: missing AUTO_INCREMENT
--
-- Uses MariaDB "ADD COLUMN IF NOT EXISTS" (supported 10.0.2+)
-- SAFE to run multiple times on existing database.
--
-- Date: 2026-02-12
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- TABLE: hotel_vouchers — add missing columns
-- ============================================================
CREATE TABLE IF NOT EXISTS `hotel_vouchers` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `voucher_no` varchar(60) NOT NULL,
  `guest_name` varchar(200) NOT NULL,
  `hotel_name` varchar(200) NOT NULL,
  `status` enum('pending','confirmed','checked_in','checked_out','cancelled','no_show') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `hotel_vouchers`
  ADD COLUMN IF NOT EXISTS `hotel_id` int(11) UNSIGNED NOT NULL DEFAULT 0 AFTER `hotel_name`,
  ADD COLUMN IF NOT EXISTS `company_name` varchar(120) NOT NULL DEFAULT '' AFTER `hotel_id`,
  ADD COLUMN IF NOT EXISTS `company_id` int(11) UNSIGNED NOT NULL DEFAULT 0 AFTER `company_name`,
  ADD COLUMN IF NOT EXISTS `address` varchar(255) NOT NULL DEFAULT '' AFTER `company_id`,
  ADD COLUMN IF NOT EXISTS `telephone` varchar(50) NOT NULL DEFAULT '' AFTER `address`,
  ADD COLUMN IF NOT EXISTS `partner_id` int(11) UNSIGNED NOT NULL DEFAULT 0 AFTER `telephone`,
  ADD COLUMN IF NOT EXISTS `room_type` varchar(100) NOT NULL DEFAULT '' AFTER `partner_id`,
  ADD COLUMN IF NOT EXISTS `room_count` int(11) DEFAULT 1 AFTER `room_type`,
  ADD COLUMN IF NOT EXISTS `board_type` enum('room_only','bed_breakfast','half_board','full_board','all_inclusive') DEFAULT 'bed_breakfast' AFTER `room_count`,
  ADD COLUMN IF NOT EXISTS `transfer_type` enum('without','with_transfer','airport_transfer') DEFAULT 'without' AFTER `board_type`,
  ADD COLUMN IF NOT EXISTS `check_in` date NOT NULL DEFAULT '1970-01-01' AFTER `transfer_type`,
  ADD COLUMN IF NOT EXISTS `check_out` date NOT NULL DEFAULT '1970-01-01' AFTER `check_in`,
  ADD COLUMN IF NOT EXISTS `nights` int(11) DEFAULT 1 AFTER `check_out`,
  ADD COLUMN IF NOT EXISTS `total_pax` int(11) DEFAULT 0 AFTER `nights`,
  ADD COLUMN IF NOT EXISTS `adults` int(11) DEFAULT 0 AFTER `total_pax`,
  ADD COLUMN IF NOT EXISTS `children` int(11) DEFAULT 0 AFTER `adults`,
  ADD COLUMN IF NOT EXISTS `infants` int(11) DEFAULT 0 AFTER `children`,
  ADD COLUMN IF NOT EXISTS `confirmation_no` varchar(100) NOT NULL DEFAULT '' AFTER `infants`,
  ADD COLUMN IF NOT EXISTS `price_per_night` decimal(10,2) DEFAULT 0.00 AFTER `confirmation_no`,
  ADD COLUMN IF NOT EXISTS `total_price` decimal(10,2) DEFAULT 0.00 AFTER `price_per_night`,
  ADD COLUMN IF NOT EXISTS `currency` varchar(3) DEFAULT 'USD' AFTER `total_price`,
  ADD COLUMN IF NOT EXISTS `customers` text NOT NULL DEFAULT '' AFTER `currency`,
  ADD COLUMN IF NOT EXISTS `special_requests` text NOT NULL DEFAULT '' AFTER `customers`,
  ADD COLUMN IF NOT EXISTS `payment_status` enum('unpaid','partial','paid','refunded') DEFAULT 'unpaid' AFTER `status`,
  ADD COLUMN IF NOT EXISTS `notes` text NOT NULL DEFAULT '' AFTER `payment_status`,
  ADD COLUMN IF NOT EXISTS `created_by` int(11) UNSIGNED NOT NULL DEFAULT 0 AFTER `notes`,
  ADD COLUMN IF NOT EXISTS `updated_by` int(11) UNSIGNED NOT NULL DEFAULT 0 AFTER `created_by`,
  ADD COLUMN IF NOT EXISTS `additional_services` text NOT NULL DEFAULT '' COMMENT 'JSON: tour/transfer as additional services' AFTER `special_requests`;

-- ============================================================
-- TABLE: tours — add missing columns
-- ============================================================
CREATE TABLE IF NOT EXISTS `tours` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tour_name` varchar(200) NOT NULL,
  `tour_code` varchar(50) NOT NULL DEFAULT '',
  `status` enum('pending','confirmed','in_progress','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `tours`
  ADD COLUMN IF NOT EXISTS `description` text NOT NULL DEFAULT '' AFTER `tour_code`,
  ADD COLUMN IF NOT EXISTS `tour_type` enum('daily','multi_day','private','group') DEFAULT 'daily' AFTER `description`,
  ADD COLUMN IF NOT EXISTS `destination` varchar(200) NOT NULL DEFAULT '' AFTER `tour_type`,
  ADD COLUMN IF NOT EXISTS `pickup_location` varchar(255) NOT NULL DEFAULT '' AFTER `destination`,
  ADD COLUMN IF NOT EXISTS `dropoff_location` varchar(255) NOT NULL DEFAULT '' AFTER `pickup_location`,
  ADD COLUMN IF NOT EXISTS `tour_date` date NOT NULL DEFAULT '1970-01-01' AFTER `dropoff_location`,
  ADD COLUMN IF NOT EXISTS `start_time` time NOT NULL DEFAULT '00:00:00' AFTER `tour_date`,
  ADD COLUMN IF NOT EXISTS `end_time` time NOT NULL DEFAULT '00:00:00' AFTER `start_time`,
  ADD COLUMN IF NOT EXISTS `duration_days` int(11) DEFAULT 1 AFTER `end_time`,
  ADD COLUMN IF NOT EXISTS `total_pax` int(11) NOT NULL DEFAULT 0 AFTER `duration_days`,
  ADD COLUMN IF NOT EXISTS `max_pax` int(11) NOT NULL DEFAULT 0 AFTER `total_pax`,
  ADD COLUMN IF NOT EXISTS `passengers` text NOT NULL DEFAULT '' AFTER `max_pax`,
  ADD COLUMN IF NOT EXISTS `company_name` varchar(120) NOT NULL DEFAULT '' AFTER `passengers`,
  ADD COLUMN IF NOT EXISTS `hotel_name` varchar(200) NOT NULL DEFAULT '' AFTER `company_name`,
  ADD COLUMN IF NOT EXISTS `customer_phone` varchar(50) NOT NULL DEFAULT '' AFTER `hotel_name`,
  ADD COLUMN IF NOT EXISTS `adults` int(11) NOT NULL DEFAULT 0 AFTER `customer_phone`,
  ADD COLUMN IF NOT EXISTS `children` int(11) NOT NULL DEFAULT 0 AFTER `adults`,
  ADD COLUMN IF NOT EXISTS `infants` int(11) NOT NULL DEFAULT 0 AFTER `children`,
  ADD COLUMN IF NOT EXISTS `customers` text NOT NULL DEFAULT '[]' AFTER `infants`,
  ADD COLUMN IF NOT EXISTS `tour_items` text NOT NULL DEFAULT '[]' AFTER `customers`,
  ADD COLUMN IF NOT EXISTS `company_id` int(11) UNSIGNED NOT NULL DEFAULT 0 AFTER `tour_items`,
  ADD COLUMN IF NOT EXISTS `partner_id` int(11) UNSIGNED NOT NULL DEFAULT 0 AFTER `company_id`,
  ADD COLUMN IF NOT EXISTS `guide_id` int(11) UNSIGNED NOT NULL DEFAULT 0 AFTER `partner_id`,
  ADD COLUMN IF NOT EXISTS `vehicle_id` int(11) UNSIGNED NOT NULL DEFAULT 0 AFTER `guide_id`,
  ADD COLUMN IF NOT EXISTS `driver_id` int(11) UNSIGNED NOT NULL DEFAULT 0 AFTER `vehicle_id`,
  ADD COLUMN IF NOT EXISTS `price_per_person` decimal(10,2) DEFAULT 0.00 AFTER `driver_id`,
  ADD COLUMN IF NOT EXISTS `total_price` decimal(10,2) DEFAULT 0.00 AFTER `price_per_person`,
  ADD COLUMN IF NOT EXISTS `currency` varchar(3) DEFAULT 'USD' AFTER `total_price`,
  ADD COLUMN IF NOT EXISTS `includes` text NOT NULL DEFAULT '' AFTER `currency`,
  ADD COLUMN IF NOT EXISTS `excludes` text NOT NULL DEFAULT '' AFTER `includes`,
  ADD COLUMN IF NOT EXISTS `itinerary` text NOT NULL DEFAULT '' AFTER `excludes`,
  ADD COLUMN IF NOT EXISTS `special_requests` text NOT NULL DEFAULT '' AFTER `itinerary`,
  ADD COLUMN IF NOT EXISTS `payment_status` enum('unpaid','partial','paid','refunded') DEFAULT 'unpaid' AFTER `status`,
  ADD COLUMN IF NOT EXISTS `notes` text NOT NULL DEFAULT '' AFTER `payment_status`,
  ADD COLUMN IF NOT EXISTS `created_by` int(11) UNSIGNED NOT NULL DEFAULT 0 AFTER `notes`,
  ADD COLUMN IF NOT EXISTS `updated_by` int(11) UNSIGNED NOT NULL DEFAULT 0 AFTER `created_by`,
  ADD COLUMN IF NOT EXISTS `price_child` decimal(10,2) DEFAULT 0.00 AFTER `price_per_person`,
  ADD COLUMN IF NOT EXISTS `price_per_infant` decimal(10,2) DEFAULT 0.00 AFTER `price_child`;

-- ============================================================
-- TABLE: invoices — add missing columns
-- ============================================================
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_no` varchar(60) NOT NULL,
  `company_name` varchar(120) NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date NOT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','sent','paid','overdue','cancelled','partial','pending') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `invoices`
  ADD COLUMN IF NOT EXISTS `company_id` int(11) UNSIGNED NOT NULL DEFAULT 0 AFTER `company_name`,
  ADD COLUMN IF NOT EXISTS `partner_id` int(11) UNSIGNED NOT NULL DEFAULT 0 AFTER `company_id`,
  ADD COLUMN IF NOT EXISTS `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00 AFTER `due_date`,
  ADD COLUMN IF NOT EXISTS `tax_rate` decimal(5,2) DEFAULT 0.00 AFTER `subtotal`,
  ADD COLUMN IF NOT EXISTS `tax_amount` decimal(12,2) DEFAULT 0.00 AFTER `tax_rate`,
  ADD COLUMN IF NOT EXISTS `discount` decimal(12,2) DEFAULT 0.00 AFTER `tax_amount`,
  ADD COLUMN IF NOT EXISTS `paid_amount` decimal(12,2) DEFAULT 0.00 AFTER `total_amount`,
  ADD COLUMN IF NOT EXISTS `currency` varchar(3) DEFAULT 'USD' AFTER `paid_amount`,
  ADD COLUMN IF NOT EXISTS `payment_method` varchar(50) NOT NULL DEFAULT '' AFTER `status`,
  ADD COLUMN IF NOT EXISTS `payment_date` date NOT NULL DEFAULT '1970-01-01' AFTER `payment_method`,
  ADD COLUMN IF NOT EXISTS `notes` text NOT NULL DEFAULT '' AFTER `payment_date`,
  ADD COLUMN IF NOT EXISTS `type` varchar(20) NOT NULL DEFAULT 'general' AFTER `notes`,
  ADD COLUMN IF NOT EXISTS `terms` text NOT NULL DEFAULT '' AFTER `type`,
  ADD COLUMN IF NOT EXISTS `file_path` varchar(255) NOT NULL DEFAULT '' AFTER `terms`,
  ADD COLUMN IF NOT EXISTS `sent_at` datetime NOT NULL DEFAULT current_timestamp() AFTER `file_path`,
  ADD COLUMN IF NOT EXISTS `sent_by` int(11) UNSIGNED NOT NULL DEFAULT 0 AFTER `sent_at`,
  ADD COLUMN IF NOT EXISTS `created_by` int(11) UNSIGNED NOT NULL DEFAULT 0 AFTER `sent_by`;

-- ============================================================
-- TABLE: invoice_items — create if missing
-- ============================================================
CREATE TABLE IF NOT EXISTS `invoice_items` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) UNSIGNED NOT NULL,
  `item_type` enum('voucher','tour','service','other') DEFAULT 'voucher',
  `item_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `description` text NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_invoice_items_invoice` (`invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: vouchers — add missing columns
-- ============================================================
ALTER TABLE `vouchers`
  ADD COLUMN IF NOT EXISTS `company_id` int(11) UNSIGNED NOT NULL DEFAULT 0 AFTER `company_name`,
  ADD COLUMN IF NOT EXISTS `partner_id` int(11) UNSIGNED NOT NULL DEFAULT 0 AFTER `company_id`,
  ADD COLUMN IF NOT EXISTS `flight_arrival_time` time NOT NULL DEFAULT '00:00:00' AFTER `flight_number`,
  ADD COLUMN IF NOT EXISTS `special_requests` text NOT NULL DEFAULT '' AFTER `guide_id`,
  ADD COLUMN IF NOT EXISTS `payment_status` enum('unpaid','partial','paid','refunded') DEFAULT 'unpaid' AFTER `status`,
  ADD COLUMN IF NOT EXISTS `created_by` int(11) UNSIGNED NOT NULL DEFAULT 0 AFTER `notes`,
  ADD COLUMN IF NOT EXISTS `updated_by` int(11) UNSIGNED NOT NULL DEFAULT 0 AFTER `created_by`;

-- ============================================================
-- TABLE: partners — add missing columns
-- ============================================================
ALTER TABLE `partners`
  ADD COLUMN IF NOT EXISTS `password` varchar(255) NOT NULL DEFAULT '' AFTER `email`,
  ADD COLUMN IF NOT EXISTS `mobile` varchar(20) NOT NULL DEFAULT '' AFTER `phone`,
  ADD COLUMN IF NOT EXISTS `postal_code` varchar(20) NOT NULL DEFAULT '' AFTER `country`,
  ADD COLUMN IF NOT EXISTS `commission_rate` decimal(5,2) DEFAULT 0.00 AFTER `tax_id`,
  ADD COLUMN IF NOT EXISTS `credit_limit` decimal(12,2) DEFAULT 0.00 AFTER `commission_rate`,
  ADD COLUMN IF NOT EXISTS `balance` decimal(12,2) DEFAULT 0.00 AFTER `credit_limit`,
  ADD COLUMN IF NOT EXISTS `payment_terms` int(11) DEFAULT 30 AFTER `balance`,
  ADD COLUMN IF NOT EXISTS `partner_type` enum('agency','hotel','supplier','other') DEFAULT 'agency' AFTER `payment_terms`,
  ADD COLUMN IF NOT EXISTS `contract_file` varchar(255) NOT NULL DEFAULT '' AFTER `notes`,
  ADD COLUMN IF NOT EXISTS `created_by` int(11) UNSIGNED NOT NULL DEFAULT 0 AFTER `contract_file`;

-- ============================================================
-- TABLE: services — create if missing
-- ============================================================
CREATE TABLE IF NOT EXISTS `services` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `service_type` enum('tour','transfer','hotel','other') DEFAULT 'tour',
  `name` varchar(150) NOT NULL,
  `description` text NOT NULL DEFAULT '',
  `price` decimal(10,2) DEFAULT 0.00,
  `currency` varchar(3) DEFAULT 'USD',
  `unit` varchar(50) DEFAULT 'per_person',
  `details` text NOT NULL DEFAULT '',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: hotels — create if missing
-- ============================================================
CREATE TABLE IF NOT EXISTS `hotels` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: hotel_rooms — create if missing
-- ============================================================
CREATE TABLE IF NOT EXISTS `hotel_rooms` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_hotel_rooms_hotel` (`hotel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: partner_booking_requests — fix AUTO_INCREMENT
-- ============================================================
ALTER TABLE `partner_booking_requests`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

-- ============================================================
-- TABLE: partner_messages — fix AUTO_INCREMENT
-- ============================================================
ALTER TABLE `partner_messages`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

-- ============================================================
-- TABLE: notifications — ensure exists + AUTO_INCREMENT
-- ============================================================
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_notifications_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: tour_assignments — ensure exists
-- ============================================================
CREATE TABLE IF NOT EXISTS `tour_assignments` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: voucher_services — link existing tours/transfers to hotel vouchers (Guest Program)
-- ============================================================
CREATE TABLE IF NOT EXISTS `voucher_services` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `voucher_id` int(11) UNSIGNED NOT NULL COMMENT 'hotel_vouchers.id',
  `service_type` enum('tour','transfer') NOT NULL,
  `reference_id` int(11) UNSIGNED NOT NULL COMMENT 'tours.id or vouchers.id',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_voucher_services_voucher` (`voucher_id`),
  KEY `idx_voucher_services_reference` (`service_type`,`reference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- DONE! All missing columns and tables added.
-- Run migration_fix_keys.sql AFTER this file if needed.
-- ============================================================
