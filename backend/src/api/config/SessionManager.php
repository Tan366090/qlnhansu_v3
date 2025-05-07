<?php
namespace App\Config;

use Exception;

class SessionManager {
    private static $instance = null;
    private $encryptionKey;
    private $sessionConfig;
    private $isInitialized = false;

    private function __construct() {
        $this->sessionConfig = require_once __DIR__ . '/../../config/session.php';
        $this->encryptionKey = $this->sessionConfig['encryption_key'];
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init() {
        if ($this->isInitialized) {
            return;
        }

        // Set session configuration
        ini_set('session.gc_maxlifetime', $this->sessionConfig['gc_maxlifetime']);
        ini_set('session.gc_probability', $this->sessionConfig['gc_probability']);
        ini_set('session.gc_divisor', $this->sessionConfig['gc_divisor']);
        ini_set('session.use_strict_mode', $this->sessionConfig['use_strict_mode']);
        ini_set('session.use_only_cookies', $this->sessionConfig['use_only_cookies']);
        ini_set('session.cookie_httponly', $this->sessionConfig['cookie_httponly']);
        ini_set('session.cookie_secure', $this->sessionConfig['cookie_secure']);
        ini_set('session.cookie_samesite', $this->sessionConfig['cookie_samesite']);

        // Set session cookie parameters
        session_set_cookie_params([
            'lifetime' => $this->sessionConfig['cookie_lifetime'],
            'path' => $this->sessionConfig['cookie_path'],
            'domain' => $this->sessionConfig['cookie_domain'],
            'secure' => $this->sessionConfig['cookie_secure'],
            'httponly' => $this->sessionConfig['cookie_httponly'],
            'samesite' => $this->sessionConfig['cookie_samesite']
        ]);

        // Set session name
        session_name($this->sessionConfig['name']);

        // Start session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Validate session
        $this->validateSession();

        $this->isInitialized = true;
    }

    private function validateSession() {
        // Check session timeout
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $this->sessionConfig['gc_maxlifetime'])) {
            $this->destroy();
            throw new Exception('Session expired');
        }

        // Check IP address
        if (isset($_SESSION['ip_address']) && $_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
            $this->destroy();
            throw new Exception('Session IP mismatch');
        }

        // Check user agent
        if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
            $this->destroy();
            throw new Exception('Session user agent mismatch');
        }

        // Update last activity
        $_SESSION['last_activity'] = time();
    }

    public function set($key, $value) {
        if (!$this->isInitialized) {
            $this->init();
        }

        // Encrypt sensitive data
        if (in_array($key, $this->sessionConfig['encrypted_keys'])) {
            $value = $this->encrypt($value);
        }

        $_SESSION[$key] = $value;
    }

    public function get($key) {
        if (!$this->isInitialized) {
            $this->init();
        }

        if (!isset($_SESSION[$key])) {
            return null;
        }

        $value = $_SESSION[$key];

        // Decrypt sensitive data
        if (in_array($key, $this->sessionConfig['encrypted_keys'])) {
            $value = $this->decrypt($value);
        }

        return $value;
    }

    public function destroy() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            // Unset all session variables
            $_SESSION = array();

            // Destroy the session cookie
            if (isset($_COOKIE[session_name()])) {
                setcookie(
                    session_name(),
                    '',
                    time() - 3600,
                    $this->sessionConfig['cookie_path'],
                    $this->sessionConfig['cookie_domain'],
                    $this->sessionConfig['cookie_secure'],
                    $this->sessionConfig['cookie_httponly']
                );
            }

            // Destroy the session
            session_destroy();
        }
        $this->isInitialized = false;
    }

    private function encrypt($data) {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt(
            $data,
            'aes-256-cbc',
            $this->encryptionKey,
            0,
            $iv
        );
        return base64_encode($iv . $encrypted);
    }

    private function decrypt($data) {
        $data = base64_decode($data);
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);
        return openssl_decrypt(
            $encrypted,
            'aes-256-cbc',
            $this->encryptionKey,
            0,
            $iv
        );
    }

    public function regenerate() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public function isAuthenticated() {
        if (!$this->isInitialized) {
            $this->init();
        }

        return isset($_SESSION['user_id']) && 
               isset($_SESSION['username']) && 
               isset($_SESSION['role']);
    }

    public function getCurrentUser() {
        if ($this->isAuthenticated()) {
            return [
                'user_id' => $this->get('user_id'),
                'username' => $this->get('username'),
                'role' => $this->get('role'),
                'email' => $this->get('email')
            ];
        }
        return null;
    }

    public function setUser($userData) {
        $this->set('user_id', $userData['id']);
        $this->set('username', $userData['username']);
        $this->set('role', $userData['role']);
        $this->set('email', $userData['email'] ?? null);
        $this->set('last_activity', time());
        $this->set('ip_address', $_SERVER['REMOTE_ADDR']);
        $this->set('user_agent', $_SERVER['HTTP_USER_AGENT']);
        $this->regenerate();
    }
} 