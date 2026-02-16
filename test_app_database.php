<?php
/**
 * Test the ACTUAL Application Database Class
 * This loads your real config and database files to test
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "=== TESTING YOUR APPLICATION'S DATABASE CLASS ===\n\n";

// Set APP_ROOT
define('APP_ROOT', '/home/barqvkxs/public_html');

// Load your actual config
echo "Step 1: Loading config...\n";
if (file_exists(APP_ROOT . '/config/config.php')) {
    require_once APP_ROOT . '/config/config.php';
    echo "✓ Config loaded\n";
    echo "  DB_HOST: " . DB_HOST . "\n";
    echo "  DB_NAME: " . DB_NAME . "\n";
    echo "  DB_USER: " . DB_USER . "\n";
    echo "  DB_DRIVER: " . (defined('DB_DRIVER') ? DB_DRIVER : 'not set') . "\n";
} else {
    die("✗ Config file not found at: " . APP_ROOT . "/config/config.php\n");
}

// Load your actual database class
echo "\nStep 2: Loading Database class...\n";
if (file_exists(APP_ROOT . '/config/database.php')) {
    require_once APP_ROOT . '/config/database.php';
    echo "✓ Database class loaded\n";
} else {
    die("✗ Database class not found at: " . APP_ROOT . "/config/database.php\n");
}

// Test instantiation
echo "\nStep 3: Testing Database::getInstance()...\n";
try {
    $db = Database::getInstance();
    echo "✓ Database instance created\n";
} catch (Exception $e) {
    echo "✗ FAILED to create instance: " . $e->getMessage() . "\n";
    die("\nCannot proceed.\n</pre>");
}

// Test the static fetchAll method (like your app uses)
echo "\nStep 4: Testing Database::fetchAll() [STATIC]...\n";
try {
    $sql = "SELECT COUNT(*) as count FROM invoices";
    $result = Database::fetchAll($sql);
    echo "✓ Static fetchAll() works\n";
    echo "  Result: " . print_r($result, true) . "\n";
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    echo "  This is exactly what your Invoice model calls!\n";
}

// Test the static fetchOne method
echo "\nStep 5: Testing Database::fetchOne() [STATIC]...\n";
try {
    $sql = "SELECT COUNT(*) as c FROM invoices";
    $result = Database::fetchOne($sql);
    echo "✓ Static fetchOne() works\n";
    echo "  Count: " . $result['c'] . "\n";
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    echo "  This is the EXACT call causing your error!\n";
    echo "  Error on line 204 in database.php\n";
}

// Test with parameters (prepared statements)
echo "\nStep 6: Testing with parameters...\n";
try {
    $sql = "SELECT * FROM invoices WHERE status = ? LIMIT 5";
    $results = Database::fetchAll($sql, ['paid']);
    echo "✓ Prepared statement with params works\n";
    echo "  Found " . count($results) . " paid invoices\n";
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
}

// Test the exact Invoice::getAll() pattern
echo "\nStep 7: Simulating Invoice::getAll() exactly...\n";
try {
    // Load Invoice model
    if (file_exists(APP_ROOT . '/src/Models/Invoice.php')) {
        require_once APP_ROOT . '/src/Models/Invoice.php';
        echo "✓ Invoice model loaded\n";
        
        // Call the actual method
        $result = Invoice::getAll();
        echo "✓ Invoice::getAll() SUCCESS!\n";
        echo "  Total invoices: " . $result['total'] . "\n";
        echo "  Current page: " . $result['page'] . "\n";
        echo "  Total pages: " . $result['pages'] . "\n";
        echo "  Records returned: " . count($result['data']) . "\n";
        
        if (count($result['data']) > 0) {
            echo "\n  Sample invoice:\n";
            $first = $result['data'][0];
            echo "    ID: {$first['id']}\n";
            echo "    Invoice No: {$first['invoice_no']}\n";
            echo "    Company: {$first['company_name']}\n";
            echo "    Amount: {$first['total_amount']} {$first['currency']}\n";
            echo "    Status: {$first['status']}\n";
        }
    } else {
        echo "⚠ Invoice model not found, skipping\n";
    }
} catch (Exception $e) {
    echo "✗ Invoice::getAll() FAILED: " . $e->getMessage() . "\n";
    echo "\n  THIS IS YOUR ACTUAL ERROR!\n";
    echo "  Stack trace:\n";
    echo "  " . str_replace("\n", "\n  ", $e->getTraceAsString()) . "\n";
}

// Check PDO connection directly
echo "\nStep 8: Checking PDO connection...\n";
try {
    $connection = $db->getConnection();
    echo "✓ PDO connection retrieved\n";
    echo "  Connection class: " . get_class($connection) . "\n";
    
    // Try a direct query on the connection
    $stmt = $connection->query("SELECT COUNT(*) as c FROM invoices");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Direct PDO query works: {$result['c']} invoices\n";
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
}

// Check if there are any error logs
echo "\nStep 9: Checking recent error logs...\n";
$log_file = APP_ROOT . '/logs/database_errors.log';
if (file_exists($log_file)) {
    echo "✓ Log file exists\n";
    $lines = file($log_file);
    if (count($lines) > 0) {
        echo "  Last 10 errors:\n";
        $recent = array_slice($lines, -10);
        foreach ($recent as $line) {
            echo "  " . $line;
        }
    } else {
        echo "  Log is empty\n";
    }
} else {
    echo "  No log file found (this is OK)\n";
}

echo "\n========================================\n";
echo "DIAGNOSIS COMPLETE\n";
echo "========================================\n";
echo "\nIf you see '✗ FAILED' above, that's your problem!\n";
echo "Check the error message for the specific issue.\n\n";
echo "Common issues:\n";
echo "1. Config file has wrong credentials\n";
echo "2. Database class instantiation failing\n";
echo "3. PDO connection not working in your app context\n";
echo "4. File permissions preventing class loading\n";
echo "5. Namespace or autoloader issues\n";
echo "</pre>";
?>
