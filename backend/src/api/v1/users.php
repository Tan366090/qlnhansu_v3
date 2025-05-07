<?php
header('Content-Type: application/json');
require_once '../config/database.php';
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
        getAllUsers();
        break;
    case 'getById':
        getUserById();
        break;
    case 'create':
        createUser();
        break;
    case 'update':
        updateUser();
        break;
    case 'delete':
        deleteUser();
        break;
    case 'changePassword':
        changePassword();
        break;
    default:
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Action không hợp lệ'
        ]);
}

// Hàm lấy danh sách người dùng
function getAllUsers() {
    global $conn;
    
    try {
        $sql = "SELECT u.id, u.username, u.email, u.role, u.status, 
                       up.full_name, up.phone, up.gender, up.birth_date,
                       d.name as department_name, p.name as position_name
                FROM users u
                LEFT JOIN user_profiles up ON u.id = up.user_id
                LEFT JOIN departments d ON up.department_id = d.id
                LEFT JOIN positions p ON up.position_id = p.id
                ORDER BY u.created_at DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $users
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi lấy danh sách người dùng: ' . $e->getMessage()
        ]);
    }
}

// Hàm lấy thông tin người dùng theo ID
function getUserById() {
    global $conn;
    
    $id = $_GET['id'] ?? '';
    if (!$id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu ID người dùng'
        ]);
        return;
    }
    
    try {
        $sql = "SELECT u.id, u.username, u.email, u.role, u.status, 
                       up.full_name, up.phone, up.gender, up.birth_date,
                       up.identity_card, up.address,
                       d.id as department_id, d.name as department_name,
                       p.id as position_id, p.name as position_name,
                       up.hire_date, up.employment_status
                FROM users u
                LEFT JOIN user_profiles up ON u.id = up.user_id
                LEFT JOIN departments d ON up.department_id = d.id
                LEFT JOIN positions p ON up.position_id = p.id
                WHERE u.id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy người dùng'
            ]);
            return;
        }
        
        $user = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'data' => $user
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi lấy thông tin người dùng: ' . $e->getMessage()
        ]);
    }
}

// Hàm tạo người dùng mới
function createUser() {
    global $conn;
    
    // Lấy dữ liệu từ request body
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || empty($data['username']) || empty($data['email']) || empty($data['password'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu thông tin bắt buộc'
        ]);
        return;
    }
    
    try {
        // Bắt đầu transaction
        $conn->begin_transaction();
        
        // Tạo người dùng
        $sql = "INSERT INTO users (username, email, password, role, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $conn->prepare($sql);
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $role = $data['role'] ?? 'employee';
        $status = $data['status'] ?? 'active';
        
        $stmt->bind_param('sssss', 
            $data['username'],
            $data['email'],
            $hashedPassword,
            $role,
            $status
        );
        $stmt->execute();
        
        $userId = $conn->insert_id;
        
        // Tạo thông tin cá nhân
        $sql = "INSERT INTO user_profiles (user_id, full_name, phone, gender, birth_date,
                                         identity_card, address, department_id, position_id,
                                         hire_date, employment_status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('issssssiiss', 
            $userId,
            $data['full_name'] ?? '',
            $data['phone'] ?? '',
            $data['gender'] ?? '',
            $data['birth_date'] ?? null,
            $data['identity_card'] ?? '',
            $data['address'] ?? '',
            $data['department_id'] ?? null,
            $data['position_id'] ?? null,
            $data['hire_date'] ?? null,
            $data['employment_status'] ?? 'active'
        );
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Tạo người dùng thành công',
            'data' => [
                'id' => $userId,
                'username' => $data['username'],
                'email' => $data['email'],
                'role' => $role,
                'status' => $status
            ]
        ]);
    } catch (Exception $e) {
        // Rollback transaction nếu có lỗi
        $conn->rollback();
        
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi tạo người dùng: ' . $e->getMessage()
        ]);
    }
}

// Hàm cập nhật người dùng
function updateUser() {
    global $conn;
    
    $id = $_GET['id'] ?? '';
    if (!$id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu ID người dùng'
        ]);
        return;
    }
    
    // Lấy dữ liệu từ request body
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu dữ liệu cập nhật'
        ]);
        return;
    }
    
    try {
        // Bắt đầu transaction
        $conn->begin_transaction();
        
        // Cập nhật thông tin người dùng
        $sql = "UPDATE users SET 
                email = ?,
                role = ?,
                status = ?,
                updated_at = NOW()
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssi', 
            $data['email'],
            $data['role'],
            $data['status'],
            $id
        );
        $stmt->execute();
        
        // Cập nhật thông tin cá nhân
        $sql = "UPDATE user_profiles SET 
                full_name = ?,
                phone = ?,
                gender = ?,
                birth_date = ?,
                identity_card = ?,
                address = ?,
                department_id = ?,
                position_id = ?,
                hire_date = ?,
                employment_status = ?,
                updated_at = NOW()
                WHERE user_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssssiissi', 
            $data['full_name'],
            $data['phone'],
            $data['gender'],
            $data['birth_date'],
            $data['identity_card'],
            $data['address'],
            $data['department_id'],
            $data['position_id'],
            $data['hire_date'],
            $data['employment_status'],
            $id
        );
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Cập nhật người dùng thành công'
        ]);
    } catch (Exception $e) {
        // Rollback transaction nếu có lỗi
        $conn->rollback();
        
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi cập nhật người dùng: ' . $e->getMessage()
        ]);
    }
}

// Hàm xóa người dùng
function deleteUser() {
    global $conn;
    
    $id = $_GET['id'] ?? '';
    if (!$id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu ID người dùng'
        ]);
        return;
    }
    
    try {
        // Bắt đầu transaction
        $conn->begin_transaction();
        
        // Xóa thông tin cá nhân
        $sql = "DELETE FROM user_profiles WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        
        // Xóa người dùng
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        
        if ($stmt->affected_rows === 0) {
            throw new Exception('Không tìm thấy người dùng');
        }
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Xóa người dùng thành công'
        ]);
    } catch (Exception $e) {
        // Rollback transaction nếu có lỗi
        $conn->rollback();
        
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi xóa người dùng: ' . $e->getMessage()
        ]);
    }
}

// Hàm đổi mật khẩu
function changePassword() {
    global $conn;
    
    $id = $_GET['id'] ?? '';
    if (!$id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu ID người dùng'
        ]);
        return;
    }
    
    // Lấy dữ liệu từ request body
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || empty($data['password'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Mật khẩu mới là bắt buộc'
        ]);
        return;
    }
    
    try {
        $sql = "UPDATE users SET 
                password = ?,
                updated_at = NOW()
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt->bind_param('si', $hashedPassword, $id);
        $stmt->execute();
        
        if ($stmt->affected_rows === 0) {
            throw new Exception('Không tìm thấy người dùng');
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Đổi mật khẩu thành công'
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi đổi mật khẩu: ' . $e->getMessage()
        ]);
    }
} 