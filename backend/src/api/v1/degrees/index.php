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
                    $employees = $dataStore->getData('employee_degrees', ['degree_id' => $id]);
                    Response::success($employees);
                } else {
                    $degree = $dataStore->getData('degrees', ['id' => $id]);
                    if (!$degree) {
                        ErrorHandler::handleNotFound();
                    }
                    Response::success($degree[0]);
                }
            } else {
                $page = $_GET['page'] ?? 1;
                $perPage = $_GET['per_page'] ?? 10;
                $type = $_GET['type'] ?? null;
                $field = $_GET['field'] ?? null;
                $search = $_GET['search'] ?? null;

                $conditions = [];
                if ($type) {
                    $conditions['type'] = $type;
                }
                if ($field) {
                    $conditions['field'] = $field;
                }
                if ($search) {
                    $conditions['search'] = $search;
                }

                $degrees = $dataStore->getAllData('degrees', [
                    'page' => $page,
                    'per_page' => $perPage,
                    'conditions' => $conditions
                ]);
                Response::paginated($degrees['data'], $degrees['total'], $page, $perPage);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $rules = [
                'name' => 'required|min:2|max:100',
                'type' => 'required|in:bachelor,master,doctorate,associate,certificate',
                'field' => 'required|min:2|max:100',
                'description' => 'required|min:10|max:1000',
                'duration' => 'required|integer|min:1',
                'credits' => 'required|integer|min:0',
                'status' => 'required|in:active,inactive',
                'notes' => 'max:1000'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            // Start transaction
            $dataStore->beginTransaction();

            try {
                $degreeId = $dataStore->insertData('degrees', $data);
                $degree = $dataStore->getData('degrees', ['id' => $degreeId]);
                $dataStore->commit();
                Response::created($degree[0]);
            } catch (\Exception $e) {
                $dataStore->rollback();
                throw $e;
            }
            break;

        case 'PUT':
            if (!$id) {
                ErrorHandler::handleNotFound();
            }

            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $rules = [
                'name' => 'min:2|max:100',
                'type' => 'in:bachelor,master,doctorate,associate,certificate',
                'field' => 'min:2|max:100',
                'description' => 'min:10|max:1000',
                'duration' => 'integer|min:1',
                'credits' => 'integer|min:0',
                'status' => 'in:active,inactive',
                'notes' => 'max:1000'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            $dataStore->updateData('degrees', $data, ['id' => $id]);
            $degree = $dataStore->getData('degrees', ['id' => $id]);
            Response::updated($degree[0]);
            break;

        case 'DELETE':
            if (!$id) {
                ErrorHandler::handleNotFound();
            }

            // Check if degree has employees
            $employees = $dataStore->getData('employee_degrees', ['degree_id' => $id]);
            if (!empty($employees)) {
                ErrorHandler::handle('Cannot delete degree with assigned employees', 400);
            }

            $dataStore->deleteData('degrees', ['id' => $id]);
            Response::deleted();
            break;

        default:
            ErrorHandler::handle('Method not allowed', 405);
    }
} catch (\Exception $e) {
    ErrorHandler::handle($e);
}
?> 