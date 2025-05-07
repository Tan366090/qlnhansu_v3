<?php
// Include database connection
require_once '../../config/database.php';

// Handle different HTTP methods
switch ($method) {
    case 'GET':
        if ($id) {
            // Get single training
            $stmt = $conn->prepare("
                SELECT t.*, 
                       u.name as organizer_name,
                       d.name as department_name
                FROM trainings t
                LEFT JOIN users u ON t.user_id = u.id
                LEFT JOIN departments d ON t.department_id = d.id
                WHERE t.training_id = ?
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $training = $result->fetch_assoc();
            
            if ($training) {
                sendResponse($training);
            } else {
                handleError('Training not found', 404);
            }
        } else {
            // Get training list with filters
            $status = $_GET['status'] ?? null;
            $start_date = $_GET['start_date'] ?? null;
            $end_date = $_GET['end_date'] ?? null;
            $department_id = $_GET['department_id'] ?? null;
            $user_id = $_GET['user_id'] ?? null;
            
            $query = "
                SELECT t.*, 
                       u.name as organizer_name,
                       d.name as department_name
                FROM trainings t
                LEFT JOIN users u ON t.user_id = u.id
                LEFT JOIN departments d ON t.department_id = d.id
                WHERE 1=1
            ";
            
            $params = [];
            $types = "";
            
            if ($status) {
                $query .= " AND t.status = ?";
                $params[] = $status;
                $types .= "s";
            }
            
            if ($start_date) {
                $query .= " AND t.start_date >= ?";
                $params[] = $start_date;
                $types .= "s";
            }
            
            if ($end_date) {
                $query .= " AND t.end_date <= ?";
                $params[] = $end_date;
                $types .= "s";
            }
            
            if ($department_id) {
                $query .= " AND t.department_id = ?";
                $params[] = $department_id;
                $types .= "i";
            }
            
            if ($user_id) {
                $query .= " AND t.user_id = ?";
                $params[] = $user_id;
                $types .= "i";
            }
            
            $query .= " ORDER BY t.created_at DESC";
            
            $stmt = $conn->prepare($query);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            
            $trainings = [];
            while ($row = $result->fetch_assoc()) {
                $trainings[] = $row;
            }
            sendResponse($trainings);
        }
        break;
        
    case 'POST':
        // Create new training
        $required_fields = ['title', 'description', 'start_date', 'end_date', 'department_id', 'user_id'];
        foreach ($required_fields as $field) {
            if (!isset($input[$field])) {
                handleError("Missing required field: $field");
            }
        }
        
        $stmt = $conn->prepare("
            INSERT INTO trainings (
                title,
                description,
                start_date,
                end_date,
                location,
                user_id,
                department_id,
                status,
                created_at,
                updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        $stmt->bind_param(
            "sssssiss",
            $input['title'],
            $input['description'],
            $input['start_date'],
            $input['end_date'],
            $input['location'] ?? '',
            $input['user_id'],
            $input['department_id'],
            $input['status'] ?? 'planned'
        );
        
        if ($stmt->execute()) {
            sendResponse(['training_id' => $conn->insert_id], 201);
        } else {
            handleError('Failed to create training');
        }
        break;
        
    case 'PUT':
        // Update training
        if (!$id) {
            handleError('Training ID is required');
        }
        
        $stmt = $conn->prepare("
            UPDATE trainings 
            SET title = ?,
                description = ?,
                start_date = ?,
                end_date = ?,
                location = ?,
                user_id = ?,
                department_id = ?,
                status = ?,
                updated_at = NOW()
            WHERE training_id = ?
        ");
        
        $stmt->bind_param(
            "sssssissi",
            $input['title'],
            $input['description'],
            $input['start_date'],
            $input['end_date'],
            $input['location'] ?? '',
            $input['user_id'],
            $input['department_id'],
            $input['status'] ?? 'planned',
            $id
        );
        
        if ($stmt->execute()) {
            sendResponse(['message' => 'Training updated successfully']);
        } else {
            handleError('Failed to update training');
        }
        break;
        
    case 'DELETE':
        // Delete training
        if (!$id) {
            handleError('Training ID is required');
        }
        
        $stmt = $conn->prepare("DELETE FROM trainings WHERE training_id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            sendResponse(['message' => 'Training deleted successfully']);
        } else {
            handleError('Failed to delete training');
        }
        break;
        
    default:
        handleError('Method not allowed', 405);
} 