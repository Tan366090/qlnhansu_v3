<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/auth.php';

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

// Lấy kết nối database
$conn = Database::getConnection();

// Lấy action từ request
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'getAll':
        getAllDepartments();
        break;
    case 'getById':
        getDepartmentById();
        break;
    case 'create':
        createDepartment();
        break;
    case 'update':
        updateDepartment();
        break;
    case 'delete':
        deleteDepartment();
        break;
    case 'getEmployees':
        getDepartmentEmployees();
        break;
    default:
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Action không hợp lệ'
        ]);
}

// Hàm lấy danh sách phòng ban
function getAllDepartments() {
    global $conn;
    
    try {
        // Query to get departments with related information
        $query = "SELECT 
            d.id,
            d.name as department_name,
            d.description,
            d.created_at,
            d.updated_at,
            d.parent_id,
            d.manager_id,
            -- Get manager name
            e.name as manager_name,
            -- Count employees
            COUNT(emp.id) as employee_count,
            -- Get parent department name
            pd.name as parent_department_name,
            -- Get position name for manager
            p.name as manager_position
        FROM departments d
        LEFT JOIN employees e ON d.manager_id = e.id
        LEFT JOIN employees emp ON d.id = emp.department_id
        LEFT JOIN departments pd ON d.parent_id = pd.id
        LEFT JOIN positions p ON e.position_id = p.id
        GROUP BY d.id
        ORDER BY d.name ASC";

        $stmt = $conn->prepare($query);
        $stmt->execute();
        $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format the response
        $formatted_departments = array_map(function($dept) {
            return [
                'id' => $dept['id'],
                'name' => $dept['department_name'],
                'description' => $dept['description'],
                'manager' => [
                    'id' => $dept['manager_id'],
                    'name' => $dept['manager_name'],
                    'position' => $dept['manager_position']
                ],
                'employee_count' => (int)$dept['employee_count'],
                'parent_department' => [
                    'id' => $dept['parent_id'],
                    'name' => $dept['parent_department_name']
                ],
                'created_at' => $dept['created_at'],
                'updated_at' => $dept['updated_at'],
                'status' => $dept['employee_count'] > 0 ? 'active' : 'inactive'
            ];
        }, $departments);

        echo json_encode([
            'status' => 'success',
            'data' => $formatted_departments
        ]);

    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}

// Hàm lấy thông tin phòng ban theo ID
function getDepartmentById() {
    global $conn;
    
    $id = $_GET['id'] ?? '';
    if (!$id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu ID phòng ban'
        ]);
        return;
    }
    
    try {
        $sql = "SELECT d.id, d.name, d.description, d.created_at, d.updated_at,
                       COUNT(up.id) as employee_count
                FROM departments d
                LEFT JOIN user_profiles up ON d.id = up.department_id
                WHERE d.id = ?
                GROUP BY d.id";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy phòng ban'
            ]);
            return;
        }
        
        $department = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'data' => $department
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi lấy thông tin phòng ban: ' . $e->getMessage()
        ]);
    }
}

// Hàm tạo phòng ban mới
function createDepartment() {
    global $conn;
    
    // Lấy dữ liệu từ request body
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || empty($data['name'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Tên phòng ban là bắt buộc'
        ]);
        return;
    }
    
    try {
        $sql = "INSERT INTO departments (name, description, created_at, updated_at)
                VALUES (?, ?, NOW(), NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $data['name'], $data['description'] ?? '');
        $stmt->execute();
        
        $departmentId = $conn->insert_id;
        
        echo json_encode([
            'success' => true,
            'message' => 'Tạo phòng ban thành công',
            'data' => [
                'id' => $departmentId,
                'name' => $data['name'],
                'description' => $data['description'] ?? ''
            ]
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi tạo phòng ban: ' . $e->getMessage()
        ]);
    }
}

// Hàm cập nhật phòng ban
function updateDepartment() {
    global $conn;
    
    $id = $_GET['id'] ?? '';
    if (!$id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu ID phòng ban'
        ]);
        return;
    }
    
    // Lấy dữ liệu từ request body
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || empty($data['name'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Tên phòng ban là bắt buộc'
        ]);
        return;
    }
    
    try {
        $sql = "UPDATE departments SET 
                name = ?,
                description = ?,
                updated_at = NOW()
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssi', $data['name'], $data['description'] ?? '', $id);
        $stmt->execute();
        
        if ($stmt->affected_rows === 0) {
            throw new Exception('Không tìm thấy phòng ban');
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Cập nhật phòng ban thành công'
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi cập nhật phòng ban: ' . $e->getMessage()
        ]);
    }
}

// Hàm xóa phòng ban
function deleteDepartment() {
    global $conn;
    
    $id = $_GET['id'] ?? '';
    if (!$id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu ID phòng ban'
        ]);
        return;
    }
    
    try {
        // Kiểm tra xem phòng ban có nhân viên không
        $sql = "SELECT COUNT(*) as count FROM user_profiles WHERE department_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            throw new Exception('Không thể xóa phòng ban vì còn nhân viên');
        }
        
        // Xóa phòng ban
        $sql = "DELETE FROM departments WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        
        if ($stmt->affected_rows === 0) {
            throw new Exception('Không tìm thấy phòng ban');
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Xóa phòng ban thành công'
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi xóa phòng ban: ' . $e->getMessage()
        ]);
    }
}

// Hàm lấy danh sách nhân viên trong phòng ban
function getDepartmentEmployees() {
    global $conn;
    
    $id = $_GET['id'] ?? '';
    if (!$id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu ID phòng ban'
        ]);
        return;
    }
    
    try {
        $sql = "SELECT u.id, u.username, u.email, u.role, u.status,
                       up.full_name, up.phone, up.gender, up.birth_date,
                       p.name as position_name, up.hire_date, up.employment_status
                FROM users u
                JOIN user_profiles up ON u.id = up.user_id
                LEFT JOIN positions p ON up.position_id = p.id
                WHERE up.department_id = ?
                ORDER BY up.full_name ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $employees = [];
        while ($row = $result->fetch_assoc()) {
            $employees[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $employees
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi lấy danh sách nhân viên: ' . $e->getMessage()
        ]);
    }
} 