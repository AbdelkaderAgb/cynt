-- CYN Tourism — Migration 001: Fix Missing Columns
-- STATUS: Already applied to database (all columns confirmed present as of 2026-02-18)
-- This file is kept as documentation of what was added.
-- Safe to run again — duplicate column errors are harmless (just pipe 2>/dev/null).
--
-- Run with: sqlite3 database/cyn_tourism.sqlite < database/migrations/001_fix_missing_columns.sql 2>/dev/null

-- vouchers: guest identity fields
ALTER TABLE vouchers ADD COLUMN guest_name TEXT DEFAULT '';
ALTER TABLE vouchers ADD COLUMN passenger_passport TEXT DEFAULT '';

-- missions: all operational fields
ALTER TABLE missions ADD COLUMN mission_type TEXT DEFAULT 'transfer';
ALTER TABLE missions ADD COLUMN reference_id INTEGER DEFAULT 0;
ALTER TABLE missions ADD COLUMN guest_name TEXT DEFAULT '';
ALTER TABLE missions ADD COLUMN guest_passport TEXT DEFAULT '';
ALTER TABLE missions ADD COLUMN pax_count INTEGER DEFAULT 0;
ALTER TABLE missions ADD COLUMN pickup_location TEXT DEFAULT '';
ALTER TABLE missions ADD COLUMN dropoff_location TEXT DEFAULT '';
ALTER TABLE missions ADD COLUMN created_by INTEGER DEFAULT 0;

-- quotations: all fields used by QuotationController
ALTER TABLE quotations ADD COLUMN quote_number TEXT DEFAULT '';
ALTER TABLE quotations ADD COLUMN partner_id INTEGER DEFAULT 0;
ALTER TABLE quotations ADD COLUMN client_name TEXT DEFAULT '';
ALTER TABLE quotations ADD COLUMN client_email TEXT DEFAULT '';
ALTER TABLE quotations ADD COLUMN client_phone TEXT DEFAULT '';
ALTER TABLE quotations ADD COLUMN travel_dates_from DATE DEFAULT NULL;
ALTER TABLE quotations ADD COLUMN travel_dates_to DATE DEFAULT NULL;
ALTER TABLE quotations ADD COLUMN adults INTEGER DEFAULT 0;
ALTER TABLE quotations ADD COLUMN children INTEGER DEFAULT 0;
ALTER TABLE quotations ADD COLUMN infants INTEGER DEFAULT 0;
ALTER TABLE quotations ADD COLUMN subtotal REAL DEFAULT 0;
ALTER TABLE quotations ADD COLUMN discount_percent REAL DEFAULT 0;
ALTER TABLE quotations ADD COLUMN discount_amount REAL DEFAULT 0;
ALTER TABLE quotations ADD COLUMN tax_percent REAL DEFAULT 0;
ALTER TABLE quotations ADD COLUMN tax_amount REAL DEFAULT 0;
ALTER TABLE quotations ADD COLUMN total REAL DEFAULT 0;
ALTER TABLE quotations ADD COLUMN cancellation_policy TEXT DEFAULT '';
ALTER TABLE quotations ADD COLUMN payment_terms TEXT DEFAULT '';
ALTER TABLE quotations ADD COLUMN created_by INTEGER DEFAULT 0;
ALTER TABLE quotations ADD COLUMN converted_at TEXT DEFAULT NULL;

-- quotation_items: extra fields
ALTER TABLE quotation_items ADD COLUMN day_number INTEGER DEFAULT 1;
ALTER TABLE quotation_items ADD COLUMN item_name TEXT DEFAULT '';
ALTER TABLE quotation_items ADD COLUMN currency TEXT DEFAULT 'USD';

-- group_files: all fields used by GroupFileController
ALTER TABLE group_files ADD COLUMN file_number TEXT DEFAULT '';
ALTER TABLE group_files ADD COLUMN partner_id INTEGER DEFAULT 0;
ALTER TABLE group_files ADD COLUMN leader_name TEXT DEFAULT '';
ALTER TABLE group_files ADD COLUMN leader_phone TEXT DEFAULT '';
ALTER TABLE group_files ADD COLUMN total_pax INTEGER DEFAULT 0;
ALTER TABLE group_files ADD COLUMN adults INTEGER DEFAULT 0;
ALTER TABLE group_files ADD COLUMN children INTEGER DEFAULT 0;
ALTER TABLE group_files ADD COLUMN infants INTEGER DEFAULT 0;
ALTER TABLE group_files ADD COLUMN created_by INTEGER DEFAULT 0;

-- group_file_items: extra fields
ALTER TABLE group_file_items ADD COLUMN day_number INTEGER DEFAULT 1;
ALTER TABLE group_file_items ADD COLUMN reference_id INTEGER DEFAULT 0;

-- tax_rates: country and applies_to
ALTER TABLE tax_rates ADD COLUMN country TEXT DEFAULT '';
ALTER TABLE tax_rates ADD COLUMN applies_to TEXT DEFAULT 'all';

-- invoice_items: service catalog link
ALTER TABLE invoice_items ADD COLUMN service_id INTEGER DEFAULT NULL;
ALTER TABLE invoice_items ADD COLUMN unit_type TEXT DEFAULT 'per_person';

-- partner_booking_requests: infants and hotel_id
ALTER TABLE partner_booking_requests ADD COLUMN infants INTEGER DEFAULT 0;
ALTER TABLE partner_booking_requests ADD COLUMN hotel_id INTEGER DEFAULT NULL;
