<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the root directory path
$rootDir = dirname(dirname(__DIR__));

// Check if required files exist
$required_files = [
    $rootDir . '/config/database.php',
    $rootDir . '/middleware/auth.php'
];

foreach ($required_files as $file) {
    if (!file_exists($file)) {
        die("Required file not found: $file");
    }
}

require_once $rootDir . '/config/database.php';
require_once $rootDir . '/middleware/auth.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check database connection
try {
    $db = Database::getConnection();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit;
}

// Kiểm tra quyền truy cập
$auth = new Auth();
$user = $auth->getUser();

if (!$user) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Chưa đăng nhập'
    ]);
    exit;
}

// Lấy action từ request
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'getAll':
        getAllAttendance();
        break;
    case 'getByEmployee':
        getAttendanceByEmployee();
        break;
    case 'checkIn':
        checkIn();
        break;
    case 'checkOut':
        checkOut();
        break;
    case 'getStatistics':
        getAttendanceStatistics();
        break;
    default:
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Action không hợp lệ'
        ]);
}

// Hàm lấy danh sách chấm công
function getAllAttendance() {
    global $db, $user;
    
    // Chỉ admin và HR mới có quyền xem tất cả
    if ($user['role'] !== 'admin' && $user['role'] !== 'hr') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Không có quyền truy cập'
        ]);
        return;
    }
    
    try {
        $sql = "SELECT a.id, a.user_id, u.username, up.full_name,
                       a.check_in_time, a.check_out_time,
                       a.status, a.notes, a.created_at
                FROM attendance a
                JOIN users u ON a.user_id = u.id
                JOIN user_profiles up ON u.id = up.user_id
                ORDER BY a.created_at DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $attendance = [];
        while ($row = $result->fetch_assoc()) {
            $attendance[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $attendance
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi lấy danh sách chấm công: ' . $e->getMessage()
        ]);
    }
}

// Hàm lấy lịch sử chấm công của nhân viên
function getAttendanceByEmployee() {
    global $db, $user;
    
    $employeeId = $_GET['employeeId'] ?? $user['id'];
    
    // Kiểm tra quyền xem
    if ($user['role'] !== 'admin' && $user['role'] !== 'hr' && $user['id'] !== $employeeId) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Không có quyền truy cập'
        ]);
        return;
    }
    
    try {
        $sql = "SELECT a.id, a.check_in_time, a.check_out_time,
                       a.status, a.notes, a.created_at
                FROM attendance a
                WHERE a.user_id = ?
                ORDER BY a.created_at DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $employeeId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $attendance = [];
        while ($row = $result->fetch_assoc()) {
            $attendance[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $attendance
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi lấy lịch sử chấm công: ' . $e->getMessage()
        ]);
    }
}

// Hàm chấm công vào
function checkIn() {
    global $db, $user;
    
    try {
        // Kiểm tra xem đã chấm công vào chưa
        $today = date('Y-m-d');
        $sql = "SELECT id FROM attendance 
                WHERE user_id = ? AND DATE(check_in_time) = ?";
        
        $stmt = $db->prepare($sql);
        $stmt->bind_param('is', $user['id'], $today);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception('Bạn đã chấm công vào hôm nay');
        }
        
        // Thêm bản ghi chấm công
        $sql = "INSERT INTO attendance (user_id, check_in_time, status, created_at)
                VALUES (?, NOW(), 'present', NOW())";
        
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $user['id']);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Chấm công vào thành công'
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi chấm công vào: ' . $e->getMessage()
        ]);
    }
}

// Hàm chấm công ra
function checkOut() {
    global $db, $user;
    
    try {
        // Kiểm tra xem đã chấm công vào chưa
        $today = date('Y-m-d');
        $sql = "SELECT id FROM attendance 
                WHERE user_id = ? AND DATE(check_in_time) = ? AND check_out_time IS NULL";
        
        $stmt = $db->prepare($sql);
        $stmt->bind_param('is', $user['id'], $today);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Bạn chưa chấm công vào hôm nay');
        }
        
        // Cập nhật thời gian chấm công ra
        $sql = "UPDATE attendance 
                SET check_out_time = NOW()
                WHERE user_id = ? AND DATE(check_in_time) = ?";
        
        $stmt = $db->prepare($sql);
        $stmt->bind_param('is', $user['id'], $today);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Chấm công ra thành công'
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi chấm công ra: ' . $e->getMessage()
        ]);
    }
}

// Hàm lấy thống kê chấm công
function getAttendanceStatistics() {
    global $db;
    
    try {
        // Test data
        $data = [
            'totalEmployees' => 50,
            'presentToday' => 45,
            'absentToday' => 5,
            'lateToday' => 3,
            'onTimePercentage' => 84
        ];
        
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi lấy thống kê: ' . $e->getMessage()
        ]);
    }
} 