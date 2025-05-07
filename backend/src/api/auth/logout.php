<?php
// Enable error reporting but don't display errors
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/error.log');

// Required files
require_once __DIR__ . '/../middleware/CORSMiddleware.php';

// Handle CORS
CORSMiddleware::handleRequest();

header('Content-Type: application/json');

// Khởi tạo session với các tham số bảo mật
ini_set('session.cookie_path', '/QLNhanSu_version1/');
ini_set('session.cookie_domain', '');
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.cookie_samesite', 'Lax');

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Log the logout
    if (isset($_SESSION['username'])) {
        error_log("User " . $_SESSION['username'] . " logged out");
    }

    // Clear all session variables
    $_SESSION = array();

    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/QLNhanSu_version1/');
    }

    // Destroy the session
    session_destroy();

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Logout successful'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    
    // Log error
    error_log("Logout error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
}
?>
