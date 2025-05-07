<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT');
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
        if (isset($path_parts[5]) && $path_parts[5] === 'attendance-symbols') {
            // Get attendance symbols
            $stmt = $pdo->query("SELECT * FROM attendance_symbols");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else if (isset($path_parts[5]) && $path_parts[5] === 'bonus-types') {
            // Get bonus types
            $stmt = $pdo->query("SELECT * FROM bonus_types");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else if (isset($path_parts[5]) && $path_parts[5] === 'degree-types') {
            // Get degree types
            $stmt = $pdo->query("SELECT * FROM degree_types");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else if (isset($path_parts[5]) && $path_parts[5] === 'leave-types') {
            // Get leave types
            $stmt = $pdo->query("SELECT * FROM leave_types");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else if (isset($path_parts[5]) && $path_parts[5] === 'document-types') {
            // Get document types
            $stmt = $pdo->query("SELECT * FROM document_types");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else {
            // Get all settings
            $settings = [];
            
            // Get attendance symbols
            $stmt = $pdo->query("SELECT * FROM attendance_symbols");
            $settings['attendance_symbols'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get bonus types
            $stmt = $pdo->query("SELECT * FROM bonus_types");
            $settings['bonus_types'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get degree types
            $stmt = $pdo->query("SELECT * FROM degree_types");
            $settings['degree_types'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get leave types
            $stmt = $pdo->query("SELECT * FROM leave_types");
            $settings['leave_types'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get document types
            $stmt = $pdo->query("SELECT * FROM document_types");
            $settings['document_types'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode($settings);
        }
        break;

    case 'POST':
        if (isset($path_parts[5]) && $path_parts[5] === 'attendance-symbols') {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['symbol']) || !isset($data['description'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }

            $stmt = $pdo->prepare("INSERT INTO attendance_symbols (symbol, description) VALUES (?, ?)");
            $stmt->execute([$data['symbol'], $data['description']]);
            echo json_encode(['message' => 'Attendance symbol created successfully']);
        } else if (isset($path_parts[5]) && $path_parts[5] === 'bonus-types') {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['type_name']) || !isset($data['description'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }

            $stmt = $pdo->prepare("INSERT INTO bonus_types (type_name, description) VALUES (?, ?)");
            $stmt->execute([$data['type_name'], $data['description']]);
            echo json_encode(['message' => 'Bonus type created successfully']);
        } else if (isset($path_parts[5]) && $path_parts[5] === 'degree-types') {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['type_name']) || !isset($data['description'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }

            $stmt = $pdo->prepare("INSERT INTO degree_types (type_name, description) VALUES (?, ?)");
            $stmt->execute([$data['type_name'], $data['description']]);
            echo json_encode(['message' => 'Degree type created successfully']);
        } else if (isset($path_parts[5]) && $path_parts[5] === 'leave-types') {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['type_name']) || !isset($data['description'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }

            $stmt = $pdo->prepare("INSERT INTO leave_types (type_name, description) VALUES (?, ?)");
            $stmt->execute([$data['type_name'], $data['description']]);
            echo json_encode(['message' => 'Leave type created successfully']);
        } else if (isset($path_parts[5]) && $path_parts[5] === 'document-types') {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['type_name']) || !isset($data['description'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }

            $stmt = $pdo->prepare("INSERT INTO document_types (type_name, description) VALUES (?, ?)");
            $stmt->execute([$data['type_name'], $data['description']]);
            echo json_encode(['message' => 'Document type created successfully']);
        }
        break;

    case 'PUT':
        if (isset($path_parts[5]) && $path_parts[5] === 'attendance-symbols' && isset($path_parts[6])) {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['symbol']) || !isset($data['description'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE attendance_symbols SET symbol = ?, description = ? WHERE id = ?");
            $stmt->execute([$data['symbol'], $data['description'], $path_parts[6]]);
            echo json_encode(['message' => 'Attendance symbol updated successfully']);
        } else if (isset($path_parts[5]) && $path_parts[5] === 'bonus-types' && isset($path_parts[6])) {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['type_name']) || !isset($data['description'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE bonus_types SET type_name = ?, description = ? WHERE id = ?");
            $stmt->execute([$data['type_name'], $data['description'], $path_parts[6]]);
            echo json_encode(['message' => 'Bonus type updated successfully']);
        } else if (isset($path_parts[5]) && $path_parts[5] === 'degree-types' && isset($path_parts[6])) {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['type_name']) || !isset($data['description'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE degree_types SET type_name = ?, description = ? WHERE id = ?");
            $stmt->execute([$data['type_name'], $data['description'], $path_parts[6]]);
            echo json_encode(['message' => 'Degree type updated successfully']);
        } else if (isset($path_parts[5]) && $path_parts[5] === 'leave-types' && isset($path_parts[6])) {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['type_name']) || !isset($data['description'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE leave_types SET type_name = ?, description = ? WHERE id = ?");
            $stmt->execute([$data['type_name'], $data['description'], $path_parts[6]]);
            echo json_encode(['message' => 'Leave type updated successfully']);
        } else if (isset($path_parts[5]) && $path_parts[5] === 'document-types' && isset($path_parts[6])) {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['type_name']) || !isset($data['description'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE document_types SET type_name = ?, description = ? WHERE id = ?");
            $stmt->execute([$data['type_name'], $data['description'], $path_parts[6]]);
            echo json_encode(['message' => 'Document type updated successfully']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?> 