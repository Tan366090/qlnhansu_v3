<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

try {
    require_once __DIR__ . '/../../../config/database.php';
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception('Database connection failed');
    }

    // Start transaction
    $db->beginTransaction();

    // 1. Get existing department and position
    $query = "SELECT department_id FROM departments LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $department = $stmt->fetch(PDO::FETCH_ASSOC);

    $query = "SELECT position_id FROM positions LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $position = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$department || !$position) {
        throw new Exception('Required department or position not found');
    }

    // 2. Add sample employees
    $employeeData = [
        ['John Doe', '2024-01-15'],
        ['Jane Smith', '2024-02-20'],
        ['Mike Johnson', '2024-03-10'],
        ['Sarah Williams', '2024-04-05'],
        ['David Brown', '2024-05-12'],
        ['Emily Davis', '2024-06-01']
    ];

    $employeeIds = [];
    foreach ($employeeData as $index => $data) {
        // First create a user
        $query = "INSERT INTO users (username, email, user_password, full_name, status, created_at, updated_at) 
                 VALUES (:username, :email, :user_password, :full_name, 'active', NOW(), NOW())";
        $stmt = $db->prepare($query);
        $username = strtolower(str_replace(' ', '', $data[0]));
        $email = $username . '@example.com';
        $password = password_hash('123456', PASSWORD_DEFAULT);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':user_password', $password);
        $stmt->bindParam(':full_name', $data[0]);
        $stmt->execute();
        $userId = $db->lastInsertId('user_id');

        // Then create employee
        $query = "INSERT INTO employees (id, full_name, department_id, position_id, hire_date, status, created_at, updated_at) 
                 VALUES (:id, :full_name, :department_id, :position_id, :hire_date, 'active', NOW(), NOW())";
        $stmt = $db->prepare($query);
        $employeeId = $index + 1; // Tạo ID tăng dần từ 1
        $stmt->bindParam(':id', $employeeId);
        $stmt->bindParam(':full_name', $data[0]);
        $stmt->bindParam(':department_id', $department['department_id']);
        $stmt->bindParam(':position_id', $position['position_id']);
        $stmt->bindParam(':hire_date', $data[1]);
        $stmt->execute();
        $employeeIds[] = $employeeId;

        // Add to employee_positions
        $query = "INSERT INTO employee_positions (employee_id, position_id, start_date, is_current, created_at, updated_at) 
                 VALUES (:employee_id, :position_id, :start_date, 1, NOW(), NOW())";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':employee_id', $employeeId);
        $stmt->bindParam(':position_id', $position['position_id']);
        $stmt->bindParam(':start_date', $data[1]);
        $stmt->execute();
    }

    // 3. Add sample sentiment data
    $sentimentData = [
        [0.8, '2024-04-01'],
        [0.6, '2024-04-05'],
        [0.3, '2024-04-10'],
        [0.9, '2024-04-15'],
        [0.5, '2024-04-20'],
        [0.7, '2024-04-25'],
        [0.4, '2024-04-30'],
        [0.2, '2024-05-05'],
        [0.8, '2024-05-10'],
        [0.6, '2024-05-15']
    ];

    foreach ($sentimentData as $data) {
        $employeeId = $employeeIds[array_rand($employeeIds)];
        $query = "INSERT INTO employee_sentiment (employee_id, sentiment_score, analysis_date, created_at, updated_at) 
                 VALUES (:employee_id, :sentiment_score, :analysis_date, NOW(), NOW())";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':employee_id', $employeeId);
        $stmt->bindParam(':sentiment_score', $data[0]);
        $stmt->bindParam(':analysis_date', $data[1]);
        $stmt->execute();
    }

    // Commit transaction
    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Sample data added successfully',
        'data' => [
            'department_id' => $department['department_id'],
            'position_id' => $position['position_id'],
            'employees_added' => count($employeeIds),
            'sentiment_records_added' => count($sentimentData)
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
        'message' => 'Error adding sample data',
        'error' => $e->getMessage()
    ]);
} 