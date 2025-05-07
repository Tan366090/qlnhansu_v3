<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/jwt.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Parse the URL to determine the endpoint
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/qlnhansu_V2/backend/src/public/api/';
$endpoint = str_replace($base_path, '', $request_uri);
$endpoint = strtok($endpoint, '?'); // Remove query parameters

// Route the request to the appropriate handler
try {
    switch ($endpoint) {
        case 'auth/login':
            require_once __DIR__ . '/../../Controllers/AuthController.php';
            $controller = new AuthController();
            $controller->login();
            break;
            
        case 'users':
            require_once __DIR__ . '/../../Controllers/UserController.php';
            $controller = new UserController();
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $controller->getUsers();
            }
            break;
            
        case 'departments/list':
        case 'departments':
            require_once __DIR__ . '/departments.php';
            break;
            
        case 'contract-types':
            require_once __DIR__ . '/contract-types.php';
            break;
            
        case 'dashboard':
            require_once __DIR__ . '/dashboard_api.php';
            $endpoint = $_GET['endpoint'] ?? '';
            switch ($endpoint) {
                case 'attendance':
                    $period = $_GET['period'] ?? 'week';
                    echo json_encode(getAttendanceData($period));
                    break;
                case 'data':
                    echo json_encode(getDashboardStats());
                    break;
                default:
                    http_response_code(404);
                    echo json_encode(['error' => 'Dashboard endpoint not found']);
                    break;
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 