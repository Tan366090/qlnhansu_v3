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
                $version = $dataStore->getData('document_versions', ['id' => $id]);
                if (!$version) {
                    ErrorHandler::handleNotFound();
                }
                Response::success($version[0]);
            } else {
                $page = $_GET['page'] ?? 1;
                $perPage = $_GET['per_page'] ?? 10;
                $documentId = $_GET['document_id'] ?? null;
                $version = $_GET['version'] ?? null;
                $status = $_GET['status'] ?? null;
                $startDate = $_GET['start_date'] ?? null;
                $endDate = $_GET['end_date'] ?? null;

                $conditions = [];
                if ($documentId) {
                    $conditions['document_id'] = $documentId;
                }
                if ($version) {
                    $conditions['version'] = $version;
                }
                if ($status) {
                    $conditions['status'] = $status;
                }
                if ($startDate && $endDate) {
                    $conditions['created_at'] = ['BETWEEN', $startDate, $endDate];
                }

                $versions = $dataStore->getAllData('document_versions', [
                    'page' => $page,
                    'per_page' => $perPage,
                    'conditions' => $conditions,
                    'order_by' => 'created_at DESC'
                ]);
                Response::paginated($versions['data'], $versions['total'], $page, $perPage);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $rules = [
                'document_id' => 'required|integer|exists:documents,id',
                'version' => 'required|min:1|max:20',
                'file_url' => 'required|max:255',
                'changes' => 'required|min:10|max:1000',
                'status' => 'required|in:draft,published,archived',
                'created_by' => 'required|integer|exists:users,user_id',
                'notes' => 'max:1000'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            // Check if version already exists
            $existingVersion = $dataStore->getData('document_versions', [
                'document_id' => $data['document_id'],
                'version' => $data['version']
            ]);
            if (!empty($existingVersion)) {
                ErrorHandler::handle('Version already exists', 400);
            }

            // Start transaction
            $dataStore->beginTransaction();

            try {
                $versionId = $dataStore->insertData('document_versions', $data);
                $version = $dataStore->getData('document_versions', ['id' => $versionId]);
                $dataStore->commit();
                Response::created($version[0]);
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
                'version' => 'min:1|max:20',
                'file_url' => 'max:255',
                'changes' => 'min:10|max:1000',
                'status' => 'in:draft,published,archived',
                'created_by' => 'integer|exists:users,user_id',
                'notes' => 'max:1000'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            // Check if version already exists
            if (isset($data['version'])) {
                $existingVersion = $dataStore->getData('document_versions', [
                    'document_id' => $data['document_id'],
                    'version' => $data['version'],
                    'id' => ['!=', $id]
                ]);
                if (!empty($existingVersion)) {
                    ErrorHandler::handle('Version already exists', 400);
                }
            }

            $dataStore->updateData('document_versions', $data, ['id' => $id]);
            $version = $dataStore->getData('document_versions', ['id' => $id]);
            Response::updated($version[0]);
            break;

        case 'DELETE':
            if (!$id) {
                ErrorHandler::handleNotFound();
            }

            $dataStore->deleteData('document_versions', ['id' => $id]);
            Response::deleted();
            break;

        default:
            ErrorHandler::handle('Method not allowed', 405);
    }
} catch (\Exception $e) {
    ErrorHandler::handle($e);
}
?> 