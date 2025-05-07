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
                $bonus = $dataStore->getData('bonuses', ['id' => $id]);
                if (!$bonus) {
                    ErrorHandler::handleNotFound();
                }
                Response::success($bonus[0]);
            } else {
                $page = $_GET['page'] ?? 1;
                $perPage = $_GET['per_page'] ?? 10;
                $employeeId = $_GET['employee_id'] ?? null;
                $type = $_GET['type'] ?? null;
                $status = $_GET['status'] ?? null;
                $startDate = $_GET['start_date'] ?? null;
                $endDate = $_GET['end_date'] ?? null;

                $conditions = [];
                if ($employeeId) {
                    $conditions['employee_id'] = $employeeId;
                }
                if ($type) {
                    $conditions['type'] = $type;
                }
                if ($status) {
                    $conditions['status'] = $status;
                }
                if ($startDate && $endDate) {
                    $conditions['date'] = ['BETWEEN', $startDate, $endDate];
                }

                $bonuses = $dataStore->getAllData('bonuses', [
                    'page' => $page,
                    'per_page' => $perPage,
                    'conditions' => $conditions,
                    'order_by' => 'date DESC'
                ]);
                Response::paginated($bonuses['data'], $bonuses['total'], $page, $perPage);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $rules = [
                'employee_id' => 'required|integer|exists:employees,id',
                'type' => 'required|in:performance,attendance,project,holiday,other',
                'amount' => 'required|numeric|min:0',
                'date' => 'required|date',
                'status' => 'required|in:pending,approved,paid',
                'reason' => 'required|min:10|max:1000',
                'approved_by' => 'required|integer|exists:users,user_id',
                'approved_at' => 'date',
                'payment_date' => 'date|after:date',
                'notes' => 'max:1000'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            // Start transaction
            $dataStore->beginTransaction();

            try {
                $bonusId = $dataStore->insertData('bonuses', $data);
                $bonus = $dataStore->getData('bonuses', ['id' => $bonusId]);
                $dataStore->commit();
                Response::created($bonus[0]);
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
                'employee_id' => 'integer|exists:employees,id',
                'type' => 'in:performance,attendance,project,holiday,other',
                'amount' => 'numeric|min:0',
                'date' => 'date',
                'status' => 'in:pending,approved,paid',
                'reason' => 'min:10|max:1000',
                'approved_by' => 'integer|exists:users,user_id',
                'approved_at' => 'date',
                'payment_date' => 'date|after:date',
                'notes' => 'max:1000'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            $dataStore->updateData('bonuses', $data, ['id' => $id]);
            $bonus = $dataStore->getData('bonuses', ['id' => $id]);
            Response::updated($bonus[0]);
            break;

        case 'DELETE':
            if (!$id) {
                ErrorHandler::handleNotFound();
            }

            // Check if bonus is already paid
            $bonus = $dataStore->getData('bonuses', ['id' => $id]);
            if ($bonus[0]['status'] === 'paid') {
                ErrorHandler::handle('Cannot delete paid bonus', 400);
            }

            $dataStore->deleteData('bonuses', ['id' => $id]);
            Response::deleted();
            break;

        default:
            ErrorHandler::handle('Method not allowed', 405);
    }
} catch (\Exception $e) {
    ErrorHandler::handle($e);
}
?> 