<?php

class SessionHelper {
    private static $config;
    private static $initialized = false;
    
    public static function init() {
        if (self::$initialized) {
            return;
        }

        // Load session configuration
        self::$config = require_once __DIR__ . '/../../config/session.php';
        
        // Only set session settings if session is not already active
        if (session_status() === PHP_SESSION_NONE) {
            // Set session configuration
            ini_set('session.gc_maxlifetime', self::$config['gc_maxlifetime']);
            ini_set('session.gc_probability', self::$config['gc_probability']);
            ini_set('session.gc_divisor', self::$config['gc_divisor']);
            ini_set('session.use_strict_mode', self::$config['use_strict_mode']);
            ini_set('session.use_only_cookies', self::$config['use_only_cookies']);
            
            // Set session cookie parameters
            session_set_cookie_params([
                'lifetime' => self::$config['cookie_lifetime'],
                'path' => self::$config['cookie_path'],
                'domain' => self::$config['cookie_domain'],
                'secure' => self::$config['cookie_secure'],
                'httponly' => self::$config['cookie_httponly'],
                'samesite' => self::$config['cookie_samesite']
            ]);
            
            // Set session name
            session_name(self::$config['name']);
            
            // Start session
            session_start();
        }
        
        self::$initialized = true;
    }

    public static function validate() {
        if (session_status() === PHP_SESSION_NONE) {
            self::init();
        }

        // Check if session exists
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        // Check session timeout
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > self::$config['gc_maxlifetime'])) {
            self::destroy();
            return false;
        }

        // Check IP address if enabled in config
        if (self::$config['validation']['check_ip'] && 
            isset($_SESSION['ip_address']) && 
            $_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
            self::destroy();
            return false;
        }

        // Check user agent if enabled in config
        if (self::$config['validation']['check_user_agent'] && 
            isset($_SESSION['user_agent']) && 
            $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
            self::destroy();
            return false;
        }

        // Update last activity
        $_SESSION['last_activity'] = time();
        return true;
    }

    public static function destroy() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            // Unset all session variables
            $_SESSION = array();

            // Destroy the session cookie
            if (isset($_COOKIE[session_name()])) {
                setcookie(
                    session_name(),
                    '',
                    time() - 3600,
                    self::$config['cookie_path'],
                    self::$config['cookie_domain'],
                    self::$config['cookie_secure'],
                    self::$config['cookie_httponly']
                );
            }

            // Destroy the session
            session_destroy();
        }
        self::$initialized = false;
    }

    public static function set($key, $value) {
        if (session_status() === PHP_SESSION_NONE) {
            self::init();
        }
        $_SESSION[$key] = $value;
    }

    public static function get($key) {
        if (session_status() === PHP_SESSION_NONE) {
            self::init();
        }
        return $_SESSION[$key] ?? null;
    }

    public static function isAuthenticated() {
        return true; // Always return true to bypass authentication
    }

    public static function getCurrentUser() {
        return [
            'user_id' => 1,
            'username' => 'admin',
            'role' => 'admin',
            'email' => 'admin@example.com'
        ];
    }

    public static function setUser($userData) {
        // No need to set user data since we're bypassing authentication
    }

    public static function requireAuth() {
        // No need to check authentication
    }

    public static function requireRole($roles) {
        self::requireAuth();
        $currentUser = self::getCurrentUser();
        if (!in_array($currentUser['role'], (array)$roles)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Access denied'
            ]);
            exit();
        }
    }
} 