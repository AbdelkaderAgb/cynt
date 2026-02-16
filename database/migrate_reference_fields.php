<?php
/**
 * Migration: Add reference-system fields to hotel_vouchers and tours tables
 */

$dbPath = __DIR__ . '/cyn_tourism.sqlite';
if (!file_exists($dbPath)) {
    die("Database not found at $dbPath\n");
}

$pdo = new PDO("sqlite:$dbPath");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Running migration: reference system fields...\n";

// Helper: check if column exists
function columnExists(PDO $pdo, string $table, string $column): bool {
    $cols = $pdo->query("PRAGMA table_info($table)")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        if ($c['name'] === $column) return true;
    }
    return false;
}

// --- hotel_vouchers ---
$hvCols = [
    'address'        => "ALTER TABLE hotel_vouchers ADD COLUMN address TEXT DEFAULT ''",
    'telephone'      => "ALTER TABLE hotel_vouchers ADD COLUMN telephone TEXT DEFAULT ''",
    'transfer_type'  => "ALTER TABLE hotel_vouchers ADD COLUMN transfer_type TEXT DEFAULT 'without'",
    'customers'      => "ALTER TABLE hotel_vouchers ADD COLUMN customers TEXT DEFAULT '[]'",
];
foreach ($hvCols as $col => $sql) {
    if (!columnExists($pdo, 'hotel_vouchers', $col)) {
        $pdo->exec($sql);
        echo "  ✓ hotel_vouchers.$col added\n";
    } else {
        echo "  – hotel_vouchers.$col already exists\n";
    }
}

// --- tours ---
$tourCols = [
    'hotel_name'     => "ALTER TABLE tours ADD COLUMN hotel_name TEXT DEFAULT ''",
    'customer_phone' => "ALTER TABLE tours ADD COLUMN customer_phone TEXT DEFAULT ''",
    'adults'         => "ALTER TABLE tours ADD COLUMN adults INTEGER DEFAULT 0",
    'children'       => "ALTER TABLE tours ADD COLUMN children INTEGER DEFAULT 0",
    'infants'        => "ALTER TABLE tours ADD COLUMN infants INTEGER DEFAULT 0",
    'customers'      => "ALTER TABLE tours ADD COLUMN customers TEXT DEFAULT '[]'",
    'tour_items'     => "ALTER TABLE tours ADD COLUMN tour_items TEXT DEFAULT '[]'",
];
foreach ($tourCols as $col => $sql) {
    if (!columnExists($pdo, 'tours', $col)) {
        $pdo->exec($sql);
        echo "  ✓ tours.$col added\n";
    } else {
        echo "  – tours.$col already exists\n";
    }
}

echo "\nMigration complete!\n";
