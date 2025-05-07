<?php
require_once __DIR__ . '/../controllers/DataController.php';

use App\Controllers\DataController;

header('Content-Type: application/json');

try {
    $controller = new DataController();
    $request = json_decode(file_get_contents('php://input'), true) ?? [];
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';

    switch ($method) {
        case 'GET':
            switch ($action) {
                case 'getData':
                    $response = $controller->getData($request);
                    break;
                case 'getFilteredData':
                    $response = $controller->getFilteredData($request);
                    break;
                case 'getRelatedData':
                    $response = $controller->getRelatedData($request);
                    break;
                case 'clearCache':
                    $response = $controller->clearCache($request);
                    break;
                default:
                    throw new Exception('Invalid action');
            }
            break;
        case 'POST':
            switch ($action) {
                case 'insertData':
                    $response = $controller->insertData($request);
                    break;
                default:
                    throw new Exception('Invalid action');
            }
            break;
        case 'PUT':
            switch ($action) {
                case 'updateData':
                    $response = $controller->updateData($request);
                    break;
                default:
                    throw new Exception('Invalid action');
            }
            break;
        case 'DELETE':
            switch ($action) {
                case 'deleteData':
                    $response = $controller->deleteData($request);
                    break;
                default:
                    throw new Exception('Invalid action');
            }
            break;
        default:
            throw new Exception('Method not allowed');
    }

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 