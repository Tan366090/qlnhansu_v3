<?php

use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use App\Models\Project;
use App\Models\Task;
use App\Models\Performance;
use App\Models\Leave;
use App\Models\Attendance;
use App\Models\Certificate;
use App\Models\Skill;
use App\Models\Experience;
use App\Models\Document;
use App\Models\Training;
use App\Models\Notification;
use App\Models\User;

return [
    // Application paths
    'paths' => [
        'app' => __DIR__ . '/../app',
        'api' => __DIR__ . '/../api',
        'config' => __DIR__,
        'database' => __DIR__ . '/../database',
        'routes' => __DIR__ . '/../routes',
        'public' => __DIR__ . '/../../public',
        'temp' => __DIR__ . '/../../temp',
        'uploads' => __DIR__ . '/../../uploads',
        'logs' => __DIR__ . '/../../logs'
    ],

    // Logging configuration
    'logging' => [
        'error' => __DIR__ . '/../../logs/error.log',
        'access' => __DIR__ . '/../../logs/access.log',
        'upload' => __DIR__ . '/../../logs/upload.log'
    ],

    // Security settings
    'security' => [
        'jwt_secret' => getenv('JWT_SECRET', 'your-secret-key'),
        'jwt_expiration' => 3600, // 1 hour
        'rate_limit' => [
            'requests' => 100,
            'period' => 60 // seconds
        ]
    ],

    // File upload settings
    'upload' => [
        'max_size' => 10485760, // 10MB
        'allowed_types' => [
            'image/jpeg',
            'image/png',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ],
        'temp_dir' => __DIR__ . '/../../temp',
        'upload_dir' => __DIR__ . '/../../uploads'
    ],

    // Cache settings
    'cache' => [
        'driver' => 'file',
        'path' => __DIR__ . '/../../temp/cache',
        'ttl' => 3600 // 1 hour
    ],

    // Session settings
    'session' => [
        'name' => 'qlnhansu_session',
        'lifetime' => 7200, // 2 hours
        'path' => '/',
        'domain' => null,
        'secure' => true,
        'httponly' => true,
        'save_handler' => 'files',
        'save_path' => __DIR__ . '/../../temp/sessions',
        'gc_maxlifetime' => 7200,
        'gc_probability' => 1,
        'gc_divisor' => 100
    ],

    // CORS settings
    'cors' => [
        'allowed_origins' => ['*'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization'],
        'max_age' => 86400 // 24 hours
    ]
];

// JWT Configuration
define('JWT_SECRET', getenv('JWT_SECRET') ?: 'your-secret-key-here');
define('JWT_EXPIRE', 86400); // 24 hours
define('JWT_REFRESH_EXPIRE', 604800); // 7 days

// Security Configuration
define('PASSWORD_COST', 12); // For password_hash()
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 minutes
define('SESSION_LIFETIME', 3600); // 1 hour

// Rate Limiting
define('RATE_LIMIT_REQUESTS', 100);
define('RATE_LIMIT_WINDOW', 3600); // 1 hour

// Logging Configuration
define('LOG_PATH', __DIR__ . '/../logs/');
define('ERROR_LOG', LOG_PATH . 'error.log');
define('ACCESS_LOG', LOG_PATH . 'access.log');
define('DEBUG_LOG', LOG_PATH . 'debug.log');

// Ensure log directory exists
if (!file_exists(LOG_PATH)) {
    mkdir(LOG_PATH, 0755, true);
}

// Custom error handler
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $error_message = date('[Y-m-d H:i:s]') . " Error: [$errno] $errstr in $errfile on line $errline\n";
    error_log($error_message, 3, ERROR_LOG);
    
    if (defined('DISPLAY_ERRORS') && DISPLAY_ERRORS) {
        return false; // Let PHP handle the error as well
    }
    return true; // Suppress PHP's error handling
}

// Custom exception handler
function customExceptionHandler($exception) {
    $error_message = date('[Y-m-d H:i:s]') . " Exception: " . $exception->getMessage() . 
                    " in " . $exception->getFile() . 
                    " on line " . $exception->getLine() . "\n";
    error_log($error_message, 3, ERROR_LOG);
    
    http_response_code(500);
    if (defined('DISPLAY_ERRORS') && DISPLAY_ERRORS) {
        echo json_encode([
            'success' => false,
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine()
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Internal Server Error'
        ]);
    }
}

// Set error handlers
set_error_handler('customErrorHandler');
set_exception_handler('customExceptionHandler');

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
header('Content-Security-Policy: default-src \'self\'');

// Environment specific settings
define('DISPLAY_ERRORS', getenv('DISPLAY_ERRORS') === 'true');
ini_set('display_errors', DISPLAY_ERRORS ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', ERROR_LOG);

// Rate limiting function
function checkRateLimit($ip, $endpoint) {
    $conn = getDBConnection();
    $time = time();
    $window = $time - RATE_LIMIT_WINDOW;
    
    // Clean old records
    $stmt = $conn->prepare("DELETE FROM rate_limits WHERE timestamp < ?");
    $stmt->bind_param("i", $window);
    $stmt->execute();
    
    // Count requests
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM rate_limits WHERE ip = ? AND endpoint = ? AND timestamp > ?");
    $stmt->bind_param("ssi", $ip, $endpoint, $window);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result['count'] >= RATE_LIMIT_REQUESTS) {
        throw new Exception("Rate limit exceeded", 429);
    }
    
    // Log new request
    $stmt = $conn->prepare("INSERT INTO rate_limits (ip, endpoint, timestamp) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $ip, $endpoint, $time);
    $stmt->execute();
}

// Base URL configuration
define('BASE_URL', 'http://localhost/qlnhansu_V2');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS

// Timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// File upload configuration
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);

// API configuration
define('API_VERSION', 'v1');
define('API_RATE_LIMIT', 100); // requests per minute

// CSRF Configuration
define('CSRF_ENABLED', getenv('CSRF_ENABLED') !== 'false');
define('CSRF_TOKEN_NAME', '_csrf');
define('CSRF_HEADER_NAME', 'X-CSRF-Token');
define('CSRF_COOKIE_NAME', 'XSRF-TOKEN');
define('CSRF_COOKIE_LIFETIME', 7200); // 2 hours
define('CSRF_COOKIE_PATH', '/');
define('CSRF_COOKIE_DOMAIN', null);
define('CSRF_COOKIE_SECURE', false); // Set to true in production
define('CSRF_COOKIE_HTTPONLY', true);
?> 