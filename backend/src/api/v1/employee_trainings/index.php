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
                if ($action === 'evaluations') {
                    $evaluations = $dataStore->getData('training_evaluations', ['employee_training_id' => $id]);
                    Response::success($evaluations);
                } else {
                    $training = $dataStore->getData('employee_trainings', ['id' => $id]);
                    if (!$training) {
                        ErrorHandler::handleNotFound();
                    }
                    Response::success($training[0]);
                }
            } else {
                $page = $_GET['page'] ?? 1;
                $perPage = $_GET['per_page'] ?? 10;
                $employeeId = $_GET['employee_id'] ?? null;
                $trainingId = $_GET['training_id'] ?? null;
                $status = $_GET['status'] ?? null;
                $startDate = $_GET['start_date'] ?? null;
                $endDate = $_GET['end_date'] ?? null;

                $conditions = [];
                if ($employeeId) {
                    $conditions['employee_id'] = $employeeId;
                }
                if ($trainingId) {
                    $conditions['training_id'] = $trainingId;
                }
                if ($status) {
                    $conditions['status'] = $status;
                }
                if ($startDate && $endDate) {
                    $conditions['start_date'] = ['BETWEEN', $startDate, $endDate];
                }

                $trainings = $dataStore->getAllData('employee_trainings', [
                    'page' => $page,
                    'per_page' => $perPage,
                    'conditions' => $conditions,
                    'order_by' => 'start_date DESC'
                ]);
                Response::paginated($trainings['data'], $trainings['total'], $page, $perPage);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $rules = [
                'employee_id' => 'required|integer|exists:employees,id',
                'training_id' => 'required|integer|exists:trainings,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'status' => 'required|in:registered,in_progress,completed,cancelled',
                'result' => 'in:pass,fail,not_applicable',
                'certificate_url' => 'max:255',
                'notes' => 'max:1000'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            // Check if employee is already registered for this training
            $existingTraining = $dataStore->getData('employee_trainings', [
                'employee_id' => $data['employee_id'],
                'training_id' => $data['training_id'],
                'status' => ['IN', ['registered', 'in_progress']]
            ]);
            if (!empty($existingTraining)) {
                ErrorHandler::handle('Employee is already registered for this training', 400);
            }

            // Start transaction
            $dataStore->beginTransaction();

            try {
                $trainingId = $dataStore->insertData('employee_trainings', $data);
                $training = $dataStore->getData('employee_trainings', ['id' => $trainingId]);
                $dataStore->commit();
                Response::created($training[0]);
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
                'start_date' => 'date',
                'end_date' => 'date|after:start_date',
                'status' => 'in:registered,in_progress,completed,cancelled',
                'result' => 'in:pass,fail,not_applicable',
                'certificate_url' => 'max:255',
                'notes' => 'max:1000'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            // Check if training is already completed
            $training = $dataStore->getData('employee_trainings', ['id' => $id]);
            if ($training[0]['status'] === 'completed') {
                ErrorHandler::handle('Cannot update completed training', 400);
            }

            $dataStore->updateData('employee_trainings', $data, ['id' => $id]);
            $training = $dataStore->getData('employee_trainings', ['id' => $id]);
            Response::updated($training[0]);
            break;

        case 'DELETE':
            if (!$id) {
                ErrorHandler::handleNotFound();
            }

            // Check if training has evaluations
            $evaluations = $dataStore->getData('training_evaluations', ['employee_training_id' => $id]);
            if (!empty($evaluations)) {
                ErrorHandler::handle('Cannot delete training with evaluations', 400);
            }

            $dataStore->deleteData('employee_trainings', ['id' => $id]);
            Response::deleted();
            break;

        default:
            ErrorHandler::handle('Method not allowed', 405);
    }
} catch (\Exception $e) {
    ErrorHandler::handle($e);
}
?> 