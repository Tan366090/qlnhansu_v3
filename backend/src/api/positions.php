<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';

class PositionAPI {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Kiểm tra nếu có department_id trong query string
        if ($method === 'GET' && isset($_GET['department_id'])) {
            $this->getPositionsByDepartment($_GET['department_id']);
            return;
        }
        
        // API endpoint để lấy chức vụ theo phòng ban
        if ($method === 'GET' && isset($_GET['department_id'])) {
            try {
                $departmentId = $_GET['department_id'];
                
                $sql = "SELECT p.*, d.name as department_name 
                        FROM positions p 
                        JOIN departments d ON p.department_id = d.id 
                        WHERE p.department_id = :department_id 
                        AND p.status = 'active'";
                
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(':department_id', $departmentId);
                $stmt->execute();
                
                $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'data' => $positions
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Lỗi khi lấy danh sách chức vụ: ' . $e->getMessage()
                ]);
            }
            return;
        }

        // API endpoint để lấy thông tin hợp đồng theo chức vụ
        if ($method === 'GET' && isset($_GET['position_id'])) {
            try {
                $positionId = $_GET['position_id'];
                
                $sql = "SELECT p.*, 
                               p.min_salary as salary,
                               p.contract_type,
                               DATE_FORMAT(CURRENT_DATE(), '%Y-%m-%d') as start_date
                        FROM positions p 
                        WHERE p.id = :position_id";
                
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(':position_id', $positionId);
                $stmt->execute();
                
                $contractInfo = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($contractInfo) {
                    echo json_encode([
                        'success' => true,
                        'data' => $contractInfo
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Không tìm thấy thông tin chức vụ'
                    ]);
                }
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Lỗi khi lấy thông tin hợp đồng: ' . $e->getMessage()
                ]);
            }
            return;
        }
        
        switch ($method) {
            case 'GET':
                $this->getPositions();
                break;
            case 'POST':
                $this->createPosition();
                break;
            case 'PUT':
                $this->updatePosition();
                break;
            case 'DELETE':
                $this->deletePosition();
                break;
            default:
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
        }
    }

    private function getPositionsByDepartment($department_id) {
        try {
            $query = "SELECT p.id, p.name, p.description, p.department_id, p.salary_grade, 
                            d.name as department_name,
                            p.created_at, p.updated_at 
                     FROM positions p
                     LEFT JOIN departments d ON p.department_id = d.id
                     WHERE p.department_id = :department_id
                     ORDER BY p.name ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':department_id' => $department_id]);

            $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => $positions
            ]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi kết nối cơ sở dữ liệu: ' . $e->getMessage()
            ]);
        }
    }

    private function getPositions() {
        try {
            $query = "SELECT p.id, p.name, p.description, p.department_id, p.salary_grade, 
                            d.name as department_name,
                            p.created_at, p.updated_at 
                     FROM positions p
                     LEFT JOIN departments d ON p.department_id = d.id
                     ORDER BY p.name ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => $positions
            ]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi kết nối cơ sở dữ liệu: ' . $e->getMessage()
            ]);
        }
    }

    private function createPosition() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['name']) || !isset($data['department_id']) || !isset($data['salary_grade'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Tên chức vụ, mã phòng ban và bậc lương là bắt buộc']);
                return;
            }

            $query = "INSERT INTO positions (name, description, department_id, salary_grade) 
                     VALUES (:name, :description, :department_id, :salary_grade)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':name' => $data['name'],
                ':description' => $data['description'] ?? null,
                ':department_id' => $data['department_id'],
                ':salary_grade' => $data['salary_grade']
            ]);

            $positionId = $this->conn->lastInsertId();
            
            echo json_encode([
                'success' => true,
                'message' => 'Tạo chức vụ thành công',
                'data' => ['id' => $positionId]
            ]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi tạo chức vụ: ' . $e->getMessage()
            ]);
        }
    }

    private function updatePosition() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id']) || !isset($data['name']) || !isset($data['department_id']) || !isset($data['salary_grade'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID, tên chức vụ, mã phòng ban và bậc lương là bắt buộc']);
                return;
            }

            $query = "UPDATE positions 
                     SET name = :name, 
                         description = :description,
                         department_id = :department_id,
                         salary_grade = :salary_grade,
                         updated_at = CURRENT_TIMESTAMP
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':id' => $data['id'],
                ':name' => $data['name'],
                ':description' => $data['description'] ?? null,
                ':department_id' => $data['department_id'],
                ':salary_grade' => $data['salary_grade']
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật chức vụ thành công'
            ]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi cập nhật chức vụ: ' . $e->getMessage()
            ]);
        }
    }

    private function deletePosition() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID chức vụ là bắt buộc']);
                return;
            }

            // Kiểm tra xem chức vụ có nhân viên không
            $checkQuery = "SELECT COUNT(*) FROM employees WHERE position_id = :id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->execute([':id' => $data['id']]);
            
            if ($checkStmt->fetchColumn() > 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Không thể xóa chức vụ vì vẫn còn nhân viên'
                ]);
                return;
            }

            $query = "DELETE FROM positions WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $data['id']]);

            echo json_encode([
                'success' => true,
                'message' => 'Xóa chức vụ thành công'
            ]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi xóa chức vụ: ' . $e->getMessage()
            ]);
        }
    }
}

$api = new PositionAPI();
$api->handleRequest(); 