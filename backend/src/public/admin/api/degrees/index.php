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
            // Get degree by ID
            $stmt = $pdo->prepare("SELECT * FROM degrees WHERE degree_id = ?");
            $stmt->execute([$path_parts[5]]);
            $degree = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($degree ?: ['error' => 'Degree not found']);
        } else if (isset($_GET['user_id'])) {
            // Get degrees by user ID
            $stmt = $pdo->prepare("SELECT * FROM degrees WHERE user_id = ?");
            $stmt->execute([$_GET['user_id']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else if (isset($_GET['degree_name'])) {
            // Get degrees by name
            $stmt = $pdo->prepare("SELECT * FROM degrees WHERE degree_name LIKE ?");
            $stmt->execute(['%' . $_GET['degree_name'] . '%']);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else if (isset($_GET['is_active'])) {
            // Get degrees by active status
            $stmt = $pdo->prepare("SELECT * FROM degrees WHERE is_active = ?");
            $stmt->execute([$_GET['is_active']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else {
            // Get all degrees
            $stmt = $pdo->query("SELECT * FROM degrees");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("INSERT INTO degrees (user_id, degree_name, issue_date, expiry_date, validity, attachment_url, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['user_id'],
            $data['degree_name'],
            $data['issue_date'],
            $data['expiry_date'] ?? null,
            $data['validity'],
            $data['attachment_url'] ?? null,
            $data['is_active'] ?? 1
        ]);
        echo json_encode(['message' => 'Degree created successfully', 'degree_id' => $pdo->lastInsertId()]);
        break;

    case 'PUT':
        if (isset($path_parts[5]) && is_numeric($path_parts[5])) {
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE degrees SET user_id = ?, degree_name = ?, issue_date = ?, expiry_date = ?, validity = ?, attachment_url = ?, is_active = ? WHERE degree_id = ?");
            $stmt->execute([
                $data['user_id'],
                $data['degree_name'],
                $data['issue_date'],
                $data['expiry_date'] ?? null,
                $data['validity'],
                $data['attachment_url'] ?? null,
                $data['is_active'] ?? 1,
                $path_parts[5]
            ]);
            echo json_encode(['message' => 'Degree updated successfully']);
        }
        break;

    case 'DELETE':
        if (isset($path_parts[5]) && is_numeric($path_parts[5])) {
            $stmt = $pdo->prepare("DELETE FROM degrees WHERE degree_id = ?");
            $stmt->execute([$path_parts[5]]);
            echo json_encode(['message' => 'Degree deleted successfully']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?> 