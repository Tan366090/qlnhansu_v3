<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

class DepartmentsAPI {
    private $conn;
    private $table_name = "departments";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy danh sách phòng ban
    public function getDepartments($params = []) {
        try {
            $query = "SELECT d.*, 
                     p.name as parent_name,
                     e.full_name as manager_name,
                     (SELECT COUNT(*) FROM employees WHERE department_id = d.id) as employee_count
                     FROM " . $this->table_name . " d
                     LEFT JOIN " . $this->table_name . " p ON d.parent_id = p.id
                     LEFT JOIN employees e ON d.manager_id = e.id
                     LEFT JOIN user_profiles up ON e.user_id = up.user_id
                     WHERE 1=1";
            
            // Thêm điều kiện tìm kiếm
            if (!empty($params['search'])) {
                $search = $params['search'];
                $query .= " AND (d.name LIKE '%$search%' OR d.description LIKE '%$search%')";
            }

            // Thêm điều kiện phòng ban cha
            if (!empty($params['parent_id'])) {
                $parent_id = $params['parent_id'];
                $query .= " AND d.parent_id = $parent_id";
            }

            // Thêm điều kiện trưởng phòng
            if (!empty($params['manager_id'])) {
                $manager_id = $params['manager_id'];
                $query .= " AND d.manager_id = $manager_id";
            }

            // Thêm phân trang
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            $query .= " ORDER BY d.created_at DESC LIMIT $limit OFFSET $offset";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'status' => 'success',
                'data' => $departments,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $this->getTotalDepartments($params)
                ]
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Lấy thông tin chi tiết phòng ban
    public function getDepartment($id) {
        try {
            $query = "SELECT d.*, 
                     p.name as parent_name,
                     e.full_name as manager_name,
                     (SELECT COUNT(*) FROM employees WHERE department_id = d.id) as employee_count
                     FROM " . $this->table_name . " d
                     LEFT JOIN " . $this->table_name . " p ON d.parent_id = p.id
                     LEFT JOIN employees e ON d.manager_id = e.id
                     LEFT JOIN user_profiles up ON e.user_id = up.user_id
                     WHERE d.id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $department = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($department) {
                // Lấy danh sách vị trí trong phòng ban
                $positions = $this->getDepartmentPositions($id);
                $department['positions'] = $positions;

                // Lấy danh sách nhân viên trong phòng ban
                $employees = $this->getDepartmentEmployees($id);
                $department['employees'] = $employees;

                // Lấy danh sách phòng ban con
                $children = $this->getDepartmentChildren($id);
                $department['children'] = $children;

                return [
                    'status' => 'success',
                    'data' => $department
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Department not found'
                ];
            }
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Thêm phòng ban mới
    public function createDepartment($data) {
        try {
            $query = "INSERT INTO " . $this->table_name . "
                     (name, description, parent_id, manager_id)
                     VALUES
                     (:name, :description, :parent_id, :manager_id)";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'parent_id' => $data['parent_id'] ?? null,
                'manager_id' => $data['manager_id'] ?? null
            ]);

            $department_id = $this->conn->lastInsertId();

            return [
                'status' => 'success',
                'message' => 'Department created successfully',
                'department_id' => $department_id
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Cập nhật thông tin phòng ban
    public function updateDepartment($id, $data) {
        try {
            $query = "UPDATE " . $this->table_name . "
                     SET name = :name,
                         description = :description,
                         parent_id = :parent_id,
                         manager_id = :manager_id,
                         updated_at = CURRENT_TIMESTAMP
                     WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'id' => $id,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'parent_id' => $data['parent_id'] ?? null,
                'manager_id' => $data['manager_id'] ?? null
            ]);

            return [
                'status' => 'success',
                'message' => 'Department updated successfully'
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Xóa phòng ban
    public function deleteDepartment($id) {
        try {
            // Bắt đầu transaction
            $this->conn->beginTransaction();

            // 1. Kiểm tra xem phòng ban có phòng ban con không
            $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE parent_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                throw new Exception('Cannot delete department with child departments');
            }

            // 2. Kiểm tra xem phòng ban có nhân viên không
            $query = "SELECT COUNT(*) as count FROM employees WHERE department_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                throw new Exception('Cannot delete department with employees');
            }

            // 3. Xóa các vị trí trong phòng ban
            $query = "DELETE FROM positions WHERE department_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            // 4. Xóa phòng ban
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            // Commit transaction
            $this->conn->commit();

            return [
                'status' => 'success',
                'message' => 'Department deleted successfully'
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
    private function getDepartmentPositions($department_id) {
        $query = "SELECT p.*, COUNT(e.id) as employee_count
                 FROM positions p
                 LEFT JOIN employees e ON p.id = e.position_id
                 WHERE p.department_id = :department_id
                 GROUP BY p.id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':department_id', $department_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getDepartmentEmployees($department_id) {
        $query = "SELECT e.*, up.full_name, p.name as position_name
                 FROM employees e
                 LEFT JOIN user_profiles up ON e.user_id = up.user_id
                 LEFT JOIN positions p ON e.position_id = p.id
                 WHERE e.department_id = :department_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':department_id', $department_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getDepartmentChildren($department_id) {
        $query = "SELECT d.*, 
                 e.full_name as manager_name,
                 (SELECT COUNT(*) FROM employees WHERE department_id = d.id) as employee_count
                 FROM " . $this->table_name . " d
                 LEFT JOIN employees e ON d.manager_id = e.id
                 LEFT JOIN user_profiles up ON e.user_id = up.user_id
                 WHERE d.parent_id = :department_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':department_id', $department_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getTotalDepartments($params) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE 1=1";
        
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query .= " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
        }

        if (!empty($params['parent_id'])) {
            $parent_id = $params['parent_id'];
            $query .= " AND parent_id = $parent_id";
        }

        if (!empty($params['manager_id'])) {
            $manager_id = $params['manager_id'];
            $query .= " AND manager_id = $manager_id";
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
$api = new DepartmentsAPI($db);

$method = $_SERVER['REQUEST_METHOD'];
$response = [];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            $response = $api->getDepartment($_GET['id']);
        } else {
            $response = $api->getDepartments($_GET);
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $response = $api->createDepartment($data);
        break;
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $_GET['id'];
        $response = $api->updateDepartment($id, $data);
        break;
    case 'DELETE':
        $id = $_GET['id'];
        $response = $api->deleteDepartment($id);
        break;
    default:
        $response = [
            'status' => 'error',
            'message' => 'Method not allowed'
        ];
        break;
}

echo json_encode($response); 