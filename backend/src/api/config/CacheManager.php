<?php
namespace App\Config;

use Exception;
use Redis;

class CacheManager {
    private static $instance = null;
    private $redis;
    private $config;
    private $isConnected = false;

    private function __construct() {
        $this->config = require_once __DIR__ . '/../../config/cache.php';
        $this->connect();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect() {
        try {
            $this->redis = new Redis();
            $this->redis->connect(
                $this->config['redis']['host'],
                $this->config['redis']['port'],
                $this->config['redis']['timeout']
            );
            
            if (!empty($this->config['redis']['password'])) {
                $this->redis->auth($this->config['redis']['password']);
            }
            
            $this->redis->select($this->config['redis']['database']);
            $this->isConnected = true;
        } catch (Exception $e) {
            error_log("Redis connection failed: " . $e->getMessage());
            $this->isConnected = false;
        }
    }

    public function get($key) {
        if (!$this->isConnected) {
            return null;
        }

        try {
            $value = $this->redis->get($key);
            return $value ? json_decode($value, true) : null;
        } catch (Exception $e) {
            error_log("Cache get failed: " . $e->getMessage());
            return null;
        }
    }

    public function set($key, $value, $ttl = null) {
        if (!$this->isConnected) {
            return false;
        }

        try {
            $value = json_encode($value);
            if ($ttl === null) {
                $ttl = $this->config['default_ttl'];
            }
            return $this->redis->setex($key, $ttl, $value);
        } catch (Exception $e) {
            error_log("Cache set failed: " . $e->getMessage());
            return false;
        }
    }

    public function delete($key) {
        if (!$this->isConnected) {
            return false;
        }

        try {
            return $this->redis->del($key);
        } catch (Exception $e) {
            error_log("Cache delete failed: " . $e->getMessage());
            return false;
        }
    }

    public function clear() {
        if (!$this->isConnected) {
            return false;
        }

        try {
            return $this->redis->flushDB();
        } catch (Exception $e) {
            error_log("Cache clear failed: " . $e->getMessage());
            return false;
        }
    }

    public function getOrSet($key, $callback, $ttl = null) {
        $value = $this->get($key);
        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);
        return $value;
    }

    public function increment($key, $value = 1) {
        if (!$this->isConnected) {
            return false;
        }

        try {
            return $this->redis->incrBy($key, $value);
        } catch (Exception $e) {
            error_log("Cache increment failed: " . $e->getMessage());
            return false;
        }
    }

    public function decrement($key, $value = 1) {
        if (!$this->isConnected) {
            return false;
        }

        try {
            return $this->redis->decrBy($key, $value);
        } catch (Exception $e) {
            error_log("Cache decrement failed: " . $e->getMessage());
            return false;
        }
    }

    public function __destruct() {
        if ($this->isConnected) {
            $this->redis->close();
        }
    }
} 