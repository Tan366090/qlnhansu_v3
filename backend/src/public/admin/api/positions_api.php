<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

class PositionsAPI {
    private $conn;
    private $table_name = "positions";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy danh sách vị trí
    public function getPositions($params = []) {
        try {
            $query = "SELECT p.*, d.name as department_name, 
                     COUNT(e.id) as employee_count
                     FROM " . $this->table_name . " p
                     LEFT JOIN departments d ON p.department_id = d.id
                     LEFT JOIN employees e ON p.id = e.position_id
                     WHERE 1=1";
            
            // Thêm điều kiện tìm kiếm
            if (!empty($params['search'])) {
                $search = $params['search'];
                $query .= " AND (p.name LIKE '%$search%' OR p.description LIKE '%$search%')";
            }

            // Thêm điều kiện phòng ban
            if (!empty($params['department_id'])) {
                $department_id = $params['department_id'];
                $query .= " AND p.department_id = $department_id";
            }

            // Thêm điều kiện bậc lương
            if (!empty($params['salary_grade'])) {
                $salary_grade = $params['salary_grade'];
                $query .= " AND p.salary_grade = '$salary_grade'";
            }

            // Thêm phân trang
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            $query .= " GROUP BY p.id ORDER BY p.created_at DESC LIMIT $limit OFFSET $offset";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'status' => 'success',
                'data' => $positions,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $this->getTotalPositions($params)
                ]
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Lấy thông tin chi tiết vị trí
    public function getPosition($id) {
        try {
            $query = "SELECT p.*, d.name as department_name,
                     COUNT(e.id) as employee_count
                     FROM " . $this->table_name . " p
                     LEFT JOIN departments d ON p.department_id = d.id
                     LEFT JOIN employees e ON p.id = e.position_id
                     WHERE p.id = :id
                     GROUP BY p.id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $position = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($position) {
                // Lấy danh sách nhân viên ở vị trí này
                $employees = $this->getPositionEmployees($id);
                $position['employees'] = $employees;

                // Lấy lịch sử vị trí
                $history = $this->getPositionHistory($id);
                $position['history'] = $history;

                return [
                    'status' => 'success',
                    'data' => $position
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Position not found'
                ];
            }
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Thêm vị trí mới
    public function createPosition($data) {
        try {
            $query = "INSERT INTO " . $this->table_name . "
                     (name, description, department_id, salary_grade)
                     VALUES
                     (:name, :description, :department_id, :salary_grade)";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'department_id' => $data['department_id'],
                'salary_grade' => $data['salary_grade'] ?? null
            ]);

            $position_id = $this->conn->lastInsertId();

            return [
                'status' => 'success',
                'message' => 'Position created successfully',
                'position_id' => $position_id
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Cập nhật thông tin vị trí
    public function updatePosition($id, $data) {
        try {
            $query = "UPDATE " . $this->table_name . "
                     SET name = :name,
                         description = :description,
                         department_id = :department_id,
                         salary_grade = :salary_grade,
                         updated_at = CURRENT_TIMESTAMP
                     WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'id' => $id,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'department_id' => $data['department_id'],
                'salary_grade' => $data['salary_grade'] ?? null
            ]);

            return [
                'status' => 'success',
                'message' => 'Position updated successfully'
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Xóa vị trí
    public function deletePosition($id) {
        try {
            // Bắt đầu transaction
            $this->conn->beginTransaction();

            // 1. Kiểm tra xem vị trí có nhân viên không
            $query = "SELECT COUNT(*) as count FROM employees WHERE position_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                throw new Exception('Cannot delete position with employees');
            }

            // 2. Xóa vị trí
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            // Commit transaction
            $this->conn->commit();

            return [
                'status' => 'success',
                'message' => 'Position deleted successfully'
            ];
        } catch(Exception $e) {
            // Rollback transaction nếu có lỗi
            $this->conn->rollBack();
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Các hàm helper
    private function getPositionEmployees($position_id) {
        $query = "SELECT e.*, up.full_name, d.name as department_name
                 FROM employees e
                 LEFT JOIN user_profiles up ON e.user_id = up.user_id
                 LEFT JOIN departments d ON e.department_id = d.id
                 WHERE e.position_id = :position_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':position_id', $position_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getPositionHistory($position_id) {
        $query = "SELECT ep.*, e.full_name as employee_name,
                 d.name as department_name, p.name as position_name
                 FROM employee_positions ep
                 LEFT JOIN employees e ON ep.employee_id = e.id
                 LEFT JOIN departments d ON ep.department_id = d.id
                 LEFT JOIN positions p ON ep.position_id = p.id
                 WHERE ep.position_id = :position_id
                 ORDER BY ep.start_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':position_id', $position_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getTotalPositions($params) {
        $query = "SELECT COUNT(DISTINCT p.id) as total 
                 FROM " . $this->table_name . " p
                 WHERE 1=1";
        
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query .= " AND (p.name LIKE '%$search%' OR p.description LIKE '%$search%')";
        }

        if (!empty($params['department_id'])) {
            $department_id = $params['department_id'];
            $query .= " AND p.department_id = $department_id";
        }

        if (!empty($params['salary_grade'])) {
            $salary_grade = $params['salary_grade'];
            $query .= " AND p.salary_grade = '$salary_grade'";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
}

// Xử lý request
$database = new Database();
$db = $database->getConnection();
$api = new PositionsAPI($db);

$method = $_SERVER['REQUEST_METHOD'];
$response = [];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            $response = $api->getPosition($_GET['id']);
        } else {
            $response = $api->getPositions($_GET);
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $response = $api->createPosition($data);
        break;
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $_GET['id'];
        $response = $api->updatePosition($id, $data);
        break;
    case 'DELETE':
        $id = $_GET['id'];
        $response = $api->deletePosition($id);
        break;
    default:
        $response = [
            'status' => 'error',
            'message' => 'Method not allowed'
        ];
        break;
}

echo json_encode($response); 