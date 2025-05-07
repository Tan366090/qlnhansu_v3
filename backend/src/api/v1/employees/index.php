<?php
require_once __DIR__ . '/../../../config/autoload.php';

use App\Middleware\CorsMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\ValidationMiddleware;
use App\Handlers\Response;
use App\Handlers\ErrorHandler;
use App\Services\DataStore;

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Apply CORS middleware
$cors = new CorsMiddleware();
$cors->handle();

// Apply Auth middleware
$auth = new AuthMiddleware();
$user = $auth->handle();

if (!$user) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized'
    ]);
    exit;
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
                if ($action === 'details') {
                    // Get employee details with related data
                    $employee = $dataStore->getData('employees', ['id' => $id]);
                    if (!$employee) {
                        http_response_code(404);
                        echo json_encode([
                            'success' => false,
                            'error' => 'Employee not found'
                        ]);
                        exit;
                    }
                    
                    $employee = $employee[0];
                    
                    // Get related data
                    $employee['department'] = $dataStore->getData('departments', ['id' => $employee['department_id']])[0] ?? null;
                    $employee['position'] = $dataStore->getData('positions', ['id' => $employee['position_id']])[0] ?? null;
                    $employee['contracts'] = $dataStore->getData('contracts', ['employee_id' => $id]);
                    $employee['certificates'] = $dataStore->getData('certificates', ['employee_id' => $id]);
                    $employee['family_members'] = $dataStore->getData('family_members', ['employee_id' => $id]);
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $employee
                    ]);
                } else {
                    $employee = $dataStore->getData('employees', ['id' => $id]);
                    if (!$employee) {
                        http_response_code(404);
                        echo json_encode([
                            'success' => false,
                            'error' => 'Employee not found'
                        ]);
                        exit;
                    }
                    echo json_encode([
                        'success' => true,
                        'data' => $employee[0]
                    ]);
                }
            } else {
                $page = $_GET['page'] ?? 1;
                $perPage = $_GET['per_page'] ?? 10;
                $departmentId = $_GET['department_id'] ?? null;
                $positionId = $_GET['position_id'] ?? null;
                $status = $_GET['status'] ?? null;
                $search = $_GET['search'] ?? null;

                $conditions = [];
                if ($departmentId) {
                    $conditions['department_id'] = $departmentId;
                }
                if ($positionId) {
                    $conditions['position_id'] = $positionId;
                }
                if ($status) {
                    $conditions['status'] = $status;
                }
                if ($search) {
                    $conditions['search'] = $search;
                }

                $employees = $dataStore->getAllData('employees', [
                    'page' => $page,
                    'per_page' => $perPage,
                    'conditions' => $conditions
                ]);
                echo json_encode([
                    'success' => true,
                    'data' => $employees['data'],
                    'total' => $employees['total'],
                    'page' => $page,
                    'per_page' => $perPage
                ]);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid JSON data',
                    'details' => json_last_error_msg()
                ]);
                exit;
            }
            
            // Log received data for debugging
            error_log('Received data: ' . print_r($data, true));
            
            // Validate input
            $rules = [
                'fullName' => 'required|min:2|max:100',
                'phone' => 'required|min:10|max:15',
                'department' => 'required|integer|exists:departments,id',
                'position' => 'required|integer|exists:positions,id',
                'birthDate' => 'required|date',
                'gender' => 'required|in:male,female,other',
                'address' => 'required|min:5|max:255',
                'idNumber' => 'required|min:9|max:12',
                'startDate' => 'required|date',
                'password' => 'required|min:6'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Validation failed',
                    'errors' => $validator->getErrors()
                ]);
                exit;
            }

            try {
                // Start transaction
                $dataStore->beginTransaction();

                // Generate default email if not provided
                if (empty($data['email'])) {
                    $fullName = strtolower(str_replace(' ', '', $data['fullName']));
                    $phoneLast5 = substr($data['phone'], -5);
                    $data['email'] = $fullName . $phoneLast5 . '@gmail.com';
                }

                // 1. Create user record
                $userData = [
                    'username' => strtolower(str_replace(' ', '.', $data['fullName'])) . '_' . uniqid(),
                    'email' => $data['email'],
                    'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
                    'role_id' => 4, // Default employee role
                    'is_active' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $userId = $dataStore->insertData('users', $userData);

                // 2. Create user profile
                $profileData = [
                    'user_id' => $userId,
                    'full_name' => $data['fullName'],
                    'phone_number' => $data['phone'],
                    'email' => $data['email'],
                    'date_of_birth' => $data['birthDate'],
                    'gender' => $data['gender'],
                    'permanent_address' => $data['address'],
                    'identity_card' => $data['idNumber'],
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $dataStore->insertData('user_profiles', $profileData);

                // 3. Generate employee code
                $employeeCode = 'NV' . str_pad($userId, 6, '0', STR_PAD_LEFT);

                // 4. Create employee record
                $employeeData = [
                    'user_id' => $userId,
                    'employee_code' => $employeeCode,
                    'department_id' => $data['department'],
                    'position_id' => $data['position'],
                    'employment_type' => 'full_time', // Default to full time
                    'hire_date' => $data['startDate'],
                    'contract_type' => 'permanent', // Default to permanent
                    'contract_start_date' => $data['startDate'],
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $employeeId = $dataStore->insertData('employees', $employeeData);

                // Commit transaction
                $dataStore->commit();

                // Get the created employee with details
                $employee = $dataStore->getData('employees', ['id' => $employeeId]);
                if (!$employee) {
                    throw new Exception('Failed to retrieve created employee');
                }
                
                http_response_code(201);
                echo json_encode([
                    'success' => true,
                    'message' => 'Employee created successfully',
                    'data' => $employee[0],
                    'generated_email' => $data['email']
                ]);
            } catch (Exception $e) {
                // Rollback transaction on error
                $dataStore->rollback();
                error_log('Error creating employee: ' . $e->getMessage());
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'Failed to create employee',
                    'details' => $e->getMessage()
                ]);
            }
            break;

        case 'PUT':
            if (!$id) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Employee ID is required'
                ]);
                exit;
            }

            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid JSON data'
                ]);
                exit;
            }
            
            // Validate input
            $rules = [
                'username' => 'min:3|max:50|unique:users',
                'email' => 'email|unique:users',
                'password' => 'min:6',
                'full_name' => 'min:2|max:100',
                'phone_number' => 'min:10|max:15',
                'date_of_birth' => 'date',
                'gender' => 'in:male,female,other',
                'permanent_address' => 'min:5|max:255',
                'current_address' => 'min:5|max:255',
                'bank_account_number' => 'min:10|max:20',
                'bank_name' => 'min:2|max:100',
                'tax_code' => 'min:10|max:20',
                'department_id' => 'integer|exists:departments,id',
                'position_id' => 'integer|exists:positions,id',
                'employment_type' => 'in:full_time,part_time,contract,intern',
                'join_date' => 'date',
                'contract_start_date' => 'date',
                'contract_end_date' => 'date',
                'probation_end_date' => 'date',
                'manager_id' => 'integer|exists:employees,id',
                'status' => 'in:active,inactive,on_leave,terminated'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Validation failed',
                    'errors' => $validator->getErrors()
                ]);
                exit;
            }

            try {
                // Start transaction
                $dataStore->beginTransaction();

                // Get employee to update
                $employee = $dataStore->getData('employees', ['id' => $id]);
                if (!$employee) {
                    http_response_code(404);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Employee not found'
                    ]);
                    exit;
                }

                $employee = $employee[0];
                $userId = $employee['user_id'];

                // Update user record if needed
                if (isset($data['username']) || isset($data['email']) || isset($data['password'])) {
                    $userData = [];
                    if (isset($data['username'])) $userData['username'] = $data['username'];
                    if (isset($data['email'])) $userData['email'] = $data['email'];
                    if (isset($data['password'])) $userData['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
                    $userData['updated_at'] = date('Y-m-d H:i:s');
                    $dataStore->updateData('users', $userData, ['id' => $userId]);
                }

                // Update user profile if needed
                if (isset($data['full_name']) || isset($data['phone_number']) || isset($data['email']) || 
                    isset($data['date_of_birth']) || isset($data['gender']) || isset($data['permanent_address']) || 
                    isset($data['current_address']) || isset($data['bank_account_number']) || 
                    isset($data['bank_name']) || isset($data['tax_code'])) {
                    $profileData = [];
                    if (isset($data['full_name'])) $profileData['full_name'] = $data['full_name'];
                    if (isset($data['phone_number'])) $profileData['phone_number'] = $data['phone_number'];
                    if (isset($data['email'])) $profileData['email'] = $data['email'];
                    if (isset($data['date_of_birth'])) $profileData['date_of_birth'] = $data['date_of_birth'];
                    if (isset($data['gender'])) $profileData['gender'] = $data['gender'];
                    if (isset($data['permanent_address'])) $profileData['permanent_address'] = $data['permanent_address'];
                    if (isset($data['current_address'])) $profileData['current_address'] = $data['current_address'];
                    if (isset($data['bank_account_number'])) $profileData['bank_account_number'] = $data['bank_account_number'];
                    if (isset($data['bank_name'])) $profileData['bank_name'] = $data['bank_name'];
                    if (isset($data['tax_code'])) $profileData['tax_code'] = $data['tax_code'];
                    $profileData['updated_at'] = date('Y-m-d H:i:s');
                    $dataStore->updateData('user_profiles', $profileData, ['user_id' => $userId]);
                }

                // Update employee record if needed
                if (isset($data['department_id']) || isset($data['position_id']) || isset($data['employment_type']) || 
                    isset($data['join_date']) || isset($data['contract_start_date']) || isset($data['contract_end_date']) || 
                    isset($data['probation_end_date']) || isset($data['manager_id']) || isset($data['status'])) {
                    $employeeData = [];
                    if (isset($data['department_id'])) $employeeData['department_id'] = $data['department_id'];
                    if (isset($data['position_id'])) $employeeData['position_id'] = $data['position_id'];
                    if (isset($data['employment_type'])) $employeeData['employment_type'] = $data['employment_type'];
                    if (isset($data['join_date'])) $employeeData['join_date'] = $data['join_date'];
                    if (isset($data['contract_start_date'])) $employeeData['contract_start_date'] = $data['contract_start_date'];
                    if (isset($data['contract_end_date'])) $employeeData['contract_end_date'] = $data['contract_end_date'];
                    if (isset($data['probation_end_date'])) $employeeData['probation_end_date'] = $data['probation_end_date'];
                    if (isset($data['manager_id'])) $employeeData['manager_id'] = $data['manager_id'];
                    if (isset($data['status'])) $employeeData['status'] = $data['status'];
                    $employeeData['updated_at'] = date('Y-m-d H:i:s');
                    $dataStore->updateData('employees', $employeeData, ['id' => $id]);
                }

                // Commit transaction
                $dataStore->commit();

                // Get the updated employee with details
                $employee = $dataStore->getData('employees', ['id' => $id]);
                echo json_encode([
                    'success' => true,
                    'data' => $employee[0]
                ]);
            } catch (Exception $e) {
                // Rollback transaction on error
                $dataStore->rollback();
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
            }
            break;

        case 'DELETE':
            if (!$id) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Employee ID is required'
                ]);
                exit;
            }

            try {
                // Start transaction
                $dataStore->beginTransaction();

                // Get employee to delete
                $employee = $dataStore->getData('employees', ['id' => $id]);
                if (!$employee) {
                    http_response_code(404);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Employee not found'
                    ]);
                    exit;
                }

                $employee = $employee[0];
                $userId = $employee['user_id'];

                // Check if employee has related records
                $hasRelatedRecords = false;
                $relatedTables = ['contracts', 'certificates', 'family_members', 'attendance', 'salaries'];
                
                foreach ($relatedTables as $table) {
                    $records = $dataStore->getData($table, ['employee_id' => $id]);
                    if (!empty($records)) {
                        $hasRelatedRecords = true;
                        break;
                    }
                }

                if ($hasRelatedRecords) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Cannot delete employee with related records'
                    ]);
                    exit;
                }

                // Delete employee record
                $dataStore->deleteData('employees', ['id' => $id]);

                // Delete user profile
                $dataStore->deleteData('user_profiles', ['user_id' => $userId]);

                // Delete user record
                $dataStore->deleteData('users', ['id' => $userId]);

                // Commit transaction
                $dataStore->commit();

                echo json_encode([
                    'success' => true,
                    'message' => 'Employee deleted successfully'
                ]);
            } catch (Exception $e) {
                // Rollback transaction on error
                $dataStore->rollback();
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'error' => 'Method not allowed'
            ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 