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
                if ($action === 'interviews') {
                    $interviews = $dataStore->getData('interviews', ['candidate_id' => $id]);
                    Response::success($interviews);
                } else if ($action === 'documents') {
                    $documents = $dataStore->getData('candidate_documents', ['candidate_id' => $id]);
                    Response::success($documents);
                } else {
                    $candidate = $dataStore->getData('candidates', ['id' => $id]);
                    if (!$candidate) {
                        ErrorHandler::handleNotFound();
                    }
                    Response::success($candidate[0]);
                }
            } else {
                $page = $_GET['page'] ?? 1;
                $perPage = $_GET['per_page'] ?? 10;
                $jobPositionId = $_GET['job_position_id'] ?? null;
                $status = $_GET['status'] ?? null;
                $startDate = $_GET['start_date'] ?? null;
                $endDate = $_GET['end_date'] ?? null;
                $search = $_GET['search'] ?? null;

                $conditions = [];
                if ($jobPositionId) {
                    $conditions['job_position_id'] = $jobPositionId;
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

                $candidates = $dataStore->getAllData('candidates', [
                    'page' => $page,
                    'per_page' => $perPage,
                    'conditions' => $conditions,
                    'order_by' => 'created_at DESC'
                ]);
                Response::paginated($candidates['data'], $candidates['total'], $page, $perPage);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            $rules = [
                'first_name' => 'required|min:2|max:50',
                'last_name' => 'required|min:2|max:50',
                'email' => 'required|email|unique:candidates',
                'phone' => 'required|min:10|max:15',
                'address' => 'required|min:5|max:255',
                'gender' => 'required|in:male,female,other',
                'birth_date' => 'required|date',
                'job_position_id' => 'required|integer|exists:job_positions,id',
                'status' => 'required|in:new,reviewing,interviewing,offered,hired,rejected',
                'source' => 'required|in:website,referral,agency,other',
                'resume_url' => 'required|max:255',
                'cover_letter' => 'max:2000',
                'notes' => 'max:1000'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            // Start transaction
            $dataStore->beginTransaction();

            try {
                $candidateId = $dataStore->insertData('candidates', $data);
                $candidate = $dataStore->getData('candidates', ['id' => $candidateId]);
                $dataStore->commit();
                Response::created($candidate[0]);
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
                'first_name' => 'min:2|max:50',
                'last_name' => 'min:2|max:50',
                'email' => 'email|unique:candidates',
                'phone' => 'min:10|max:15',
                'address' => 'min:5|max:255',
                'gender' => 'in:male,female,other',
                'birth_date' => 'date',
                'job_position_id' => 'integer|exists:job_positions,id',
                'status' => 'in:new,reviewing,interviewing,offered,hired,rejected',
                'source' => 'in:website,referral,agency,other',
                'resume_url' => 'max:255',
                'cover_letter' => 'max:2000',
                'notes' => 'max:1000'
            ];
            
            $validator = new ValidationMiddleware($rules);
            if (!$validator->validate($data)) {
                ErrorHandler::handleValidationError($validator->getErrors());
            }

            $dataStore->updateData('candidates', $data, ['id' => $id]);
            $candidate = $dataStore->getData('candidates', ['id' => $id]);
            Response::updated($candidate[0]);
            break;

        case 'DELETE':
            if (!$id) {
                ErrorHandler::handleNotFound();
            }

            // Check if candidate has interviews or documents
            $hasInterviews = !empty($dataStore->getData('interviews', ['candidate_id' => $id]));
            $hasDocuments = !empty($dataStore->getData('candidate_documents', ['candidate_id' => $id]));

            if ($hasInterviews || $hasDocuments) {
                ErrorHandler::handle('Cannot delete candidate with interviews or documents', 400);
            }

            $dataStore->deleteData('candidates', ['id' => $id]);
            Response::deleted();
            break;

        default:
            ErrorHandler::handle('Method not allowed', 405);
    }
} catch (\Exception $e) {
    ErrorHandler::handle($e);
}
?> 