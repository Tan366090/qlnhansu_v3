<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php_errors.log');

// Set JSON content type
header('Content-Type: application/json; charset=utf-8');

// Required files
$required_files = [
    __DIR__ . '/../middleware/CORSMiddleware.php',
    __DIR__ . '/SessionHelper.php'
];

foreach ($required_files as $file) {
    if (!file_exists($file)) {
        error_log("Required file not found: " . $file);
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Server configuration error'
        ]);
        exit();
    }
    require_once $file;
}

// Handle CORS
CORSMiddleware::handleRequest();

// Always return authenticated user
$currentUser = [
    'id' => 1,
    'username' => 'admin',
    'role' => 'admin'
];

// Return success response
echo json_encode([
    'authenticated' => true,
    'user' => $currentUser
]);
?> 