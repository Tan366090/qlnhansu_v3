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
                if ($action === 'balance') {
                    // Calculate leave balance
                    $employee = $dataStore->getData('employees', ['id' => $id]);
                    if (!$employee) {
                        ErrorHandler::handleNotFound();
                    }

                    $currentYear = date('Y');
                    $leaves = $dataStore->getData('leaves', [
                        'employee_id' => $id,
                        'status' => 'approved',
                        'start_date' => ['>=', $currentYear . '-01-01']
                    ]);

                    $totalDays = 0;
                    foreach ($leaves as $leave) {
                        $start = new DateTime($leave['start_date']);
                        $end = new DateTime($leave['end_date']);
                        $totalDays += $end->diff($start)->days + 1;
                    }

                    $balance = [
                        'total_allocated' => 12, // Default annual leave days
                        'used_days' => $totalDays,
                        'remaining_days' => 12 - $totalDays
                    ];

                    Response::success($balance);
                } else {
                    $leave = $dataStore->getData('leaves', ['id' => $id]);
                    if (!$leave) {
                        ErrorHandler::handleNotFound();
                    }
                    Response::success($leave[0]);
                }
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
                    $conditions['start_date'] = ['BETWEEN', $startDate, $endDate];
                }

                $leaves = $dataStore->getAllData('leaves', [
                    'page' => $page,
                    'per_page' => $perPage,
                    'conditions' => $conditions
                ]);
                Response::paginated($leaves['data'], $leaves['total'], $page, $perPage);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $rules = [
                'employee_id' => 'required|integer|exists:employees,id',
                'type' => 'required|in:annual,sick,unpaid,compassionate,maternity,paternity',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'reason' => 'required|min:10|max:500',
                'status' => 'required|in:pending,approved,rejected',
                'approved_by' => 'integer|exists:users,user_id',
                'approved_at' => 'date',
                'notes' => 'max:500'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            // Check leave balance for annual leave
            if ($data['type'] === 'annual') {
                $currentYear = date('Y');
                $leaves = $dataStore->getData('leaves', [
                    'employee_id' => $data['employee_id'],
                    'type' => 'annual',
                    'status' => 'approved',
                    'start_date' => ['>=', $currentYear . '-01-01']
                ]);

                $totalDays = 0;
                foreach ($leaves as $leave) {
                    $start = new DateTime($leave['start_date']);
                    $end = new DateTime($leave['end_date']);
                    $totalDays += $end->diff($start)->days + 1;
                }

                $requestedStart = new DateTime($data['start_date']);
                $requestedEnd = new DateTime($data['end_date']);
                $requestedDays = $requestedEnd->diff($requestedStart)->days + 1;

                if ($totalDays + $requestedDays > 12) {
                    ErrorHandler::handle('Insufficient annual leave balance', 400);
                }
            }

            // Check for overlapping leaves
            $overlappingLeaves = $dataStore->getData('leaves', [
                'employee_id' => $data['employee_id'],
                'status' => ['IN', ['pending', 'approved']],
                'start_date' => ['<=', $data['end_date']],
                'end_date' => ['>=', $data['start_date']]
            ]);

            if (!empty($overlappingLeaves)) {
                ErrorHandler::handle('Leave request overlaps with existing approved or pending leaves', 400);
            }

            $leaveId = $dataStore->insertData('leaves', $data);
            $leave = $dataStore->getData('leaves', ['id' => $leaveId]);
            Response::created($leave[0]);
            break;

        case 'PUT':
            if (!$id) {
                ErrorHandler::handleNotFound();
            }

            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $rules = [
                'type' => 'in:annual,sick,unpaid,compassionate,maternity,paternity',
                'start_date' => 'date',
                'end_date' => 'date|after:start_date',
                'reason' => 'min:10|max:500',
                'status' => 'in:pending,approved,rejected',
                'approved_by' => 'integer|exists:users,user_id',
                'approved_at' => 'date',
                'notes' => 'max:500'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            // If updating status to approved, check leave balance
            if (isset($data['status']) && $data['status'] === 'approved') {
                $leave = $dataStore->getData('leaves', ['id' => $id])[0];
                
                if ($leave['type'] === 'annual') {
                    $currentYear = date('Y');
                    $leaves = $dataStore->getData('leaves', [
                        'employee_id' => $leave['employee_id'],
                        'type' => 'annual',
                        'status' => 'approved',
                        'start_date' => ['>=', $currentYear . '-01-01'],
                        'id' => ['!=', $id]
                    ]);

                    $totalDays = 0;
                    foreach ($leaves as $l) {
                        $start = new DateTime($l['start_date']);
                        $end = new DateTime($l['end_date']);
                        $totalDays += $end->diff($start)->days + 1;
                    }

                    $requestedStart = new DateTime($leave['start_date']);
                    $requestedEnd = new DateTime($leave['end_date']);
                    $requestedDays = $requestedEnd->diff($requestedStart)->days + 1;

                    if ($totalDays + $requestedDays > 12) {
                        ErrorHandler::handle('Insufficient annual leave balance', 400);
                    }
                }
            }

            $dataStore->updateData('leaves', $data, ['id' => $id]);
            $leave = $dataStore->getData('leaves', ['id' => $id]);
            Response::updated($leave[0]);
            break;

        case 'DELETE':
            if (!$id) {
                ErrorHandler::handleNotFound();
            }

            // Check if leave is approved
            $leave = $dataStore->getData('leaves', ['id' => $id]);
            if ($leave[0]['status'] === 'approved') {
                ErrorHandler::handle('Cannot delete approved leave', 400);
            }

            $dataStore->deleteData('leaves', ['id' => $id]);
            Response::deleted();
            break;

        default:
            ErrorHandler::handle('Method not allowed', 405);
    }
} catch (\Exception $e) {
    ErrorHandler::handle($e);
}
?> 