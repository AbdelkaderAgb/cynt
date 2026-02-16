<?php
/**
 * CYN Tourism - Logger Class
 * Simple logging utility for the application
 * 
 * @package CYN_Tourism
 * @version 2.0.0
 */

class Logger {
    
    private static $logDir = null;
    
    /**
     * Get the log directory path
     * @return string
     */
    private static function getLogDir() {
        if (self::$logDir === null) {
            self::$logDir = defined('APP_ROOT') ? APP_ROOT . '/logs' : __DIR__ . '/logs';
        }
        return self::$logDir;
    }
    
    /**
     * Ensure log directory exists
     */
    private static function ensureLogDir() {
        $dir = self::getLogDir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }
    
    /**
     * Write a log entry
     * 
     * @param string $level Log level
     * @param string $message Log message
     * @param array $context Additional context
     */
    private static function log($level, $message, $context = []) {
        self::ensureLogDir();
        
        $logFile = self::getLogDir() . '/app-' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $logLine = "[{$timestamp}] [{$level}] {$message}{$contextStr}" . PHP_EOL;
        
        @file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Log an error message
     * 
     * @param string $message
     * @param array $context
     */
    public static function error($message, $context = []) {
        self::log('ERROR', $message, $context);
    }
    
    /**
     * Log an info message
     * 
     * @param string $message
     * @param array $context
     */
    public static function info($message, $context = []) {
        self::log('INFO', $message, $context);
    }
    
    /**
     * Log a warning message
     * 
     * @param string $message
     * @param array $context
     */
    public static function warning($message, $context = []) {
        self::log('WARNING', $message, $context);
    }
    
    /**
     * Log a debug message
     * 
     * @param string $message
     * @param array $context
     */
    public static function debug($message, $context = []) {
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            self::log('DEBUG', $message, $context);
        }
    }
    
    /**
     * Log an authentication event
     * 
     * @param string $action Action performed (login, logout, failed)
     * @param int|null $userId User ID
     * @param array $context Additional context
     */
    public static function auth($action, $userId = null, $context = []) {
        $context['user_id'] = $userId;
        $context['ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $context['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        self::log('AUTH', "Auth action: {$action}", $context);
    }
    
    /**
     * Log a security event
     * 
     * @param string $message
     * @param array $context
     */
    public static function security($message, $context = []) {
        $context['ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        self::log('SECURITY', $message, $context);
    }
    
    /**
     * Log an activity event
     * 
     * @param string $action Action performed
     * @param string $entity Entity type
     * @param int|null $entityId Entity ID
     * @param array $context Additional context
     */
    public static function activity($action, $entity, $entityId = null, $context = []) {
        $context['entity'] = $entity;
        $context['entity_id'] = $entityId;
        self::log('ACTIVITY', "Activity: {$action} on {$entity}", $context);
    }
}
