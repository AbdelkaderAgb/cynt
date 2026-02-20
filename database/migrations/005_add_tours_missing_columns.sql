-- Migration 005: Add missing columns to tours table
-- TourController references these columns but they are absent from seed.sql CREATE TABLE.
-- They may already exist in the live SQLite DB (added previously outside of migrations).
-- Safe to re-run: SQLite ALTER TABLE ADD COLUMN is harmless if column already exists (pipe 2>/dev/null).
--
-- Run with: sqlite3 database/cyn_tourism.sqlite < database/migrations/005_add_tours_missing_columns.sql 2>/dev/null

ALTER TABLE tours ADD COLUMN guest_name TEXT DEFAULT '';
ALTER TABLE tours ADD COLUMN passenger_passport TEXT DEFAULT '';
ALTER TABLE tours ADD COLUMN city TEXT DEFAULT '';
ALTER TABLE tours ADD COLUMN country TEXT DEFAULT 'Turkey';
ALTER TABLE tours ADD COLUMN address TEXT DEFAULT '';
ALTER TABLE tours ADD COLUMN meeting_point TEXT DEFAULT '';
ALTER TABLE tours ADD COLUMN meeting_point_address TEXT DEFAULT '';
ALTER TABLE tours ADD COLUMN duration_hours REAL DEFAULT 0;
