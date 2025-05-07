<?php
// Include database connection
require_once '../../config/database.php';

// Handle different HTTP methods
switch ($method) {
    case 'GET':
        if ($id) {
            // Get single equipment with assigned employee info
            $stmt = $conn->prepare("
                SELECT e.*, 
                       a.name as assigned_employee_name,
                       a.employee_code as assigned_employee_code,
                       d.name as department_name
                FROM equipment e
                LEFT JOIN employees a ON e.assigned_to = a.id
                LEFT JOIN departments d ON e.department_id = d.id
                WHERE e.id = ?
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $equipment = $result->fetch_assoc();
            
            if ($equipment) {
                sendResponse($equipment);
            } else {
                handleError('Equipment not found', 404);
            }
        } else {
            // Get equipment list with filters
            $type = $_GET['type'] ?? null;
            $status = $_GET['status'] ?? null;
            $department_id = $_GET['department_id'] ?? null;
            $assigned_to = $_GET['assigned_to'] ?? null;
            
            $query = "
                SELECT e.*, 
                       a.name as assigned_employee_name,
                       a.employee_code as assigned_employee_code,
                       d.name as department_name
                FROM equipment e
                LEFT JOIN employees a ON e.assigned_to = a.id
                LEFT JOIN departments d ON e.department_id = d.id
                WHERE 1=1
            ";
            
            $params = [];
            $types = "";
            
            if ($type) {
                $query .= " AND e.type = ?";
                $params[] = $type;
                $types .= "s";
            }
            
            if ($status) {
                $query .= " AND e.status = ?";
                $params[] = $status;
                $types .= "s";
            }
            
            if ($department_id) {
                $query .= " AND e.department_id = ?";
                $params[] = $department_id;
                $types .= "i";
            }
            
            if ($assigned_to) {
                $query .= " AND e.assigned_to = ?";
                $params[] = $assigned_to;
                $types .= "i";
            }
            
            $query .= " ORDER BY e.name ASC";
            
            $stmt = $conn->prepare($query);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            
            $equipment_list = [];
            while ($row = $result->fetch_assoc()) {
                $equipment_list[] = $row;
            }
            sendResponse($equipment_list);
        }
        break;
        
    case 'POST':
        // Create new equipment
        $required_fields = ['name', 'type', 'department_id'];
        foreach ($required_fields as $field) {
            if (!isset($input[$field])) {
                handleError("Missing required field: $field");
            }
        }
        
        $stmt = $conn->prepare("
            INSERT INTO equipment (
                name,
                type,
                serial_number,
                purchase_date,
                purchase_cost,
                department_id,
                assigned_to,
                status,
                condition,
                notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param(
            "ssssdissss",
            $input['name'],
            $input['type'],
            $input['serial_number'] ?? '',
            $input['purchase_date'] ?? null,
            $input['purchase_cost'] ?? 0,
            $input['department_id'],
            $input['assigned_to'] ?? null,
            $input['status'] ?? 'available',
            $input['condition'] ?? 'good',
            $input['notes'] ?? ''
        );
        
        if ($stmt->execute()) {
            sendResponse(['id' => $conn->insert_id], 201);
        } else {
            handleError('Failed to create equipment record');
        }
        break;
        
    case 'PUT':
        // Update equipment
        if (!$id) {
            handleError('Equipment ID is required');
        }
        
        $stmt = $conn->prepare("
            UPDATE equipment 
            SET name = ?,
                type = ?,
                serial_number = ?,
                purchase_date = ?,
                purchase_cost = ?,
                department_id = ?,
                assigned_to = ?,
                status = ?,
                condition = ?,
                notes = ?
            WHERE id = ?
        ");
        
        $stmt->bind_param(
            "ssssdissssi",
            $input['name'],
            $input['type'],
            $input['serial_number'] ?? '',
            $input['purchase_date'] ?? null,
            $input['purchase_cost'] ?? 0,
            $input['department_id'],
            $input['assigned_to'] ?? null,
            $input['status'] ?? 'available',
            $input['condition'] ?? 'good',
            $input['notes'] ?? '',
            $id
        );
        
        if ($stmt->execute()) {
            sendResponse(['message' => 'Equipment updated successfully']);
        } else {
            handleError('Failed to update equipment');
        }
        break;
        
    case 'DELETE':
        // Delete equipment (only if not assigned)
        if (!$id) {
            handleError('Equipment ID is required');
        }
        
        // Check if equipment is assigned
        $check_stmt = $conn->prepare("SELECT assigned_to FROM equipment WHERE id = ?");
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $assigned_to = $result->fetch_assoc()['assigned_to'];
        
        if ($assigned_to) {
            handleError('Cannot delete assigned equipment', 400);
        }
        
        $stmt = $conn->prepare("DELETE FROM equipment WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            sendResponse(['message' => 'Equipment deleted successfully']);
        } else {
            handleError('Failed to delete equipment');
        }
        break;
        
    default:
        handleError('Method not allowed', 405);
} 