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
                $log = $dataStore->getData('audit_logs', ['id' => $id]);
                if (!$log) {
                    ErrorHandler::handleNotFound();
                }
                Response::success($log[0]);
            } else {
                $page = $_GET['page'] ?? 1;
                $perPage = $_GET['per_page'] ?? 10;
                $userId = $_GET['user_id'] ?? null;
                $action = $_GET['action'] ?? null;
                $table = $_GET['table'] ?? null;
                $startDate = $_GET['start_date'] ?? null;
                $endDate = $_GET['end_date'] ?? null;

                $conditions = [];
                if ($userId) {
                    $conditions['user_id'] = $userId;
                }
                if ($action) {
                    $conditions['action'] = $action;
                }
                if ($table) {
                    $conditions['table_name'] = $table;
                }
                if ($startDate && $endDate) {
                    $conditions['created_at'] = ['BETWEEN', $startDate, $endDate];
                }

                $logs = $dataStore->getAllData('audit_logs', [
                    'page' => $page,
                    'per_page' => $perPage,
                    'conditions' => $conditions,
                    'order_by' => 'created_at DESC'
                ]);
                Response::paginated($logs['data'], $logs['total'], $page, $perPage);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $rules = [
                'user_id' => 'required|integer|exists:users,user_id',
                'action' => 'required|in:create,update,delete,login,logout',
                'table_name' => 'required|min:2|max:50',
                'record_id' => 'required|integer',
                'old_data' => 'json',
                'new_data' => 'json',
                'ip_address' => 'required|ip',
                'user_agent' => 'required|min:2|max:255'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            $logId = $dataStore->insertData('audit_logs', $data);
            $log = $dataStore->getData('audit_logs', ['id' => $logId]);
            Response::created($log[0]);
            break;

        case 'PUT':
            if (!$id) {
                ErrorHandler::handleNotFound();
            }

            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $rules = [
                'user_id' => 'integer|exists:users,user_id',
                'action' => 'in:create,update,delete,login,logout',
                'table_name' => 'min:2|max:50',
                'record_id' => 'integer',
                'old_data' => 'json',
                'new_data' => 'json',
                'ip_address' => 'ip',
                'user_agent' => 'min:2|max:255'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            $dataStore->updateData('audit_logs', $data, ['id' => $id]);
            $log = $dataStore->getData('audit_logs', ['id' => $id]);
            Response::updated($log[0]);
            break;

        case 'DELETE':
            if (!$id) {
                ErrorHandler::handleNotFound();
            }

            $dataStore->deleteData('audit_logs', ['id' => $id]);
            Response::deleted();
            break;

        default:
            ErrorHandler::handle('Method not allowed', 405);
    }
} catch (\Exception $e) {
    ErrorHandler::handle($e);
}
?> 