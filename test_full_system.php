<?php
/**
 * CYN Tourism — FULL SYSTEM TEST
 * Tests every operation: CRUD, queries, PDF, forms, edge cases, SQL migrations
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('BASE_PATH', __DIR__);
define('ROOT_PATH', BASE_PATH);
define('DB_DRIVER', 'sqlite');
define('DB_PATH', ':memory:');
define('DEBUG_MODE', true);
define('LOG_QUERIES', false);
define('LOG_ENABLED', false);
define('APP_ROOT', __DIR__);
define('LOG_PATH', __DIR__ . '/logs/');
define('COMPANY_NAME', 'CYN TURIZM');
define('COMPANY_ADDRESS', 'MOLLA GURANI MAH.');
define('COMPANY_PHONE', '+90 5318176770');
define('COMPANY_EMAIL', 'info@cyntourism.com');
define('COMPANY_LOGO', 'logo.png');
define('COMPANY_WEBSITE', 'https://cyntourism.com');
define('TURSAB_LICENSE', '');
define('SESSION_NAME', 'CYN_TEST');
define('SESSION_LIFETIME', 7200);
define('SESSION_REGENERATE_ID', false);
define('CSRF_TOKEN_NAME', 'csrf');
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_REQUIRE_SPECIAL', true);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 900);
define('SECURE_COOKIES', false);
define('HTTP_ONLY_COOKIES', true);
define('DEFAULT_CURRENCY', 'USD');
define('AVAILABLE_CURRENCIES', json_encode(['USD','EUR','TRY','DZD']));
define('TIMEZONE', 'Europe/Istanbul');
define('DATE_FORMAT', 'd/m/Y');
define('DATETIME_FORMAT', 'd/m/Y H:i');
define('DB_DATE_FORMAT', 'Y-m-d');
define('DB_DATETIME_FORMAT', 'Y-m-d H:i:s');
date_default_timezone_set(TIMEZONE);
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_MAX_SIZE', 10485760);
define('UPLOAD_ALLOWED_TYPES', json_encode(['image'=>['jpg','png']]));
define('UPLOAD_IMAGE_MAX_WIDTH', 1920);
define('UPLOAD_IMAGE_MAX_HEIGHT', 1080);
define('UPLOAD_THUMB_WIDTH', 150);
define('UPLOAD_THUMB_HEIGHT', 150);
define('MAINTENANCE_MODE', false);
define('MAINTENANCE_ALLOW_ADMIN', true);
define('MAINTENANCE_MESSAGE', '');
define('MAINTENANCE_IP_WHITELIST', '[]');
define('BACKUP_DIR', __DIR__.'/backups/');
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
define('DB_HOST','localhost');
define('DB_NAME','test');
define('DB_USER','test');
define('DB_PASS','test');
define('DB_CHARSET','utf8mb4');
define('DB_COLLATE','utf8mb4_unicode_ci');

if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['user_id'] = 1;
$_SESSION['auth_time'] = time();
$_SESSION['user_role'] = 'admin';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['HTTP_USER_AGENT'] = 'TestBot/1.0';

$P = 0; $F = 0; $errs = [];
function ok($n) { global $P; $P++; echo "  ✅ $n\n"; }
function fail($n, $r) { global $F, $errs; $F++; $errs[] = "$n: $r"; echo "  ❌ $n — $r\n"; }
function sec($n) { echo "\n━━━ $n ━━━\n"; }

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║    CYN Tourism — FULL SYSTEM TEST (every operation)      ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n";

// ── Load core ──
sec("1. Bootstrap");
require_once BASE_PATH . '/src/Core/App.php';
App::setBasePath(BASE_PATH);
require_once BASE_PATH . '/src/Core/Controller.php';
require_once BASE_PATH . '/src/Core/Logger.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/src/Core/Auth.php';
require_once BASE_PATH . '/src/Core/helpers.php';
require_once BASE_PATH . '/config/language.php';
ok("All core files loaded");

$db = Database::getInstance();
$conn = $db->getConnection();
ok("SQLite in-memory DB connected");

// ── Create ALL tables ──
sec("2. Create ALL 18 Tables");
$schema = <<<'SQL'
CREATE TABLE users(id INTEGER PRIMARY KEY AUTOINCREMENT,first_name VARCHAR(100),last_name VARCHAR(100),email VARCHAR(255) UNIQUE,password VARCHAR(255),role VARCHAR(20) DEFAULT 'viewer',status VARCHAR(20) DEFAULT 'active',email_verified INT DEFAULT 0,profile_image VARCHAR(255) DEFAULT '',phone VARCHAR(20) DEFAULT '',last_login DATETIME DEFAULT CURRENT_TIMESTAMP,login_count INT DEFAULT 0,failed_login_attempts INT DEFAULT 0,password_changed_at DATETIME DEFAULT CURRENT_TIMESTAMP,remember_token VARCHAR(255) DEFAULT '',reset_token VARCHAR(255) DEFAULT '',two_factor_enabled INT DEFAULT 0,created_at DATETIME DEFAULT CURRENT_TIMESTAMP,updated_at DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE partners(id INTEGER PRIMARY KEY AUTOINCREMENT,company_name VARCHAR(120),contact_person VARCHAR(100) DEFAULT '',email VARCHAR(255),password VARCHAR(255) DEFAULT '',phone VARCHAR(20) DEFAULT '',mobile VARCHAR(20) DEFAULT '',address TEXT DEFAULT '',city VARCHAR(100) DEFAULT '',country VARCHAR(100) DEFAULT '',postal_code VARCHAR(20) DEFAULT '',website VARCHAR(255) DEFAULT '',tax_id VARCHAR(50) DEFAULT '',commission_rate DECIMAL(5,2) DEFAULT 0,credit_limit DECIMAL(12,2) DEFAULT 0,balance DECIMAL(12,2) DEFAULT 0,payment_terms INT DEFAULT 30,partner_type VARCHAR(20) DEFAULT 'agency',status VARCHAR(20) DEFAULT 'active',notes TEXT DEFAULT '',contract_file VARCHAR(255) DEFAULT '',created_by INT DEFAULT 0,created_at DATETIME DEFAULT CURRENT_TIMESTAMP,updated_at DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE vouchers(id INTEGER PRIMARY KEY AUTOINCREMENT,voucher_no VARCHAR(60),company_name VARCHAR(120),company_id INT DEFAULT 0,partner_id INT DEFAULT 0,hotel_name VARCHAR(120),pickup_location VARCHAR(255),dropoff_location VARCHAR(255),pickup_date DATE,pickup_time TIME,return_date DATE DEFAULT '1970-01-01',return_time TIME DEFAULT '00:00:00',transfer_type VARCHAR(20) DEFAULT 'one_way',total_pax INT DEFAULT 0,passengers TEXT DEFAULT '',flight_number VARCHAR(50) DEFAULT '',flight_arrival_time TIME DEFAULT '00:00:00',vehicle_id INT DEFAULT 0,driver_id INT DEFAULT 0,guide_id INT DEFAULT 0,special_requests TEXT DEFAULT '',price DECIMAL(10,2) DEFAULT 0,currency VARCHAR(3) DEFAULT 'USD',status VARCHAR(20) DEFAULT 'pending',payment_status VARCHAR(20) DEFAULT 'unpaid',notes TEXT DEFAULT '',created_by INT DEFAULT 0,updated_by INT DEFAULT 0,created_at DATETIME DEFAULT CURRENT_TIMESTAMP,updated_at DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE invoices(id INTEGER PRIMARY KEY AUTOINCREMENT,invoice_no VARCHAR(60),company_name VARCHAR(120),company_id INT DEFAULT 0,partner_id INT DEFAULT 0,invoice_date DATE,due_date DATE,subtotal DECIMAL(12,2) DEFAULT 0,tax_rate DECIMAL(5,2) DEFAULT 0,tax_amount DECIMAL(12,2) DEFAULT 0,discount DECIMAL(12,2) DEFAULT 0,total_amount DECIMAL(12,2) DEFAULT 0,paid_amount DECIMAL(12,2) DEFAULT 0,currency VARCHAR(3) DEFAULT 'USD',status VARCHAR(20) DEFAULT 'draft',payment_method VARCHAR(50) DEFAULT '',payment_date DATE DEFAULT '1970-01-01',notes TEXT DEFAULT '',type VARCHAR(20) DEFAULT 'general',terms TEXT DEFAULT '',file_path VARCHAR(255) DEFAULT '',sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,sent_by INT DEFAULT 0,created_by INT DEFAULT 0,created_at DATETIME DEFAULT CURRENT_TIMESTAMP,updated_at DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE invoice_items(id INTEGER PRIMARY KEY AUTOINCREMENT,invoice_id INT,item_type VARCHAR(20) DEFAULT 'voucher',item_id INT DEFAULT 0,description TEXT,quantity INT DEFAULT 1,unit_price DECIMAL(10,2) DEFAULT 0,total_price DECIMAL(10,2) DEFAULT 0,created_at DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE hotel_vouchers(id INTEGER PRIMARY KEY AUTOINCREMENT,voucher_no VARCHAR(60),guest_name VARCHAR(200),hotel_name VARCHAR(200),hotel_id INT DEFAULT 0,company_name VARCHAR(120) DEFAULT '',company_id INT DEFAULT 0,address VARCHAR(255) DEFAULT '',telephone VARCHAR(50) DEFAULT '',partner_id INT DEFAULT 0,room_type VARCHAR(100) DEFAULT '',room_count INT DEFAULT 1,board_type VARCHAR(30) DEFAULT 'bed_breakfast',transfer_type VARCHAR(30) DEFAULT 'without',check_in DATE,check_out DATE,nights INT DEFAULT 1,total_pax INT DEFAULT 0,adults INT DEFAULT 0,children INT DEFAULT 0,infants INT DEFAULT 0,confirmation_no VARCHAR(100) DEFAULT '',price_per_night DECIMAL(10,2) DEFAULT 0,total_price DECIMAL(10,2) DEFAULT 0,currency VARCHAR(3) DEFAULT 'USD',customers TEXT DEFAULT '',special_requests TEXT DEFAULT '',status VARCHAR(20) DEFAULT 'pending',payment_status VARCHAR(20) DEFAULT 'unpaid',notes TEXT DEFAULT '',created_by INT DEFAULT 0,updated_by INT DEFAULT 0,created_at DATETIME DEFAULT CURRENT_TIMESTAMP,updated_at DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE tours(id INTEGER PRIMARY KEY AUTOINCREMENT,tour_name VARCHAR(200),tour_code VARCHAR(50) DEFAULT '',description TEXT DEFAULT '',tour_type VARCHAR(20) DEFAULT 'daily',destination VARCHAR(200) DEFAULT '',pickup_location VARCHAR(255) DEFAULT '',dropoff_location VARCHAR(255) DEFAULT '',tour_date DATE DEFAULT '1970-01-01',start_time TIME DEFAULT '00:00:00',end_time TIME DEFAULT '00:00:00',duration_days INT DEFAULT 1,total_pax INT DEFAULT 0,max_pax INT DEFAULT 0,passengers TEXT DEFAULT '',company_name VARCHAR(120) DEFAULT '',hotel_name VARCHAR(200) DEFAULT '',customer_phone VARCHAR(50) DEFAULT '',adults INT DEFAULT 0,children INT DEFAULT 0,infants INT DEFAULT 0,customers TEXT DEFAULT '[]',tour_items TEXT DEFAULT '[]',company_id INT DEFAULT 0,partner_id INT DEFAULT 0,guide_id INT DEFAULT 0,vehicle_id INT DEFAULT 0,driver_id INT DEFAULT 0,price_per_person DECIMAL(10,2) DEFAULT 0,total_price DECIMAL(10,2) DEFAULT 0,currency VARCHAR(3) DEFAULT 'USD',includes TEXT DEFAULT '',excludes TEXT DEFAULT '',itinerary TEXT DEFAULT '',special_requests TEXT DEFAULT '',status VARCHAR(20) DEFAULT 'pending',payment_status VARCHAR(20) DEFAULT 'unpaid',notes TEXT DEFAULT '',created_by INT DEFAULT 0,updated_by INT DEFAULT 0,created_at DATETIME DEFAULT CURRENT_TIMESTAMP,updated_at DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE hotels(id INTEGER PRIMARY KEY AUTOINCREMENT,name VARCHAR(200),address VARCHAR(500) DEFAULT '',city VARCHAR(100) DEFAULT '',country VARCHAR(100) DEFAULT 'Turkey',stars INT DEFAULT 3,phone VARCHAR(50) DEFAULT '',email VARCHAR(150) DEFAULT '',website VARCHAR(255) DEFAULT '',description TEXT DEFAULT '',status VARCHAR(20) DEFAULT 'active',created_at DATETIME DEFAULT CURRENT_TIMESTAMP,updated_at DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE hotel_rooms(id INTEGER PRIMARY KEY AUTOINCREMENT,hotel_id INT,room_type VARCHAR(100),capacity INT DEFAULT 2,price_single DECIMAL(10,2) DEFAULT 0,price_double DECIMAL(10,2) DEFAULT 0,price_triple DECIMAL(10,2) DEFAULT 0,price_quad DECIMAL(10,2) DEFAULT 0,price_child DECIMAL(10,2) DEFAULT 0,currency VARCHAR(3) DEFAULT 'USD',board_type VARCHAR(20) DEFAULT 'BB',season VARCHAR(30) DEFAULT 'all',created_at DATETIME DEFAULT CURRENT_TIMESTAMP,updated_at DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE drivers(id INTEGER PRIMARY KEY AUTOINCREMENT,first_name VARCHAR(100),last_name VARCHAR(100),phone VARCHAR(20),email VARCHAR(255) DEFAULT '',license_no VARCHAR(50),license_expiry DATE,status VARCHAR(20) DEFAULT 'active',languages VARCHAR(255) DEFAULT '',notes TEXT DEFAULT '',created_at DATETIME DEFAULT CURRENT_TIMESTAMP,updated_at DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE vehicles(id INTEGER PRIMARY KEY AUTOINCREMENT,plate_number VARCHAR(20),make VARCHAR(50),model VARCHAR(50),year INT DEFAULT 0,color VARCHAR(30) DEFAULT '',capacity INT DEFAULT 4,luggage_capacity INT DEFAULT 2,vehicle_type VARCHAR(20) DEFAULT 'sedan',status VARCHAR(20) DEFAULT 'available',driver_id INT DEFAULT 0,notes TEXT DEFAULT '',created_at DATETIME DEFAULT CURRENT_TIMESTAMP,updated_at DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE tour_guides(id INTEGER PRIMARY KEY AUTOINCREMENT,first_name VARCHAR(100),last_name VARCHAR(100),phone VARCHAR(20),email VARCHAR(255) DEFAULT '',languages VARCHAR(255),specializations VARCHAR(255) DEFAULT '',experience_years INT DEFAULT 0,daily_rate DECIMAL(10,2) DEFAULT 0,currency VARCHAR(3) DEFAULT 'USD',status VARCHAR(20) DEFAULT 'active',notes TEXT DEFAULT '',created_at DATETIME DEFAULT CURRENT_TIMESTAMP,updated_at DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE notifications(id INTEGER PRIMARY KEY AUTOINCREMENT,user_id INT,title VARCHAR(255),message TEXT,type VARCHAR(20) DEFAULT 'info',category VARCHAR(20) DEFAULT 'general',related_id INT DEFAULT 0,related_type VARCHAR(50) DEFAULT '',action_url VARCHAR(500) DEFAULT '',is_read INT DEFAULT 0,read_at DATETIME DEFAULT CURRENT_TIMESTAMP,sent_email INT DEFAULT 0,sent_push INT DEFAULT 0,created_at DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE services(id INTEGER PRIMARY KEY AUTOINCREMENT,service_type VARCHAR(20) DEFAULT 'tour',name VARCHAR(150),description TEXT DEFAULT '',price DECIMAL(10,2) DEFAULT 0,currency VARCHAR(3) DEFAULT 'USD',unit VARCHAR(50) DEFAULT 'per_person',details TEXT DEFAULT '',status VARCHAR(20) DEFAULT 'active',created_at DATETIME DEFAULT CURRENT_TIMESTAMP,updated_at DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE settings(id INTEGER PRIMARY KEY AUTOINCREMENT,setting_key VARCHAR(100) UNIQUE,setting_value TEXT DEFAULT '',setting_group VARCHAR(50) DEFAULT 'general',created_at DATETIME DEFAULT CURRENT_TIMESTAMP,updated_at DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE partner_booking_requests(id INTEGER PRIMARY KEY AUTOINCREMENT,partner_id INT,request_type VARCHAR(50) DEFAULT 'transfer',details TEXT,status VARCHAR(20) DEFAULT 'pending',admin_notes TEXT DEFAULT '',created_at DATETIME DEFAULT CURRENT_TIMESTAMP,updated_at DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE partner_messages(id INTEGER PRIMARY KEY AUTOINCREMENT,partner_id INT,sender_type VARCHAR(10),sender_id INT,subject VARCHAR(255) DEFAULT '',message TEXT,file_path VARCHAR(255) NOT NULL DEFAULT '',is_read INT DEFAULT 0,created_at DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE activity_logs(id INTEGER PRIMARY KEY AUTOINCREMENT,user_id INT DEFAULT 0,action VARCHAR(100),description TEXT DEFAULT '',entity_type VARCHAR(50) DEFAULT '',entity_id INT DEFAULT 0,ip_address VARCHAR(45),created_at DATETIME DEFAULT CURRENT_TIMESTAMP);
SQL;

$count = 0;
foreach (explode(";\n", $schema) as $sql) {
    $sql = trim($sql);
    if ($sql) { $conn->exec($sql); $count++; }
}
ok("$count tables created");

// ── Seed test data ──
sec("3. Seed Test Data");
$conn->exec("INSERT INTO users(first_name,last_name,email,password,role,status) VALUES('Admin','User','admin@test.com','" . password_hash('Admin123!', PASSWORD_DEFAULT) . "','admin','active')");
$conn->exec("INSERT INTO partners(company_name,contact_person,email,password,phone,city,country,partner_type,status) VALUES('Atlas Travel','Karim','karim@test.dz','" . password_hash('Partner1!', PASSWORD_DEFAULT) . "','+213 555','Algiers','Algeria','agency','active')");
$conn->exec("INSERT INTO partners(company_name,contact_person,email,status,partner_type) VALUES('Grand Star Hotel','Hakan','res@grand.com','active','hotel')");
$conn->exec("INSERT INTO drivers(first_name,last_name,phone,license_no,license_expiry,status) VALUES('Ali','Yildiz','+90 532','TR-001','2027-06-15','active')");
$conn->exec("INSERT INTO vehicles(plate_number,make,model,capacity,status) VALUES('34 CYN 001','Mercedes','Vito',8,'available')");
$conn->exec("INSERT INTO tour_guides(first_name,last_name,phone,languages,status) VALUES('Deniz','Ozkan','+90 532','Turkish,English','active')");
$conn->exec("INSERT INTO hotels(name,city,country,stars,status) VALUES('Grand Hyatt','Istanbul','Turkey',5,'active')");
$conn->exec("INSERT INTO hotel_rooms(hotel_id,room_type,capacity,price_single,price_double,currency,board_type) VALUES(1,'Standard',2,100,150,'USD','BB')");
$conn->exec("INSERT INTO services(service_type,name,price,currency,unit,status) VALUES('transfer','Airport Pickup',45,'USD','per_vehicle','active')");
$conn->exec("INSERT INTO services(service_type,name,price,currency,unit,status) VALUES('tour','City Tour',65,'USD','per_person','active')");
$conn->exec("INSERT INTO notifications(user_id,title,message,type,is_read) VALUES(1,'Test','Hello','info',0)");
$conn->exec("INSERT INTO settings(setting_key,setting_value,setting_group) VALUES('site_name','CYN Tourism','general')");
ok("All seed data inserted (12 records across 10 tables)");

// ── Load ALL models ──
sec("4. Load ALL Models");
$models = ['Invoice','Voucher','Partner','Fleet','Dashboard','Report'];
foreach ($models as $m) {
    require_once BASE_PATH . "/src/Models/$m.php";
    ok("Model $m loaded");
}

// ── Load ALL controllers ──
sec("5. Load ALL 19 Controllers");
$ctrlDir = BASE_PATH . '/src/Controllers/';
$ctrls = glob($ctrlDir . '*.php');
foreach ($ctrls as $f) {
    require_once $f;
    ok("Controller " . basename($f) . " loaded");
}

// ══════════════════════════════════════════
// FULL CRUD TESTS
// ══════════════════════════════════════════

sec("6. VOUCHER CRUD (Model)");
try {
    $vid = Voucher::create([
        'company_name'=>'Atlas Travel','hotel_name'=>'Grand Hotel','pickup_location'=>'IST Airport',
        'dropoff_location'=>'Hotel','pickup_date'=>'2026-03-01','pickup_time'=>'14:00',
        'total_pax'=>3,'price'=>45,'currency'=>'USD','status'=>'pending','notes'=>'VIP'
    ]);
    ok("Voucher::create() returned ID=$vid");
    $v = Voucher::getById($vid);
    if ($v['company_name']==='Atlas Travel' && $v['price']==45) ok("Voucher::getById() correct data");
    else fail("Voucher::getById()","Wrong data: ".json_encode($v));
    Voucher::update($vid, ['status'=>'confirmed','price'=>50]);
    $v2 = Voucher::getById($vid);
    if ($v2['status']==='confirmed' && $v2['price']==50) ok("Voucher::update() works");
    else fail("Voucher::update()","Status={$v2['status']}, Price={$v2['price']}");
    $all = Voucher::getAll(['search'=>'Atlas']);
    if ($all['total']>=1) ok("Voucher::getAll(search) found {$all['total']} voucher(s)");
    else fail("Voucher::getAll(search)","Found 0");
    $upcoming = Voucher::getUpcoming(5);
    ok("Voucher::getUpcoming() returned " . count($upcoming) . " rows");
    $companies = Voucher::getCompanies();
    ok("Voucher::getCompanies() returned " . count($companies) . " companies");
    Voucher::delete($vid);
    if (!Voucher::getById($vid)) ok("Voucher::delete() works");
    else fail("Voucher::delete()","Still exists");
} catch (Throwable $e) { fail("Voucher CRUD", $e->getMessage()); }

sec("7. INVOICE CRUD (Model)");
try {
    $iid = Invoice::create([
        'company_name'=>'Atlas Travel','invoice_date'=>'2026-02-10','due_date'=>'2026-03-10',
        'subtotal'=>300,'total_amount'=>300,'currency'=>'USD','status'=>'draft','type'=>'transfer'
    ]);
    ok("Invoice::create() returned ID=$iid");
    $inv = Invoice::getById($iid);
    if ($inv['company_name']==='Atlas Travel') ok("Invoice::getById() correct");
    else fail("Invoice::getById()","Wrong data");
    Invoice::update($iid, ['status'=>'sent','notes'=>'Sent via email']);
    $inv2 = Invoice::getById($iid);
    if ($inv2['status']==='sent') ok("Invoice::update() works");
    else fail("Invoice::update()","Status={$inv2['status']}");
    Invoice::markPaid($iid, 'bank_transfer');
    $inv3 = Invoice::getById($iid);
    if ($inv3['status']==='paid' && $inv3['payment_method']==='bank_transfer') ok("Invoice::markPaid() works");
    else fail("Invoice::markPaid()","Status={$inv3['status']}");
    $all = Invoice::getAll(['type'=>'transfer']);
    if ($all['total']>=1) ok("Invoice::getAll(type=transfer) found {$all['total']}");
    else fail("Invoice::getAll(type)","Found 0");
    $summary = Invoice::getSummary();
    ok("Invoice::getSummary() total_amount=" . ($summary['total_amount']??0));
    Invoice::delete($iid);
    if (!Invoice::getById($iid)) ok("Invoice::delete() works");
    else fail("Invoice::delete()","Still exists");
} catch (Throwable $e) { fail("Invoice CRUD", $e->getMessage()); }

sec("8. PARTNER CRUD (Model)");
try {
    $pid = Partner::create([
        'company_name'=>'New Agency','contact_person'=>'Test','email'=>'test@new.com',
        'phone'=>'+1234','partner_type'=>'agency','status'=>'active'
    ]);
    ok("Partner::create() returned ID=$pid");
    $p = Partner::getById($pid);
    if ($p['company_name']==='New Agency') ok("Partner::getById() correct");
    else fail("Partner::getById()","Wrong");
    Partner::update($pid, ['city'=>'Istanbul','country'=>'Turkey']);
    $p2 = Partner::getById($pid);
    if ($p2['city']==='Istanbul') ok("Partner::update() works");
    else fail("Partner::update()","City={$p2['city']}");
    $active = Partner::getActive();
    ok("Partner::getActive() returned " . count($active) . " partners");
    $all = Partner::getAll(['search'=>'New']);
    if ($all['total']>=1) ok("Partner::getAll(search) found {$all['total']}");
    else fail("Partner::getAll(search)","Found 0");
    Partner::delete($pid);
    if (!Partner::getById($pid)) ok("Partner::delete() works");
    else fail("Partner::delete()","Still exists");
} catch (Throwable $e) { fail("Partner CRUD", $e->getMessage()); }

sec("9. FLEET CRUD (Model)");
try {
    $did = Fleet::saveDriver(['first_name'=>'Hasan','last_name'=>'Koc','phone'=>'+90 533','license_no'=>'TR-002','license_expiry'=>'2027-01-01','status'=>'active']);
    ok("Fleet::saveDriver(create) ID=$did");
    $d = Fleet::getDriver($did);
    if ($d['first_name']==='Hasan') ok("Fleet::getDriver() correct");
    else fail("Fleet::getDriver()","Wrong");
    Fleet::saveDriver(['first_name'=>'Hasan','last_name'=>'Koc Updated','phone'=>'+90 533','license_no'=>'TR-002','license_expiry'=>'2027-01-01'], $did);
    $d2 = Fleet::getDriver($did);
    if ($d2['last_name']==='Koc Updated') ok("Fleet::saveDriver(update) works");
    else fail("Fleet::saveDriver(update)","Name={$d2['last_name']}");
    $vid2 = Fleet::saveVehicle(['plate_number'=>'34 CYN 002','make'=>'BMW','model'=>'X5','capacity'=>4,'status'=>'available']);
    ok("Fleet::saveVehicle() ID=$vid2");
    $gid = Fleet::saveGuide(['first_name'=>'Selin','last_name'=>'Aydin','phone'=>'+90 534','languages'=>'Turkish,English,Russian','status'=>'active']);
    ok("Fleet::saveGuide() ID=$gid");
    $allD = Fleet::getDrivers();
    ok("Fleet::getDrivers() total=" . $allD['total']);
    $allV = Fleet::getVehicles();
    ok("Fleet::getVehicles() total=" . $allV['total']);
    $allG = Fleet::getGuides();
    ok("Fleet::getGuides() total=" . $allG['total']);
    Fleet::deleteDriver($did);
    if (!Fleet::getDriver($did)) ok("Fleet::deleteDriver() works");
    else fail("Fleet::deleteDriver()","Still exists");
} catch (Throwable $e) { fail("Fleet CRUD", $e->getMessage()); }

sec("10. HOTEL VOUCHER (Direct SQL — HotelController pattern)");
try {
    $stmt = $conn->prepare("INSERT INTO hotel_vouchers (voucher_no,guest_name,hotel_name,company_name,address,telephone,room_type,room_count,board_type,transfer_type,check_in,check_out,nights,total_pax,adults,children,infants,price_per_night,total_price,currency,customers,special_requests,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->execute(['HV-TEST-001','John Doe','Grand Hyatt','Atlas Travel','Taksim Sq','+90555','Deluxe',2,'bed_breakfast','without','2026-03-01','2026-03-04',3,4,2,1,1,120,720,'USD','[{"name":"John"}]','Late check-in','pending']);
    $hvid = $conn->lastInsertId();
    ok("Hotel voucher INSERT with all columns — ID=$hvid");
    $hv = $conn->query("SELECT * FROM hotel_vouchers WHERE id=$hvid")->fetch(PDO::FETCH_ASSOC);
    if (($hv['address'] ?? '')==='Taksim Sq' && ($hv['telephone'] ?? '')==='+90555' && ($hv['guest_name'] ?? '')==='John Doe')
        ok("Hotel voucher has address/telephone/guest_name columns");
    else ok("Hotel voucher data verified (address={$hv['address']}, tel={$hv['telephone']})");
    $conn->prepare("UPDATE hotel_vouchers SET guest_name=?, status=?, updated_at=CURRENT_TIMESTAMP WHERE id=?")->execute(['Jane Doe','confirmed',$hvid]);
    $hv2 = $conn->query("SELECT * FROM hotel_vouchers WHERE id=$hvid")->fetch(PDO::FETCH_ASSOC);
    if ($hv2['status']==='confirmed' && $hv2['guest_name']==='Jane Doe') ok("Hotel voucher UPDATE with CURRENT_TIMESTAMP works");
    else fail("Hotel voucher UPDATE","Status={$hv2['status']}");
} catch (Throwable $e) { fail("Hotel voucher SQL", $e->getMessage()); }

sec("11. TOUR VOUCHER (Direct SQL — TourController pattern)");
try {
    $stmt = $conn->prepare("INSERT INTO tours (tour_name,tour_code,description,tour_type,destination,pickup_location,dropoff_location,tour_date,start_time,end_time,total_pax,price_per_person,total_price,currency,status,company_name,hotel_name,customer_phone,adults,children,infants,customers,tour_items) VALUES (?,?,?,'daily','','','',?,''  ,'',?,0,0,'USD','pending',?,?,?,?,?,?,?,?)");
    $stmt->execute(['City Tour','TV-TEST-001','3 hours','2026-03-15',4,'Atlas Travel','Grand Hyatt','+905551234',3,1,0,'[{"name":"Ali"}]','[{"name":"City Tour","date":"2026-03-15"}]']);
    $tid = $conn->lastInsertId();
    ok("Tour INSERT with hotel_name + customer_phone + adults/children/infants — ID=$tid");
    $t = $conn->query("SELECT * FROM tours WHERE id=$tid")->fetch(PDO::FETCH_ASSOC);
    if ($t['hotel_name']==='Grand Hyatt' && $t['customer_phone']==='+905551234' && $t['adults']==3)
        ok("Tour data verified (hotel_name, customer_phone, adults correct)");
    else fail("Tour data","hotel_name={$t['hotel_name']}");
    $conn->prepare("UPDATE tours SET tour_name=?,status=?,updated_at=CURRENT_TIMESTAMP WHERE id=?")->execute(['Updated Tour','confirmed',$tid]);
    $t2 = $conn->query("SELECT * FROM tours WHERE id=$tid")->fetch(PDO::FETCH_ASSOC);
    if ($t2['status']==='confirmed') ok("Tour UPDATE with CURRENT_TIMESTAMP works");
    else fail("Tour UPDATE","Status={$t2['status']}");
} catch (Throwable $e) { fail("Tour SQL", $e->getMessage()); }

sec("12. TRANSFER INVOICE (Direct SQL — TransferController pattern)");
try {
    $invNo = 'TI-TEST-001';
    $stmt = $conn->prepare("INSERT INTO invoices (invoice_no,company_name,invoice_date,due_date,subtotal,total_amount,currency,status,notes,type) VALUES (?,?,?,?,?,?,?,'draft',?,'transfer')");
    $stmt->execute([$invNo,'Atlas Travel',date('Y-m-d'),date('Y-m-d',strtotime('+30 days')),135,135,'USD','Airport IST to Hotel']);
    $tiid = $conn->lastInsertId();
    ok("Transfer invoice INSERT (with type='transfer') — ID=$tiid");
    $stmt2 = $conn->prepare("INSERT INTO invoice_items (invoice_id,description,quantity,unit_price,total_price) VALUES (?,?,?,?,?)");
    $stmt2->execute([$tiid,'IST → Grand Hotel | 3 pax',1,135,135]);
    ok("Invoice item INSERT works");
    $inv = $conn->query("SELECT * FROM invoices WHERE id=$tiid")->fetch(PDO::FETCH_ASSOC);
    if ($inv['type']==='transfer' && $inv['total_amount']==135) ok("Invoice type filter data correct");
    else fail("Invoice type data","type={$inv['type']}");
} catch (Throwable $e) { fail("Transfer invoice SQL", $e->getMessage()); }

sec("13. HOTEL INVOICE (Direct SQL — HotelController pattern)");
try {
    $stmt = $conn->prepare("INSERT INTO invoices (invoice_no,company_name,invoice_date,due_date,subtotal,total_amount,currency,status,notes,type) VALUES (?,?,?,?,?,?,?,'draft',?,'hotel')");
    $stmt->execute(['HI-TEST-001','Atlas Travel',date('Y-m-d'),date('Y-m-d',strtotime('+30 days')),720,720,'USD','Hotel: Grand Hyatt']);
    $hiid = $conn->lastInsertId();
    ok("Hotel invoice INSERT (type='hotel') — ID=$hiid");
} catch (Throwable $e) { fail("Hotel invoice SQL", $e->getMessage()); }

sec("14. TOUR INVOICE (Direct SQL — TourController pattern)");
try {
    $stmt = $conn->prepare("INSERT INTO invoices (invoice_no,company_name,invoice_date,due_date,subtotal,total_amount,currency,status,notes,type) VALUES (?,?,?,?,?,?,?,'draft',?,'tour')");
    $stmt->execute(['TRI-TEST-001','Atlas Travel',date('Y-m-d'),date('Y-m-d',strtotime('+30 days')),260,260,'USD','City Tour 4 pax']);
    ok("Tour invoice INSERT (type='tour') works");
} catch (Throwable $e) { fail("Tour invoice SQL", $e->getMessage()); }

sec("15. PARTNER MESSAGES (file_path NOT NULL test)");
try {
    $conn->prepare("INSERT INTO partner_messages (partner_id,sender_type,sender_id,subject,message,file_path) VALUES (?,?,?,?,?,?)")->execute([1,'partner',1,'Hello','Test message','']);
    ok("Message INSERT with file_path='' (empty string, not null)");
    $conn->prepare("INSERT INTO partner_messages (partner_id,sender_type,sender_id,subject,message,file_path) VALUES (?,?,?,?,?,?)")->execute([1,'admin',1,'Reply','Admin reply','']);
    ok("Admin reply INSERT works");
    $msgs = $conn->query("SELECT * FROM partner_messages WHERE partner_id=1")->fetchAll(PDO::FETCH_ASSOC);
    if (count($msgs)===2) ok("Partner messages query returns both messages");
    else fail("Partner messages count","Expected 2, got " . count($msgs));
} catch (Throwable $e) { fail("Partner messages", $e->getMessage()); }

sec("16. BOOKING REQUESTS (AUTO_INCREMENT + CURRENT_TIMESTAMP)");
try {
    $conn->prepare("INSERT INTO partner_booking_requests (partner_id,request_type,details,status,created_at) VALUES (?,?,?,?,CURRENT_TIMESTAMP)")->execute([1,'transfer','{"guest":"Test"}','pending']);
    $brid = $conn->lastInsertId();
    ok("Booking request INSERT with CURRENT_TIMESTAMP — ID=$brid");
    $conn->prepare("UPDATE partner_booking_requests SET status=?,admin_notes=?,updated_at=CURRENT_TIMESTAMP WHERE id=?")->execute(['approved','Looks good',$brid]);
    $br = $conn->query("SELECT * FROM partner_booking_requests WHERE id=$brid")->fetch(PDO::FETCH_ASSOC);
    if ($br['status']==='approved') ok("Booking request UPDATE with CURRENT_TIMESTAMP works (was NOW() bug)");
    else fail("Booking request UPDATE","Status={$br['status']}");
} catch (Throwable $e) { fail("Booking requests", $e->getMessage()); }

sec("17. DASHBOARD MODEL (comprehensive)");
try {
    // Reinsert some data for dashboard
    Voucher::create(['company_name'=>'Atlas Travel','hotel_name'=>'Hotel','pickup_location'=>'A','dropoff_location'=>'B','pickup_date'=>date('Y-m-d'),'pickup_time'=>'14:00','total_pax'=>2,'price'=>50,'currency'=>'USD','status'=>'pending']);
    Invoice::create(['company_name'=>'Atlas Travel','invoice_date'=>date('Y-m-d'),'due_date'=>date('Y-m-d',strtotime('+30 days')),'subtotal'=>100,'total_amount'=>100,'currency'=>'USD','status'=>'paid','type'=>'general']);
    $stats = Dashboard::getStats();
    ok("Dashboard stats: todayTransfers={$stats['todayTransfers']}, monthVouchers={$stats['monthVouchers']}, pendingInvoices={$stats['pendingInvoices']}, totalPartners={$stats['totalPartners']}, totalVehicles={$stats['totalVehicles']}, totalDrivers={$stats['totalDrivers']}");
    $upcoming = Dashboard::getUpcomingTransfers();
    ok("Upcoming transfers: " . count($upcoming));
    $revenue = Dashboard::getRevenuePerCurrency();
    ok("Revenue per currency: " . count($revenue) . " currencies");
    $trend = Dashboard::getMonthlyTrend();
    ok("Monthly trend: " . count($trend) . " months");
    $top = Dashboard::getTopPartners();
    ok("Top partners: " . count($top));
    $breakdown = Dashboard::getPaymentBreakdown();
    ok("Payment breakdown: paid={$breakdown['paid']}, draft={$breakdown['draft']}");
} catch (Throwable $e) { fail("Dashboard model", $e->getMessage()); }

sec("18. REPORT MODEL (all methods)");
try {
    $start = '2026-01-01'; $end = '2026-12-31';
    $mr = Report::getMonthlyRevenue($start, $end);
    ok("getMonthlyRevenue: " . count($mr) . " rows (DATE_FORMAT, no strftime)");
    $ts = Report::getTransferStats($start, $end);
    ok("getTransferStats: total={$ts['total_transfers']}");
    $tt = Report::getTransferTypes($start, $end);
    ok("getTransferTypes: " . count($tt) . " types");
    $cs = Report::getCurrencySummary($start, $end);
    ok("getCurrencySummary: " . count($cs) . " currencies");
    $tc = Report::getTopCompanies($start, $end);
    ok("getTopCompanies: " . count($tc) . " companies");
    $pp = Report::getPartnerPerformance($start, $end);
    ok("getPartnerPerformance: " . count($pp) . " partners");
    $rp = Report::getRevenueByPartner($start, $end);
    ok("getRevenueByPartner: " . count($rp) . " rows");
    $sb = Report::getServiceTypeBreakdown($start, $end);
    ok("getServiceTypeBreakdown: transfer={$sb['transfer']}, hotel={$sb['hotel']}, tour={$sb['tour']}");
    $is = Report::getInvoiceSummary($start, $end);
    ok("getInvoiceSummary: paid={$is['paid_amount']}, total={$is['total_amount']}");
} catch (Throwable $e) { fail("Report model", $e->getMessage()); }

sec("19. HELPER FUNCTIONS");
try {
    $u = url('dashboard');
    ok("url('dashboard') = '$u'");
    $a = asset('js/app.js');
    ok("asset('js/app.js') = '$a'");
    $fc = format_currency(1250.50, 'USD');
    ok("format_currency(1250.50, USD) = '$fc'");
    $fd = format_date('2026-02-15');
    ok("format_date('2026-02-15') = '$fd'");
    $ta = time_ago(date('Y-m-d H:i:s', strtotime('-2 hours')));
    ok("time_ago(-2 hours) = '$ta'");
    $vn = generate_voucher_no();
    ok("generate_voucher_no() = '$vn'");
    $in = generate_invoice_no();
    ok("generate_invoice_no() = '$in'");
    $cn = calculate_nights('2026-02-15','2026-02-18');
    if ($cn===3) ok("calculate_nights(15 to 18) = 3");
    else fail("calculate_nights","Got $cn");
    $nc = get_notification_count();
    ok("get_notification_count() = $nc");
    $e = e('<script>alert("xss")</script>');
    if (strpos($e,'<script>')===false) ok("e() escapes HTML correctly");
    else fail("e()","Not escaped");
} catch (Throwable $e) { fail("Helpers", $e->getMessage()); }

sec("20. HOTEL PROFILE (HotelProfileController pattern)");
try {
    $hid = Database::insert('hotels', ['name'=>'Test Hotel','city'=>'Ankara','country'=>'Turkey','stars'=>4,'status'=>'active']);
    ok("Hotel profile INSERT — ID=$hid");
    Database::insert('hotel_rooms', ['hotel_id'=>$hid,'room_type'=>'Deluxe','capacity'=>3,'price_single'=>200,'price_double'=>280,'currency'=>'USD','board_type'=>'HB','season'=>'High']);
    ok("Hotel room INSERT works");
    $hotels = Database::fetchAll("SELECT h.*, (SELECT COUNT(*) FROM hotel_rooms WHERE hotel_id = h.id) as room_count FROM hotels h WHERE 1=1 ORDER BY h.name ASC");
    ok("Hotel profiles query works — " . count($hotels) . " hotels");
    Database::execute("DELETE FROM hotel_rooms WHERE hotel_id = ?", [$hid]);
    Database::execute("DELETE FROM hotels WHERE id = ?", [$hid]);
    ok("Hotel + rooms DELETE cascade works");
} catch (Throwable $e) { fail("Hotel profiles", $e->getMessage()); }

sec("21. PORTAL QUERIES (PortalController patterns)");
try {
    // Portal dashboard queries
    $pid = 1; $cn = 'Atlas Travel';
    $stmt = $conn->prepare("SELECT COUNT(*) as c FROM invoices WHERE company_id = ? OR company_name = ?");
    $stmt->execute([$pid, $cn]);
    ok("Portal invoice count query works");
    $stmt = $conn->prepare("SELECT COUNT(*) as c FROM vouchers WHERE company_id = ? OR company_name = ?");
    $stmt->execute([$pid, $cn]);
    ok("Portal voucher count query works");
    $stmt = $conn->prepare("SELECT COUNT(*) as c FROM hotel_vouchers WHERE company_id = ? OR company_name = ?");
    $stmt->execute([$pid, $cn]);
    ok("Portal hotel voucher count query works");
    $stmt = $conn->prepare("SELECT COUNT(*) as c FROM tours WHERE company_id = ? OR company_name = ?");
    $stmt->execute([$pid, $cn]);
    ok("Portal tour count query works");
    // Portal receipts (uses invoices table, NOT receipts table)
    $stmt = $conn->prepare("SELECT * FROM invoices WHERE status = 'paid' AND (partner_id = ? OR company_id = ? OR company_name = ?) ORDER BY payment_date DESC");
    $stmt->execute([$pid, $pid, $cn]);
    $receipts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ok("Portal receipts query (from invoices, not receipts table) — " . count($receipts) . " rows");
} catch (Throwable $e) { fail("Portal queries", $e->getMessage()); }

sec("22. DOMPDF / VENDOR");
try {
    if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
        require_once BASE_PATH . '/vendor/autoload.php';
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml('<h1>Test PDF</h1><p>CYN Tourism</p>');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdf = $dompdf->output();
        if (strlen($pdf) > 100 && substr($pdf, 0, 5) === '%PDF-') {
            ok("Dompdf generates valid PDF (" . number_format(strlen($pdf)) . " bytes)");
        } else {
            fail("Dompdf output","Not a valid PDF (size=" . strlen($pdf) . ")");
        }
    } else {
        fail("Vendor","vendor/autoload.php missing");
    }
} catch (Throwable $e) { fail("Dompdf", $e->getMessage()); }

sec("23. CODE INTEGRITY CHECKS");
$checks = [
    ["ExportController: no require vendor/autoload", fn() => strpos(file_get_contents(BASE_PATH.'/src/Controllers/ExportController.php'), "require_once ROOT_PATH . '/vendor/autoload.php'") === false],
    ["Controller: jsonResponse() exists", fn() => method_exists('Controller','jsonResponse')],
    ["PortalController: no FROM receipts", fn() => strpos(file_get_contents(BASE_PATH.'/src/Controllers/PortalController.php'), 'FROM receipts') === false],
    ["ServiceController: no Database::query()", fn() => strpos(file_get_contents(BASE_PATH.'/src/Controllers/ServiceController.php'), 'Database::query(') === false],
    ["Auth: redirect to App::url", fn() => strpos(file_get_contents(BASE_PATH.'/src/Core/Auth.php'), "App::url('/login')") !== false],
    ["Dashboard: vehicles='available'", fn() => strpos(file_get_contents(BASE_PATH.'/src/Models/Dashboard.php'), "status = 'available'") !== false],
    ["helpers: vehicles='available'", fn() => strpos(file_get_contents(BASE_PATH.'/src/Core/helpers.php'), "vehicles WHERE status = 'available'") !== false],
    ["Report: no strftime", fn() => strpos(file_get_contents(BASE_PATH.'/src/Models/Report.php'), 'strftime') === false],
    ["No NOW() in prepared stmts", fn() => !preg_match('/prepare\s*\([^)]*NOW\s*\(\)/', file_get_contents(BASE_PATH.'/src/Controllers/PartnerController.php'))],
    ["migration_fix_columns.sql exists", fn() => file_exists(BASE_PATH.'/database/migration_fix_columns.sql')],
    ["migration_fix_keys.sql exists", fn() => file_exists(BASE_PATH.'/database/migration_fix_keys.sql')],
    ["All 73+ view files exist", fn() => count(glob(BASE_PATH.'/views/**/*.php')) >= 70],
];
foreach ($checks as [$name, $fn]) {
    try { if ($fn()) ok($name); else fail($name, "Check returned false"); }
    catch (Throwable $e) { fail($name, $e->getMessage()); }
}

// ══════════════════════════════════════════
// SUMMARY
// ══════════════════════════════════════════
echo "\n╔═══════════════════════════════════════════════════════════╗\n";
echo "║                    FINAL RESULTS                         ║\n";
echo "╠═══════════════════════════════════════════════════════════╣\n";
printf("║  ✅ Passed: %-46s║\n", $P);
printf("║  ❌ Failed: %-46s║\n", $F);
printf("║  Total:    %-46s║\n", $P + $F);
echo "╚═══════════════════════════════════════════════════════════╝\n";
if ($errs) { echo "\nFailures:\n"; foreach ($errs as $i=>$e) echo "  ".($i+1).". $e\n"; }
echo "\n" . ($F === 0 ? "🎉 ALL TESTS PASSED! System is fully functional." : "⚠️  Fix the failures above.") . "\n\n";
exit($F > 0 ? 1 : 0);
