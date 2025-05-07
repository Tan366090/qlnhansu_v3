<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

require_once '../config/database.php';
require_once '../middleware/auth.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Kiểm tra quyền truy cập
$auth = new Auth();
$user = $auth->getUser();

if (!$user || $user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Access denied',
        'errors' => ['auth' => 'You do not have permission to access this resource']
    ]);
    exit;
}

// Helper function to send response
function sendResponse($success, $message, $data = [], $errors = [], $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'errors' => $errors
    ]);
    exit;
}

// Lấy action từ request
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        getEmployeeList();
        break;
    case 'get':
        getEmployee();
        break;
    case 'create':
        createEmployee();
        break;
    case 'update':
        updateEmployee();
        break;
    case 'deactivate':
        deactivateEmployee();
        break;
    case 'documents':
        getEmployeeDocuments();
        break;
    case 'report':
        getEmployeeReport();
        break;
    default:
        sendResponse(false, 'Invalid action', [], ['action' => 'Invalid action specified'], 400);
}

// Hàm lấy danh sách nhân viên
function getEmployeeList() {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    try {
        $department_id = $_GET['department_id'] ?? null;
        $status = $_GET['status'] ?? 'active';
        
        $sql = "SELECT u.id, u.username, u.email, u.status, 
                       up.full_name, up.phone_number, up.gender, up.birth_date, 
                       up.identity_card, up.address, up.tax_code, up.bank_account, up.bank_name,
                       d.name as department_name, p.name as position_name,
                       u.created_at, u.updated_at
                FROM users u
                LEFT JOIN user_profiles up ON u.id = up.user_id
                LEFT JOIN departments d ON up.department_id = d.id
                LEFT JOIN positions p ON up.position_id = p.id
                WHERE u.role = 'employee'";
        
        $params = [];
        
        if ($department_id) {
            $sql .= " AND up.department_id = ?";
            $params[] = $department_id;
        }
        
        if ($status) {
            $sql .= " AND u.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY u.created_at DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $employees = $stmt->fetchAll();
        
        foreach ($employees as &$row) {
            // Lấy thông tin emergency contact
            $stmt2 = $conn->prepare("SELECT * FROM emergency_contacts WHERE user_id = ?");
            $stmt2->execute([$row['id']]);
            $row['emergency_contact'] = $stmt2->fetch();
            
            // Lấy thông tin education
            $stmt3 = $conn->prepare("SELECT * FROM education WHERE user_id = ?");
            $stmt3->execute([$row['id']]);
            $row['education'] = $stmt3->fetchAll();
            
            // Lấy thông tin work experience
            $stmt4 = $conn->prepare("SELECT * FROM work_experience WHERE user_id = ?");
            $stmt4->execute([$row['id']]);
            $row['work_experience'] = $stmt4->fetchAll();
        }
        
        sendResponse(true, 'Employee list retrieved successfully', ['employees' => $employees]);
    } catch (Exception $e) {
        sendResponse(false, 'Failed to get employee list', [], ['server' => $e->getMessage()], 500);
    }
}

// Hàm lấy thông tin nhân viên theo ID
function getEmployee() {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $id = $_GET['id'] ?? '';
    if (!$id) {
        sendResponse(false, 'Employee ID is required', [], ['id' => 'Employee ID is required'], 400);
    }
    
    try {
        $sql = "SELECT u.id, u.username, u.email, u.status, 
                       up.full_name, up.phone_number, up.gender, up.birth_date, 
                       up.identity_card, up.address, up.tax_code, up.bank_account, up.bank_name,
                       up.department_id, up.position_id,
                       d.name as department_name, p.name as position_name,
                       u.created_at, u.updated_at
                FROM users u
                LEFT JOIN user_profiles up ON u.id = up.user_id
                LEFT JOIN departments d ON up.department_id = d.id
                LEFT JOIN positions p ON up.position_id = p.id
                WHERE u.id = ? AND u.role = 'employee'";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() === 0) {
            sendResponse(false, 'Employee not found', [], ['id' => 'Employee not found'], 404);
        }
        
        $employee = $stmt->fetch();
        
        // Lấy thông tin emergency contact
        $stmt2 = $conn->prepare("SELECT * FROM emergency_contacts WHERE user_id = ?");
        $stmt2->execute([$id]);
        $employee['emergency_contact'] = $stmt2->fetch();
        
        // Lấy thông tin education
        $stmt3 = $conn->prepare("SELECT * FROM education WHERE user_id = ?");
        $stmt3->execute([$id]);
        $employee['education'] = $stmt3->fetchAll();
        
        // Lấy thông tin work experience
        $stmt4 = $conn->prepare("SELECT * FROM work_experience WHERE user_id = ?");
        $stmt4->execute([$id]);
        $employee['work_experience'] = $stmt4->fetchAll();
        
        sendResponse(true, 'Employee details retrieved successfully', ['employee' => $employee]);
    } catch (Exception $e) {
        sendResponse(false, 'Failed to get employee details', [], ['server' => $e->getMessage()], 500);
    }
}

// Hàm tạo nhân viên mới
function createEmployee() {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        sendResponse(false, 'Invalid request data', [], ['data' => 'Invalid request data'], 400);
    }
    
    // Validate required fields
    $errors = [];
    if (empty($data['first_name'])) $errors['first_name'] = 'First name is required';
    if (empty($data['last_name'])) $errors['last_name'] = 'Last name is required';
    if (empty($data['email'])) $errors['email'] = 'Email is required';
    if (empty($data['phone'])) $errors['phone'] = 'Phone is required';
    if (empty($data['birth_date'])) $errors['birth_date'] = 'Birth date is required';
    if (empty($data['gender'])) $errors['gender'] = 'Gender is required';
    if (empty($data['national_id'])) $errors['national_id'] = 'National ID is required';
    if (empty($data['tax_code'])) $errors['tax_code'] = 'Tax code is required';
    
    if (!empty($errors)) {
        sendResponse(false, 'Validation failed', [], $errors, 400);
    }
    
    try {
        $conn->beginTransaction();
        
        // Tạo user
        $sql = "INSERT INTO users (username, email, password, role, status, created_at) 
                VALUES (?, ?, ?, 'employee', 'active', NOW())";
        
        $password = password_hash('123456', PASSWORD_DEFAULT); // Default password
        $username = strtolower($data['first_name'] . '.' . $data['last_name']);
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$username, $data['email'], $password]);
        
        $user_id = $conn->lastInsertId();
        
        // Tạo user profile
        $sql = "INSERT INTO user_profiles (
                    user_id, full_name, phone_number, gender, birth_date, 
                    identity_card, address, tax_code, bank_account, bank_name,
                    department_id, position_id, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $full_name = $data['first_name'] . ' ' . $data['last_name'];
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $user_id,
            $full_name,
            $data['phone'],
            $data['gender'],
            $data['birth_date'],
            $data['national_id'],
            $data['address'],
            $data['tax_code'],
            $data['bank_account'],
            $data['bank_name'],
            $data['department_id'],
            $data['position_id']
        ]);
        
        // Tạo emergency contact
        if (!empty($data['emergency_contact'])) {
            $sql = "INSERT INTO emergency_contacts (
                        user_id, name, relationship, phone, created_at
                    ) VALUES (?, ?, ?, ?, NOW())";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $user_id,
                $data['emergency_contact']['name'],
                $data['emergency_contact']['relationship'],
                $data['emergency_contact']['phone']
            ]);
        }
        
        // Tạo education records
        if (!empty($data['education'])) {
            $sql = "INSERT INTO education (
                        user_id, degree, major, school, graduation_year, created_at
                    ) VALUES (?, ?, ?, ?, ?, NOW())";
            
            $stmt = $conn->prepare($sql);
            foreach ($data['education'] as $edu) {
                $stmt->execute([
                    $user_id,
                    $edu['degree'],
                    $edu['major'],
                    $edu['school'],
                    $edu['graduation_year']
                ]);
            }
        }
        
        // Tạo work experience records
        if (!empty($data['work_experience'])) {
            $sql = "INSERT INTO work_experience (
                        user_id, company, position, start_date, end_date, description, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $conn->prepare($sql);
            foreach ($data['work_experience'] as $exp) {
                $stmt->execute([
                    $user_id,
                    $exp['company'],
                    $exp['position'],
                    $exp['start_date'],
                    $exp['end_date'],
                    $exp['description']
                ]);
            }
        }
        
        $conn->commit();
        sendResponse(true, 'Employee created successfully', ['employee_id' => $user_id]);
    } catch (Exception $e) {
        $conn->rollBack();
        sendResponse(false, 'Failed to create employee', [], ['server' => $e->getMessage()], 500);
    }
}

// Hàm cập nhật thông tin nhân viên
function updateEmployee() {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        sendResponse(false, 'Invalid request data', [], ['data' => 'Invalid request data'], 400);
    }
    
    $id = $data['id'] ?? '';
    if (!$id) {
        sendResponse(false, 'Employee ID is required', [], ['id' => 'Employee ID is required'], 400);
    }
    
    try {
        $conn->beginTransaction();
        
        // Cập nhật user
        $sql = "UPDATE users SET 
                email = ?,
                status = ?,
                updated_at = NOW()
                WHERE id = ? AND role = 'employee'";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$data['email'], $data['status'], $id]);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception('Employee not found');
        }
        
        // Cập nhật user profile
        $sql = "UPDATE user_profiles SET 
                full_name = ?,
                phone_number = ?,
                gender = ?,
                birth_date = ?,
                identity_card = ?,
                address = ?,
                tax_code = ?,
                bank_account = ?,
                bank_name = ?,
                department_id = ?,
                position_id = ?,
                updated_at = NOW()
                WHERE user_id = ?";
        
        $full_name = $data['first_name'] . ' ' . $data['last_name'];
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $full_name,
            $data['phone'],
            $data['gender'],
            $data['birth_date'],
            $data['national_id'],
            $data['address'],
            $data['tax_code'],
            $data['bank_account'],
            $data['bank_name'],
            $data['department_id'],
            $data['position_id'],
            $id
        ]);
        
        // Cập nhật emergency contact
        if (!empty($data['emergency_contact'])) {
            $sql = "UPDATE emergency_contacts SET 
                    name = ?,
                    relationship = ?,
                    phone = ?,
                    updated_at = NOW()
                    WHERE user_id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $data['emergency_contact']['name'],
                $data['emergency_contact']['relationship'],
                $data['emergency_contact']['phone'],
                $id
            ]);
        }
        
        $conn->commit();
        sendResponse(true, 'Employee updated successfully');
    } catch (Exception $e) {
        $conn->rollBack();
        sendResponse(false, 'Failed to update employee', [], ['server' => $e->getMessage()], 500);
    }
}

// Hàm deactivate nhân viên
function deactivateEmployee() {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $id = $_GET['id'] ?? '';
    if (!$id) {
        sendResponse(false, 'Employee ID is required', [], ['id' => 'Employee ID is required'], 400);
    }
    
    try {
        $sql = "UPDATE users SET 
                status = 'inactive',
                updated_at = NOW()
                WHERE id = ? AND role = 'employee'";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() === 0) {
            sendResponse(false, 'Employee not found', [], ['id' => 'Employee not found'], 404);
        }
        
        sendResponse(true, 'Employee deactivated successfully');
    } catch (Exception $e) {
        sendResponse(false, 'Failed to deactivate employee', [], ['server' => $e->getMessage()], 500);
    }
}

// Hàm lấy danh sách tài liệu của nhân viên
function getEmployeeDocuments() {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $id = $_GET['id'] ?? '';
    if (!$id) {
        sendResponse(false, 'Employee ID is required', [], ['id' => 'Employee ID is required'], 400);
    }
    
    try {
        $sql = "SELECT * FROM employee_documents WHERE user_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
        $documents = $stmt->fetchAll();
        
        sendResponse(true, 'Employee documents retrieved successfully', ['documents' => $documents]);
    } catch (Exception $e) {
        sendResponse(false, 'Failed to get employee documents', [], ['server' => $e->getMessage()], 500);
    }
}

// Hàm lấy báo cáo của nhân viên
function getEmployeeReport() {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $id = $_GET['id'] ?? '';
    if (!$id) {
        sendResponse(false, 'Employee ID is required', [], ['id' => 'Employee ID is required'], 400);
    }
    
    try {
        // Lấy thông tin cơ bản
        $sql = "SELECT u.id, u.username, u.email, u.status, 
                       up.full_name, up.phone_number, up.gender, up.birth_date, 
                       up.identity_card, up.address, up.tax_code, up.bank_account, up.bank_name,
                       d.name as department_name, p.name as position_name,
                       u.created_at, u.updated_at
                FROM users u
                LEFT JOIN user_profiles up ON u.id = up.user_id
                LEFT JOIN departments d ON up.department_id = d.id
                LEFT JOIN positions p ON up.position_id = p.id
                WHERE u.id = ? AND u.role = 'employee'";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() === 0) {
            sendResponse(false, 'Employee not found', [], ['id' => 'Employee not found'], 404);
        }
        
        $report = $stmt->fetch();
        
        // Lấy thông tin emergency contact
        $stmt2 = $conn->prepare("SELECT * FROM emergency_contacts WHERE user_id = ?");
        $stmt2->execute([$id]);
        $report['emergency_contact'] = $stmt2->fetch();
        
        // Lấy thông tin education
        $stmt3 = $conn->prepare("SELECT * FROM education WHERE user_id = ?");
        $stmt3->execute([$id]);
        $report['education'] = $stmt3->fetchAll();
        
        // Lấy thông tin work experience
        $stmt4 = $conn->prepare("SELECT * FROM work_experience WHERE user_id = ?");
        $stmt4->execute([$id]);
        $report['work_experience'] = $stmt4->fetchAll();
        
        // Lấy thông tin documents
        $stmt5 = $conn->prepare("SELECT * FROM employee_documents WHERE user_id = ?");
        $stmt5->execute([$id]);
        $report['documents'] = $stmt5->fetchAll();
        
        sendResponse(true, 'Employee report retrieved successfully', ['report' => $report]);
    } catch (Exception $e) {
        sendResponse(false, 'Failed to get employee report', [], ['server' => $e->getMessage()], 500);
    }
} 