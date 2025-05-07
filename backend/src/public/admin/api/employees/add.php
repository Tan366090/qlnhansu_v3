<?php
header('Content-Type: application/json');
require_once '../../../config/database.php';
require_once '../../../config/mail.php';
require_once '../../../includes/functions.php';

// Kiểm tra quyền truy cập
session_start();
if (!isset($_SESSION['user_id']) || !hasPermission('employees', 'create')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

try {
    // Get database connection
    $db = Database::getConnection();
    
    // Start transaction
    $db->beginTransaction();
    
    // Get form data
    $data = $_POST;
    
    // Validate required fields
    $requiredFields = ['first_name', 'last_name', 'email', 'phone', 'department_id', 'position_id', 'hire_date', 'salary'];
    $errors = [];
    
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            $errors[] = "Trường $field là bắt buộc";
        }
    }
    
    // Validate email format
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ";
    }
    
    // Check if email already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) {
        $errors[] = "Email đã tồn tại";
    }
    
    // Validate phone format
    if (!preg_match('/^\d{10,11}$/', $data['phone'])) {
        $errors[] = "Số điện thoại phải có 10-11 chữ số";
    }
    
    // Validate salary
    if (!is_numeric($data['salary']) || $data['salary'] <= 0) {
        $errors[] = "Lương phải là số dương";
    }
    
    // Handle avatar upload
    $avatarPath = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['avatar']['type'], $allowedTypes)) {
            $errors[] = "Chỉ chấp nhận file ảnh JPG, PNG hoặc GIF";
        } elseif ($_FILES['avatar']['size'] > $maxSize) {
            $errors[] = "File ảnh không được vượt quá 5MB";
        } else {
            $uploadDir = '../../../uploads/avatars/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $extension;
            $avatarPath = 'uploads/avatars/' . $filename;
            
            if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . $filename)) {
                $errors[] = "Không thể tải lên ảnh đại diện";
            }
        }
    }
    
    // If there are errors, return them
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        exit;
    }
    
    // Generate username from full name
    $username = strtolower($data['first_name'] . $data['last_name']);
    $username = preg_replace('/[^a-z0-9]/', '', $username);
    
    // Check if username exists and append number if needed
    $baseUsername = $username;
    $counter = 1;
    while (true) {
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if (!$stmt->fetch()) {
            break;
        }
        $username = $baseUsername . $counter;
        $counter++;
    }
    
    // Generate random password
    $password = generateRandomPassword();
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // Create user account
    $stmt = $db->prepare("INSERT INTO users (username, email, password_hash, role_id, created_at) VALUES (?, ?, ?, 3, NOW())");
    $stmt->execute([$username, $data['email'], $passwordHash]);
    $userId = $db->lastInsertId();
    
    // Generate employee code
    $employeeCode = 'EMP' . date('YmdHis');
    
    // Create employee record
    $stmt = $db->prepare("INSERT INTO employees (user_id, employee_code, department_id, position_id, hire_date, status, avatar, created_at) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([
        $userId,
        $employeeCode,
        $data['department_id'],
        $data['position_id'],
        $data['hire_date'],
        $data['status'] ?? 'active',
        $avatarPath
    ]);
    $employeeId = $db->lastInsertId();
    
    // Create user profile
    $stmt = $db->prepare("INSERT INTO user_profiles (user_id, full_name, phone_number, permanent_address, created_at) 
                         VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([
        $userId,
        $data['first_name'] . ' ' . $data['last_name'],
        $data['phone'],
        $data['address'] ?? ''
    ]);
    
    // Create salary history
    $stmt = $db->prepare("INSERT INTO salary_history (employee_id, effective_date, new_salary, reason, created_at) 
                         VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([
        $employeeId,
        $data['hire_date'],
        $data['salary'],
        'Lương khởi điểm'
    ]);
    
    // Commit transaction
    $db->commit();
    
    // Send welcome email
    $mailConfig = new MailConfig();
    $mailConfig->sendWelcomeEmail($data['email'], $username, $password);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Thêm nhân viên thành công',
        'data' => [
            'employee_id' => $employeeId,
            'user_id' => $userId,
            'employee_code' => $employeeCode
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($db)) {
        $db->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
    ]);
}

// Hàm tạo mật khẩu ngẫu nhiên
function generateRandomPassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $password;
}
?> 