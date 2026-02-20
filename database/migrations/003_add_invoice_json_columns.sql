-- Migration 003: Add hotels_json and guests_json columns to invoices table
-- Stores multi-hotel structure and guest list for hotel invoices

ALTER TABLE `invoices`
  ADD COLUMN IF NOT EXISTS `hotels_json` LONGTEXT NOT NULL DEFAULT '[]' COMMENT 'JSON: multi-hotel rooms structure for hotel invoices',
  ADD COLUMN IF NOT EXISTS `guests_json`  LONGTEXT NOT NULL DEFAULT '[]' COMMENT 'JSON: guest passenger list for hotel invoices';
