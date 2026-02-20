-- Migration: Partner Credit Transactions ledger
-- Creates the credit_transactions table for tracking partner balance recharges and invoice payments

CREATE TABLE IF NOT EXISTS credit_transactions (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    partner_id     INTEGER NOT NULL,
    type           TEXT    NOT NULL DEFAULT 'recharge',  -- 'recharge' | 'payment' | 'refund' | 'adjustment'
    amount         REAL    NOT NULL DEFAULT 0,            -- always a positive value
    currency       TEXT    NOT NULL DEFAULT 'EUR',
    description    TEXT,
    ref_type       TEXT,     -- 'invoice' | 'manual'
    ref_id         INTEGER,  -- invoice.id when type = 'payment'
    balance_before REAL    NOT NULL DEFAULT 0,
    balance_after  REAL    NOT NULL DEFAULT 0,
    created_by     INTEGER,
    created_at     TEXT    NOT NULL DEFAULT (datetime('now'))
);
