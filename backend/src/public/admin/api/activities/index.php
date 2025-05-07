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
        if (isset($path_parts[5]) && is_numeric($path_parts[5])) {
            // Get activity by ID
            $stmt = $pdo->prepare("SELECT * FROM activities WHERE id = ?");
            $stmt->execute([$path_parts[5]]);
            $activity = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($activity ?: ['error' => 'Activity not found']);
        } else if (isset($_GET['user_id'])) {
            // Get activities by user ID
            $stmt = $pdo->prepare("SELECT * FROM activities WHERE user_id = ?");
            $stmt->execute([$_GET['user_id']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else if (isset($_GET['type'])) {
            // Get activities by type
            $stmt = $pdo->prepare("SELECT * FROM activities WHERE type = ?");
            $stmt->execute([$_GET['type']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else if (isset($_GET['status'])) {
            // Get activities by status
            $stmt = $pdo->prepare("SELECT * FROM activities WHERE status = ?");
            $stmt->execute([$_GET['status']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
            // Get activities by date range
            $stmt = $pdo->prepare("SELECT * FROM activities WHERE created_at BETWEEN ? AND ?");
            $stmt->execute([$_GET['start_date'], $_GET['end_date']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else {
            // Get all activities
            $stmt = $pdo->query("SELECT * FROM activities");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("INSERT INTO activities (user_id, type, description, user_agent, ip_address, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['user_id'],
            $data['type'],
            $data['description'],
            $data['user_agent'] ?? null,
            $data['ip_address'] ?? null,
            $data['status'] ?? 'success'
        ]);
        echo json_encode(['message' => 'Activity created successfully', 'id' => $pdo->lastInsertId()]);
        break;

    case 'PUT':
        if (isset($path_parts[5]) && is_numeric($path_parts[5])) {
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE activities SET user_id = ?, type = ?, description = ?, user_agent = ?, ip_address = ?, status = ? WHERE id = ?");
            $stmt->execute([
                $data['user_id'],
                $data['type'],
                $data['description'],
                $data['user_agent'] ?? null,
                $data['ip_address'] ?? null,
                $data['status'] ?? 'success',
                $path_parts[5]
            ]);
            echo json_encode(['message' => 'Activity updated successfully']);
        }
        break;

    case 'DELETE':
        if (isset($path_parts[5]) && is_numeric($path_parts[5])) {
            $stmt = $pdo->prepare("DELETE FROM activities WHERE id = ?");
            $stmt->execute([$path_parts[5]]);
            echo json_encode(['message' => 'Activity deleted successfully']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?> 