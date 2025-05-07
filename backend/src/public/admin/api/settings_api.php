<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

class SettingsAPI {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy cấu hình hệ thống
    public function getSystemConfigs($params = []) {
        try {
            $query = "SELECT * FROM system_configs WHERE 1=1";
            
            // Thêm điều kiện tìm kiếm
            if (!empty($params['search'])) {
                $search = $params['search'];
                $query .= " AND (config_key LIKE '%$search%' OR config_value LIKE '%$search%')";
            }

            // Thêm điều kiện loại cấu hình
            if (!empty($params['config_type'])) {
                $config_type = $params['config_type'];
                $query .= " AND config_type = '$config_type'";
            }

            // Thêm phân trang
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            $query .= " LIMIT $limit OFFSET $offset";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $configs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'status' => 'success',
                'data' => $configs,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $this->getTotalSystemConfigs($params)
                ]
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Lấy cấu hình bảo mật
    public function getSecuritySettings($params = []) {
        try {
            $query = "SELECT * FROM security_settings WHERE 1=1";
            
            // Thêm điều kiện tìm kiếm
            if (!empty($params['search'])) {
                $search = $params['search'];
                $query .= " AND (setting_key LIKE '%$search%' OR setting_value LIKE '%$search%')";
            }

            // Thêm điều kiện loại cấu hình
            if (!empty($params['setting_type'])) {
                $setting_type = $params['setting_type'];
                $query .= " AND setting_type = '$setting_type'";
            }

            // Thêm phân trang
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            $query .= " LIMIT $limit OFFSET $offset";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'status' => 'success',
                'data' => $settings,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $this->getTotalSecuritySettings($params)
                ]
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Lấy chính sách
    public function getPolicies($params = []) {
        try {
            $query = "SELECT p.*, u.username as created_by_name 
                     FROM policies p
                     LEFT JOIN users u ON p.created_by = u.user_id
                     WHERE 1=1";
            
            // Thêm điều kiện tìm kiếm
            if (!empty($params['search'])) {
                $search = $params['search'];
                $query .= " AND (p.policy_name LIKE '%$search%' OR p.policy_description LIKE '%$search%')";
            }

            // Thêm điều kiện loại chính sách
            if (!empty($params['policy_type'])) {
                $policy_type = $params['policy_type'];
                $query .= " AND p.policy_type = '$policy_type'";
            }

            // Thêm điều kiện trạng thái
            if (!empty($params['status'])) {
                $status = $params['status'];
                $query .= " AND p.status = '$status'";
            }

            // Thêm phân trang
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            $query .= " LIMIT $limit OFFSET $offset";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $policies = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'status' => 'success',
                'data' => $policies,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $this->getTotalPolicies($params)
                ]
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Lấy tích hợp
    public function getIntegrations($params = []) {
        try {
            $query = "SELECT i.*, u.username as created_by_name 
                     FROM integrations i
                     LEFT JOIN users u ON i.created_by = u.user_id
                     WHERE 1=1";
            
            // Thêm điều kiện tìm kiếm
            if (!empty($params['search'])) {
                $search = $params['search'];
                $query .= " AND (i.integration_name LIKE '%$search%' OR i.integration_description LIKE '%$search%')";
            }

            // Thêm điều kiện loại tích hợp
            if (!empty($params['integration_type'])) {
                $integration_type = $params['integration_type'];
                $query .= " AND i.integration_type = '$integration_type'";
            }

            // Thêm điều kiện trạng thái
            if (!empty($params['status'])) {
                $status = $params['status'];
                $query .= " AND i.status = '$status'";
            }

            // Thêm phân trang
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            $query .= " LIMIT $limit OFFSET $offset";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $integrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'status' => 'success',
                'data' => $integrations,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $this->getTotalIntegrations($params)
                ]
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Lấy tổng số cấu hình hệ thống
    private function getTotalSystemConfigs($params) {
        $query = "SELECT COUNT(*) as total FROM system_configs WHERE 1=1";
        
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query .= " AND (config_key LIKE '%$search%' OR config_value LIKE '%$search%')";
        }

        if (!empty($params['config_type'])) {
            $config_type = $params['config_type'];
            $query .= " AND config_type = '$config_type'";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    // Lấy tổng số cấu hình bảo mật
    private function getTotalSecuritySettings($params) {
        $query = "SELECT COUNT(*) as total FROM security_settings WHERE 1=1";
        
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query .= " AND (setting_key LIKE '%$search%' OR setting_value LIKE '%$search%')";
        }

        if (!empty($params['setting_type'])) {
            $setting_type = $params['setting_type'];
            $query .= " AND setting_type = '$setting_type'";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    // Lấy tổng số chính sách
    private function getTotalPolicies($params) {
        $query = "SELECT COUNT(*) as total FROM policies WHERE 1=1";
        
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query .= " AND (policy_name LIKE '%$search%' OR policy_description LIKE '%$search%')";
        }

        if (!empty($params['policy_type'])) {
            $policy_type = $params['policy_type'];
            $query .= " AND policy_type = '$policy_type'";
        }

        if (!empty($params['status'])) {
            $status = $params['status'];
            $query .= " AND status = '$status'";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    // Lấy tổng số tích hợp
    private function getTotalIntegrations($params) {
        $query = "SELECT COUNT(*) as total FROM integrations WHERE 1=1";
        
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query .= " AND (integration_name LIKE '%$search%' OR integration_description LIKE '%$search%')";
        }

        if (!empty($params['integration_type'])) {
            $integration_type = $params['integration_type'];
            $query .= " AND integration_type = '$integration_type'";
        }

        if (!empty($params['status'])) {
            $status = $params['status'];
            $query .= " AND status = '$status'";
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
$api = new SettingsAPI($db);

$method = $_SERVER['REQUEST_METHOD'];
$response = [];

switch($method) {
    case 'GET':
        if(isset($_GET['system-configs'])) {
            $response = $api->getSystemConfigs($_GET);
        } else if(isset($_GET['security-settings'])) {
            $response = $api->getSecuritySettings($_GET);
        } else if(isset($_GET['policies'])) {
            $response = $api->getPolicies($_GET);
        } else if(isset($_GET['integrations'])) {
            $response = $api->getIntegrations($_GET);
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