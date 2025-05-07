<?php
// Bắt đầu output buffering
ob_start();

// Bật báo lỗi
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../../../logs/php_errors.log');

// Kiểm tra lỗi PHP
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_clean();
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Internal server error',
            'error' => $error['message']
        ]);
    }
});

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../../auth/SessionHelper.php';

// Set JSON content type and CORS headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: http://localhost:4000');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Get JSON input
    $json = file_get_contents('php://input');
    if (!$json) {
        throw new Exception('No data received');
    }

    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data');
    }

    // Validate required fields
    if (empty($data['username']) || empty($data['password'])) {
        throw new Exception('Username and password are required');
    }

    // Initialize User model
    $userModel = new User();
    $result = $userModel->authenticate($data['username'], $data['password']);

    if (!$result['success']) {
        throw new Exception($result['error']);
    }

    $user = $result['user'];

    // Set session variables directly
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role_id'];

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

    // Return JSON response
    $response = [
        'success' => true,
        'message' => 'Login successful',
        'redirect' => $redirectUrl,
        'user' => [
            'id' => $user['user_id'],
            'username' => $user['username'],
            'role_id' => $user['role_id']
        ]
    ];

    // Xóa tất cả output buffer
    ob_clean();
    echo json_encode($response, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    http_response_code(401);
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
    // Xóa tất cả output buffer
    ob_clean();
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}

// Kết thúc output buffering
ob_end_flush();

