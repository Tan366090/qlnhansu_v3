<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

class ReportsAPI {
    private $conn;
    private $table_name = "reports";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy danh sách báo cáo
    public function getReports($params = []) {
        try {
            $query = "SELECT r.*, e.full_name as created_by_name,
                     d.name as department_name, rt.name as report_type_name
                     FROM " . $this->table_name . " r
                     LEFT JOIN employees e ON r.created_by = e.id
                     LEFT JOIN departments d ON r.department_id = d.id
                     LEFT JOIN report_types rt ON r.report_type_id = rt.id
                     WHERE 1=1";
            
            // Thêm điều kiện tìm kiếm
            if (!empty($params['search'])) {
                $search = $params['search'];
                $query .= " AND (r.report_code LIKE '%$search%' OR r.title LIKE '%$search%')";
            }

            // Thêm điều kiện phòng ban
            if (!empty($params['department_id'])) {
                $department_id = $params['department_id'];
                $query .= " AND r.department_id = $department_id";
            }

            // Thêm điều kiện loại báo cáo
            if (!empty($params['report_type_id'])) {
                $report_type_id = $params['report_type_id'];
                $query .= " AND r.report_type_id = $report_type_id";
            }

            // Thêm điều kiện người tạo
            if (!empty($params['created_by'])) {
                $created_by = $params['created_by'];
                $query .= " AND r.created_by = $created_by";
            }

            // Thêm điều kiện trạng thái
            if (isset($params['status'])) {
                $status = $params['status'];
                $query .= " AND r.status = '$status'";
            }

            // Thêm điều kiện khoảng thời gian
            if (!empty($params['created_at_from']) && !empty($params['created_at_to'])) {
                $created_at_from = $params['created_at_from'];
                $created_at_to = $params['created_at_to'];
                $query .= " AND r.created_at BETWEEN '$created_at_from' AND '$created_at_to'";
            }

            // Thêm phân trang
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            $query .= " ORDER BY r.created_at DESC LIMIT $limit OFFSET $offset";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'status' => 'success',
                'data' => $reports,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $this->getTotalReports($params)
                ]
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Lấy thông tin chi tiết báo cáo
    public function getReportDetail($id) {
        try {
            $query = "SELECT r.*, e.full_name as created_by_name,
                     d.name as department_name, rt.name as report_type_name
                     FROM " . $this->table_name . " r
                     LEFT JOIN employees e ON r.created_by = e.id
                     LEFT JOIN departments d ON r.department_id = d.id
                     LEFT JOIN report_types rt ON r.report_type_id = rt.id
                     WHERE r.id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $report = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($report) {
                // Lấy danh sách tham số
                $parameters = $this->getReportParameters($id);
                $report['parameters'] = $parameters;

                // Lấy danh sách lịch chạy
                $schedules = $this->getReportSchedules($id);
                $report['schedules'] = $schedules;

                return [
                    'status' => 'success',
                    'data' => $report
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Report not found'
                ];
            }
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Tạo báo cáo mới
    public function createReport($data) {
        try {
            // Bắt đầu transaction
            $this->conn->beginTransaction();

            // 1. Tạo báo cáo
            $query = "INSERT INTO " . $this->table_name . "
                     (report_code, title, report_type_id, department_id,
                      created_by, status, description, query)
                     VALUES
                     (:report_code, :title, :report_type_id, :department_id,
                      :created_by, :status, :description, :query)";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'report_code' => $data['report_code'],
                'title' => $data['title'],
                'report_type_id' => $data['report_type_id'],
                'department_id' => $data['department_id'],
                'created_by' => $data['created_by'],
                'status' => $data['status'] ?? 'draft',
                'description' => $data['description'],
                'query' => $data['query']
            ]);

            $report_id = $this->conn->lastInsertId();

            // 2. Thêm tham số nếu có
            if (!empty($data['parameters'])) {
                $this->addReportParameters($report_id, $data['parameters']);
            }

            // 3. Thêm lịch chạy nếu có
            if (!empty($data['schedules'])) {
                $this->addReportSchedules($report_id, $data['schedules']);
            }

            // Commit transaction
            $this->conn->commit();

            return [
                'status' => 'success',
                'message' => 'Report created successfully',
                'report_id' => $report_id
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

    // Cập nhật báo cáo
    public function updateReport($id, $data) {
        try {
            $query = "UPDATE " . $this->table_name . "
                     SET title = :title,
                         report_type_id = :report_type_id,
                         department_id = :department_id,
                         status = :status,
                         description = :description,
                         query = :query,
                         updated_at = CURRENT_TIMESTAMP
                     WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'id' => $id,
                'title' => $data['title'],
                'report_type_id' => $data['report_type_id'],
                'department_id' => $data['department_id'],
                'status' => $data['status'],
                'description' => $data['description'],
                'query' => $data['query']
            ]);

            return [
                'status' => 'success',
                'message' => 'Report updated successfully'
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Xóa báo cáo
    public function deleteReport($id) {
        try {
            // Bắt đầu transaction
            $this->conn->beginTransaction();

            // 1. Xóa tham số
            $query = "DELETE FROM report_parameters WHERE report_id = :report_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':report_id', $id);
            $stmt->execute();

            // 2. Xóa lịch chạy
            $query = "DELETE FROM report_schedules WHERE report_id = :report_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':report_id', $id);
            $stmt->execute();

            // 3. Xóa báo cáo
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            // Commit transaction
            $this->conn->commit();

            return [
                'status' => 'success',
                'message' => 'Report deleted successfully'
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
    private function getReportParameters($report_id) {
        $query = "SELECT p.*, e.full_name as created_by_name
                 FROM report_parameters p
                 LEFT JOIN employees e ON p.created_by = e.id
                 WHERE p.report_id = :report_id
                 ORDER BY p.parameter_order ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':report_id', $report_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getReportSchedules($report_id) {
        $query = "SELECT s.*, e.full_name as created_by_name
                 FROM report_schedules s
                 LEFT JOIN employees e ON s.created_by = e.id
                 WHERE s.report_id = :report_id
                 ORDER BY s.next_run_at ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':report_id', $report_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function addReportParameters($report_id, $parameters) {
        $query = "INSERT INTO report_parameters
                 (report_id, name, label, data_type, default_value,
                  is_required, parameter_order, created_by)
                 VALUES
                 (:report_id, :name, :label, :data_type, :default_value,
                  :is_required, :parameter_order, :created_by)";

        $stmt = $this->conn->prepare($query);
        foreach ($parameters as $parameter) {
            $stmt->execute([
                'report_id' => $report_id,
                'name' => $parameter['name'],
                'label' => $parameter['label'],
                'data_type' => $parameter['data_type'],
                'default_value' => $parameter['default_value'] ?? null,
                'is_required' => $parameter['is_required'] ?? true,
                'parameter_order' => $parameter['parameter_order'] ?? 1,
                'created_by' => $parameter['created_by']
            ]);
        }
    }

    private function addReportSchedules($report_id, $schedules) {
        $query = "INSERT INTO report_schedules
                 (report_id, schedule_type, schedule_value,
                  next_run_at, created_by)
                 VALUES
                 (:report_id, :schedule_type, :schedule_value,
                  :next_run_at, :created_by)";

        $stmt = $this->conn->prepare($query);
        foreach ($schedules as $schedule) {
            $stmt->execute([
                'report_id' => $report_id,
                'schedule_type' => $schedule['schedule_type'],
                'schedule_value' => $schedule['schedule_value'],
                'next_run_at' => $schedule['next_run_at'],
                'created_by' => $schedule['created_by']
            ]);
        }
    }

    private function getTotalReports($params) {
        $query = "SELECT COUNT(*) as total 
                 FROM " . $this->table_name . " r
                 WHERE 1=1";
        
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query .= " AND (r.report_code LIKE '%$search%' OR r.title LIKE '%$search%')";
        }

        if (!empty($params['department_id'])) {
            $department_id = $params['department_id'];
            $query .= " AND r.department_id = $department_id";
        }

        if (!empty($params['report_type_id'])) {
            $report_type_id = $params['report_type_id'];
            $query .= " AND r.report_type_id = $report_type_id";
        }

        if (!empty($params['created_by'])) {
            $created_by = $params['created_by'];
            $query .= " AND r.created_by = $created_by";
        }

        if (isset($params['status'])) {
            $status = $params['status'];
            $query .= " AND r.status = '$status'";
        }

        if (!empty($params['created_at_from']) && !empty($params['created_at_to'])) {
            $created_at_from = $params['created_at_from'];
            $created_at_to = $params['created_at_to'];
            $query .= " AND r.created_at BETWEEN '$created_at_from' AND '$created_at_to'";
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
$api = new ReportsAPI($db);

$method = $_SERVER['REQUEST_METHOD'];
$response = [];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            $response = $api->getReportDetail($_GET['id']);
        } else {
            $response = $api->getReports($_GET);
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $response = $api->createReport($data);
        break;
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $_GET['id'];
        $response = $api->updateReport($id, $data);
        break;
    case 'DELETE':
        $id = $_GET['id'];
        $response = $api->deleteReport($id);
        break;
    default:
        $response = [
            'status' => 'error',
            'message' => 'Method not allowed'
        ];
        break;
}

echo json_encode($response); 