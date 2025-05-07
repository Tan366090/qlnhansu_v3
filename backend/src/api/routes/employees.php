<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../config/database.php';

try {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_departments':
            getDepartments();
            break;
        case 'get_positions':
            getPositions();
            break;
        case 'get_document_categories':
            getDocumentCategories();
            break;
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function getDepartments() {
    global $conn;
    
    try {
        $query = "SELECT 
                    d.id,
                    d.name,
                    d.code,
                    d.description,
                    d.parent_id,
                    d.manager_id,
                    e.name as manager_name,
                    parent.name as parent_name
                FROM departments d
                LEFT JOIN employees e ON d.manager_id = e.id
                LEFT JOIN departments parent ON d.parent_id = parent.id
                ORDER BY d.name";
        
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $departments = [];
        while ($row = $result->fetch_assoc()) {
            $departments[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'code' => $row['code'],
                'description' => $row['description'],
                'parent_id' => $row['parent_id'],
                'parent_name' => $row['parent_name'],
                'manager_id' => $row['manager_id'],
                'manager_name' => $row['manager_name']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'data' => $departments
        ]);
        
    } catch (Exception $e) {
        throw new Exception('Error fetching departments: ' . $e->getMessage());
    }
}

function getPositions() {
    global $conn;
    
    try {
        $stmt = $conn->query("SELECT id, name FROM positions");
        $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $positions
        ]);
    } catch (Exception $e) {
        throw new Exception('Error fetching positions: ' . $e->getMessage());
    }
}

function getDocumentCategories() {
    global $conn;
    
    try {
        $stmt = $conn->query("SELECT id, name FROM document_categories");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $categories
        ]);
    } catch (Exception $e) {
        throw new Exception('Error fetching document categories: ' . $e->getMessage());
    }
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Handle different HTTP methods
switch ($method) {
    case 'GET':
        // Check for action parameter
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'get_departments':
                    getDepartments();
                    break;
                    
                case 'get_positions':
                    getPositions();
                    break;
                    
                case 'get_document_categories':
                    getDocumentCategories();
                    break;
                    
                default:
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Invalid action'
                    ]);
                    break;
            }
        } else if (isset($uri[4])) {
            // Get specific employee
            $employee_id = $uri[4];
            $stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
            $stmt->execute([$employee_id]);
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($employee) {
                echo json_encode([
                    'status' => 'success',
                    'data' => $employee
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Employee not found'
                ]);
            }
        } else {
            // Get all employees
            $stmt = $conn->query("SELECT * FROM employees");
            $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'status' => 'success',
                'data' => $employees
            ]);
        }
        break;
        
    case 'POST':
        // Add new employee
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!empty($data)) {
            $stmt = $conn->prepare("INSERT INTO employees (name, email, position, department) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $data['name'],
                $data['email'],
                $data['position'],
                $data['department']
            ]);
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Employee added successfully',
                'id' => $conn->lastInsertId()
            ]);
        } else {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid data'
            ]);
        }
        break;
        
    case 'PUT':
        // Update employee
        if (isset($uri[4])) {
            $employee_id = $uri[4];
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!empty($data)) {
                $stmt = $conn->prepare("UPDATE employees SET name = ?, email = ?, position = ?, department = ? WHERE id = ?");
                $stmt->execute([
                    $data['name'],
                    $data['email'],
                    $data['position'],
                    $data['department'],
                    $employee_id
                ]);
                
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Employee updated successfully'
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Invalid data'
                ]);
            }
        } else {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Employee ID is required'
            ]);
        }
        break;
        
    case 'DELETE':
        // Delete employee
        if (isset($uri[4])) {
            $employee_id = $uri[4];
            $stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
            $stmt->execute([$employee_id]);
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Employee deleted successfully'
            ]);
        } else {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Employee ID is required'
            ]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'message' => 'Method not allowed'
        ]);
        break;
}
?> 