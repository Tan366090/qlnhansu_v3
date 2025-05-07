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
                $position = $dataStore->getData('employee_positions', ['id' => $id]);
                if (!$position) {
                    ErrorHandler::handleNotFound();
                }
                Response::success($position[0]);
            } else {
                $page = $_GET['page'] ?? 1;
                $perPage = $_GET['per_page'] ?? 10;
                $employeeId = $_GET['employee_id'] ?? null;
                $positionId = $_GET['position_id'] ?? null;
                $departmentId = $_GET['department_id'] ?? null;
                $status = $_GET['status'] ?? null;
                $startDate = $_GET['start_date'] ?? null;
                $endDate = $_GET['end_date'] ?? null;

                $conditions = [];
                if ($employeeId) {
                    $conditions['employee_id'] = $employeeId;
                }
                if ($positionId) {
                    $conditions['position_id'] = $positionId;
                }
                if ($departmentId) {
                    $conditions['department_id'] = $departmentId;
                }
                if ($status) {
                    $conditions['status'] = $status;
                }
                if ($startDate && $endDate) {
                    $conditions['start_date'] = ['BETWEEN', $startDate, $endDate];
                }

                $positions = $dataStore->getAllData('employee_positions', [
                    'page' => $page,
                    'per_page' => $perPage,
                    'conditions' => $conditions,
                    'order_by' => 'start_date DESC'
                ]);
                Response::paginated($positions['data'], $positions['total'], $page, $perPage);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $rules = [
                'employee_id' => 'required|integer|exists:employees,id',
                'position_id' => 'required|integer|exists:positions,id',
                'department_id' => 'required|integer|exists:departments,id',
                'start_date' => 'required|date',
                'end_date' => 'date|after:start_date',
                'status' => 'required|in:active,inactive',
                'salary' => 'required|numeric|min:0',
                'notes' => 'max:1000'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            // Check if employee already has an active position
            $existingPosition = $dataStore->getData('employee_positions', [
                'employee_id' => $data['employee_id'],
                'status' => 'active'
            ]);
            if (!empty($existingPosition)) {
                ErrorHandler::handle('Employee already has an active position', 400);
            }

            // Start transaction
            $dataStore->beginTransaction();

            try {
                $positionId = $dataStore->insertData('employee_positions', $data);
                $position = $dataStore->getData('employee_positions', ['id' => $positionId]);
                $dataStore->commit();
                Response::created($position[0]);
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
                'position_id' => 'integer|exists:positions,id',
                'department_id' => 'integer|exists:departments,id',
                'start_date' => 'date',
                'end_date' => 'date|after:start_date',
                'status' => 'in:active,inactive',
                'salary' => 'numeric|min:0',
                'notes' => 'max:1000'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            // Check if updating to active status
            if (isset($data['status']) && $data['status'] === 'active') {
                $existingPosition = $dataStore->getData('employee_positions', [
                    'employee_id' => $data['employee_id'],
                    'status' => 'active',
                    'id' => ['!=', $id]
                ]);
                if (!empty($existingPosition)) {
                    ErrorHandler::handle('Employee already has an active position', 400);
                }
            }

            $dataStore->updateData('employee_positions', $data, ['id' => $id]);
            $position = $dataStore->getData('employee_positions', ['id' => $id]);
            Response::updated($position[0]);
            break;

        case 'DELETE':
            if (!$id) {
                ErrorHandler::handleNotFound();
            }

            // Check if position is active
            $position = $dataStore->getData('employee_positions', ['id' => $id]);
            if ($position[0]['status'] === 'active') {
                ErrorHandler::handle('Cannot delete active position', 400);
            }

            $dataStore->deleteData('employee_positions', ['id' => $id]);
            Response::deleted();
            break;

        default:
            ErrorHandler::handle('Method not allowed', 405);
    }
} catch (\Exception $e) {
    ErrorHandler::handle($e);
}
?> 