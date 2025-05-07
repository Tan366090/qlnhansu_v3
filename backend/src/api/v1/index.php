<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../config/database.php';

// Get the request path
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);
$path = str_replace('/qlnhansu_V2/backend/src/api/v1/', '', $path);

// Get the request method
$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = new PDO("mysql:host=localhost;dbname=qlnhansu", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Route the request
    switch ($path) {
        case 'employees':
            require_once 'employees.php';
            break;
            
        case 'departments':
            require_once 'departments.php';
            break;
            
        case 'positions':
            require_once 'positions.php';
            break;
            
        default:
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'API endpoint not found'
            ]);
            break;
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 