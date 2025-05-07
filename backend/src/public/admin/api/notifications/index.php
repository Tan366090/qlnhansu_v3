<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT');
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
            // Get notification by ID
            $stmt = $pdo->prepare("SELECT * FROM notifications WHERE id = ?");
            $stmt->execute([$path_parts[5]]);
            $notification = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($notification ?: ['error' => 'Notification not found']);
        } else if (isset($_GET['user_id'])) {
            // Get notifications by user ID
            $stmt = $pdo->prepare("
                SELECT n.*, u.full_name as sender_name 
                FROM notifications n
                LEFT JOIN users u ON n.sender_id = u.id
                WHERE n.user_id = ? 
                ORDER BY n.created_at DESC
            ");
            $stmt->execute([$_GET['user_id']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else if (isset($_GET['type'])) {
            // Get notifications by type
            $stmt = $pdo->prepare("
                SELECT n.*, u.full_name as sender_name 
                FROM notifications n
                LEFT JOIN users u ON n.sender_id = u.id
                WHERE n.type = ? 
                ORDER BY n.created_at DESC
            ");
            $stmt->execute([$_GET['type']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else if (isset($_GET['status'])) {
            // Get notifications by status
            $stmt = $pdo->prepare("
                SELECT n.*, u.full_name as sender_name 
                FROM notifications n
                LEFT JOIN users u ON n.sender_id = u.id
                WHERE n.status = ? 
                ORDER BY n.created_at DESC
            ");
            $stmt->execute([$_GET['status']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else {
            // Get all notifications with pagination
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = ($page - 1) * $limit;

            $stmt = $pdo->prepare("
                SELECT n.*, u.full_name as sender_name 
                FROM notifications n
                LEFT JOIN users u ON n.sender_id = u.id
                ORDER BY n.created_at DESC 
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$limit, $offset]);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get total count
            $countStmt = $pdo->query("SELECT COUNT(*) FROM notifications");
            $total = $countStmt->fetchColumn();

            echo json_encode([
                'data' => $notifications,
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
        if (!isset($data['user_id']) || !isset($data['type']) || !isset($data['title']) || !isset($data['message'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO notifications (
                user_id, 
                sender_id, 
                type, 
                title, 
                message, 
                status, 
                read_at, 
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $data['user_id'],
            $data['sender_id'] ?? null,
            $data['type'],
            $data['title'],
            $data['message'],
            $data['status'] ?? 'unread',
            $data['read_at'] ?? null
        ]);
        
        echo json_encode([
            'message' => 'Notification created successfully',
            'id' => $pdo->lastInsertId()
        ]);
        break;

    case 'PUT':
        if (isset($path_parts[5]) && is_numeric($path_parts[5])) {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Update notification status
            if (isset($data['status'])) {
                $stmt = $pdo->prepare("UPDATE notifications SET status = ?, read_at = NOW() WHERE id = ?");
                $stmt->execute([$data['status'], $path_parts[5]]);
                echo json_encode(['message' => 'Notification status updated successfully']);
            }
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?> 