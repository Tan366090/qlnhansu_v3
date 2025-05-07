<?php
namespace App\Config;

class SessionManager {
    private static $instance = null;
    private static $config;
    private static $initialized = false;

    private function __construct() {
        self::$config = require_once __DIR__ . '/session.php';
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init() {
        if (self::$initialized) {
            return;
        }

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

        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Initialize session if needed
        if (!isset($_SESSION['initialized'])) {
            $_SESSION['initialized'] = true;
            $_SESSION['last_activity'] = time();
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        }

        // Validate session
        if (!$this->validateSession()) {
            $this->destroy();
            return false;
        }

        self::$initialized = true;
        return true;
    }

    private function validateSession() {
        // Check if session is initialized
        if (!isset($_SESSION['initialized'])) {
            return false;
        }

        // Check session timeout
        if (isset($_SESSION['last_activity'])) {
            $inactive = time() - $_SESSION['last_activity'];
            
            // Session expires after 24 hours
            if ($inactive >= self::$config['gc_maxlifetime']) {
                $this->destroy();
                return false;
            }
            
            // Check if we need to regenerate session ID (every 30 minutes)
            if ($inactive > 1800) {
                session_regenerate_id(true);
                $_SESSION['last_activity'] = time();
            }
        }

        // Check IP address if enabled
        if (self::$config['validation']['check_ip'] && 
            isset($_SESSION['ip_address']) && 
            $_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
            $this->destroy();
            return false;
        }

        // Check user agent if enabled
        if (self::$config['validation']['check_user_agent'] && 
            isset($_SESSION['user_agent']) && 
            $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
            $this->destroy();
            return false;
        }

        return true;
    }

    public function checkSessionTimeout() {
        if (isset($_SESSION['last_activity'])) {
            $inactive = time() - $_SESSION['last_activity'];
            if ($inactive >= self::$config['gc_maxlifetime']) {
                $this->destroy();
                return false;
            }
            $_SESSION['last_activity'] = time();
        }
        return true;
    }

    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public function get($key) {
        return $_SESSION[$key] ?? null;
    }

    public function destroy() {
        // Unset all session variables
        $_SESSION = array();

        // Destroy the session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        // Destroy the session
        session_destroy();
    }

    public function isAuthenticated() {
        return isset($_SESSION['user_id']) && 
               isset($_SESSION['username']) && 
               isset($_SESSION['role']) &&
               $this->checkSessionTimeout();
    }

    public function setUserData($userData) {
        $_SESSION['user_id'] = $userData['user_id'];
        $_SESSION['username'] = $userData['username'];
        $_SESSION['role'] = $userData['role'];
        $_SESSION['full_name'] = $userData['full_name'] ?? null;
        $_SESSION['email'] = $userData['email'] ?? null;
        $_SESSION['last_activity'] = time();
    }

    public function getCurrentUser() {
        if (!$this->isAuthenticated()) {
            return null;
        }

        return [
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role'],
            'full_name' => $_SESSION['full_name'] ?? null,
            'email' => $_SESSION['email'] ?? null
        ];
    }
} 