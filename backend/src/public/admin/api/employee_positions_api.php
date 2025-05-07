<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

class EmployeePositionsAPI {
    private $conn;
    private $table_name = "employee_positions";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy danh sách vị trí của nhân viên
    public function getEmployeePositions($params = []) {
        try {
            $query = "SELECT ep.*, e.full_name as employee_name,
                     p.name as position_name, d.name as department_name
                     FROM " . $this->table_name . " ep
                     LEFT JOIN employees e ON ep.employee_id = e.id
                     LEFT JOIN positions p ON ep.position_id = p.id
                     LEFT JOIN departments d ON ep.department_id = d.id
                     WHERE 1=1";
            
            // Thêm điều kiện tìm kiếm
            if (!empty($params['search'])) {
                $search = $params['search'];
                $query .= " AND (e.full_name LIKE '%$search%' OR p.name LIKE '%$search%')";
            }

            // Thêm điều kiện nhân viên
            if (!empty($params['employee_id'])) {
                $employee_id = $params['employee_id'];
                $query .= " AND ep.employee_id = $employee_id";
            }

            // Thêm điều kiện vị trí
            if (!empty($params['position_id'])) {
                $position_id = $params['position_id'];
                $query .= " AND ep.position_id = $position_id";
            }

            // Thêm điều kiện phòng ban
            if (!empty($params['department_id'])) {
                $department_id = $params['department_id'];
                $query .= " AND ep.department_id = $department_id";
            }

            // Thêm điều kiện vị trí hiện tại
            if (isset($params['is_current'])) {
                $is_current = $params['is_current'] ? 1 : 0;
                $query .= " AND ep.is_current = $is_current";
            }

            // Thêm phân trang
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            $query .= " ORDER BY ep.start_date DESC LIMIT $limit OFFSET $offset";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $employee_positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'status' => 'success',
                'data' => $employee_positions,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $this->getTotalEmployeePositions($params)
                ]
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Lấy thông tin chi tiết vị trí của nhân viên
    public function getEmployeePosition($id) {
        try {
            $query = "SELECT ep.*, e.full_name as employee_name,
                     p.name as position_name, d.name as department_name
                     FROM " . $this->table_name . " ep
                     LEFT JOIN employees e ON ep.employee_id = e.id
                     LEFT JOIN positions p ON ep.position_id = p.id
                     LEFT JOIN departments d ON ep.department_id = d.id
                     WHERE ep.id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $employee_position = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($employee_position) {
                // Lấy lịch sử vị trí của nhân viên
                $history = $this->getEmployeePositionHistory($employee_position['employee_id']);
                $employee_position['history'] = $history;

                return [
                    'status' => 'success',
                    'data' => $employee_position
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Employee position not found'
                ];
            }
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Thêm vị trí mới cho nhân viên
    public function createEmployeePosition($data) {
        try {
            // Bắt đầu transaction
            $this->conn->beginTransaction();

            // 1. Cập nhật vị trí hiện tại thành không còn hiện tại
            if ($data['is_current']) {
                $query = "UPDATE " . $this->table_name . "
                         SET is_current = 0,
                             end_date = CURRENT_DATE
                         WHERE employee_id = :employee_id AND is_current = 1";
                $stmt = $this->conn->prepare($query);
                $stmt->execute(['employee_id' => $data['employee_id']]);
            }

            // 2. Thêm vị trí mới
            $query = "INSERT INTO " . $this->table_name . "
                     (employee_id, position_id, department_id, start_date,
                      end_date, is_current, reason_for_change)
                     VALUES
                     (:employee_id, :position_id, :department_id, :start_date,
                      :end_date, :is_current, :reason_for_change)";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'employee_id' => $data['employee_id'],
                'position_id' => $data['position_id'],
                'department_id' => $data['department_id'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? null,
                'is_current' => $data['is_current'] ?? 1,
                'reason_for_change' => $data['reason_for_change'] ?? null
            ]);

            $employee_position_id = $this->conn->lastInsertId();

            // Commit transaction
            $this->conn->commit();

            return [
                'status' => 'success',
                'message' => 'Employee position created successfully',
                'employee_position_id' => $employee_position_id
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

    // Cập nhật thông tin vị trí của nhân viên
    public function updateEmployeePosition($id, $data) {
        try {
            $query = "UPDATE " . $this->table_name . "
                     SET position_id = :position_id,
                         department_id = :department_id,
                         start_date = :start_date,
                         end_date = :end_date,
                         is_current = :is_current,
                         reason_for_change = :reason_for_change,
                         updated_at = CURRENT_TIMESTAMP
                     WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'id' => $id,
                'position_id' => $data['position_id'],
                'department_id' => $data['department_id'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? null,
                'is_current' => $data['is_current'] ?? 1,
                'reason_for_change' => $data['reason_for_change'] ?? null
            ]);

            return [
                'status' => 'success',
                'message' => 'Employee position updated successfully'
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Xóa vị trí của nhân viên
    public function deleteEmployeePosition($id) {
        try {
            // Bắt đầu transaction
            $this->conn->beginTransaction();

            // 1. Kiểm tra xem có phải vị trí hiện tại không
            $query = "SELECT employee_id, is_current FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['is_current']) {
                throw new Exception('Cannot delete current position');
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
                'message' => 'Employee position deleted successfully'
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
    private function getEmployeePositionHistory($employee_id) {
        $query = "SELECT ep.*, p.name as position_name,
                 d.name as department_name
                 FROM " . $this->table_name . " ep
                 LEFT JOIN positions p ON ep.position_id = p.id
                 LEFT JOIN departments d ON ep.department_id = d.id
                 WHERE ep.employee_id = :employee_id
                 ORDER BY ep.start_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':employee_id', $employee_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getTotalEmployeePositions($params) {
        $query = "SELECT COUNT(*) as total 
                 FROM " . $this->table_name . " ep
                 WHERE 1=1";
        
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query .= " AND EXISTS (
                SELECT 1 FROM employees e 
                WHERE e.id = ep.employee_id 
                AND e.full_name LIKE '%$search%'
            )";
        }

        if (!empty($params['employee_id'])) {
            $employee_id = $params['employee_id'];
            $query .= " AND ep.employee_id = $employee_id";
        }

        if (!empty($params['position_id'])) {
            $position_id = $params['position_id'];
            $query .= " AND ep.position_id = $position_id";
        }

        if (!empty($params['department_id'])) {
            $department_id = $params['department_id'];
            $query .= " AND ep.department_id = $department_id";
        }

        if (isset($params['is_current'])) {
            $is_current = $params['is_current'] ? 1 : 0;
            $query .= " AND ep.is_current = $is_current";
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
$api = new EmployeePositionsAPI($db);

$method = $_SERVER['REQUEST_METHOD'];
$response = [];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            $response = $api->getEmployeePosition($_GET['id']);
        } else {
            $response = $api->getEmployeePositions($_GET);
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $response = $api->createEmployeePosition($data);
        break;
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $_GET['id'];
        $response = $api->updateEmployeePosition($id, $data);
        break;
    case 'DELETE':
        $id = $_GET['id'];
        $response = $api->deleteEmployeePosition($id);
        break;
    default:
        $response = [
            'status' => 'error',
            'message' => 'Method not allowed'
        ];
        break;
}

echo json_encode($response); 