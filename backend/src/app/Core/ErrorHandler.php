<?php

namespace App\Core;

use Exception;
use ErrorException;
use Throwable;

class ErrorHandler {
    private static $instance = null;
    private $logFile;
    private $displayErrors;
    private $errorMessages = [
        E_ERROR => 'Fatal Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parse Error',
        E_NOTICE => 'Notice',
        E_CORE_ERROR => 'Core Error',
        E_CORE_WARNING => 'Core Warning',
        E_COMPILE_ERROR => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
        E_STRICT => 'Strict Standards',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_DEPRECATED => 'Deprecated',
        E_USER_DEPRECATED => 'User Deprecated'
    ];

    private function __construct() {
        $this->logFile = __DIR__ . '/../../logs/error.log';
        $this->displayErrors = getenv('APP_ENV') !== 'production';
        
        // Set error handlers
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function handleError($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $errorType = $this->errorMessages[$errno] ?? 'Unknown Error';
        $message = "[$errorType] $errstr in $errfile on line $errline";

        $this->logError($message);
        
        if ($this->displayErrors) {
            $this->displayError($message);
        } else {
            $this->displayUserFriendlyError();
        }

        return true;
    }

    public function handleException(Throwable $exception) {
        $message = sprintf(
            "[Exception] %s in %s on line %d\nStack trace:\n%s",
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );

        $this->logError($message);
        
        if ($this->displayErrors) {
            $this->displayError($message);
        } else {
            $this->displayUserFriendlyError();
        }
    }

    public function handleShutdown() {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $message = sprintf(
                "[Fatal Error] %s in %s on line %d",
                $error['message'],
                $error['file'],
                $error['line']
            );

            $this->logError($message);
            
            if ($this->displayErrors) {
                $this->displayError($message);
            } else {
                $this->displayUserFriendlyError();
            }
        }
    }

    private function logError($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message\n";
        
        if (!is_dir(dirname($this->logFile))) {
            mkdir(dirname($this->logFile), 0777, true);
        }
        
        error_log($logMessage, 3, $this->logFile);
    }

    private function displayError($message) {
        if (php_sapi_name() === 'cli') {
            echo $message . PHP_EOL;
        } else {
            echo '<div style="background-color: #f8d7da; color: #721c24; padding: 15px; margin: 10px; border: 1px solid #f5c6cb; border-radius: 4px;">';
            echo '<h3>Error Details:</h3>';
            echo '<pre>' . htmlspecialchars($message) . '</pre>';
            echo '</div>';
        }
    }

    private function displayUserFriendlyError() {
        if (php_sapi_name() === 'cli') {
            echo "An error occurred. Please check the error log for details.\n";
        } else {
            echo '<div style="text-align: center; padding: 50px;">';
            echo '<h1 style="color: #721c24;">Oops! Something went wrong</h1>';
            echo '<p>We apologize for the inconvenience. Our team has been notified and is working to fix the issue.</p>';
            echo '<p>Please try again later or contact support if the problem persists.</p>';
            echo '</div>';
        }
    }
} 