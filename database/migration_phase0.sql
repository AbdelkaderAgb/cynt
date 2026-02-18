-- ═══════════════════════════════════════════════════════════════
-- CYN Tourism — Phase 0 Migration
-- Passport columns, board pricing, location fields, services pricing
-- Run against MySQL/MariaDB — all statements are idempotent (IF NOT EXISTS)
-- ═══════════════════════════════════════════════════════════════

-- ---------------------------------------------------------------
-- 0.1 Passport columns on all voucher types
-- ---------------------------------------------------------------

-- Hotel vouchers
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hotel_vouchers' AND COLUMN_NAME = 'passenger_passport');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `hotel_vouchers` ADD COLUMN `passenger_passport` VARCHAR(50) NOT NULL DEFAULT \'\' AFTER `guest_name`', 
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Tours
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tours' AND COLUMN_NAME = 'passenger_passport');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `tours` ADD COLUMN `passenger_passport` VARCHAR(50) NOT NULL DEFAULT \'\' AFTER `tour_name`', 
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Transfer vouchers
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'vouchers' AND COLUMN_NAME = 'passenger_passport');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `vouchers` ADD COLUMN `passenger_passport` VARCHAR(50) NOT NULL DEFAULT \'\' AFTER `company_name`', 
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Client portal token on hotel_vouchers
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hotel_vouchers' AND COLUMN_NAME = 'client_portal_token');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `hotel_vouchers` ADD COLUMN `client_portal_token` VARCHAR(64) DEFAULT NULL, ADD COLUMN `client_portal_enabled` TINYINT(1) DEFAULT 0', 
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------
-- 0.2 Hotel room capacity fields
-- ---------------------------------------------------------------

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hotel_rooms' AND COLUMN_NAME = 'max_adults');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `hotel_rooms` ADD COLUMN `max_adults` INT NOT NULL DEFAULT 2 AFTER `capacity`, ADD COLUMN `max_children` INT NOT NULL DEFAULT 1 AFTER `max_adults`', 
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Hotel child/infant age settings
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hotels' AND COLUMN_NAME = 'child_age_min');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `hotels` ADD COLUMN `child_age_min` INT NOT NULL DEFAULT 2, ADD COLUMN `child_age_max` INT NOT NULL DEFAULT 12, ADD COLUMN `infant_age_max` INT NOT NULL DEFAULT 2', 
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Hotel lat/lng
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hotels' AND COLUMN_NAME = 'latitude');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `hotels` ADD COLUMN `latitude` DECIMAL(10,7) DEFAULT NULL, ADD COLUMN `longitude` DECIMAL(10,7) DEFAULT NULL', 
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------
-- 0.3 Room board prices table
-- ---------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `room_board_prices` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `room_id` INT UNSIGNED NOT NULL,
    `board_type` ENUM('RO','BB','HB','FB','AI') NOT NULL DEFAULT 'BB',
    `price_single` DECIMAL(10,2) DEFAULT 0,
    `price_double` DECIMAL(10,2) DEFAULT 0,
    `price_triple` DECIMAL(10,2) DEFAULT 0,
    `price_quad` DECIMAL(10,2) DEFAULT 0,
    `price_child` DECIMAL(10,2) DEFAULT 0,
    `currency` VARCHAR(3) DEFAULT 'USD',
    UNIQUE KEY `uq_room_board` (`room_id`, `board_type`),
    CONSTRAINT `fk_rbp_room` FOREIGN KEY (`room_id`) REFERENCES `hotel_rooms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
-- 0.4 Services pricing — adult/child/infant
-- ---------------------------------------------------------------

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'services' AND COLUMN_NAME = 'price_adult');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `services` ADD COLUMN `price_adult` DECIMAL(10,2) DEFAULT 0 AFTER `price`, ADD COLUMN `price_child` DECIMAL(10,2) DEFAULT 0 AFTER `price_adult`, ADD COLUMN `price_infant` DECIMAL(10,2) DEFAULT 0 AFTER `price_child`', 
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------
-- 0.5 Tour location fields (if missing)
-- ---------------------------------------------------------------

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tours' AND COLUMN_NAME = 'city');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `tours` ADD COLUMN `city` VARCHAR(100) NOT NULL DEFAULT \'\' AFTER `destination`, ADD COLUMN `country` VARCHAR(100) NOT NULL DEFAULT \'Turkey\' AFTER `city`, ADD COLUMN `address` VARCHAR(500) NOT NULL DEFAULT \'\' AFTER `country`', 
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tours' AND COLUMN_NAME = 'meeting_point');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `tours` ADD COLUMN `meeting_point` VARCHAR(255) NOT NULL DEFAULT \'\', ADD COLUMN `meeting_point_address` VARCHAR(500) NOT NULL DEFAULT \'\', ADD COLUMN `duration_hours` DECIMAL(5,2) DEFAULT 0, ADD COLUMN `latitude` DECIMAL(10,7) DEFAULT NULL, ADD COLUMN `longitude` DECIMAL(10,7) DEFAULT NULL', 
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Tour includes/excludes
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tours' AND COLUMN_NAME = 'includes');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `tours` ADD COLUMN `includes` TEXT DEFAULT NULL, ADD COLUMN `excludes` TEXT DEFAULT NULL', 
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Guest name on tours (for passport linkage)
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tours' AND COLUMN_NAME = 'guest_name');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `tours` ADD COLUMN `guest_name` VARCHAR(200) NOT NULL DEFAULT \'\' AFTER `tour_name`', 
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------
-- 0.6 Transfer (vouchers table) location fields
-- ---------------------------------------------------------------

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'vouchers' AND COLUMN_NAME = 'pickup_city');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `vouchers` ADD COLUMN `pickup_city` VARCHAR(100) NOT NULL DEFAULT \'\' AFTER `pickup_location`, ADD COLUMN `pickup_country` VARCHAR(100) NOT NULL DEFAULT \'Turkey\' AFTER `pickup_city`, ADD COLUMN `dropoff_city` VARCHAR(100) NOT NULL DEFAULT \'\' AFTER `dropoff_location`, ADD COLUMN `dropoff_country` VARCHAR(100) NOT NULL DEFAULT \'Turkey\' AFTER `dropoff_city`', 
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'vouchers' AND COLUMN_NAME = 'estimated_duration_min');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `vouchers` ADD COLUMN `estimated_duration_min` INT DEFAULT 0, ADD COLUMN `distance_km` DECIMAL(8,2) DEFAULT 0, ADD COLUMN `description` TEXT NOT NULL DEFAULT \'\'', 
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Guest name on transfers
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'vouchers' AND COLUMN_NAME = 'guest_name');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `vouchers` ADD COLUMN `guest_name` VARCHAR(200) NOT NULL DEFAULT \'\' AFTER `voucher_no`', 
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------
-- 0.7 Markup & Commission fields on hotel_vouchers
-- ---------------------------------------------------------------

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hotel_vouchers' AND COLUMN_NAME = 'cost_price');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `hotel_vouchers` ADD COLUMN `cost_price` DECIMAL(10,2) DEFAULT 0 AFTER `total_price`, ADD COLUMN `markup_percent` DECIMAL(5,2) DEFAULT 0 AFTER `cost_price`, ADD COLUMN `selling_price` DECIMAL(10,2) DEFAULT 0 AFTER `markup_percent`, ADD COLUMN `commission_percent` DECIMAL(5,2) DEFAULT 0 AFTER `selling_price`, ADD COLUMN `commission_amount` DECIMAL(10,2) DEFAULT 0 AFTER `commission_percent`', 
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Markup on tours
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tours' AND COLUMN_NAME = 'cost_price');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `tours` ADD COLUMN `cost_price` DECIMAL(10,2) DEFAULT 0 AFTER `total_price`, ADD COLUMN `markup_percent` DECIMAL(5,2) DEFAULT 0 AFTER `cost_price`, ADD COLUMN `selling_price` DECIMAL(10,2) DEFAULT 0 AFTER `markup_percent`', 
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------
-- 0.8 Phase 2 tables (created now so controllers can reference them)
-- ---------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `pricing_seasons` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `hotel_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `date_from` DATE NOT NULL,
    `date_to` DATE NOT NULL,
    `multiplier` DECIMAL(5,2) DEFAULT 1.00,
    `is_blackout` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pricing_rates` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `season_id` INT UNSIGNED NOT NULL,
    `room_type_id` INT UNSIGNED NOT NULL,
    `price_single` DECIMAL(10,2) DEFAULT 0,
    `price_double` DECIMAL(10,2) DEFAULT 0,
    `price_triple` DECIMAL(10,2) DEFAULT 0,
    `price_quad` DECIMAL(10,2) DEFAULT 0,
    `price_child` DECIMAL(10,2) DEFAULT 0,
    `currency` VARCHAR(3) DEFAULT 'USD',
    FOREIGN KEY (`season_id`) REFERENCES `pricing_seasons`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`room_type_id`) REFERENCES `hotel_rooms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `allotments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `hotel_id` INT UNSIGNED NOT NULL,
    `room_type_id` INT UNSIGNED NOT NULL,
    `date_from` DATE NOT NULL,
    `date_to` DATE NOT NULL,
    `total_rooms` INT NOT NULL DEFAULT 0,
    `used_rooms` INT NOT NULL DEFAULT 0,
    `release_days` INT NOT NULL DEFAULT 7,
    `status` ENUM('active','released','expired') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`hotel_id`) REFERENCES `hotels`(`id`),
    FOREIGN KEY (`room_type_id`) REFERENCES `hotel_rooms`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rooming_list` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `voucher_id` INT UNSIGNED NOT NULL,
    `guest_name` VARCHAR(200) NOT NULL,
    `passport_no` VARCHAR(50) DEFAULT '',
    `nationality` VARCHAR(100) DEFAULT '',
    `room_number` VARCHAR(20) DEFAULT '',
    `room_type` VARCHAR(100) DEFAULT '',
    `gender` ENUM('male','female','child') DEFAULT 'male',
    `age_category` ENUM('adult','child','infant') DEFAULT 'adult',
    `special_requests` TEXT DEFAULT '',
    `sort_order` INT DEFAULT 0,
    FOREIGN KEY (`voucher_id`) REFERENCES `hotel_vouchers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `missions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `mission_type` ENUM('tour','transfer','hotel_service') NOT NULL,
    `reference_id` INT UNSIGNED NOT NULL,
    `driver_id` INT UNSIGNED DEFAULT NULL,
    `guide_id` INT UNSIGNED DEFAULT NULL,
    `vehicle_id` INT UNSIGNED DEFAULT NULL,
    `mission_date` DATE NOT NULL,
    `start_time` TIME DEFAULT NULL,
    `end_time` TIME DEFAULT NULL,
    `pickup_location` VARCHAR(255) DEFAULT '',
    `dropoff_location` VARCHAR(255) DEFAULT '',
    `pax_count` INT DEFAULT 0,
    `guest_name` VARCHAR(200) DEFAULT '',
    `guest_passport` VARCHAR(50) DEFAULT '',
    `status` ENUM('pending','assigned','in_progress','completed','cancelled') DEFAULT 'pending',
    `notes` TEXT DEFAULT '',
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`driver_id`) REFERENCES `drivers`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`guide_id`) REFERENCES `tour_guides`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
-- 0.9 Phase 3 tables
-- ---------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `quotations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `quote_number` VARCHAR(50) NOT NULL UNIQUE,
    `partner_id` INT UNSIGNED DEFAULT NULL,
    `client_name` VARCHAR(200) NOT NULL,
    `client_email` VARCHAR(255) DEFAULT '',
    `client_phone` VARCHAR(50) DEFAULT '',
    `travel_dates_from` DATE DEFAULT NULL,
    `travel_dates_to` DATE DEFAULT NULL,
    `adults` INT DEFAULT 0,
    `children` INT DEFAULT 0,
    `infants` INT DEFAULT 0,
    `subtotal` DECIMAL(10,2) DEFAULT 0,
    `discount_percent` DECIMAL(5,2) DEFAULT 0,
    `discount_amount` DECIMAL(10,2) DEFAULT 0,
    `tax_percent` DECIMAL(5,2) DEFAULT 0,
    `tax_amount` DECIMAL(10,2) DEFAULT 0,
    `total` DECIMAL(10,2) DEFAULT 0,
    `currency` VARCHAR(3) DEFAULT 'USD',
    `valid_until` DATE DEFAULT NULL,
    `cancellation_policy` TEXT DEFAULT '',
    `payment_terms` TEXT DEFAULT '',
    `notes` TEXT DEFAULT '',
    `status` ENUM('draft','sent','accepted','rejected','expired','converted') DEFAULT 'draft',
    `converted_at` DATETIME DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `quotation_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `quotation_id` INT UNSIGNED NOT NULL,
    `day_number` INT DEFAULT 1,
    `item_type` ENUM('hotel','tour','transfer','other') NOT NULL,
    `item_name` VARCHAR(200) NOT NULL,
    `description` TEXT DEFAULT '',
    `quantity` INT DEFAULT 1,
    `unit_price` DECIMAL(10,2) DEFAULT 0,
    `total_price` DECIMAL(10,2) DEFAULT 0,
    `currency` VARCHAR(3) DEFAULT 'USD',
    `sort_order` INT DEFAULT 0,
    FOREIGN KEY (`quotation_id`) REFERENCES `quotations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `group_files` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `file_number` VARCHAR(50) NOT NULL UNIQUE,
    `group_name` VARCHAR(200) NOT NULL,
    `partner_id` INT UNSIGNED DEFAULT NULL,
    `leader_name` VARCHAR(200) DEFAULT '',
    `leader_phone` VARCHAR(50) DEFAULT '',
    `arrival_date` DATE DEFAULT NULL,
    `departure_date` DATE DEFAULT NULL,
    `total_pax` INT DEFAULT 0,
    `adults` INT DEFAULT 0,
    `children` INT DEFAULT 0,
    `infants` INT DEFAULT 0,
    `status` ENUM('planning','confirmed','in_progress','completed','cancelled') DEFAULT 'planning',
    `notes` TEXT DEFAULT '',
    `client_portal_token` VARCHAR(64) DEFAULT NULL,
    `client_portal_enabled` TINYINT(1) DEFAULT 0,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `group_file_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `group_file_id` INT UNSIGNED NOT NULL,
    `item_type` ENUM('hotel_voucher','tour','transfer') NOT NULL,
    `reference_id` INT UNSIGNED NOT NULL,
    `day_number` INT DEFAULT 1,
    `sort_order` INT DEFAULT 0,
    `notes` TEXT DEFAULT '',
    FOREIGN KEY (`group_file_id`) REFERENCES `group_files`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `credit_notes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `credit_note_no` VARCHAR(50) NOT NULL UNIQUE,
    `invoice_id` INT UNSIGNED DEFAULT NULL,
    `partner_id` INT UNSIGNED DEFAULT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `currency` VARCHAR(3) DEFAULT 'USD',
    `reason` TEXT DEFAULT '',
    `status` ENUM('draft','issued','applied') DEFAULT 'draft',
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tax_rates` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `rate` DECIMAL(5,2) NOT NULL,
    `country` VARCHAR(100) DEFAULT '',
    `applies_to` ENUM('all','hotel','tour','transfer') DEFAULT 'all',
    `is_default` TINYINT(1) DEFAULT 0,
    `status` ENUM('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
-- 0.10 Phase 4 tables
-- ---------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `reviews` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `review_token` VARCHAR(64) NOT NULL UNIQUE,
    `booking_type` ENUM('hotel','tour','transfer') NOT NULL,
    `booking_id` INT UNSIGNED NOT NULL,
    `guest_name` VARCHAR(200) DEFAULT '',
    `rating` INT NOT NULL DEFAULT 5,
    `comment` TEXT DEFAULT '',
    `submitted_at` TIMESTAMP DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
-- 0.11 Phase 6 tables
-- ---------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `notification_rules` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `event_type` VARCHAR(100) NOT NULL,
    `channel` ENUM('email','whatsapp','system','all') DEFAULT 'system',
    `template` TEXT NOT NULL,
    `delay_minutes` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
-- 0.12 Phase 8 tables
-- ---------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `cancellation_policies` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `rules` TEXT NOT NULL,
    `applies_to` ENUM('hotel','tour','transfer','all') DEFAULT 'all',
    `is_default` TINYINT(1) DEFAULT 0,
    `status` ENUM('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══════════════════════════════════════════════════════════════
-- END Phase 0 Migration
-- ═══════════════════════════════════════════════════════════════
