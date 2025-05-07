<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

class RecruitmentAPI {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy danh sách tuyển dụng
    public function getRecruitment($params = []) {
        try {
            $query = "SELECT r.*, u.username as created_by_name, 
                     d.name as department_name, p.name as position_name
                     FROM recruitment r
                     LEFT JOIN users u ON r.created_by = u.user_id
                     LEFT JOIN departments d ON r.department_id = d.department_id
                     LEFT JOIN positions p ON r.position_id = p.position_id
                     WHERE 1=1";
            
            // Thêm điều kiện tìm kiếm
            if (!empty($params['search'])) {
                $search = $params['search'];
                $query .= " AND (r.title LIKE '%$search%' OR r.description LIKE '%$search%')";
            }

            // Thêm điều kiện phòng ban
            if (!empty($params['department_id'])) {
                $department_id = $params['department_id'];
                $query .= " AND r.department_id = $department_id";
            }

            // Thêm điều kiện vị trí
            if (!empty($params['position_id'])) {
                $position_id = $params['position_id'];
                $query .= " AND r.position_id = $position_id";
            }

            // Thêm điều kiện trạng thái
            if (!empty($params['status'])) {
                $status = $params['status'];
                $query .= " AND r.status = '$status'";
            }

            // Thêm điều kiện ngày bắt đầu
            if (!empty($params['start_date_from'])) {
                $start_date_from = $params['start_date_from'];
                $query .= " AND r.start_date >= '$start_date_from'";
            }
            if (!empty($params['start_date_to'])) {
                $start_date_to = $params['start_date_to'];
                $query .= " AND r.start_date <= '$start_date_to'";
            }

            // Thêm điều kiện ngày kết thúc
            if (!empty($params['end_date_from'])) {
                $end_date_from = $params['end_date_from'];
                $query .= " AND r.end_date >= '$end_date_from'";
            }
            if (!empty($params['end_date_to'])) {
                $end_date_to = $params['end_date_to'];
                $query .= " AND r.end_date <= '$end_date_to'";
            }

            // Thêm phân trang
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            $query .= " LIMIT $limit OFFSET $offset";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $recruitment = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'status' => 'success',
                'data' => $recruitment,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $this->getTotalRecruitment($params)
                ]
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Lấy danh sách ứng viên
    public function getCandidates($params = []) {
        try {
            $query = "SELECT c.*, r.title as recruitment_title,
                     u.username as created_by_name
                     FROM candidates c
                     LEFT JOIN recruitment r ON c.recruitment_id = r.recruitment_id
                     LEFT JOIN users u ON c.created_by = u.user_id
                     WHERE 1=1";
            
            // Thêm điều kiện tìm kiếm
            if (!empty($params['search'])) {
                $search = $params['search'];
                $query .= " AND (c.name LIKE '%$search%' OR c.email LIKE '%$search%' OR c.phone LIKE '%$search%')";
            }

            // Thêm điều kiện tuyển dụng
            if (!empty($params['recruitment_id'])) {
                $recruitment_id = $params['recruitment_id'];
                $query .= " AND c.recruitment_id = $recruitment_id";
            }

            // Thêm điều kiện trạng thái
            if (!empty($params['status'])) {
                $status = $params['status'];
                $query .= " AND c.status = '$status'";
            }

            // Thêm điều kiện ngày nộp đơn
            if (!empty($params['apply_date_from'])) {
                $apply_date_from = $params['apply_date_from'];
                $query .= " AND c.apply_date >= '$apply_date_from'";
            }
            if (!empty($params['apply_date_to'])) {
                $apply_date_to = $params['apply_date_to'];
                $query .= " AND c.apply_date <= '$apply_date_to'";
            }

            // Thêm phân trang
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            $query .= " LIMIT $limit OFFSET $offset";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'status' => 'success',
                'data' => $candidates,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $this->getTotalCandidates($params)
                ]
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Lấy lịch phỏng vấn
    public function getInterviews($params = []) {
        try {
            $query = "SELECT i.*, c.name as candidate_name, c.email as candidate_email,
                     u.username as interviewer_name, r.title as recruitment_title
                     FROM interviews i
                     LEFT JOIN candidates c ON i.candidate_id = c.candidate_id
                     LEFT JOIN users u ON i.interviewer_id = u.user_id
                     LEFT JOIN recruitment r ON i.recruitment_id = r.recruitment_id
                     WHERE 1=1";
            
            // Thêm điều kiện tìm kiếm
            if (!empty($params['search'])) {
                $search = $params['search'];
                $query .= " AND (c.name LIKE '%$search%' OR c.email LIKE '%$search%')";
            }

            // Thêm điều kiện ứng viên
            if (!empty($params['candidate_id'])) {
                $candidate_id = $params['candidate_id'];
                $query .= " AND i.candidate_id = $candidate_id";
            }

            // Thêm điều kiện người phỏng vấn
            if (!empty($params['interviewer_id'])) {
                $interviewer_id = $params['interviewer_id'];
                $query .= " AND i.interviewer_id = $interviewer_id";
            }

            // Thêm điều kiện trạng thái
            if (!empty($params['status'])) {
                $status = $params['status'];
                $query .= " AND i.status = '$status'";
            }

            // Thêm điều kiện ngày phỏng vấn
            if (!empty($params['interview_date_from'])) {
                $interview_date_from = $params['interview_date_from'];
                $query .= " AND i.interview_date >= '$interview_date_from'";
            }
            if (!empty($params['interview_date_to'])) {
                $interview_date_to = $params['interview_date_to'];
                $query .= " AND i.interview_date <= '$interview_date_to'";
            }

            // Thêm phân trang
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            $query .= " LIMIT $limit OFFSET $offset";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $interviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'status' => 'success',
                'data' => $interviews,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $this->getTotalInterviews($params)
                ]
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Lấy thông tin onboarding
    public function getOnboarding($params = []) {
        try {
            $query = "SELECT o.*, c.name as candidate_name, c.email as candidate_email,
                     u.username as assigned_to_name, r.title as recruitment_title
                     FROM onboarding o
                     LEFT JOIN candidates c ON o.candidate_id = c.candidate_id
                     LEFT JOIN users u ON o.assigned_to = u.user_id
                     LEFT JOIN recruitment r ON o.recruitment_id = r.recruitment_id
                     WHERE 1=1";
            
            // Thêm điều kiện tìm kiếm
            if (!empty($params['search'])) {
                $search = $params['search'];
                $query .= " AND (c.name LIKE '%$search%' OR c.email LIKE '%$search%')";
            }

            // Thêm điều kiện ứng viên
            if (!empty($params['candidate_id'])) {
                $candidate_id = $params['candidate_id'];
                $query .= " AND o.candidate_id = $candidate_id";
            }

            // Thêm điều kiện người được giao
            if (!empty($params['assigned_to'])) {
                $assigned_to = $params['assigned_to'];
                $query .= " AND o.assigned_to = $assigned_to";
            }

            // Thêm điều kiện trạng thái
            if (!empty($params['status'])) {
                $status = $params['status'];
                $query .= " AND o.status = '$status'";
            }

            // Thêm điều kiện ngày bắt đầu
            if (!empty($params['start_date_from'])) {
                $start_date_from = $params['start_date_from'];
                $query .= " AND o.start_date >= '$start_date_from'";
            }
            if (!empty($params['start_date_to'])) {
                $start_date_to = $params['start_date_to'];
                $query .= " AND o.start_date <= '$start_date_to'";
            }

            // Thêm điều kiện ngày kết thúc
            if (!empty($params['end_date_from'])) {
                $end_date_from = $params['end_date_from'];
                $query .= " AND o.end_date >= '$end_date_from'";
            }
            if (!empty($params['end_date_to'])) {
                $end_date_to = $params['end_date_to'];
                $query .= " AND o.end_date <= '$end_date_to'";
            }

            // Thêm phân trang
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            $query .= " LIMIT $limit OFFSET $offset";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $onboarding = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'status' => 'success',
                'data' => $onboarding,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $this->getTotalOnboarding($params)
                ]
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Lấy tổng số tuyển dụng
    private function getTotalRecruitment($params) {
        $query = "SELECT COUNT(*) as total FROM recruitment r WHERE 1=1";
        
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query .= " AND (r.title LIKE '%$search%' OR r.description LIKE '%$search%')";
        }

        if (!empty($params['department_id'])) {
            $department_id = $params['department_id'];
            $query .= " AND r.department_id = $department_id";
        }

        if (!empty($params['position_id'])) {
            $position_id = $params['position_id'];
            $query .= " AND r.position_id = $position_id";
        }

        if (!empty($params['status'])) {
            $status = $params['status'];
            $query .= " AND r.status = '$status'";
        }

        if (!empty($params['start_date_from'])) {
            $start_date_from = $params['start_date_from'];
            $query .= " AND r.start_date >= '$start_date_from'";
        }
        if (!empty($params['start_date_to'])) {
            $start_date_to = $params['start_date_to'];
            $query .= " AND r.start_date <= '$start_date_to'";
        }

        if (!empty($params['end_date_from'])) {
            $end_date_from = $params['end_date_from'];
            $query .= " AND r.end_date >= '$end_date_from'";
        }
        if (!empty($params['end_date_to'])) {
            $end_date_to = $params['end_date_to'];
            $query .= " AND r.end_date <= '$end_date_to'";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    // Lấy tổng số ứng viên
    private function getTotalCandidates($params) {
        $query = "SELECT COUNT(*) as total FROM candidates c WHERE 1=1";
        
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query .= " AND (c.name LIKE '%$search%' OR c.email LIKE '%$search%' OR c.phone LIKE '%$search%')";
        }

        if (!empty($params['recruitment_id'])) {
            $recruitment_id = $params['recruitment_id'];
            $query .= " AND c.recruitment_id = $recruitment_id";
        }

        if (!empty($params['status'])) {
            $status = $params['status'];
            $query .= " AND c.status = '$status'";
        }

        if (!empty($params['apply_date_from'])) {
            $apply_date_from = $params['apply_date_from'];
            $query .= " AND c.apply_date >= '$apply_date_from'";
        }
        if (!empty($params['apply_date_to'])) {
            $apply_date_to = $params['apply_date_to'];
            $query .= " AND c.apply_date <= '$apply_date_to'";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    // Lấy tổng số phỏng vấn
    private function getTotalInterviews($params) {
        $query = "SELECT COUNT(*) as total FROM interviews i WHERE 1=1";
        
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query .= " AND EXISTS (SELECT 1 FROM candidates c WHERE i.candidate_id = c.candidate_id 
                      AND (c.name LIKE '%$search%' OR c.email LIKE '%$search%'))";
        }

        if (!empty($params['candidate_id'])) {
            $candidate_id = $params['candidate_id'];
            $query .= " AND i.candidate_id = $candidate_id";
        }

        if (!empty($params['interviewer_id'])) {
            $interviewer_id = $params['interviewer_id'];
            $query .= " AND i.interviewer_id = $interviewer_id";
        }

        if (!empty($params['status'])) {
            $status = $params['status'];
            $query .= " AND i.status = '$status'";
        }

        if (!empty($params['interview_date_from'])) {
            $interview_date_from = $params['interview_date_from'];
            $query .= " AND i.interview_date >= '$interview_date_from'";
        }
        if (!empty($params['interview_date_to'])) {
            $interview_date_to = $params['interview_date_to'];
            $query .= " AND i.interview_date <= '$interview_date_to'";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    // Lấy tổng số onboarding
    private function getTotalOnboarding($params) {
        $query = "SELECT COUNT(*) as total FROM onboarding o WHERE 1=1";
        
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query .= " AND EXISTS (SELECT 1 FROM candidates c WHERE o.candidate_id = c.candidate_id 
                      AND (c.name LIKE '%$search%' OR c.email LIKE '%$search%'))";
        }

        if (!empty($params['candidate_id'])) {
            $candidate_id = $params['candidate_id'];
            $query .= " AND o.candidate_id = $candidate_id";
        }

        if (!empty($params['assigned_to'])) {
            $assigned_to = $params['assigned_to'];
            $query .= " AND o.assigned_to = $assigned_to";
        }

        if (!empty($params['status'])) {
            $status = $params['status'];
            $query .= " AND o.status = '$status'";
        }

        if (!empty($params['start_date_from'])) {
            $start_date_from = $params['start_date_from'];
            $query .= " AND o.start_date >= '$start_date_from'";
        }
        if (!empty($params['start_date_to'])) {
            $start_date_to = $params['start_date_to'];
            $query .= " AND o.start_date <= '$start_date_to'";
        }

        if (!empty($params['end_date_from'])) {
            $end_date_from = $params['end_date_from'];
            $query .= " AND o.end_date >= '$end_date_from'";
        }
        if (!empty($params['end_date_to'])) {
            $end_date_to = $params['end_date_to'];
            $query .= " AND o.end_date <= '$end_date_to'";
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
$api = new RecruitmentAPI($db);

$method = $_SERVER['REQUEST_METHOD'];
$response = [];

switch($method) {
    case 'GET':
        if(isset($_GET['recruitment'])) {
            $response = $api->getRecruitment($_GET);
        } else if(isset($_GET['candidates'])) {
            $response = $api->getCandidates($_GET);
        } else if(isset($_GET['interviews'])) {
            $response = $api->getInterviews($_GET);
        } else if(isset($_GET['onboarding'])) {
            $response = $api->getOnboarding($_GET);
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Invalid endpoint'
            ];
        }
        break;
    default:
        $response = [
            'status' => 'error',
            'message' => 'Method not allowed'
        ];
        break;
}

echo json_encode($response); 