<?php

namespace App\Core;

class Logger {
    private static $instance = null;
    private $logFile;
    private $logLevels = [
        'debug' => 0,
        'info' => 1,
        'warning' => 2,
        'error' => 3,
        'critical' => 4
    ];
    private $currentLogLevel;

    private function __construct() {
        $this->logFile = __DIR__ . '/../../logs/app.log';
        $this->currentLogLevel = getenv('LOG_LEVEL') ?: 'info';
        
        if (!is_dir(dirname($this->logFile))) {
            mkdir(dirname($this->logFile), 0777, true);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function debug($message, array $context = []) {
        $this->log('debug', $message, $context);
    }

    public function info($message, array $context = []) {
        $this->log('info', $message, $context);
    }

    public function warning($message, array $context = []) {
        $this->log('warning', $message, $context);
    }

    public function error($message, array $context = []) {
        $this->log('error', $message, $context);
    }

    public function critical($message, array $context = []) {
        $this->log('critical', $message, $context);
    }

    private function log($level, $message, array $context = []) {
        if ($this->logLevels[$level] < $this->logLevels[$this->currentLogLevel]) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $logMessage = sprintf(
            "[%s] [%s] %s %s\n",
            $timestamp,
            strtoupper($level),
            $message,
            !empty($context) ? json_encode($context) : ''
        );

        error_log($logMessage, 3, $this->logFile);
    }

    public function setLogLevel($level) {
        if (isset($this->logLevels[$level])) {
            $this->currentLogLevel = $level;
        }
    }

    public function getLogLevel() {
        return $this->currentLogLevel;
    }
} 