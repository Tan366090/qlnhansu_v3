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
            // Get certificate by ID
            $stmt = $pdo->prepare("SELECT * FROM certificates WHERE id = ?");
            $stmt->execute([$path_parts[5]]);
            $certificate = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($certificate ?: ['error' => 'Certificate not found']);
        } else if (isset($_GET['user_id'])) {
            // Get certificates by user ID
            $stmt = $pdo->prepare("SELECT * FROM certificates WHERE user_id = ?");
            $stmt->execute([$_GET['user_id']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else if (isset($_GET['certificate_type'])) {
            // Get certificates by type
            $stmt = $pdo->prepare("SELECT * FROM certificates WHERE certificate_type = ?");
            $stmt->execute([$_GET['certificate_type']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else {
            // Get all certificates with pagination
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = ($page - 1) * $limit;

            $stmt = $pdo->prepare("SELECT * FROM certificates ORDER BY created_at DESC LIMIT ? OFFSET ?");
            $stmt->execute([$limit, $offset]);
            $certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get total count
            $countStmt = $pdo->query("SELECT COUNT(*) FROM certificates");
            $total = $countStmt->fetchColumn();

            echo json_encode([
                'data' => $certificates,
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
        if (!isset($data['user_id']) || !isset($data['certificate_name']) || !isset($data['issuing_organization'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO certificates (user_id, certificate_name, issuing_organization, issue_date, expiry_date, certificate_url, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['user_id'],
            $data['certificate_name'],
            $data['issuing_organization'],
            $data['issue_date'] ?? null,
            $data['expiry_date'] ?? null,
            $data['certificate_url'] ?? null,
            $data['notes'] ?? null
        ]);
        
        echo json_encode([
            'message' => 'Certificate created successfully',
            'id' => $pdo->lastInsertId()
        ]);
        break;

    case 'PUT':
        if (isset($path_parts[5]) && is_numeric($path_parts[5])) {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            if (!isset($data['certificate_name']) || !isset($data['issuing_organization'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE certificates SET certificate_name = ?, issuing_organization = ?, issue_date = ?, expiry_date = ?, certificate_url = ?, notes = ? WHERE id = ?");
            $stmt->execute([
                $data['certificate_name'],
                $data['issuing_organization'],
                $data['issue_date'] ?? null,
                $data['expiry_date'] ?? null,
                $data['certificate_url'] ?? null,
                $data['notes'] ?? null,
                $path_parts[5]
            ]);
            
            echo json_encode(['message' => 'Certificate updated successfully']);
        }
        break;

    case 'DELETE':
        if (isset($path_parts[5]) && is_numeric($path_parts[5])) {
            $stmt = $pdo->prepare("DELETE FROM certificates WHERE id = ?");
            $stmt->execute([$path_parts[5]]);
            echo json_encode(['message' => 'Certificate deleted successfully']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?> 