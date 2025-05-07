<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../middleware/auth.php';

// Kiểm tra xác thực
$auth = checkAuth();
if (!$auth['authenticated']) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Lấy phương thức HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Xử lý các phương thức HTTP
switch ($method) {
    case 'GET':
        // Lấy danh sách bằng cấp hoặc chi tiết một bằng cấp
        if (isset($_GET['id'])) {
            getCertificate($_GET['id']);
        } else {
            getCertificates();
        }
        break;
        
    case 'POST':
        // Thêm bằng cấp mới
        addCertificate();
        break;
        
    case 'PUT':
        // Cập nhật bằng cấp
        updateCertificate();
        break;
        
    case 'DELETE':
        // Xóa bằng cấp
        deleteCertificate();
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}

// Lấy danh sách bằng cấp
function getCertificates() {
    global $conn;
    
    try {
        $sql = "SELECT c.*, e.name as employee_name, d.name as department_name 
                FROM certificates c 
                LEFT JOIN employees e ON c.employee_id = e.id 
                LEFT JOIN departments d ON e.department_id = d.id 
                ORDER BY c.issue_date DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $certificates = [];
        while ($row = $result->fetch_assoc()) {
            $certificates[] = $row;
        }
        
        echo json_encode($certificates);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Lấy chi tiết một bằng cấp
function getCertificate($id) {
    global $conn;
    
    try {
        $sql = "SELECT c.*, e.name as employee_name, d.name as department_name 
                FROM certificates c 
                LEFT JOIN employees e ON c.employee_id = e.id 
                LEFT JOIN departments d ON e.department_id = d.id 
                WHERE c.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Certificate not found']);
            return;
        }
        
        echo json_encode($result->fetch_assoc());
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Thêm bằng cấp mới
function addCertificate() {
    global $conn;
    
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate input
        if (!isset($data['employee_id']) || !isset($data['name']) || 
            !isset($data['issuing_organization']) || !isset($data['issue_date'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }
        
        $sql = "INSERT INTO certificates (employee_id, name, issuing_organization, issue_date, expiry_date, description) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssss", 
            $data['employee_id'],
            $data['name'],
            $data['issuing_organization'],
            $data['issue_date'],
            $data['expiry_date'] ?? null,
            $data['description'] ?? null
        );
        
        if ($stmt->execute()) {
            $id = $conn->insert_id;
            echo json_encode(['id' => $id, 'message' => 'Certificate added successfully']);
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Cập nhật bằng cấp
function updateCertificate() {
    global $conn;
    
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Certificate ID is required']);
            return;
        }
        
        $sql = "UPDATE certificates SET 
                employee_id = ?, 
                name = ?, 
                issuing_organization = ?, 
                issue_date = ?, 
                expiry_date = ?, 
                description = ? 
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssssi", 
            $data['employee_id'],
            $data['name'],
            $data['issuing_organization'],
            $data['issue_date'],
            $data['expiry_date'] ?? null,
            $data['description'] ?? null,
            $data['id']
        );
        
        if ($stmt->execute()) {
            echo json_encode(['message' => 'Certificate updated successfully']);
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Xóa bằng cấp
function deleteCertificate() {
    global $conn;
    
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Certificate ID is required']);
            return;
        }
        
        $sql = "DELETE FROM certificates WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $data['id']);
        
        if ($stmt->execute()) {
            echo json_encode(['message' => 'Certificate deleted successfully']);
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?> 