<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    $method = $_SERVER['REQUEST_METHOD'];

    switch($method) {
        case 'GET':
            // Get all contract types
            $query = "SELECT * FROM contract_types ORDER BY name";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $contractTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $contractTypes
            ]);
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['name'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Contract type name is required'
                ]);
                exit();
            }
            
            $query = "INSERT INTO contract_types (name, description) VALUES (?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->execute([
                $data['name'],
                $data['description'] ?? null
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Contract type created successfully',
                'id' => $conn->lastInsertId()
            ]);
            break;
            
        case 'PUT':
            if (!isset($_GET['id'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Contract type ID is required'
                ]);
                exit();
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['name'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Contract type name is required'
                ]);
                exit();
            }
            
            $query = "UPDATE contract_types SET name = ?, description = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([
                $data['name'],
                $data['description'] ?? null,
                $_GET['id']
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Contract type updated successfully'
            ]);
            break;
            
        case 'DELETE':
            if (!isset($_GET['id'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Contract type ID is required'
                ]);
                exit();
            }
            
            $query = "DELETE FROM contract_types WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$_GET['id']]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Contract type deleted successfully'
            ]);
            break;
            
        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed'
            ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 