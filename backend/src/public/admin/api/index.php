<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get the request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/admin/api/', '', $path);

// Split the path into segments
$segments = explode('/', $path);
$resource = $segments[0] ?? '';
$id = $segments[1] ?? null;

// Get request body
$input = json_decode(file_get_contents('php://input'), true);

// Response helper function
function sendResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode([
        'status' => $status,
        'data' => $data
    ]);
    exit();
}

// Error handler
function handleError($message, $status = 400) {
    sendResponse(['error' => $message], $status);
}

// Route handling
try {
    switch ($resource) {
        case 'employees':
            require_once 'employees.php';
            break;
        case 'departments':
            require_once 'departments.php';
            break;
        case 'positions':
            require_once 'positions.php';
            break;
        case 'salaries':
            require_once 'salaries.php';
            break;
        case 'attendance':
            require_once 'attendance.php';
            break;
        case 'leaves':
            require_once 'leaves.php';
            break;
        default:
            handleError('Resource not found', 404);
    }
} catch (Exception $e) {
    handleError($e->getMessage(), 500);
} 