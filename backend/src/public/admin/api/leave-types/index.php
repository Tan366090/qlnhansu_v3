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
            // Get leave type by ID
            $stmt = $pdo->prepare("SELECT * FROM leave_types WHERE id = ?");
            $stmt->execute([$path_parts[5]]);
            $leave_type = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($leave_type ?: ['error' => 'Leave type not found']);
        } else if (isset($_GET['name'])) {
            // Get leave type by name
            $stmt = $pdo->prepare("SELECT * FROM leave_types WHERE name = ?");
            $stmt->execute([$_GET['name']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else {
            // Get all leave types
            $stmt = $pdo->query("SELECT * FROM leave_types");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("INSERT INTO leave_types (name, description, max_days, carry_forward) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $data['name'],
            $data['description'],
            $data['max_days'],
            $data['carry_forward'] ?? false
        ]);
        echo json_encode(['message' => 'Leave type created successfully', 'id' => $pdo->lastInsertId()]);
        break;

    case 'PUT':
        if (isset($path_parts[5]) && is_numeric($path_parts[5])) {
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE leave_types SET name = ?, description = ?, max_days = ?, carry_forward = ? WHERE id = ?");
            $stmt->execute([
                $data['name'],
                $data['description'],
                $data['max_days'],
                $data['carry_forward'] ?? false,
                $path_parts[5]
            ]);
            echo json_encode(['message' => 'Leave type updated successfully']);
        }
        break;

    case 'DELETE':
        if (isset($path_parts[5]) && is_numeric($path_parts[5])) {
            // Check if leave type is being used in any leave requests
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM leave_requests WHERE leave_type = (SELECT name FROM leave_types WHERE id = ?)");
            $stmt->execute([$path_parts[5]]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                echo json_encode(['error' => 'Cannot delete leave type as it is being used in leave requests']);
            } else {
                $stmt = $pdo->prepare("DELETE FROM leave_types WHERE id = ?");
                $stmt->execute([$path_parts[5]]);
                echo json_encode(['message' => 'Leave type deleted successfully']);
            }
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?> 