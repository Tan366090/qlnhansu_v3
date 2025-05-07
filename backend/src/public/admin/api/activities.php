<?php
// Include database connection
require_once '../../config/database.php';

// Handle different HTTP methods
switch ($method) {
    case 'GET':
        if ($id) {
            // Get single activity
            $stmt = $conn->prepare("
                SELECT a.*, 
                       u.username as user_name,
                       u.email as user_email
                FROM activities a
                LEFT JOIN users u ON a.user_id = u.user_id
                WHERE a.id = ?
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $activity = $result->fetch_assoc();
            
            if ($activity) {
                sendResponse($activity);
            } else {
                handleError('Activity not found', 404);
            }
        } else {
            // Get activities list with filters
            $user_id = $_GET['user_id'] ?? null;
            $type = $_GET['type'] ?? null;
            $status = $_GET['status'] ?? null;
            $start_date = $_GET['start_date'] ?? null;
            $end_date = $_GET['end_date'] ?? null;
            
            $query = "
                SELECT a.*, 
                       u.username as user_name,
                       u.email as user_email
                FROM activities a
                LEFT JOIN users u ON a.user_id = u.user_id
                WHERE 1=1
            ";
            
            $params = [];
            $types = "";
            
            if ($user_id) {
                $query .= " AND a.user_id = ?";
                $params[] = $user_id;
                $types .= "i";
            }
            
            if ($type) {
                $query .= " AND a.type = ?";
                $params[] = $type;
                $types .= "s";
            }
            
            if ($status) {
                $query .= " AND a.status = ?";
                $params[] = $status;
                $types .= "s";
            }
            
            if ($start_date) {
                $query .= " AND a.created_at >= ?";
                $params[] = $start_date;
                $types .= "s";
            }
            
            if ($end_date) {
                $query .= " AND a.created_at <= ?";
                $params[] = $end_date;
                $types .= "s";
            }
            
            $query .= " ORDER BY a.created_at DESC";
            
            $stmt = $conn->prepare($query);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            
            $activities = [];
            while ($row = $result->fetch_assoc()) {
                $activities[] = $row;
            }
            sendResponse($activities);
        }
        break;
        
    case 'POST':
        // Create new activity
        $required_fields = ['user_id', 'type', 'description', 'status'];
        foreach ($required_fields as $field) {
            if (!isset($input[$field])) {
                handleError("Missing required field: $field");
            }
        }
        
        $stmt = $conn->prepare("
            INSERT INTO activities (
                user_id,
                type,
                description,
                user_agent,
                ip_address,
                status,
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->bind_param(
            "isssss",
            $input['user_id'],
            $input['type'],
            $input['description'],
            $input['user_agent'] ?? null,
            $input['ip_address'] ?? null,
            $input['status']
        );
        
        if ($stmt->execute()) {
            sendResponse(['id' => $conn->insert_id], 201);
        } else {
            handleError('Failed to create activity');
        }
        break;
        
    case 'PUT':
        // Update activity
        if (!$id) {
            handleError('Activity ID is required');
        }
        
        $stmt = $conn->prepare("
            UPDATE activities 
            SET user_id = ?,
                type = ?,
                description = ?,
                user_agent = ?,
                ip_address = ?,
                status = ?
            WHERE id = ?
        ");
        
        $stmt->bind_param(
            "isssssi",
            $input['user_id'],
            $input['type'],
            $input['description'],
            $input['user_agent'] ?? null,
            $input['ip_address'] ?? null,
            $input['status'],
            $id
        );
        
        if ($stmt->execute()) {
            sendResponse(['message' => 'Activity updated successfully']);
        } else {
            handleError('Failed to update activity');
        }
        break;
        
    case 'DELETE':
        // Delete activity
        if (!$id) {
            handleError('Activity ID is required');
        }
        
        $stmt = $conn->prepare("DELETE FROM activities WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            sendResponse(['message' => 'Activity deleted successfully']);
        } else {
            handleError('Failed to delete activity');
        }
        break;
        
    default:
        handleError('Method not allowed', 405);
} 