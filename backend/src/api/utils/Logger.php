<?php
namespace App\Utils;

class Logger {
    private static $logDir;
    private static $logFile;
    
    public static function init() {
        self::$logDir = __DIR__ . '/../../logs/';
        if (!file_exists(self::$logDir)) {
            mkdir(self::$logDir, 0777, true);
        }
        self::$logFile = self::$logDir . date('Y-m-d') . '.log';
    }
    
    public static function log($level, $message, $context = []) {
        if (!isset(self::$logFile)) {
            self::init();
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$level] $message";
        
        if (!empty($context)) {
            $logMessage .= " " . json_encode($context);
        }
        
        $logMessage .= PHP_EOL;
        
        file_put_contents(self::$logFile, $logMessage, FILE_APPEND);
    }
    
    public static function error($message, $context = []) {
        self::log('ERROR', $message, $context);
    }
    
    public static function info($message, $context = []) {
        self::log('INFO', $message, $context);
    }
    
    public static function debug($message, $context = []) {
        self::log('DEBUG', $message, $context);
    }
    
    public static function warning($message, $context = []) {
        self::log('WARNING', $message, $context);
    }
    
    public static function audit($action, $userId, $details = []) {
        $context = [
            'user_id' => $userId,
            'action' => $action,
            'details' => $details
        ];
        self::log('AUDIT', "User action: $action", $context);
    }
} 