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
                $assignment = $dataStore->getData('equipment_assignments', ['id' => $id]);
                if (!$assignment) {
                    ErrorHandler::handleNotFound();
                }
                Response::success($assignment[0]);
            } else {
                $page = $_GET['page'] ?? 1;
                $perPage = $_GET['per_page'] ?? 10;
                $equipmentId = $_GET['equipment_id'] ?? null;
                $employeeId = $_GET['employee_id'] ?? null;
                $status = $_GET['status'] ?? null;
                $startDate = $_GET['start_date'] ?? null;
                $endDate = $_GET['end_date'] ?? null;

                $conditions = [];
                if ($equipmentId) {
                    $conditions['equipment_id'] = $equipmentId;
                }
                if ($employeeId) {
                    $conditions['employee_id'] = $employeeId;
                }
                if ($status) {
                    $conditions['status'] = $status;
                }
                if ($startDate && $endDate) {
                    $conditions['assigned_date'] = ['BETWEEN', $startDate, $endDate];
                }

                $assignments = $dataStore->getAllData('equipment_assignments', [
                    'page' => $page,
                    'per_page' => $perPage,
                    'conditions' => $conditions,
                    'order_by' => 'assigned_date DESC'
                ]);
                Response::paginated($assignments['data'], $assignments['total'], $page, $perPage);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $rules = [
                'equipment_id' => 'required|integer|exists:equipment,id',
                'employee_id' => 'required|integer|exists:employees,id',
                'assigned_date' => 'required|date',
                'expected_return_date' => 'date|after:assigned_date',
                'status' => 'required|in:active,returned,lost,damaged',
                'notes' => 'max:1000'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            // Check if equipment is available
            $equipment = $dataStore->getData('equipment', ['id' => $data['equipment_id']]);
            if ($equipment[0]['status'] !== 'available') {
                ErrorHandler::handle('Equipment is not available for assignment', 400);
            }

            // Check if employee already has an active assignment for this equipment
            $existingAssignment = $dataStore->getData('equipment_assignments', [
                'equipment_id' => $data['equipment_id'],
                'employee_id' => $data['employee_id'],
                'status' => 'active'
            ]);
            if (!empty($existingAssignment)) {
                ErrorHandler::handle('Employee already has an active assignment for this equipment', 400);
            }

            // Start transaction
            $dataStore->beginTransaction();

            try {
                $assignmentId = $dataStore->insertData('equipment_assignments', $data);
                
                // Update equipment status
                $dataStore->updateData('equipment', 
                    ['status' => 'in_use'], 
                    ['id' => $data['equipment_id']]
                );
                
                $assignment = $dataStore->getData('equipment_assignments', ['id' => $assignmentId]);
                $dataStore->commit();
                Response::created($assignment[0]);
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
                'expected_return_date' => 'date|after:assigned_date',
                'status' => 'in:active,returned,lost,damaged',
                'notes' => 'max:1000'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            // Check if assignment exists
            $assignment = $dataStore->getData('equipment_assignments', ['id' => $id]);
            if (!$assignment) {
                ErrorHandler::handleNotFound();
            }

            // If changing status to returned/lost/damaged
            if (isset($data['status']) && in_array($data['status'], ['returned', 'lost', 'damaged'])) {
                // Start transaction
                $dataStore->beginTransaction();

                try {
                    // Update assignment
                    $dataStore->updateData('equipment_assignments', $data, ['id' => $id]);
                    
                    // Update equipment status
                    $newStatus = $data['status'] === 'returned' ? 'available' : 'maintenance';
                    $dataStore->updateData('equipment', 
                        ['status' => $newStatus], 
                        ['id' => $assignment[0]['equipment_id']]
                    );
                    
                    $assignment = $dataStore->getData('equipment_assignments', ['id' => $id]);
                    $dataStore->commit();
                    Response::updated($assignment[0]);
                } catch (\Exception $e) {
                    $dataStore->rollback();
                    throw $e;
                }
            } else {
                $dataStore->updateData('equipment_assignments', $data, ['id' => $id]);
                $assignment = $dataStore->getData('equipment_assignments', ['id' => $id]);
                Response::updated($assignment[0]);
            }
            break;

        case 'DELETE':
            if (!$id) {
                ErrorHandler::handleNotFound();
            }

            // Check if assignment exists and is active
            $assignment = $dataStore->getData('equipment_assignments', ['id' => $id]);
            if (!$assignment) {
                ErrorHandler::handleNotFound();
            }
            if ($assignment[0]['status'] === 'active') {
                ErrorHandler::handle('Cannot delete active assignment', 400);
            }

            $dataStore->deleteData('equipment_assignments', ['id' => $id]);
            Response::deleted();
            break;

        default:
            ErrorHandler::handle('Method not allowed', 405);
    }
} catch (\Exception $e) {
    ErrorHandler::handle($e);
}
?> 