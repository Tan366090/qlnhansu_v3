<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

class JobPositionsAPI {
    private $conn;
    private $table_name = "job_positions";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy danh sách vị trí tuyển dụng
    public function getJobPositions($params = []) {
        try {
            $query = "SELECT jp.*, d.name as department_name,
                     COUNT(a.id) as application_count
                     FROM " . $this->table_name . " jp
                     LEFT JOIN departments d ON jp.department_id = d.id
                     LEFT JOIN applications a ON jp.id = a.job_position_id
                     WHERE 1=1";
            
            // Thêm điều kiện tìms kiếm
            if (!empty($params['search'])) {
                $search = $params['search'];
                $query .= " AND (jp.title LIKE '%$search%' OR jp.description LIKE '%$search%')";
            }

            // Thêm điều kiện phòng ban
            if (!empty($params['department_id'])) {
                $department_id = $params['department_id'];
                $query .= " AND jp.department_id = $department_id";
            }

            // Thêm điều kiện chiến dịch
            if (!empty($params['campaign_id'])) {
                $campaign_id = $params['campaign_id'];
                $query .= " AND jp.campaign_id = $campaign_id";
            }

            // Thêm điều kiện trạng thái
            if (isset($params['is_active'])) {
                $is_active = $params['is_active'] ? 1 : 0;
                $query .= " AND jp.is_active = $is_active";
            }

            // Thêm phân trang
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            $query .= " GROUP BY jp.id ORDER BY jp.created_at DESC LIMIT $limit OFFSET $offset";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $job_positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'status' => 'success',
                'data' => $job_positions,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $this->getTotalJobPositions($params)
                ]
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Lấy thông tin chi tiết vị trí tuyển dụng
    public function getJobPosition($id) {
        try {
            $query = "SELECT jp.*, d.name as department_name,
                     COUNT(a.id) as application_count
                     FROM " . $this->table_name . " jp
                     LEFT JOIN departments d ON jp.department_id = d.id
                     LEFT JOIN applications a ON jp.id = a.job_position_id
                     WHERE jp.id = :id
                     GROUP BY jp.id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $job_position = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($job_position) {
                // Lấy danh sách ứng viên
                $applications = $this->getJobApplications($id);
                $job_position['applications'] = $applications;

                // Lấy thông tin chiến dịch
                if ($job_position['campaign_id']) {
                    $campaign = $this->getCampaign($job_position['campaign_id']);
                    $job_position['campaign'] = $campaign;
                }

                return [
                    'status' => 'success',
                    'data' => $job_position
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Job position not found'
                ];
            }
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Thêm vị trí tuyển dụng mới
    public function createJobPosition($data) {
        try {
            $query = "INSERT INTO " . $this->table_name . "
                     (title, title_override, description, department_id, 
                      campaign_id, salary_range_min, salary_range_max, 
                      requirements, responsibilities, benefits, is_active)
                     VALUES
                     (:title, :title_override, :description, :department_id,
                      :campaign_id, :salary_range_min, :salary_range_max,
                      :requirements, :responsibilities, :benefits, :is_active)";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'title' => $data['title'],
                'title_override' => $data['title_override'] ?? null,
                'description' => $data['description'] ?? null,
                'department_id' => $data['department_id'],
                'campaign_id' => $data['campaign_id'] ?? null,
                'salary_range_min' => $data['salary_range_min'] ?? null,
                'salary_range_max' => $data['salary_range_max'] ?? null,
                'requirements' => $data['requirements'] ?? null,
                'responsibilities' => $data['responsibilities'] ?? null,
                'benefits' => $data['benefits'] ?? null,
                'is_active' => $data['is_active'] ?? 1
            ]);

            $job_position_id = $this->conn->lastInsertId();

            return [
                'status' => 'success',
                'message' => 'Job position created successfully',
                'job_position_id' => $job_position_id
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Cập nhật thông tin vị trí tuyển dụng
    public function updateJobPosition($id, $data) {
        try {
            $query = "UPDATE " . $this->table_name . "
                     SET title = :title,
                         title_override = :title_override,
                         description = :description,
                         department_id = :department_id,
                         campaign_id = :campaign_id,
                         salary_range_min = :salary_range_min,
                         salary_range_max = :salary_range_max,
                         requirements = :requirements,
                         responsibilities = :responsibilities,
                         benefits = :benefits,
                         is_active = :is_active,
                         updated_at = CURRENT_TIMESTAMP
                     WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'id' => $id,
                'title' => $data['title'],
                'title_override' => $data['title_override'] ?? null,
                'description' => $data['description'] ?? null,
                'department_id' => $data['department_id'],
                'campaign_id' => $data['campaign_id'] ?? null,
                'salary_range_min' => $data['salary_range_min'] ?? null,
                'salary_range_max' => $data['salary_range_max'] ?? null,
                'requirements' => $data['requirements'] ?? null,
                'responsibilities' => $data['responsibilities'] ?? null,
                'benefits' => $data['benefits'] ?? null,
                'is_active' => $data['is_active'] ?? 1
            ]);

            return [
                'status' => 'success',
                'message' => 'Job position updated successfully'
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Xóa vị trí tuyển dụng
    public function deleteJobPosition($id) {
        try {
            // Bắt đầu transaction
            $this->conn->beginTransaction();

            // 1. Kiểm tra xem vị trí có ứng viên không
            $query = "SELECT COUNT(*) as count FROM applications WHERE job_position_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                throw new Exception('Cannot delete job position with applications');
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
                'message' => 'Job position deleted successfully'
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
    private function getJobApplications($job_position_id) {
        $query = "SELECT a.*, c.full_name as candidate_name,
                 c.email as candidate_email, c.phone as candidate_phone
                 FROM applications a
                 LEFT JOIN candidates c ON a.candidate_id = c.id
                 WHERE a.job_position_id = :job_position_id
                 ORDER BY a.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':job_position_id', $job_position_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getCampaign($campaign_id) {
        $query = "SELECT * FROM recruitment_campaigns WHERE id = :campaign_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':campaign_id', $campaign_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getTotalJobPositions($params) {
        $query = "SELECT COUNT(DISTINCT jp.id) as total 
                 FROM " . $this->table_name . " jp
                 WHERE 1=1";
        
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query .= " AND (jp.title LIKE '%$search%' OR jp.description LIKE '%$search%')";
        }

        if (!empty($params['department_id'])) {
            $department_id = $params['department_id'];
            $query .= " AND jp.department_id = $department_id";
        }

        if (!empty($params['campaign_id'])) {
            $campaign_id = $params['campaign_id'];
            $query .= " AND jp.campaign_id = $campaign_id";
        }

        if (isset($params['is_active'])) {
            $is_active = $params['is_active'] ? 1 : 0;
            $query .= " AND jp.is_active = $is_active";
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
$api = new JobPositionsAPI($db);

$method = $_SERVER['REQUEST_METHOD'];
$response = [];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            $response = $api->getJobPosition($_GET['id']);
        } else {
            $response = $api->getJobPositions($_GET);
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $response = $api->createJobPosition($data);
        break;
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $_GET['id'];
        $response = $api->updateJobPosition($id, $data);
        break;
    case 'DELETE':
        $id = $_GET['id'];
        $response = $api->deleteJobPosition($id);
        break;
    default:
        $response = [
            'status' => 'error',
            'message' => 'Method not allowed'
        ];
        break;
}

echo json_encode($response); 