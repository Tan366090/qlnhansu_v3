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
                if ($action === 'resources') {
                    $resources = $dataStore->getData('project_resources', ['project_id' => $id]);
                    Response::success($resources);
                } else if ($action === 'tasks') {
                    $tasks = $dataStore->getData('project_tasks', ['project_id' => $id]);
                    Response::success($tasks);
                } else {
                    $project = $dataStore->getData('projects', ['id' => $id]);
                    if (!$project) {
                        ErrorHandler::handleNotFound();
                    }
                    Response::success($project[0]);
                }
            } else {
                $page = $_GET['page'] ?? 1;
                $perPage = $_GET['per_page'] ?? 10;
                $status = $_GET['status'] ?? null;
                $managerId = $_GET['manager_id'] ?? null;
                $startDate = $_GET['start_date'] ?? null;
                $endDate = $_GET['end_date'] ?? null;
                $search = $_GET['search'] ?? null;

                $conditions = [];
                if ($status) {
                    $conditions['status'] = $status;
                }
                if ($managerId) {
                    $conditions['manager_id'] = $managerId;
                }
                if ($startDate && $endDate) {
                    $conditions['start_date'] = ['BETWEEN', $startDate, $endDate];
                }
                if ($search) {
                    $conditions['search'] = $search;
                }

                $projects = $dataStore->getAllData('projects', [
                    'page' => $page,
                    'per_page' => $perPage,
                    'conditions' => $conditions
                ]);
                Response::paginated($projects['data'], $projects['total'], $page, $perPage);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $rules = [
                'name' => 'required|min:2|max:100',
                'description' => 'required|min:10|max:1000',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'manager_id' => 'required|integer|exists:employees,id',
                'status' => 'required|in:planning,in_progress,on_hold,completed,cancelled',
                'budget' => 'required|numeric|min:0',
                'priority' => 'required|in:low,medium,high,critical',
                'client_name' => 'required|min:2|max:100',
                'client_contact' => 'required|min:5|max:255',
                'client_email' => 'required|email',
                'client_phone' => 'required|min:10|max:15'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            // Start transaction
            $dataStore->beginTransaction();

            try {
                $projectId = $dataStore->insertData('projects', $data);
                $project = $dataStore->getData('projects', ['id' => $projectId]);
                $dataStore->commit();
                Response::created($project[0]);
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
                'start_date' => 'date',
                'end_date' => 'date|after:start_date',
                'manager_id' => 'integer|exists:employees,id',
                'status' => 'in:planning,in_progress,on_hold,completed,cancelled',
                'budget' => 'numeric|min:0',
                'priority' => 'in:low,medium,high,critical',
                'client_name' => 'min:2|max:100',
                'client_contact' => 'min:5|max:255',
                'client_email' => 'email',
                'client_phone' => 'min:10|max:15'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            $dataStore->updateData('projects', $data, ['id' => $id]);
            $project = $dataStore->getData('projects', ['id' => $id]);
            Response::updated($project[0]);
            break;

        case 'DELETE':
            if (!$id) {
                ErrorHandler::handleNotFound();
            }

            // Check if project has resources or tasks
            $hasResources = !empty($dataStore->getData('project_resources', ['project_id' => $id]));
            $hasTasks = !empty($dataStore->getData('project_tasks', ['project_id' => $id]));

            if ($hasResources || $hasTasks) {
                ErrorHandler::handle('Cannot delete project with resources or tasks', 400);
            }

            $dataStore->deleteData('projects', ['id' => $id]);
            Response::deleted();
            break;

        default:
            ErrorHandler::handle('Method not allowed', 405);
    }
} catch (\Exception $e) {
    ErrorHandler::handle($e);
}
?> 