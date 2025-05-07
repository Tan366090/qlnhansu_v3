<?php
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

// Enable CORS with specific origins
$allowedOrigins = [
    'http://localhost:3000', // Frontend development
    'http://127.0.0.1:3000', // Alternative localhost
    'https://yourdomain.com' // Production domain
];

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Credentials: true');
}

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Rate limiting
require_once 'RateLimiter.php';
$rateLimiter = new RateLimiter(100, 60); // 100 requests per minute

// Session handling
session_start();

// Tăng cường bảo mật cho session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.gc_maxlifetime', 1800); // 30 phút

if (!isset($_SESSION['last_activity'])) {
    $_SESSION['last_activity'] = time();
}

// Check session timeout (30 minutes)
if (time() - $_SESSION['last_activity'] > 1800) {
    session_unset();
    session_destroy();
    http_response_code(401);
    echo json_encode(['error' => 'Session expired']);
    exit();
}

$_SESSION['last_activity'] = time();

// Input validation and sanitization
function validateInput($data) {
    if (empty($data)) {
        return false;
    }
    return true;
}

// Error logging
function logError($type, $message, $stackTrace = null) {
    $logFile = __DIR__ . '/../logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [$type] $message";
    if ($stackTrace) {
        $logMessage .= "\nStack trace:\n$stackTrace";
    }
    $logMessage .= "\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Input sanitization
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

// Response caching
function getCachedResponse($key) {
    $cacheFile = __DIR__ . "/../cache/$key.json";
    if (file_exists($cacheFile)) {
        $data = json_decode(file_get_contents($cacheFile), true);
        if ($data['timestamp'] > time() - 300) { // 5 minutes cache
            return $data['response'];
        }
    }
    return null;
}

function cacheResponse($key, $response) {
    $cacheDir = __DIR__ . '/../cache';
    if (!file_exists($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    $cacheFile = "$cacheDir/$key.json";
    $data = [
        'timestamp' => time(),
        'response' => $response
    ];
    file_put_contents($cacheFile, json_encode($data));
}

// Database connection with error handling
function getDatabaseConnection() {
    static $conn = null;
    if ($conn === null) {
        try {
            $conn = new PDO(
                "mysql:host=localhost;dbname=your_database",
                "your_username",
                "your_password",
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            logError('Database', $e->getMessage(), $e->getTraceAsString());
            http_response_code(500);
            echo json_encode(['error' => 'Database connection failed']);
            exit();
        }
    }
    return $conn;
}

// Kiểm tra quyền truy cập
function checkAccess($requiredRole) {
    if (!isset($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized access']);
        exit();
    }

    $userRole = $_SESSION['user']['role_name'];
    
    // Kiểm tra quyền truy cập
    if ($userRole !== $requiredRole) {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied']);
        exit();
    }
}

// Handle API requests
try {
    // Check rate limit
    if (!$rateLimiter->canMakeRequest()) {
        http_response_code(429);
        echo json_encode(['error' => 'Too many requests']);
        exit();
    }

    $requestMethod = $_SERVER['REQUEST_METHOD'];
    $requestUri = $_SERVER['REQUEST_URI'];
    $requestData = json_decode(file_get_contents('php://input'), true);

    // Sanitize request data
    if ($requestData) {
        $requestData = sanitizeInput($requestData);
    }

    // Check cache for GET requests
    if ($requestMethod === 'GET') {
        $cacheKey = md5($requestUri);
        $cachedResponse = getCachedResponse($cacheKey);
        if ($cachedResponse) {
            echo json_encode($cachedResponse);
            exit();
        }
    }

    // Process request
    $response = processRequest($requestMethod, $requestUri, $requestData);

    // Cache GET responses
    if ($requestMethod === 'GET') {
        cacheResponse($cacheKey, $response);
    }

    echo json_encode($response);
} catch (Exception $e) {
    logError('API', $e->getMessage(), $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}

// Process request based on method and URI
function processRequest($method, $uri, $data) {
    $db = getDatabaseConnection();
    
    switch ($method) {
        case 'GET':
            // Handle GET requests
            break;
        case 'POST':
            // Handle POST requests
            break;
        case 'PUT':
            // Handle PUT requests
            break;
        case 'DELETE':
            // Handle DELETE requests
            break;
        default:
            throw new Exception('Method not allowed');
    }
}