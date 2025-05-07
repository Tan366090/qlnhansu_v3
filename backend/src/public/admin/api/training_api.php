<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

class TrainingAPI {
    private $conn;
    private $table_name = "trainings";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy danh sách khóa đào tạo
    public function getTrainings($params = []) {
        try {
            $query = "SELECT t.*, d.name as department_name,
                     e.full_name as trainer_name, c.name as course_name
                     FROM " . $this->table_name . " t
                     LEFT JOIN departments d ON t.department_id = d.id
                     LEFT JOIN employees e ON t.trainer_id = e.id
                     LEFT JOIN courses c ON t.course_id = c.id
                     WHERE 1=1";
            
            // Thêm điều kiện tìm kiếm
            if (!empty($params['search'])) {
                $search = $params['search'];
                $query .= " AND (t.training_code LIKE '%$search%' OR t.title LIKE '%$search%')";
            }

            // Thêm điều kiện phòng ban
            if (!empty($params['department_id'])) {
                $department_id = $params['department_id'];
                $query .= " AND t.department_id = $department_id";
            }

            // Thêm điều kiện khóa học
            if (!empty($params['course_id'])) {
                $course_id = $params['course_id'];
                $query .= " AND t.course_id = $course_id";
            }

            // Thêm điều kiện giảng viên
            if (!empty($params['trainer_id'])) {
                $trainer_id = $params['trainer_id'];
                $query .= " AND t.trainer_id = $trainer_id";
            }

            // Thêm điều kiện trạng thái
            if (isset($params['status'])) {
                $status = $params['status'];
                $query .= " AND t.status = '$status'";
            }

            // Thêm điều kiện khoảng thời gian
            if (!empty($params['start_date']) && !empty($params['end_date'])) {
                $start_date = $params['start_date'];
                $end_date = $params['end_date'];
                $query .= " AND ((t.start_date BETWEEN '$start_date' AND '$end_date')
                          OR (t.end_date BETWEEN '$start_date' AND '$end_date'))";
            }

            // Thêm phân trang
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            $query .= " ORDER BY t.start_date DESC LIMIT $limit OFFSET $offset";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $trainings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'status' => 'success',
                'data' => $trainings,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $this->getTotalTrainings($params)
                ]
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Lấy thông tin chi tiết khóa đào tạo
    public function getTrainingDetail($id) {
        try {
            $query = "SELECT t.*, d.name as department_name,
                     e.full_name as trainer_name, c.name as course_name
                     FROM " . $this->table_name . " t
                     LEFT JOIN departments d ON t.department_id = d.id
                     LEFT JOIN employees e ON t.trainer_id = e.id
                     LEFT JOIN courses c ON t.course_id = c.id
                     WHERE t.id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $training = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($training) {
                // Lấy danh sách học viên
                $participants = $this->getTrainingParticipants($id);
                $training['participants'] = $participants;

                // Lấy danh sách tài liệu
                $materials = $this->getTrainingMaterials($id);
                $training['materials'] = $materials;

                return [
                    'status' => 'success',
                    'data' => $training
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Training not found'
                ];
            }
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Tạo khóa đào tạo mới
    public function createTraining($data) {
        try {
            // Bắt đầu transaction
            $this->conn->beginTransaction();

            // 1. Tạo khóa đào tạo
            $query = "INSERT INTO " . $this->table_name . "
                     (training_code, title, course_id, department_id,
                      trainer_id, start_date, end_date, location,
                      description, status)
                     VALUES
                     (:training_code, :title, :course_id, :department_id,
                      :trainer_id, :start_date, :end_date, :location,
                      :description, :status)";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'training_code' => $data['training_code'],
                'title' => $data['title'],
                'course_id' => $data['course_id'],
                'department_id' => $data['department_id'],
                'trainer_id' => $data['trainer_id'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'location' => $data['location'],
                'description' => $data['description'],
                'status' => $data['status'] ?? 'pending'
            ]);

            $training_id = $this->conn->lastInsertId();

            // 2. Thêm học viên nếu có
            if (!empty($data['participants'])) {
                $this->addTrainingParticipants($training_id, $data['participants']);
            }

            // 3. Thêm tài liệu nếu có
            if (!empty($data['materials'])) {
                $this->addTrainingMaterials($training_id, $data['materials']);
            }

            // Commit transaction
            $this->conn->commit();

            return [
                'status' => 'success',
                'message' => 'Training created successfully',
                'training_id' => $training_id
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

    // Cập nhật khóa đào tạo
    public function updateTraining($id, $data) {
        try {
            $query = "UPDATE " . $this->table_name . "
                     SET title = :title,
                         course_id = :course_id,
                         department_id = :department_id,
                         trainer_id = :trainer_id,
                         start_date = :start_date,
                         end_date = :end_date,
                         location = :location,
                         description = :description,
                         status = :status,
                         updated_at = CURRENT_TIMESTAMP
                     WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'id' => $id,
                'title' => $data['title'],
                'course_id' => $data['course_id'],
                'department_id' => $data['department_id'],
                'trainer_id' => $data['trainer_id'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'location' => $data['location'],
                'description' => $data['description'],
                'status' => $data['status']
            ]);

            return [
                'status' => 'success',
                'message' => 'Training updated successfully'
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Xóa khóa đào tạo
    public function deleteTraining($id) {
        try {
            // Bắt đầu transaction
            $this->conn->beginTransaction();

            // 1. Xóa học viên
            $query = "DELETE FROM training_participants WHERE training_id = :training_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':training_id', $id);
            $stmt->execute();

            // 2. Xóa tài liệu
            $query = "DELETE FROM training_materials WHERE training_id = :training_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':training_id', $id);
            $stmt->execute();

            // 3. Xóa khóa đào tạo
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            // Commit transaction
            $this->conn->commit();

            return [
                'status' => 'success',
                'message' => 'Training deleted successfully'
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
    private function getTrainingParticipants($training_id) {
        $query = "SELECT tp.*, e.full_name as employee_name,
                 d.name as department_name
                 FROM training_participants tp
                 LEFT JOIN employees e ON tp.employee_id = e.id
                 LEFT JOIN departments d ON e.department_id = d.id
                 WHERE tp.training_id = :training_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':training_id', $training_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getTrainingMaterials($training_id) {
        $query = "SELECT * FROM training_materials WHERE training_id = :training_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':training_id', $training_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function addTrainingParticipants($training_id, $participants) {
        $query = "INSERT INTO training_participants
                 (training_id, employee_id, status, score)
                 VALUES
                 (:training_id, :employee_id, :status, :score)";

        $stmt = $this->conn->prepare($query);
        foreach ($participants as $participant) {
            $stmt->execute([
                'training_id' => $training_id,
                'employee_id' => $participant['employee_id'],
                'status' => $participant['status'] ?? 'registered',
                'score' => $participant['score'] ?? null
            ]);
        }
    }

    private function addTrainingMaterials($training_id, $materials) {
        $query = "INSERT INTO training_materials
                 (training_id, title, description, file_path, file_type, file_size)
                 VALUES
                 (:training_id, :title, :description, :file_path, :file_type, :file_size)";

        $stmt = $this->conn->prepare($query);
        foreach ($materials as $material) {
            $stmt->execute([
                'training_id' => $training_id,
                'title' => $material['title'],
                'description' => $material['description'],
                'file_path' => $material['file_path'],
                'file_type' => $material['file_type'],
                'file_size' => $material['file_size']
            ]);
        }
    }

    private function getTotalTrainings($params) {
        $query = "SELECT COUNT(*) as total 
                 FROM " . $this->table_name . " t
                 WHERE 1=1";
        
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query .= " AND (t.training_code LIKE '%$search%' OR t.title LIKE '%$search%')";
        }

        if (!empty($params['department_id'])) {
            $department_id = $params['department_id'];
            $query .= " AND t.department_id = $department_id";
        }

        if (!empty($params['course_id'])) {
            $course_id = $params['course_id'];
            $query .= " AND t.course_id = $course_id";
        }

        if (!empty($params['trainer_id'])) {
            $trainer_id = $params['trainer_id'];
            $query .= " AND t.trainer_id = $trainer_id";
        }

        if (isset($params['status'])) {
            $status = $params['status'];
            $query .= " AND t.status = '$status'";
        }

        if (!empty($params['start_date']) && !empty($params['end_date'])) {
            $start_date = $params['start_date'];
            $end_date = $params['end_date'];
            $query .= " AND ((t.start_date BETWEEN '$start_date' AND '$end_date')
                          OR (t.end_date BETWEEN '$start_date' AND '$end_date'))";
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
$api = new TrainingAPI($db);

$method = $_SERVER['REQUEST_METHOD'];
$response = [];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            $response = $api->getTrainingDetail($_GET['id']);
        } else {
            $response = $api->getTrainings($_GET);
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $response = $api->createTraining($data);
        break;
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $_GET['id'];
        $response = $api->updateTraining($id, $data);
        break;
    case 'DELETE':
        $id = $_GET['id'];
        $response = $api->deleteTraining($id);
        break;
    default:
        $response = [
            'status' => 'error',
            'message' => 'Method not allowed'
        ];
        break;
}

echo json_encode($response); 