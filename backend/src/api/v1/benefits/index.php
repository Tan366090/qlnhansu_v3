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
                    $employees = $dataStore->getData('employee_benefits', ['benefit_id' => $id]);
                    Response::success($employees);
                } else {
                    $benefit = $dataStore->getData('benefits', ['id' => $id]);
                    if (!$benefit) {
                        ErrorHandler::handleNotFound();
                    }
                    Response::success($benefit[0]);
                }
            } else {
                $page = $_GET['page'] ?? 1;
                $perPage = $_GET['per_page'] ?? 10;
                $type = $_GET['type'] ?? null;
                $status = $_GET['status'] ?? null;
                $search = $_GET['search'] ?? null;

                $conditions = [];
                if ($type) {
                    $conditions['type'] = $type;
                }
                if ($status) {
                    $conditions['status'] = $status;
                }
                if ($search) {
                    $conditions['search'] = $search;
                }

                $benefits = $dataStore->getAllData('benefits', [
                    'page' => $page,
                    'per_page' => $perPage,
                    'conditions' => $conditions
                ]);
                Response::paginated($benefits['data'], $benefits['total'], $page, $perPage);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $rules = [
                'name' => 'required|min:2|max:100',
                'description' => 'required|min:10|max:1000',
                'type' => 'required|in:health,insurance,retirement,education,other',
                'amount' => 'required|numeric|min:0',
                'frequency' => 'required|in:monthly,quarterly,yearly,one_time',
                'start_date' => 'required|date',
                'end_date' => 'date|after:start_date',
                'status' => 'required|in:active,inactive',
                'eligibility_criteria' => 'required|min:10|max:1000',
                'document_url' => 'max:255'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            // Start transaction
            $dataStore->beginTransaction();

            try {
                $benefitId = $dataStore->insertData('benefits', $data);
                $benefit = $dataStore->getData('benefits', ['id' => $benefitId]);
                $dataStore->commit();
                Response::created($benefit[0]);
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
                'description' => 'min:10|max:1000',
                'type' => 'in:health,insurance,retirement,education,other',
                'amount' => 'numeric|min:0',
                'frequency' => 'in:monthly,quarterly,yearly,one_time',
                'start_date' => 'date',
                'end_date' => 'date|after:start_date',
                'status' => 'in:active,inactive',
                'eligibility_criteria' => 'min:10|max:1000',
                'document_url' => 'max:255'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            $dataStore->updateData('benefits', $data, ['id' => $id]);
            $benefit = $dataStore->getData('benefits', ['id' => $id]);
            Response::updated($benefit[0]);
            break;

        case 'DELETE':
            if (!$id) {
                ErrorHandler::handleNotFound();
            }

            // Check if benefit has employees
            $employees = $dataStore->getData('employee_benefits', ['benefit_id' => $id]);
            if (!empty($employees)) {
                ErrorHandler::handle('Cannot delete benefit with assigned employees', 400);
            }

            $dataStore->deleteData('benefits', ['id' => $id]);
            Response::deleted();
            break;

        default:
            ErrorHandler::handle('Method not allowed', 405);
    }
} catch (\Exception $e) {
    ErrorHandler::handle($e);
}
?> 