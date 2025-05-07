<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../models/User.php';

// Set JSON content type and CORS headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Create default credentials
    $defaultUsername = 'admin';
    $defaultPassword = 'admin123';

    // Initialize User model
    $userModel = new User();
    
    // Attempt authentication
    $result = $userModel->authenticate($defaultUsername, $defaultPassword);

    if (!$result['success']) {
        throw new Exception($result['error']);
    }

    $user = $result['user'];

    // Set session variables
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role_id'];

    // Get redirect URL based on role
    $baseUrl = '/qlnhansu_V2/backend/src/public';
    switch ($user['role_id']) {
        case 1: // Admin
            $redirectUrl = $baseUrl . '/admin/dashboard.html';
            break;
        case 2: // Manager
            $redirectUrl = $baseUrl . '/manager/dashboard.html';
            break;
        case 3: // HR
            $redirectUrl = $baseUrl . '/hr/dashboard.html';
            break;
        case 4: // Employee
            $redirectUrl = $baseUrl . '/employee/dashboard.html';
            break;
        default:
            $redirectUrl = $baseUrl . '/login.html';
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Auto login successful',
        'redirect' => $redirectUrl,
        'user' => [
            'id' => $user['user_id'],
            'username' => $user['username'],
            'role_id' => $user['role_id']
        ]
    ]);

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 