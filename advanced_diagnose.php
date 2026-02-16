<?php
/**
 * ADVANCED Database Diagnostic - Test Queries & Permissions
 * Run this to find out WHY queries are failing even though tables exist
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "=== ADVANCED CYN TOURISM DATABASE DIAGNOSTIC ===\n";
echo "Testing actual queries and permissions...\n\n";

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'barqvkxs_cyn');
define('DB_USER', 'barqvkxs_cyn');
define('DB_PASS', 'tW@{cFk&^ep]');
define('DB_CHARSET', 'utf8mb4');

$tests_passed = 0;
$tests_failed = 0;

// Test 1: Basic Connection
echo "TEST 1: Database Connection\n";
echo "----------------------------------------\n";
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    echo "✓ Connected successfully\n";
    $tests_passed++;
} catch (PDOException $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
    die("\nCannot proceed without database connection.\n</pre>");
}

// Test 2: List all tables
echo "\nTEST 2: Check Tables Exist\n";
echo "----------------------------------------\n";
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "✓ Found " . count($tables) . " tables\n";
        echo "Tables: " . implode(", ", $tables) . "\n";
        $tests_passed++;
    } else {
        echo "✗ No tables found!\n";
        $tests_failed++;
    }
} catch (PDOException $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// Test 3: Check User Permissions
echo "\nTEST 3: Database User Permissions\n";
echo "----------------------------------------\n";
try {
    $stmt = $pdo->query("SHOW GRANTS FOR CURRENT_USER()");
    $grants = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Current grants:\n";
    foreach ($grants as $grant) {
        echo "  $grant\n";
    }
    
    // Check for necessary permissions
    $grants_text = implode(" ", $grants);
    $has_select = stripos($grants_text, 'SELECT') !== false || stripos($grants_text, 'ALL PRIVILEGES') !== false;
    $has_insert = stripos($grants_text, 'INSERT') !== false || stripos($grants_text, 'ALL PRIVILEGES') !== false;
    $has_update = stripos($grants_text, 'UPDATE') !== false || stripos($grants_text, 'ALL PRIVILEGES') !== false;
    
    if ($has_select && $has_insert && $has_update) {
        echo "✓ User has necessary permissions (SELECT, INSERT, UPDATE)\n";
        $tests_passed++;
    } else {
        echo "✗ User missing permissions:\n";
        if (!$has_select) echo "  - Missing SELECT\n";
        if (!$has_insert) echo "  - Missing INSERT\n";
        if (!$has_update) echo "  - Missing UPDATE\n";
        $tests_failed++;
    }
} catch (PDOException $e) {
    echo "⚠ Could not check grants: " . $e->getMessage() . "\n";
}

// Test 4: Try to SELECT from invoices table
echo "\nTEST 4: SELECT Query on 'invoices' Table\n";
echo "----------------------------------------\n";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM invoices");
    $result = $stmt->fetch();
    echo "✓ SELECT query successful\n";
    echo "  Found {$result['count']} invoice records\n";
    $tests_passed++;
    
    // Try to get one invoice
    $stmt = $pdo->query("SELECT * FROM invoices LIMIT 1");
    $invoice = $stmt->fetch();
    if ($invoice) {
        echo "  Sample invoice: {$invoice['invoice_no']} - {$invoice['company_name']}\n";
    }
} catch (PDOException $e) {
    echo "✗ SELECT FAILED: " . $e->getMessage() . "\n";
    echo "  SQL State: " . $e->getCode() . "\n";
    $tests_failed++;
}

// Test 5: Test prepared statement (like your app uses)
echo "\nTEST 5: Prepared Statement Query\n";
echo "----------------------------------------\n";
try {
    $stmt = $pdo->prepare("SELECT * FROM invoices WHERE status = ? LIMIT 5");
    $stmt->execute(['paid']);
    $results = $stmt->fetchAll();
    echo "✓ Prepared statement successful\n";
    echo "  Found " . count($results) . " paid invoices\n";
    $tests_passed++;
} catch (PDOException $e) {
    echo "✗ Prepared statement FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// Test 6: Test the exact query from Invoice::getAll()
echo "\nTEST 6: Invoice::getAll() Query (from your code)\n";
echo "----------------------------------------\n";
try {
    // This is the exact query structure from your Invoice.php model
    $sql = "SELECT COUNT(*) as c FROM invoices";
    $stmt = $pdo->query($sql);
    $result = $stmt->fetch();
    
    echo "✓ Count query successful: {$result['c']} invoices\n";
    
    $sql = "SELECT * FROM invoices ORDER BY created_at DESC LIMIT 20 OFFSET 0";
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll();
    
    echo "✓ Full query successful: " . count($results) . " records returned\n";
    $tests_passed++;
} catch (PDOException $e) {
    echo "✗ Query FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// Test 7: Check table structure
echo "\nTEST 7: Invoice Table Structure\n";
echo "----------------------------------------\n";
try {
    $stmt = $pdo->query("DESCRIBE invoices");
    $columns = $stmt->fetchAll();
    echo "✓ Table structure:\n";
    foreach ($columns as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }
    $tests_passed++;
} catch (PDOException $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $tests_failed++;
}

// Test 8: Check character set and collation
echo "\nTEST 8: Character Set & Collation\n";
echo "----------------------------------------\n";
try {
    $stmt = $pdo->query("SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");
    $result = $stmt->fetch();
    echo "Database charset: {$result['DEFAULT_CHARACTER_SET_NAME']}\n";
    echo "Database collation: {$result['DEFAULT_COLLATION_NAME']}\n";
    
    $stmt = $pdo->query("SHOW TABLE STATUS LIKE 'invoices'");
    $result = $stmt->fetch();
    echo "Invoices table collation: {$result['Collation']}\n";
    
    $tests_passed++;
} catch (PDOException $e) {
    echo "⚠ Could not check charset: " . $e->getMessage() . "\n";
}

// Test 9: MySQL/MariaDB version and settings
echo "\nTEST 9: MySQL/MariaDB Configuration\n";
echo "----------------------------------------\n";
try {
    $stmt = $pdo->query("SELECT VERSION() as version");
    $result = $stmt->fetch();
    echo "MySQL Version: {$result['version']}\n";
    
    $stmt = $pdo->query("SHOW VARIABLES LIKE 'max_connections'");
    $result = $stmt->fetch();
    echo "Max connections: {$result['Value']}\n";
    
    $stmt = $pdo->query("SHOW STATUS LIKE 'Threads_connected'");
    $result = $stmt->fetch();
    echo "Current connections: {$result['Value']}\n";
    
    $tests_passed++;
} catch (PDOException $e) {
    echo "⚠ Could not check MySQL config: " . $e->getMessage() . "\n";
}

// Test 10: Test with same method as your app (using Database class pattern)
echo "\nTEST 10: Simulate Your Application's Database Class\n";
echo "----------------------------------------\n";
try {
    // Simulate the static method call pattern
    $sql = "SELECT COUNT(*) as c FROM invoices WHERE 1=1";
    $params = [];
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch();
    
    echo "✓ Static method pattern successful\n";
    echo "  Count: {$result['c']}\n";
    $tests_passed++;
} catch (PDOException $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    echo "  This is the pattern your app uses!\n";
    $tests_failed++;
}

// Test 11: Check if config file is accessible
echo "\nTEST 11: Application Files Check\n";
echo "----------------------------------------\n";
$config_file = '/home/barqvkxs/public_html/config/config.php';
$database_file = '/home/barqvkxs/public_html/config/database.php';

if (file_exists($config_file)) {
    echo "✓ Config file exists: $config_file\n";
    if (is_readable($config_file)) {
        echo "✓ Config file is readable\n";
    } else {
        echo "✗ Config file is NOT readable (permissions issue)\n";
    }
} else {
    echo "✗ Config file NOT found: $config_file\n";
}

if (file_exists($database_file)) {
    echo "✓ Database class file exists: $database_file\n";
    if (is_readable($database_file)) {
        echo "✓ Database file is readable\n";
    } else {
        echo "✗ Database file is NOT readable (permissions issue)\n";
    }
} else {
    echo "✗ Database file NOT found: $database_file\n";
}

// Test 12: Check error logs
echo "\nTEST 12: Check Error Logs\n";
echo "----------------------------------------\n";
$log_files = [
    '/home/barqvkxs/public_html/logs/database_errors.log',
    '/home/barqvkxs/public_html/logs/php-errors.log'
];

foreach ($log_files as $log_file) {
    if (file_exists($log_file)) {
        echo "Log file: $log_file\n";
        $lines = @file($log_file);
        if ($lines) {
            $recent = array_slice($lines, -5);
            echo "Last 5 entries:\n";
            foreach ($recent as $line) {
                echo "  " . trim($line) . "\n";
            }
        } else {
            echo "  (empty or unreadable)\n";
        }
    } else {
        echo "Log file not found: $log_file\n";
    }
}

// Summary
echo "\n========================================\n";
echo "DIAGNOSTIC SUMMARY\n";
echo "========================================\n";
echo "Tests Passed: $tests_passed\n";
echo "Tests Failed: $tests_failed\n";
echo "\n";

if ($tests_failed > 0) {
    echo "⚠ ISSUES DETECTED\n\n";
    echo "Based on the failed tests above, the problem is likely:\n";
    
    if (stripos(ob_get_contents(), 'Missing SELECT') !== false) {
        echo "1. DATABASE PERMISSIONS - User lacks necessary permissions\n";
        echo "   FIX: Grant proper permissions to user\n\n";
    }
    
    if (stripos(ob_get_contents(), 'SELECT FAILED') !== false) {
        echo "2. QUERY EXECUTION ISSUE\n";
        echo "   FIX: Check the error message above for details\n\n";
    }
    
    echo "3. Check your application's config.php file\n";
    echo "   Ensure DB_HOST, DB_NAME, DB_USER, DB_PASS are correct\n\n";
    
    echo "4. Check if your app is using a different database or connection\n\n";
    
} else {
    echo "✓ ALL TESTS PASSED!\n\n";
    echo "The database is working correctly when accessed directly.\n";
    echo "If your application still shows errors, the problem is likely:\n\n";
    echo "1. CONFIG FILE ISSUE\n";
    echo "   - Check /home/barqvkxs/public_html/config/config.php\n";
    echo "   - Verify DB credentials match what we tested\n";
    echo "   - Look for typos in DB_HOST, DB_NAME, DB_USER, DB_PASS\n\n";
    
    echo "2. APPLICATION CODE ISSUE\n";
    echo "   - Database class might not be instantiating correctly\n";
    echo "   - Check if Database::getInstance() is working\n";
    echo "   - Look at /home/barqvkxs/public_html/config/database.php line 204\n\n";
    
    echo "3. PHP CONFIGURATION\n";
    echo "   - PDO/MySQL extension might not be loaded in your PHP environment\n";
    echo "   - Check php.ini or run phpinfo()\n\n";
    
    echo "4. FILE PERMISSIONS\n";
    echo "   - Config files might not be readable by web server\n";
    echo "   - Check file ownership and permissions\n\n";
}

echo "========================================\n";
echo "</pre>";
?>
