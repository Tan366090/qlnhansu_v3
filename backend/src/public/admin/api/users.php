<?php
// Include database connection
require_once '../../config/database.php';

// Handle different HTTP methods
switch ($method) {
    case 'GET':
        if ($id) {
            // Get single user
            $stmt = $conn->prepare("
                SELECT u.*, 
                       r.name as role_name,
                       d.name as department_name,
                       p.name as position_name
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                LEFT JOIN departments d ON u.department_id = d.id
                LEFT JOIN positions p ON u.position_id = p.id
                WHERE u.user_id = ?
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            if ($user) {
                // Remove sensitive data
                unset($user['password_hash']);
                unset($user['password_salt']);
                unset($user['remember_token']);
                sendResponse($user);
            } else {
                handleError('User not found', 404);
            }
        } else {
            // Get users list with filters
            $role_id = $_GET['role_id'] ?? null;
            $department_id = $_GET['department_id'] ?? null;
            $status = $_GET['status'] ?? null;
            $is_active = $_GET['is_active'] ?? null;
            
            $query = "
                SELECT u.*, 
                       r.name as role_name,
                       d.name as department_name,
                       p.name as position_name
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                LEFT JOIN departments d ON u.department_id = d.id
                LEFT JOIN positions p ON u.position_id = p.id
                WHERE 1=1
            ";
            
            $params = [];
            $types = "";
            
            if ($role_id) {
                $query .= " AND u.role_id = ?";
                $params[] = $role_id;
                $types .= "i";
            }
            
            if ($department_id) {
                $query .= " AND u.department_id = ?";
                $params[] = $department_id;
                $types .= "i";
            }
            
            if ($status) {
                $query .= " AND u.status = ?";
                $params[] = $status;
                $types .= "s";
            }
            
            if ($is_active !== null) {
                $query .= " AND u.is_active = ?";
                $params[] = $is_active;
                $types .= "i";
            }
            
            $query .= " ORDER BY u.created_at DESC";
            
            $stmt = $conn->prepare($query);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            
            $users = [];
            while ($row = $result->fetch_assoc()) {
                // Remove sensitive data
                unset($row['password_hash']);
                unset($row['password_salt']);
                unset($row['remember_token']);
                $users[] = $row;
            }
            sendResponse($users);
        }
        break;
        
    case 'POST':
        // Create new user
        $required_fields = ['username', 'email', 'password_hash', 'role_id'];
        foreach ($required_fields as $field) {
            if (!isset($input[$field])) {
                handleError("Missing required field: $field");
            }
        }
        
        $stmt = $conn->prepare("
            INSERT INTO users (
                username,
                email,
                password_hash,
                password_salt,
                role_id,
                is_active,
                requires_password_change,
                department_id,
                position_id,
                hire_date,
                status,
                employee_code,
                contract_type,
                contract_start_date,
                contract_end_date,
                created_at,
                updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        $stmt->bind_param(
            "ssssiiiiissssss",
            $input['username'],
            $input['email'],
            $input['password_hash'],
            $input['password_salt'] ?? null,
            $input['role_id'],
            $input['is_active'] ?? 1,
            $input['requires_password_change'] ?? 0,
            $input['department_id'] ?? null,
            $input['position_id'] ?? null,
            $input['hire_date'] ?? null,
            $input['status'] ?? 'active',
            $input['employee_code'] ?? null,
            $input['contract_type'] ?? null,
            $input['contract_start_date'] ?? null,
            $input['contract_end_date'] ?? null
        );
        
        if ($stmt->execute()) {
            sendResponse(['user_id' => $conn->insert_id], 201);
        } else {
            handleError('Failed to create user');
        }
        break;
        
    case 'PUT':
        // Update user
        if (!$id) {
            handleError('User ID is required');
        }
        
        $stmt = $conn->prepare("
            UPDATE users 
            SET username = ?,
                email = ?,
                role_id = ?,
                is_active = ?,
                requires_password_change = ?,
                department_id = ?,
                position_id = ?,
                hire_date = ?,
                status = ?,
                employee_code = ?,
                contract_type = ?,
                contract_start_date = ?,
                contract_end_date = ?,
                updated_at = NOW()
            WHERE user_id = ?
        ");
        
        $stmt->bind_param(
            "ssiiiiissssssi",
            $input['username'],
            $input['email'],
            $input['role_id'],
            $input['is_active'],
            $input['requires_password_change'],
            $input['department_id'] ?? null,
            $input['position_id'] ?? null,
            $input['hire_date'] ?? null,
            $input['status'],
            $input['employee_code'] ?? null,
            $input['contract_type'] ?? null,
            $input['contract_start_date'] ?? null,
            $input['contract_end_date'] ?? null,
            $id
        );
        
        if ($stmt->execute()) {
            sendResponse(['message' => 'User updated successfully']);
        } else {
            handleError('Failed to update user');
        }
        break;
        
    case 'DELETE':
        // Delete user
        if (!$id) {
            handleError('User ID is required');
        }
        
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            sendResponse(['message' => 'User deleted successfully']);
        } else {
            handleError('Failed to delete user');
        }
        break;
        
    default:
        handleError('Method not allowed', 405);
} 