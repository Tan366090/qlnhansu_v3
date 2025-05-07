<?php

namespace App\Core;

class Session {
    private static $instance = null;
    private $config;

    private function __construct() {
        $this->config = require __DIR__ . '/../../config/config.php';
        $this->initialize();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initialize() {
        // Set session save path
        if (isset($this->config['session']['save_path'])) {
            if (!is_dir($this->config['session']['save_path'])) {
                mkdir($this->config['session']['save_path'], 0755, true);
            }
            ini_set('session.save_path', $this->config['session']['save_path']);
        }

        // Set session name
        if (isset($this->config['session']['name'])) {
            session_name($this->config['session']['name']);
        }

        // Set session cookie parameters
        session_set_cookie_params(
            $this->config['session']['lifetime'],
            $this->config['session']['path'],
            $this->config['session']['domain'],
            $this->config['session']['secure'],
            $this->config['session']['httponly']
        );

        // Start session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Regenerate session ID periodically
        if (!isset($_SESSION['last_regeneration'])) {
            $this->regenerate();
        } else {
            $timeSinceLastRegeneration = time() - $_SESSION['last_regeneration'];
            if ($timeSinceLastRegeneration > 1800) { // 30 minutes
                $this->regenerate();
            }
        }
    }

    public function regenerate() {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }

    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public function destroy() {
        session_destroy();
        $_SESSION = array();
    }

    public function has($key) {
        return isset($_SESSION[$key]);
    }
} 