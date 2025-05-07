<?php
// Bật báo lỗi chi tiết
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cấu hình CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Xử lý preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Kiểm tra authentication
function checkAuth() {
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized: No token provided']);
        exit();
    }

    $token = str_replace('Bearer ', '', $headers['Authorization']);
    if (empty($token)) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized: Invalid token']);
        exit();
    }

    return $token;
}

// Kiểm tra kết nối database
function testDatabase() {
    try {
        $configPath = __DIR__ . '/../../config/database.php';
        if (!file_exists($configPath)) {
            throw new Exception("Database configuration file not found");
        }

        $dbConfig = require $configPath;
        
        $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 5
        ];

        $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $options);
        
        // Kiểm tra kết nối
        $pdo->query("SELECT 1");
        
        // Kiểm tra các bảng cần thiết
        $requiredTables = ['employees', 'departments', 'positions', 'attendance', 'payroll'];
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        $missingTables = array_diff($requiredTables, $tables);
        if (!empty($missingTables)) {
            throw new Exception("Missing required tables: " . implode(', ', $missingTables));
        }
        
        return true;
    } catch (Exception $e) {
        throw new Exception("Database test failed: " . $e->getMessage());
    }
}

// Kiểm tra session
function testSession() {
    try {
        session_start();
        return isset($_SESSION['user_id']);
    } catch (Exception $e) {
        throw new Exception("Session test failed: " . $e->getMessage());
    }
}

// Kiểm tra quyền truy cập file
function testFilePermissions() {
    try {
        $testFile = __DIR__ . '/test.txt';
        $testContent = 'Test content';
        
        // Kiểm tra ghi file
        if (file_put_contents($testFile, $testContent) === false) {
            throw new Exception("Cannot write to file");
        }
        
        // Kiểm tra đọc file
        if (file_get_contents($testFile) !== $testContent) {
            throw new Exception("Cannot read from file");
        }
        
        // Xóa file test
        unlink($testFile);
        
        return true;
    } catch (Exception $e) {
        throw new Exception("File permissions test failed: " . $e->getMessage());
    }
}

// Xử lý request
try {
    // Kiểm tra authentication
    checkAuth();
    
    // Lấy endpoint từ URL
    $endpoint = $_GET['endpoint'] ?? '';
    
    switch ($endpoint) {
        case 'connection':
            echo json_encode([
                'success' => true,
                'message' => 'API connection successful'
            ]);
            break;
            
        case 'database':
            $result = testDatabase();
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Database connection successful' : 'Database connection failed'
            ]);
            break;
            
        case 'session':
            $result = testSession();
            echo json_encode([
                'success' => $result,
                'active' => $result,
                'message' => $result ? 'Session is active' : 'Session is not active'
            ]);
            break;
            
        case 'permissions':
            $result = testFilePermissions();
            echo json_encode([
                'success' => $result,
                'writable' => $result,
                'message' => $result ? 'File permissions are correct' : 'File permissions are incorrect'
            ]);
            break;
            
        default:
            throw new Exception("Invalid endpoint");
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 