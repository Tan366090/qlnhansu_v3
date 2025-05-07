<?php
// Bật error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cấu hình CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Xử lý preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Lấy đường dẫn request
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/qlnhansu_V2/backend/src/api/';
$path = str_replace($base_path, '', $request_uri);
$path = explode('?', $path)[0]; // Loại bỏ query string

// Xử lý routing
switch ($path) {
    case 'departments':
        require_once 'departments.php';
        break;
    case 'contracts':
        require_once 'contracts.php';
        break;
    case 'positions':
        require_once 'positions.php';
        break;
    case 'employees':
        require_once 'employees.php';
        break;
    case 'import_employees':
        require_once 'import_employees.php';
        break;
    default:
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'API endpoint not found'
        ]);
        break;
} 