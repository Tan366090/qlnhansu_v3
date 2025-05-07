<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1); // Bật hiển thị lỗi
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
ini_set('default_charset', 'UTF-8'); // Ensure UTF-8 encoding for Vietnamese text

// Set headers for JSON response and CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 3600');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Start session with proper configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => false, // Set to false for local development
        'cookie_samesite' => 'Lax', // Set to Lax for local development
        'gc_maxlifetime' => 3600, // 1 hour
        'cookie_lifetime' => 3600 // 1 hour
    ]);
}

// Initialize database connection
require_once '../config/database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

// Check database connection
try {
    $conn->query("SELECT 1");
    error_log("Database connection successful");
} catch (Exception $e) {
    error_log("Database connection failed: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'errors' => ['database' => $e->getMessage()]
    ]);
    exit();
}

// Get action from request
$action = $_GET['action'] ?? '';

// Helper function to send JSON response
function sendResponse($success, $message, $data = [], $errors = [], $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'errors' => $errors
    ]);
    exit();
}

require_once '../controllers/LoginController.php';

$loginController = new \Controllers\LoginController();

try {
    switch ($action) {
        case 'login':
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $loginController->authenticate($data);
            echo json_encode($result);
            break;

        case 'logout':
            session_start();
            session_destroy();
            echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
            break;

        case 'check':
            // Validate token
            $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
            if (empty($token)) {
                sendResponse(false, 'Authorization token is required', [], ['auth' => 'Authorization token is required'], 401);
            }
            
            try {
                $conn = $db->getConnection();
                
                // Get user by token
                $stmt = $conn->prepare("SELECT * FROM users WHERE remember_token = ? AND token_expires_at > NOW() AND status = 'active' LIMIT 1");
                $stmt->execute([$token]);
                $user = $stmt->fetch();
                
                if (!$user) {
                    sendResponse(false, 'Invalid or expired token', [], ['auth' => 'Invalid or expired token'], 401);
                }
                
                // Remove sensitive data
                unset($user['password']);
                unset($user['remember_token']);
                unset($user['token_expires_at']);
                
                sendResponse(true, 'Token is valid', ['user' => $user]);
            } catch (Exception $e) {
                sendResponse(false, 'Token validation failed', [], ['server' => $e->getMessage()], 500);
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Action not found']);
            break;
    }
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    sendResponse(false, 'Internal server error', [], ['server' => $e->getMessage()], 500);
}