<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Set CORS headers
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Set content type to JSON
header('Content-Type: application/json');

// Include required files
require_once __DIR__ . '/../../config/database.php';

try {
    // Get database instance
    $database = Database::getInstance();
    $db = $database->getConnection();

    // Check if admin user exists
    $query = "SELECT * FROM users WHERE username = 'admin'";
    $stmt = $db->query($query);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        // Create admin user if not exists
        $password = '123456';
        $hashedPassword = md5($password);
        
        $query = "INSERT INTO users (username, password_hash, email, role_name, created_at) 
                 VALUES ('admin', :password, 'admin@example.com', 'admin', CURRENT_TIMESTAMP)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->execute();

        echo json_encode([
            'success' => true,
            'message' => 'Admin user created successfully',
            'username' => 'admin',
            'password' => '123456'
        ]);
    } else {
        // Update admin password if exists
        $password = '123456';
        $hashedPassword = md5($password);
        
        $query = "UPDATE users SET password_hash = :password WHERE username = 'admin'";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->execute();

        echo json_encode([
            'success' => true,
            'message' => 'Admin password updated successfully',
            'username' => 'admin',
            'password' => '123456'
        ]);
    }

} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 