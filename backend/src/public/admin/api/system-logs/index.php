<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
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
        if (isset($path_parts[5]) && is_numeric($path_parts[5])) {
            // Get system log by ID
            $stmt = $pdo->prepare("
                SELECT sl.*, u.full_name as user_name 
                FROM system_logs sl
                LEFT JOIN users u ON sl.user_id = u.id
                WHERE sl.id = ?
            ");
            $stmt->execute([$path_parts[5]]);
            $log = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($log ?: ['error' => 'System log not found']);
        } else if (isset($_GET['user_id'])) {
            // Get system logs by user ID
            $stmt = $pdo->prepare("
                SELECT sl.*, u.full_name as user_name 
                FROM system_logs sl
                LEFT JOIN users u ON sl.user_id = u.id
                WHERE sl.user_id = ? 
                ORDER BY sl.created_at DESC
            ");
            $stmt->execute([$_GET['user_id']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else if (isset($_GET['type'])) {
            // Get system logs by type
            $stmt = $pdo->prepare("
                SELECT sl.*, u.full_name as user_name 
                FROM system_logs sl
                LEFT JOIN users u ON sl.user_id = u.id
                WHERE sl.type = ? 
                ORDER BY sl.created_at DESC
            ");
            $stmt->execute([$_GET['type']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
            // Get system logs by date range
            $stmt = $pdo->prepare("
                SELECT sl.*, u.full_name as user_name 
                FROM system_logs sl
                LEFT JOIN users u ON sl.user_id = u.id
                WHERE sl.created_at BETWEEN ? AND ? 
                ORDER BY sl.created_at DESC
            ");
            $stmt->execute([$_GET['start_date'], $_GET['end_date']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else {
            // Get all system logs with pagination
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = ($page - 1) * $limit;

            $stmt = $pdo->prepare("
                SELECT sl.*, u.full_name as user_name 
                FROM system_logs sl
                LEFT JOIN users u ON sl.user_id = u.id
                ORDER BY sl.created_at DESC 
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$limit, $offset]);
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get total count
            $countStmt = $pdo->query("SELECT COUNT(*) FROM system_logs");
            $total = $countStmt->fetchColumn();

            echo json_encode([
                'data' => $logs,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'total_pages' => ceil($total / $limit)
                ]
            ]);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (!isset($data['user_id']) || !isset($data['type']) || !isset($data['description'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO system_logs (
                user_id, 
                type, 
                description, 
                ip_address, 
                user_agent, 
                created_at
            ) VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $data['user_id'],
            $data['type'],
            $data['description'],
            $data['ip_address'] ?? null,
            $data['user_agent'] ?? null
        ]);
        
        echo json_encode([
            'message' => 'System log created successfully',
            'id' => $pdo->lastInsertId()
        ]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?> 