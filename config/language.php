<?php
/**
 * CYN Tourism - Language Helper
 * Multi-language support system with proper error handling
 * 
 * @package CYN_Tourism
 * @version 1.1.0
 */

// Prevent direct access
if (!defined('APP_ROOT') && basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    header('HTTP/1.1 403 Forbidden');
    exit('Direct access to this file is not allowed.');
}

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Default language configuration
$defaultLang = 'tr';
$fallbackLang = 'en';

// Available languages with their display names
$availableLangs = [
    'en' => ['name' => 'English', 'flag' => '🇬🇧', 'dir' => 'ltr'],
    'tr' => ['name' => 'Türkçe', 'flag' => '🇹🇷', 'dir' => 'ltr'],
    'az' => ['name' => 'Azərbaycan', 'flag' => '🇦🇿', 'dir' => 'ltr'],
    'ar' => ['name' => 'العربية', 'flag' => '🇸🇦', 'dir' => 'rtl']
];

/**
 * Get language from session, cookie, browser, or use default
 * @return string Current language code
 */
function detectLanguage() {
    global $defaultLang, $availableLangs;
    
    // Check URL parameter first (highest priority)
    if (isset($_GET['lang']) && isset($availableLangs[$_GET['lang']])) {
        return $_GET['lang'];
    }
    
    // Check session
    if (isset($_SESSION['lang']) && isset($availableLangs[$_SESSION['lang']])) {
        return $_SESSION['lang'];
    }
    
    // Check cookie
    if (isset($_COOKIE['preferred_lang']) && isset($availableLangs[$_COOKIE['preferred_lang']])) {
        return $_COOKIE['preferred_lang'];
    }
    
    // Detect from browser
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $browserLangs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        foreach ($browserLangs as $lang) {
            $langCode = substr($lang, 0, 2);
            if (isset($availableLangs[$langCode])) {
                return $langCode;
            }
        }
    }
    
    return $defaultLang;
}

// Set current language
if (isset($_GET['lang']) && isset($availableLangs[$_GET['lang']])) {
    $currentLang = $_GET['lang'];
    $_SESSION['lang'] = $currentLang;
    // Set cookie for 30 days
    setcookie('preferred_lang', $currentLang, time() + (30 * 24 * 60 * 60), '/');
} else {
    $currentLang = detectLanguage();
    $_SESSION['lang'] = $currentLang;
}

// Language array
$lang = [];

/**
 * Load language file with fallback support
 * @param string $langCode Language code to load
 * @return array Language translations
 */
function loadLanguageFile($langCode) {
    global $fallbackLang;
    
    $langFile = __DIR__ . '/languages/' . $langCode . '.php';
    $fallbackFile = __DIR__ . '/languages/' . $fallbackLang . '.php';
    
    $translations = [];
    
    // Load fallback language first
    if (file_exists($fallbackFile)) {
        $fallbackTranslations = require $fallbackFile;
        if (is_array($fallbackTranslations)) {
            $translations = $fallbackTranslations;
        }
    }
    
    // Load requested language and merge (override fallback)
    if ($langCode !== $fallbackLang && file_exists($langFile)) {
        $langTranslations = require $langFile;
        if (is_array($langTranslations)) {
            $translations = array_merge($translations, $langTranslations);
        }
    }
    
    return $translations;
}

// Load language file
$lang = loadLanguageFile($currentLang);

/**
 * Get translated text with placeholder support
 * 
 * @param string $key Language key
 * @param array $params Optional parameters for placeholders (e.g., ['min' => 8])
 * @param string $default Default text if key not found
 * @return string Translated text
 * 
 * @example __('password_too_short', ['min' => 8]) // Returns "Password must be at least 8 characters"
 */
function __($key, $params = [], $default = null) {
    global $lang;
    
    // Get translation or use key/default
    $text = isset($lang[$key]) ? $lang[$key] : ($default !== null ? $default : $key);
    
    // Replace placeholders
    if (is_array($params) && !empty($params)) {
        foreach ($params as $placeholder => $value) {
            $text = str_replace(':' . $placeholder, htmlspecialchars($value, ENT_QUOTES, 'UTF-8'), $text);
        }
    }
    
    return $text;
}

/**
 * Get translated text and echo it directly
 * 
 * @param string $key Language key
 * @param array $params Optional parameters for placeholders
 * @param string $default Default text if key not found
 */
function _e($key, $params = [], $default = null) {
    echo __($key, $params, $default);
}

/**
 * Get current language code
 * @return string Current language code
 */
function getCurrentLang() {
    global $currentLang;
    return $currentLang;
}

/**
 * Get current language info
 * @return array Language info (name, flag, dir)
 */
function getCurrentLangInfo() {
    global $currentLang, $availableLangs;
    return isset($availableLangs[$currentLang]) ? $availableLangs[$currentLang] : $availableLangs['en'];
}

/**
 * Get available languages
 * @return array Available languages
 */
function getAvailableLanguages() {
    global $availableLangs;
    return $availableLangs;
}

/**
 * Check if translation key exists
 * @param string $key Language key
 * @return bool True if key exists
 */
function translationExists($key) {
    global $lang;
    return isset($lang[$key]);
}

/**
 * Get language switcher HTML
 * 
 * @param string $type Type of switcher ('dropdown', 'buttons', 'links')
 * @param array $options Additional options
 * @return string HTML for language switcher
 */
function getLanguageSwitcher($type = 'buttons', $options = []) {
    global $currentLang, $availableLangs;
    
    // Build URL preserving all query parameters except 'lang'
    $params = $_GET;
    unset($params['lang']);
    $queryString = !empty($params) ? '&' . http_build_query($params) : '';
    
    $html = '';
    
    switch ($type) {
        case 'dropdown':
            $html .= '<div class="language-switcher dropdown">';
            $html .= '<button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">';
            $html .= $availableLangs[$currentLang]['flag'] . ' ' . $availableLangs[$currentLang]['name'];
            $html .= '</button>';
            $html .= '<ul class="dropdown-menu">';
            foreach ($availableLangs as $langCode => $langInfo) {
                $active = $langCode === $currentLang ? 'active' : '';
                $html .= '<li><a class="dropdown-item ' . $active . '" href="?lang=' . $langCode . $queryString . '">';
                $html .= $langInfo['flag'] . ' ' . $langInfo['name'];
                $html .= '</a></li>';
            }
            $html .= '</ul></div>';
            break;
            
        case 'links':
            $html .= '<div class="language-switcher">';
            foreach ($availableLangs as $langCode => $langInfo) {
                $active = $langCode === $currentLang ? 'active' : '';
                $html .= '<a href="?lang=' . $langCode . $queryString . '" class="lang-link ' . $active . '">';
                $html .= $langInfo['flag'] . ' ' . $langInfo['name'];
                $html .= '</a>';
            }
            $html .= '</div>';
            break;
            
        case 'buttons':
        default:
            $html .= '<div class="language-switcher">';
            foreach ($availableLangs as $langCode => $langInfo) {
                $active = $langCode === $currentLang ? 'active' : '';
                $label = strtoupper($langCode);
                $html .= '<a href="?lang=' . $langCode . $queryString . '" class="lang-btn ' . $active . '">' . $label . '</a>';
            }
            $html .= '</div>';
            break;
    }
    
    return $html;
}

/**
 * Set language for current session
 * @param string $langCode Language code
 * @return bool True if successful
 */
function setLanguage($langCode) {
    global $availableLangs;
    
    if (!isset($availableLangs[$langCode])) {
        return false;
    }
    
    $_SESSION['lang'] = $langCode;
    setcookie('preferred_lang', $langCode, time() + (30 * 24 * 60 * 60), '/');
    
    return true;
}
