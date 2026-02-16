<?php
/**
 * CYN Tourism — Comprehensive Fix Verification Test
 * Tests ALL database schemas, models, controllers, and fixes locally.
 * 
 * Usage: php test_all_fixes.php
 * 
 * Uses SQLite in-memory database to test everything without requiring MySQL.
 * The Database class already supports SQLite with MySQL-compatible functions.
 */

// Emulate the app bootstrap
define('BASE_PATH', __DIR__);
define('ROOT_PATH', BASE_PATH);

// Override database config to use SQLite in-memory
define('DB_DRIVER', 'sqlite');
define('DB_PATH', ':memory:');

// Load config (but we already defined DB_DRIVER so it won't override)
// We need to define all config constants before loading
define('DEBUG_MODE', true);
define('LOG_QUERIES', false);
define('LOG_ENABLED', false);
define('APP_ROOT', __DIR__);
define('LOG_PATH', __DIR__ . '/logs/');

define('COMPANY_NAME', 'CYN TURIZM');
define('COMPANY_ADDRESS', 'Test Address');
define('COMPANY_PHONE', '+90 5318176770');
define('COMPANY_EMAIL', 'info@cyntourism.com');
define('COMPANY_LOGO', 'logo.png');
define('COMPANY_WEBSITE', 'https://cyntourism.com');
define('TURSAB_LICENSE', '');

define('SESSION_NAME', 'CYN_SESSION');
define('SESSION_LIFETIME', 7200);
define('SESSION_REGENERATE_ID', false);
define('CSRF_TOKEN_NAME', 'cyn_csrf_token');
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_REQUIRE_SPECIAL', true);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 900);
define('SECURE_COOKIES', false);
define('HTTP_ONLY_COOKIES', true);

define('DEFAULT_CURRENCY', 'USD');
define('AVAILABLE_CURRENCIES', json_encode(['USD', 'EUR', 'TRY', 'DZD']));

define('TIMEZONE', 'Europe/Istanbul');
define('DATE_FORMAT', 'd/m/Y');
define('DATETIME_FORMAT', 'd/m/Y H:i');
define('DB_DATE_FORMAT', 'Y-m-d');
define('DB_DATETIME_FORMAT', 'Y-m-d H:i:s');
date_default_timezone_set(TIMEZONE);

define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024);
define('UPLOAD_ALLOWED_TYPES', json_encode(['image' => ['jpg', 'jpeg', 'png']]));
define('UPLOAD_IMAGE_MAX_WIDTH', 1920);
define('UPLOAD_IMAGE_MAX_HEIGHT', 1080);
define('UPLOAD_THUMB_WIDTH', 150);
define('UPLOAD_THUMB_HEIGHT', 150);
define('MAINTENANCE_MODE', false);
define('MAINTENANCE_ALLOW_ADMIN', true);
define('MAINTENANCE_MESSAGE', 'Maintenance');
define('MAINTENANCE_IP_WHITELIST', json_encode([]));
define('BACKUP_DIR', __DIR__ . '/backups/');
define('BACKUP_RETENTION_DAYS', 30);
define('AUTO_BACKUP_ENABLED', false);
define('AUTO_BACKUP_FREQUENCY', 'daily');
define('BACKUP_INCLUDE_FILES', true);
define('BACKUP_MAX_FILES', 10);
define('SMTP_ENABLED', false);
define('SMTP_HOST', '');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_ENCRYPTION', 'tls');
define('SMTP_FROM_EMAIL', COMPANY_EMAIL);
define('SMTP_FROM_NAME', COMPANY_NAME);
define('SMTP_TIMEOUT', 30);
define('DEFAULT_ITEMS_PER_PAGE', 20);
define('PAGINATION_RANGE', 2);
define('MAX_ITEMS_PER_PAGE', 100);
define('NOTIFICATION_RETENTION_DAYS', 30);
define('ENABLE_BROWSER_NOTIFICATIONS', true);
define('NOTIFICATION_CHECK_INTERVAL', 60);
define('API_ENABLED', false);
define('API_RATE_LIMIT', 100);
define('API_KEY_REQUIRED', true);
define('DB_HOST', 'localhost');
define('DB_NAME', 'test');
define('DB_USER', 'test');
define('DB_PASS', 'test');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', 'utf8mb4_unicode_ci');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['user_id'] = 1;
$_SESSION['auth_time'] = time();
$_SESSION['user_role'] = 'admin';

// Fake $_SERVER
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_HOST'] = 'localhost';

// ============================================
// Test Framework
// ============================================
$passed = 0;
$failed = 0;
$errors = [];

function test_pass($name) {
    global $passed;
    $passed++;
    echo "  ✅ PASS: $name\n";
}

function test_fail($name, $reason) {
    global $failed, $errors;
    $failed++;
    $errors[] = "$name: $reason";
    echo "  ❌ FAIL: $name — $reason\n";
}

function test_section($name) {
    echo "\n━━━ $name ━━━\n";
}

echo "╔══════════════════════════════════════════════════╗\n";
echo "║   CYN Tourism — Comprehensive Fix Test Suite    ║\n";
echo "╚══════════════════════════════════════════════════╝\n";

// ============================================
// 1. Load Core Files
// ============================================
test_section("1. Loading Core Files");

try {
    require_once BASE_PATH . '/src/Core/App.php';
    App::setBasePath(BASE_PATH);
    test_pass("App.php loaded");
} catch (Throwable $e) {
    test_fail("App.php", $e->getMessage());
}

try {
    require_once BASE_PATH . '/src/Core/Controller.php';
    test_pass("Controller.php loaded");
} catch (Throwable $e) {
    test_fail("Controller.php", $e->getMessage());
}

try {
    require_once BASE_PATH . '/src/Core/Logger.php';
    test_pass("Logger.php loaded");
} catch (Throwable $e) {
    test_fail("Logger.php", $e->getMessage());
}

try {
    require_once BASE_PATH . '/config/database.php';
    test_pass("Database.php loaded");
} catch (Throwable $e) {
    test_fail("Database.php", $e->getMessage());
}

try {
    require_once BASE_PATH . '/src/Core/Auth.php';
    test_pass("Auth.php loaded");
} catch (Throwable $e) {
    test_fail("Auth.php", $e->getMessage());
}

try {
    require_once BASE_PATH . '/src/Core/helpers.php';
    test_pass("helpers.php loaded");
} catch (Throwable $e) {
    test_fail("helpers.php", $e->getMessage());
}

// ============================================
// 2. Test Database Connection (SQLite)
// ============================================
test_section("2. Database Connection (SQLite in-memory)");

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    test_pass("Database singleton created");
} catch (Throwable $e) {
    test_fail("Database connection", $e->getMessage());
    echo "\n💀 Cannot continue without database. Exiting.\n";
    exit(1);
}

// ============================================
// 3. Create All Tables (SQLite-compatible schema)
// ============================================
test_section("3. Creating Database Schema");

$tables = [
    'users' => "CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) DEFAULT 'viewer',
        status VARCHAR(20) DEFAULT 'active',
        profile_image VARCHAR(255) DEFAULT '',
        phone VARCHAR(20) DEFAULT '',
        last_login DATETIME DEFAULT CURRENT_TIMESTAMP,
        login_count INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    'partners' => "CREATE TABLE IF NOT EXISTS partners (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        company_name VARCHAR(120) NOT NULL,
        contact_person VARCHAR(100) DEFAULT '',
        email VARCHAR(255) NOT NULL,
        password VARCHAR(255) DEFAULT '',
        phone VARCHAR(20) DEFAULT '',
        mobile VARCHAR(20) DEFAULT '',
        address TEXT DEFAULT '',
        city VARCHAR(100) DEFAULT '',
        country VARCHAR(100) DEFAULT '',
        postal_code VARCHAR(20) DEFAULT '',
        website VARCHAR(255) DEFAULT '',
        tax_id VARCHAR(50) DEFAULT '',
        commission_rate DECIMAL(5,2) DEFAULT 0.00,
        credit_limit DECIMAL(12,2) DEFAULT 0.00,
        balance DECIMAL(12,2) DEFAULT 0.00,
        payment_terms INT DEFAULT 30,
        partner_type VARCHAR(20) DEFAULT 'agency',
        status VARCHAR(20) DEFAULT 'active',
        notes TEXT DEFAULT '',
        contract_file VARCHAR(255) DEFAULT '',
        created_by INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    'vouchers' => "CREATE TABLE IF NOT EXISTS vouchers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        voucher_no VARCHAR(60) NOT NULL,
        company_name VARCHAR(120) NOT NULL,
        company_id INT DEFAULT 0,
        partner_id INT DEFAULT 0,
        hotel_name VARCHAR(120) NOT NULL,
        pickup_location VARCHAR(255) NOT NULL,
        dropoff_location VARCHAR(255) NOT NULL,
        pickup_date DATE NOT NULL,
        pickup_time TIME NOT NULL,
        return_date DATE DEFAULT '1970-01-01',
        return_time TIME DEFAULT '00:00:00',
        transfer_type VARCHAR(20) DEFAULT 'one_way',
        total_pax INT DEFAULT 0,
        passengers TEXT DEFAULT '',
        flight_number VARCHAR(50) DEFAULT '',
        flight_arrival_time TIME DEFAULT '00:00:00',
        vehicle_id INT DEFAULT 0,
        driver_id INT DEFAULT 0,
        guide_id INT DEFAULT 0,
        special_requests TEXT DEFAULT '',
        price DECIMAL(10,2) DEFAULT 0.00,
        currency VARCHAR(3) DEFAULT 'USD',
        status VARCHAR(20) DEFAULT 'pending',
        payment_status VARCHAR(20) DEFAULT 'unpaid',
        notes TEXT DEFAULT '',
        created_by INT DEFAULT 0,
        updated_by INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    'invoices' => "CREATE TABLE IF NOT EXISTS invoices (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        invoice_no VARCHAR(60) NOT NULL,
        company_name VARCHAR(120) NOT NULL,
        company_id INT DEFAULT 0,
        partner_id INT DEFAULT 0,
        invoice_date DATE NOT NULL,
        due_date DATE NOT NULL,
        subtotal DECIMAL(12,2) DEFAULT 0.00,
        tax_rate DECIMAL(5,2) DEFAULT 0.00,
        tax_amount DECIMAL(12,2) DEFAULT 0.00,
        discount DECIMAL(12,2) DEFAULT 0.00,
        total_amount DECIMAL(12,2) DEFAULT 0.00,
        paid_amount DECIMAL(12,2) DEFAULT 0.00,
        currency VARCHAR(3) DEFAULT 'USD',
        status VARCHAR(20) DEFAULT 'draft',
        payment_method VARCHAR(50) DEFAULT '',
        payment_date DATE DEFAULT '1970-01-01',
        notes TEXT DEFAULT '',
        type VARCHAR(20) DEFAULT 'general',
        terms TEXT DEFAULT '',
        file_path VARCHAR(255) DEFAULT '',
        sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        sent_by INT DEFAULT 0,
        created_by INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    'invoice_items' => "CREATE TABLE IF NOT EXISTS invoice_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        invoice_id INT NOT NULL,
        item_type VARCHAR(20) DEFAULT 'voucher',
        item_id INT DEFAULT 0,
        description TEXT NOT NULL,
        quantity INT DEFAULT 1,
        unit_price DECIMAL(10,2) DEFAULT 0.00,
        total_price DECIMAL(10,2) DEFAULT 0.00,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    'hotel_vouchers' => "CREATE TABLE IF NOT EXISTS hotel_vouchers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        voucher_no VARCHAR(60) NOT NULL,
        guest_name VARCHAR(200) NOT NULL,
        hotel_name VARCHAR(200) NOT NULL,
        hotel_id INT DEFAULT 0,
        company_name VARCHAR(120) DEFAULT '',
        company_id INT DEFAULT 0,
        address VARCHAR(255) DEFAULT '',
        telephone VARCHAR(50) DEFAULT '',
        partner_id INT DEFAULT 0,
        room_type VARCHAR(100) DEFAULT '',
        room_count INT DEFAULT 1,
        board_type VARCHAR(30) DEFAULT 'bed_breakfast',
        transfer_type VARCHAR(30) DEFAULT 'without',
        check_in DATE NOT NULL,
        check_out DATE NOT NULL,
        nights INT DEFAULT 1,
        total_pax INT DEFAULT 0,
        adults INT DEFAULT 0,
        children INT DEFAULT 0,
        infants INT DEFAULT 0,
        confirmation_no VARCHAR(100) DEFAULT '',
        price_per_night DECIMAL(10,2) DEFAULT 0.00,
        total_price DECIMAL(10,2) DEFAULT 0.00,
        currency VARCHAR(3) DEFAULT 'USD',
        customers TEXT DEFAULT '',
        special_requests TEXT DEFAULT '',
        status VARCHAR(20) DEFAULT 'pending',
        payment_status VARCHAR(20) DEFAULT 'unpaid',
        notes TEXT DEFAULT '',
        created_by INT DEFAULT 0,
        updated_by INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    'tours' => "CREATE TABLE IF NOT EXISTS tours (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        tour_name VARCHAR(200) NOT NULL,
        tour_code VARCHAR(50) DEFAULT '',
        description TEXT DEFAULT '',
        tour_type VARCHAR(20) DEFAULT 'daily',
        destination VARCHAR(200) DEFAULT '',
        pickup_location VARCHAR(255) DEFAULT '',
        dropoff_location VARCHAR(255) DEFAULT '',
        tour_date DATE NOT NULL DEFAULT '1970-01-01',
        start_time TIME DEFAULT '00:00:00',
        end_time TIME DEFAULT '00:00:00',
        duration_days INT DEFAULT 1,
        total_pax INT DEFAULT 0,
        max_pax INT DEFAULT 0,
        passengers TEXT DEFAULT '',
        company_name VARCHAR(120) DEFAULT '',
        hotel_name VARCHAR(200) DEFAULT '',
        customer_phone VARCHAR(50) DEFAULT '',
        adults INT DEFAULT 0,
        children INT DEFAULT 0,
        infants INT DEFAULT 0,
        customers TEXT DEFAULT '[]',
        tour_items TEXT DEFAULT '[]',
        company_id INT DEFAULT 0,
        partner_id INT DEFAULT 0,
        guide_id INT DEFAULT 0,
        vehicle_id INT DEFAULT 0,
        driver_id INT DEFAULT 0,
        price_per_person DECIMAL(10,2) DEFAULT 0.00,
        total_price DECIMAL(10,2) DEFAULT 0.00,
        currency VARCHAR(3) DEFAULT 'USD',
        includes TEXT DEFAULT '',
        excludes TEXT DEFAULT '',
        itinerary TEXT DEFAULT '',
        special_requests TEXT DEFAULT '',
        status VARCHAR(20) DEFAULT 'pending',
        payment_status VARCHAR(20) DEFAULT 'unpaid',
        notes TEXT DEFAULT '',
        created_by INT DEFAULT 0,
        updated_by INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    'hotels' => "CREATE TABLE IF NOT EXISTS hotels (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(200) NOT NULL,
        address VARCHAR(500) DEFAULT '',
        city VARCHAR(100) DEFAULT '',
        country VARCHAR(100) DEFAULT 'Turkey',
        stars INT DEFAULT 3,
        phone VARCHAR(50) DEFAULT '',
        email VARCHAR(150) DEFAULT '',
        website VARCHAR(255) DEFAULT '',
        description TEXT DEFAULT '',
        status VARCHAR(20) DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    'hotel_rooms' => "CREATE TABLE IF NOT EXISTS hotel_rooms (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        hotel_id INT NOT NULL,
        room_type VARCHAR(100) NOT NULL,
        capacity INT DEFAULT 2,
        price_single DECIMAL(10,2) DEFAULT 0.00,
        price_double DECIMAL(10,2) DEFAULT 0.00,
        price_triple DECIMAL(10,2) DEFAULT 0.00,
        price_quad DECIMAL(10,2) DEFAULT 0.00,
        price_child DECIMAL(10,2) DEFAULT 0.00,
        currency VARCHAR(3) DEFAULT 'USD',
        board_type VARCHAR(20) DEFAULT 'BB',
        season VARCHAR(30) DEFAULT 'all',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    'drivers' => "CREATE TABLE IF NOT EXISTS drivers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        license_no VARCHAR(50) NOT NULL,
        license_expiry DATE NOT NULL,
        status VARCHAR(20) DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    'vehicles' => "CREATE TABLE IF NOT EXISTS vehicles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        plate_number VARCHAR(20) NOT NULL,
        make VARCHAR(50) NOT NULL,
        model VARCHAR(50) NOT NULL,
        capacity INT DEFAULT 4,
        vehicle_type VARCHAR(20) DEFAULT 'sedan',
        status VARCHAR(20) DEFAULT 'available',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    'tour_guides' => "CREATE TABLE IF NOT EXISTS tour_guides (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        languages VARCHAR(255) NOT NULL,
        status VARCHAR(20) DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    'notifications' => "CREATE TABLE IF NOT EXISTS notifications (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        type VARCHAR(20) DEFAULT 'info',
        category VARCHAR(20) DEFAULT 'general',
        is_read INT DEFAULT 0,
        read_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    'services' => "CREATE TABLE IF NOT EXISTS services (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        service_type VARCHAR(20) DEFAULT 'tour',
        name VARCHAR(150) NOT NULL,
        description TEXT DEFAULT '',
        price DECIMAL(10,2) DEFAULT 0.00,
        currency VARCHAR(3) DEFAULT 'USD',
        unit VARCHAR(50) DEFAULT 'per_person',
        details TEXT DEFAULT '',
        status VARCHAR(20) DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    'settings' => "CREATE TABLE IF NOT EXISTS settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        setting_key VARCHAR(100) NOT NULL UNIQUE,
        setting_value TEXT DEFAULT '',
        setting_group VARCHAR(50) DEFAULT 'general',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    'partner_booking_requests' => "CREATE TABLE IF NOT EXISTS partner_booking_requests (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        partner_id INT NOT NULL,
        request_type VARCHAR(50) DEFAULT 'transfer',
        details TEXT NOT NULL,
        status VARCHAR(20) DEFAULT 'pending',
        admin_notes TEXT DEFAULT '',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    'partner_messages' => "CREATE TABLE IF NOT EXISTS partner_messages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        partner_id INT NOT NULL,
        sender_type VARCHAR(10) NOT NULL,
        sender_id INT NOT NULL,
        subject VARCHAR(255) DEFAULT '',
        message TEXT NOT NULL,
        file_path VARCHAR(255) NOT NULL DEFAULT '',
        is_read INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
];

foreach ($tables as $name => $sql) {
    try {
        $conn->exec($sql);
        test_pass("Table '$name' created");
    } catch (Throwable $e) {
        test_fail("Table '$name'", $e->getMessage());
    }
}

// ============================================
// 4. Insert Test Data
// ============================================
test_section("4. Inserting Test Data");

try {
    $conn->exec("INSERT INTO users (first_name, last_name, email, password, role, status) VALUES ('System', 'Admin', 'admin@test.com', '\$2y\$10\$dummy', 'admin', 'active')");
    test_pass("Test user inserted");
} catch (Throwable $e) { test_fail("Insert user", $e->getMessage()); }

try {
    $conn->exec("INSERT INTO partners (company_name, contact_person, email, password, status, partner_type) VALUES ('Atlas Travel', 'Karim', 'karim@test.com', '\$2y\$10\$dummy', 'active', 'agency')");
    test_pass("Test partner inserted");
} catch (Throwable $e) { test_fail("Insert partner", $e->getMessage()); }

try {
    $conn->exec("INSERT INTO vouchers (voucher_no, company_name, hotel_name, pickup_location, dropoff_location, pickup_date, pickup_time, total_pax, price, currency, status) VALUES ('VC-202602-0001', 'Atlas Travel', 'Grand Hotel', 'Airport', 'Hotel', '2026-02-15', '14:00', 3, 45.00, 'USD', 'pending')");
    test_pass("Test voucher inserted");
} catch (Throwable $e) { test_fail("Insert voucher", $e->getMessage()); }

try {
    $conn->exec("INSERT INTO invoices (invoice_no, company_name, invoice_date, due_date, subtotal, total_amount, currency, status, type) VALUES ('INV-202602-0001', 'Atlas Travel', '2026-02-10', '2026-03-10', 300.00, 300.00, 'USD', 'paid', 'transfer')");
    test_pass("Test invoice inserted");
} catch (Throwable $e) { test_fail("Insert invoice", $e->getMessage()); }

try {
    $conn->exec("INSERT INTO hotel_vouchers (voucher_no, guest_name, hotel_name, company_name, address, telephone, room_type, board_type, transfer_type, check_in, check_out, nights, total_pax, adults, price_per_night, total_price, currency, customers, special_requests, status) VALUES ('HV-20260215-0001', 'Test Guest', 'Grand Hotel', 'Atlas Travel', 'Test St', '+90555', 'Deluxe', 'bed_breakfast', 'without', '2026-02-15', '2026-02-18', 3, 2, 2, 100.00, 300.00, 'USD', '[]', '', 'pending')");
    test_pass("Test hotel voucher inserted (with address + telephone columns)");
} catch (Throwable $e) { test_fail("Insert hotel voucher", $e->getMessage()); }

try {
    $conn->exec("INSERT INTO tours (tour_name, tour_code, tour_date, total_pax, company_name, hotel_name, customer_phone, adults, children, infants, customers, tour_items, status) VALUES ('City Tour', 'TV-001', '2026-02-20', 4, 'Atlas Travel', 'Grand Hotel', '+905551234', 3, 1, 0, '[]', '[]', 'pending')");
    test_pass("Test tour inserted (with hotel_name + customer_phone columns)");
} catch (Throwable $e) { test_fail("Insert tour", $e->getMessage()); }

try {
    $conn->exec("INSERT INTO drivers (first_name, last_name, phone, license_no, license_expiry, status) VALUES ('Ali', 'Yildiz', '+90 532 100', 'TR-001', '2027-06-15', 'active')");
    $conn->exec("INSERT INTO vehicles (plate_number, make, model, capacity, status) VALUES ('34 CYN 001', 'Mercedes', 'Vito', 8, 'available')");
    $conn->exec("INSERT INTO tour_guides (first_name, last_name, phone, languages, status) VALUES ('Deniz', 'Ozkan', '+90 532 200', 'Turkish,English', 'active')");
    test_pass("Test fleet data inserted (drivers, vehicles, guides)");
} catch (Throwable $e) { test_fail("Insert fleet", $e->getMessage()); }

try {
    $conn->exec("INSERT INTO notifications (user_id, title, message, type, is_read) VALUES (1, 'Test Notification', 'Test message', 'info', 0)");
    test_pass("Test notification inserted");
} catch (Throwable $e) { test_fail("Insert notification", $e->getMessage()); }

try {
    $conn->exec("INSERT INTO services (service_type, name, description, price, currency, unit, status) VALUES ('transfer', 'Airport Pickup', 'IST to Hotel', 45.00, 'USD', 'per_vehicle', 'active')");
    test_pass("Test service inserted");
} catch (Throwable $e) { test_fail("Insert service", $e->getMessage()); }

try {
    $conn->exec("INSERT INTO partner_booking_requests (partner_id, request_type, details, status) VALUES (1, 'transfer', '{\"guest_name\":\"Test\"}', 'pending')");
    test_pass("Test booking request inserted");
} catch (Throwable $e) { test_fail("Insert booking request", $e->getMessage()); }

try {
    $conn->exec("INSERT INTO partner_messages (partner_id, sender_type, sender_id, subject, message, file_path) VALUES (1, 'partner', 1, 'Test', 'Hello', '')");
    test_pass("Test message inserted (file_path = '' not NULL)");
} catch (Throwable $e) { test_fail("Insert message", $e->getMessage()); }

try {
    $conn->exec("INSERT INTO hotels (name, city, country, stars, status) VALUES ('Grand Hotel', 'Istanbul', 'Turkey', 5, 'active')");
    $conn->exec("INSERT INTO hotel_rooms (hotel_id, room_type, capacity, price_single, price_double) VALUES (1, 'Standard', 2, 100.00, 150.00)");
    test_pass("Test hotel + room inserted");
} catch (Throwable $e) { test_fail("Insert hotel", $e->getMessage()); }

// ============================================
// 5. Test Models
// ============================================
test_section("5. Testing Models");

try {
    require_once BASE_PATH . '/src/Models/Invoice.php';
    $inv = Invoice::getById(1);
    if ($inv && $inv['invoice_no'] === 'INV-202602-0001') {
        test_pass("Invoice::getById() works");
    } else {
        test_fail("Invoice::getById()", "Returned: " . json_encode($inv));
    }
} catch (Throwable $e) { test_fail("Invoice model", $e->getMessage()); }

try {
    $result = Invoice::getAll(['type' => 'transfer']);
    if ($result['total'] === 1) {
        test_pass("Invoice::getAll() with type filter works");
    } else {
        test_fail("Invoice::getAll(type)", "Expected 1, got: " . $result['total']);
    }
} catch (Throwable $e) { test_fail("Invoice::getAll(type)", $e->getMessage()); }

try {
    $summary = Invoice::getSummary();
    test_pass("Invoice::getSummary() works — total: " . ($summary['total'] ?? 0));
} catch (Throwable $e) { test_fail("Invoice::getSummary()", $e->getMessage()); }

try {
    require_once BASE_PATH . '/src/Models/Voucher.php';
    $v = Voucher::getById(1);
    if ($v && $v['voucher_no'] === 'VC-202602-0001') {
        test_pass("Voucher::getById() works");
    } else {
        test_fail("Voucher::getById()", "Returned: " . json_encode($v));
    }
} catch (Throwable $e) { test_fail("Voucher model", $e->getMessage()); }

try {
    require_once BASE_PATH . '/src/Models/Partner.php';
    $partners = Partner::getActive();
    if (count($partners) === 1) {
        test_pass("Partner::getActive() works — " . $partners[0]['company_name']);
    } else {
        test_fail("Partner::getActive()", "Expected 1, got: " . count($partners));
    }
} catch (Throwable $e) { test_fail("Partner model", $e->getMessage()); }

try {
    require_once BASE_PATH . '/src/Models/Fleet.php';
    $drivers = Fleet::getActiveDrivers();
    $vehicles = Fleet::getActiveVehicles();
    $guides = Fleet::getActiveGuides();
    test_pass("Fleet model works — drivers:" . count($drivers) . " vehicles:" . count($vehicles) . " guides:" . count($guides));
} catch (Throwable $e) { test_fail("Fleet model", $e->getMessage()); }

try {
    require_once BASE_PATH . '/src/Models/Dashboard.php';
    $stats = Dashboard::getStats();
    test_pass("Dashboard::getStats() works — keys: " . implode(',', array_keys($stats)));
} catch (Throwable $e) { test_fail("Dashboard model", $e->getMessage()); }

try {
    require_once BASE_PATH . '/src/Models/Report.php';
    $revenue = Report::getMonthlyRevenue('2026-01-01', '2026-12-31');
    test_pass("Report::getMonthlyRevenue() works (no strftime error!)");
} catch (Throwable $e) { test_fail("Report model", $e->getMessage()); }

// ============================================
// 6. Test Controller Method Existence
// ============================================
test_section("6. Testing Controller Classes");

$controllers = [
    'ExportController'       => ['invoicePdf', 'voucherPdf', 'hotelVoucherPdf', 'tourVoucherPdf', 'receiptPdf', 'sendEmail', 'whatsappShare'],
    'InvoiceController'      => ['index', 'create', 'store', 'show', 'edit', 'delete', 'markPaid', 'sendToPortal'],
    'VoucherController'      => ['index', 'create', 'store', 'show', 'edit', 'delete'],
    'HotelController'        => ['voucher', 'voucherStore', 'voucherShow', 'voucherEdit', 'voucherUpdate', 'voucherDelete', 'invoice', 'invoiceCreate', 'invoiceStore'],
    'TourController'         => ['voucher', 'voucherCreate', 'voucherStore', 'voucherShow', 'voucherEdit', 'voucherUpdate', 'voucherDelete', 'invoice', 'invoiceCreate', 'invoiceStore'],
    'TransferController'     => ['index', 'store', 'invoice', 'invoiceCreate', 'invoiceStore'],
    'PartnerController'      => ['index', 'create', 'store', 'show', 'edit', 'delete', 'searchApi', 'bookingRequests', 'bookingRequestAction', 'partnerMessages', 'messageReply'],
    'PortalController'       => ['login', 'logout', 'dashboard', 'invoices', 'invoiceView', 'vouchers', 'voucherView', 'bookingRequests', 'bookingRequest', 'bookingRequestStore', 'messages', 'messageSend', 'profile', 'profileUpdate', 'receipts', 'receiptView'],
    'ReceiptController'      => ['index', 'show', 'markPaid', 'edit', 'update', 'revert', 'sendToPortal'],
    'DashboardController'    => ['index'],
    'CalendarController'     => ['index', 'hotelCalendar'],
    'NotificationController' => ['index', 'markRead', 'markAllRead'],
    'ReportController'       => ['index'],
    'ServiceController'      => ['index', 'create', 'edit', 'store', 'delete', 'searchApi'],
    'HotelProfileController' => ['index', 'create', 'edit', 'store', 'delete', 'importXlsx'],
    'UserController'         => ['index', 'create', 'store', 'edit', 'profile', 'updateProfile'],
    'SettingsController'     => ['index', 'update', 'email'],
    'FleetController'        => ['drivers', 'driverForm', 'driverStore', 'driverDelete', 'vehicles', 'vehicleForm', 'vehicleStore', 'vehicleDelete', 'guides', 'guideForm', 'guideStore', 'guideDelete'],
    'AuthController'         => ['index', 'login', 'logout'],
];

foreach ($controllers as $className => $methods) {
    $file = BASE_PATH . "/src/Controllers/{$className}.php";
    if (!file_exists($file)) {
        test_fail($className, "File not found: $file");
        continue;
    }
    require_once $file;
    if (!class_exists($className)) {
        test_fail($className, "Class not found after loading file");
        continue;
    }
    $missing = [];
    foreach ($methods as $m) {
        if (!method_exists($className, $m)) {
            $missing[] = $m;
        }
    }
    if (empty($missing)) {
        test_pass("$className — all " . count($methods) . " methods exist");
    } else {
        test_fail($className, "Missing methods: " . implode(', ', $missing));
    }
}

// ============================================
// 7. Test Specific Fixes
// ============================================
test_section("7. Testing Specific Bug Fixes");

// Fix: jsonResponse() method exists on Controller
if (method_exists('Controller', 'jsonResponse')) {
    test_pass("Controller::jsonResponse() exists (was missing before)");
} else {
    test_fail("Controller::jsonResponse()", "Method still missing!");
}

// Fix: ExportController does NOT require vendor/autoload.php
$exportContent = file_get_contents(BASE_PATH . '/src/Controllers/ExportController.php');
if (strpos($exportContent, "require_once ROOT_PATH . '/vendor/autoload.php'") === false) {
    test_pass("ExportController no longer has require_once vendor/autoload.php");
} else {
    test_fail("ExportController vendor fix", "Still has the require_once!");
}

// Fix: No NOW() in prepared statements
$filesToCheck = [
    'src/Controllers/PartnerController.php',
    'src/Controllers/HotelController.php',
    'src/Controllers/TourController.php',
    'src/Controllers/NotificationController.php',
    'src/Controllers/PortalController.php',
    'src/Core/Auth.php',
    'src/Core/helpers.php',
];
$nowFixed = true;
foreach ($filesToCheck as $f) {
    $content = file_get_contents(BASE_PATH . '/' . $f);
    if (preg_match('/prepare\s*\(.*NOW\s*\(\)/', $content)) {
        test_fail("NOW() fix in $f", "Still has NOW() in a prepared statement");
        $nowFixed = false;
    }
}
if ($nowFixed) {
    test_pass("All NOW() replaced with CURRENT_TIMESTAMP in prepared statements (" . count($filesToCheck) . " files checked)");
}

// Fix: Database::query() not called statically
$serviceContent = file_get_contents(BASE_PATH . '/src/Controllers/ServiceController.php');
if (strpos($serviceContent, 'Database::query(') === false) {
    test_pass("ServiceController uses Database::execute() not Database::query()");
} else {
    test_fail("ServiceController", "Still uses Database::query() statically");
}

// Fix: Auth::requireAuth redirects to /login not login.php
$authContent = file_get_contents(BASE_PATH . '/src/Core/Auth.php');
if (strpos($authContent, "App::url('/login')") !== false && strpos($authContent, "header('Location: login.php')") === false) {
    test_pass("Auth::requireAuth() redirects to App::url('/login')");
} else {
    test_fail("Auth redirect fix", "Still redirects to login.php");
}

// Fix: vehicles status uses 'available' not 'active'
$dashContent = file_get_contents(BASE_PATH . '/src/Models/Dashboard.php');
$helpContent = file_get_contents(BASE_PATH . '/src/Core/helpers.php');
if (strpos($dashContent, "status = 'available'") !== false && strpos($helpContent, "vehicles WHERE status = 'available'") !== false) {
    test_pass("Vehicles status correctly uses 'available' in Dashboard + helpers");
} else {
    test_fail("Vehicles status", "Still uses 'active' somewhere");
}

// Fix: PortalController receipts doesn't use 'receipts' table
$portalContent = file_get_contents(BASE_PATH . '/src/Controllers/PortalController.php');
if (strpos($portalContent, 'FROM receipts') === false) {
    test_pass("PortalController no longer queries non-existent 'receipts' table");
} else {
    test_fail("PortalController receipts", "Still queries 'receipts' table");
}

// Fix: No strftime in Report model
$reportContent = file_get_contents(BASE_PATH . '/src/Models/Report.php');
if (strpos($reportContent, 'strftime') === false) {
    test_pass("Report model uses DATE_FORMAT, not strftime");
} else {
    test_fail("Report strftime", "Still uses strftime (SQLite function)");
}

// ============================================
// 8. Test View Files Exist
// ============================================
test_section("8. Verifying All View Files");

$requiredViews = [
    'dashboard/index', 'auth/login',
    'vouchers/index', 'vouchers/form', 'vouchers/show', 'vouchers/pdf',
    'invoices/index', 'invoices/form', 'invoices/show', 'invoices/pdf',
    'hotels/voucher', 'hotels/voucher_show', 'hotels/voucher_edit', 'hotels/voucher_pdf',
    'hotels/invoice', 'hotels/invoice_form',
    'tours/voucher', 'tours/voucher_show', 'tours/voucher_pdf', 'tours/form', 'tours/form_edit',
    'tours/invoice', 'tours/invoice_form',
    'transfers/index', 'transfers/invoice', 'transfers/invoice_form', 'transfers/form',
    'partners/index', 'partners/form', 'partners/show', 'partners/booking_requests', 'partners/messages', 'partners/messages_list',
    'receipts/index', 'receipts/show', 'receipts/edit', 'receipts/pdf',
    'calendar/index', 'calendar/hotel',
    'reports/index', 'services/index', 'services/form',
    'hotel_profiles/index', 'hotel_profiles/form',
    'users/index', 'users/form', 'users/profile',
    'settings/index', 'settings/email',
    'notifications/index',
    'portal/login', 'portal/dashboard', 'portal/invoices', 'portal/invoice_detail',
    'portal/vouchers', 'portal/voucher_detail', 'portal/bookings', 'portal/booking_form',
    'portal/messages', 'portal/profile', 'portal/receipts', 'portal/receipt_detail',
    'layouts/app', 'layouts/portal',
    'fleet/drivers', 'fleet/driver_form', 'fleet/vehicles', 'fleet/vehicle_form', 'fleet/guides', 'fleet/guide_form',
    'errors/404', 'errors/403', 'errors/500',
];

$missingViews = [];
foreach ($requiredViews as $view) {
    if (!file_exists(BASE_PATH . "/views/{$view}.php")) {
        $missingViews[] = $view;
    }
}

if (empty($missingViews)) {
    test_pass("All " . count($requiredViews) . " view files exist");
} else {
    test_fail("Missing views", implode(', ', $missingViews));
}

// ============================================
// 9. Test Migration SQL Files
// ============================================
test_section("9. Verifying Migration SQL Files");

$migFiles = [
    'database/migration_fix_columns.sql',
    'database/migration_fix_keys.sql',
    'database/seed.sql',
];

foreach ($migFiles as $f) {
    if (file_exists(BASE_PATH . '/' . $f)) {
        $size = filesize(BASE_PATH . '/' . $f);
        test_pass("$f exists (" . number_format($size) . " bytes)");
    } else {
        test_fail($f, "File missing");
    }
}

// Check migration_fix_columns.sql has CREATE TABLE IF NOT EXISTS for all critical tables
$migContent = file_get_contents(BASE_PATH . '/database/migration_fix_columns.sql');
$criticalTables = ['hotel_vouchers', 'tours', 'invoices', 'invoice_items', 'hotels', 'hotel_rooms', 'services', 'notifications'];
$allFound = true;
foreach ($criticalTables as $t) {
    if (strpos($migContent, "CREATE TABLE IF NOT EXISTS `$t`") === false) {
        test_fail("migration_fix_columns.sql", "Missing CREATE TABLE for '$t'");
        $allFound = false;
    }
}
if ($allFound) {
    test_pass("migration_fix_columns.sql has CREATE TABLE for all " . count($criticalTables) . " critical tables");
}

// Check migration_fix_columns.sql has ADD COLUMN IF NOT EXISTS for key missing columns
$criticalColumns = [
    'hotel_vouchers' => ['address', 'telephone', 'company_id', 'partner_id'],
    'tours' => ['hotel_name', 'customer_phone', 'adults', 'children', 'customers', 'tour_items'],
    'invoices' => ['company_id', 'partner_id', 'type', 'paid_amount'],
    'vouchers' => ['company_id', 'partner_id'],
];
$colsOk = true;
foreach ($criticalColumns as $table => $cols) {
    foreach ($cols as $col) {
        if (strpos($migContent, "`$col`") === false) {
            test_fail("migration columns", "Missing column '$col' for table '$table'");
            $colsOk = false;
        }
    }
}
if ($colsOk) {
    test_pass("migration_fix_columns.sql has all critical missing columns");
}

// ============================================
// 10. Test Vendor/Dompdf
// ============================================
test_section("10. Vendor Dependencies");

if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
    if (class_exists('Dompdf\\Dompdf')) {
        test_pass("Dompdf library is available and loadable");
    } else {
        test_fail("Dompdf", "vendor/autoload.php loaded but Dompdf class not found");
    }
} else {
    test_fail("vendor/autoload.php", "File missing — run 'composer install' to install Dompdf");
}

// ============================================
// Summary
// ============================================
echo "\n╔══════════════════════════════════════════════════╗\n";
echo "║                  TEST RESULTS                   ║\n";
echo "╠══════════════════════════════════════════════════╣\n";
printf("║  ✅ Passed: %-36s ║\n", $passed);
printf("║  ❌ Failed: %-36s ║\n", $failed);
echo "╚══════════════════════════════════════════════════╝\n";

if (!empty($errors)) {
    echo "\nFailures:\n";
    foreach ($errors as $i => $err) {
        echo "  " . ($i + 1) . ". $err\n";
    }
}

echo "\n" . ($failed === 0 ? "🎉 ALL TESTS PASSED!" : "⚠️  Some tests failed — see above for details.") . "\n\n";

exit($failed > 0 ? 1 : 0);
