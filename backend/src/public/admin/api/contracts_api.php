<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

class ContractsAPI {
    private $conn;
    private $table_name = "contracts";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy danh sách hợp đồng
    public function getContracts($params = []) {
        try {
            $query = "SELECT c.*, e.full_name as employee_name,
                     ct.name as contract_type_name, d.name as department_name
                     FROM " . $this->table_name . " c
                     LEFT JOIN employees e ON c.employee_id = e.id
                     LEFT JOIN contract_types ct ON c.contract_type_id = ct.id
                     LEFT JOIN departments d ON c.department_id = d.id
                     WHERE 1=1";
            
            // Thêm điều kiện tìm kiếm
            if (!empty($params['search'])) {
                $search = $params['search'];
                $query .= " AND (c.contract_code LIKE '%$search%' OR e.full_name LIKE '%$search%')";
            }

            // Thêm điều kiện nhân viên
            if (!empty($params['employee_id'])) {
                $employee_id = $params['employee_id'];
                $query .= " AND c.employee_id = $employee_id";
            }

            // Thêm điều kiện loại hợp đồng
            if (!empty($params['contract_type_id'])) {
                $contract_type_id = $params['contract_type_id'];
                $query .= " AND c.contract_type_id = $contract_type_id";
            }

            // Thêm điều kiện phòng ban
            if (!empty($params['department_id'])) {
                $department_id = $params['department_id'];
                $query .= " AND c.department_id = $department_id";
            }

            // Thêm điều kiện trạng thái
            if (isset($params['status'])) {
                $status = $params['status'];
                $query .= " AND c.status = '$status'";
            }

            // Thêm phân trang
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            $query .= " ORDER BY c.created_at DESC LIMIT $limit OFFSET $offset";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $contracts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'status' => 'success',
                'data' => $contracts,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $this->getTotalContracts($params)
                ]
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Lấy thông tin chi tiết hợp đồng
    public function getContract($id) {
        try {
            $query = "SELECT c.*, e.full_name as employee_name,
                     ct.name as contract_type_name, d.name as department_name
                     FROM " . $this->table_name . " c
                     LEFT JOIN employees e ON c.employee_id = e.id
                     LEFT JOIN contract_types ct ON c.contract_type_id = ct.id
                     LEFT JOIN departments d ON c.department_id = d.id
                     WHERE c.id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $contract = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($contract) {
                // Lấy lịch sử hợp đồng
                $history = $this->getContractHistory($contract['employee_id']);
                $contract['history'] = $history;

                // Lấy thông tin phụ lục
                $attachments = $this->getContractAttachments($id);
                $contract['attachments'] = $attachments;

                return [
                    'status' => 'success',
                    'data' => $contract
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Contract not found'
                ];
            }
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Thêm hợp đồng mới
    public function createContract($data) {
        try {
            // Bắt đầu transaction
            $this->conn->beginTransaction();

            // 1. Kiểm tra xem nhân viên đã có hợp đồng hiện tại chưa
            if ($data['status'] == 'active') {
                $query = "UPDATE " . $this->table_name . "
                         SET status = 'inactive',
                             end_date = CURRENT_DATE
                         WHERE employee_id = :employee_id AND status = 'active'";
                $stmt = $this->conn->prepare($query);
                $stmt->execute(['employee_id' => $data['employee_id']]);
            }

            // 2. Thêm hợp đồng mới
            $query = "INSERT INTO " . $this->table_name . "
                     (contract_code, employee_id, contract_type_id, department_id,
                      start_date, end_date, salary, status, description)
                     VALUES
                     (:contract_code, :employee_id, :contract_type_id, :department_id,
                      :start_date, :end_date, :salary, :status, :description)";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'contract_code' => $data['contract_code'],
                'employee_id' => $data['employee_id'],
                'contract_type_id' => $data['contract_type_id'],
                'department_id' => $data['department_id'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? null,
                'salary' => $data['salary'],
                'status' => $data['status'] ?? 'active',
                'description' => $data['description'] ?? null
            ]);

            $contract_id = $this->conn->lastInsertId();

            // 3. Thêm phụ lục nếu có
            if (!empty($data['attachments'])) {
                $this->addContractAttachments($contract_id, $data['attachments']);
            }

            // Commit transaction
            $this->conn->commit();

            return [
                'status' => 'success',
                'message' => 'Contract created successfully',
                'contract_id' => $contract_id
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

    // Cập nhật thông tin hợp đồng
    public function updateContract($id, $data) {
        try {
            $query = "UPDATE " . $this->table_name . "
                     SET contract_code = :contract_code,
                         contract_type_id = :contract_type_id,
                         department_id = :department_id,
                         start_date = :start_date,
                         end_date = :end_date,
                         salary = :salary,
                         status = :status,
                         description = :description,
                         updated_at = CURRENT_TIMESTAMP
                     WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'id' => $id,
                'contract_code' => $data['contract_code'],
                'contract_type_id' => $data['contract_type_id'],
                'department_id' => $data['department_id'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? null,
                'salary' => $data['salary'],
                'status' => $data['status'] ?? 'active',
                'description' => $data['description'] ?? null
            ]);

            return [
                'status' => 'success',
                'message' => 'Contract updated successfully'
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Xóa hợp đồng
    public function deleteContract($id) {
        try {
            // Bắt đầu transaction
            $this->conn->beginTransaction();

            // 1. Kiểm tra xem có phải hợp đồng hiện tại không
            $query = "SELECT employee_id, status FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['status'] == 'active') {
                throw new Exception('Cannot delete active contract');
            }

            // 2. Xóa phụ lục
            $query = "DELETE FROM contract_attachments WHERE contract_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            // 3. Xóa hợp đồng
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            // Commit transaction
            $this->conn->commit();

            return [
                'status' => 'success',
                'message' => 'Contract deleted successfully'
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
    private function getContractHistory($employee_id) {
        $query = "SELECT c.*, ct.name as contract_type_name,
                 d.name as department_name
                 FROM " . $this->table_name . " c
                 LEFT JOIN contract_types ct ON c.contract_type_id = ct.id
                 LEFT JOIN departments d ON c.department_id = d.id
                 WHERE c.employee_id = :employee_id
                 ORDER BY c.start_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':employee_id', $employee_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getContractAttachments($contract_id) {
        $query = "SELECT * FROM contract_attachments WHERE contract_id = :contract_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':contract_id', $contract_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function addContractAttachments($contract_id, $attachments) {
        $query = "INSERT INTO contract_attachments
                 (contract_id, file_name, file_path, description)
                 VALUES
                 (:contract_id, :file_name, :file_path, :description)";

        $stmt = $this->conn->prepare($query);
        foreach ($attachments as $attachment) {
            $stmt->execute([
                'contract_id' => $contract_id,
                'file_name' => $attachment['file_name'],
                'file_path' => $attachment['file_path'],
                'description' => $attachment['description'] ?? null
            ]);
        }
    }

    private function getTotalContracts($params) {
        $query = "SELECT COUNT(*) as total 
                 FROM " . $this->table_name . " c
                 WHERE 1=1";
        
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query .= " AND (c.contract_code LIKE '%$search%' OR EXISTS (
                SELECT 1 FROM employees e 
                WHERE e.id = c.employee_id 
                AND e.full_name LIKE '%$search%'
            ))";
        }

        if (!empty($params['employee_id'])) {
            $employee_id = $params['employee_id'];
            $query .= " AND c.employee_id = $employee_id";
        }

        if (!empty($params['contract_type_id'])) {
            $contract_type_id = $params['contract_type_id'];
            $query .= " AND c.contract_type_id = $contract_type_id";
        }

        if (!empty($params['department_id'])) {
            $department_id = $params['department_id'];
            $query .= " AND c.department_id = $department_id";
        }

        if (isset($params['status'])) {
            $status = $params['status'];
            $query .= " AND c.status = '$status'";
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
$api = new ContractsAPI($db);

$method = $_SERVER['REQUEST_METHOD'];
$response = [];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            $response = $api->getContract($_GET['id']);
        } else {
            $response = $api->getContracts($_GET);
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $response = $api->createContract($data);
        break;
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $_GET['id'];
        $response = $api->updateContract($id, $data);
        break;
    case 'DELETE':
        $id = $_GET['id'];
        $response = $api->deleteContract($id);
        break;
    default:
        $response = [
            'status' => 'error',
            'message' => 'Method not allowed'
        ];
        break;
}

echo json_encode($response); 