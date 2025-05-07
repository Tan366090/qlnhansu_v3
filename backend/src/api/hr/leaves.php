<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/api_error.log');

// Create logs directory if it doesn't exist
if (!file_exists(__DIR__ . '/../../logs')) {
    mkdir(__DIR__ . '/../../logs', 0777, true);
}

// Log request details
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Request URI: " . $_SERVER['REQUEST_URI']);
error_log("Query String: " . $_SERVER['QUERY_STRING']);

try {
    require_once __DIR__ . '/../../app/Models/Leave.php';
    require_once __DIR__ . '/../../app/Models/Employee.php';
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../middleware/auth.php';

    // Set headers
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    // Initialize database connection
    $database = new Database();
    $db = $database->getConnection();
    error_log("Database connection successful");

    // Initialize Leave model
    $leave = new Leave($db);
    error_log("Leave model initialized");

    // Get request method
    $method = $_SERVER['REQUEST_METHOD'];

    // Handle preflight request
    if ($method === 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    // Require authentication
    Auth::requireAuth();
    error_log("Authentication successful");

    switch ($method) {
        case 'GET':
            if (isset($_GET['action']) && $_GET['action'] === 'statistics') {
                // Get statistics
                $departmentId = isset($_GET['department_id']) ? $_GET['department_id'] : null;
                $stats = $leave->getLeaveStats($departmentId);
                
                if ($stats) {
                    echo json_encode([
                        'success' => true,
                        'data' => $stats
                    ]);
                } else {
                    throw new Exception('Không thể lấy thống kê nghỉ phép');
                }
            } else {
                // Get leaves with pagination
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
                $status = isset($_GET['status']) ? $_GET['status'] : null;
                $employeeId = isset($_GET['employee_id']) ? $_GET['employee_id'] : null;
                
                error_log("Fetching leaves - Page: $page, PerPage: $perPage, Status: $status, EmployeeId: $employeeId");
                
                $leaves = $leave->getLeaves($employeeId, $status);
                $total = count($leaves);
                $offset = ($page - 1) * $perPage;
                $leaves = array_slice($leaves, $offset, $perPage);
                
                echo json_encode([
                    'success' => true,
                    'data' => $leaves,
                    'total' => $total,
                    'page' => $page,
                    'per_page' => $perPage
                ]);
            }
            break;
            
        case 'POST':
            // Create new leave
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['employee_id']) || !isset($data['leave_type']) || 
                !isset($data['start_date']) || !isset($data['end_date']) || 
                !isset($data['reason'])) {
                throw new Exception('Thiếu thông tin cần thiết');
            }

            // Calculate leave duration
            $start = new DateTime($data['start_date']);
            $end = new DateTime($data['end_date']);
            $duration = $start->diff($end)->days + 1;
            
            $result = $leave->createLeave(
                $data['employee_id'],
                $data['leave_type'],
                $data['start_date'],
                $data['end_date'],
                $duration,
                $data['reason'],
                isset($data['attachment_url']) ? $data['attachment_url'] : null
            );
            
            if ($result['success']) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Tạo đơn nghỉ phép thành công',
                    'leave_id' => $result['leave_id']
                ]);
            } else {
                throw new Exception($result['error']);
            }
            break;
            
        case 'PUT':
            // Update leave status
            $data = json_decode(file_get_contents('php://input'), true);
            $leaveId = isset($_GET['id']) ? $_GET['id'] : null;
            
            if (!$leaveId) {
                throw new Exception('Thiếu ID đơn nghỉ phép');
            }
            
            if (!isset($data['status'])) {
                throw new Exception('Thiếu trạng thái cập nhật');
            }
            
            $result = $leave->updateLeaveStatus(
                $leaveId,
                $data['status'],
                isset($data['approved_by_user_id']) ? $data['approved_by_user_id'] : null,
                isset($data['approver_comments']) ? $data['approver_comments'] : null
            );
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Cập nhật trạng thái thành công'
                ]);
            } else {
                throw new Exception('Không thể cập nhật trạng thái');
            }
            break;
            
        case 'DELETE':
            // Delete leave
            $leaveId = isset($_GET['id']) ? $_GET['id'] : null;
            
            if (!$leaveId) {
                throw new Exception('Thiếu ID đơn nghỉ phép');
            }
            
            $result = $leave->delete($leaveId);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Xóa đơn nghỉ phép thành công'
                ]);
            } else {
                throw new Exception('Không thể xóa đơn nghỉ phép');
            }
            break;
            
        default:
            throw new Exception('Method không được hỗ trợ');
    }
} catch (Exception $e) {
    error_log("Error in leaves.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 