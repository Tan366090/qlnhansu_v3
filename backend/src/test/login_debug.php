<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON content type
header('Content-Type: application/json');

// Include required files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

try {
    // Test database connection
    $db = new Database();
    $conn = $db->getConnection();
    echo json_encode(['success' => true, 'message' => 'Database connection successful']);

    // Test admin user existence
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        // Create admin user if not exists
        $password = '123456';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("
            INSERT INTO users (username, password, role_id, created_at) 
            VALUES ('admin', :password, 1, CURRENT_TIMESTAMP)
        ");
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->execute();

        echo json_encode([
            'success' => true,
            'message' => 'Admin user created successfully',
            'username' => 'admin',
            'password' => '123456'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Admin user exists',
            'username' => $admin['username'],
            'role_id' => $admin['role_id']
        ]);
    }

    // Test session configuration
    session_start();
    $_SESSION['test'] = 'session_test';
    echo json_encode([
        'success' => true,
        'message' => 'Session test successful',
        'session_id' => session_id()
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?> 