<?php
// Include database connection
require_once '../../config/database.php';

// Handle different HTTP methods
switch ($method) {
    case 'GET':
        if ($id) {
            // Get single salary record
            $stmt = $conn->prepare("
                SELECT s.*, 
                       u.username as user_name,
                       u.email as user_email,
                       d.name as department_name,
                       p.name as position_name
                FROM salaries s
                LEFT JOIN users u ON s.user_id = u.user_id
                LEFT JOIN departments d ON u.department_id = d.id
                LEFT JOIN positions p ON u.position_id = p.id
                WHERE s.id = ?
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $salary = $result->fetch_assoc();
            
            if ($salary) {
                sendResponse($salary);
            } else {
                handleError('Salary record not found', 404);
            }
        } else {
            // Get salaries list with filters
            $user_id = $_GET['user_id'] ?? null;
            $month = $_GET['month'] ?? null;
            $year = $_GET['year'] ?? null;
            $department_id = $_GET['department_id'] ?? null;
            $status = $_GET['status'] ?? null;
            
            $query = "
                SELECT s.*, 
                       u.username as user_name,
                       u.email as user_email,
                       d.name as department_name,
                       p.name as position_name
                FROM salaries s
                LEFT JOIN users u ON s.user_id = u.user_id
                LEFT JOIN departments d ON u.department_id = d.id
                LEFT JOIN positions p ON u.position_id = p.id
                WHERE 1=1
            ";
            
            $params = [];
            $types = "";
            
            if ($user_id) {
                $query .= " AND s.user_id = ?";
                $params[] = $user_id;
                $types .= "i";
            }
            
            if ($month) {
                $query .= " AND MONTH(s.payment_date) = ?";
                $params[] = $month;
                $types .= "i";
            }
            
            if ($year) {
                $query .= " AND YEAR(s.payment_date) = ?";
                $params[] = $year;
                $types .= "i";
            }
            
            if ($department_id) {
                $query .= " AND u.department_id = ?";
                $params[] = $department_id;
                $types .= "i";
            }
            
            if ($status) {
                $query .= " AND s.status = ?";
                $params[] = $status;
                $types .= "s";
            }
            
            $query .= " ORDER BY s.payment_date DESC";
            
            $stmt = $conn->prepare($query);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            
            $salaries = [];
            while ($row = $result->fetch_assoc()) {
                $salaries[] = $row;
            }
            sendResponse($salaries);
        }
        break;
        
    case 'POST':
        // Create new salary record
        $required_fields = ['user_id', 'basic_salary', 'allowances', 'deductions', 'payment_date', 'status'];
        foreach ($required_fields as $field) {
            if (!isset($input[$field])) {
                handleError("Missing required field: $field");
            }
        }
        
        $stmt = $conn->prepare("
            INSERT INTO salaries (
                user_id,
                basic_salary,
                allowances,
                deductions,
                net_salary,
                payment_date,
                status,
                notes,
                created_at,
                updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        // Calculate net salary
        $net_salary = $input['basic_salary'] + $input['allowances'] - $input['deductions'];
        
        $stmt->bind_param(
            "idddsss",
            $input['user_id'],
            $input['basic_salary'],
            $input['allowances'],
            $input['deductions'],
            $net_salary,
            $input['payment_date'],
            $input['status'],
            $input['notes'] ?? null
        );
        
        if ($stmt->execute()) {
            sendResponse(['id' => $conn->insert_id], 201);
        } else {
            handleError('Failed to create salary record');
        }
        break;
        
    case 'PUT':
        // Update salary record
        if (!$id) {
            handleError('Salary record ID is required');
        }
        
        $stmt = $conn->prepare("
            UPDATE salaries 
            SET user_id = ?,
                basic_salary = ?,
                allowances = ?,
                deductions = ?,
                net_salary = ?,
                payment_date = ?,
                status = ?,
                notes = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        // Calculate net salary
        $net_salary = $input['basic_salary'] + $input['allowances'] - $input['deductions'];
        
        $stmt->bind_param(
            "idddsssi",
            $input['user_id'],
            $input['basic_salary'],
            $input['allowances'],
            $input['deductions'],
            $net_salary,
            $input['payment_date'],
            $input['status'],
            $input['notes'] ?? null,
            $id
        );
        
        if ($stmt->execute()) {
            sendResponse(['message' => 'Salary record updated successfully']);
        } else {
            handleError('Failed to update salary record');
        }
        break;
        
    case 'DELETE':
        // Delete salary record
        if (!$id) {
            handleError('Salary record ID is required');
        }
        
        $stmt = $conn->prepare("DELETE FROM salaries WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            sendResponse(['message' => 'Salary record deleted successfully']);
        } else {
            handleError('Failed to delete salary record');
        }
        break;
        
    default:
        handleError('Method not allowed', 405);
} 