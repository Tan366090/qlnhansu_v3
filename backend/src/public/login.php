<?php
// Disable error display in output
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Set JSON content type first
header('Content-Type: application/json; charset=utf-8');

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Start session
session_start();

try {
    // Get JSON data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data) {
        throw new Exception('Invalid request format');
    }

    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    // Validate input
    if (empty($username) || empty($password)) {
        throw new Exception('Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu');
    }

    // Database connection
    require_once __DIR__ . '/../backend/src/config/Database.php';
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Get user information
    $query = "SELECT u.*, r.role_name 
              FROM users u 
              JOIN roles r ON u.role_id = r.role_id 
              WHERE u.username = :username AND u.is_active = 1";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception('Tên đăng nhập hoặc mật khẩu không đúng');
    }

    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        throw new Exception('Tên đăng nhập hoặc mật khẩu không đúng');
    }

    // Set session variables
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role_name'];
    $_SESSION['full_name'] = $user['full_name'];

    // Get redirect URL based on role
    $redirectUrl = '/QLNhanSu/backend/src/public/';
    switch ($user['role_id']) {
        case 1: // Admin
            $redirectUrl = '/QLNhanSu/backend/src/public/admin/dashboard.html';
            break;
        case 2: // Manager
            $redirectUrl = '/QLNhanSu/backend/src/public/manager/dashboard.html';
            break;
        case 3: // HR
            $redirectUrl = '/QLNhanSu/backend/src/public/hr/dashboard.html';
            break;
        case 4: // Employee
            $redirectUrl = '/QLNhanSu/backend/src/public/employee/dashboard.html';
            break;
        default:
            $redirectUrl = '/QLNhanSu/backend/src/public/login_new.html';
    }

    // Return success response with redirect URL
    echo json_encode([
        'success' => true,
        'data' => [
            'user_id' => $user['user_id'],
            'username' => $user['username'],
            'role' => $user['role_name'],
            'full_name' => $user['full_name'],
            'redirect_url' => $redirectUrl
        ]
    ]);
    exit();

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit();
}

// If not POST request, return error
http_response_code(405);
echo json_encode([
    'success' => false,
    'message' => 'Method not allowed'
]);
exit();

// Ensure no output after JSON
exit();
?> 