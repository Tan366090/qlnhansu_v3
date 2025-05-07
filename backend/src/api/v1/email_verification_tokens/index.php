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
                $token = $dataStore->getData('email_verification_tokens', ['id' => $id]);
                if (!$token) {
                    ErrorHandler::handleNotFound();
                }
                Response::success($token[0]);
            } else {
                $page = $_GET['page'] ?? 1;
                $perPage = $_GET['per_page'] ?? 10;
                $userId = $_GET['user_id'] ?? null;
                $email = $_GET['email'] ?? null;
                $status = $_GET['status'] ?? null;
                $startDate = $_GET['start_date'] ?? null;
                $endDate = $_GET['end_date'] ?? null;

                $conditions = [];
                if ($userId) {
                    $conditions['user_id'] = $userId;
                }
                if ($email) {
                    $conditions['email'] = $email;
                }
                if ($status) {
                    $conditions['status'] = $status;
                }
                if ($startDate && $endDate) {
                    $conditions['created_at'] = ['BETWEEN', $startDate, $endDate];
                }

                $tokens = $dataStore->getAllData('email_verification_tokens', [
                    'page' => $page,
                    'per_page' => $perPage,
                    'conditions' => $conditions,
                    'order_by' => 'created_at DESC'
                ]);
                Response::paginated($tokens['data'], $tokens['total'], $page, $perPage);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $rules = [
                'user_id' => 'required|integer|exists:users,user_id',
                'email' => 'required|email|max:255',
                'token' => 'required|min:32|max:255',
                'expires_at' => 'required|date|after:now',
                'status' => 'required|in:pending,verified,expired'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            // Check if user already has a pending token
            $existingToken = $dataStore->getData('email_verification_tokens', [
                'user_id' => $data['user_id'],
                'status' => 'pending'
            ]);
            if (!empty($existingToken)) {
                ErrorHandler::handle('User already has a pending verification token', 400);
            }

            // Start transaction
            $dataStore->beginTransaction();

            try {
                $tokenId = $dataStore->insertData('email_verification_tokens', $data);
                $token = $dataStore->getData('email_verification_tokens', ['id' => $tokenId]);
                $dataStore->commit();
                Response::created($token[0]);
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
                'status' => 'in:pending,verified,expired',
                'expires_at' => 'date|after:now'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            // Check if token is already verified
            $token = $dataStore->getData('email_verification_tokens', ['id' => $id]);
            if ($token[0]['status'] === 'verified') {
                ErrorHandler::handle('Token is already verified', 400);
            }

            $dataStore->updateData('email_verification_tokens', $data, ['id' => $id]);
            $token = $dataStore->getData('email_verification_tokens', ['id' => $id]);
            Response::updated($token[0]);
            break;

        case 'DELETE':
            if (!$id) {
                ErrorHandler::handleNotFound();
            }

            $dataStore->deleteData('email_verification_tokens', ['id' => $id]);
            Response::deleted();
            break;

        default:
            ErrorHandler::handle('Method not allowed', 405);
    }
} catch (\Exception $e) {
    ErrorHandler::handle($e);
}
?> 