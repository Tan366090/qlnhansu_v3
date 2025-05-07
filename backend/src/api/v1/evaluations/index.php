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
                $evaluation = $dataStore->getData('evaluations', ['id' => $id]);
                if (!$evaluation) {
                    ErrorHandler::handleNotFound();
                }
                Response::success($evaluation[0]);
            } else {
                $page = $_GET['page'] ?? 1;
                $perPage = $_GET['per_page'] ?? 10;
                $employeeId = $_GET['employee_id'] ?? null;
                $evaluatorId = $_GET['evaluator_id'] ?? null;
                $evaluationType = $_GET['evaluation_type'] ?? null;
                $startDate = $_GET['start_date'] ?? null;
                $endDate = $_GET['end_date'] ?? null;
                $status = $_GET['status'] ?? null;

                $conditions = [];
                if ($employeeId) {
                    $conditions['employee_id'] = $employeeId;
                }
                if ($evaluatorId) {
                    $conditions['evaluator_id'] = $evaluatorId;
                }
                if ($evaluationType) {
                    $conditions['evaluation_type'] = $evaluationType;
                }
                if ($status) {
                    $conditions['status'] = $status;
                }
                if ($startDate && $endDate) {
                    $conditions['evaluation_date'] = ['BETWEEN', $startDate, $endDate];
                }

                $evaluations = $dataStore->getAllData('evaluations', [
                    'page' => $page,
                    'per_page' => $perPage,
                    'conditions' => $conditions,
                    'order_by' => 'evaluation_date DESC'
                ]);
                Response::paginated($evaluations['data'], $evaluations['total'], $page, $perPage);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $rules = [
                'employee_id' => 'required|integer|exists:employees,id',
                'evaluator_id' => 'required|integer|exists:employees,id',
                'evaluation_type' => 'required|in:performance,probation,annual',
                'evaluation_date' => 'required|date',
                'score' => 'required|numeric|min:0|max:100',
                'status' => 'required|in:draft,pending,completed,rejected',
                'notes' => 'max:1000'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            // Check if evaluator is authorized
            if ($data['evaluator_id'] === $data['employee_id']) {
                ErrorHandler::handle('Evaluator cannot evaluate themselves', 400);
            }

            // Check if employee has a pending evaluation of the same type
            $pendingEvaluation = $dataStore->getData('evaluations', [
                'employee_id' => $data['employee_id'],
                'evaluation_type' => $data['evaluation_type'],
                'status' => 'pending'
            ]);
            if (!empty($pendingEvaluation)) {
                ErrorHandler::handle('Employee already has a pending evaluation of this type', 400);
            }

            $evaluationId = $dataStore->insertData('evaluations', $data);
            $evaluation = $dataStore->getData('evaluations', ['id' => $evaluationId]);
            Response::created($evaluation[0]);
            break;

        case 'PUT':
            if (!$id) {
                ErrorHandler::handleNotFound();
            }

            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $rules = [
                'score' => 'numeric|min:0|max:100',
                'status' => 'in:draft,pending,completed,rejected',
                'notes' => 'max:1000'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            // Check if evaluation exists
            $evaluation = $dataStore->getData('evaluations', ['id' => $id]);
            if (!$evaluation) {
                ErrorHandler::handleNotFound();
            }

            // If changing status to completed
            if (isset($data['status']) && $data['status'] === 'completed') {
                // Start transaction
                $dataStore->beginTransaction();

                try {
                    // Update evaluation
                    $dataStore->updateData('evaluations', $data, ['id' => $id]);
                    
                    // Update employee performance score if it's a performance evaluation
                    if ($evaluation[0]['evaluation_type'] === 'performance') {
                        $dataStore->updateData('employees', 
                            ['performance_score' => $data['score']], 
                            ['id' => $evaluation[0]['employee_id']]
                        );
                    }
                    
                    $evaluation = $dataStore->getData('evaluations', ['id' => $id]);
                    $dataStore->commit();
                    Response::updated($evaluation[0]);
                } catch (\Exception $e) {
                    $dataStore->rollback();
                    throw $e;
                }
            } else {
                $dataStore->updateData('evaluations', $data, ['id' => $id]);
                $evaluation = $dataStore->getData('evaluations', ['id' => $id]);
                Response::updated($evaluation[0]);
            }
            break;

        case 'DELETE':
            if (!$id) {
                ErrorHandler::handleNotFound();
            }

            // Check if evaluation exists and is not completed
            $evaluation = $dataStore->getData('evaluations', ['id' => $id]);
            if (!$evaluation) {
                ErrorHandler::handleNotFound();
            }
            if ($evaluation[0]['status'] === 'completed') {
                ErrorHandler::handle('Cannot delete completed evaluation', 400);
            }

            $dataStore->deleteData('evaluations', ['id' => $id]);
            Response::deleted();
            break;

        default:
            ErrorHandler::handle('Method not allowed', 405);
    }
} catch (\Exception $e) {
    ErrorHandler::handle($e);
}
?> 