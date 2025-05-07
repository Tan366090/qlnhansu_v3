<?php
// Include database connection
require_once '../../config/database.php';

// Handle different HTTP methods
switch ($method) {
    case 'GET':
        if ($id) {
            // Get single attendance record
            $stmt = $conn->prepare("
                SELECT a.*, 
                       u.username as user_name,
                       u.email as user_email,
                       d.name as department_name,
                       p.name as position_name
                FROM attendance a
                LEFT JOIN users u ON a.user_id = u.user_id
                LEFT JOIN departments d ON u.department_id = d.id
                LEFT JOIN positions p ON u.position_id = p.id
                WHERE a.attendance_id = ?
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $attendance = $result->fetch_assoc();
            
            if ($attendance) {
                sendResponse($attendance);
            } else {
                handleError('Attendance record not found', 404);
            }
        } else {
            // Get attendance records with filters
            $user_id = $_GET['user_id'] ?? null;
            $department_id = $_GET['department_id'] ?? null;
            $start_date = $_GET['start_date'] ?? null;
            $end_date = $_GET['end_date'] ?? null;
            $attendance_symbol = $_GET['attendance_symbol'] ?? null;
            
            $query = "
                SELECT a.*, 
                       u.username as user_name,
                       u.email as user_email,
                       d.name as department_name,
                       p.name as position_name
                FROM attendance a
                LEFT JOIN users u ON a.user_id = u.user_id
                LEFT JOIN departments d ON u.department_id = d.id
                LEFT JOIN positions p ON u.position_id = p.id
                WHERE 1=1
            ";
            
            $params = [];
            $types = "";
            
            if ($user_id) {
                $query .= " AND a.user_id = ?";
                $params[] = $user_id;
                $types .= "i";
            }
            
            if ($department_id) {
                $query .= " AND u.department_id = ?";
                $params[] = $department_id;
                $types .= "i";
            }
            
            if ($start_date) {
                $query .= " AND a.attendance_date >= ?";
                $params[] = $start_date;
                $types .= "s";
            }
            
            if ($end_date) {
                $query .= " AND a.attendance_date <= ?";
                $params[] = $end_date;
                $types .= "s";
            }
            
            if ($attendance_symbol) {
                $query .= " AND a.attendance_symbol = ?";
                $params[] = $attendance_symbol;
                $types .= "s";
            }
            
            $query .= " ORDER BY a.attendance_date DESC, a.recorded_at DESC";
            
            $stmt = $conn->prepare($query);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            
            $attendance_records = [];
            while ($row = $result->fetch_assoc()) {
                $attendance_records[] = $row;
            }
            sendResponse($attendance_records);
        }
        break;
        
    case 'POST':
        // Create new attendance record
        $required_fields = ['user_id', 'attendance_date', 'attendance_symbol'];
        foreach ($required_fields as $field) {
            if (!isset($input[$field])) {
                handleError("Missing required field: $field");
            }
        }
        
        $stmt = $conn->prepare("
            INSERT INTO attendance (
                user_id,
                attendance_date,
                recorded_at,
                notes,
                attendance_symbol,
                created_at
            ) VALUES (?, ?, NOW(), ?, ?, NOW())
        ");
        
        $stmt->bind_param(
            "isss",
            $input['user_id'],
            $input['attendance_date'],
            $input['notes'] ?? null,
            $input['attendance_symbol']
        );
        
        if ($stmt->execute()) {
            sendResponse(['attendance_id' => $conn->insert_id], 201);
        } else {
            handleError('Failed to create attendance record');
        }
        break;
        
    case 'PUT':
        // Update attendance record
        if (!$id) {
            handleError('Attendance record ID is required');
        }
        
        $stmt = $conn->prepare("
            UPDATE attendance 
            SET user_id = ?,
                attendance_date = ?,
                notes = ?,
                attendance_symbol = ?
            WHERE attendance_id = ?
        ");
        
        $stmt->bind_param(
            "isssi",
            $input['user_id'],
            $input['attendance_date'],
            $input['notes'] ?? null,
            $input['attendance_symbol'],
            $id
        );
        
        if ($stmt->execute()) {
            sendResponse(['message' => 'Attendance record updated successfully']);
        } else {
            handleError('Failed to update attendance record');
        }
        break;
        
    case 'DELETE':
        // Delete attendance record
        if (!$id) {
            handleError('Attendance record ID is required');
        }
        
        $stmt = $conn->prepare("DELETE FROM attendance WHERE attendance_id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            sendResponse(['message' => 'Attendance record deleted successfully']);
        } else {
            handleError('Failed to delete attendance record');
        }
        break;
        
    default:
        handleError('Method not allowed', 405);
} 