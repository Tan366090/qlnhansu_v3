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
                if ($action === 'profile') {
                    $profile = $dataStore->getData('user_profiles', ['user_id' => $id]);
                    if (!$profile) {
                        ErrorHandler::handleNotFound();
                    }
                    Response::success($profile[0]);
                } else if ($action === 'permissions') {
                    $permissions = $dataStore->getData('role_permissions', ['role_id' => $id]);
                    Response::success($permissions);
                } else {
                    $user = $dataStore->getData('users', ['user_id' => $id]);
                    if (!$user) {
                        ErrorHandler::handleNotFound();
                    }
                    Response::success($user[0]);
                }
            } else {
                $page = $_GET['page'] ?? 1;
                $perPage = $_GET['per_page'] ?? 10;
                $roleId = $_GET['role_id'] ?? null;
                $status = $_GET['status'] ?? null;
                $search = $_GET['search'] ?? null;

                $conditions = [];
                if ($roleId) {
                    $conditions['role_id'] = $roleId;
                }
                if ($status) {
                    $conditions['status'] = $status;
                }
                if ($search) {
                    $conditions['search'] = $search;
                }

                $users = $dataStore->getAllData('users', [
                    'page' => $page,
                    'per_page' => $perPage,
                    'conditions' => $conditions
                ]);
                Response::paginated($users['data'], $users['total'], $page, $perPage);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $rules = [
                'username' => 'required|min:3|max:50|unique:users',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6|max:100',
                'role_id' => 'required|integer|exists:roles,id',
                'status' => 'required|in:active,inactive,locked',
                'first_name' => 'required|min:2|max:50',
                'last_name' => 'required|min:2|max:50',
                'phone' => 'required|min:10|max:15',
                'address' => 'required|min:5|max:255',
                'gender' => 'required|in:male,female,other',
                'birth_date' => 'required|date',
                'avatar' => 'max:255'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            // Hash password
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

            // Start transaction
            $dataStore->beginTransaction();

            try {
                // Insert user
                $userId = $dataStore->insertData('users', [
                    'username' => $data['username'],
                    'email' => $data['email'],
                    'password' => $data['password'],
                    'role_id' => $data['role_id'],
                    'status' => $data['status']
                ]);

                // Insert profile
                $dataStore->insertData('user_profiles', [
                    'user_id' => $userId,
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'phone' => $data['phone'],
                    'address' => $data['address'],
                    'gender' => $data['gender'],
                    'birth_date' => $data['birth_date'],
                    'avatar' => $data['avatar'] ?? null
                ]);

                $dataStore->commit();

                $user = $dataStore->getData('users', ['user_id' => $userId]);
                Response::created($user[0]);
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
                'username' => 'min:3|max:50|unique:users',
                'email' => 'email|unique:users',
                'password' => 'min:6|max:100',
                'role_id' => 'integer|exists:roles,id',
                'status' => 'in:active,inactive,locked',
                'first_name' => 'min:2|max:50',
                'last_name' => 'min:2|max:50',
                'phone' => 'min:10|max:15',
                'address' => 'min:5|max:255',
                'gender' => 'in:male,female,other',
                'birth_date' => 'date',
                'avatar' => 'max:255'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            // Start transaction
            $dataStore->beginTransaction();

            try {
                $userData = [];
                $profileData = [];

                // Separate user and profile data
                $userFields = ['username', 'email', 'password', 'role_id', 'status'];
                $profileFields = ['first_name', 'last_name', 'phone', 'address', 'gender', 'birth_date', 'avatar'];

                foreach ($data as $key => $value) {
                    if (in_array($key, $userFields)) {
                        if ($key === 'password') {
                            $userData[$key] = password_hash($value, PASSWORD_DEFAULT);
                        } else {
                            $userData[$key] = $value;
                        }
                    } else if (in_array($key, $profileFields)) {
                        $profileData[$key] = $value;
                    }
                }

                // Update user
                if (!empty($userData)) {
                    $dataStore->updateData('users', $userData, ['user_id' => $id]);
                }

                // Update profile
                if (!empty($profileData)) {
                    $dataStore->updateData('user_profiles', $profileData, ['user_id' => $id]);
                }

                $dataStore->commit();

                $user = $dataStore->getData('users', ['user_id' => $id]);
                Response::updated($user[0]);
            } catch (\Exception $e) {
                $dataStore->rollback();
                throw $e;
            }
            break;

        case 'DELETE':
            if (!$id) {
                ErrorHandler::handleNotFound();
            }

            // Check if user has related records
            $hasRelatedRecords = false;
            $relatedTables = ['employees', 'audit_logs', 'notifications'];
            
            foreach ($relatedTables as $table) {
                $records = $dataStore->getData($table, ['user_id' => $id]);
                if (!empty($records)) {
                    $hasRelatedRecords = true;
                    break;
                }
            }

            if ($hasRelatedRecords) {
                ErrorHandler::handle('Cannot delete user with related records', 400);
            }

            // Start transaction
            $dataStore->beginTransaction();

            try {
                // Delete profile first
                $dataStore->deleteData('user_profiles', ['user_id' => $id]);
                // Then delete user
                $dataStore->deleteData('users', ['user_id' => $id]);

                $dataStore->commit();
                Response::deleted();
            } catch (\Exception $e) {
                $dataStore->rollback();
                throw $e;
            }
            break;

        default:
            ErrorHandler::handle('Method not allowed', 405);
    }
} catch (\Exception $e) {
    ErrorHandler::handle($e);
}
?> 