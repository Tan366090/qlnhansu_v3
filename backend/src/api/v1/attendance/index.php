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
                $attendance = $dataStore->getData('attendance', ['id' => $id]);
                if (!$attendance) {
                    ErrorHandler::handleNotFound();
                }
                Response::success($attendance[0]);
            } else {
                $page = $_GET['page'] ?? 1;
                $perPage = $_GET['per_page'] ?? 10;
                $employeeId = $_GET['employee_id'] ?? null;
                $startDate = $_GET['start_date'] ?? null;
                $endDate = $_GET['end_date'] ?? null;

                $conditions = [];
                if ($employeeId) {
                    $conditions['employee_id'] = $employeeId;
                }
                if ($startDate && $endDate) {
                    $conditions['date'] = ['BETWEEN', $startDate, $endDate];
                }

                $attendance = $dataStore->getAllData('attendance', [
                    'page' => $page,
                    'per_page' => $perPage,
                    'conditions' => $conditions
                ]);
                Response::paginated($attendance['data'], $attendance['total'], $page, $perPage);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $rules = [
                'employee_id' => 'required|integer',
                'date' => 'required|date',
                'check_in' => 'required|date_format:H:i:s',
                'check_out' => 'date_format:H:i:s',
                'status' => 'required|in:present,absent,late,early_leave',
                'notes' => 'max:500'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            // Check if attendance record already exists for this employee and date
            $existing = $dataStore->getData('attendance', [
                'employee_id' => $data['employee_id'],
                'date' => $data['date']
            ]);
            if (!empty($existing)) {
                ErrorHandler::handle('Attendance record already exists for this date', 400);
            }

            $attendanceId = $dataStore->insertData('attendance', $data);
            $attendance = $dataStore->getData('attendance', ['id' => $attendanceId]);
            Response::created($attendance[0]);
            break;

        case 'PUT':
            if (!$id) {
                ErrorHandler::handleNotFound();
            }

            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $rules = [
                'check_in' => 'date_format:H:i:s',
                'check_out' => 'date_format:H:i:s',
                'status' => 'in:present,absent,late,early_leave',
                'notes' => 'max:500'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            $dataStore->updateData('attendance', $data, ['id' => $id]);
            $attendance = $dataStore->getData('attendance', ['id' => $id]);
            Response::updated($attendance[0]);
            break;

        case 'DELETE':
            if (!$id) {
                ErrorHandler::handleNotFound();
            }

            $dataStore->deleteData('attendance', ['id' => $id]);
            Response::deleted();
            break;

        default:
            ErrorHandler::handle('Method not allowed', 405);
    }
} catch (\Exception $e) {
    ErrorHandler::handle($e);
}
?> 