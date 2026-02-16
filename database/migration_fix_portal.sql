-- =============================================================
-- CYN Tourism — Database Migration: Fix Missing Columns & Enums
-- Run this on your production database via phpMyAdmin or CLI
-- =============================================================

-- 1. Add 'hotel' to services.service_type enum
ALTER TABLE `services` 
MODIFY COLUMN `service_type` ENUM('tour','transfer','hotel','other') DEFAULT 'tour';

-- 2. Verify partner_booking_requests status enum is correct
-- (Should be 'pending','approved','rejected')
-- If your enum was accidentally set to 'confirmed', fix it:
-- ALTER TABLE `partner_booking_requests` 
-- MODIFY COLUMN `status` ENUM('pending','approved','rejected') DEFAULT 'pending';

-- 3. Add some sample hotel services (optional — run if you want hotel services in the booking form)
INSERT INTO `services` (`service_type`, `name`, `description`, `price`, `currency`, `unit`, `status`) VALUES
('hotel', 'Grand Star Hotel — Standard', 'Standard Double Room, Bed & Breakfast', 85.00, 'USD', 'per_night', 'active'),
('hotel', 'Grand Star Hotel — Deluxe', 'Deluxe Room, Bed & Breakfast', 120.00, 'USD', 'per_night', 'active'),
('hotel', 'Sultanahmet Palace Hotel — Standard', 'Standard Twin Room, Bed & Breakfast', 100.00, 'EUR', 'per_night', 'active'),
('hotel', 'Taksim Deluxe Suites — Superior', 'Superior Double, Half Board', 95.00, 'USD', 'per_night', 'active'),
('hotel', 'Bosphorus View Hotel — Sea View', 'Sea View Suite, Breakfast Included', 150.00, 'USD', 'per_night', 'active');

-- Done!
