<?php
// Include database connection
require_once '../../config/database.php';

// Handle different HTTP methods
switch ($method) {
    case 'GET':
        if ($id) {
            // Get single employee
            $stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $employee = $result->fetch_assoc();
            
            if ($employee) {
                sendResponse($employee);
            } else {
                handleError('Employee not found', 404);
            }
        } else {
            // Get all employees
            $result = $conn->query("SELECT * FROM employees");
            $employees = [];
            while ($row = $result->fetch_assoc()) {
                $employees[] = $row;
            }
            sendResponse($employees);
        }
        break;
        
    case 'POST':
        // Create new employee
        $required_fields = ['name', 'email', 'department_id', 'position_id'];
        foreach ($required_fields as $field) {
            if (!isset($input[$field])) {
                handleError("Missing required field: $field");
            }
        }
        
        $stmt = $conn->prepare("INSERT INTO employees (name, email, department_id, position_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssii", $input['name'], $input['email'], $input['department_id'], $input['position_id']);
        
        if ($stmt->execute()) {
            sendResponse(['id' => $conn->insert_id], 201);
        } else {
            handleError('Failed to create employee');
        }
        break;
        
    case 'PUT':
        // Update employee
        if (!$id) {
            handleError('Employee ID is required');
        }
        
        $stmt = $conn->prepare("UPDATE employees SET name = ?, email = ?, department_id = ?, position_id = ? WHERE id = ?");
        $stmt->bind_param("ssiii", $input['name'], $input['email'], $input['department_id'], $input['position_id'], $id);
        
        if ($stmt->execute()) {
            sendResponse(['message' => 'Employee updated successfully']);
        } else {
            handleError('Failed to update employee');
        }
        break;
        
    case 'DELETE':
        // Delete employee
        if (!$id) {
            handleError('Employee ID is required');
        }
        
        $stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            sendResponse(['message' => 'Employee deleted successfully']);
        } else {
            handleError('Failed to delete employee');
        }
        break;
        
    default:
        handleError('Method not allowed', 405);
} 