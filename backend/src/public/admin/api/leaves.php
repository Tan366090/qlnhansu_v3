<?php
// Include database connection
require_once '../../config/database.php';

// Handle different HTTP methods
switch ($method) {
    case 'GET':
        if ($id) {
            // Get single leave request
            $stmt = $conn->prepare("
                SELECT l.*, 
                       u.username as user_name,
                       u.email as user_email,
                       d.name as department_name,
                       p.name as position_name
                FROM leaves l
                LEFT JOIN users u ON l.user_id = u.user_id
                LEFT JOIN departments d ON u.department_id = d.id
                LEFT JOIN positions p ON u.position_id = p.id
                WHERE l.id = ?
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $leave = $result->fetch_assoc();
            
            if ($leave) {
                sendResponse($leave);
            } else {
                handleError('Leave request not found', 404);
            }
        } else {
            // Get leaves list with filters
            $user_id = $_GET['user_id'] ?? null;
            $type = $_GET['type'] ?? null;
            $status = $_GET['status'] ?? null;
            $start_date = $_GET['start_date'] ?? null;
            $end_date = $_GET['end_date'] ?? null;
            $department_id = $_GET['department_id'] ?? null;
            
            $query = "
                SELECT l.*, 
                       u.username as user_name,
                       u.email as user_email,
                       d.name as department_name,
                       p.name as position_name
                FROM leaves l
                LEFT JOIN users u ON l.user_id = u.user_id
                LEFT JOIN departments d ON u.department_id = d.id
                LEFT JOIN positions p ON u.position_id = p.id
                WHERE 1=1
            ";
            
            $params = [];
            $types = "";
            
            if ($user_id) {
                $query .= " AND l.user_id = ?";
                $params[] = $user_id;
                $types .= "i";
            }
            
            if ($type) {
                $query .= " AND l.type = ?";
                $params[] = $type;
                $types .= "s";
            }
            
            if ($status) {
                $query .= " AND l.status = ?";
                $params[] = $status;
                $types .= "s";
            }
            
            if ($start_date) {
                $query .= " AND l.start_date >= ?";
                $params[] = $start_date;
                $types .= "s";
            }
            
            if ($end_date) {
                $query .= " AND l.end_date <= ?";
                $params[] = $end_date;
                $types .= "s";
            }
            
            if ($department_id) {
                $query .= " AND u.department_id = ?";
                $params[] = $department_id;
                $types .= "i";
            }
            
            $query .= " ORDER BY l.created_at DESC";
            
            $stmt = $conn->prepare($query);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            
            $leaves = [];
            while ($row = $result->fetch_assoc()) {
                $leaves[] = $row;
            }
            sendResponse($leaves);
        }
        break;
        
    case 'POST':
        // Create new leave request
        $required_fields = ['user_id', 'type', 'start_date', 'end_date', 'reason', 'status'];
        foreach ($required_fields as $field) {
            if (!isset($input[$field])) {
                handleError("Missing required field: $field");
            }
        }
        
        $stmt = $conn->prepare("
            INSERT INTO leaves (
                user_id,
                type,
                start_date,
                end_date,
                reason,
                status,
                approved_by,
                approved_at,
                notes,
                created_at,
                updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        $stmt->bind_param(
            "isssssiss",
            $input['user_id'],
            $input['type'],
            $input['start_date'],
            $input['end_date'],
            $input['reason'],
            $input['status'],
            $input['approved_by'] ?? null,
            $input['approved_at'] ?? null,
            $input['notes'] ?? null
        );
        
        if ($stmt->execute()) {
            sendResponse(['id' => $conn->insert_id], 201);
        } else {
            handleError('Failed to create leave request');
        }
        break;
        
    case 'PUT':
        // Update leave request
        if (!$id) {
            handleError('Leave request ID is required');
        }
        
        $stmt = $conn->prepare("
            UPDATE leaves 
            SET user_id = ?,
                type = ?,
                start_date = ?,
                end_date = ?,
                reason = ?,
                status = ?,
                approved_by = ?,
                approved_at = ?,
                notes = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        $stmt->bind_param(
            "isssssissi",
            $input['user_id'],
            $input['type'],
            $input['start_date'],
            $input['end_date'],
            $input['reason'],
            $input['status'],
            $input['approved_by'] ?? null,
            $input['approved_at'] ?? null,
            $input['notes'] ?? null,
            $id
        );
        
        if ($stmt->execute()) {
            sendResponse(['message' => 'Leave request updated successfully']);
        } else {
            handleError('Failed to update leave request');
        }
        break;
        
    case 'DELETE':
        // Delete leave request
        if (!$id) {
            handleError('Leave request ID is required');
        }
        
        $stmt = $conn->prepare("DELETE FROM leaves WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            sendResponse(['message' => 'Leave request deleted successfully']);
        } else {
            handleError('Failed to delete leave request');
        }
        break;
        
    default:
        handleError('Method not allowed', 405);
} 