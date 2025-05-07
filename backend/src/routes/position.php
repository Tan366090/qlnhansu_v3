<?php
require_once __DIR__ . '/../controllers/PositionController.php';

// Get database connection
$db = require_once __DIR__ . '/../config/database.php';

// Initialize controller
$positionController = new PositionController($db);

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/api/positions', '', $path);

// Handle request
switch ($method) {
    case 'GET':
        if (empty($path) || $path === '/') {
            // Get all positions
            $response = $positionController->getAll();
        } elseif (preg_match('/^\/(\d+)$/', $path, $matches)) {
            // Get position by ID
            $response = $positionController->getById($matches[1]);
        } elseif (preg_match('/^\/department\/(\d+)$/', $path, $matches)) {
            // Get positions by department
            $response = $positionController->getByDepartment($matches[1]);
        } else {
            $response = [
                'success' => false,
                'message' => 'Đường dẫn không hợp lệ'
            ];
        }
        break;

    case 'POST':
        if (empty($path) || $path === '/') {
            // Create new position
            $data = json_decode(file_get_contents('php://input'), true);
            $response = $positionController->create($data);
        } else {
            $response = [
                'success' => false,
                'message' => 'Đường dẫn không hợp lệ'
            ];
        }
        break;

    case 'PUT':
        if (preg_match('/^\/(\d+)$/', $path, $matches)) {
            // Update position
            $data = json_decode(file_get_contents('php://input'), true);
            $response = $positionController->update($matches[1], $data);
        } else {
            $response = [
                'success' => false,
                'message' => 'Đường dẫn không hợp lệ'
            ];
        }
        break;

    case 'DELETE':
        if (preg_match('/^\/(\d+)$/', $path, $matches)) {
            // Delete position
            $response = $positionController->delete($matches[1]);
        } else {
            $response = [
                'success' => false,
                'message' => 'Đường dẫn không hợp lệ'
            ];
        }
        break;

    default:
        $response = [
            'success' => false,
            'message' => 'Phương thức không được hỗ trợ'
        ];
        break;
}

// Set response headers
header('Content-Type: application/json');
http_response_code($response['success'] ? 200 : ($response['message'] === 'Chức vụ không tồn tại' ? 404 : 400));

// Return response
echo json_encode($response); 