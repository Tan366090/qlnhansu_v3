<?php
require_once 'config.php';

try {
    // Create users table
    $conn->exec("CREATE TABLE IF NOT EXISTS users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL UNIQUE,
        email VARCHAR(255),
        password_hash VARCHAR(32) NOT NULL,
        role_id INT NOT NULL,
        department_id INT,
        position_id INT,
        status VARCHAR(20) DEFAULT 'active',
        INDEX idx_username (username)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Create departments table
    $conn->exec("CREATE TABLE IF NOT EXISTS departments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Create positions table
    $conn->exec("CREATE TABLE IF NOT EXISTS positions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Create login_attempts table
    $conn->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL,
        username VARCHAR(255) NOT NULL,
        attempt_time DATETIME NOT NULL,
        INDEX idx_ip_time (ip_address, attempt_time)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Insert test data
    // Departments
    $conn->exec("INSERT IGNORE INTO departments (id, name) VALUES 
        (1, 'Human Resources'),
        (2, 'IT'),
        (3, 'Finance')");
    
    // Positions
    $conn->exec("INSERT IGNORE INTO positions (id, name) VALUES 
        (1, 'Manager'),
        (2, 'Developer'),
        (3, 'HR Specialist')");
    
    // Users (password is '123456' with MD5 hash)
    $password_hash = md5('123456');
    $stmt = $conn->prepare("INSERT IGNORE INTO users 
        (username, email, password_hash, role_id, department_id, position_id) VALUES 
        ('admin', 'admin@example.com', :password, 1, 1, 1),
        ('john.doe', 'john.doe@example.com', :password, 2, 2, 2),
        ('jane.smith', 'jane.smith@example.com', :password, 2, 3, 3)");
    $stmt->bindParam(':password', $password_hash);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Database initialized successfully'
    ]);
} catch (PDOException $e) {
    error_log("Database initialization error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database initialization failed: ' . $e->getMessage()
    ]);
} 