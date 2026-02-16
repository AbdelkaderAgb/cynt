<?php
/**
 * CYN Tourism - Helper Functions
 * Comprehensive utility functions for the tourism management system
 * 
 * @package CYN_Tourism
 * @version 2.0.0
 */

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/src/Core/Logger.php';

// =============================================================================
// FORMATTING FUNCTIONS
// =============================================================================

/**
 * Format currency amount with symbol
 * 
 * @param float $amount Amount to format
 * @param string $currency Currency code (USD, EUR, TRY, DZD)
 * @return string Formatted currency string
 */
function format_currency($amount, $currency = 'USD') {
    $symbols = [
        'USD' => '$',
        'EUR' => '€',
        'TRY' => '₺',
        'DZD' => 'DZD'
    ];
    
    $symbol = isset($symbols[$currency]) ? $symbols[$currency] : $currency;
    return $symbol . number_format((float)$amount, 2);
}

/**
 * Format date according to system settings
 * 
 * @param string $date Date string
 * @param string $format Output format
 * @return string Formatted date
 */
function format_date($date, $format = DATE_FORMAT) {
    if (empty($date) || $date === '0000-00-00') {
        return '-';
    }
    
    $timestamp = strtotime($date);
    return $timestamp ? date($format, $timestamp) : $date;
}

/**
 * Format datetime
 * 
 * @param string $datetime Datetime string
 * @return string Formatted datetime
 */
function format_datetime($datetime) {
    return format_date($datetime, DATETIME_FORMAT);
}

/**
 * Format time ago (human readable)
 * 
 * @param string $datetime Datetime string
 * @return string Time ago string
 */
function time_ago($datetime) {
    if (empty($datetime)) {
        return '-';
    }
    
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    $currentLang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';
    $isTurkish = $currentLang === 'tr';
    
    if ($diff < 60) {
        return $isTurkish ? 'Az önce' : 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $isTurkish ? $mins . ' dakika önce' : $mins . ' minutes ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $isTurkish ? $hours . ' saat önce' : $hours . ' hours ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $isTurkish ? $days . ' gün önce' : $days . ' days ago';
    } elseif ($diff < 2592000) {
        $weeks = floor($diff / 604800);
        return $isTurkish ? $weeks . ' hafta önce' : $weeks . ' weeks ago';
    } else {
        return format_date($datetime);
    }
}

/**
 * Format file size
 * 
 * @param int $bytes Bytes
 * @return string Formatted size
 */
function format_file_size($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $unitIndex = 0;
    
    while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
        $bytes /= 1024;
        $unitIndex++;
    }
    
    return round($bytes, 2) . ' ' . $units[$unitIndex];
}

/**
 * Format phone number
 * 
 * @param string $phone Phone number
 * @return string Formatted phone
 */
function format_phone($phone) {
    if (empty($phone)) {
        return '';
    }
    
    // Remove all non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Format Turkish phone numbers
    if (strlen($phone) == 10) {
        return sprintf('(%s) %s %s %s',
            substr($phone, 0, 3),
            substr($phone, 3, 3),
            substr($phone, 6, 2),
            substr($phone, 8, 2)
        );
    }
    
    return $phone;
}

// =============================================================================
// AVATAR & IMAGE FUNCTIONS
// =============================================================================

/**
 * Generate initials avatar HTML
 * 
 * @param string $name Full name
 * @param int $size Avatar size in pixels
 * @return string HTML for avatar
 */
function get_initials_avatar($name, $size = 60) {
    $name = trim($name);
    
    if (empty($name)) {
        $name = 'User';
    }
    
    $words = explode(' ', $name);
    $initials = '';
    
    // Get first letter of first word
    $initials .= isset($words[0][0]) ? strtoupper($words[0][0]) : '';
    
    // Get first letter of second word if exists
    $initials .= isset($words[1][0]) ? strtoupper($words[1][0]) : '';
    
    // If only one word, get second letter
    if (strlen($initials) == 1 && isset($words[0][1])) {
        $initials .= strtoupper($words[0][1]);
    }
    
    // Fallback
    $initials = $initials ?: 'U';
    
    // Generate consistent color based on name
    $colors = [
        '#6366f1', '#8b5cf6', '#ec4899', '#f43f5e', 
        '#f97316', '#eab308', '#22c55e', '#06b6d4',
        '#3b82f6', '#14b8a6', '#f59e0b', '#ef4444'
    ];
    $colorIndex = array_sum(array_map('ord', str_split($name))) % count($colors);
    $bgColor = $colors[$colorIndex];
    
    return sprintf(
        '<div class="avatar-initials" style="width:%dpx;height:%dpx;background:%s;display:flex;align-items:center;justify-content:center;border-radius:50%%;color:white;font-weight:600;font-size:%dpx;text-transform:uppercase;">%s</div>',
        $size,
        $size,
        $bgColor,
        (int)($size * 0.4),
        htmlspecialchars($initials)
    );
}

// =============================================================================
// VOUCHER & INVOICE FUNCTIONS
// =============================================================================

/**
 * Generate unique voucher number
 * 
 * @return string Voucher number
 */
function generate_voucher_no() {
    $year = date('Y');
    $month = date('m');
    
    try {
        $last = Database::fetchOne(
            "SELECT voucher_no FROM vouchers WHERE voucher_no LIKE ? ORDER BY id DESC LIMIT 1",
            ["VC-{$year}{$month}-%"]
        );
        
        $nextNumber = 1;
        if ($last && isset($last['voucher_no'])) {
            $parts = explode('-', $last['voucher_no']);
            $lastNum = (int) end($parts);
            $nextNumber = $lastNum + 1;
        }
        
        return sprintf("VC-%s%s-%04d", $year, $month, $nextNumber);
    } catch (Exception $e) {
        Logger::error('Failed to generate voucher number', ['error' => $e->getMessage()]);
        return 'VC-' . date('Ymd') . '-' . uniqid();
    }
}

/**
 * Generate invoice number
 * 
 * @return string Invoice number
 */
function generate_invoice_no() {
    $year = date('Y');
    $month = date('m');
    
    try {
        $last = Database::fetchOne(
            "SELECT invoice_no FROM invoices WHERE invoice_no LIKE ? ORDER BY id DESC LIMIT 1",
            ["INV-{$year}{$month}-%"]
        );
        
        $nextNumber = 1;
        if ($last && isset($last['invoice_no'])) {
            $parts = explode('-', $last['invoice_no']);
            $lastNum = (int) end($parts);
            $nextNumber = $lastNum + 1;
        }
        
        return sprintf("INV-%s%s-%04d", $year, $month, $nextNumber);
    } catch (Exception $e) {
        Logger::error('Failed to generate invoice number', ['error' => $e->getMessage()]);
        return 'INV-' . date('Ymd') . '-' . uniqid();
    }
}

/**
 * Calculate nights between two dates
 * 
 * @param string $checkIn Check-in date
 * @param string $checkOut Check-out date
 * @return int Number of nights
 */
function calculate_nights($checkIn, $checkOut) {
    try {
        $start = new DateTime($checkIn);
        $end = new DateTime($checkOut);
        $interval = $start->diff($end);
        return max(0, $interval->days);
    } catch (Exception $e) {
        return 0;
    }
}

// =============================================================================
// DATE CONVERSION FUNCTIONS
// =============================================================================

/**
 * Convert date from display format to database format
 * 
 * @param string $date Date in display format
 * @return string Date in database format (Y-m-d)
 */
function convert_date_format($date) {
    if (empty($date)) {
        return null;
    }
    
    // Remove any non-numeric characters except slashes and dashes
    $date = preg_replace('/[^0-9\/\-]/', '', $date);
    
    // Try to parse dd/mm/yyyy format
    if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $date, $matches)) {
        $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
        $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
        $year = $matches[3];
        return "{$year}-{$month}-{$day}";
    }
    
    // If already in YYYY-MM-DD format, return as is
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return $date;
    }
    
    // Fallback: try strtotime
    $timestamp = strtotime($date);
    return $timestamp ? date('Y-m-d', $timestamp) : null;
}

/**
 * Convert date from database format to display format
 * 
 * @param string $date Date in database format
 * @return string Date in display format
 */
function display_date($date) {
    if (empty($date) || $date === '0000-00-00') {
        return '';
    }
    
    $timestamp = strtotime($date);
    return $timestamp ? date(DATE_FORMAT, $timestamp) : $date;
}

// =============================================================================
// PAGINATION FUNCTIONS
// =============================================================================

/**
 * Calculate pagination data
 * 
 * @param int $total Total number of items
 * @param int $page Current page number
 * @param int $perPage Items per page
 * @return array Pagination data
 */
function paginate($total, $page = 1, $perPage = null) {
    $perPage = $perPage ?: DEFAULT_ITEMS_PER_PAGE;
    $perPage = min($perPage, MAX_ITEMS_PER_PAGE);
    
    $totalPages = (int) ceil($total / $perPage);
    $page = max(1, min((int)$page, max(1, $totalPages)));
    $offset = ($page - 1) * $perPage;
    
    return [
        'total' => (int)$total,
        'page' => $page,
        'per_page' => $perPage,
        'total_pages' => $totalPages,
        'offset' => $offset,
        'has_more' => $page < $totalPages,
        'has_previous' => $page > 1,
        'start' => $total > 0 ? $offset + 1 : 0,
        'end' => min($offset + $perPage, $total)
    ];
}

/**
 * Render pagination HTML
 * 
 * @param array $pagination Pagination data from paginate()
 * @param string $url Base URL
 * @param array $params Additional URL parameters
 * @return string Pagination HTML
 */
function render_pagination($pagination, $url, $params = []) {
    if ($pagination['total_pages'] <= 1) {
        return '';
    }
    
    $queryString = !empty($params) ? '&' . http_build_query($params) : '';
    $range = PAGINATION_RANGE;
    
    $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
    
    // Previous button
    $prevDisabled = $pagination['page'] <= 1 ? ' disabled' : '';
    $prevUrl = $pagination['page'] > 1 ? $url . '?page=' . ($pagination['page'] - 1) . $queryString : '#';
    $html .= '<li class="page-item' . $prevDisabled . '">';
    $html .= '<a class="page-link" href="' . $prevUrl . '" aria-label="Previous">';
    $html .= '<span aria-hidden="true">&laquo;</span></a></li>';
    
    // First page + ellipsis
    if ($pagination['page'] > $range + 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=1' . $queryString . '">1</a></li>';
        if ($pagination['page'] > $range + 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    // Page numbers
    $start = max(1, $pagination['page'] - $range);
    $end = min($pagination['total_pages'], $pagination['page'] + $range);
    
    for ($i = $start; $i <= $end; $i++) {
        $active = $i === $pagination['page'] ? ' active' : '';
        $pageUrl = $url . '?page=' . $i . $queryString;
        $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . $pageUrl . '">' . $i . '</a></li>';
    }
    
    // Last page + ellipsis
    if ($pagination['page'] < $pagination['total_pages'] - $range) {
        if ($pagination['page'] < $pagination['total_pages'] - $range - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $html .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . $pagination['total_pages'] . $queryString . '">' . $pagination['total_pages'] . '</a></li>';
    }
    
    // Next button
    $nextDisabled = $pagination['page'] >= $pagination['total_pages'] ? ' disabled' : '';
    $nextUrl = $pagination['page'] < $pagination['total_pages'] ? $url . '?page=' . ($pagination['page'] + 1) . $queryString : '#';
    $html .= '<li class="page-item' . $nextDisabled . '">';
    $html .= '<a class="page-link" href="' . $nextUrl . '" aria-label="Next">';
    $html .= '<span aria-hidden="true">&raquo;</span></a></li>';
    
    $html .= '</ul></nav>';
    
    // Pagination info
    $html .= '<div class="text-center text-muted small mt-2">';
    $html .= sprintf(
        __('showing') . ' %d ' . __('to') . ' %d ' . __('of') . ' %d ' . __('entries'),
        $pagination['start'],
        $pagination['end'],
        $pagination['total']
    );
    $html .= '</div>';
    
    return $html;
}

// =============================================================================
// DASHBOARD STATISTICS
// =============================================================================

/**
 * Get dashboard statistics
 * 
 * @return array Dashboard statistics
 */
function get_dashboard_stats() {
    $stats = [];
    
    try {
        // Today's transfers
        $today = Database::fetchOne(
            "SELECT COUNT(*) as count FROM vouchers WHERE pickup_date = CURDATE() OR return_date = CURDATE()"
        );
        $stats['today_transfers'] = (int) ($today['count'] ?? 0);
        
        // Monthly vouchers
        $monthVouchers = Database::fetchOne(
            "SELECT COUNT(*) as count FROM vouchers WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())"
        );
        $stats['month_vouchers'] = (int) ($monthVouchers['count'] ?? 0);
        
        // Pending invoices
        $pendingInvoices = Database::fetchOne(
            "SELECT COUNT(*) as count FROM invoices WHERE status = 'pending'"
        );
        $stats['pending_invoices'] = (int) ($pendingInvoices['count'] ?? 0);
        
        // Monthly revenue
        $revenue = Database::fetchOne(
            "SELECT SUM(total_amount) as total FROM invoices WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) AND status = 'paid'"
        );
        $stats['month_revenue'] = (float) ($revenue['total'] ?? 0);
        
        // Total partners
        $partners = Database::fetchOne("SELECT COUNT(*) as count FROM partners WHERE status = 'active'");
        $stats['total_partners'] = (int) ($partners['count'] ?? 0);
        
        // Total vehicles (vehicles table uses 'available' not 'active')
        $vehicles = Database::fetchOne("SELECT COUNT(*) as count FROM vehicles WHERE status = 'available'");
        $stats['total_vehicles'] = (int) ($vehicles['count'] ?? 0);
        
        // Total drivers
        $drivers = Database::fetchOne("SELECT COUNT(*) as count FROM drivers WHERE status = 'active'");
        $stats['total_drivers'] = (int) ($drivers['count'] ?? 0);
        
    } catch (Exception $e) {
        Logger::error('Failed to get dashboard stats', ['error' => $e->getMessage()]);
    }
    
    return $stats;
}

// =============================================================================
// NOTIFICATION FUNCTIONS
// =============================================================================

/**
 * Get notification count for current user
 * 
 * @return int Notification count
 */
function get_notification_count() {
    if (!isset($_SESSION['user_id'])) {
        return 0;
    }
    
    try {
        $result = Database::fetchOne(
            "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0",
            [$_SESSION['user_id']]
        );
        return (int) ($result['count'] ?? 0);
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Add notification
 * 
 * @param int $userId User ID
 * @param string $title Notification title
 * @param string $message Notification message
 * @param string $type Notification type (info, success, warning, error)
 * @return bool True if successful
 */
function add_notification($userId, $title, $message, $type = 'info') {
    try {
        // Use explicit next ID to work even without AUTO_INCREMENT
        $nextId = (int)Database::fetchColumn("SELECT COALESCE(MAX(id), 0) + 1 FROM notifications");
        Database::execute(
            "INSERT INTO notifications (id, user_id, title, message, type, created_at) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)",
            [$nextId, $userId, $title, $message, $type]
        );
        return true;
    } catch (Exception $e) {
        Logger::error('Failed to add notification', ['error' => $e->getMessage()]);
        return false;
    }
}

// =============================================================================
// UTILITY FUNCTIONS
// =============================================================================

/**
 * Generate random string
 * 
 * @param int $length String length
 * @return string Random string
 */
function generate_random_string($length = 10) {
    return bin2hex(random_bytes(ceil($length / 2)));
}

/**
 * Sanitize filename
 * 
 * @param string $filename Original filename
 * @return string Sanitized filename
 */
function sanitize_filename($filename) {
    // Remove any path components
    $filename = basename($filename);
    
    // Remove any non-alphanumeric characters except dots, dashes, and underscores
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    
    // Remove multiple consecutive dots
    $filename = preg_replace('/\.+/', '.', $filename);
    
    // Ensure filename doesn't start with a dot
    $filename = ltrim($filename, '.');
    
    return strtolower($filename);
}

/**
 * Truncate text
 * 
 * @param string $text Text to truncate
 * @param int $length Maximum length
 * @param string $suffix Suffix to add
 * @return string Truncated text
 */
function truncate_text($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length - strlen($suffix)) . $suffix;
}

/**
 * Get client IP address
 * 
 * @return string IP address
 */
function get_client_ip() {
    $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $ips = explode(',', $_SERVER[$key]);
            $ip = trim($ips[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    
    return '0.0.0.0';
}

/**
 * Check if string is JSON
 * 
 * @param string $string String to check
 * @return bool True if valid JSON
 */
function is_json($string) {
    if (!is_string($string)) {
        return false;
    }
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}

/**
 * Safe JSON encode
 * 
 * @param mixed $data Data to encode
 * @return string JSON string
 */
function safe_json_encode($data) {
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

/**
 * Safe JSON decode
 * 
 * @param string $json JSON string
 * @param bool $assoc Return as array
 * @return mixed Decoded data
 */
function safe_json_decode($json, $assoc = true) {
    if (!is_string($json)) {
        return $assoc ? [] : new stdClass();
    }
    
    $result = json_decode($json, $assoc);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return $assoc ? [] : new stdClass();
    }
    
    return $result;
}

// =============================================================================
// SECURITY FUNCTIONS
// =============================================================================

/**
 * Generate CSRF token
 * 
 * @return string CSRF token
 */
function generate_csrf_token() {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Validate CSRF token
 * 
 * @param string $token Token to validate
 * @return bool True if valid
 */
function validate_csrf_token($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Get CSRF token field HTML
 * 
 * @return string HTML input field
 */
function csrf_field() {
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . generate_csrf_token() . '">';
}

/**
 * Escape HTML entities
 * 
 * @param string $text Text to escape
 * @return string Escaped text
 */
function e($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Clean input data
 * 
 * @param mixed $data Data to clean
 * @return mixed Cleaned data
 */
function clean_input($data) {
    if (is_array($data)) {
        return array_map('clean_input', $data);
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    
    return $data;
}

// =============================================================================
// MVC HELPER FUNCTIONS
// =============================================================================

/**
 * Generate a URL for the application
 * Shorthand for App::url()
 */
function url(string $path = '/'): string
{
    return App::url($path);
}

/**
 * Generate an asset URL (relative to public/assets/)
 */
function asset(string $path): string
{
    return App::url('assets/' . ltrim($path, '/'));
}

/**
 * Check if the current route matches a given path
 */
function is_active(string $path): bool
{
    $currentUri = '/' . trim($_GET['url'] ?? '', '/');
    return $currentUri === '/' . trim($path, '/');
}

/**
 * Get active class string for navigation
 */
function active_class(string $path, string $class = 'bg-slate-800 text-white'): string
{
    return is_active($path) ? $class : '';
}
