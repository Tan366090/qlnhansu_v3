<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../includes/auth.php';

// Check authentication
checkAuth();

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Handle different request methods
switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            getEquipment($_GET['id']);
        } else {
            getEquipments();
        }
        break;
    case 'POST':
        if (isset($_GET['action']) && $_GET['action'] === 'assign') {
            assignEquipment();
        } else {
            addEquipment();
        }
        break;
    case 'PUT':
        updateEquipment();
        break;
    case 'DELETE':
        deleteEquipment();
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}

// Get all equipments
function getEquipments() {
    global $conn;
    
    try {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT e.*, 
                d.name as department_name,
                COALESCE(COUNT(a.id), 0) as assigned_count
                FROM equipments e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN equipment_assignments a ON e.id = a.equipment_id
                GROUP BY e.id
                ORDER BY e.name ASC
                LIMIT ? OFFSET ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $equipments = [];
        while ($row = $result->fetch_assoc()) {
            $equipments[] = $row;
        }
        
        // Get total count for pagination
        $countSql = "SELECT COUNT(*) as total FROM equipments";
        $countResult = $conn->query($countSql);
        $total = $countResult->fetch_assoc()['total'];
        $totalPages = ceil($total / $limit);
        
        echo json_encode([
            'success' => true,
            'equipments' => $equipments,
            'total_pages' => $totalPages
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Get single equipment
function getEquipment($id) {
    global $conn;
    
    try {
        $sql = "SELECT e.*, d.name as department_name
                FROM equipments e
                LEFT JOIN departments d ON e.department_id = d.id
                WHERE e.id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Equipment not found']);
            return;
        }
        
        $equipment = $result->fetch_assoc();
        
        // Get assignment history
        $historySql = "SELECT a.*, e.name as employee_name
                      FROM equipment_assignments a
                      JOIN employees e ON a.employee_id = e.id
                      WHERE a.equipment_id = ?
                      ORDER BY a.assigned_date DESC";
        
        $historyStmt = $conn->prepare($historySql);
        $historyStmt->bind_param('i', $id);
        $historyStmt->execute();
        $historyResult = $historyStmt->get_result();
        
        $history = [];
        while ($row = $historyResult->fetch_assoc()) {
            $history[] = $row;
        }
        
        $equipment['history'] = $history;
        
        echo json_encode(['success' => true, 'equipment' => $equipment]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Add new equipment
function addEquipment() {
    global $conn;
    
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['name']) || !isset($data['type']) || !isset($data['department_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            return;
        }
        
        $sql = "INSERT INTO equipments (name, type, model, serial_number, purchase_date, 
                warranty_end_date, department_id, status, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssssiss',
            $data['name'],
            $data['type'],
            $data['model'] ?? null,
            $data['serial_number'] ?? null,
            $data['purchase_date'] ?? null,
            $data['warranty_end_date'] ?? null,
            $data['department_id'],
            $data['status'] ?? 'available',
            $data['notes'] ?? null
        );
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
        } else {
            throw new Exception('Failed to add equipment');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Update equipment
function updateEquipment() {
    global $conn;
    
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Equipment ID is required']);
            return;
        }
        
        $sql = "UPDATE equipments SET 
                name = ?,
                type = ?,
                model = ?,
                serial_number = ?,
                purchase_date = ?,
                warranty_end_date = ?,
                department_id = ?,
                status = ?,
                notes = ?
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssssissi',
            $data['name'],
            $data['type'],
            $data['model'] ?? null,
            $data['serial_number'] ?? null,
            $data['purchase_date'] ?? null,
            $data['warranty_end_date'] ?? null,
            $data['department_id'],
            $data['status'] ?? 'available',
            $data['notes'] ?? null,
            $data['id']
        );
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('Failed to update equipment');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Delete equipment
function deleteEquipment() {
    global $conn;
    
    try {
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Equipment ID is required']);
            return;
        }
        
        $id = $_GET['id'];
        
        // Check if equipment is assigned
        $checkSql = "SELECT COUNT(*) as count FROM equipment_assignments WHERE equipment_id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param('i', $id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $count = $result->fetch_assoc()['count'];
        
        if ($count > 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Cannot delete assigned equipment']);
            return;
        }
        
        $sql = "DELETE FROM equipments WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('Failed to delete equipment');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Assign equipment to employee
function assignEquipment() {
    global $conn;
    
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['equipment_id']) || !isset($data['employee_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            return;
        }
        
        // Check if equipment is available
        $checkSql = "SELECT status FROM equipments WHERE id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param('i', $data['equipment_id']);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Equipment not found']);
            return;
        }
        
        $status = $result->fetch_assoc()['status'];
        if ($status !== 'available') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Equipment is not available']);
            return;
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert assignment record
            $sql = "INSERT INTO equipment_assignments (equipment_id, employee_id, assigned_date, notes)
                    VALUES (?, ?, NOW(), ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('iis',
                $data['equipment_id'],
                $data['employee_id'],
                $data['notes'] ?? null
            );
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to create assignment');
            }
            
            // Update equipment status
            $updateSql = "UPDATE equipments SET status = 'assigned' WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param('i', $data['equipment_id']);
            
            if (!$updateStmt->execute()) {
                throw new Exception('Failed to update equipment status');
            }
            
            $conn->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?> 