<?php
/**
 * SQLite Database Setup Script
 * Creates the SQLite database with all tables and seed data
 */

$dbPath = __DIR__ . '/cyn_tourism.sqlite';

// Remove old database if exists
if (file_exists($dbPath)) {
    unlink($dbPath);
}

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("PRAGMA journal_mode=WAL");
    $pdo->exec("PRAGMA foreign_keys=ON");
    
    echo "Creating tables...\n";
    
    // 1. USERS
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        first_name TEXT NOT NULL,
        last_name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        role TEXT DEFAULT 'viewer',
        status TEXT DEFAULT 'pending',
        email_verified INTEGER DEFAULT 0,
        email_verified_at TEXT,
        last_login TEXT,
        last_login_ip TEXT,
        login_count INTEGER DEFAULT 0,
        failed_login_attempts INTEGER DEFAULT 0,
        locked_until TEXT,
        password_changed_at TEXT,
        remember_token TEXT,
        remember_token_expires TEXT,
        reset_token TEXT,
        reset_token_expires TEXT,
        two_factor_secret TEXT,
        two_factor_enabled INTEGER DEFAULT 0,
        profile_image TEXT,
        phone TEXT,
        created_at TEXT DEFAULT (datetime('now')),
        updated_at TEXT DEFAULT (datetime('now')),
        deleted_at TEXT
    )");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_users_role ON users(role)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_users_status ON users(status)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_users_remember_token ON users(remember_token)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_users_reset_token ON users(reset_token)");
    
    // 2. VOUCHERS
    $pdo->exec("CREATE TABLE IF NOT EXISTS vouchers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        voucher_no TEXT NOT NULL UNIQUE,
        company_name TEXT NOT NULL,
        company_id INTEGER,
        hotel_name TEXT NOT NULL,
        pickup_location TEXT NOT NULL,
        dropoff_location TEXT NOT NULL,
        pickup_date TEXT NOT NULL,
        pickup_time TEXT NOT NULL,
        return_date TEXT,
        return_time TEXT,
        transfer_type TEXT DEFAULT 'one_way',
        total_pax INTEGER NOT NULL DEFAULT 0,
        passengers TEXT,
        flight_number TEXT,
        flight_arrival_time TEXT,
        vehicle_id INTEGER,
        driver_id INTEGER,
        guide_id INTEGER,
        special_requests TEXT,
        price REAL DEFAULT 0.00,
        currency TEXT DEFAULT 'USD',
        status TEXT DEFAULT 'pending',
        payment_status TEXT DEFAULT 'unpaid',
        notes TEXT,
        created_by INTEGER,
        updated_by INTEGER,
        created_at TEXT DEFAULT (datetime('now')),
        updated_at TEXT DEFAULT (datetime('now'))
    )");
    
    // 4. TOURS
    $pdo->exec("CREATE TABLE IF NOT EXISTS tours (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        tour_name TEXT NOT NULL,
        tour_code TEXT,
        description TEXT,
        tour_type TEXT DEFAULT 'daily',
        destination TEXT,
        pickup_location TEXT,
        dropoff_location TEXT,
        tour_date TEXT NOT NULL,
        start_time TEXT,
        end_time TEXT,
        duration_days INTEGER DEFAULT 1,
        total_pax INTEGER NOT NULL DEFAULT 0,
        max_pax INTEGER,
        passengers TEXT,
        company_name TEXT,
        company_id INTEGER,
        hotel_name TEXT DEFAULT '',
        customer_phone TEXT DEFAULT '',
        adults INTEGER DEFAULT 0,
        children INTEGER DEFAULT 0,
        infants INTEGER DEFAULT 0,
        guide_id INTEGER,
        vehicle_id INTEGER,
        driver_id INTEGER,
        price_per_person REAL DEFAULT 0.00,
        price_child REAL DEFAULT 0.00,
        price_per_infant REAL DEFAULT 0.00,
        total_price REAL DEFAULT 0.00,
        currency TEXT DEFAULT 'USD',
        includes TEXT,
        excludes TEXT,
        itinerary TEXT,
        special_requests TEXT,
        status TEXT DEFAULT 'pending',
        payment_status TEXT DEFAULT 'unpaid',
        notes TEXT,
        customers TEXT DEFAULT '[]',
        tour_items TEXT DEFAULT '[]',
        created_by INTEGER,
        updated_by INTEGER,
        created_at TEXT DEFAULT (datetime('now')),
        updated_at TEXT DEFAULT (datetime('now'))
    )");
    
    // 4. HOTEL VOUCHERS
    $pdo->exec("CREATE TABLE IF NOT EXISTS hotel_vouchers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        voucher_no TEXT NOT NULL UNIQUE,
        guest_name TEXT NOT NULL,
        hotel_name TEXT NOT NULL,
        hotel_id INTEGER,
        company_name TEXT,
        company_id INTEGER,
        address TEXT DEFAULT '',
        telephone TEXT DEFAULT '',
        room_type TEXT,
        room_count INTEGER DEFAULT 1,
        board_type TEXT DEFAULT 'bed_breakfast',
        transfer_type TEXT DEFAULT 'without',
        check_in TEXT NOT NULL,
        check_out TEXT NOT NULL,
        nights INTEGER DEFAULT 1,
        total_pax INTEGER DEFAULT 0,
        adults INTEGER DEFAULT 0,
        children INTEGER DEFAULT 0,
        infants INTEGER DEFAULT 0,
        confirmation_no TEXT,
        price_per_night REAL DEFAULT 0.00,
        total_price REAL DEFAULT 0.00,
        currency TEXT DEFAULT 'USD',
        customers TEXT DEFAULT '[]',
        special_requests TEXT,
        status TEXT DEFAULT 'pending',
        payment_status TEXT DEFAULT 'unpaid',
        notes TEXT,
        created_by INTEGER,
        updated_by INTEGER,
        created_at TEXT DEFAULT (datetime('now')),
        updated_at TEXT DEFAULT (datetime('now'))
    )");
    
    // 5. INVOICES
    $pdo->exec("CREATE TABLE IF NOT EXISTS invoices (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        invoice_no TEXT NOT NULL UNIQUE,
        company_name TEXT NOT NULL,
        company_id INTEGER,
        invoice_date TEXT NOT NULL,
        due_date TEXT NOT NULL,
        subtotal REAL NOT NULL DEFAULT 0.00,
        tax_rate REAL DEFAULT 0.00,
        tax_amount REAL DEFAULT 0.00,
        discount REAL DEFAULT 0.00,
        total_amount REAL NOT NULL DEFAULT 0.00,
        paid_amount REAL DEFAULT 0.00,
        currency TEXT DEFAULT 'USD',
        status TEXT DEFAULT 'draft',
        payment_method TEXT,
        payment_date TEXT,
        notes TEXT,
        terms TEXT,
        file_path TEXT,
        sent_at TEXT,
        sent_by INTEGER,
        created_by INTEGER,
        created_at TEXT DEFAULT (datetime('now')),
        updated_at TEXT DEFAULT (datetime('now'))
    )");
    
    // 6. INVOICE ITEMS
    $pdo->exec("CREATE TABLE IF NOT EXISTS invoice_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        invoice_id INTEGER NOT NULL,
        item_type TEXT DEFAULT 'voucher',
        item_id INTEGER,
        description TEXT NOT NULL,
        quantity INTEGER NOT NULL DEFAULT 1,
        unit_price REAL NOT NULL DEFAULT 0.00,
        total_price REAL NOT NULL DEFAULT 0.00,
        created_at TEXT DEFAULT (datetime('now')),
        FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
    )");
    
    // 7. PARTNERS
    $pdo->exec("CREATE TABLE IF NOT EXISTS partners (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        company_name TEXT NOT NULL,
        contact_person TEXT,
        email TEXT NOT NULL UNIQUE,
        phone TEXT,
        mobile TEXT,
        address TEXT,
        city TEXT,
        country TEXT,
        postal_code TEXT,
        website TEXT,
        tax_id TEXT,
        commission_rate REAL DEFAULT 0.00,
        credit_limit REAL DEFAULT 0.00,
        balance REAL DEFAULT 0.00,
        payment_terms INTEGER DEFAULT 30,
        partner_type TEXT DEFAULT 'agency',
        status TEXT DEFAULT 'active',
        notes TEXT,
        contract_file TEXT,
        created_by INTEGER,
        created_at TEXT DEFAULT (datetime('now')),
        updated_at TEXT DEFAULT (datetime('now'))
    )");
    
    // 8. VEHICLES
    $pdo->exec("CREATE TABLE IF NOT EXISTS vehicles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        plate_number TEXT NOT NULL UNIQUE,
        make TEXT NOT NULL,
        model TEXT NOT NULL,
        year INTEGER,
        color TEXT,
        capacity INTEGER NOT NULL DEFAULT 4,
        luggage_capacity INTEGER DEFAULT 2,
        vehicle_type TEXT DEFAULT 'sedan',
        fuel_type TEXT DEFAULT 'gasoline',
        insurance_expiry TEXT,
        registration_expiry TEXT,
        mileage INTEGER DEFAULT 0,
        status TEXT DEFAULT 'available',
        last_maintenance TEXT,
        next_maintenance TEXT,
        driver_id INTEGER,
        image TEXT,
        notes TEXT,
        created_at TEXT DEFAULT (datetime('now')),
        updated_at TEXT DEFAULT (datetime('now'))
    )");
    
    // 9. DRIVERS
    $pdo->exec("CREATE TABLE IF NOT EXISTS drivers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        first_name TEXT NOT NULL,
        last_name TEXT NOT NULL,
        email TEXT UNIQUE,
        phone TEXT NOT NULL,
        mobile TEXT,
        license_no TEXT NOT NULL UNIQUE,
        license_expiry TEXT NOT NULL,
        license_type TEXT,
        id_number TEXT,
        date_of_birth TEXT,
        address TEXT,
        city TEXT,
        emergency_contact TEXT,
        emergency_phone TEXT,
        hire_date TEXT,
        termination_date TEXT,
        status TEXT DEFAULT 'active',
        rating REAL DEFAULT 5.0,
        total_trips INTEGER DEFAULT 0,
        languages TEXT,
        photo TEXT,
        documents TEXT,
        notes TEXT,
        created_at TEXT DEFAULT (datetime('now')),
        updated_at TEXT DEFAULT (datetime('now'))
    )");
    
    // 10. TOUR GUIDES
    $pdo->exec("CREATE TABLE IF NOT EXISTS tour_guides (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        first_name TEXT NOT NULL,
        last_name TEXT NOT NULL,
        email TEXT UNIQUE,
        phone TEXT NOT NULL,
        mobile TEXT,
        license_no TEXT UNIQUE,
        license_expiry TEXT,
        id_number TEXT,
        date_of_birth TEXT,
        address TEXT,
        city TEXT,
        languages TEXT NOT NULL,
        specializations TEXT,
        experience_years INTEGER DEFAULT 0,
        daily_rate REAL DEFAULT 0.00,
        currency TEXT DEFAULT 'USD',
        hire_date TEXT,
        termination_date TEXT,
        status TEXT DEFAULT 'active',
        rating REAL DEFAULT 5.0,
        total_tours INTEGER DEFAULT 0,
        photo TEXT,
        documents TEXT,
        bio TEXT,
        notes TEXT,
        created_at TEXT DEFAULT (datetime('now')),
        updated_at TEXT DEFAULT (datetime('now'))
    )");
    
    // 11. NOTIFICATIONS
    $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        title TEXT NOT NULL,
        message TEXT NOT NULL,
        type TEXT DEFAULT 'info',
        category TEXT DEFAULT 'general',
        related_id INTEGER,
        related_type TEXT,
        action_url TEXT,
        is_read INTEGER DEFAULT 0,
        read_at TEXT,
        sent_email INTEGER DEFAULT 0,
        sent_push INTEGER DEFAULT 0,
        created_at TEXT DEFAULT (datetime('now'))
    )");
    
    // 12. ACTIVITY LOGS
    $pdo->exec("CREATE TABLE IF NOT EXISTS activity_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        action TEXT NOT NULL,
        description TEXT,
        entity_type TEXT,
        entity_id INTEGER,
        old_values TEXT,
        new_values TEXT,
        ip_address TEXT NOT NULL,
        user_agent TEXT,
        session_id TEXT,
        request_method TEXT,
        request_url TEXT,
        severity TEXT DEFAULT 'info',
        created_at TEXT DEFAULT (datetime('now'))
    )");
    
    // 13. LOGIN ATTEMPTS
    $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT NOT NULL,
        ip_address TEXT NOT NULL,
        attempts INTEGER DEFAULT 1,
        last_attempt TEXT DEFAULT (datetime('now')),
        locked_until TEXT,
        success INTEGER DEFAULT 0,
        user_agent TEXT
    )");
    
    // 14. SETTINGS
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        setting_key TEXT NOT NULL UNIQUE,
        setting_value TEXT,
        setting_group TEXT DEFAULT 'general',
        data_type TEXT DEFAULT 'string',
        is_encrypted INTEGER DEFAULT 0,
        description TEXT,
        created_at TEXT DEFAULT (datetime('now')),
        updated_at TEXT DEFAULT (datetime('now'))
    )");
    
    // 15. PASSWORD RESETS
    $pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT NOT NULL,
        token TEXT NOT NULL UNIQUE,
        expires_at TEXT NOT NULL,
        used INTEGER DEFAULT 0,
        used_at TEXT,
        ip_address TEXT,
        created_at TEXT DEFAULT (datetime('now'))
    )");
    
    // 16. API TOKENS
    $pdo->exec("CREATE TABLE IF NOT EXISTS api_tokens (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        token_name TEXT NOT NULL,
        token TEXT NOT NULL UNIQUE,
        abilities TEXT,
        last_used_at TEXT,
        expires_at TEXT,
        revoked INTEGER DEFAULT 0,
        revoked_at TEXT,
        created_at TEXT DEFAULT (datetime('now'))
    )");
    
    // 17. API KEYS
    $pdo->exec("CREATE TABLE IF NOT EXISTS api_keys (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        api_key TEXT NOT NULL UNIQUE,
        name TEXT,
        is_active INTEGER DEFAULT 1,
        last_used_at TEXT,
        created_at TEXT DEFAULT (datetime('now'))
    )");
    
    // 18. EMAIL CONFIG
    $pdo->exec("CREATE TABLE IF NOT EXISTS email_config (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        smtp_host TEXT,
        smtp_port INTEGER DEFAULT 587,
        smtp_username TEXT,
        smtp_password TEXT,
        from_email TEXT,
        from_name TEXT,
        enable_notifications INTEGER DEFAULT 1,
        enable_reminders INTEGER DEFAULT 1,
        updated_at TEXT DEFAULT (datetime('now'))
    )");
    
    // 19. REMINDER LOGS
    $pdo->exec("CREATE TABLE IF NOT EXISTS reminder_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        voucher_id INTEGER NOT NULL,
        reminder_type TEXT NOT NULL,
        status TEXT NOT NULL,
        created_at TEXT DEFAULT (datetime('now'))
    )");
    
    // 20. TOUR ASSIGNMENTS
    $pdo->exec("CREATE TABLE IF NOT EXISTS tour_assignments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        tour_id INTEGER NOT NULL,
        guide_id INTEGER,
        vehicle_id INTEGER,
        driver_id INTEGER,
        assignment_date TEXT NOT NULL,
        start_time TEXT,
        end_time TEXT,
        status TEXT DEFAULT 'scheduled',
        notes TEXT,
        created_by INTEGER,
        created_at TEXT DEFAULT (datetime('now')),
        updated_at TEXT DEFAULT (datetime('now'))
    )");
    
    echo "Tables created successfully!\n";
    
    // Seed admin user (password: Admin@123)
    $pdo->exec("INSERT INTO users (first_name, last_name, email, password, role, status, email_verified, email_verified_at, created_at)
        VALUES ('System', 'Administrator', 'admin@cyntourism.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', 1, datetime('now'), datetime('now'))");
    
    // Seed default settings
    $settings = [
        ['site_name', 'CYN Tourism', 'general', 'string', 'Website name'],
        ['site_email', 'info@cyntourism.com', 'general', 'string', 'Default site email'],
        ['timezone', 'Europe/Istanbul', 'general', 'string', 'System timezone'],
        ['date_format', 'd/m/Y', 'general', 'string', 'Default date format'],
        ['time_format', 'H:i', 'general', 'string', 'Default time format'],
        ['currency', 'USD', 'general', 'string', 'Default currency'],
        ['max_login_attempts', '5', 'security', 'integer', 'Maximum failed login attempts before lockout'],
        ['lockout_duration', '30', 'security', 'integer', 'Account lockout duration in minutes'],
        ['password_min_length', '8', 'security', 'integer', 'Minimum password length'],
        ['session_timeout', '120', 'security', 'integer', 'Session timeout in minutes'],
        ['maintenance_mode', '0', 'system', 'boolean', 'Enable maintenance mode'],
        ['debug_mode', '0', 'system', 'boolean', 'Enable debug mode'],
    ];
    
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO settings (setting_key, setting_value, setting_group, data_type, description) VALUES (?, ?, ?, ?, ?)");
    foreach ($settings as $s) {
        $stmt->execute($s);
    }
    
    echo "Seed data inserted!\n";
    echo "Admin login: admin@cyntourism.com / Admin@123\n";
    echo "Database created at: $dbPath\n";
    echo "SUCCESS!\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
