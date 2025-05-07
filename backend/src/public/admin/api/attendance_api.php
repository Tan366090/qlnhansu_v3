<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

class AttendanceAPI {
    private $conn;
    private $table_name = "attendance";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy danh sách chấm công
    public function getAttendance($params = []) {
        try {
            $query = "SELECT a.*, e.full_name as employee_name,
                     d.name as department_name, ws.name as work_schedule_name
                     FROM " . $this->table_name . " a
                     LEFT JOIN employees e ON a.employee_id = e.id
                     LEFT JOIN departments d ON e.department_id = d.id
                     LEFT JOIN work_schedules ws ON a.work_schedule_id = ws.id
                     WHERE 1=1";
            
            // Thêm điều kiện tìm kiếm
            if (!empty($params['search'])) {
                $search = $params['search'];
                $query .= " AND (a.attendance_code LIKE '%$search%' OR e.full_name LIKE '%$search%')";
            }

            // Thêm điều kiện nhân viên
            if (!empty($params['employee_id'])) {
                $employee_id = $params['employee_id'];
                $query .= " AND a.employee_id = $employee_id";
            }

            // Thêm điều kiện phòng ban
            if (!empty($params['department_id'])) {
                $department_id = $params['department_id'];
                $query .= " AND e.department_id = $department_id";
            }

            // Thêm điều kiện ngày
            if (!empty($params['date'])) {
                $date = $params['date'];
                $query .= " AND DATE(a.check_in_time) = '$date'";
            }

            // Thêm điều kiện khoảng thời gian
            if (!empty($params['start_date']) && !empty($params['end_date'])) {
                $start_date = $params['start_date'];
                $end_date = $params['end_date'];
                $query .= " AND DATE(a.check_in_time) BETWEEN '$start_date' AND '$end_date'";
            }

            // Thêm điều kiện trạng thái
            if (isset($params['status'])) {
                $status = $params['status'];
                $query .= " AND a.status = '$status'";
            }

            // Thêm phân trang
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            $query .= " ORDER BY a.check_in_time DESC LIMIT $limit OFFSET $offset";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'status' => 'success',
                'data' => $attendance,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $this->getTotalAttendance($params)
                ]
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Lấy thông tin chi tiết chấm công
    public function getAttendanceDetail($id) {
        try {
            $query = "SELECT a.*, e.full_name as employee_name,
                     d.name as department_name, ws.name as work_schedule_name
                     FROM " . $this->table_name . " a
                     LEFT JOIN employees e ON a.employee_id = e.id
                     LEFT JOIN departments d ON e.department_id = d.id
                     LEFT JOIN work_schedules ws ON a.work_schedule_id = ws.id
                     WHERE a.id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $attendance = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($attendance) {
                // Lấy ghi chú
                $notes = $this->getAttendanceNotes($id);
                $attendance['notes'] = $notes;

                return [
                    'status' => 'success',
                    'data' => $attendance
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Attendance record not found'
                ];
            }
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Thêm chấm công mới
    public function createAttendance($data) {
        try {
            // Bắt đầu transaction
            $this->conn->beginTransaction();

            // 1. Thêm chấm công
            $query = "INSERT INTO " . $this->table_name . "
                     (attendance_code, employee_id, work_schedule_id,
                      check_in_time, check_out_time, check_in_image,
                      check_out_image, location, device_info, status)
                     VALUES
                     (:attendance_code, :employee_id, :work_schedule_id,
                      :check_in_time, :check_out_time, :check_in_image,
                      :check_out_image, :location, :device_info, :status)";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'attendance_code' => $data['attendance_code'],
                'employee_id' => $data['employee_id'],
                'work_schedule_id' => $data['work_schedule_id'],
                'check_in_time' => $data['check_in_time'],
                'check_out_time' => $data['check_out_time'] ?? null,
                'check_in_image' => $data['check_in_image'] ?? null,
                'check_out_image' => $data['check_out_image'] ?? null,
                'location' => $data['location'] ?? null,
                'device_info' => $data['device_info'] ?? null,
                'status' => $data['status'] ?? 'pending'
            ]);

            $attendance_id = $this->conn->lastInsertId();

            // 2. Thêm ghi chú nếu có
            if (!empty($data['notes'])) {
                $this->addAttendanceNotes($attendance_id, $data['notes']);
            }

            // Commit transaction
            $this->conn->commit();

            return [
                'status' => 'success',
                'message' => 'Attendance record created successfully',
                'attendance_id' => $attendance_id
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

    // Cập nhật thông tin chấm công
    public function updateAttendance($id, $data) {
        try {
            $query = "UPDATE " . $this->table_name . "
                     SET work_schedule_id = :work_schedule_id,
                         check_in_time = :check_in_time,
                         check_out_time = :check_out_time,
                         check_in_image = :check_in_image,
                         check_out_image = :check_out_image,
                         location = :location,
                         device_info = :device_info,
                         status = :status,
                         updated_at = CURRENT_TIMESTAMP
                     WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'id' => $id,
                'work_schedule_id' => $data['work_schedule_id'],
                'check_in_time' => $data['check_in_time'],
                'check_out_time' => $data['check_out_time'] ?? null,
                'check_in_image' => $data['check_in_image'] ?? null,
                'check_out_image' => $data['check_out_image'] ?? null,
                'location' => $data['location'] ?? null,
                'device_info' => $data['device_info'] ?? null,
                'status' => $data['status'] ?? 'pending'
            ]);

            return [
                'status' => 'success',
                'message' => 'Attendance record updated successfully'
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Xóa chấm công
    public function deleteAttendance($id) {
        try {
            // Bắt đầu transaction
            $this->conn->beginTransaction();

            // 1. Xóa ghi chú
            $query = "DELETE FROM attendance_notes WHERE attendance_id = :attendance_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':attendance_id', $id);
            $stmt->execute();

            // 2. Xóa chấm công
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            // Commit transaction
            $this->conn->commit();

            return [
                'status' => 'success',
                'message' => 'Attendance record deleted successfully'
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
    private function getAttendanceNotes($attendance_id) {
        $query = "SELECT * FROM attendance_notes WHERE attendance_id = :attendance_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':attendance_id', $attendance_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function addAttendanceNotes($attendance_id, $notes) {
        $query = "INSERT INTO attendance_notes
                 (attendance_id, note, created_by)
                 VALUES
                 (:attendance_id, :note, :created_by)";

        $stmt = $this->conn->prepare($query);
        foreach ($notes as $note) {
            $stmt->execute([
                'attendance_id' => $attendance_id,
                'note' => $note['note'],
                'created_by' => $note['created_by']
            ]);
        }
    }

    private function getTotalAttendance($params) {
        $query = "SELECT COUNT(*) as total 
                 FROM " . $this->table_name . " a
                 LEFT JOIN employees e ON a.employee_id = e.id
                 WHERE 1=1";
        
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query .= " AND (a.attendance_code LIKE '%$search%' OR EXISTS (
                SELECT 1 FROM employees e 
                WHERE e.id = a.employee_id 
                AND e.full_name LIKE '%$search%'
            ))";
        }

        if (!empty($params['employee_id'])) {
            $employee_id = $params['employee_id'];
            $query .= " AND a.employee_id = $employee_id";
        }

        if (!empty($params['department_id'])) {
            $department_id = $params['department_id'];
            $query .= " AND e.department_id = $department_id";
        }

        if (!empty($params['date'])) {
            $date = $params['date'];
            $query .= " AND DATE(a.check_in_time) = '$date'";
        }

        if (!empty($params['start_date']) && !empty($params['end_date'])) {
            $start_date = $params['start_date'];
            $end_date = $params['end_date'];
            $query .= " AND DATE(a.check_in_time) BETWEEN '$start_date' AND '$end_date'";
        }

        if (isset($params['status'])) {
            $status = $params['status'];
            $query .= " AND a.status = '$status'";
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
$api = new AttendanceAPI($db);

$method = $_SERVER['REQUEST_METHOD'];
$response = [];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            $response = $api->getAttendanceDetail($_GET['id']);
        } else {
            $response = $api->getAttendance($_GET);
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $response = $api->createAttendance($data);
        break;
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $_GET['id'];
        $response = $api->updateAttendance($id, $data);
        break;
    case 'DELETE':
        $id = $_GET['id'];
        $response = $api->deleteAttendance($id);
        break;
    default:
        $response = [
            'status' => 'error',
            'message' => 'Method not allowed'
        ];
        break;
}

echo json_encode($response); 