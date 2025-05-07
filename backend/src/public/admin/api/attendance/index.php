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
            // Get attendance by ID
            $stmt = $pdo->prepare("SELECT * FROM attendance WHERE id = ?");
            $stmt->execute([$path_parts[5]]);
            $attendance = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($attendance ?: ['error' => 'Attendance record not found']);
        } else if (isset($_GET['employee_id'])) {
            // Get attendance by employee ID
            $stmt = $pdo->prepare("SELECT * FROM attendance WHERE employee_id = ?");
            $stmt->execute([$_GET['employee_id']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else if (isset($_GET['date'])) {
            // Get attendance by date
            $stmt = $pdo->prepare("SELECT * FROM attendance WHERE attendance_date = ?");
            $stmt->execute([$_GET['date']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else {
            // Get all attendance records
            $stmt = $pdo->query("SELECT * FROM attendance");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("INSERT INTO attendance (employee_id, attendance_date, check_in, check_out, status, notes) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['employee_id'],
            $data['attendance_date'],
            $data['check_in'],
            $data['check_out'],
            $data['status'],
            $data['notes'] ?? null
        ]);
        echo json_encode(['message' => 'Attendance record created successfully', 'id' => $pdo->lastInsertId()]);
        break;

    case 'PUT':
        if (isset($path_parts[5]) && is_numeric($path_parts[5])) {
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE attendance SET employee_id = ?, attendance_date = ?, check_in = ?, check_out = ?, status = ?, notes = ? WHERE id = ?");
            $stmt->execute([
                $data['employee_id'],
                $data['attendance_date'],
                $data['check_in'],
                $data['check_out'],
                $data['status'],
                $data['notes'] ?? null,
                $path_parts[5]
            ]);
            echo json_encode(['message' => 'Attendance record updated successfully']);
        }
        break;

    case 'DELETE':
        if (isset($path_parts[5]) && is_numeric($path_parts[5])) {
            $stmt = $pdo->prepare("DELETE FROM attendance WHERE id = ?");
            $stmt->execute([$path_parts[5]]);
            echo json_encode(['message' => 'Attendance record deleted successfully']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?> 