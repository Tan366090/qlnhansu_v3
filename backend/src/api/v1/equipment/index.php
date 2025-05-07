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
                if ($action === 'assignments') {
                    $assignments = $dataStore->getData('equipment_assignments', ['equipment_id' => $id]);
                    Response::success($assignments);
                } else if ($action === 'maintenance') {
                    $maintenance = $dataStore->getData('equipment_maintenance', ['equipment_id' => $id]);
                    Response::success($maintenance);
                } else {
                    $equipment = $dataStore->getData('equipment', ['id' => $id]);
                    if (!$equipment) {
                        ErrorHandler::handleNotFound();
                    }
                    Response::success($equipment[0]);
                }
            } else {
                $page = $_GET['page'] ?? 1;
                $perPage = $_GET['per_page'] ?? 10;
                $type = $_GET['type'] ?? null;
                $status = $_GET['status'] ?? null;
                $departmentId = $_GET['department_id'] ?? null;
                $search = $_GET['search'] ?? null;

                $conditions = [];
                if ($type) {
                    $conditions['type'] = $type;
                }
                if ($status) {
                    $conditions['status'] = $status;
                }
                if ($departmentId) {
                    $conditions['department_id'] = $departmentId;
                }
                if ($search) {
                    $conditions['name'] = ['LIKE', "%$search%"];
                }

                $equipment = $dataStore->getAllData('equipment', [
                    'page' => $page,
                    'per_page' => $perPage,
                    'conditions' => $conditions,
                    'order_by' => 'name ASC'
                ]);
                Response::paginated($equipment['data'], $equipment['total'], $page, $perPage);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $rules = [
                'name' => 'required|max:255',
                'type' => 'required|max:100',
                'model' => 'required|max:100',
                'serial_number' => 'required|max:100|unique:equipment,serial_number',
                'purchase_date' => 'required|date',
                'purchase_price' => 'required|numeric|min:0',
                'warranty_period' => 'integer|min:0',
                'department_id' => 'required|integer|exists:departments,id',
                'status' => 'required|in:available,in_use,maintenance,disposed',
                'location' => 'max:255',
                'notes' => 'max:1000'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            // Start transaction
            $dataStore->beginTransaction();

            try {
                $equipmentId = $dataStore->insertData('equipment', $data);
                $equipment = $dataStore->getData('equipment', ['id' => $equipmentId]);
                $dataStore->commit();
                Response::created($equipment[0]);
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
                'name' => 'max:255',
                'type' => 'max:100',
                'model' => 'max:100',
                'serial_number' => 'max:100|unique:equipment,serial_number',
                'purchase_date' => 'date',
                'purchase_price' => 'numeric|min:0',
                'warranty_period' => 'integer|min:0',
                'department_id' => 'integer|exists:departments,id',
                'status' => 'in:available,in_use,maintenance,disposed',
                'location' => 'max:255',
                'notes' => 'max:1000'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            // Check if equipment exists
            $equipment = $dataStore->getData('equipment', ['id' => $id]);
            if (!$equipment) {
                ErrorHandler::handleNotFound();
            }

            // Check if equipment is in use
            if ($equipment[0]['status'] === 'in_use' && isset($data['status']) && $data['status'] !== 'in_use') {
                $assignments = $dataStore->getData('equipment_assignments', [
                    'equipment_id' => $id,
                    'status' => 'active'
                ]);
                if (!empty($assignments)) {
                    ErrorHandler::handle('Cannot change status of equipment that is currently assigned', 400);
                }
            }

            $dataStore->updateData('equipment', $data, ['id' => $id]);
            $equipment = $dataStore->getData('equipment', ['id' => $id]);
            Response::updated($equipment[0]);
            break;

        case 'DELETE':
            if (!$id) {
                ErrorHandler::handleNotFound();
            }

            // Check if equipment is in use
            $equipment = $dataStore->getData('equipment', ['id' => $id]);
            if ($equipment[0]['status'] === 'in_use') {
                ErrorHandler::handle('Cannot delete equipment that is currently in use', 400);
            }

            // Check if equipment has maintenance records
            $maintenance = $dataStore->getData('equipment_maintenance', ['equipment_id' => $id]);
            if (!empty($maintenance)) {
                ErrorHandler::handle('Cannot delete equipment with maintenance records', 400);
            }

            $dataStore->deleteData('equipment', ['id' => $id]);
            Response::deleted();
            break;

        default:
            ErrorHandler::handle('Method not allowed', 405);
    }
} catch (\Exception $e) {
    ErrorHandler::handle($e);
}
?> 