<?php
/**
 * CYN Tourism Management System - Central Configuration
 * 
 * This file contains all global configuration settings for the application.
 * Modify these settings according to your environment.
 * 
 * @package CYN_Tourism
 * @version 2.0.0
 */

// Prevent direct access
if (!defined('APP_ROOT') && basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    header('HTTP/1.1 403 Forbidden');
    exit('Direct access to this file is not allowed.');
}

// Define application root
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

// =============================================================================
// ERROR REPORTING & DEBUGGING
// =============================================================================

// Debug mode - set to false in production
define('DEBUG_MODE', true);

// Log database queries
define('LOG_QUERIES', false);
define('LOG_ENABLED', true);

// Error reporting configuration
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
}

ini_set('log_errors', 1);
define('LOG_PATH', APP_ROOT . '/logs/');
ini_set('error_log', LOG_PATH . 'php-errors.log');

// =============================================================================
// DATABASE CONFIGURATION
// =============================================================================

// MySQL configuration (uncomment and set DB_DRIVER to 'mysql' to use MySQL)
// SWITCHED TO SQLITE FOR LOCAL TESTING — restore 'mysql' for production
define('DB_DRIVER', 'mysql');
define('DB_HOST', 'localhost');
define('DB_NAME', 'barqvkxs_cyn');
define('DB_USER', 'barqvkxs_cyn');
define('DB_PASS', 'tW@{cFk&^ep]');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', 'utf8mb4_unicode_ci');
// =============================================================================
// COMPANY INFORMATION
// =============================================================================

define('COMPANY_NAME', 'CYN TURIZM');
define('COMPANY_ADDRESS', 'MOLLA GURANI MAH. OGUSHAN CAD. KARAKOYUNLU SOK. NO: 2 D: 4 FINDIKZADE / FATIH');
define('COMPANY_PHONE', '+90 5318176770');
define('COMPANY_EMAIL', 'info@cyntourism.com');
define('COMPANY_LOGO', 'logo.png');
define('COMPANY_WEBSITE', 'https://cyntourism.com');
define('TURSAB_LICENSE', '');

// =============================================================================
// SECURITY SETTINGS
// =============================================================================

define('SESSION_NAME', 'CYN_SESSION');
define('SESSION_LIFETIME', 7200); // 2 hours
define('SESSION_REGENERATE_ID', true); // Regenerate session ID periodically
define('CSRF_TOKEN_NAME', 'cyn_csrf_token');
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_REQUIRE_SPECIAL', true);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 900); // 15 minutes
define('SECURE_COOKIES', false); // Set to true for HTTPS
define('HTTP_ONLY_COOKIES', true);

// =============================================================================
// CURRENCY SETTINGS
// =============================================================================

define('DEFAULT_CURRENCY', 'USD');
define('AVAILABLE_CURRENCIES', json_encode(['USD', 'EUR', 'TRY', 'DZD']));

// =============================================================================
// DATE/TIME SETTINGS
// =============================================================================

define('TIMEZONE', 'Europe/Istanbul');
define('DATE_FORMAT', 'd/m/Y');
define('DATETIME_FORMAT', 'd/m/Y H:i');
define('DB_DATE_FORMAT', 'Y-m-d');
define('DB_DATETIME_FORMAT', 'Y-m-d H:i:s');

// Set timezone
date_default_timezone_set(TIMEZONE);

// =============================================================================
// FILE UPLOAD SETTINGS
// =============================================================================

define('UPLOAD_DIR', APP_ROOT . '/uploads/');
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('UPLOAD_ALLOWED_TYPES', json_encode([
    'image' => ['jpg', 'jpeg', 'png', 'gif', 'svg'],
    'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'],
    'archive' => ['zip', 'rar']
]));
define('UPLOAD_IMAGE_MAX_WIDTH', 1920);
define('UPLOAD_IMAGE_MAX_HEIGHT', 1080);
define('UPLOAD_THUMB_WIDTH', 150);
define('UPLOAD_THUMB_HEIGHT', 150);

// =============================================================================
// MAINTENANCE MODE
// =============================================================================

define('MAINTENANCE_MODE', false);
define('MAINTENANCE_ALLOW_ADMIN', true); // Allow admin access during maintenance
define('MAINTENANCE_MESSAGE', 'We are currently performing scheduled maintenance. Please check back later.');
define('MAINTENANCE_IP_WHITELIST', json_encode([])); // IPs allowed during maintenance

// Check maintenance mode (skip for error pages and admin if allowed)
if (MAINTENANCE_MODE) {
    $currentFile = basename($_SERVER['PHP_SELF']);
    $isErrorPage = in_array($currentFile, ['404.php', '403.php', '500.php', 'maintenance.php']);
    $isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    $isWhitelisted = in_array($_SERVER['REMOTE_ADDR'], json_decode(MAINTENANCE_IP_WHITELIST, true) ?: []);
    
    if (!$isErrorPage && !($isAdmin && MAINTENANCE_ALLOW_ADMIN) && !$isWhitelisted) {
        header('Location: /errors/maintenance.php');
        exit;
    }
}

// =============================================================================
// BACKUP SETTINGS
// =============================================================================

define('BACKUP_DIR', APP_ROOT . '/backups/');
define('BACKUP_RETENTION_DAYS', 30);
define('AUTO_BACKUP_ENABLED', false);
define('AUTO_BACKUP_FREQUENCY', 'daily'); // daily, weekly, monthly
define('BACKUP_INCLUDE_FILES', true);
define('BACKUP_MAX_FILES', 10);

// =============================================================================
// EMAIL SETTINGS (SMTP)
// =============================================================================

define('SMTP_ENABLED', false);
define('SMTP_HOST', '');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_ENCRYPTION', 'tls'); // tls, ssl, or none
define('SMTP_FROM_EMAIL', COMPANY_EMAIL);
define('SMTP_FROM_NAME', COMPANY_NAME);
define('SMTP_TIMEOUT', 30);

// =============================================================================
// PAGINATION SETTINGS
// =============================================================================

define('DEFAULT_ITEMS_PER_PAGE', 20);
define('PAGINATION_RANGE', 2); // Number of pages to show on each side of current page
define('MAX_ITEMS_PER_PAGE', 100);

// =============================================================================
// NOTIFICATION SETTINGS
// =============================================================================

define('NOTIFICATION_RETENTION_DAYS', 30);
define('ENABLE_BROWSER_NOTIFICATIONS', true);
define('NOTIFICATION_CHECK_INTERVAL', 60); // seconds

// =============================================================================
// API SETTINGS
// =============================================================================

define('API_ENABLED', false);
define('API_RATE_LIMIT', 100); // requests per hour
define('API_KEY_REQUIRED', true);

// =============================================================================
// SESSION MANAGEMENT
// =============================================================================

if (session_status() === PHP_SESSION_NONE) {
    // Session configuration
    ini_set('session.name', SESSION_NAME);
    ini_set('session.cookie_lifetime', SESSION_LIFETIME);
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    ini_set('session.cookie_httponly', HTTP_ONLY_COOKIES ? 1 : 0);
    ini_set('session.cookie_secure', SECURE_COOKIES ? 1 : 0);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_only_cookies', 1);
    
    session_name(SESSION_NAME);
    session_start();
    
    // Regenerate session ID periodically for security
    if (SESSION_REGENERATE_ID && !isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    }
    
    if (SESSION_REGENERATE_ID && isset($_SESSION['last_regeneration'])) {
        if (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
}

// =============================================================================
// LOAD REQUIRED FILES
// =============================================================================

// Autoloader for classes
spl_autoload_register(function ($class) {
    $file = APP_ROOT . '/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// =============================================================================
// GLOBAL FUNCTIONS
// =============================================================================

/**
 * Get application version
 * @return string Application version
 */
function getAppVersion() {
    return '2.0.0';
}

/**
 * Check if application is in production mode
 * @return bool True if in production
 */
function isProduction() {
    return !DEBUG_MODE;
}

/**
 * Get base URL
 * @return string Base URL
 */
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    return $protocol . '://' . $host;
}

/**
 * Get current URL
 * @return string Current URL
 */
function getCurrentUrl() {
    return getBaseUrl() . $_SERVER['REQUEST_URI'];
}

/**
 * Generate unique ID
 * @param string $prefix ID prefix
 * @return string Unique ID
 */
function generateUniqueId($prefix = '') {
    return $prefix . uniqid() . bin2hex(random_bytes(4));
}
