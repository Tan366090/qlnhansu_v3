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
                if ($action === 'participants') {
                    $participants = $dataStore->getData('activity_participants', ['activity_id' => $id]);
                    Response::success($participants);
                } else {
                    $activity = $dataStore->getData('activities', ['id' => $id]);
                    if (!$activity) {
                        ErrorHandler::handleNotFound();
                    }
                    Response::success($activity[0]);
                }
            } else {
                $page = $_GET['page'] ?? 1;
                $perPage = $_GET['per_page'] ?? 10;
                $type = $_GET['type'] ?? null;
                $status = $_GET['status'] ?? null;
                $startDate = $_GET['start_date'] ?? null;
                $endDate = $_GET['end_date'] ?? null;
                $search = $_GET['search'] ?? null;

                $conditions = [];
                if ($type) {
                    $conditions['type'] = $type;
                }
                if ($status) {
                    $conditions['status'] = $status;
                }
                if ($startDate && $endDate) {
                    $conditions['start_date'] = ['BETWEEN', $startDate, $endDate];
                }
                if ($search) {
                    $conditions['search'] = $search;
                }

                $activities = $dataStore->getAllData('activities', [
                    'page' => $page,
                    'per_page' => $perPage,
                    'conditions' => $conditions
                ]);
                Response::paginated($activities['data'], $activities['total'], $page, $perPage);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $rules = [
                'title' => 'required|min:2|max:100',
                'description' => 'required|min:10|max:1000',
                'type' => 'required|in:meeting,training,event,other',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'location' => 'required|min:2|max:255',
                'organizer_id' => 'required|integer|exists:users,user_id',
                'status' => 'required|in:planned,in_progress,completed,cancelled',
                'max_participants' => 'required|integer|min:1',
                'cost' => 'required|numeric|min:0',
                'notes' => 'max:1000'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            // Start transaction
            $dataStore->beginTransaction();

            try {
                $activityId = $dataStore->insertData('activities', $data);
                $activity = $dataStore->getData('activities', ['id' => $activityId]);
                $dataStore->commit();
                Response::created($activity[0]);
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
                'title' => 'min:2|max:100',
                'description' => 'min:10|max:1000',
                'type' => 'in:meeting,training,event,other',
                'start_date' => 'date',
                'end_date' => 'date|after:start_date',
                'location' => 'min:2|max:255',
                'organizer_id' => 'integer|exists:users,user_id',
                'status' => 'in:planned,in_progress,completed,cancelled',
                'max_participants' => 'integer|min:1',
                'cost' => 'numeric|min:0',
                'notes' => 'max:1000'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            $dataStore->updateData('activities', $data, ['id' => $id]);
            $activity = $dataStore->getData('activities', ['id' => $id]);
            Response::updated($activity[0]);
            break;

        case 'DELETE':
            if (!$id) {
                ErrorHandler::handleNotFound();
            }

            // Check if activity has participants
            $participants = $dataStore->getData('activity_participants', ['activity_id' => $id]);
            if (!empty($participants)) {
                ErrorHandler::handle('Cannot delete activity with participants', 400);
            }

            $dataStore->deleteData('activities', ['id' => $id]);
            Response::deleted();
            break;

        default:
            ErrorHandler::handle('Method not allowed', 405);
    }
} catch (\Exception $e) {
    ErrorHandler::handle($e);
}
?> 