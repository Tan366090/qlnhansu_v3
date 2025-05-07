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
            // Get benefit by ID
            $stmt = $pdo->prepare("SELECT * FROM benefits WHERE id = ?");
            $stmt->execute([$path_parts[5]]);
            $benefit = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($benefit ?: ['error' => 'Benefit not found']);
        } else if (isset($_GET['user_id'])) {
            // Get benefits by user ID
            $stmt = $pdo->prepare("SELECT * FROM benefits WHERE user_id = ?");
            $stmt->execute([$_GET['user_id']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else if (isset($_GET['benefit_type'])) {
            // Get benefits by type
            $stmt = $pdo->prepare("SELECT * FROM benefits WHERE benefit_type = ?");
            $stmt->execute([$_GET['benefit_type']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else {
            // Get all benefits with pagination
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = ($page - 1) * $limit;

            $stmt = $pdo->prepare("SELECT * FROM benefits ORDER BY created_at DESC LIMIT ? OFFSET ?");
            $stmt->execute([$limit, $offset]);
            $benefits = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get total count
            $countStmt = $pdo->query("SELECT COUNT(*) FROM benefits");
            $total = $countStmt->fetchColumn();

            echo json_encode([
                'data' => $benefits,
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
        if (!isset($data['user_id']) || !isset($data['benefit_type']) || !isset($data['description'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO benefits (user_id, benefit_type, description, amount, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['user_id'],
            $data['benefit_type'],
            $data['description'],
            $data['amount'] ?? null,
            $data['start_date'] ?? null,
            $data['end_date'] ?? null
        ]);
        
        echo json_encode([
            'message' => 'Benefit created successfully',
            'id' => $pdo->lastInsertId()
        ]);
        break;

    case 'PUT':
        if (isset($path_parts[5]) && is_numeric($path_parts[5])) {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            if (!isset($data['benefit_type']) || !isset($data['description'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE benefits SET benefit_type = ?, description = ?, amount = ?, start_date = ?, end_date = ? WHERE id = ?");
            $stmt->execute([
                $data['benefit_type'],
                $data['description'],
                $data['amount'] ?? null,
                $data['start_date'] ?? null,
                $data['end_date'] ?? null,
                $path_parts[5]
            ]);
            
            echo json_encode(['message' => 'Benefit updated successfully']);
        }
        break;

    case 'DELETE':
        if (isset($path_parts[5]) && is_numeric($path_parts[5])) {
            $stmt = $pdo->prepare("DELETE FROM benefits WHERE id = ?");
            $stmt->execute([$path_parts[5]]);
            echo json_encode(['message' => 'Benefit deleted successfully']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?> 