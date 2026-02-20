-- Migration 004: Currency System — currencies + exchange_rates tables
-- Run with: sqlite3 database/cyn_tourism.sqlite < database/migrations/004_create_currency_system.sql

-- Active currencies the company works with
CREATE TABLE IF NOT EXISTS currencies (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    code        TEXT    NOT NULL UNIQUE,           -- ISO 4217 code: USD, EUR, TRY, DZD
    name        TEXT    NOT NULL,                  -- Full name: US Dollar, Euro…
    symbol      TEXT    NOT NULL DEFAULT '',       -- $, €, ₺, DZD
    is_base     INTEGER NOT NULL DEFAULT 0,        -- 1 = company base/reporting currency (only one)
    is_active   INTEGER NOT NULL DEFAULT 1,
    created_at  TEXT    NOT NULL DEFAULT (datetime('now')),
    updated_at  TEXT    NOT NULL DEFAULT (datetime('now'))
);

-- Exchange rate history between currency pairs
-- effective_rate = market_rate * (1 + markup_percent / 100)
-- This is the rate actually applied to customer conversions
CREATE TABLE IF NOT EXISTS exchange_rates (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    from_currency   TEXT    NOT NULL,
    to_currency     TEXT    NOT NULL,
    market_rate     REAL    NOT NULL DEFAULT 1.0,   -- raw interbank rate
    markup_percent  REAL    NOT NULL DEFAULT 0.0,   -- company markup %
    effective_rate  REAL    NOT NULL DEFAULT 1.0,   -- market_rate * (1 + markup/100)
    valid_from      TEXT    NOT NULL,               -- DATE: rate is valid from this date
    valid_to        TEXT,                           -- DATE: null = currently active
    notes           TEXT    DEFAULT '',
    created_by      INTEGER DEFAULT 0,
    created_at      TEXT    NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX IF NOT EXISTS idx_exchange_rates_pair ON exchange_rates (from_currency, to_currency);
CREATE INDEX IF NOT EXISTS idx_exchange_rates_valid ON exchange_rates (valid_from, valid_to);

-- Seed default currencies
INSERT OR IGNORE INTO currencies (code, name, symbol, is_base, is_active) VALUES
    ('USD', 'US Dollar',        '$',   1, 1),
    ('EUR', 'Euro',             '€',   0, 1),
    ('TRY', 'Turkish Lira',     '₺',   0, 1),
    ('DZD', 'Algerian Dinar',   'DZD', 0, 1);

-- Seed representative exchange rates (from USD)
INSERT OR IGNORE INTO exchange_rates (from_currency, to_currency, market_rate, markup_percent, effective_rate, valid_from, valid_to, notes, created_by) VALUES
    ('USD', 'EUR', 0.9200, 1.5, 0.9338, date('now'), NULL, 'Seed rate', 1),
    ('USD', 'TRY', 32.50,  1.5, 32.9875, date('now'), NULL, 'Seed rate', 1),
    ('USD', 'DZD', 134.50, 1.5, 136.5175, date('now'), NULL, 'Seed rate', 1),
    ('EUR', 'USD', 1.0870, 1.5, 1.1033, date('now'), NULL, 'Seed rate', 1),
    ('EUR', 'TRY', 35.30,  1.5, 35.8295, date('now'), NULL, 'Seed rate', 1),
    ('EUR', 'DZD', 146.10, 1.5, 148.2915, date('now'), NULL, 'Seed rate', 1);
