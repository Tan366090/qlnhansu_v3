<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Database configuration
$db_host = 'localhost';
$db_name = 'qlnhansu';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));

// Handle different HTTP methods
switch($method) {
    case 'GET':
        if (isset($path_parts[5]) && $path_parts[5] === 'system') {
            // Get system configuration
            $stmt = $pdo->query("SELECT * FROM system_config");
            $config = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $system_config = [];
            foreach ($config as $row) {
                $system_config[$row['key']] = $row['value'];
            }
            
            echo json_encode($system_config);
        } else if (isset($path_parts[5]) && $path_parts[5] === 'email') {
            // Get email configuration
            $stmt = $pdo->query("SELECT * FROM email_config");
            $config = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $email_config = [];
            foreach ($config as $row) {
                $email_config[$row['key']] = $row['value'];
            }
            
            echo json_encode($email_config);
        } else if (isset($path_parts[5]) && $path_parts[5] === 'attendance') {
            // Get attendance configuration
            $stmt = $pdo->query("SELECT * FROM attendance_config");
            $config = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $attendance_config = [];
            foreach ($config as $row) {
                $attendance_config[$row['key']] = $row['value'];
            }
            
            echo json_encode($attendance_config);
        } else if (isset($path_parts[5]) && $path_parts[5] === 'salary') {
            // Get salary configuration
            $stmt = $pdo->query("SELECT * FROM salary_config");
            $config = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $salary_config = [];
            foreach ($config as $row) {
                $salary_config[$row['key']] = $row['value'];
            }
            
            echo json_encode($salary_config);
        } else {
            // Get all configurations
            $configs = [
                'system' => [],
                'email' => [],
                'attendance' => [],
                'salary' => []
            ];
            
            // System config
            $stmt = $pdo->query("SELECT * FROM system_config");
            $system_config = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($system_config as $row) {
                $configs['system'][$row['key']] = $row['value'];
            }
            
            // Email config
            $stmt = $pdo->query("SELECT * FROM email_config");
            $email_config = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($email_config as $row) {
                $configs['email'][$row['key']] = $row['value'];
            }
            
            // Attendance config
            $stmt = $pdo->query("SELECT * FROM attendance_config");
            $attendance_config = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($attendance_config as $row) {
                $configs['attendance'][$row['key']] = $row['value'];
            }
            
            // Salary config
            $stmt = $pdo->query("SELECT * FROM salary_config");
            $salary_config = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($salary_config as $row) {
                $configs['salary'][$row['key']] = $row['value'];
            }
            
            echo json_encode($configs);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($path_parts[5]) && $path_parts[5] === 'system') {
            // Update system configuration
            foreach ($data as $key => $value) {
                $stmt = $pdo->prepare("INSERT INTO system_config (key, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = ?");
                $stmt->execute([$key, $value, $value]);
            }
            echo json_encode(['message' => 'System configuration updated successfully']);
        } else if (isset($path_parts[5]) && $path_parts[5] === 'email') {
            // Update email configuration
            foreach ($data as $key => $value) {
                $stmt = $pdo->prepare("INSERT INTO email_config (key, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = ?");
                $stmt->execute([$key, $value, $value]);
            }
            echo json_encode(['message' => 'Email configuration updated successfully']);
        } else if (isset($path_parts[5]) && $path_parts[5] === 'attendance') {
            // Update attendance configuration
            foreach ($data as $key => $value) {
                $stmt = $pdo->prepare("INSERT INTO attendance_config (key, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = ?");
                $stmt->execute([$key, $value, $value]);
            }
            echo json_encode(['message' => 'Attendance configuration updated successfully']);
        } else if (isset($path_parts[5]) && $path_parts[5] === 'salary') {
            // Update salary configuration
            foreach ($data as $key => $value) {
                $stmt = $pdo->prepare("INSERT INTO salary_config (key, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = ?");
                $stmt->execute([$key, $value, $value]);
            }
            echo json_encode(['message' => 'Salary configuration updated successfully']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid configuration type']);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($path_parts[5]) && $path_parts[5] === 'system') {
            // Update system configuration
            foreach ($data as $key => $value) {
                $stmt = $pdo->prepare("UPDATE system_config SET value = ? WHERE key = ?");
                $stmt->execute([$value, $key]);
            }
            echo json_encode(['message' => 'System configuration updated successfully']);
        } else if (isset($path_parts[5]) && $path_parts[5] === 'email') {
            // Update email configuration
            foreach ($data as $key => $value) {
                $stmt = $pdo->prepare("UPDATE email_config SET value = ? WHERE key = ?");
                $stmt->execute([$value, $key]);
            }
            echo json_encode(['message' => 'Email configuration updated successfully']);
        } else if (isset($path_parts[5]) && $path_parts[5] === 'attendance') {
            // Update attendance configuration
            foreach ($data as $key => $value) {
                $stmt = $pdo->prepare("UPDATE attendance_config SET value = ? WHERE key = ?");
                $stmt->execute([$value, $key]);
            }
            echo json_encode(['message' => 'Attendance configuration updated successfully']);
        } else if (isset($path_parts[5]) && $path_parts[5] === 'salary') {
            // Update salary configuration
            foreach ($data as $key => $value) {
                $stmt = $pdo->prepare("UPDATE salary_config SET value = ? WHERE key = ?");
                $stmt->execute([$value, $key]);
            }
            echo json_encode(['message' => 'Salary configuration updated successfully']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid configuration type']);
        }
        break;

    case 'DELETE':
        if (isset($path_parts[5]) && $path_parts[5] === 'system') {
            // Delete system configuration
            $key = isset($_GET['key']) ? $_GET['key'] : '';
            if ($key) {
                $stmt = $pdo->prepare("DELETE FROM system_config WHERE key = ?");
                $stmt->execute([$key]);
                echo json_encode(['message' => 'System configuration deleted successfully']);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Key is required']);
            }
        } else if (isset($path_parts[5]) && $path_parts[5] === 'email') {
            // Delete email configuration
            $key = isset($_GET['key']) ? $_GET['key'] : '';
            if ($key) {
                $stmt = $pdo->prepare("DELETE FROM email_config WHERE key = ?");
                $stmt->execute([$key]);
                echo json_encode(['message' => 'Email configuration deleted successfully']);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Key is required']);
            }
        } else if (isset($path_parts[5]) && $path_parts[5] === 'attendance') {
            // Delete attendance configuration
            $key = isset($_GET['key']) ? $_GET['key'] : '';
            if ($key) {
                $stmt = $pdo->prepare("DELETE FROM attendance_config WHERE key = ?");
                $stmt->execute([$key]);
                echo json_encode(['message' => 'Attendance configuration deleted successfully']);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Key is required']);
            }
        } else if (isset($path_parts[5]) && $path_parts[5] === 'salary') {
            // Delete salary configuration
            $key = isset($_GET['key']) ? $_GET['key'] : '';
            if ($key) {
                $stmt = $pdo->prepare("DELETE FROM salary_config WHERE key = ?");
                $stmt->execute([$key]);
                echo json_encode(['message' => 'Salary configuration deleted successfully']);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Key is required']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid configuration type']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?> 