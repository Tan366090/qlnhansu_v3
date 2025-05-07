<?php
require_once __DIR__ . '/../../../config/autoload.php';

use App\Middleware\CorsMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\ValidationMiddleware;
use App\Handlers\Response;
use App\Handlers\ErrorHandler;
use App\Services\DataStore;

// Apply CORS middleware
$cors = new CorsMiddleware();
$cors->handle();

// Apply Auth middleware
$auth = new AuthMiddleware();
$user = $auth->handle();

if (!$user) {
    ErrorHandler::handleUnauthorized();
}

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// Get resource ID if exists
$id = isset($pathParts[3]) ? (int)$pathParts[3] : null;

// Get action if exists
$action = isset($pathParts[4]) ? $pathParts[4] : null;

// Initialize DataStore
$dataStore = DataStore::getInstance();

try {
    switch ($method) {
        case 'GET':
            if ($id) {
                if ($action === 'employees') {
                    $employees = $dataStore->getData('employees', ['position_id' => $id]);
                    Response::success($employees);
                } else {
                    $position = $dataStore->getData('positions', ['id' => $id]);
                    if (!$position) {
                        ErrorHandler::handleNotFound();
                    }
                    Response::success($position[0]);
                }
            } else {
                $page = $_GET['page'] ?? 1;
                $perPage = $_GET['per_page'] ?? 10;
                $positions = $dataStore->getAllData('positions', [
                    'page' => $page,
                    'per_page' => $perPage
                ]);
                Response::paginated($positions['data'], $positions['total'], $page, $perPage);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $rules = [
                'title' => 'required|min:2|max:100',
                'department_id' => 'required|integer',
                'description' => 'max:500',
                'requirements' => 'max:1000',
                'salary_range_min' => 'required|numeric',
                'salary_range_max' => 'required|numeric'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            $positionId = $dataStore->insertData('positions', $data);
            $position = $dataStore->getData('positions', ['id' => $positionId]);
            Response::created($position[0]);
            break;

        case 'PUT':
            if (!$id) {
                ErrorHandler::handleNotFound();
            }

            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $rules = [
                'title' => 'min:2|max:100',
                'department_id' => 'integer',
                'description' => 'max:500',
                'requirements' => 'max:1000',
                'salary_range_min' => 'numeric',
                'salary_range_max' => 'numeric'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            $dataStore->updateData('positions', $data, ['id' => $id]);
            $position = $dataStore->getData('positions', ['id' => $id]);
            Response::updated($position[0]);
            break;

        case 'DELETE':
            if (!$id) {
                ErrorHandler::handleNotFound();
            }

            // Check if position has employees
            $employees = $dataStore->getData('employees', ['position_id' => $id]);
            if (!empty($employees)) {
                ErrorHandler::handle('Cannot delete position with employees', 400);
            }

            $dataStore->deleteData('positions', ['id' => $id]);
            Response::deleted();
            break;

        default:
            ErrorHandler::handle('Method not allowed', 405);
    }
} catch (\Exception $e) {
    ErrorHandler::handle($e);
}
?> 