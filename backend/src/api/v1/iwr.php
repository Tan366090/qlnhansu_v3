<?php
require_once '../config/config.php';
require_once '../config/cors.php';

// Configure CORS
configureCORS();
handlePreflightRequest();

// Check rate limit
try {
    $ip = $_SERVER['REMOTE_ADDR'];
    $endpoint = $_SERVER['REQUEST_URI'];
    checkRateLimit($ip, $endpoint);
} catch (Exception $e) {
    http_response_code($e->getCode());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit();
}

header('Content-Type: application/json');

// Kiểm tra token và phân quyền
function checkAuth() {
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        throw new Exception('Token không hợp lệ');
    }

    $token = str_replace('Bearer ', '', $headers['Authorization']);
    $payload = verifyJWT($token);

    return $payload;
}

// Xác thực JWT token
function verifyJWT($token) {
    try {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new Exception('Token không hợp lệ', 401);
        }

        $header = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[0])), true);
        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true);
        $signature = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[2]));

        // Kiểm tra signature
        $validSignature = hash_hmac('sha256', $parts[0] . "." . $parts[1], JWT_SECRET, true);
        if (!hash_equals($signature, $validSignature)) {
            throw new Exception('Token không hợp lệ', 401);
        }

        // Kiểm tra thời gian hết hạn
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new Exception('Token đã hết hạn', 401);
        }

        return $payload;
    } catch (Exception $e) {
        error_log("JWT Verification Error: " . $e->getMessage());
        throw new Exception('Token không hợp lệ', 401);
    }
}

// Kiểm tra quyền truy cập
function checkPermission($requiredRole) {
    try {
        $payload = checkAuth();
        
        if (!isset($payload['role'])) {
            throw new Exception('Token không chứa thông tin quyền truy cập', 403);
        }

        if ($payload['role'] === 'admin') {
            return true;
        }

        if ($requiredRole === 'employee' && in_array($payload['role'], ['admin', 'manager', 'employee'])) {
            return true;
        }

        if ($requiredRole === 'manager' && in_array($payload['role'], ['admin', 'manager'])) {
            return true;
        }

        throw new Exception('Bạn không có quyền truy cập', 403);
    } catch (Exception $e) {
        error_log("Permission Check Error: " . $e->getMessage());
        throw new Exception($e->getMessage(), $e->getCode() ?: 403);
    }
}

// Lấy thông tin người dùng hiện tại
function getCurrentUser() {
    try {
        $payload = checkAuth();
        
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            error_log("Database Connection Error: " . $conn->connect_error);
            throw new Exception('Lỗi kết nối database', 500);
        }

        $stmt = $conn->prepare("SELECT user_id, email, full_name, role, status FROM users WHERE user_id = ? AND status = 'active'");
        if (!$stmt) {
            error_log("Prepare Statement Error: " . $conn->error);
            throw new Exception('Lỗi truy vấn database', 500);
        }

        $stmt->bind_param("i", $payload['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Người dùng không tồn tại hoặc đã bị vô hiệu hóa', 404);
        }

        $user = $result->fetch_assoc();
        $stmt->close();
        $conn->close();

        return $user;
    } catch (Exception $e) {
        error_log("Get Current User Error: " . $e->getMessage());
        throw new Exception($e->getMessage(), $e->getCode() ?: 500);
    }
}

// Xử lý request
try {
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    $response = ['success' => false, 'message' => '', 'data' => null];
    $statusCode = 200;

    switch ($action) {
        case 'check-auth':
            $payload = checkAuth();
            $response = [
                'success' => true,
                'data' => $payload
            ];
            break;

        case 'get-user':
            $user = getCurrentUser();
            $response = [
                'success' => true,
                'data' => $user
            ];
            break;

        case 'check-permission':
            $requiredRole = isset($_GET['role']) ? $_GET['role'] : 'employee';
            checkPermission($requiredRole);
            $response = [
                'success' => true,
                'message' => 'Bạn có quyền truy cập'
            ];
            break;

        case 'submit-degree':
            // Validate input
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                throw new Exception('Invalid JSON input', 400);
            }

            // Validate required fields
            $requiredFields = ['degree_name', 'issue_date', 'attachment_url'];
            foreach ($requiredFields as $field) {
                if (!isset($input[$field]) || empty($input[$field])) {
                    throw new Exception("Field '$field' is required", 400);
                }
            }

            // Validate attachment_url format
            if (is_array($input['attachment_url'])) {
                if (!isset($input['attachment_url']['url'])) {
                    throw new Exception("Invalid attachment_url format. Expected 'url' field", 400);
                }
                $input['attachment_url'] = $input['attachment_url']['url'];
            }

            // Validate date format
            if (!strtotime($input['issue_date'])) {
                throw new Exception("Invalid date format for issue_date", 400);
            }

            // Process the submission
            $conn = getDBConnection();
            $stmt = $conn->prepare("INSERT INTO degrees (degree_name, issue_date, attachment_url) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $input['degree_name'], $input['issue_date'], $input['attachment_url']);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to save degree: " . $stmt->error, 500);
            }

            $response = [
                'success' => true,
                'message' => 'Degree saved successfully',
                'id' => $stmt->insert_id
            ];
            break;

        default:
            $statusCode = 400;
            throw new Exception('Action không hợp lệ', 400);
    }

} catch (Exception $e) {
    $statusCode = $e->getCode() ?: 500;
    $response = [
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => $statusCode
    ];
    error_log("API Error: " . $e->getMessage() . " [Status: $statusCode]");
}

http_response_code($statusCode);
header('Content-Type: application/json');
echo json_encode($response);
?> 