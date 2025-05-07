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
                if ($action === 'history') {
                    $history = $dataStore->getData('salary_history', ['employee_id' => $id]);
                    Response::success($history);
                } else {
                    $salary = $dataStore->getData('salaries', ['id' => $id]);
                    if (!$salary) {
                        ErrorHandler::handleNotFound();
                    }
                    Response::success($salary[0]);
                }
            } else {
                $page = $_GET['page'] ?? 1;
                $perPage = $_GET['per_page'] ?? 10;
                $employeeId = $_GET['employee_id'] ?? null;
                $month = $_GET['month'] ?? null;
                $year = $_GET['year'] ?? null;

                $conditions = [];
                if ($employeeId) {
                    $conditions['employee_id'] = $employeeId;
                }
                if ($month && $year) {
                    $conditions['month'] = $month;
                    $conditions['year'] = $year;
                }

                $salaries = $dataStore->getAllData('salaries', [
                    'page' => $page,
                    'per_page' => $perPage,
                    'conditions' => $conditions
                ]);
                Response::paginated($salaries['data'], $salaries['total'], $page, $perPage);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $rules = [
                'employee_id' => 'required|integer',
                'month' => 'required|integer|min:1|max:12',
                'year' => 'required|integer|min:2000',
                'basic_salary' => 'required|numeric|min:0',
                'allowances' => 'required|numeric|min:0',
                'deductions' => 'required|numeric|min:0',
                'bonuses' => 'required|numeric|min:0',
                'tax' => 'required|numeric|min:0',
                'net_salary' => 'required|numeric|min:0',
                'status' => 'required|in:pending,approved,paid'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            // Check if salary record already exists for this employee, month and year
            $existing = $dataStore->getData('salaries', [
                'employee_id' => $data['employee_id'],
                'month' => $data['month'],
                'year' => $data['year']
            ]);
            if (!empty($existing)) {
                ErrorHandler::handle('Salary record already exists for this period', 400);
            }

            $salaryId = $dataStore->insertData('salaries', $data);
            $salary = $dataStore->getData('salaries', ['id' => $salaryId]);
            Response::created($salary[0]);
            break;

        case 'PUT':
            if (!$id) {
                ErrorHandler::handleNotFound();
            }

            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $rules = [
                'basic_salary' => 'numeric|min:0',
                'allowances' => 'numeric|min:0',
                'deductions' => 'numeric|min:0',
                'bonuses' => 'numeric|min:0',
                'tax' => 'numeric|min:0',
                'net_salary' => 'numeric|min:0',
                'status' => 'in:pending,approved,paid'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            $dataStore->updateData('salaries', $data, ['id' => $id]);
            $salary = $dataStore->getData('salaries', ['id' => $id]);
            Response::updated($salary[0]);
            break;

        case 'DELETE':
            if (!$id) {
                ErrorHandler::handleNotFound();
            }

            $dataStore->deleteData('salaries', ['id' => $id]);
            Response::deleted();
            break;

        default:
            ErrorHandler::handle('Method not allowed', 405);
    }
} catch (\Exception $e) {
    ErrorHandler::handle($e);
}
?> 