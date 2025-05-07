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
            // Get employee by ID
            $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
            $stmt->execute([$path_parts[5]]);
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($employee ?: ['error' => 'Employee not found']);
        } else {
            // Get all employees
            $stmt = $pdo->query("SELECT * FROM employees");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("INSERT INTO employees (name, email, phone, department_id, position_id, hire_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$data['name'], $data['email'], $data['phone'], $data['department_id'], $data['position_id'], $data['hire_date']]);
        echo json_encode(['message' => 'Employee created successfully', 'id' => $pdo->lastInsertId()]);
        break;

    case 'PUT':
        if (isset($path_parts[5]) && is_numeric($path_parts[5])) {
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE employees SET name = ?, email = ?, phone = ?, department_id = ?, position_id = ?, hire_date = ? WHERE id = ?");
            $stmt->execute([$data['name'], $data['email'], $data['phone'], $data['department_id'], $data['position_id'], $data['hire_date'], $path_parts[5]]);
            echo json_encode(['message' => 'Employee updated successfully']);
        }
        break;

    case 'DELETE':
        if (isset($path_parts[5]) && is_numeric($path_parts[5])) {
            $stmt = $pdo->prepare("DELETE FROM employees WHERE id = ?");
            $stmt->execute([$path_parts[5]]);
            echo json_encode(['message' => 'Employee deleted successfully']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?> 