<?php
/**
 * Local SQLite Test Setup
 * Creates a SQLite database with essential tables and a test admin user
 * Run: php setup_local_test.php
 */

$dbPath = __DIR__ . '/database/cyn_tourism.sqlite';
$dbDir  = dirname($dbPath);

if (!is_dir($dbDir)) mkdir($dbDir, 0755, true);

$pdo = new PDO("sqlite:$dbPath");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec("PRAGMA journal_mode=WAL");
$pdo->exec("PRAGMA foreign_keys=ON");

echo "Creating tables...\n";

$pdo->exec("
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    first_name TEXT NOT NULL DEFAULT '',
    last_name TEXT NOT NULL DEFAULT '',
    role TEXT NOT NULL DEFAULT 'admin',
    status TEXT NOT NULL DEFAULT 'active',
    profile_image TEXT DEFAULT NULL,
    phone TEXT DEFAULT NULL,
    last_login DATETIME DEFAULT NULL,
    login_count INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS hotels (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    address TEXT DEFAULT '',
    city TEXT DEFAULT '',
    country TEXT DEFAULT 'Turkey',
    stars INTEGER DEFAULT 3,
    phone TEXT DEFAULT '',
    email TEXT DEFAULT '',
    website TEXT DEFAULT '',
    description TEXT DEFAULT '',
    status TEXT DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS hotel_rooms (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    hotel_id INTEGER NOT NULL,
    room_type TEXT NOT NULL,
    capacity INTEGER DEFAULT 2,
    price_single REAL DEFAULT 0,
    price_double REAL DEFAULT 0,
    price_triple REAL DEFAULT 0,
    price_quad REAL DEFAULT 0,
    price_child REAL DEFAULT 0,
    currency TEXT DEFAULT 'USD',
    board_type TEXT DEFAULT 'BB',
    season TEXT DEFAULT 'all',
    FOREIGN KEY (hotel_id) REFERENCES hotels(id)
);

CREATE TABLE IF NOT EXISTS room_board_prices (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    room_id INTEGER NOT NULL,
    board_type TEXT NOT NULL,
    price_single REAL DEFAULT 0,
    price_double REAL DEFAULT 0,
    price_triple REAL DEFAULT 0,
    price_quad REAL DEFAULT 0,
    price_child REAL DEFAULT 0,
    currency TEXT DEFAULT 'USD'
);

CREATE TABLE IF NOT EXISTS partners (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_name TEXT NOT NULL,
    contact_person TEXT DEFAULT '',
    email TEXT DEFAULT '',
    phone TEXT DEFAULT '',
    address TEXT DEFAULT '',
    city TEXT DEFAULT '',
    country TEXT DEFAULT '',
    status TEXT DEFAULT 'active',
    type TEXT DEFAULT 'agent',
    password TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS hotel_vouchers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    voucher_no TEXT UNIQUE,
    company_id INTEGER DEFAULT 0,
    hotel_name TEXT DEFAULT '',
    address TEXT DEFAULT '',
    telephone TEXT DEFAULT '',
    room_type TEXT DEFAULT '',
    room_count INTEGER DEFAULT 1,
    board_type TEXT DEFAULT 'BB',
    check_in DATE,
    nights INTEGER DEFAULT 1,
    guest_name TEXT DEFAULT '',
    passenger_passport TEXT DEFAULT '',
    adults INTEGER DEFAULT 0,
    children INTEGER DEFAULT 0,
    infants INTEGER DEFAULT 0,
    customers TEXT DEFAULT '[]',
    price_per_night REAL DEFAULT 0,
    total_price REAL DEFAULT 0,
    currency TEXT DEFAULT 'USD',
    special_requests TEXT DEFAULT '',
    linked_services TEXT DEFAULT '[]',
    status TEXT DEFAULT 'pending',
    cost_price REAL DEFAULT 0,
    selling_price REAL DEFAULT 0,
    created_by INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tours (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    voucher_no TEXT UNIQUE,
    company_id INTEGER DEFAULT 0,
    customer_phone TEXT DEFAULT '',
    hotel_name TEXT DEFAULT '',
    guest_name TEXT DEFAULT '',
    passenger_passport TEXT DEFAULT '',
    city TEXT DEFAULT '',
    country TEXT DEFAULT '',
    address TEXT DEFAULT '',
    meeting_point TEXT DEFAULT '',
    meeting_point_address TEXT DEFAULT '',
    tour_date DATE,
    start_time TEXT DEFAULT '',
    end_time TEXT DEFAULT '',
    tour_name TEXT DEFAULT '',
    includes TEXT DEFAULT '',
    excludes TEXT DEFAULT '',
    adults INTEGER DEFAULT 0,
    children INTEGER DEFAULT 0,
    infants INTEGER DEFAULT 0,
    total_pax INTEGER DEFAULT 0,
    price_per_person REAL DEFAULT 0,
    price_child REAL DEFAULT 0,
    price_per_infant REAL DEFAULT 0,
    total_price REAL DEFAULT 0,
    currency TEXT DEFAULT 'USD',
    customers TEXT DEFAULT '[]',
    notes TEXT DEFAULT '',
    status TEXT DEFAULT 'pending',
    cost_price REAL DEFAULT 0,
    selling_price REAL DEFAULT 0,
    driver_id INTEGER DEFAULT 0,
    vehicle_id INTEGER DEFAULT 0,
    guide_id INTEGER DEFAULT 0,
    created_by INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS transfer_vouchers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    voucher_no TEXT UNIQUE,
    company_id INTEGER DEFAULT 0,
    company_name TEXT DEFAULT '',
    hotel_name TEXT DEFAULT '',
    guest_name TEXT DEFAULT '',
    passenger_passport TEXT DEFAULT '',
    pickup_location TEXT DEFAULT '',
    dropoff_location TEXT DEFAULT '',
    pickup_date DATE,
    pickup_time TEXT DEFAULT '',
    return_date DATE,
    return_time TEXT DEFAULT '',
    transfer_type TEXT DEFAULT 'one_way',
    total_pax INTEGER DEFAULT 1,
    flight_number TEXT DEFAULT '',
    transfer_legs TEXT DEFAULT '[]',
    passengers TEXT DEFAULT '',
    price REAL DEFAULT 0,
    currency TEXT DEFAULT 'USD',
    notes TEXT DEFAULT '',
    status TEXT DEFAULT 'pending',
    driver_id INTEGER DEFAULT 0,
    vehicle_id INTEGER DEFAULT 0,
    guide_id INTEGER DEFAULT 0,
    cost_price REAL DEFAULT 0,
    selling_price REAL DEFAULT 0,
    created_by INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS invoices (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    invoice_no TEXT UNIQUE,
    partner_id INTEGER DEFAULT 0,
    invoice_date DATE,
    due_date DATE,
    total_amount REAL DEFAULT 0,
    paid_amount REAL DEFAULT 0,
    currency TEXT DEFAULT 'USD',
    status TEXT DEFAULT 'draft',
    notes TEXT DEFAULT '',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS receipts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    receipt_no TEXT UNIQUE,
    partner_id INTEGER DEFAULT 0,
    invoice_id INTEGER DEFAULT 0,
    amount REAL DEFAULT 0,
    currency TEXT DEFAULT 'USD',
    payment_method TEXT DEFAULT 'cash',
    payment_date DATE,
    notes TEXT DEFAULT '',
    status TEXT DEFAULT 'completed',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS drivers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    phone TEXT DEFAULT '',
    status TEXT DEFAULT 'active'
);

CREATE TABLE IF NOT EXISTS vehicles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    plate_number TEXT DEFAULT '',
    capacity INTEGER DEFAULT 4,
    status TEXT DEFAULT 'active'
);

CREATE TABLE IF NOT EXISTS guides (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    phone TEXT DEFAULT '',
    languages TEXT DEFAULT '',
    status TEXT DEFAULT 'active'
);

CREATE TABLE IF NOT EXISTS settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    setting_key TEXT UNIQUE NOT NULL,
    setting_value TEXT DEFAULT ''
);
");

// Insert test admin user (password: admin123)
$hash = password_hash('admin123', PASSWORD_DEFAULT);
$existing = $pdo->query("SELECT id FROM users WHERE email='admin@test.com'")->fetch();
if (!$existing) {
    $pdo->prepare("INSERT INTO users (email, password, first_name, last_name, role, status) VALUES (?, ?, ?, ?, ?, ?)")
        ->execute(['admin@test.com', $hash, 'Admin', 'User', 'admin', 'active']);
    echo "✅ Test admin created: admin@test.com / admin123\n";
} else {
    // Update password in case it changed
    $pdo->prepare("UPDATE users SET password = ? WHERE email = 'admin@test.com'")->execute([$hash]);
    echo "✅ Admin password reset: admin@test.com / admin123\n";
}

// Insert sample hotels for testing cascade
$hotels = [
    ['Grand Mardin Hotel', 'Mardin Cad. No:5', 'Istanbul', 'Turkey', 5, '+90 212 555 0001'],
    ['Blue Mosque Inn', 'Sultanahmet Sq.', 'Istanbul', 'Turkey', 4, '+90 212 555 0002'],
    ['Cappadocia Cave Suites', 'Göreme Valley', 'Nevşehir', 'Turkey', 5, '+90 384 555 0001'],
    ['Antalya Beach Resort', 'Lara Beach', 'Antalya', 'Turkey', 4, '+90 242 555 0001'],
    ['Le Royal Algiers', 'Rue Didouche', 'Algiers', 'Algeria', 5, '+213 21 555 001'],
    ['Sofitel Algiers', 'Hamma Business', 'Algiers', 'Algeria', 5, '+213 21 555 002'],
    ['El Djazair Oran', 'Front de Mer', 'Oran', 'Algeria', 4, '+213 41 555 001'],
];

foreach ($hotels as $h) {
    $existing = $pdo->prepare("SELECT id FROM hotels WHERE name = ?")->execute([$h[0]]);
    $row = $pdo->query("SELECT id FROM hotels WHERE name = '{$h[0]}'")->fetch();
    if (!$row) {
        $pdo->prepare("INSERT INTO hotels (name, address, city, country, stars, phone, status) VALUES (?, ?, ?, ?, ?, ?, 'active')")
            ->execute($h);
    }
}
echo "✅ " . count($hotels) . " sample hotels created (Turkey + Algeria)\n";

// Insert some rooms
$hotelIds = $pdo->query("SELECT id, name FROM hotels")->fetchAll(PDO::FETCH_ASSOC);
foreach ($hotelIds as $hotel) {
    $existing = $pdo->prepare("SELECT id FROM hotel_rooms WHERE hotel_id = ?")->execute([$hotel['id']]);
    $roomCount = $pdo->query("SELECT COUNT(*) FROM hotel_rooms WHERE hotel_id = {$hotel['id']}")->fetchColumn();
    if ($roomCount == 0) {
        $pdo->prepare("INSERT INTO hotel_rooms (hotel_id, room_type, capacity, price_single, price_double, price_triple, currency, board_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([$hotel['id'], 'Standard', 2, 80, 120, 160, 'USD', 'BB']);
        $pdo->prepare("INSERT INTO hotel_rooms (hotel_id, room_type, capacity, price_single, price_double, price_triple, currency, board_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([$hotel['id'], 'Deluxe', 3, 150, 200, 260, 'USD', 'HB']);
    }
}
echo "✅ Room types added to all hotels\n";

// Insert company settings
$settings = [
    ['company_name', 'CYN Tourism (Test)'],
    ['company_address', '123 Test Street, Istanbul'],
    ['company_phone', '+90 212 000 0000'],
    ['company_email', 'test@cyntourism.com'],
];
foreach ($settings as [$key, $val]) {
    $pdo->exec("INSERT OR IGNORE INTO settings (setting_key, setting_value) VALUES ('$key', '$val')");
}
echo "✅ Settings configured\n";

echo "\n🎉 Local SQLite database ready at: $dbPath\n";
echo "📝 Login with: admin@test.com / admin123\n";
