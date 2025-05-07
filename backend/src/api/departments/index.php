<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../config/database.php';

class DepartmentAPI {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch ($method) {
            case 'GET':
                $this->getDepartments();
                break;
            case 'POST':
                $this->createDepartment();
                break;
            case 'PUT':
                $this->updateDepartment();
                break;
            case 'DELETE':
                $this->deleteDepartment();
                break;
            default:
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
        }
    }

    private function getDepartments() {
        try {
            $query = "SELECT id, name, description, manager_id, parent_id, created_at, updated_at 
                     FROM departments 
                     ORDER BY name ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => $departments
            ]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi kết nối cơ sở dữ liệu: ' . $e->getMessage()
            ]);
        }
    }

    private function createDepartment() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['name'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Tên phòng ban là bắt buộc']);
                return;
            }

            $query = "INSERT INTO departments (name, description, manager_id, parent_id) 
                     VALUES (:name, :description, :manager_id, :parent_id)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':name' => $data['name'],
                ':description' => $data['description'] ?? null,
                ':manager_id' => $data['manager_id'] ?? null,
                ':parent_id' => $data['parent_id'] ?? null
            ]);

            $departmentId = $this->conn->lastInsertId();
            
            echo json_encode([
                'success' => true,
                'message' => 'Tạo phòng ban thành công',
                'data' => ['id' => $departmentId]
            ]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi tạo phòng ban: ' . $e->getMessage()
            ]);
        }
    }

    private function updateDepartment() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id']) || !isset($data['name'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID và tên phòng ban là bắt buộc']);
                return;
            }

            $query = "UPDATE departments 
                     SET name = :name, 
                         description = :description,
                         manager_id = :manager_id,
                         parent_id = :parent_id,
                         updated_at = CURRENT_TIMESTAMP
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':id' => $data['id'],
                ':name' => $data['name'],
                ':description' => $data['description'] ?? null,
                ':manager_id' => $data['manager_id'] ?? null,
                ':parent_id' => $data['parent_id'] ?? null
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật phòng ban thành công'
            ]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi cập nhật phòng ban: ' . $e->getMessage()
            ]);
        }
    }

    private function deleteDepartment() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID phòng ban là bắt buộc']);
                return;
            }

            // Kiểm tra xem phòng ban có nhân viên không
            $checkQuery = "SELECT COUNT(*) FROM employees WHERE department_id = :id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->execute([':id' => $data['id']]);
            
            if ($checkStmt->fetchColumn() > 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Không thể xóa phòng ban vì vẫn còn nhân viên'
                ]);
                return;
            }

            $query = "DELETE FROM departments WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $data['id']]);

            echo json_encode([
                'success' => true,
                'message' => 'Xóa phòng ban thành công'
            ]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi xóa phòng ban: ' . $e->getMessage()
            ]);
        }
    }
}

$api = new DepartmentAPI();
$api->handleRequest(); 