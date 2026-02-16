<?php
/**
 * CYN Tourism - Authentication System
 * Secure authentication with session management and CSRF protection
 * 
 * @package CYN_Tourism
 * @version 2.0.0
 */

require_once dirname(__FILE__) . '/Database.php';
require_once dirname(__FILE__) . '/helpers.php';
require_once dirname(__FILE__) . '/Logger.php';

/**
 * Authentication class
 */
class Auth {
    
    private static $currentUser = null;
    private static $checked = false;
    
    /**
     * Check if user is authenticated
     * 
     * @return bool True if authenticated
     */
    public static function check() {
        // Return cached result
        if (self::$checked && self::$currentUser !== null) {
            return true;
        }
        
        // Check session variables
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['auth_time'])) {
            return false;
        }
        
        // Check session expiration
        if (time() - $_SESSION['auth_time'] > SESSION_LIFETIME) {
            self::logout();
            return false;
        }
        
        // Update last activity
        $_SESSION['auth_time'] = time();
        
        // Load user from database
        try {
            $user = Database::fetchOne(
                "SELECT id, first_name, last_name, email, password, role, status, 
                        profile_image, phone, last_login
                 FROM users WHERE id = ? AND status = 'active'",
                [$_SESSION['user_id']]
            );
            
            if (!$user) {
                self::logout();
                return false;
            }
            
            // Add full_name
            $user['full_name'] = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
            
            self::$currentUser = $user;
            self::$checked = true;
            
            return true;
            
        } catch (Exception $e) {
            Logger::error('Auth check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * Get current user
     * 
     * @return array|null User data or null
     */
    public static function user() {
        if (!self::check()) {
            return null;
        }
        return self::$currentUser;
    }
    
    /**
     * Get current user ID
     * 
     * @return int|null User ID or null
     */
    public static function id() {
        $user = self::user();
        return $user ? (int)$user['id'] : null;
    }
    
    /**
     * Get current user role
     * 
     * @return string|null User role or null
     */
    public static function role() {
        $user = self::user();
        return $user ? $user['role'] : null;
    }
    
    /**
     * Check if user is admin
     * 
     * @return bool True if admin
     */
    public static function isAdmin() {
        return self::role() === 'admin';
    }
    
    /**
     * Check if user is manager
     * 
     * @return bool True if manager
     */
    public static function isManager() {
        return in_array(self::role(), ['admin', 'manager']);
    }
    
    /**
     * Login user
     * 
     * @param string $email User email
     * @param string $password User password
     * @return array Login result
     */
    public static function login($email, $password) {
        // Validate input
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => __('field_required')];
        }
        
        // Check for brute force
        if (self::isLockedOut($email)) {
            Logger::security('Login attempt during lockout', ['email' => $email]);
            return ['success' => false, 'message' => __('account_locked')];
        }
        
        try {
            // Get user by email - use explicit columns to avoid issues with generated columns
            $user = Database::fetchOne(
                "SELECT id, first_name, last_name, email, password, role, status, 
                        profile_image, phone, last_login
                 FROM users WHERE email = ?",
                [$email]
            );
            
            // Check if user exists
            if (!$user) {
                self::recordFailedAttempt($email);
                Logger::auth('failed', null, ['email' => $email, 'reason' => 'user_not_found']);
                return ['success' => false, 'message' => __('login_failed')];
            }
            
            // Check password
            if (!password_verify($password, $user['password'])) {
                self::recordFailedAttempt($email);
                Logger::auth('failed', null, ['email' => $email, 'reason' => 'wrong_password']);
                return ['success' => false, 'message' => __('login_failed')];
            }
            
            // Check if user is active
            if ($user['status'] !== 'active') {
                Logger::auth('failed', $user['id'], ['reason' => 'inactive_account']);
                return ['success' => false, 'message' => 'Account is not active'];
            }
            
            // Clear failed attempts
            self::clearFailedAttempts($email);
            
            // Add full_name to user array
            $user['full_name'] = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
            
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['auth_time'] = time();
            $_SESSION['user_role'] = $user['role'];
            
            // Update last login (non-blocking - don't fail login if this fails)
            try {
                Database::execute(
                    "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?",
                    [$user['id']]
                );
                // Try login_count separately in case column doesn't exist
                Database::execute(
                    "UPDATE users SET login_count = IFNULL(login_count, 0) + 1 WHERE id = ?",
                    [$user['id']]
                );
            } catch (Exception $e) {
                // Non-critical - log but don't block login
                Logger::error('Login count update failed', ['error' => $e->getMessage()]);
            }
            
            self::$currentUser = $user;
            self::$checked = true;
            
            // Log success
            Logger::auth('login', $user['id']);
            
            return [
                'success' => true,
                'message' => 'Login successful',
                'user' => $user
            ];
            
        } catch (Exception $e) {
            Logger::error('Login error', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => __('login_failed')];
        }
    }
    
    /**
     * Logout user
     */
    public static function logout() {
        $userId = self::id();
        
        // Log logout
        if ($userId) {
            Logger::auth('logout', $userId);
        }
        
        // Clear session
        $_SESSION = [];
        
        // Destroy session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', [
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => SECURE_COOKIES,
                'httponly' => HTTP_ONLY_COOKIES,
                'samesite' => 'Strict'
            ]);
        }
        
        session_destroy();
        
        self::$currentUser = null;
        self::$checked = false;
    }
    
    /**
     * Require authentication
     * Redirects to login if not authenticated
     */
    public static function requireAuth() {
        if (!self::check()) {
            // Store intended URL
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            
            header('Location: ' . App::url('/login'));
            exit;
        }
    }
    
    /**
     * Require admin role
     */
    public static function requireAdmin() {
        self::requireAuth();
        
        if (!self::isAdmin()) {
            header('HTTP/1.1 403 Forbidden');
            header('Location: /errors/403.php');
            exit;
        }
    }
    
    /**
     * Require manager or admin role
     */
    public static function requireManager() {
        self::requireAuth();
        
        if (!self::isManager()) {
            header('HTTP/1.1 403 Forbidden');
            header('Location: /errors/403.php');
            exit;
        }
    }
    
    /**
     * Register new user
     * 
     * @param array $data User data
     * @return array Registration result
     */
    public static function register($data) {
        try {
            // Validate required fields
            $required = ['email', 'password', 'first_name', 'last_name'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return ['success' => false, 'message' => ucfirst($field) . ' is required'];
                }
            }
            
            // Validate email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Invalid email address'];
            }
            
            // Check if email exists
            $existing = Database::fetchOne(
                "SELECT id FROM users WHERE email = ?",
                [$data['email']]
            );
            
            if ($existing) {
                return ['success' => false, 'message' => 'Email already exists'];
            }
            
            // Validate password
            if (strlen($data['password']) < PASSWORD_MIN_LENGTH) {
                return [
                    'success' => false,
                    'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'
                ];
            }
            
            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Insert user
            $userId = Database::insert(
                "INSERT INTO users (email, password, first_name, last_name, role, status, created_at) 
                 VALUES (?, ?, ?, ?, ?, 'active', CURRENT_TIMESTAMP)",
                [
                    $data['email'],
                    $hashedPassword,
                    $data['first_name'],
                    $data['last_name'],
                    $data['role'] ?? 'staff'
                ]
            );
            
            Logger::activity('created', 'user', $userId);
            
            return [
                'success' => true,
                'message' => 'User registered successfully',
                'user_id' => $userId
            ];
            
        } catch (Exception $e) {
            Logger::error('Registration error', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => __('error_occurred')];
        }
    }
    
    /**
     * Change user password
     * 
     * @param int $userId User ID
     * @param string $currentPassword Current password
     * @param string $newPassword New password
     * @return array Result
     */
    public static function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // Get user
            $user = Database::fetchOne(
                "SELECT password FROM users WHERE id = ?",
                [$userId]
            );
            
            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }
            
            // Verify current password
            if (!password_verify($currentPassword, $user['password'])) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }
            
            // Validate new password
            if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
                return [
                    'success' => false,
                    'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'
                ];
            }
            
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            Database::execute(
                "UPDATE users SET password = ?, password_changed_at = CURRENT_TIMESTAMP WHERE id = ?",
                [$hashedPassword, $userId]
            );
            
            Logger::activity('password_changed', 'user', $userId);
            
            return ['success' => true, 'message' => 'Password changed successfully'];
            
        } catch (Exception $e) {
            Logger::error('Password change error', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => __('error_occurred')];
        }
    }
    
    /**
     * Check if user is locked out
     * 
     * @param string $email User email
     * @return bool True if locked out
     */
    private static function isLockedOut($email) {
        $key = 'login_attempts_' . md5($email);
        
        if (!isset($_SESSION[$key])) {
            return false;
        }
        
        $attempts = $_SESSION[$key];
        
        // Check if max attempts reached and lockout period not expired
        if (count($attempts) >= MAX_LOGIN_ATTEMPTS) {
            $lastAttempt = end($attempts);
            if (time() - $lastAttempt < LOCKOUT_DURATION) {
                return true;
            }
            
            // Clear old attempts
            self::clearFailedAttempts($email);
        }
        
        return false;
    }
    
    /**
     * Record failed login attempt
     * 
     * @param string $email User email
     */
    private static function recordFailedAttempt($email) {
        $key = 'login_attempts_' . md5($email);
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [];
        }
        
        $_SESSION[$key][] = time();
        
        // Keep only recent attempts
        $_SESSION[$key] = array_filter($_SESSION[$key], function($time) {
            return time() - $time < LOCKOUT_DURATION;
        });
    }
    
    /**
     * Clear failed login attempts
     * 
     * @param string $email User email
     */
    private static function clearFailedAttempts($email) {
        $key = 'login_attempts_' . md5($email);
        unset($_SESSION[$key]);
    }

    // ========================================
    // Partner Portal Authentication
    // ========================================

    private static $currentPartner = null;
    private static $partnerChecked = false;

    /**
     * Login a partner
     */
    public static function loginPartner($email, $password) {
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email and password are required'];
        }

        try {
            $partner = Database::fetchOne(
                "SELECT * FROM partners WHERE email = ? AND status = 'active'",
                [$email]
            );

            if (!$partner || empty($partner['password'])) {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }

            if (!password_verify($password, $partner['password'])) {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }

            $_SESSION['partner_id'] = $partner['id'];
            $_SESSION['partner_auth_time'] = time();

            self::$currentPartner = $partner;
            self::$partnerChecked = true;

            return ['success' => true, 'message' => 'Login successful', 'partner' => $partner];

        } catch (Exception $e) {
            Logger::error('Partner login error', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'An error occurred'];
        }
    }

    /**
     * Check if a partner is authenticated
     */
    public static function checkPartner() {
        if (self::$partnerChecked && self::$currentPartner !== null) {
            return true;
        }

        if (!isset($_SESSION['partner_id']) || !isset($_SESSION['partner_auth_time'])) {
            return false;
        }

        if (time() - $_SESSION['partner_auth_time'] > SESSION_LIFETIME) {
            self::logoutPartner();
            return false;
        }

        $_SESSION['partner_auth_time'] = time();

        try {
            $partner = Database::fetchOne(
                "SELECT * FROM partners WHERE id = ? AND status = 'active'",
                [$_SESSION['partner_id']]
            );

            if (!$partner) {
                self::logoutPartner();
                return false;
            }

            self::$currentPartner = $partner;
            self::$partnerChecked = true;
            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get current partner
     */
    public static function partner() {
        if (!self::checkPartner()) return null;
        return self::$currentPartner;
    }

    /**
     * Get current partner ID
     */
    public static function partnerId() {
        $p = self::partner();
        return $p ? (int)$p['id'] : null;
    }

    /**
     * Require partner authentication
     */
    public static function requirePartner() {
        if (!self::checkPartner()) {
            header('Location: ' . App::url('/portal/login'));
            exit;
        }
    }

    /**
     * Logout partner
     */
    public static function logoutPartner() {
        unset($_SESSION['partner_id'], $_SESSION['partner_auth_time']);
        self::$currentPartner = null;
        self::$partnerChecked = false;
    }
}

/**
 * CSRF Protection class
 */
class CSRF {
    
    /**
     * Get or generate CSRF token
     * 
     * @return string CSRF token
     */
    public static function token() {
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
    public static function validate($token = null) {
        $token = $token ?? ($_POST[CSRF_TOKEN_NAME] ?? '');
        $storedToken = $_SESSION[CSRF_TOKEN_NAME] ?? '';
        
        if (empty($token) || empty($storedToken)) {
            return false;
        }
        
        return hash_equals($storedToken, $token);
    }
    
    /**
     * Get CSRF token field HTML
     * 
     * @return string HTML input field
     */
    public static function field() {
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . self::token() . '">';
    }
    
    /**
     * Regenerate CSRF token
     */
    public static function regenerate() {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
}

/**
 * Input sanitization class
 */
class Input {
    
    /**
     * Sanitize string input
     * 
     * @param mixed $input Input value
     * @return string Sanitized value
     */
    public static function string($input) {
        if (is_array($input)) {
            return array_map([self::class, 'string'], $input);
        }
        return htmlspecialchars(trim($input ?? ''), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Sanitize email input
     * 
     * @param string $email Email address
     * @return string Sanitized email
     */
    public static function email($email) {
        return filter_var(trim($email ?? ''), FILTER_SANITIZE_EMAIL);
    }
    
    /**
     * Sanitize integer input
     * 
     * @param mixed $value Input value
     * @return int Integer value
     */
    public static function int($value) {
        return (int) filter_var($value, FILTER_VALIDATE_INT);
    }
    
    /**
     * Sanitize float input
     * 
     * @param mixed $value Input value
     * @return float Float value
     */
    public static function float($value) {
        return (float) filter_var($value, FILTER_VALIDATE_FLOAT);
    }
    
    /**
     * Sanitize URL input
     * 
     * @param string $url URL
     * @return string Sanitized URL
     */
    public static function url($url) {
        return filter_var(trim($url ?? ''), FILTER_SANITIZE_URL);
    }
    
    /**
     * Get input from POST
     * 
     * @param string $key Input key
     * @param mixed $default Default value
     * @return mixed Input value
     */
    public static function post($key, $default = null) {
        return isset($_POST[$key]) ? self::string($_POST[$key]) : $default;
    }
    
    /**
     * Get input from GET
     * 
     * @param string $key Input key
     * @param mixed $default Default value
     * @return mixed Input value
     */
    public static function get($key, $default = null) {
        return isset($_GET[$key]) ? self::string($_GET[$key]) : $default;
    }
}

/**
 * Flash message functions
 */

/**
 * Set or get flash message
 * 
 * @param string $key Message key
 * @param mixed $value Message value
 * @return mixed Message or null
 */
function flash($key, $value = null) {
    if ($value !== null) {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        $_SESSION['flash'][$key] = $value;
        return null;
    }
    
    $message = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $message;
}

/**
 * Check if flash message exists
 * 
 * @param string $key Message key
 * @return bool True if exists
 */
function hasFlash($key) {
    return isset($_SESSION['flash'][$key]);
}

/**
 * Redirect with flash message
 * 
 * @param string $url Redirect URL
 * @param string $message Flash message
 * @param string $type Message type
 */
function redirect($url, $message = '', $type = 'info') {
    if ($message) {
        flash($type, $message);
    }
    header("Location: {$url}");
    exit;
}

// Convenience functions (guarded to prevent redeclaration if functions.php loaded first)
if (!function_exists('csrf_token')) {
    function csrf_token() {
        return CSRF::token();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field() {
        return CSRF::field();
    }
}
