<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../middleware/auth.php';

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
        getAllSalaries();
        break;
    case 'getByEmployee':
        getSalaryByEmployee();
        break;
    case 'getHistory':
        getSalaryHistory();
        break;
    case 'calculate':
        calculateSalary();
        break;
    case 'update':
        updateSalary();
        break;
    default:
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Action không hợp lệ'
        ]);
}

// Hàm lấy danh sách lương
function getAllSalaries() {
    global $conn, $user;
    
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
        $sql = "SELECT s.id, s.user_id, u.username, up.full_name,
                       s.basic_salary, s.allowance, s.bonus,
                       s.tax, s.insurance, s.total_salary,
                       s.month, s.year, s.status, s.payment_date,
                       s.created_at
                FROM salaries s
                JOIN users u ON s.user_id = u.id
                JOIN user_profiles up ON u.id = up.user_id
                ORDER BY s.created_at DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $salaries = [];
        while ($row = $result->fetch_assoc()) {
            $salaries[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $salaries
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi lấy danh sách lương: ' . $e->getMessage()
        ]);
    }
}

// Hàm lấy lương của nhân viên
function getSalaryByEmployee() {
    global $conn, $user;
    
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
        $sql = "SELECT s.id, s.basic_salary, s.allowance, s.bonus,
                       s.tax, s.insurance, s.total_salary,
                       s.month, s.year, s.status, s.payment_date,
                       s.created_at
                FROM salaries s
                WHERE s.user_id = ?
                ORDER BY s.created_at DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $employeeId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $salaries = [];
        while ($row = $result->fetch_assoc()) {
            $salaries[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $salaries
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi lấy lương: ' . $e->getMessage()
        ]);
    }
}

// Hàm lấy lịch sử lương
function getSalaryHistory() {
    global $conn, $user;
    
    $employeeId = $_GET['employeeId'] ?? $user['id'];
    $month = $_GET['month'] ?? date('m');
    $year = $_GET['year'] ?? date('Y');
    
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
        $sql = "SELECT s.id, s.basic_salary, s.allowance, s.bonus,
                       s.tax, s.insurance, s.total_salary,
                       s.month, s.year, s.status, s.payment_date,
                       s.created_at
                FROM salaries s
                WHERE s.user_id = ? 
                AND s.month = ? 
                AND s.year = ?
                ORDER BY s.created_at DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iii', $employeeId, $month, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $history
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi lấy lịch sử lương: ' . $e->getMessage()
        ]);
    }
}

// Hàm tính lương
function calculateSalary() {
    global $conn, $user;
    
    // Chỉ admin và HR mới có quyền tính lương
    if ($user['role'] !== 'admin' && $user['role'] !== 'hr') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Không có quyền truy cập'
        ]);
        return;
    }
    
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['user_id']) || !isset($data['month']) || !isset($data['year'])) {
            throw new Exception('Thiếu thông tin cần thiết');
        }
        
        // Lấy thông tin lương cơ bản
        $sql = "SELECT basic_salary, allowance FROM user_profiles WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $data['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $profile = $result->fetch_assoc();
        
        if (!$profile) {
            throw new Exception('Không tìm thấy thông tin nhân viên');
        }
        
        // Tính toán lương
        $basicSalary = $profile['basic_salary'];
        $allowance = $profile['allowance'];
        $bonus = $data['bonus'] ?? 0;
        
        // Tính bảo hiểm (10.5% lương cơ bản)
        $insurance = $basicSalary * 0.105;
        
        // Tính thuế (10% tổng thu nhập)
        $taxableIncome = $basicSalary + $allowance + $bonus - $insurance;
        $tax = $taxableIncome * 0.1;
        
        // Tính tổng lương
        $totalSalary = $basicSalary + $allowance + $bonus - $insurance - $tax;
        
        echo json_encode([
            'success' => true,
            'data' => [
                'basic_salary' => $basicSalary,
                'allowance' => $allowance,
                'bonus' => $bonus,
                'insurance' => $insurance,
                'tax' => $tax,
                'total_salary' => $totalSalary
            ]
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi tính lương: ' . $e->getMessage()
        ]);
    }
}

// Hàm cập nhật lương
function updateSalary() {
    global $conn, $user;
    
    // Chỉ admin và HR mới có quyền cập nhật lương
    if ($user['role'] !== 'admin' && $user['role'] !== 'hr') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Không có quyền truy cập'
        ]);
        return;
    }
    
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['user_id']) || !isset($data['month']) || !isset($data['year']) ||
            !isset($data['basic_salary']) || !isset($data['allowance']) || !isset($data['bonus']) ||
            !isset($data['insurance']) || !isset($data['tax']) || !isset($data['total_salary'])) {
            throw new Exception('Thiếu thông tin cần thiết');
        }
        
        // Kiểm tra xem đã có bản ghi lương chưa
        $sql = "SELECT id FROM salaries 
                WHERE user_id = ? AND month = ? AND year = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iii', $data['user_id'], $data['month'], $data['year']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Cập nhật bản ghi hiện có
            $sql = "UPDATE salaries 
                    SET basic_salary = ?, allowance = ?, bonus = ?,
                        insurance = ?, tax = ?, total_salary = ?,
                        status = ?, payment_date = NOW()
                    WHERE user_id = ? AND month = ? AND year = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ddddddiii', 
                $data['basic_salary'], $data['allowance'], $data['bonus'],
                $data['insurance'], $data['tax'], $data['total_salary'],
                $data['status'], $data['user_id'], $data['month'], $data['year']
            );
        } else {
            // Thêm bản ghi mới
            $sql = "INSERT INTO salaries (user_id, month, year, basic_salary, 
                                        allowance, bonus, insurance, tax, 
                                        total_salary, status, payment_date)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('iiiddddddi', 
                $data['user_id'], $data['month'], $data['year'],
                $data['basic_salary'], $data['allowance'], $data['bonus'],
                $data['insurance'], $data['tax'], $data['total_salary'],
                $data['status']
            );
        }
        
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Cập nhật lương thành công'
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi cập nhật lương: ' . $e->getMessage()
        ]);
    }
} 