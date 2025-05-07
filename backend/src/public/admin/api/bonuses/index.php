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
            // Get bonus by ID
            $stmt = $pdo->prepare("SELECT * FROM bonuses WHERE bonus_id = ?");
            $stmt->execute([$path_parts[5]]);
            $bonus = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($bonus ?: ['error' => 'Bonus not found']);
        } else if (isset($_GET['user_id'])) {
            // Get bonuses by user ID
            $stmt = $pdo->prepare("SELECT * FROM bonuses WHERE user_id = ?");
            $stmt->execute([$_GET['user_id']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else if (isset($_GET['bonus_type'])) {
            // Get bonuses by type
            $stmt = $pdo->prepare("SELECT * FROM bonuses WHERE bonus_type = ?");
            $stmt->execute([$_GET['bonus_type']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
            // Get bonuses by date range
            $stmt = $pdo->prepare("SELECT * FROM bonuses WHERE effective_date BETWEEN ? AND ?");
            $stmt->execute([$_GET['start_date'], $_GET['end_date']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else {
            // Get all bonuses
            $stmt = $pdo->query("SELECT * FROM bonuses");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("INSERT INTO bonuses (user_id, bonus_type, amount, days_off, reason, effective_date, added_by_user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['user_id'],
            $data['bonus_type'],
            $data['amount'],
            $data['days_off'] ?? null,
            $data['reason'],
            $data['effective_date'],
            $data['added_by_user_id']
        ]);
        echo json_encode(['message' => 'Bonus created successfully', 'bonus_id' => $pdo->lastInsertId()]);
        break;

    case 'PUT':
        if (isset($path_parts[5]) && is_numeric($path_parts[5])) {
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE bonuses SET user_id = ?, bonus_type = ?, amount = ?, days_off = ?, reason = ?, effective_date = ?, added_by_user_id = ? WHERE bonus_id = ?");
            $stmt->execute([
                $data['user_id'],
                $data['bonus_type'],
                $data['amount'],
                $data['days_off'] ?? null,
                $data['reason'],
                $data['effective_date'],
                $data['added_by_user_id'],
                $path_parts[5]
            ]);
            echo json_encode(['message' => 'Bonus updated successfully']);
        }
        break;

    case 'DELETE':
        if (isset($path_parts[5]) && is_numeric($path_parts[5])) {
            $stmt = $pdo->prepare("DELETE FROM bonuses WHERE bonus_id = ?");
            $stmt->execute([$path_parts[5]]);
            echo json_encode(['message' => 'Bonus deleted successfully']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?> 