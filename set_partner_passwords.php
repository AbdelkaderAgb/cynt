<?php
/**
 * CYN Tourism - Set Partner Portal Passwords
 * 
 * INSTRUCTIONS:
 * 1. Upload this file to public_html/
 * 2. Visit: https://yourdomain.com/set_partner_passwords.php
 * 3. DELETE this file immediately after running!
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$passwords = [
    1 => 'Atlas2026!',      // Atlas Travel Algeria    | karim@atlastravel.dz
    2 => 'Sahara2026!',     // Sahara Tours DZ         | fatima@saharatours.dz
    3 => 'Baku2026!',       // Baku Premium Travel     | eldar@bakutravel.az
    4 => 'Caspian2026!',    // Caspian Holidays        | leyla@caspianholidays.az
    5 => 'Anatolia2026!',   // Anatolian Voyages       | ahmet@anatolianvoyages.com
];

echo "<h2>CYN Tourism - Setting Partner Passwords</h2>";
echo "<table border='1' cellpadding='8' cellspacing='0'>";
echo "<tr><th>#</th><th>Company</th><th>Email</th><th>Password</th><th>Status</th></tr>";

$partners = Database::fetchAll("SELECT id, company_name, email FROM partners WHERE id IN (1,2,3,4,5) ORDER BY id");

foreach ($partners as $partner) {
    $id = (int)$partner['id'];
    if (!isset($passwords[$id])) continue;
    
    $pass = $passwords[$id];
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    
    Database::execute("UPDATE partners SET password = ? WHERE id = ?", [$hash, $id]);
    
    echo "<tr>";
    echo "<td>{$id}</td>";
    echo "<td>{$partner['company_name']}</td>";
    echo "<td>{$partner['email']}</td>";
    echo "<td>{$pass}</td>";
    echo "<td style='color:green'>✓ Set</td>";
    echo "</tr>";
}

echo "</table>";
echo "<br><p><strong style='color:red'>⚠ DELETE THIS FILE IMMEDIATELY!</strong></p>";
echo "<p>Partners can now login at: <a href='" . url('portal/login') . "'>" . url('portal/login') . "</a></p>";
