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
                if ($action === 'versions') {
                    $versions = $dataStore->getData('document_versions', ['document_id' => $id]);
                    Response::success($versions);
                } else {
                    $document = $dataStore->getData('documents', ['id' => $id]);
                    if (!$document) {
                        ErrorHandler::handleNotFound();
                    }
                    Response::success($document[0]);
                }
            } else {
                $page = $_GET['page'] ?? 1;
                $perPage = $_GET['per_page'] ?? 10;
                $type = $_GET['type'] ?? null;
                $category = $_GET['category'] ?? null;
                $status = $_GET['status'] ?? null;
                $startDate = $_GET['start_date'] ?? null;
                $endDate = $_GET['end_date'] ?? null;
                $search = $_GET['search'] ?? null;

                $conditions = [];
                if ($type) {
                    $conditions['type'] = $type;
                }
                if ($category) {
                    $conditions['category'] = $category;
                }
                if ($status) {
                    $conditions['status'] = $status;
                }
                if ($startDate && $endDate) {
                    $conditions['created_at'] = ['BETWEEN', $startDate, $endDate];
                }
                if ($search) {
                    $conditions['search'] = $search;
                }

                $documents = $dataStore->getAllData('documents', [
                    'page' => $page,
                    'per_page' => $perPage,
                    'conditions' => $conditions,
                    'order_by' => 'created_at DESC'
                ]);
                Response::paginated($documents['data'], $documents['total'], $page, $perPage);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $rules = [
                'title' => 'required|min:2|max:100',
                'description' => 'required|min:10|max:1000',
                'type' => 'required|in:policy,procedure,form,report,other',
                'category' => 'required|min:2|max:50',
                'file_url' => 'required|max:255',
                'version' => 'required|min:1|max:20',
                'status' => 'required|in:draft,published,archived',
                'created_by' => 'required|integer|exists:users,user_id',
                'notes' => 'max:1000'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            // Start transaction
            $dataStore->beginTransaction();

            try {
                $documentId = $dataStore->insertData('documents', $data);
                $document = $dataStore->getData('documents', ['id' => $documentId]);
                $dataStore->commit();
                Response::created($document[0]);
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
                'type' => 'in:policy,procedure,form,report,other',
                'category' => 'min:2|max:50',
                'file_url' => 'max:255',
                'version' => 'min:1|max:20',
                'status' => 'in:draft,published,archived',
                'created_by' => 'integer|exists:users,user_id',
                'notes' => 'max:1000'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            $dataStore->updateData('documents', $data, ['id' => $id]);
            $document = $dataStore->getData('documents', ['id' => $id]);
            Response::updated($document[0]);
            break;

        case 'DELETE':
            if (!$id) {
                ErrorHandler::handleNotFound();
            }

            // Check if document has versions
            $versions = $dataStore->getData('document_versions', ['document_id' => $id]);
            if (!empty($versions)) {
                ErrorHandler::handle('Cannot delete document with versions', 400);
            }

            $dataStore->deleteData('documents', ['id' => $id]);
            Response::deleted();
            break;

        default:
            ErrorHandler::handle('Method not allowed', 405);
    }
} catch (\Exception $e) {
    ErrorHandler::handle($e);
}
?> 