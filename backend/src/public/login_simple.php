<?php
// Bật hiển thị lỗi PHP tạm thời để debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Đảm bảo header JSON được gửi trước
header('Content-Type: application/json; charset=utf-8');

// Khởi tạo output buffer
ob_start();

// Log function
function logError($message, $data = null) {
    $logMessage = "[Login Debug] " . $message;
    if ($data !== null) {
        $logMessage .= " Data: " . print_r($data, true);
    }
    error_log($logMessage);
}

try {
    logError("Starting login process");
    
    // Kiểm tra phương thức request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Phương thức không được hỗ trợ');
    }

    // Log request headers
    $headers = getallheaders();
    logError("Request headers:", $headers);

    // Đọc raw input
    $rawInput = file_get_contents('php://input');
    logError("Raw input received:", $rawInput);

    // Đọc JSON input
    $data = json_decode($rawInput, true);
    logError("Decoded JSON data:", $data);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }

    // Lấy dữ liệu từ JSON
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    logError("Processed login attempt for username: " . $username);

    // Kiểm tra dữ liệu đầu vào
    if (empty($username) || empty($password)) {
        throw new Exception('Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu');
    }

    // Kết nối database
    logError("Loading database.php");
    $databasePath = __DIR__ . '/../config/database.php';
    logError("Checking database path: " . $databasePath);
    if (!file_exists($databasePath)) {
        throw new Exception("database.php not found at: " . $databasePath);
    }
    require_once $databasePath;
    logError("database.php loaded successfully");
    
    $db = Database::getInstance();
    $conn = $db->getConnection();
    logError("Database connection established");

    // Lấy thông tin người dùng
    $query = "SELECT u.*, r.role_name 
              FROM users u 
              JOIN roles r ON u.role_id = r.role_id 
              WHERE u.username = :username AND u.is_active = 1";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        logError("User not found: " . $username);
        throw new Exception('Tên đăng nhập hoặc mật khẩu không đúng');
    }

    logError("User found: " . $username);

    // Kiểm tra mật khẩu
    if (!password_verify($password, $user['password_hash'])) {
        logError("Invalid password for user: " . $username);
        throw new Exception('Tên đăng nhập hoặc mật khẩu không đúng');
    }

    logError("Password verified for user: " . $username);

    // Tạo session
    session_start();
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role_name'];
    $_SESSION['last_activity'] = time();

    logError("Session created for user: " . $username);

    // Lấy cấu hình roles
    logError("Loading roles configuration");
    $rolesPath = __DIR__ . '/../config/permissions.php';
    logError("Checking roles path: " . $rolesPath);
    if (!file_exists($rolesPath)) {
        throw new Exception("permissions.php not found at: " . $rolesPath);
    }
    $permissions = require $rolesPath;
    $role = strtolower($user['role_name']);
    
    if (!isset($permissions['roles'][$role])) {
        logError("Invalid role for user: " . $username . ", role: " . $role);
        throw new Exception('Vai trò không hợp lệ');
    }

    logError("Role configuration loaded for user: " . $username);

    // Trả về kết quả thành công
    $response = [
        'success' => true,
        'message' => 'Đăng nhập thành công',
        'redirectUrl' => '/QLNhanSu/backend/src/public/dashboard.html',
        'user' => [
            'id' => $user['user_id'],
            'username' => $user['username'],
            'role' => $user['role_name']
        ]
    ];

} catch (Exception $e) {
    logError("Error during login: " . $e->getMessage());
    // Trả về lỗi
    http_response_code(400);
    $response = [
        'success' => false,
        'error' => $e->getMessage()
    ];
}

// Xóa output buffer
ob_clean();

// Trả về JSON response
echo json_encode($response, JSON_UNESCAPED_UNICODE); 