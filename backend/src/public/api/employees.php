<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Tạm thời bỏ qua xác thực
// session_start();
// if (!isset($_SESSION['user_id'])) {
//     http_response_code(401);
//     echo json_encode([
//         'success' => false,
//         'message' => 'Unauthorized access'
//     ]);
//     exit();
// }

require_once '../../config/database.php';
require_once '../../models/Employee.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    $employee = new Employee($conn);

    $method = $_SERVER['REQUEST_METHOD'];

    switch($method) {
        case 'GET':
            if(isset($_GET['id'])) {
                // Lấy thông tin một nhân viên
                $result = $employee->getById($_GET['id']);
            } else {
                // Lấy danh sách nhân viên
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
                $search = isset($_GET['search']) ? $_GET['search'] : '';
                $department = isset($_GET['department']) ? $_GET['department'] : '';
                $status = isset($_GET['status']) ? $_GET['status'] : '';
                
                $result = $employee->getAll($page, $limit, $search, $department, $status);
            }
            break;
        
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $employee->create($data);
            break;
        
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            if(isset($_GET['id'])) {
                $result = $employee->update($_GET['id'], $data);
            }
            break;
        
        case 'DELETE':
            if(isset($_GET['id'])) {
                $result = $employee->delete($_GET['id']);
            }
            break;
        
        default:
            http_response_code(405);
            $result = ['error' => 'Method not allowed'];
    }

    echo json_encode($result);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 