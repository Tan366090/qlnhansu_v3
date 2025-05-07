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
                    $employees = $dataStore->getData('employees', ['department_id' => $id]);
                    Response::success($employees);
                } else {
                    $department = $dataStore->getData('departments', ['id' => $id]);
                    if (!$department) {
                        ErrorHandler::handleNotFound();
                    }
                    Response::success($department[0]);
                }
            } else {
                $page = $_GET['page'] ?? 1;
                $perPage = $_GET['per_page'] ?? 10;
                $departments = $dataStore->getAllData('departments', [
                    'page' => $page,
                    'per_page' => $perPage
                ]);
                Response::paginated($departments['data'], $departments['total'], $page, $perPage);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $rules = [
                'name' => 'required|min:2|max:100',
                'description' => 'max:500',
                'manager_id' => 'integer',
                'parent_id' => 'integer'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            $departmentId = $dataStore->insertData('departments', $data);
            $department = $dataStore->getData('departments', ['id' => $departmentId]);
            Response::created($department[0]);
            break;

        case 'PUT':
            if (!$id) {
                ErrorHandler::handleNotFound();
            }

            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $rules = [
                'name' => 'min:2|max:100',
                'description' => 'max:500',
                'manager_id' => 'integer',
                'parent_id' => 'integer'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            $dataStore->updateData('departments', $data, ['id' => $id]);
            $department = $dataStore->getData('departments', ['id' => $id]);
            Response::updated($department[0]);
            break;

        case 'DELETE':
            if (!$id) {
                ErrorHandler::handleNotFound();
            }

            // Check if department has employees
            $employees = $dataStore->getData('employees', ['department_id' => $id]);
            if (!empty($employees)) {
                ErrorHandler::handle('Cannot delete department with employees', 400);
            }

            $dataStore->deleteData('departments', ['id' => $id]);
            Response::deleted();
            break;

        default:
            ErrorHandler::handle('Method not allowed', 405);
    }
} catch (\Exception $e) {
    ErrorHandler::handle($e);
}
?> 