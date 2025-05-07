<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get request path
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/qlnhansu_V2/backend/src/public/';
$path = str_replace($base_path, '', $request_uri);

// Parse endpoint
$endpoint = explode('/', $path)[0];
$endpoint = str_replace('.php', '', $endpoint);

// Include API file if exists
$api_file = __DIR__ . '/api/' . $endpoint . '_api.php';
if (file_exists($api_file)) {
    require_once $api_file;
} else {
    http_response_code(404);
    echo json_encode([
        'status' => 'error',
        'message' => 'API endpoint not found'
    ]);
} 