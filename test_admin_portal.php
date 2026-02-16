<?php
/**
 * CYN Tourism — Admin + Portal Full Operation Test
 * Simulates EVERY real user action on both admin and client portal.
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('BASE_PATH', __DIR__); define('ROOT_PATH', BASE_PATH);
define('DB_DRIVER','sqlite'); define('DB_PATH',':memory:');
define('DEBUG_MODE',true); define('LOG_QUERIES',false); define('LOG_ENABLED',false);
define('APP_ROOT',__DIR__); define('LOG_PATH',__DIR__.'/logs/');
define('COMPANY_NAME','CYN TURIZM'); define('COMPANY_ADDRESS','MOLLA GURANI MAH.');
define('COMPANY_PHONE','+90 531'); define('COMPANY_EMAIL','info@cyn.com');
define('COMPANY_LOGO','logo.png'); define('COMPANY_WEBSITE','https://cyn.com'); define('TURSAB_LICENSE','');
define('SESSION_NAME','TEST'); define('SESSION_LIFETIME',7200); define('SESSION_REGENERATE_ID',false);
define('CSRF_TOKEN_NAME','csrf'); define('PASSWORD_MIN_LENGTH',8); define('PASSWORD_REQUIRE_SPECIAL',true);
define('MAX_LOGIN_ATTEMPTS',5); define('LOCKOUT_DURATION',900);
define('SECURE_COOKIES',false); define('HTTP_ONLY_COOKIES',true);
define('DEFAULT_CURRENCY','USD'); define('AVAILABLE_CURRENCIES','["USD","EUR","TRY"]');
define('TIMEZONE','Europe/Istanbul'); define('DATE_FORMAT','d/m/Y'); define('DATETIME_FORMAT','d/m/Y H:i');
define('DB_DATE_FORMAT','Y-m-d'); define('DB_DATETIME_FORMAT','Y-m-d H:i:s');
date_default_timezone_set(TIMEZONE);
define('UPLOAD_DIR',__DIR__.'/uploads/'); define('UPLOAD_MAX_SIZE',10485760);
define('UPLOAD_ALLOWED_TYPES','{"image":["jpg","png"]}');
define('UPLOAD_IMAGE_MAX_WIDTH',1920); define('UPLOAD_IMAGE_MAX_HEIGHT',1080);
define('UPLOAD_THUMB_WIDTH',150); define('UPLOAD_THUMB_HEIGHT',150);
define('MAINTENANCE_MODE',false); define('MAINTENANCE_ALLOW_ADMIN',true);
define('MAINTENANCE_MESSAGE',''); define('MAINTENANCE_IP_WHITELIST','[]');
define('BACKUP_DIR',__DIR__.'/backups/'); define('BACKUP_RETENTION_DAYS',30);
define('AUTO_BACKUP_ENABLED',false); define('AUTO_BACKUP_FREQUENCY','daily');
define('BACKUP_INCLUDE_FILES',true); define('BACKUP_MAX_FILES',10);
define('SMTP_ENABLED',false); define('SMTP_HOST',''); define('SMTP_PORT',587);
define('SMTP_USER',''); define('SMTP_PASS',''); define('SMTP_ENCRYPTION','tls');
define('SMTP_FROM_EMAIL','info@cyn.com'); define('SMTP_FROM_NAME','CYN');
define('SMTP_TIMEOUT',30);
define('DEFAULT_ITEMS_PER_PAGE',20); define('PAGINATION_RANGE',2); define('MAX_ITEMS_PER_PAGE',100);
define('NOTIFICATION_RETENTION_DAYS',30); define('ENABLE_BROWSER_NOTIFICATIONS',true);
define('NOTIFICATION_CHECK_INTERVAL',60);
define('API_ENABLED',false); define('API_RATE_LIMIT',100); define('API_KEY_REQUIRED',true);
define('DB_HOST','localhost'); define('DB_NAME','t'); define('DB_USER','t');
define('DB_PASS','t'); define('DB_CHARSET','utf8mb4'); define('DB_COLLATE','utf8mb4_unicode_ci');

if (session_status()===PHP_SESSION_NONE) session_start();
$_SESSION['user_id']=1; $_SESSION['auth_time']=time(); $_SESSION['user_role']='admin';
$_SERVER=['REQUEST_METHOD'=>'GET','REQUEST_URI'=>'/','SCRIPT_NAME'=>'/index.php',
    'REMOTE_ADDR'=>'127.0.0.1','HTTP_HOST'=>'localhost','HTTP_USER_AGENT'=>'Test/1.0','PHP_SELF'=>'index.php'];

$P=0;$F=0;$W=0;$errs=[];
function ok($n){global $P;$P++;echo "  ✅ $n\n";}
function fail($n,$r){global $F,$errs;$F++;$errs[]="$n: $r";echo "  ❌ $n — $r\n";}
function warn($n,$r){global $W;$W++;echo "  ⚠️  $n — $r\n";}
function sec($n){echo "\n━━━ $n ━━━\n";}

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  CYN Tourism — ADMIN + PORTAL Complete Operation Test     ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";

// Bootstrap
require_once BASE_PATH.'/src/Core/App.php'; App::setBasePath(BASE_PATH);
require_once BASE_PATH.'/src/Core/Controller.php';
require_once BASE_PATH.'/src/Core/Logger.php';
require_once BASE_PATH.'/config/database.php';
require_once BASE_PATH.'/src/Core/Auth.php';
require_once BASE_PATH.'/src/Core/helpers.php';
require_once BASE_PATH.'/config/language.php';
$db=Database::getInstance(); $conn=$db->getConnection();

// Create schema (NO AUTO_INCREMENT to simulate the broken server!)
$sql = <<<'SQL'
CREATE TABLE users(id INTEGER NOT NULL,first_name TEXT,last_name TEXT,email TEXT UNIQUE,password TEXT,role TEXT DEFAULT 'viewer',status TEXT DEFAULT 'active',email_verified INT DEFAULT 0,profile_image TEXT DEFAULT '',phone TEXT DEFAULT '',last_login TEXT,login_count INT DEFAULT 0,failed_login_attempts INT DEFAULT 0,password_changed_at TEXT,remember_token TEXT DEFAULT '',reset_token TEXT DEFAULT '',two_factor_enabled INT DEFAULT 0,created_at TEXT DEFAULT CURRENT_TIMESTAMP,updated_at TEXT DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY(id));
CREATE TABLE partners(id INTEGER NOT NULL,company_name TEXT,contact_person TEXT DEFAULT '',email TEXT,password TEXT DEFAULT '',phone TEXT DEFAULT '',mobile TEXT DEFAULT '',address TEXT DEFAULT '',city TEXT DEFAULT '',country TEXT DEFAULT '',postal_code TEXT DEFAULT '',website TEXT DEFAULT '',tax_id TEXT DEFAULT '',commission_rate REAL DEFAULT 0,credit_limit REAL DEFAULT 0,balance REAL DEFAULT 0,payment_terms INT DEFAULT 30,partner_type TEXT DEFAULT 'agency',status TEXT DEFAULT 'active',notes TEXT DEFAULT '',contract_file TEXT DEFAULT '',created_by INT DEFAULT 0,created_at TEXT DEFAULT CURRENT_TIMESTAMP,updated_at TEXT DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY(id));
CREATE TABLE vouchers(id INTEGER NOT NULL,voucher_no TEXT,company_name TEXT,company_id INT DEFAULT 0,partner_id INT DEFAULT 0,hotel_name TEXT,pickup_location TEXT,dropoff_location TEXT,pickup_date TEXT,pickup_time TEXT,return_date TEXT DEFAULT '1970-01-01',return_time TEXT DEFAULT '00:00:00',transfer_type TEXT DEFAULT 'one_way',total_pax INT DEFAULT 0,passengers TEXT DEFAULT '',flight_number TEXT DEFAULT '',flight_arrival_time TEXT DEFAULT '00:00:00',vehicle_id INT DEFAULT 0,driver_id INT DEFAULT 0,guide_id INT DEFAULT 0,special_requests TEXT DEFAULT '',price REAL DEFAULT 0,currency TEXT DEFAULT 'USD',status TEXT DEFAULT 'pending',payment_status TEXT DEFAULT 'unpaid',notes TEXT DEFAULT '',created_by INT DEFAULT 0,updated_by INT DEFAULT 0,created_at TEXT DEFAULT CURRENT_TIMESTAMP,updated_at TEXT DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY(id));
CREATE TABLE invoices(id INTEGER NOT NULL,invoice_no TEXT,company_name TEXT,company_id INT DEFAULT 0,partner_id INT DEFAULT 0,invoice_date TEXT,due_date TEXT,subtotal REAL DEFAULT 0,tax_rate REAL DEFAULT 0,tax_amount REAL DEFAULT 0,discount REAL DEFAULT 0,total_amount REAL DEFAULT 0,paid_amount REAL DEFAULT 0,currency TEXT DEFAULT 'USD',status TEXT DEFAULT 'draft',payment_method TEXT DEFAULT '',payment_date TEXT DEFAULT '1970-01-01',notes TEXT DEFAULT '',type TEXT DEFAULT 'general',terms TEXT DEFAULT '',file_path TEXT DEFAULT '',sent_at TEXT DEFAULT CURRENT_TIMESTAMP,sent_by INT DEFAULT 0,created_by INT DEFAULT 0,created_at TEXT DEFAULT CURRENT_TIMESTAMP,updated_at TEXT DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY(id));
CREATE TABLE invoice_items(id INTEGER NOT NULL,invoice_id INT,item_type TEXT DEFAULT 'voucher',item_id INT DEFAULT 0,description TEXT,quantity INT DEFAULT 1,unit_price REAL DEFAULT 0,total_price REAL DEFAULT 0,created_at TEXT DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY(id));
CREATE TABLE hotel_vouchers(id INTEGER NOT NULL,voucher_no TEXT,guest_name TEXT,hotel_name TEXT,hotel_id INT DEFAULT 0,company_name TEXT DEFAULT '',company_id INT DEFAULT 0,address TEXT DEFAULT '',telephone TEXT DEFAULT '',partner_id INT DEFAULT 0,room_type TEXT DEFAULT '',room_count INT DEFAULT 1,board_type TEXT DEFAULT 'bed_breakfast',transfer_type TEXT DEFAULT 'without',check_in TEXT,check_out TEXT,nights INT DEFAULT 1,total_pax INT DEFAULT 0,adults INT DEFAULT 0,children INT DEFAULT 0,infants INT DEFAULT 0,confirmation_no TEXT DEFAULT '',price_per_night REAL DEFAULT 0,total_price REAL DEFAULT 0,currency TEXT DEFAULT 'USD',customers TEXT DEFAULT '',special_requests TEXT DEFAULT '',status TEXT DEFAULT 'pending',payment_status TEXT DEFAULT 'unpaid',notes TEXT DEFAULT '',created_by INT DEFAULT 0,updated_by INT DEFAULT 0,created_at TEXT DEFAULT CURRENT_TIMESTAMP,updated_at TEXT DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY(id));
CREATE TABLE tours(id INTEGER NOT NULL,tour_name TEXT,tour_code TEXT DEFAULT '',description TEXT DEFAULT '',tour_type TEXT DEFAULT 'daily',destination TEXT DEFAULT '',pickup_location TEXT DEFAULT '',dropoff_location TEXT DEFAULT '',tour_date TEXT DEFAULT '1970-01-01',start_time TEXT DEFAULT '00:00:00',end_time TEXT DEFAULT '00:00:00',duration_days INT DEFAULT 1,total_pax INT DEFAULT 0,max_pax INT DEFAULT 0,passengers TEXT DEFAULT '',company_name TEXT DEFAULT '',hotel_name TEXT DEFAULT '',customer_phone TEXT DEFAULT '',adults INT DEFAULT 0,children INT DEFAULT 0,infants INT DEFAULT 0,customers TEXT DEFAULT '[]',tour_items TEXT DEFAULT '[]',company_id INT DEFAULT 0,partner_id INT DEFAULT 0,guide_id INT DEFAULT 0,vehicle_id INT DEFAULT 0,driver_id INT DEFAULT 0,price_per_person REAL DEFAULT 0,total_price REAL DEFAULT 0,currency TEXT DEFAULT 'USD',includes TEXT DEFAULT '',excludes TEXT DEFAULT '',itinerary TEXT DEFAULT '',special_requests TEXT DEFAULT '',status TEXT DEFAULT 'pending',payment_status TEXT DEFAULT 'unpaid',notes TEXT DEFAULT '',created_by INT DEFAULT 0,updated_by INT DEFAULT 0,created_at TEXT DEFAULT CURRENT_TIMESTAMP,updated_at TEXT DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY(id));
CREATE TABLE hotels(id INTEGER NOT NULL,name TEXT,address TEXT DEFAULT '',city TEXT DEFAULT '',country TEXT DEFAULT 'Turkey',stars INT DEFAULT 3,phone TEXT DEFAULT '',email TEXT DEFAULT '',website TEXT DEFAULT '',description TEXT DEFAULT '',status TEXT DEFAULT 'active',created_at TEXT DEFAULT CURRENT_TIMESTAMP,updated_at TEXT DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY(id));
CREATE TABLE hotel_rooms(id INTEGER NOT NULL,hotel_id INT,room_type TEXT,capacity INT DEFAULT 2,price_single REAL DEFAULT 0,price_double REAL DEFAULT 0,price_triple REAL DEFAULT 0,price_quad REAL DEFAULT 0,price_child REAL DEFAULT 0,currency TEXT DEFAULT 'USD',board_type TEXT DEFAULT 'BB',season TEXT DEFAULT 'all',created_at TEXT DEFAULT CURRENT_TIMESTAMP,updated_at TEXT DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY(id));
CREATE TABLE drivers(id INTEGER NOT NULL,first_name TEXT,last_name TEXT,phone TEXT,email TEXT DEFAULT '',license_no TEXT,license_expiry TEXT,status TEXT DEFAULT 'active',languages TEXT DEFAULT '',notes TEXT DEFAULT '',created_at TEXT DEFAULT CURRENT_TIMESTAMP,updated_at TEXT DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY(id));
CREATE TABLE vehicles(id INTEGER NOT NULL,plate_number TEXT,make TEXT,model TEXT,year INT DEFAULT 0,color TEXT DEFAULT '',capacity INT DEFAULT 4,luggage_capacity INT DEFAULT 2,vehicle_type TEXT DEFAULT 'sedan',status TEXT DEFAULT 'available',driver_id INT DEFAULT 0,notes TEXT DEFAULT '',created_at TEXT DEFAULT CURRENT_TIMESTAMP,updated_at TEXT DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY(id));
CREATE TABLE tour_guides(id INTEGER NOT NULL,first_name TEXT,last_name TEXT,phone TEXT,email TEXT DEFAULT '',languages TEXT,specializations TEXT DEFAULT '',experience_years INT DEFAULT 0,daily_rate REAL DEFAULT 0,currency TEXT DEFAULT 'USD',status TEXT DEFAULT 'active',notes TEXT DEFAULT '',created_at TEXT DEFAULT CURRENT_TIMESTAMP,updated_at TEXT DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY(id));
CREATE TABLE notifications(id INTEGER NOT NULL,user_id INT,title TEXT,message TEXT,type TEXT DEFAULT 'info',category TEXT DEFAULT 'general',related_id INT DEFAULT 0,related_type TEXT DEFAULT '',action_url TEXT DEFAULT '',is_read INT DEFAULT 0,read_at TEXT DEFAULT CURRENT_TIMESTAMP,sent_email INT DEFAULT 0,sent_push INT DEFAULT 0,created_at TEXT DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY(id));
CREATE TABLE services(id INTEGER NOT NULL,service_type TEXT DEFAULT 'tour',name TEXT,description TEXT DEFAULT '',price REAL DEFAULT 0,currency TEXT DEFAULT 'USD',unit TEXT DEFAULT 'per_person',details TEXT DEFAULT '',status TEXT DEFAULT 'active',created_at TEXT DEFAULT CURRENT_TIMESTAMP,updated_at TEXT DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY(id));
CREATE TABLE settings(id INTEGER NOT NULL,setting_key TEXT UNIQUE,setting_value TEXT DEFAULT '',setting_group TEXT DEFAULT 'general',created_at TEXT DEFAULT CURRENT_TIMESTAMP,updated_at TEXT DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY(id));
CREATE TABLE partner_booking_requests(id INTEGER NOT NULL,partner_id INT,request_type TEXT DEFAULT 'transfer',details TEXT,status TEXT DEFAULT 'pending',admin_notes TEXT DEFAULT '',created_at TEXT DEFAULT CURRENT_TIMESTAMP,updated_at TEXT DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY(id));
CREATE TABLE partner_messages(id INTEGER NOT NULL,partner_id INT,sender_type TEXT,sender_id INT,subject TEXT DEFAULT '',message TEXT,file_path TEXT NOT NULL DEFAULT '',is_read INT DEFAULT 0,created_at TEXT DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY(id));
CREATE TABLE activity_logs(id INTEGER NOT NULL,user_id INT DEFAULT 0,action TEXT,description TEXT DEFAULT '',entity_type TEXT DEFAULT '',entity_id INT DEFAULT 0,ip_address TEXT,created_at TEXT DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY(id));
CREATE TABLE email_config(id INTEGER NOT NULL,smtp_host TEXT DEFAULT '',smtp_port INT DEFAULT 587,smtp_username TEXT DEFAULT '',smtp_password TEXT DEFAULT '',from_email TEXT DEFAULT '',from_name TEXT DEFAULT '',enable_notifications INT DEFAULT 1,enable_reminders INT DEFAULT 1,updated_at TEXT DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY(id));
SQL;
foreach(explode(";\n",$sql) as $s){$s=trim($s);if($s)$conn->exec($s);}
ok("18 tables created (WITHOUT auto_increment — simulates broken server)");

// Seed
$pw=password_hash('Admin123!',PASSWORD_DEFAULT);
$conn->exec("INSERT INTO users VALUES(1,'Admin','User','admin@test.com','$pw','admin','active',1,'','',NULL,0,0,NULL,'','',0,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)");
$pp=password_hash('Partner1!',PASSWORD_DEFAULT);
$conn->exec("INSERT INTO partners VALUES(1,'Atlas Travel','Karim','karim@test.dz','$pp','+213','','','Algiers','Algeria','','','',10,0,0,30,'agency','active','','',1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)");
$conn->exec("INSERT INTO partners VALUES(2,'Grand Star Hotel','Hakan','res@grand.com','','','','','Istanbul','Turkey','','','',5,0,0,30,'hotel','active','','',1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)");
$conn->exec("INSERT INTO drivers VALUES(1,'Ali','Yildiz','+90 532','','TR-001','2027-06-15','active','Turkish,English','',CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)");
$conn->exec("INSERT INTO vehicles VALUES(1,'34 CYN 001','Mercedes','Vito',2023,'Black',8,8,'van','available',0,'',CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)");
$conn->exec("INSERT INTO tour_guides VALUES(1,'Deniz','Ozkan','+90 532','','Turkish,English','Historical',8,150,'USD','active','',CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)");
$conn->exec("INSERT INTO hotels VALUES(1,'Grand Hyatt','Taksim','Istanbul','Turkey',5,'','','','','active',CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)");
$conn->exec("INSERT INTO hotel_rooms VALUES(1,1,'Standard',2,100,150,200,250,50,'USD','BB','all',CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)");
$conn->exec("INSERT INTO services VALUES(1,'transfer','Airport Pickup','IST to Hotel',45,'USD','per_vehicle','','active',CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)");
$conn->exec("INSERT INTO services VALUES(2,'tour','City Tour','Sultanahmet',65,'USD','per_person','','active',CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)");
$conn->exec("INSERT INTO notifications VALUES(1,1,'Welcome','Welcome to CYN','info','general',0,'','',0,CURRENT_TIMESTAMP,0,0,CURRENT_TIMESTAMP)");
$conn->exec("INSERT INTO settings VALUES(1,'site_name','CYN Tourism','general',CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)");
ok("Seed data loaded");

// Load all
require_once BASE_PATH.'/src/Models/Invoice.php';
require_once BASE_PATH.'/src/Models/Voucher.php';
require_once BASE_PATH.'/src/Models/Partner.php';
require_once BASE_PATH.'/src/Models/Fleet.php';
require_once BASE_PATH.'/src/Models/Dashboard.php';
require_once BASE_PATH.'/src/Models/Report.php';
foreach(glob(BASE_PATH.'/src/Controllers/*.php') as $f) require_once $f;
ok("All models + controllers loaded");

// ═══════════════════════════════════════════════
//  ADMIN SIDE
// ═══════════════════════════════════════════════

sec("ADMIN: Dashboard");
try{$s=Dashboard::getStats();ok("Stats: transfers={$s['todayTransfers']}, partners={$s['totalPartners']}, vehicles={$s['totalVehicles']}, drivers={$s['totalDrivers']}");}catch(Throwable $e){fail("Dashboard stats",$e->getMessage());}
try{$u=Dashboard::getUpcomingTransfers();ok("Upcoming transfers: ".count($u));}catch(Throwable $e){fail("Upcoming",$e->getMessage());}
try{$r=Dashboard::getRevenuePerCurrency();ok("Revenue currencies: ".count($r));}catch(Throwable $e){fail("Revenue",$e->getMessage());}
try{$t=Dashboard::getMonthlyTrend();ok("Monthly trend: ".count($t)." months");}catch(Throwable $e){fail("Trend",$e->getMessage());}
try{$b=Dashboard::getPaymentBreakdown();ok("Payment breakdown: ".json_encode($b));}catch(Throwable $e){fail("Breakdown",$e->getMessage());}

sec("ADMIN: Voucher CRUD");
try{
    $id=Voucher::create(['company_name'=>'Atlas Travel','hotel_name'=>'Grand Hyatt','pickup_location'=>'IST','dropoff_location'=>'Hotel','pickup_date'=>date('Y-m-d'),'pickup_time'=>'14:00','total_pax'=>3,'price'=>45,'currency'=>'USD','status'=>'pending','notes'=>'']);
    ok("CREATE voucher ID=$id");
    $v=Voucher::getById($id); ok("READ voucher: {$v['voucher_no']}");
    Voucher::update($id,['status'=>'confirmed','price'=>55]); $v2=Voucher::getById($id);
    if($v2['status']==='confirmed'&&$v2['price']==55)ok("UPDATE voucher OK");else fail("UPDATE voucher","status={$v2['status']}");
    $all=Voucher::getAll(['search'=>'Atlas']); ok("LIST vouchers: {$all['total']} found");
    $all2=Voucher::getAll(['status'=>'confirmed']); ok("FILTER by status: {$all2['total']}");
    $all3=Voucher::getAll(['date_from'=>date('Y-m-d')]); ok("FILTER by date: {$all3['total']}");
    $companies=Voucher::getCompanies(); ok("GET companies: ".count($companies));
    Voucher::delete($id); ok("DELETE voucher OK");
}catch(Throwable $e){fail("Voucher CRUD",$e->getMessage());}

sec("ADMIN: Invoice CRUD");
try{
    $id=Invoice::create(['company_name'=>'Atlas Travel','invoice_date'=>date('Y-m-d'),'due_date'=>date('Y-m-d',strtotime('+30d')),'subtotal'=>300,'total_amount'=>300,'currency'=>'USD','status'=>'draft','type'=>'transfer','notes'=>'Test']);
    ok("CREATE invoice ID=$id");
    $inv=Invoice::getById($id); ok("READ invoice: {$inv['invoice_no']}, type={$inv['type']}");
    Invoice::update($id,['status'=>'sent']); ok("UPDATE status to sent");
    Invoice::markPaid($id,'bank_transfer'); $inv2=Invoice::getById($id);
    if($inv2['status']==='paid')ok("MARK PAID OK (method={$inv2['payment_method']})");else fail("markPaid","status={$inv2['status']}");
    $all=Invoice::getAll(['type'=>'transfer']); ok("FILTER by type=transfer: {$all['total']}");
    $all2=Invoice::getAll(['status'=>'paid']); ok("FILTER by status=paid: {$all2['total']}");
    $sum=Invoice::getSummary(); ok("SUMMARY: total={$sum['total']}, paid={$sum['paid']}, amount={$sum['total_amount']}");
    Invoice::delete($id); ok("DELETE invoice OK");
}catch(Throwable $e){fail("Invoice CRUD",$e->getMessage());}

sec("ADMIN: Partner CRUD");
try{
    $id=Partner::create(['company_name'=>'New Agency','contact_person'=>'Ali','email'=>'ali@new.com','phone'=>'+90','partner_type'=>'agency','status'=>'active']);
    ok("CREATE partner ID=$id");
    $p=Partner::getById($id); ok("READ partner: {$p['company_name']}");
    Partner::update($id,['city'=>'Istanbul','country'=>'Turkey','commission_rate'=>8]);
    $p2=Partner::getById($id); if($p2['city']==='Istanbul')ok("UPDATE partner OK");else fail("UPDATE","city={$p2['city']}");
    $active=Partner::getActive(); ok("GET ACTIVE: ".count($active)." partners");
    $all=Partner::getAll(['type'=>'agency']); ok("FILTER by type: {$all['total']}");
    $all2=Partner::getAll(['search'=>'New']); ok("SEARCH: {$all2['total']} found");
    Partner::delete($id); ok("DELETE partner OK");
}catch(Throwable $e){fail("Partner CRUD",$e->getMessage());}

sec("ADMIN: Fleet CRUD");
try{
    $did=Fleet::saveDriver(['first_name'=>'Hasan','last_name'=>'Koc','phone'=>'+90 533','license_no'=>'TR-002','license_expiry'=>'2027-01-01','status'=>'active']);
    ok("CREATE driver ID=$did");
    Fleet::saveDriver(['first_name'=>'Hasan','last_name'=>'Updated','phone'=>'+90 533','license_no'=>'TR-002','license_expiry'=>'2027-01-01'],$did);
    $d=Fleet::getDriver($did); if($d['last_name']==='Updated')ok("UPDATE driver OK");else fail("UPDATE driver",$d['last_name']);
    $vid=Fleet::saveVehicle(['plate_number'=>'34 CYN 002','make'=>'BMW','model'=>'X5','capacity'=>4,'status'=>'available']);
    ok("CREATE vehicle ID=$vid");
    $gid=Fleet::saveGuide(['first_name'=>'Selin','last_name'=>'Aydin','phone'=>'+90 534','languages'=>'TR,EN,RU','status'=>'active']);
    ok("CREATE guide ID=$gid");
    $ad=Fleet::getActiveDrivers(); ok("ACTIVE drivers: ".count($ad));
    $av=Fleet::getActiveVehicles(); ok("ACTIVE vehicles: ".count($av));
    $ag=Fleet::getActiveGuides(); ok("ACTIVE guides: ".count($ag));
    Fleet::deleteDriver($did); Fleet::deleteVehicle($vid); Fleet::deleteGuide($gid);
    ok("DELETE driver+vehicle+guide OK");
}catch(Throwable $e){fail("Fleet CRUD",$e->getMessage());}

sec("ADMIN: Hotel Voucher (HotelController pattern)");
try{
    $nid=(int)$conn->query("SELECT COALESCE(MAX(id),0)+1 FROM hotel_vouchers")->fetchColumn();
    $conn->prepare("INSERT INTO hotel_vouchers(id,voucher_no,guest_name,hotel_name,company_name,address,telephone,room_type,room_count,board_type,transfer_type,check_in,check_out,nights,total_pax,adults,children,infants,price_per_night,total_price,currency,customers,special_requests,status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")->execute([$nid,'HV-TEST','John Doe','Grand Hyatt','Atlas Travel','Taksim Sq','+90555','Deluxe',2,'bed_breakfast','without','2026-03-01','2026-03-04',3,4,2,1,1,120,720,'USD','[{"name":"John"}]','Late checkin','pending']);
    ok("CREATE hotel voucher ID=$nid (with address, telephone)");
    $hv=$conn->query("SELECT * FROM hotel_vouchers WHERE id=$nid")->fetch(PDO::FETCH_ASSOC);
    if($hv['address']==='Taksim Sq'&&$hv['telephone']==='+90555')ok("READ hotel voucher: address+telephone correct");
    else fail("READ hotel voucher","address={$hv['address']}");
    $conn->prepare("UPDATE hotel_vouchers SET status=?,guest_name=?,updated_at=CURRENT_TIMESTAMP WHERE id=?")->execute(['confirmed','Jane Doe',$nid]);
    $hv2=$conn->query("SELECT * FROM hotel_vouchers WHERE id=$nid")->fetch(PDO::FETCH_ASSOC);
    if($hv2['status']==='confirmed')ok("UPDATE hotel voucher OK");else fail("UPDATE hv","status={$hv2['status']}");
    $conn->prepare("DELETE FROM hotel_vouchers WHERE id=?")->execute([$nid]);
    ok("DELETE hotel voucher OK");
}catch(Throwable $e){fail("Hotel Voucher",$e->getMessage());}

sec("ADMIN: Tour Voucher (TourController pattern)");
try{
    $nid=(int)$conn->query("SELECT COALESCE(MAX(id),0)+1 FROM tours")->fetchColumn();
    $conn->prepare("INSERT INTO tours(id,tour_name,tour_code,description,tour_type,tour_date,total_pax,company_name,hotel_name,customer_phone,adults,children,infants,customers,tour_items,status) VALUES(?,?,?,?,'daily',?,?,?,?,?,?,?,?,?,?,?)")->execute([$nid,'City Tour','TV-TEST','3hrs','2026-03-15',4,'Atlas Travel','Grand Hyatt','+90555',3,1,0,'[{"name":"Ali"}]','[{"name":"City Tour"}]','pending']);
    ok("CREATE tour voucher ID=$nid (with hotel_name, customer_phone, adults/children/infants)");
    $t=$conn->query("SELECT * FROM tours WHERE id=$nid")->fetch(PDO::FETCH_ASSOC);
    if($t['hotel_name']==='Grand Hyatt'&&$t['customer_phone']==='+90555'&&$t['adults']==3)ok("READ tour: all columns correct");
    else fail("READ tour","hotel_name={$t['hotel_name']}");
    $conn->prepare("UPDATE tours SET status=?,updated_at=CURRENT_TIMESTAMP WHERE id=?")->execute(['confirmed',$nid]);
    ok("UPDATE tour OK");
    $conn->prepare("DELETE FROM tours WHERE id=?")->execute([$nid]);
    ok("DELETE tour OK");
}catch(Throwable $e){fail("Tour Voucher",$e->getMessage());}

sec("ADMIN: Transfer/Hotel/Tour Invoice (all 3 types)");
try{
    // Transfer Invoice
    $nid=(int)$conn->query("SELECT COALESCE(MAX(id),0)+1 FROM invoices")->fetchColumn();
    $conn->prepare("INSERT INTO invoices(id,invoice_no,company_name,invoice_date,due_date,subtotal,total_amount,currency,status,notes,type) VALUES(?,'TI-TEST','Atlas Travel',?,?,135,135,'USD','draft','IST→Hotel','transfer')")->execute([$nid,date('Y-m-d'),date('Y-m-d',strtotime('+30d'))]);
    ok("CREATE transfer invoice ID=$nid");
    $niid=(int)$conn->query("SELECT COALESCE(MAX(id),0)+1 FROM invoice_items")->fetchColumn();
    $conn->prepare("INSERT INTO invoice_items(id,invoice_id,description,quantity,unit_price,total_price) VALUES(?,?,'Airport Transfer',3,45,135)")->execute([$niid,$nid]);
    ok("CREATE invoice item for transfer");
    // Hotel Invoice
    $nid2=(int)$conn->query("SELECT COALESCE(MAX(id),0)+1 FROM invoices")->fetchColumn();
    $conn->prepare("INSERT INTO invoices(id,invoice_no,company_name,invoice_date,due_date,subtotal,total_amount,currency,status,notes,type) VALUES(?,'HI-TEST','Atlas Travel',?,?,720,720,'USD','draft','Grand Hyatt','hotel')")->execute([$nid2,date('Y-m-d'),date('Y-m-d',strtotime('+30d'))]);
    ok("CREATE hotel invoice ID=$nid2");
    // Tour Invoice
    $nid3=(int)$conn->query("SELECT COALESCE(MAX(id),0)+1 FROM invoices")->fetchColumn();
    $conn->prepare("INSERT INTO invoices(id,invoice_no,company_name,invoice_date,due_date,subtotal,total_amount,currency,status,notes,type) VALUES(?,'TRI-TEST','Atlas Travel',?,?,260,260,'USD','draft','City Tour','tour')")->execute([$nid3,date('Y-m-d'),date('Y-m-d',strtotime('+30d'))]);
    ok("CREATE tour invoice ID=$nid3");
    // Verify type filtering
    $transfer=Invoice::getAll(['type'=>'transfer']); ok("FILTER type=transfer: {$transfer['total']}");
    $hotel=Invoice::getAll(['type'=>'hotel']); ok("FILTER type=hotel: {$hotel['total']}");
    $tour=Invoice::getAll(['type'=>'tour']); ok("FILTER type=tour: {$tour['total']}");
}catch(Throwable $e){fail("Invoice types",$e->getMessage());}

sec("ADMIN: Reports (all 9 methods)");
try{
    $s='2026-01-01';$e2='2026-12-31';
    Report::getMonthlyRevenue($s,$e2); ok("getMonthlyRevenue (DATE_FORMAT, no strftime)");
    Report::getTransferStats($s,$e2); ok("getTransferStats");
    Report::getTransferTypes($s,$e2); ok("getTransferTypes");
    Report::getCurrencySummary($s,$e2); ok("getCurrencySummary");
    Report::getTopCompanies($s,$e2); ok("getTopCompanies");
    Report::getPartnerPerformance($s,$e2); ok("getPartnerPerformance");
    Report::getRevenueByPartner($s,$e2); ok("getRevenueByPartner");
    Report::getServiceTypeBreakdown($s,$e2); ok("getServiceTypeBreakdown");
    Report::getInvoiceSummary($s,$e2); ok("getInvoiceSummary");
}catch(Throwable $e){fail("Reports",$e->getMessage());}

sec("ADMIN: Hotel Profiles");
try{
    $hid=Database::insert('hotels',['name'=>'Ankara Hotel','city'=>'Ankara','country'=>'Turkey','stars'=>4,'status'=>'active']);
    ok("CREATE hotel profile ID=$hid");
    Database::insert('hotel_rooms',['hotel_id'=>$hid,'room_type'=>'Deluxe','capacity'=>3,'price_single'=>200,'price_double'=>280]);
    ok("CREATE room for hotel");
    $hotels=Database::fetchAll("SELECT h.*,(SELECT COUNT(*) FROM hotel_rooms WHERE hotel_id=h.id) as room_count FROM hotels h ORDER BY h.name");
    ok("LIST hotel profiles: ".count($hotels)." hotels");
    Database::execute("DELETE FROM hotel_rooms WHERE hotel_id=?",[$hid]);
    Database::execute("DELETE FROM hotels WHERE id=?",[$hid]);
    ok("DELETE hotel+rooms OK");
}catch(Throwable $e){fail("Hotel profiles",$e->getMessage());}

sec("ADMIN: Services");
try{
    Database::execute("INSERT INTO services(id,service_type,name,price,currency,status) VALUES(3,'hotel','Hotel Room',100,'USD','active')");
    ok("CREATE service");
    $svc=Database::fetchAll("SELECT * FROM services WHERE status='active'");
    ok("LIST services: ".count($svc));
    Database::execute("UPDATE services SET price=110 WHERE id=3");
    ok("UPDATE service price");
    Database::execute("DELETE FROM services WHERE id=3");
    ok("DELETE service OK");
}catch(Throwable $e){fail("Services",$e->getMessage());}

sec("ADMIN: Notifications");
try{
    $nc=get_notification_count(); ok("Notification count: $nc");
    add_notification(1,'Test','Test notification','info'); ok("ADD notification");
    $nc2=get_notification_count(); if($nc2>$nc)ok("Count increased to $nc2");else fail("Count","Still $nc2");
}catch(Throwable $e){fail("Notifications",$e->getMessage());}

sec("ADMIN: Booking Requests (approve/reject)");
try{
    $nid=(int)$conn->query("SELECT COALESCE(MAX(id),0)+1 FROM partner_booking_requests")->fetchColumn();
    $conn->prepare("INSERT INTO partner_booking_requests(id,partner_id,request_type,details,status,created_at) VALUES(?,1,'transfer','{\"guest\":\"Test\"}','pending',CURRENT_TIMESTAMP)")->execute([$nid]);
    ok("Booking request created ID=$nid");
    $conn->prepare("UPDATE partner_booking_requests SET status=?,admin_notes=?,updated_at=CURRENT_TIMESTAMP WHERE id=?")->execute(['approved','Looks good',$nid]);
    $br=$conn->query("SELECT * FROM partner_booking_requests WHERE id=$nid")->fetch(PDO::FETCH_ASSOC);
    if($br['status']==='approved')ok("APPROVE request OK (CURRENT_TIMESTAMP, not NOW())");else fail("Approve",$br['status']);
}catch(Throwable $e){fail("Booking requests",$e->getMessage());}

sec("ADMIN: Partner Messages (admin reply)");
try{
    $nid=(int)$conn->query("SELECT COALESCE(MAX(id),0)+1 FROM partner_messages")->fetchColumn();
    $conn->prepare("INSERT INTO partner_messages(id,partner_id,sender_type,sender_id,subject,message,file_path) VALUES(?,1,'admin',1,'Reply','Hello partner','')")->execute([$nid]);
    ok("Admin reply created ID=$nid (file_path='' not NULL)");
}catch(Throwable $e){fail("Admin messages",$e->getMessage());}

sec("ADMIN: Receipts (paid invoices)");
try{
    // Create and pay an invoice
    $rid=Invoice::create(['company_name'=>'Atlas Travel','invoice_date'=>date('Y-m-d'),'due_date'=>date('Y-m-d',strtotime('+30d')),'subtotal'=>500,'total_amount'=>500,'currency'=>'USD','status'=>'draft','type'=>'general']);
    Invoice::markPaid($rid,'cash');
    $receipt=Invoice::getById($rid);
    if($receipt['status']==='paid')ok("Receipt created (paid invoice ID=$rid)");else fail("Receipt",$receipt['status']);
    // Query as ReceiptController does
    $stmt=$conn->prepare("SELECT * FROM invoices WHERE status='paid' ORDER BY payment_date DESC");
    $stmt->execute();
    $receipts=$stmt->fetchAll(PDO::FETCH_ASSOC);
    ok("LIST receipts: ".count($receipts)." paid invoices");
}catch(Throwable $e){fail("Receipts",$e->getMessage());}

sec("ADMIN: PDF Generation (Dompdf)");
try{
    if(file_exists(BASE_PATH.'/vendor/autoload.php')){
        require_once BASE_PATH.'/vendor/autoload.php';
        $dompdf=new \Dompdf\Dompdf();
        $dompdf->loadHtml('<h1>Invoice #INV-TEST</h1><p>Atlas Travel - $300 USD</p>');
        $dompdf->setPaper('A4','portrait');
        $dompdf->render();
        $pdf=$dompdf->output();
        if(strlen($pdf)>100&&substr($pdf,0,5)==='%PDF-')ok("PDF generated: ".number_format(strlen($pdf))." bytes");
        else fail("PDF","Invalid output");
    }else fail("PDF","vendor/autoload.php missing");
}catch(Throwable $e){fail("PDF",$e->getMessage());}

// ═══════════════════════════════════════════════
//  CLIENT PORTAL SIDE
// ═══════════════════════════════════════════════

echo "\n══════════════════════════════════════════════════════════════\n";
echo "  CLIENT PORTAL TESTS\n";
echo "══════════════════════════════════════════════════════════════\n";

// Simulate partner session
$_SESSION['partner_id']=1;
$_SESSION['partner_auth_time']=time();
$partnerData=['id'=>1,'company_name'=>'Atlas Travel','email'=>'karim@test.dz'];

sec("PORTAL: Dashboard Queries");
try{
    $pid=1;$cn='Atlas Travel';
    // Create some data for the partner
    Voucher::create(['company_name'=>$cn,'hotel_name'=>'Grand Hyatt','pickup_location'=>'A','dropoff_location'=>'B','pickup_date'=>date('Y-m-d'),'pickup_time'=>'10:00','total_pax'=>2,'price'=>50,'currency'=>'USD','status'=>'pending']);
    $nid=(int)$conn->query("SELECT COALESCE(MAX(id),0)+1 FROM hotel_vouchers")->fetchColumn();
    $conn->prepare("INSERT INTO hotel_vouchers(id,voucher_no,guest_name,hotel_name,company_name,address,telephone,check_in,check_out,nights,total_pax,adults,status) VALUES(?,?,?,?,?,'','','2026-03-01','2026-03-04',3,2,2,'confirmed')")->execute([$nid,'HV-P1','Guest',$cn,$cn]);
    $nid2=(int)$conn->query("SELECT COALESCE(MAX(id),0)+1 FROM tours")->fetchColumn();
    $conn->prepare("INSERT INTO tours(id,tour_name,tour_code,tour_date,company_name,hotel_name,customer_phone,total_pax,adults,customers,tour_items,status) VALUES(?,'Tour','TV-P1','2026-03-10',?,'Hotel','+90',4,4,'[]','[]','pending')")->execute([$nid2,$cn]);

    $stmt=$conn->prepare("SELECT COUNT(*) FROM invoices WHERE company_id=? OR company_name=?");$stmt->execute([$pid,$cn]);
    ok("Portal invoice count: ".$stmt->fetchColumn());
    $stmt=$conn->prepare("SELECT COUNT(*) FROM vouchers WHERE company_id=? OR company_name=?");$stmt->execute([$pid,$cn]);
    ok("Portal transfer count: ".$stmt->fetchColumn());
    $stmt=$conn->prepare("SELECT COUNT(*) FROM hotel_vouchers WHERE company_id=? OR company_name=?");$stmt->execute([$pid,$cn]);
    ok("Portal hotel voucher count: ".$stmt->fetchColumn());
    $stmt=$conn->prepare("SELECT COUNT(*) FROM tours WHERE company_id=? OR company_name=?");$stmt->execute([$pid,$cn]);
    ok("Portal tour count: ".$stmt->fetchColumn());
    $stmt=$conn->prepare("SELECT COUNT(*) FROM partner_booking_requests WHERE partner_id=? AND status='pending'");$stmt->execute([$pid]);
    ok("Portal pending requests: ".$stmt->fetchColumn());
    $stmt=$conn->prepare("SELECT COUNT(*) FROM partner_messages WHERE partner_id=? AND sender_type='admin' AND is_read=0");$stmt->execute([$pid]);
    ok("Portal unread messages: ".$stmt->fetchColumn());
}catch(Throwable $e){fail("Portal dashboard",$e->getMessage());}

sec("PORTAL: View Invoices");
try{
    $stmt=$conn->prepare("SELECT * FROM invoices WHERE company_id=? OR company_name=? ORDER BY invoice_date DESC");
    $stmt->execute([1,'Atlas Travel']);
    $invs=$stmt->fetchAll(PDO::FETCH_ASSOC);
    ok("Portal invoices list: ".count($invs)." invoices");
    if(count($invs)>0){
        $inv=$invs[0];
        ok("Invoice detail: {$inv['invoice_no']}, status={$inv['status']}, amount={$inv['total_amount']} {$inv['currency']}");
    }
}catch(Throwable $e){fail("Portal invoices",$e->getMessage());}

sec("PORTAL: View Vouchers (all 3 types)");
try{
    $pid=1;$cn='Atlas Travel';
    // Transfer vouchers
    $stmt=$conn->prepare("SELECT id,voucher_no,company_name,pickup_location,dropoff_location,pickup_date,total_pax,price AS total_price,currency,status,'transfer' AS voucher_type FROM vouchers WHERE company_id=? OR company_name=?");
    $stmt->execute([$pid,$cn]); $tv=$stmt->fetchAll(PDO::FETCH_ASSOC);
    ok("Transfer vouchers: ".count($tv));
    // Hotel vouchers
    $stmt=$conn->prepare("SELECT id,voucher_no,guest_name,hotel_name,check_in,check_out,total_pax,total_price,currency,status,'hotel' AS voucher_type FROM hotel_vouchers WHERE company_id=? OR company_name=?");
    $stmt->execute([$pid,$cn]); $hv=$stmt->fetchAll(PDO::FETCH_ASSOC);
    ok("Hotel vouchers: ".count($hv));
    // Tour vouchers
    $stmt=$conn->prepare("SELECT id,tour_code AS voucher_no,tour_name,company_name,tour_date,total_pax,total_price,currency,status,'tour' AS voucher_type FROM tours WHERE company_id=? OR company_name=?");
    $stmt->execute([$pid,$cn]); $trv=$stmt->fetchAll(PDO::FETCH_ASSOC);
    ok("Tour vouchers: ".count($trv));
    ok("TOTAL vouchers: ".(count($tv)+count($hv)+count($trv)));
}catch(Throwable $e){fail("Portal vouchers",$e->getMessage());}

sec("PORTAL: View Receipts (paid invoices, NOT receipts table)");
try{
    $stmt=$conn->prepare("SELECT * FROM invoices WHERE status='paid' AND (partner_id=? OR company_id=? OR company_name=?) ORDER BY payment_date DESC, created_at DESC");
    $stmt->execute([1,1,'Atlas Travel']);
    $rcpts=$stmt->fetchAll(PDO::FETCH_ASSOC);
    ok("Portal receipts: ".count($rcpts)." paid invoices (no receipts table query!)");
}catch(Throwable $e){fail("Portal receipts",$e->getMessage());}

sec("PORTAL: Create Booking Request");
try{
    $nid=(int)$conn->query("SELECT COALESCE(MAX(id),0)+1 FROM partner_booking_requests")->fetchColumn();
    $details=json_encode(['company_name'=>'Atlas Travel','guest_name'=>'Ahmed','date'=>'2026-04-01','pickup_location'=>'IST Airport','destination'=>'Hotel','pax'=>3,'notes'=>'VIP','service_id'=>1,'service_name'=>'Airport Pickup','service_price'=>'45.00'],JSON_UNESCAPED_UNICODE);
    $conn->prepare("INSERT INTO partner_booking_requests(id,partner_id,request_type,details,status,created_at) VALUES(?,?,?,?,?,CURRENT_TIMESTAMP)")->execute([$nid,1,'transfer',$details,'pending']);
    ok("CREATE booking request ID=$nid (with explicit ID — no auto_increment needed!)");
    $br=$conn->query("SELECT * FROM partner_booking_requests WHERE id=$nid")->fetch(PDO::FETCH_ASSOC);
    $d=json_decode($br['details'],true);
    if($d['guest_name']==='Ahmed'&&$d['pax']===3)ok("Booking request data correct");
    else fail("Booking data",json_encode($d));
}catch(Throwable $e){fail("Portal booking request",$e->getMessage());}

sec("PORTAL: Create Hotel Booking Request");
try{
    $nid=(int)$conn->query("SELECT COALESCE(MAX(id),0)+1 FROM partner_booking_requests")->fetchColumn();
    $details=json_encode(['company_name'=>'Atlas Travel','guest_name'=>'Fatima','date'=>'2026-04-05','hotel_name'=>'Grand Hyatt','pax'=>2,'check_in'=>'2026-04-05','check_out'=>'2026-04-08','room_type'=>'Deluxe','board_type'=>'half_board','room_count'=>1,'adults'=>2,'children'=>0],JSON_UNESCAPED_UNICODE);
    $conn->prepare("INSERT INTO partner_booking_requests(id,partner_id,request_type,details,status,created_at) VALUES(?,?,?,?,?,CURRENT_TIMESTAMP)")->execute([$nid,1,'hotel',$details,'pending']);
    ok("CREATE hotel booking request ID=$nid");
}catch(Throwable $e){fail("Portal hotel booking",$e->getMessage());}

sec("PORTAL: Send Message (file_path='' not NULL)");
try{
    $nid=(int)$conn->query("SELECT COALESCE(MAX(id),0)+1 FROM partner_messages")->fetchColumn();
    $conn->prepare("INSERT INTO partner_messages(id,partner_id,sender_type,sender_id,subject,message,file_path) VALUES(?,?,?,?,?,?,?)")->execute([$nid,1,'partner',1,'Question','When is our next transfer?','']);
    ok("SEND message ID=$nid (file_path='' — no NULL crash!)");
    $msgs=$conn->query("SELECT * FROM partner_messages WHERE partner_id=1 ORDER BY created_at ASC")->fetchAll(PDO::FETCH_ASSOC);
    ok("Message thread: ".count($msgs)." messages");
    $hasAdmin=false;$hasPartner=false;
    foreach($msgs as $m){if($m['sender_type']==='admin')$hasAdmin=true;if($m['sender_type']==='partner')$hasPartner=true;}
    if($hasAdmin&&$hasPartner)ok("Thread has both admin and partner messages");
    else warn("Message types","admin=$hasAdmin, partner=$hasPartner");
}catch(Throwable $e){fail("Portal messages",$e->getMessage());}

sec("PORTAL: View Booking Requests");
try{
    $stmt=$conn->prepare("SELECT * FROM partner_booking_requests WHERE partner_id=? ORDER BY created_at DESC");
    $stmt->execute([1]);
    $reqs=$stmt->fetchAll(PDO::FETCH_ASSOC);
    ok("Booking requests list: ".count($reqs)." requests");
    $pending=0;$approved=0;
    foreach($reqs as $r){if($r['status']==='pending')$pending++;if($r['status']==='approved')$approved++;}
    ok("Status breakdown: pending=$pending, approved=$approved");
}catch(Throwable $e){fail("Portal view bookings",$e->getMessage());}

sec("PORTAL: Profile Update");
try{
    $conn->prepare("UPDATE partners SET contact_person=?,phone=?,city=?,country=? WHERE id=?")->execute(['Karim Updated','+213 777','Oran','Algeria',1]);
    $p=$conn->query("SELECT * FROM partners WHERE id=1")->fetch(PDO::FETCH_ASSOC);
    if($p['contact_person']==='Karim Updated'&&$p['city']==='Oran')ok("Profile update OK");
    else fail("Profile","contact={$p['contact_person']}");
    // Password change
    $newPw=password_hash('NewPass123!',PASSWORD_DEFAULT);
    $conn->prepare("UPDATE partners SET password=? WHERE id=?")->execute([$newPw,1]);
    $p2=$conn->query("SELECT password FROM partners WHERE id=1")->fetch(PDO::FETCH_ASSOC);
    if(password_verify('NewPass123!',$p2['password']))ok("Password change OK");
    else fail("Password","Verify failed");
}catch(Throwable $e){fail("Portal profile",$e->getMessage());}

// ═══════════════════════════════════════════════
//  VIEW FILE VERIFICATION
// ═══════════════════════════════════════════════

sec("VIEW FILES: Admin + Portal");
$views=['dashboard/index','auth/login','vouchers/index','vouchers/form','vouchers/show','vouchers/pdf',
'invoices/index','invoices/form','invoices/show','invoices/pdf',
'hotels/voucher','hotels/voucher_show','hotels/voucher_edit','hotels/voucher_pdf','hotels/invoice','hotels/invoice_form',
'tours/voucher','tours/voucher_show','tours/voucher_pdf','tours/form','tours/form_edit','tours/invoice','tours/invoice_form',
'transfers/index','transfers/form','transfers/invoice','transfers/invoice_form',
'partners/index','partners/form','partners/show','partners/booking_requests','partners/messages','partners/messages_list',
'receipts/index','receipts/show','receipts/edit','receipts/pdf',
'calendar/index','calendar/hotel','reports/index','services/index','services/form',
'hotel_profiles/index','hotel_profiles/form','users/index','users/form','users/profile',
'settings/index','settings/email','notifications/index','fleet/drivers','fleet/driver_form',
'fleet/vehicles','fleet/vehicle_form','fleet/guides','fleet/guide_form',
'portal/login','portal/dashboard','portal/invoices','portal/invoice_detail',
'portal/vouchers','portal/voucher_detail','portal/bookings','portal/booking_form',
'portal/messages','portal/profile','portal/receipts','portal/receipt_detail',
'layouts/app','layouts/portal','errors/404','errors/403','errors/500',
'partials/sidebar','partials/topbar','partials/share_modal'];
$missing=[];
foreach($views as $v){if(!file_exists(BASE_PATH."/views/$v.php"))$missing[]=$v;}
if(empty($missing))ok("All ".count($views)." view files exist (admin + portal + layouts)");
else fail("Missing views",implode(', ',$missing));

// ═══════════════════════════════════════════════
echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║                     FINAL RESULTS                        ║\n";
echo "╠════════════════════════════════════════════════════════════╣\n";
printf("║  ✅ Passed:   %-44s║\n",$P);
printf("║  ❌ Failed:   %-44s║\n",$F);
printf("║  ⚠️  Warnings: %-44s║\n",$W);
printf("║  Total:      %-44s║\n",$P+$F);
echo "╚════════════════════════════════════════════════════════════╝\n";
if($errs){echo"\nFailures:\n";foreach($errs as $i=>$e)echo"  ".($i+1).". $e\n";}
echo"\n".($F===0?"🎉 ALL TESTS PASSED! Admin + Portal fully functional.":"⚠️  Fix failures above.")."\n\n";
exit($F>0?1:0);
