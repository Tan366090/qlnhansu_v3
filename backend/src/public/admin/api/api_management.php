<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

class APIManagementAPI {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy danh sách endpoint
    public function getEndpoints($params = []) {
        try {
            $query = "SELECT e.*, r.name as role_name
                     FROM api_endpoints e
                     LEFT JOIN roles r ON e.role_id = r.role_id
                     WHERE 1=1";
            
            // Thêm điều kiện tìm kiếm
            if (!empty($params['search'])) {
                $search = $params['search'];
                $query .= " AND (e.endpoint_name LIKE '%$search%' OR e.description LIKE '%$search%')";
            }

            // Thêm điều kiện vai trò
            if (!empty($params['role_id'])) {
                $role_id = $params['role_id'];
                $query .= " AND e.role_id = $role_id";
            }

            // Thêm điều kiện phương thức
            if (!empty($params['method'])) {
                $method = $params['method'];
                $query .= " AND e.method = '$method'";
            }

            // Thêm điều kiện trạng thái
            if (!empty($params['status'])) {
                $status = $params['status'];
                $query .= " AND e.is_active = '$status'";
            }

            // Thêm phân trang
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            $query .= " ORDER BY e.created_at DESC LIMIT $limit OFFSET $offset";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $endpoints = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'status' => 'success',
                'data' => $endpoints,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $this->getTotalEndpoints($params)
                ]
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Lấy logs
    public function getLogs($params = []) {
        try {
            $query = "SELECT l.*, u.username, u.email
                     FROM (
                         SELECT 'api' as log_type, log_id, user_id, request_data, response_data, status_code, execution_time, created_at
                         FROM api_logs
                         UNION ALL
                         SELECT 'audit' as log_type, log_id, user_id, action_type, details, NULL, NULL, timestamp
                         FROM audit_logs
                         UNION ALL
                         SELECT 'security' as log_type, log_id, user_id, action_type, details, NULL, NULL, timestamp
                         FROM security_logs
                     ) l
                     LEFT JOIN users u ON l.user_id = u.user_id
                     WHERE 1=1";
            
            // Thêm điều kiện tìm kiếm
            if (!empty($params['search'])) {
                $search = $params['search'];
                $query .= " AND (u.username LIKE '%$search%' OR u.email LIKE '%$search%' 
                          OR l.action LIKE '%$search%' OR l.details LIKE '%$search%')";
            }

            // Thêm điều kiện loại log
            if (!empty($params['log_type'])) {
                $log_type = $params['log_type'];
                $query .= " AND l.log_type = '$log_type'";
            }

            // Thêm điều kiện người dùng
            if (!empty($params['user_id'])) {
                $user_id = $params['user_id'];
                $query .= " AND l.user_id = $user_id";
            }

            // Thêm điều kiện thời gian
            if (!empty($params['start_date'])) {
                $start_date = $params['start_date'];
                $query .= " AND l.created_at >= '$start_date'";
            }

            if (!empty($params['end_date'])) {
                $end_date = $params['end_date'];
                $query .= " AND l.created_at <= '$end_date'";
            }

            // Thêm phân trang
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            $query .= " ORDER BY l.created_at DESC LIMIT $limit OFFSET $offset";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'status' => 'success',
                'data' => $logs,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $this->getTotalLogs($params)
                ]
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Lấy giới hạn truy cập
    public function getRateLimits($params = []) {
        try {
            $query = "SELECT r.*, u.username, u.email
                     FROM rate_limits r
                     LEFT JOIN users u ON r.user_id = u.user_id
                     WHERE 1=1";
            
            // Thêm điều kiện tìm kiếm
            if (!empty($params['search'])) {
                $search = $params['search'];
                $query .= " AND (u.username LIKE '%$search%' OR u.email LIKE '%$search%' 
                          OR r.endpoint LIKE '%$search%')";
            }

            // Thêm điều kiện người dùng
            if (!empty($params['user_id'])) {
                $user_id = $params['user_id'];
                $query .= " AND r.user_id = $user_id";
            }

            // Thêm điều kiện endpoint
            if (!empty($params['endpoint'])) {
                $endpoint = $params['endpoint'];
                $query .= " AND r.endpoint = '$endpoint'";
            }

            // Thêm điều kiện trạng thái
            if (!empty($params['status'])) {
                $status = $params['status'];
                $query .= " AND r.status = '$status'";
            }

            // Thêm phân trang
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            $query .= " ORDER BY r.created_at DESC LIMIT $limit OFFSET $offset";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $limits = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'status' => 'success',
                'data' => $limits,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $this->getTotalRateLimits($params)
                ]
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Lấy danh sách backup
    public function getBackups($params = []) {
        try {
            $query = "SELECT b.*, u.username, u.email
                     FROM backups b
                     LEFT JOIN users u ON b.created_by = u.user_id
                     WHERE 1=1";
            
            // Thêm điều kiện tìm kiếm
            if (!empty($params['search'])) {
                $search = $params['search'];
                $query .= " AND (b.filename LIKE '%$search%' OR b.description LIKE '%$search%')";
            }

            // Thêm điều kiện người tạo
            if (!empty($params['created_by'])) {
                $created_by = $params['created_by'];
                $query .= " AND b.created_by = $created_by";
            }

            // Thêm điều kiện loại backup
            if (!empty($params['backup_type'])) {
                $backup_type = $params['backup_type'];
                $query .= " AND b.backup_type = '$backup_type'";
            }

            // Thêm điều kiện trạng thái
            if (!empty($params['status'])) {
                $status = $params['status'];
                $query .= " AND b.status = '$status'";
            }

            // Thêm điều kiện thời gian
            if (!empty($params['start_date'])) {
                $start_date = $params['start_date'];
                $query .= " AND b.created_at >= '$start_date'";
            }

            if (!empty($params['end_date'])) {
                $end_date = $params['end_date'];
                $query .= " AND b.created_at <= '$end_date'";
            }

            // Thêm phân trang
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            $query .= " ORDER BY b.created_at DESC LIMIT $limit OFFSET $offset";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $backups = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'status' => 'success',
                'data' => $backups,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $this->getTotalBackups($params)
                ]
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Lấy tổng số endpoint
    private function getTotalEndpoints($params) {
        $query = "SELECT COUNT(*) as total FROM api_endpoints e WHERE 1=1";
        
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query .= " AND (e.endpoint_name LIKE '%$search%' OR e.description LIKE '%$search%')";
        }

        if (!empty($params['role_id'])) {
            $role_id = $params['role_id'];
            $query .= " AND e.role_id = $role_id";
        }

        if (!empty($params['method'])) {
            $method = $params['method'];
            $query .= " AND e.method = '$method'";
        }

        if (!empty($params['status'])) {
            $status = $params['status'];
            $query .= " AND e.is_active = '$status'";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    // Lấy tổng số logs
    private function getTotalLogs($params) {
        $query = "SELECT COUNT(*) as total FROM (
                    SELECT log_id FROM api_logs
                    UNION ALL
                    SELECT log_id FROM audit_logs
                    UNION ALL
                    SELECT log_id FROM security_logs
                 ) l
                 LEFT JOIN users u ON l.user_id = u.user_id
                 WHERE 1=1";
        
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query .= " AND (u.username LIKE '%$search%' OR u.email LIKE '%$search%')";
        }

        if (!empty($params['log_type'])) {
            $log_type = $params['log_type'];
            $query .= " AND l.log_type = '$log_type'";
        }

        if (!empty($params['user_id'])) {
            $user_id = $params['user_id'];
            $query .= " AND l.user_id = $user_id";
        }

        if (!empty($params['start_date'])) {
            $start_date = $params['start_date'];
            $query .= " AND l.created_at >= '$start_date'";
        }

        if (!empty($params['end_date'])) {
            $end_date = $params['end_date'];
            $query .= " AND l.created_at <= '$end_date'";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    // Lấy tổng số giới hạn truy cập
    private function getTotalRateLimits($params) {
        $query = "SELECT COUNT(*) as total FROM rate_limits r
                 LEFT JOIN users u ON r.user_id = u.user_id
                 WHERE 1=1";
        
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query .= " AND (u.username LIKE '%$search%' OR u.email LIKE '%$search%' 
                      OR r.endpoint LIKE '%$search%')";
        }

        if (!empty($params['user_id'])) {
            $user_id = $params['user_id'];
            $query .= " AND r.user_id = $user_id";
        }

        if (!empty($params['endpoint'])) {
            $endpoint = $params['endpoint'];
            $query .= " AND r.endpoint = '$endpoint'";
        }

        if (!empty($params['status'])) {
            $status = $params['status'];
            $query .= " AND r.status = '$status'";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    // Lấy tổng số backup
    private function getTotalBackups($params) {
        $query = "SELECT COUNT(*) as total FROM backups b
                 LEFT JOIN users u ON b.created_by = u.user_id
                 WHERE 1=1";
        
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query .= " AND (b.filename LIKE '%$search%' OR b.description LIKE '%$search%')";
        }

        if (!empty($params['created_by'])) {
            $created_by = $params['created_by'];
            $query .= " AND b.created_by = $created_by";
        }

        if (!empty($params['backup_type'])) {
            $backup_type = $params['backup_type'];
            $query .= " AND b.backup_type = '$backup_type'";
        }

        if (!empty($params['status'])) {
            $status = $params['status'];
            $query .= " AND b.status = '$status'";
        }

        if (!empty($params['start_date'])) {
            $start_date = $params['start_date'];
            $query .= " AND b.created_at >= '$start_date'";
        }

        if (!empty($params['end_date'])) {
            $end_date = $params['end_date'];
            $query .= " AND b.created_at <= '$end_date'";
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
$api = new APIManagementAPI($db);

$method = $_SERVER['REQUEST_METHOD'];
$response = [];

switch($method) {
    case 'GET':
        if(isset($_GET['endpoints'])) {
            $response = $api->getEndpoints($_GET);
        } else if(isset($_GET['logs'])) {
            $response = $api->getLogs($_GET);
        } else if(isset($_GET['rate-limits'])) {
            $response = $api->getRateLimits($_GET);
        } else if(isset($_GET['backups'])) {
            $response = $api->getBackups($_GET);
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