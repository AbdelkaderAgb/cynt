<?php
/**
 * QUICK DATABASE TEST
 * Tests if basic database queries work with your credentials
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Quick Database Test</h2>";
echo "<pre>";

// Test 1: Can we connect?
echo "TEST 1: Basic Connection\n";
echo "-------------------------\n";
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=barqvkxs_cyn;charset=utf8mb4',
        'barqvkxs_cyn',
        'tW@{cFk&^ep]',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    echo "✓ SUCCESS: Connected to database\n\n";
} catch (PDOException $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n\n";
    echo "This is your problem! Cannot connect to database.\n";
    echo "Check:\n";
    echo "  1. Is MySQL running?\n";
    echo "  2. Is database name 'barqvkxs_cyn' correct?\n";
    echo "  3. Are credentials correct?\n";
    echo "  4. Does user have permissions?\n";
    die("</pre>");
}

// Test 2: Can we see tables?
echo "TEST 2: Check Tables\n";
echo "-------------------------\n";
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "✓ SUCCESS: Found " . count($tables) . " tables\n";
        echo "Tables: " . implode(", ", array_slice($tables, 0, 5));
        if (count($tables) > 5) echo ", ...";
        echo "\n\n";
    } else {
        echo "✗ PROBLEM: No tables found!\n";
        echo "You need to import COMPLETE_DATABASE.sql\n\n";
    }
} catch (PDOException $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n\n";
}

// Test 3: Can we query invoices table?
echo "TEST 3: Query Invoices Table\n";
echo "-------------------------\n";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM invoices");
    $result = $stmt->fetch();
    echo "✓ SUCCESS: Found {$result['count']} invoices\n";
    
    // Get one invoice
    $stmt = $pdo->query("SELECT * FROM invoices LIMIT 1");
    $invoice = $stmt->fetch();
    if ($invoice) {
        echo "✓ Sample invoice exists:\n";
        echo "  - Invoice No: {$invoice['invoice_no']}\n";
        echo "  - Company: {$invoice['company_name']}\n";
        echo "  - Amount: {$invoice['total_amount']} {$invoice['currency']}\n";
    }
    echo "\n";
} catch (PDOException $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    if (strpos($e->getMessage(), "doesn't exist") !== false) {
        echo "The invoices table doesn't exist!\n";
        echo "Import COMPLETE_DATABASE.sql to fix this.\n";
    } else {
        echo "Check permissions or table structure.\n";
    }
    echo "\n";
}

// Test 4: Can we use prepared statements?
echo "TEST 4: Prepared Statement\n";
echo "-------------------------\n";
try {
    $stmt = $pdo->prepare("SELECT * FROM invoices WHERE status = ? LIMIT 3");
    $stmt->execute(['paid']);
    $results = $stmt->fetchAll();
    echo "✓ SUCCESS: Prepared statement works\n";
    echo "  Found " . count($results) . " paid invoices\n\n";
} catch (PDOException $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n\n";
}

// Test 5: Check permissions
echo "TEST 5: User Permissions\n";
echo "-------------------------\n";
try {
    $stmt = $pdo->query("SHOW GRANTS FOR CURRENT_USER()");
    $grants = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Your database user has these permissions:\n";
    foreach ($grants as $grant) {
        echo "  • " . $grant . "\n";
    }
    
    $all_grants = implode(" ", $grants);
    $has_all = stripos($all_grants, 'ALL PRIVILEGES') !== false;
    $has_select = stripos($all_grants, 'SELECT') !== false;
    
    if ($has_all || $has_select) {
        echo "\n✓ Permissions look good\n\n";
    } else {
        echo "\n✗ WARNING: User might be missing SELECT permission\n";
        echo "Run this to fix:\n";
        echo "GRANT ALL PRIVILEGES ON barqvkxs_cyn.* TO 'barqvkxs_cyn'@'localhost';\n";
        echo "FLUSH PRIVILEGES;\n\n";
    }
} catch (PDOException $e) {
    echo "Could not check permissions: " . $e->getMessage() . "\n\n";
}

// SUMMARY
echo "================================\n";
echo "SUMMARY\n";
echo "================================\n\n";

$all_passed = true;
if (!isset($pdo)) {
    echo "✗ CRITICAL: Cannot connect to database\n";
    echo "Fix: Check database credentials in config.php\n\n";
    $all_passed = false;
}
if (isset($tables) && count($tables) == 0) {
    echo "✗ CRITICAL: No tables in database\n";
    echo "Fix: Import COMPLETE_DATABASE.sql\n\n";
    $all_passed = false;
}
if (!isset($result) || $result['count'] == 0) {
    echo "⚠ WARNING: Invoices table is empty\n";
    echo "Fix: Import COMPLETE_DATABASE.sql (has 25 sample invoices)\n\n";
}

if ($all_passed) {
    echo "✓ Database is working correctly!\n\n";
    echo "If your application still shows errors:\n";
    echo "1. Problem is in your application code or config\n";
    echo "2. Run test_app_database.php to test your actual app\n";
    echo "3. Check /config/config.php for wrong credentials\n";
    echo "4. Look at error logs in /logs/\n\n";
} else {
    echo "Fix the issues above, then:\n";
    echo "1. Refresh this page to test again\n";
    echo "2. Or run advanced_diagnose.php for detailed analysis\n\n";
}

echo "</pre>";
?>
