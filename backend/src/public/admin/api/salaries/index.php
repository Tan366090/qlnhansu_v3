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
            // Get salary by ID
            $stmt = $pdo->prepare("SELECT * FROM salaries WHERE id = ?");
            $stmt->execute([$path_parts[5]]);
            $salary = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($salary ?: ['error' => 'Salary record not found']);
        } else if (isset($_GET['employee_id'])) {
            // Get salaries by employee ID
            $stmt = $pdo->prepare("SELECT * FROM salaries WHERE employee_id = ?");
            $stmt->execute([$_GET['employee_id']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else if (isset($_GET['month']) && isset($_GET['year'])) {
            // Get salaries by month and year
            $stmt = $pdo->prepare("SELECT * FROM salaries WHERE payroll_month = ? AND payroll_year = ?");
            $stmt->execute([$_GET['month'], $_GET['year']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else if (isset($_GET['status'])) {
            // Get salaries by status
            $stmt = $pdo->prepare("SELECT * FROM salaries WHERE status = ?");
            $stmt->execute([$_GET['status']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else {
            // Get all salaries
            $stmt = $pdo->query("SELECT * FROM salaries");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("INSERT INTO salaries (employee_id, payroll_month, payroll_year, base_salary, allowances, bonuses, deductions, net_salary, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['employee_id'],
            $data['payroll_month'],
            $data['payroll_year'],
            $data['base_salary'],
            $data['allowances'] ?? 0,
            $data['bonuses'] ?? 0,
            $data['deductions'] ?? 0,
            $data['base_salary'] + ($data['allowances'] ?? 0) + ($data['bonuses'] ?? 0) - ($data['deductions'] ?? 0),
            $data['status'] ?? 'pending'
        ]);
        echo json_encode(['message' => 'Salary record created successfully', 'id' => $pdo->lastInsertId()]);
        break;

    case 'PUT':
        if (isset($path_parts[5]) && is_numeric($path_parts[5])) {
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE salaries SET employee_id = ?, payroll_month = ?, payroll_year = ?, base_salary = ?, allowances = ?, bonuses = ?, deductions = ?, net_salary = ?, status = ? WHERE id = ?");
            $stmt->execute([
                $data['employee_id'],
                $data['payroll_month'],
                $data['payroll_year'],
                $data['base_salary'],
                $data['allowances'] ?? 0,
                $data['bonuses'] ?? 0,
                $data['deductions'] ?? 0,
                $data['base_salary'] + ($data['allowances'] ?? 0) + ($data['bonuses'] ?? 0) - ($data['deductions'] ?? 0),
                $data['status'] ?? 'pending',
                $path_parts[5]
            ]);
            echo json_encode(['message' => 'Salary record updated successfully']);
        }
        break;

    case 'DELETE':
        if (isset($path_parts[5]) && is_numeric($path_parts[5])) {
            $stmt = $pdo->prepare("DELETE FROM salaries WHERE id = ?");
            $stmt->execute([$path_parts[5]]);
            echo json_encode(['message' => 'Salary record deleted successfully']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?> 