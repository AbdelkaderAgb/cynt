<?php
/**
 * CYN Tourism - Emergency Password Reset Utility
 * 
 * ⚠️ DELETE THIS FILE AFTER USE! ⚠️
 * 
 * Usage: Upload to public_html/ and visit: https://yourdomain.com/reset_admin_pass.php
 * This will reset the admin password and show you the login credentials.
 */

// Load config for database connection
define('BASE_PATH', __DIR__);
define('ROOT_PATH', __DIR__);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$message = '';
$success = false;

// New password to set
$newPassword = 'Admin@123';
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

try {
    // Check if users table exists
    $user = Database::fetchOne("SELECT id, email, password, status FROM users WHERE email = ?", ['admin@cyntourism.com']);
    
    if (!$user) {
        // Insert admin user
        Database::execute(
            "INSERT INTO users (first_name, last_name, email, password, role, status, email_verified, created_at) 
             VALUES ('System', 'Administrator', 'admin@cyntourism.com', ?, 'admin', 'active', 1, NOW())",
            [$hashedPassword]
        );
        $message = "Admin user CREATED with new password.";
        $success = true;
    } else {
        // Update password
        Database::execute(
            "UPDATE users SET password = ?, status = 'active' WHERE email = ?",
            [$hashedPassword, 'admin@cyntourism.com']
        );
        $message = "Admin password RESET successfully.";
        $success = true;
        
        // Show debug info
        $message .= "\n\nOld hash: " . substr($user['password'], 0, 30) . "...";
        $message .= "\nStatus was: " . $user['status'];
    }
    
    // Verify the new hash works
    $verify = password_verify($newPassword, $hashedPassword);
    $message .= "\nPassword verification: " . ($verify ? '✅ PASS' : '❌ FAIL');
    
    // Check table structure
    try {
        $cols = Database::fetchAll("SHOW COLUMNS FROM users");
        $colNames = array_column($cols, 'Field');
        $message .= "\n\nUsers table columns: " . implode(', ', $colNames);
        
        if (!in_array('login_count', $colNames)) {
            $message .= "\n\n⚠️ Missing 'login_count' column. Adding it now...";
            Database::execute("ALTER TABLE users ADD COLUMN login_count int(11) DEFAULT 0");
            $message .= " ✅ Added.";
        }
        if (!in_array('full_name', $colNames)) {
            $message .= "\n⚠️ Missing 'full_name' column (generated). Adding it now...";
            try {
                Database::execute("ALTER TABLE users ADD COLUMN full_name varchar(200) GENERATED ALWAYS AS (CONCAT(first_name,' ',last_name)) STORED");
                $message .= " ✅ Added.";
            } catch (Exception $e) {
                $message .= " ⚠️ Skipped (may not be supported): " . $e->getMessage();
            }
        }
    } catch (Exception $e) {
        $message .= "\nColumn check error: " . $e->getMessage();
    }
    
    // Check partners table for password column
    try {
        $partnerCols = Database::fetchAll("SHOW COLUMNS FROM partners");
        $partnerColNames = array_column($partnerCols, 'Field');
        if (!in_array('password', $partnerColNames)) {
            $message .= "\n\n⚠️ Missing 'password' column in partners table. Adding it now...";
            Database::execute("ALTER TABLE partners ADD COLUMN password varchar(255) DEFAULT NULL AFTER email");
            $message .= " ✅ Added.";
        }
    } catch (Exception $e) {
        $message .= "\nPartners table check: " . $e->getMessage();
    }

} catch (Exception $e) {
    $message = "ERROR: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CYN - Password Reset</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-900 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-8">
        <h1 class="text-2xl font-bold mb-6 text-center">🔐 CYN Admin Password Reset</h1>
        
        <?php if ($success): ?>
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6">
            <p class="text-green-700 font-medium">✅ <?= nl2br(htmlspecialchars($message)) ?></p>
        </div>
        
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6">
            <h2 class="font-bold text-blue-900 mb-3 text-lg">Login Credentials:</h2>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-blue-700 font-medium">Email:</span>
                    <code class="bg-blue-100 px-3 py-1 rounded text-blue-900 font-mono">admin@cyntourism.com</code>
                </div>
                <div class="flex justify-between">
                    <span class="text-blue-700 font-medium">Password:</span>
                    <code class="bg-blue-100 px-3 py-1 rounded text-blue-900 font-mono"><?= htmlspecialchars($newPassword) ?></code>
                </div>
            </div>
        </div>
        
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
            <p class="text-red-700 font-bold">⚠️ DELETE THIS FILE IMMEDIATELY!</p>
            <p class="text-red-600 text-sm mt-1">Delete <code>reset_admin_pass.php</code> from your server after login.</p>
        </div>
        
        <a href="login" class="block w-full text-center bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 rounded-xl transition-colors">
            Go to Login →
        </a>
        <?php else: ?>
        <div class="bg-red-50 border border-red-200 rounded-xl p-4">
            <p class="text-red-700"><?= nl2br(htmlspecialchars($message)) ?></p>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
