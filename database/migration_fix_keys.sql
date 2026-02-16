-- ============================================================
-- CYN Tourism — Migration: Fix Missing PRIMARY KEYs & AUTO_INCREMENT
-- 
-- Run this on your Namecheap phpMyAdmin to fix all missing
-- primary keys and auto_increment that were omitted from seed.sql.
-- This is SAFE to run on an existing database — it uses IF NOT EXISTS
-- and checks before altering.
-- 
-- Date: 2026-02-12
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- Table: drivers — Add PRIMARY KEY + AUTO_INCREMENT
-- --------------------------------------------------------
ALTER TABLE `drivers`
  ADD PRIMARY KEY IF NOT EXISTS (`id`);

ALTER TABLE `drivers`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------
-- Table: email_config — Add PRIMARY KEY + AUTO_INCREMENT
-- --------------------------------------------------------
ALTER TABLE `email_config`
  ADD PRIMARY KEY IF NOT EXISTS (`id`);

ALTER TABLE `email_config`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------
-- Table: hotel_vouchers — Add PRIMARY KEY + AUTO_INCREMENT
-- --------------------------------------------------------
ALTER TABLE `hotel_vouchers`
  ADD PRIMARY KEY IF NOT EXISTS (`id`);

ALTER TABLE `hotel_vouchers`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------
-- Table: invoices — Add PRIMARY KEY + AUTO_INCREMENT
-- --------------------------------------------------------
ALTER TABLE `invoices`
  ADD PRIMARY KEY IF NOT EXISTS (`id`);

ALTER TABLE `invoices`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------
-- Table: invoice_items — Add PRIMARY KEY + AUTO_INCREMENT
-- --------------------------------------------------------
-- invoice_items may have id=0 for some rows in seed data.
-- We must give them unique IDs before adding a PRIMARY KEY.
-- Step 1: Temporarily allow 0 values, assign unique IDs to id=0 rows
SET @max_ii = (SELECT COALESCE(MAX(`id`), 0) FROM `invoice_items`);
UPDATE `invoice_items` SET `id` = (@max_ii := @max_ii + 1) WHERE `id` = 0;

-- Step 2: Add PRIMARY KEY (now all IDs are unique)
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY IF NOT EXISTS (`id`);

-- Step 3: Add AUTO_INCREMENT (requires PRIMARY KEY to exist first)
ALTER TABLE `invoice_items`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `invoice_items`
  ADD KEY IF NOT EXISTS `idx_invoice_items_invoice` (`invoice_id`);

-- --------------------------------------------------------
-- Table: login_attempts — Add PRIMARY KEY + AUTO_INCREMENT
-- --------------------------------------------------------
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY IF NOT EXISTS (`id`);

ALTER TABLE `login_attempts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------
-- Table: notifications — Add PRIMARY KEY + AUTO_INCREMENT
-- --------------------------------------------------------
ALTER TABLE `notifications`
  ADD PRIMARY KEY IF NOT EXISTS (`id`);

ALTER TABLE `notifications`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------
-- Table: password_resets — Add PRIMARY KEY + AUTO_INCREMENT
-- --------------------------------------------------------
ALTER TABLE `password_resets`
  ADD PRIMARY KEY IF NOT EXISTS (`id`);

ALTER TABLE `password_resets`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------
-- Table: reminder_logs — Add PRIMARY KEY + AUTO_INCREMENT
-- --------------------------------------------------------
ALTER TABLE `reminder_logs`
  ADD PRIMARY KEY IF NOT EXISTS (`id`);

ALTER TABLE `reminder_logs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------
-- Table: services — Add PRIMARY KEY + AUTO_INCREMENT
-- --------------------------------------------------------
-- services may have id=0 for some rows in seed data.
-- Step 1: Assign unique IDs to id=0 rows
SET @max_svc = (SELECT COALESCE(MAX(`id`), 0) FROM `services`);
UPDATE `services` SET `id` = (@max_svc := @max_svc + 1) WHERE `id` = 0;

-- Step 2: Add PRIMARY KEY (now all IDs are unique)
ALTER TABLE `services`
  ADD PRIMARY KEY IF NOT EXISTS (`id`);

-- Step 3: Add AUTO_INCREMENT
ALTER TABLE `services`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------
-- Table: settings — Add PRIMARY KEY + AUTO_INCREMENT
-- --------------------------------------------------------
ALTER TABLE `settings`
  ADD PRIMARY KEY IF NOT EXISTS (`id`);

ALTER TABLE `settings`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `settings`
  ADD UNIQUE KEY IF NOT EXISTS `setting_key` (`setting_key`);

-- --------------------------------------------------------
-- Table: tours — Add PRIMARY KEY + AUTO_INCREMENT
-- --------------------------------------------------------
ALTER TABLE `tours`
  ADD PRIMARY KEY IF NOT EXISTS (`id`);

ALTER TABLE `tours`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------
-- Table: tour_assignments — Add PRIMARY KEY + AUTO_INCREMENT
-- --------------------------------------------------------
ALTER TABLE `tour_assignments`
  ADD PRIMARY KEY IF NOT EXISTS (`id`);

ALTER TABLE `tour_assignments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------
-- Table: tour_guides — Add PRIMARY KEY + AUTO_INCREMENT
-- --------------------------------------------------------
ALTER TABLE `tour_guides`
  ADD PRIMARY KEY IF NOT EXISTS (`id`);

ALTER TABLE `tour_guides`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------
-- Table: users — Add PRIMARY KEY + AUTO_INCREMENT + UNIQUE email
-- --------------------------------------------------------
ALTER TABLE `users`
  ADD PRIMARY KEY IF NOT EXISTS (`id`);

ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
  ADD UNIQUE KEY IF NOT EXISTS `email` (`email`);

-- --------------------------------------------------------
-- Table: vehicles — Add PRIMARY KEY + AUTO_INCREMENT
-- --------------------------------------------------------
ALTER TABLE `vehicles`
  ADD PRIMARY KEY IF NOT EXISTS (`id`);

ALTER TABLE `vehicles`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `vehicles`
  ADD UNIQUE KEY IF NOT EXISTS `plate_number` (`plate_number`);

-- --------------------------------------------------------
-- Table: vouchers — Add PRIMARY KEY + AUTO_INCREMENT
-- --------------------------------------------------------
ALTER TABLE `vouchers`
  ADD PRIMARY KEY IF NOT EXISTS (`id`);

ALTER TABLE `vouchers`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------
-- Add missing indexes for performance
-- --------------------------------------------------------
ALTER TABLE `invoices`
  ADD KEY IF NOT EXISTS `idx_invoices_status` (`status`),
  ADD KEY IF NOT EXISTS `idx_invoices_company` (`company_name`),
  ADD KEY IF NOT EXISTS `idx_invoices_date` (`invoice_date`),
  ADD KEY IF NOT EXISTS `idx_invoices_company_id` (`company_id`);

ALTER TABLE `vouchers`
  ADD KEY IF NOT EXISTS `idx_vouchers_status` (`status`),
  ADD KEY IF NOT EXISTS `idx_vouchers_company` (`company_name`),
  ADD KEY IF NOT EXISTS `idx_vouchers_date` (`pickup_date`),
  ADD KEY IF NOT EXISTS `idx_vouchers_company_id` (`company_id`);

ALTER TABLE `hotel_vouchers`
  ADD KEY IF NOT EXISTS `idx_hotel_vouchers_status` (`status`),
  ADD KEY IF NOT EXISTS `idx_hotel_vouchers_company` (`company_name`),
  ADD KEY IF NOT EXISTS `idx_hotel_vouchers_checkin` (`check_in`),
  ADD KEY IF NOT EXISTS `idx_hotel_vouchers_company_id` (`company_id`);

ALTER TABLE `tours`
  ADD KEY IF NOT EXISTS `idx_tours_status` (`status`),
  ADD KEY IF NOT EXISTS `idx_tours_company` (`company_name`),
  ADD KEY IF NOT EXISTS `idx_tours_date` (`tour_date`),
  ADD KEY IF NOT EXISTS `idx_tours_company_id` (`company_id`);

ALTER TABLE `notifications`
  ADD KEY IF NOT EXISTS `idx_notifications_user` (`user_id`),
  ADD KEY IF NOT EXISTS `idx_notifications_read` (`is_read`);

ALTER TABLE `partner_booking_requests`
  ADD KEY IF NOT EXISTS `idx_pbr_partner` (`partner_id`),
  ADD KEY IF NOT EXISTS `idx_pbr_status` (`status`);

ALTER TABLE `partner_messages`
  ADD KEY IF NOT EXISTS `idx_pm_partner` (`partner_id`),
  ADD KEY IF NOT EXISTS `idx_pm_read` (`is_read`);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- DONE! All primary keys, auto_increments, and indexes fixed.
-- ============================================================
