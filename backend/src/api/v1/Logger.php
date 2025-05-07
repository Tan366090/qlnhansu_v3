<?php
class Logger {
    private $logDir;
    private $logFile;
    private $logLevel;

    const LEVEL_DEBUG = 0;
    const LEVEL_INFO = 1;
    const LEVEL_WARN = 2;
    const LEVEL_ERROR = 3;

    public function __construct($logLevel = self::LEVEL_INFO) {
        $this->logLevel = $logLevel;
        $this->logDir = __DIR__ . '/../logs';
        $this->logFile = $this->logDir . '/api.log';
        
        if (!file_exists($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }

    public function debug($message, $context = []) {
        $this->log(self::LEVEL_DEBUG, $message, $context);
    }

    public function info($message, $context = []) {
        $this->log(self::LEVEL_INFO, $message, $context);
    }

    public function warn($message, $context = []) {
        $this->log(self::LEVEL_WARN, $message, $context);
    }

    public function error($message, $context = []) {
        $this->log(self::LEVEL_ERROR, $message, $context);
    }

    private function log($level, $message, $context = []) {
        if ($level < $this->logLevel) {
            return;
        }

        $levelStr = $this->getLevelString($level);
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? json_encode($context) : '';
        
        $logMessage = "[$timestamp] [$levelStr] $message $contextStr" . PHP_EOL;
        
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }

    private function getLevelString($level) {
        switch ($level) {
            case self::LEVEL_DEBUG:
                return 'DEBUG';
            case self::LEVEL_INFO:
                return 'INFO';
            case self::LEVEL_WARN:
                return 'WARN';
            case self::LEVEL_ERROR:
                return 'ERROR';
            default:
                return 'UNKNOWN';
        }
    }
} 