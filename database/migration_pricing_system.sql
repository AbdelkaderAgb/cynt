-- ═══════════════════════════════════════════════════════════════
-- CYN Tourism — Centralized Pricing System Migration
-- Enhances services table and invoice_items for price catalog integration
-- All statements are idempotent (IF NOT EXISTS / column checks)
-- ═══════════════════════════════════════════════════════════════

-- ---------------------------------------------------------------
-- 1. Enhance services table with granular pricing fields
-- ---------------------------------------------------------------

SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'services' AND COLUMN_NAME = 'price_adult');
SET @sql = IF(@col = 0, 
    'ALTER TABLE `services` ADD COLUMN `price_adult` DECIMAL(10,2) DEFAULT 0 AFTER `price`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'services' AND COLUMN_NAME = 'price_child');
SET @sql = IF(@col = 0, 
    'ALTER TABLE `services` ADD COLUMN `price_child` DECIMAL(10,2) DEFAULT 0 AFTER `price_adult`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'services' AND COLUMN_NAME = 'price_infant');
SET @sql = IF(@col = 0, 
    'ALTER TABLE `services` ADD COLUMN `price_infant` DECIMAL(10,2) DEFAULT 0 AFTER `price_child`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'services' AND COLUMN_NAME = 'destination');
SET @sql = IF(@col = 0, 
    'ALTER TABLE `services` ADD COLUMN `destination` VARCHAR(200) DEFAULT \'\' AFTER `details`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'services' AND COLUMN_NAME = 'duration');
SET @sql = IF(@col = 0, 
    'ALTER TABLE `services` ADD COLUMN `duration` VARCHAR(100) DEFAULT \'\' AFTER `destination`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'services' AND COLUMN_NAME = 'vehicle_type');
SET @sql = IF(@col = 0, 
    'ALTER TABLE `services` ADD COLUMN `vehicle_type` VARCHAR(100) DEFAULT \'\' AFTER `duration`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'services' AND COLUMN_NAME = 'max_pax');
SET @sql = IF(@col = 0, 
    'ALTER TABLE `services` ADD COLUMN `max_pax` INT DEFAULT 0 AFTER `vehicle_type`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'services' AND COLUMN_NAME = 'pickup_location');
SET @sql = IF(@col = 0, 
    'ALTER TABLE `services` ADD COLUMN `pickup_location` VARCHAR(255) DEFAULT \'\' AFTER `max_pax`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'services' AND COLUMN_NAME = 'dropoff_location');
SET @sql = IF(@col = 0, 
    'ALTER TABLE `services` ADD COLUMN `dropoff_location` VARCHAR(255) DEFAULT \'\' AFTER `pickup_location`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------
-- 2. Enhance invoice_items with service linkage and pricing context
-- ---------------------------------------------------------------

SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoice_items' AND COLUMN_NAME = 'service_id');
SET @sql = IF(@col = 0, 
    'ALTER TABLE `invoice_items` ADD COLUMN `service_id` INT UNSIGNED DEFAULT NULL AFTER `item_id`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoice_items' AND COLUMN_NAME = 'check_in');
SET @sql = IF(@col = 0, 
    'ALTER TABLE `invoice_items` ADD COLUMN `check_in` DATE DEFAULT NULL AFTER `service_id`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoice_items' AND COLUMN_NAME = 'check_out');
SET @sql = IF(@col = 0, 
    'ALTER TABLE `invoice_items` ADD COLUMN `check_out` DATE DEFAULT NULL AFTER `check_in`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoice_items' AND COLUMN_NAME = 'nights');
SET @sql = IF(@col = 0, 
    'ALTER TABLE `invoice_items` ADD COLUMN `nights` INT DEFAULT 0 AFTER `check_out`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoice_items' AND COLUMN_NAME = 'adults');
SET @sql = IF(@col = 0, 
    'ALTER TABLE `invoice_items` ADD COLUMN `adults` INT DEFAULT 0 AFTER `nights`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoice_items' AND COLUMN_NAME = 'children_count');
SET @sql = IF(@col = 0, 
    'ALTER TABLE `invoice_items` ADD COLUMN `children_count` INT DEFAULT 0 AFTER `adults`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoice_items' AND COLUMN_NAME = 'infants');
SET @sql = IF(@col = 0, 
    'ALTER TABLE `invoice_items` ADD COLUMN `infants` INT DEFAULT 0 AFTER `children_count`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoice_items' AND COLUMN_NAME = 'unit_type');
SET @sql = IF(@col = 0, 
    'ALTER TABLE `invoice_items` ADD COLUMN `unit_type` VARCHAR(50) DEFAULT \'flat\' AFTER `infants`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoice_items' AND COLUMN_NAME = 'base_price');
SET @sql = IF(@col = 0, 
    'ALTER TABLE `invoice_items` ADD COLUMN `base_price` DECIMAL(10,2) DEFAULT 0 AFTER `unit_type`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoice_items' AND COLUMN_NAME = 'season_multiplier');
SET @sql = IF(@col = 0, 
    'ALTER TABLE `invoice_items` ADD COLUMN `season_multiplier` DECIMAL(5,2) DEFAULT 1.00 AFTER `base_price`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ═══════════════════════════════════════════════════════════════
-- END Centralized Pricing System Migration
-- ═══════════════════════════════════════════════════════════════
