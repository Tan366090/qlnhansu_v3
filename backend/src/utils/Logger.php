<?php

class Logger {
    private $config;
    private $channels = [];

    public function __construct() {
        $this->config = require __DIR__ . '/../config/logger.php';
        $this->initializeChannels();
    }

    private function initializeChannels() {
        foreach ($this->config['channels'] as $name => $config) {
            $this->channels[$name] = $this->createChannel($config);
        }
    }

    private function createChannel($config) {
        switch ($config['driver']) {
            case 'file':
                return new FileLogger($config['path'], $config['level']);
            default:
                throw new SystemError("Unsupported logger driver: {$config['driver']}");
        }
    }

    public function log($level, $message, $context = [], $channel = null) {
        $channel = $channel ?? $this->config['default'];
        
        if (!isset($this->channels[$channel])) {
            throw new SystemError("Logger channel not found: $channel");
        }
        
        $this->channels[$channel]->log($level, $message, $context);
    }

    public function error($message, $context = [], $channel = null) {
        $this->log('error', $message, $context, $channel);
    }

    public function warning($message, $context = [], $channel = null) {
        $this->log('warning', $message, $context, $channel);
    }

    public function info($message, $context = [], $channel = null) {
        $this->log('info', $message, $context, $channel);
    }

    public function debug($message, $context = [], $channel = null) {
        $this->log('debug', $message, $context, $channel);
    }
}

class FileLogger {
    private $path;
    private $level;
    private $levels = [
        'debug' => 0,
        'info' => 1,
        'warning' => 2,
        'error' => 3,
    ];

    public function __construct($path, $level) {
        $this->path = $path;
        $this->level = $this->levels[strtolower($level)] ?? 0;
    }

    public function log($level, $message, $context = []) {
        $levelValue = $this->levels[strtolower($level)] ?? 0;
        
        if ($levelValue >= $this->level) {
            $logMessage = $this->formatMessage($level, $message, $context);
            $this->writeToFile($logMessage);
        }
    }

    private function formatMessage($level, $message, $context) {
        $timestamp = date('Y-m-d H:i:s');
        $formattedMessage = "[$timestamp] [$level] $message";
        
        if (!empty($context)) {
            $formattedMessage .= " " . json_encode($context);
        }
        
        return $formattedMessage . PHP_EOL;
    }

    private function writeToFile($message) {
        $dir = dirname($this->path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents($this->path, $message, FILE_APPEND);
    }
}

function logger($channel = 'app') {
    return new class($channel) {
        private $channel;
        private $logFile;
        
        public function __construct($channel) {
            $this->channel = $channel;
            $this->logFile = __DIR__ . '/../logs/' . date('Y-m-d') . '.log';
            
            // Create logs directory if it doesn't exist
            if (!file_exists(dirname($this->logFile))) {
                mkdir(dirname($this->logFile), 0777, true);
            }
        }
        
        public function info($message, $context = []) {
            $this->write('INFO', $message, $context);
        }
        
        public function warning($message, $context = []) {
            $this->write('WARNING', $message, $context);
        }
        
        public function error($message, $context = []) {
            $this->write('ERROR', $message, $context);
        }
        
        private function write($level, $message, $context) {
            $log = sprintf(
                "[%s] [%s] [%s] %s %s\n",
                date('Y-m-d H:i:s'),
                $level,
                $this->channel,
                $message,
                !empty($context) ? json_encode($context) : ''
            );
            
            file_put_contents($this->logFile, $log, FILE_APPEND);
        }
    };
}
?> 