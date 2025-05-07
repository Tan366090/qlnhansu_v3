<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/auth.php';

// Check authentication
$auth = checkAuth();
if (!$auth['success']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get action from request
$action = $_GET['action'] ?? '';

// Handle different actions
switch ($action) {
    case 'getAll':
        getAllLeaves();
        break;
    case 'getById':
        getLeaveById();
        break;
    case 'request':
        requestLeave();
        break;
    case 'approve':
        approveLeave();
        break;
    case 'reject':
        rejectLeave();
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

// Get all leaves
function getAllLeaves() {
    global $conn;
    $user = $_SESSION['user'];
    
    try {
        $sql = "SELECT l.*, e.name as employee_name, p.name as position_name, d.name as department_name 
                FROM leaves l 
                JOIN employees e ON l.employee_id = e.id 
                JOIN positions p ON e.position_id = p.id 
                JOIN departments d ON p.department_id = d.id";
        
        // If not admin, only show leaves of the employee
        if ($user['role'] !== 'admin') {
            $sql .= " WHERE l.employee_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user['id']);
        } else {
            $stmt = $conn->prepare($sql);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $leaves = $result->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode(['success' => true, 'leaves' => $leaves]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

// Get leave by ID
function getLeaveById() {
    global $conn;
    $user = $_SESSION['user'];
    $id = $_GET['id'] ?? 0;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Leave ID is required']);
        return;
    }
    
    try {
        $sql = "SELECT l.*, e.name as employee_name, p.name as position_name, d.name as department_name 
                FROM leaves l 
                JOIN employees e ON l.employee_id = e.id 
                JOIN positions p ON e.position_id = p.id 
                JOIN departments d ON p.department_id = d.id 
                WHERE l.id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $leave = $result->fetch_assoc();
        
        if (!$leave) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Leave not found']);
            return;
        }
        
        // Check if user has permission to view this leave
        if ($user['role'] !== 'admin' && $leave['employee_id'] !== $user['id']) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            return;
        }
        
        echo json_encode(['success' => true, 'leave' => $leave]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

// Request a new leave
function requestLeave() {
    global $conn;
    $user = $_SESSION['user'];
    
    // Get data from request body
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $required = ['start_date', 'end_date', 'type', 'reason'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Field $field is required"]);
            return;
        }
    }
    
    try {
        // Check if dates are valid
        $start_date = new DateTime($data['start_date']);
        $end_date = new DateTime($data['end_date']);
        
        if ($end_date < $start_date) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'End date must be after start date']);
            return;
        }
        
        // Calculate number of days
        $interval = $start_date->diff($end_date);
        $days = $interval->days + 1; // Include both start and end dates
        
        // Insert leave request
        $sql = "INSERT INTO leaves (employee_id, start_date, end_date, days, type, reason, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'pending')";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ississ", 
            $user['id'],
            $data['start_date'],
            $data['end_date'],
            $days,
            $data['type'],
            $data['reason']
        );
        
        if ($stmt->execute()) {
            $leave_id = $conn->insert_id;
            echo json_encode(['success' => true, 'leave_id' => $leave_id]);
        } else {
            throw new Exception('Failed to create leave request');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

// Approve a leave request
function approveLeave() {
    global $conn;
    $user = $_SESSION['user'];
    
    // Only admin can approve leaves
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Permission denied']);
        return;
    }
    
    // Get data from request body
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? 0;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Leave ID is required']);
        return;
    }
    
    try {
        // Update leave status
        $sql = "UPDATE leaves SET status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user['id'], $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('Failed to approve leave');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

// Reject a leave request
function rejectLeave() {
    global $conn;
    $user = $_SESSION['user'];
    
    // Only admin can reject leaves
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Permission denied']);
        return;
    }
    
    // Get data from request body
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? 0;
    $reject_reason = $data['reject_reason'] ?? '';
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Leave ID is required']);
        return;
    }
    
    if (empty($reject_reason)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Reject reason is required']);
        return;
    }
    
    try {
        // Update leave status
        $sql = "UPDATE leaves SET status = 'rejected', reject_reason = ?, approved_by = ?, approved_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $reject_reason, $user['id'], $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('Failed to reject leave');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} 