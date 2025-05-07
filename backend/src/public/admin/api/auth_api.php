<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

class AuthAPI {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Đăng nhập
    public function login($data) {
        try {
            // Kiểm tra thông tin đăng nhập
            $query = "SELECT u.*, r.name as role_name, d.name as department_name
                     FROM users u
                     LEFT JOIN roles r ON u.role_id = r.role_id
                     LEFT JOIN departments d ON u.department_id = d.department_id
                     WHERE u.username = :username AND u.password = :password";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $data['username']);
            $stmt->bindParam(':password', md5($data['password'])); // Mã hóa mật khẩu
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Tạo phiên đăng nhập mới
                $session_id = uniqid();
                $query = "INSERT INTO sessions (session_id, user_id, ip_address, user_agent, created_at, expires_at)
                         VALUES (:session_id, :user_id, :ip_address, :user_agent, NOW(), DATE_ADD(NOW(), INTERVAL 24 HOUR))";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':session_id', $session_id);
                $stmt->bindParam(':user_id', $user['user_id']);
                $stmt->bindParam(':ip_address', $_SERVER['REMOTE_ADDR']);
                $stmt->bindParam(':user_agent', $_SERVER['HTTP_USER_AGENT']);
                $stmt->execute();
                
                return [
                    'status' => 'success',
                    'message' => 'Login successful',
                    'data' => [
                        'user' => $user,
                        'session_id' => $session_id
                    ]
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Invalid username or password'
                ];
            }
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Đăng xuất
    public function logout($session_id) {
        try {
            $query = "UPDATE sessions SET status = 'inactive', updated_at = NOW()
                     WHERE session_id = :session_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':session_id', $session_id);
            $stmt->execute();
            
            return [
                'status' => 'success',
                'message' => 'Logout successful'
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Quên mật khẩu
    public function forgotPassword($email) {
        try {
            // Kiểm tra email tồn tại
            $query = "SELECT * FROM users WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Tạo mã reset password
                $reset_token = bin2hex(random_bytes(32));
                $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Lưu mã reset vào database
                $query = "INSERT INTO password_resets (user_id, reset_token, expires_at)
                         VALUES (:user_id, :reset_token, :expires_at)";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':user_id', $user['user_id']);
                $stmt->bindParam(':reset_token', $reset_token);
                $stmt->bindParam(':expires_at', $expires_at);
                $stmt->execute();
                
                // TODO: Gửi email chứa link reset password
                
                return [
                    'status' => 'success',
                    'message' => 'Reset password link has been sent to your email'
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Email not found'
                ];
            }
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Đặt lại mật khẩu
    public function resetPassword($data) {
        try {
            // Kiểm tra mã reset hợp lệ
            $query = "SELECT * FROM password_resets 
                     WHERE reset_token = :reset_token 
                     AND expires_at > NOW() 
                     AND used = 0";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':reset_token', $data['reset_token']);
            $stmt->execute();
            
            $reset = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($reset) {
                // Cập nhật mật khẩu mới
                $query = "UPDATE users SET password = :password 
                         WHERE user_id = :user_id";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':password', md5($data['password']));
                $stmt->bindParam(':user_id', $reset['user_id']);
                $stmt->execute();
                
                // Đánh dấu mã reset đã sử dụng
                $query = "UPDATE password_resets SET used = 1 
                         WHERE reset_token = :reset_token";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':reset_token', $data['reset_token']);
                $stmt->execute();
                
                return [
                    'status' => 'success',
                    'message' => 'Password has been reset successfully'
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Invalid or expired reset token'
                ];
            }
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Lấy danh sách phiên
    public function getSessions($params = []) {
        try {
            $query = "SELECT s.*, u.username, u.email
                     FROM sessions s
                     LEFT JOIN users u ON s.user_id = u.user_id
                     WHERE 1=1";
            
            // Thêm điều kiện tìm kiếm
            if (!empty($params['search'])) {
                $search = $params['search'];
                $query .= " AND (u.username LIKE '%$search%' OR u.email LIKE '%$search%')";
            }

            // Thêm điều kiện trạng thái
            if (!empty($params['status'])) {
                $status = $params['status'];
                $query .= " AND s.status = '$status'";
            }

            // Thêm điều kiện thời gian
            if (!empty($params['start_date'])) {
                $start_date = $params['start_date'];
                $query .= " AND s.created_at >= '$start_date'";
            }

            if (!empty($params['end_date'])) {
                $end_date = $params['end_date'];
                $query .= " AND s.created_at <= '$end_date'";
            }

            // Thêm phân trang
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            $query .= " ORDER BY s.created_at DESC LIMIT $limit OFFSET $offset";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'status' => 'success',
                'data' => $sessions,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $this->getTotalSessions($params)
                ]
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Lấy tổng số phiên
    private function getTotalSessions($params) {
        $query = "SELECT COUNT(*) as total FROM sessions s
                 LEFT JOIN users u ON s.user_id = u.user_id
                 WHERE 1=1";
        
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query .= " AND (u.username LIKE '%$search%' OR u.email LIKE '%$search%')";
        }

        if (!empty($params['status'])) {
            $status = $params['status'];
            $query .= " AND s.status = '$status'";
        }

        if (!empty($params['start_date'])) {
            $start_date = $params['start_date'];
            $query .= " AND s.created_at >= '$start_date'";
        }

        if (!empty($params['end_date'])) {
            $end_date = $params['end_date'];
            $query .= " AND s.created_at <= '$end_date'";
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
$api = new AuthAPI($db);

$method = $_SERVER['REQUEST_METHOD'];
$response = [];

switch($method) {
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if(isset($_GET['login'])) {
            $response = $api->login($data);
        } else if(isset($_GET['logout'])) {
            $response = $api->logout($data['session_id']);
        } else if(isset($_GET['forgot-password'])) {
            $response = $api->forgotPassword($data['email']);
        } else if(isset($_GET['reset-password'])) {
            $response = $api->resetPassword($data);
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Invalid endpoint'
            ];
        }
        break;
    case 'GET':
        if(isset($_GET['sessions'])) {
            $response = $api->getSessions($_GET);
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