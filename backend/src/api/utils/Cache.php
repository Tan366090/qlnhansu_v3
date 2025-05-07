<?php
namespace App\Utils;

class Cache {
    private static $cacheDir;
    private static $defaultTTL = 3600; // 1 hour
    
    public static function init() {
        self::$cacheDir = __DIR__ . '/../../cache/';
        if (!file_exists(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0777, true);
        }
    }
    
    public static function set($key, $value, $ttl = null) {
        if (!isset(self::$cacheDir)) {
            self::init();
        }
        
        $ttl = $ttl ?? self::$defaultTTL;
        $cacheFile = self::$cacheDir . md5($key) . '.cache';
        $data = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
        
        file_put_contents($cacheFile, serialize($data));
    }
    
    public static function get($key) {
        if (!isset(self::$cacheDir)) {
            self::init();
        }
        
        $cacheFile = self::$cacheDir . md5($key) . '.cache';
        
        if (!file_exists($cacheFile)) {
            return null;
        }
        
        $data = unserialize(file_get_contents($cacheFile));
        
        if ($data['expires'] < time()) {
            unlink($cacheFile);
            return null;
        }
        
        return $data['value'];
    }
    
    public static function delete($key) {
        if (!isset(self::$cacheDir)) {
            self::init();
        }
        
        $cacheFile = self::$cacheDir . md5($key) . '.cache';
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }
    
    public static function clear() {
        if (!isset(self::$cacheDir)) {
            self::init();
        }
        
        $files = glob(self::$cacheDir . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }
    
    public static function remember($key, $ttl, $callback) {
        $value = self::get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        $value = $callback();
        self::set($key, $value, $ttl);
        
        return $value;
    }
} 