<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';
require_once '../middleware/auth.php';

// Kiểm tra quyền truy cập
$auth = new Auth();
$user = $auth->getUser();

if (!$user || $user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Không có quyền truy cập'
    ]);
    exit;
}

// Lấy action từ request
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'getAll':
        getAllPositions();
        break;
    case 'getById':
        getPositionById();
        break;
    case 'create':
        createPosition();
        break;
    case 'update':
        updatePosition();
        break;
    case 'delete':
        deletePosition();
        break;
    default:
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Action không hợp lệ'
        ]);
}

// Hàm lấy danh sách vị trí
function getAllPositions() {
    global $conn;
    
    try {
        $sql = "SELECT p.*, d.name as department_name, 
                       COUNT(e.id) as employee_count
                FROM positions p
                LEFT JOIN departments d ON p.department_id = d.id
                LEFT JOIN employees e ON p.id = e.position_id
                GROUP BY p.id
                ORDER BY p.name ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $positions = [];
        while ($row = $result->fetch_assoc()) {
            $positions[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $positions
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi lấy danh sách vị trí: ' . $e->getMessage()
        ]);
    }
}

// Hàm lấy thông tin vị trí theo ID
function getPositionById() {
    global $conn;
    
    $id = $_GET['id'] ?? '';
    if (!$id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu ID vị trí'
        ]);
        return;
    }
    
    try {
        $sql = "SELECT p.*, d.name as department_name
                FROM positions p
                LEFT JOIN departments d ON p.department_id = d.id
                WHERE p.id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy vị trí'
            ]);
            return;
        }
        
        $position = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'data' => $position
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi lấy thông tin vị trí: ' . $e->getMessage()
        ]);
    }
}

// Hàm tạo vị trí mới
function createPosition() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ'
        ]);
        return;
    }
    
    $requiredFields = ['name', 'department_id', 'description', 'salary_range'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => "Thiếu trường bắt buộc: $field"
            ]);
            return;
        }
    }
    
    try {
        $sql = "INSERT INTO positions (name, department_id, description, salary_range, requirements, benefits, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'sissss',
            $data['name'],
            $data['department_id'],
            $data['description'],
            $data['salary_range'],
            $data['requirements'] ?? '',
            $data['benefits'] ?? ''
        );
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Tạo vị trí thành công',
                'id' => $stmt->insert_id
            ]);
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi tạo vị trí: ' . $e->getMessage()
        ]);
    }
}

// Hàm cập nhật vị trí
function updatePosition() {
    global $conn;
    
    $id = $_GET['id'] ?? '';
    if (!$id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu ID vị trí'
        ]);
        return;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ'
        ]);
        return;
    }
    
    try {
        $sql = "UPDATE positions SET 
                name = ?,
                department_id = ?,
                description = ?,
                salary_range = ?,
                requirements = ?,
                benefits = ?,
                updated_at = NOW()
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'sissssi',
            $data['name'],
            $data['department_id'],
            $data['description'],
            $data['salary_range'],
            $data['requirements'] ?? '',
            $data['benefits'] ?? '',
            $id
        );
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật vị trí thành công'
            ]);
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi cập nhật vị trí: ' . $e->getMessage()
        ]);
    }
}

// Hàm xóa vị trí
function deletePosition() {
    global $conn;
    
    $id = $_GET['id'] ?? '';
    if (!$id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu ID vị trí'
        ]);
        return;
    }
    
    try {
        // Kiểm tra xem vị trí có nhân viên nào không
        $checkSql = "SELECT COUNT(*) as count FROM employees WHERE position_id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param('i', $id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $count = $result->fetch_assoc()['count'];
        
        if ($count > 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Không thể xóa vị trí vì còn nhân viên đang làm việc'
            ]);
            return;
        }
        
        $sql = "DELETE FROM positions WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Xóa vị trí thành công'
            ]);
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi xóa vị trí: ' . $e->getMessage()
        ]);
    }
} 