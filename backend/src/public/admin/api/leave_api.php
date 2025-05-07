<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

class LeaveAPI {
    private $conn;
    private $table_name = "leaves";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy danh sách đơn xin nghỉ
    public function getLeaves($params = []) {
        try {
            $query = "SELECT l.*, e.full_name as employee_name,
                     d.name as department_name, lt.name as leave_type_name,
                     a.full_name as approver_name
                     FROM " . $this->table_name . " l
                     LEFT JOIN employees e ON l.employee_id = e.id
                     LEFT JOIN departments d ON e.department_id = d.id
                     LEFT JOIN leave_types lt ON l.leave_type_id = lt.id
                     LEFT JOIN employees a ON l.approver_id = a.id
                     WHERE 1=1";
            
            // Thêm điều kiện tìm kiếm
            if (!empty($params['search'])) {
                $search = $params['search'];
                $query .= " AND (l.leave_code LIKE '%$search%' OR e.full_name LIKE '%$search%')";
            }

            // Thêm điều kiện nhân viên
            if (!empty($params['employee_id'])) {
                $employee_id = $params['employee_id'];
                $query .= " AND l.employee_id = $employee_id";
            }

            // Thêm điều kiện phòng ban
            if (!empty($params['department_id'])) {
                $department_id = $params['department_id'];
                $query .= " AND e.department_id = $department_id";
            }

            // Thêm điều kiện loại nghỉ
            if (!empty($params['leave_type_id'])) {
                $leave_type_id = $params['leave_type_id'];
                $query .= " AND l.leave_type_id = $leave_type_id";
            }

            // Thêm điều kiện trạng thái
            if (isset($params['status'])) {
                $status = $params['status'];
                $query .= " AND l.status = '$status'";
            }

            // Thêm điều kiện khoảng thời gian
            if (!empty($params['start_date']) && !empty($params['end_date'])) {
                $start_date = $params['start_date'];
                $end_date = $params['end_date'];
                $query .= " AND ((l.start_date BETWEEN '$start_date' AND '$end_date')
                          OR (l.end_date BETWEEN '$start_date' AND '$end_date'))";
            }

            // Thêm phân trang
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            $query .= " ORDER BY l.created_at DESC LIMIT $limit OFFSET $offset";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $leaves = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'status' => 'success',
                'data' => $leaves,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $this->getTotalLeaves($params)
                ]
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Lấy thông tin chi tiết đơn xin nghỉ
    public function getLeaveDetail($id) {
        try {
            $query = "SELECT l.*, e.full_name as employee_name,
                     d.name as department_name, lt.name as leave_type_name,
                     a.full_name as approver_name
                     FROM " . $this->table_name . " l
                     LEFT JOIN employees e ON l.employee_id = e.id
                     LEFT JOIN departments d ON e.department_id = d.id
                     LEFT JOIN leave_types lt ON l.leave_type_id = lt.id
                     LEFT JOIN employees a ON l.approver_id = a.id
                     WHERE l.id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $leave = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($leave) {
                // Lấy danh sách file đính kèm
                $attachments = $this->getLeaveAttachments($id);
                $leave['attachments'] = $attachments;

                return [
                    'status' => 'success',
                    'data' => $leave
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Leave request not found'
                ];
            }
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Tạo đơn xin nghỉ mới
    public function createLeave($data) {
        try {
            // Bắt đầu transaction
            $this->conn->beginTransaction();

            // 1. Tạo đơn xin nghỉ
            $query = "INSERT INTO " . $this->table_name . "
                     (leave_code, employee_id, leave_type_id,
                      start_date, end_date, reason,
                      status, approver_id, approved_at)
                     VALUES
                     (:leave_code, :employee_id, :leave_type_id,
                      :start_date, :end_date, :reason,
                      :status, :approver_id, :approved_at)";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'leave_code' => $data['leave_code'],
                'employee_id' => $data['employee_id'],
                'leave_type_id' => $data['leave_type_id'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'reason' => $data['reason'],
                'status' => $data['status'] ?? 'pending',
                'approver_id' => $data['approver_id'] ?? null,
                'approved_at' => $data['approved_at'] ?? null
            ]);

            $leave_id = $this->conn->lastInsertId();

            // 2. Thêm file đính kèm nếu có
            if (!empty($data['attachments'])) {
                $this->addLeaveAttachments($leave_id, $data['attachments']);
            }

            // Commit transaction
            $this->conn->commit();

            return [
                'status' => 'success',
                'message' => 'Leave request created successfully',
                'leave_id' => $leave_id
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

    // Cập nhật đơn xin nghỉ
    public function updateLeave($id, $data) {
        try {
            $query = "UPDATE " . $this->table_name . "
                     SET leave_type_id = :leave_type_id,
                         start_date = :start_date,
                         end_date = :end_date,
                         reason = :reason,
                         status = :status,
                         approver_id = :approver_id,
                         approved_at = :approved_at,
                         updated_at = CURRENT_TIMESTAMP
                     WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'id' => $id,
                'leave_type_id' => $data['leave_type_id'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'reason' => $data['reason'],
                'status' => $data['status'],
                'approver_id' => $data['approver_id'] ?? null,
                'approved_at' => $data['approved_at'] ?? null
            ]);

            return [
                'status' => 'success',
                'message' => 'Leave request updated successfully'
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Xóa đơn xin nghỉ
    public function deleteLeave($id) {
        try {
            // Bắt đầu transaction
            $this->conn->beginTransaction();

            // 1. Xóa file đính kèm
            $query = "DELETE FROM leave_attachments WHERE leave_id = :leave_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':leave_id', $id);
            $stmt->execute();

            // 2. Xóa đơn xin nghỉ
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            // Commit transaction
            $this->conn->commit();

            return [
                'status' => 'success',
                'message' => 'Leave request deleted successfully'
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
    private function getLeaveAttachments($leave_id) {
        $query = "SELECT * FROM leave_attachments WHERE leave_id = :leave_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':leave_id', $leave_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function addLeaveAttachments($leave_id, $attachments) {
        $query = "INSERT INTO leave_attachments
                 (leave_id, file_name, file_path, file_type, file_size)
                 VALUES
                 (:leave_id, :file_name, :file_path, :file_type, :file_size)";

        $stmt = $this->conn->prepare($query);
        foreach ($attachments as $attachment) {
            $stmt->execute([
                'leave_id' => $leave_id,
                'file_name' => $attachment['file_name'],
                'file_path' => $attachment['file_path'],
                'file_type' => $attachment['file_type'],
                'file_size' => $attachment['file_size']
            ]);
        }
    }

    private function getTotalLeaves($params) {
        $query = "SELECT COUNT(*) as total 
                 FROM " . $this->table_name . " l
                 LEFT JOIN employees e ON l.employee_id = e.id
                 WHERE 1=1";
        
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query .= " AND (l.leave_code LIKE '%$search%' OR EXISTS (
                SELECT 1 FROM employees e 
                WHERE e.id = l.employee_id 
                AND e.full_name LIKE '%$search%'
            ))";
        }

        if (!empty($params['employee_id'])) {
            $employee_id = $params['employee_id'];
            $query .= " AND l.employee_id = $employee_id";
        }

        if (!empty($params['department_id'])) {
            $department_id = $params['department_id'];
            $query .= " AND e.department_id = $department_id";
        }

        if (!empty($params['leave_type_id'])) {
            $leave_type_id = $params['leave_type_id'];
            $query .= " AND l.leave_type_id = $leave_type_id";
        }

        if (isset($params['status'])) {
            $status = $params['status'];
            $query .= " AND l.status = '$status'";
        }

        if (!empty($params['start_date']) && !empty($params['end_date'])) {
            $start_date = $params['start_date'];
            $end_date = $params['end_date'];
            $query .= " AND ((l.start_date BETWEEN '$start_date' AND '$end_date')
                          OR (l.end_date BETWEEN '$start_date' AND '$end_date'))";
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
$api = new LeaveAPI($db);

$method = $_SERVER['REQUEST_METHOD'];
$response = [];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            $response = $api->getLeaveDetail($_GET['id']);
        } else {
            $response = $api->getLeaves($_GET);
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $response = $api->createLeave($data);
        break;
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $_GET['id'];
        $response = $api->updateLeave($id, $data);
        break;
    case 'DELETE':
        $id = $_GET['id'];
        $response = $api->deleteLeave($id);
        break;
    default:
        $response = [
            'status' => 'error',
            'message' => 'Method not allowed'
        ];
        break;
}

echo json_encode($response); 