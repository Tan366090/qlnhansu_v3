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
            // Get candidate by ID
            $stmt = $pdo->prepare("SELECT * FROM candidates WHERE id = ?");
            $stmt->execute([$path_parts[5]]);
            $candidate = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($candidate ?: ['error' => 'Candidate not found']);
        } else if (isset($_GET['status'])) {
            // Get candidates by status
            $stmt = $pdo->prepare("SELECT * FROM candidates WHERE status = ?");
            $stmt->execute([$_GET['status']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else if (isset($_GET['position'])) {
            // Get candidates by position
            $stmt = $pdo->prepare("SELECT * FROM candidates WHERE position = ?");
            $stmt->execute([$_GET['position']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else {
            // Get all candidates with pagination
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = ($page - 1) * $limit;

            $stmt = $pdo->prepare("SELECT * FROM candidates ORDER BY created_at DESC LIMIT ? OFFSET ?");
            $stmt->execute([$limit, $offset]);
            $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get total count
            $countStmt = $pdo->query("SELECT COUNT(*) FROM candidates");
            $total = $countStmt->fetchColumn();

            echo json_encode([
                'data' => $candidates,
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
        if (!isset($data['full_name']) || !isset($data['email']) || !isset($data['phone']) || !isset($data['position'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO candidates (full_name, email, phone, position, status, resume_url, interview_date, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['full_name'],
            $data['email'],
            $data['phone'],
            $data['position'],
            $data['status'] ?? 'pending',
            $data['resume_url'] ?? null,
            $data['interview_date'] ?? null,
            $data['notes'] ?? null
        ]);
        
        echo json_encode([
            'message' => 'Candidate created successfully',
            'id' => $pdo->lastInsertId()
        ]);
        break;

    case 'PUT':
        if (isset($path_parts[5]) && is_numeric($path_parts[5])) {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            if (!isset($data['full_name']) || !isset($data['email']) || !isset($data['phone']) || !isset($data['position'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE candidates SET full_name = ?, email = ?, phone = ?, position = ?, status = ?, resume_url = ?, interview_date = ?, notes = ? WHERE id = ?");
            $stmt->execute([
                $data['full_name'],
                $data['email'],
                $data['phone'],
                $data['position'],
                $data['status'] ?? 'pending',
                $data['resume_url'] ?? null,
                $data['interview_date'] ?? null,
                $data['notes'] ?? null,
                $path_parts[5]
            ]);
            
            echo json_encode(['message' => 'Candidate updated successfully']);
        }
        break;

    case 'DELETE':
        if (isset($path_parts[5]) && is_numeric($path_parts[5])) {
            $stmt = $pdo->prepare("DELETE FROM candidates WHERE id = ?");
            $stmt->execute([$path_parts[5]]);
            echo json_encode(['message' => 'Candidate deleted successfully']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?> 