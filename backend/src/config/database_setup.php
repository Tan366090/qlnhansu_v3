<?php
// Database configuration
$host = 'localhost';
$dbname = 'qlnhansu';
$username = 'root';
$password = '';

try {
    // Create connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Disable foreign key checks temporarily
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        
        // Drop existing tables if they exist (in correct order)
        $pdo->exec("DROP TABLE IF EXISTS attendance");
        $pdo->exec("DROP TABLE IF EXISTS activities");
        $pdo->exec("DROP TABLE IF EXISTS users");
        
        // Re-enable foreign key checks
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        
        // 0. Create Users table
        $pdo->exec("CREATE TABLE users (
            user_id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) NOT NULL UNIQUE,
            user_password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        // 0. Create Activities table
        $pdo->exec("CREATE TABLE activities (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            type VARCHAR(50) NOT NULL,
            description TEXT,
            user_agent TEXT,
            ip_address VARCHAR(45),
            status VARCHAR(20) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(user_id)
        )");
        
        // 0. Create Attendance table
        $pdo->exec("CREATE TABLE attendance (
            attendance_id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            attendance_date DATE NOT NULL,
            recorded_at TIMESTAMP NOT NULL,
            notes TEXT,
            attendance_symbol VARCHAR(10) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(user_id)
        )");
        
        // Insert sample data
        // 0. Insert default users first
        $stmt = $pdo->prepare("INSERT INTO users (username, user_password, full_name, email) VALUES (?, ?, ?, ?)");
        $users = [
            ['admin', password_hash('admin123', PASSWORD_DEFAULT), 'Administrator', 'admin@example.com'],
            ['user1', password_hash('user123', PASSWORD_DEFAULT), 'User One', 'user1@example.com'],
            ['user2', password_hash('user123', PASSWORD_DEFAULT), 'User Two', 'user2@example.com'],
            ['user3', password_hash('user123', PASSWORD_DEFAULT), 'User Three', 'user3@example.com'],
            ['user4', password_hash('user123', PASSWORD_DEFAULT), 'User Four', 'user4@example.com'],
            ['user5', password_hash('user123', PASSWORD_DEFAULT), 'User Five', 'user5@example.com'],
            ['user6', password_hash('user123', PASSWORD_DEFAULT), 'User Six', 'user6@example.com'],
            ['user7', password_hash('user123', PASSWORD_DEFAULT), 'User Seven', 'user7@example.com'],
            ['user8', password_hash('user123', PASSWORD_DEFAULT), 'User Eight', 'user8@example.com'],
            ['user9', password_hash('user123', PASSWORD_DEFAULT), 'User Nine', 'user9@example.com']
        ];
        foreach ($users as $user) {
            $stmt->execute($user);
        }
        
        // 0. Insert sample activities
        $stmt = $pdo->prepare("INSERT INTO activities (user_id, type, description, user_agent, ip_address, status) VALUES (?, ?, ?, ?, ?, ?)");
        $activities = [
            [1, 'login', 'User logged in', null, null, 'success'],
            [1, 'login', 'Admin logged into the system', 'Mozilla/5.0 (Windows NT 10.0)', '192.168.1.100', 'success'],
            [2, 'update_profile', 'Updated personal information', 'Mozilla/5.0 (Windows NT 10.0)', '192.168.1.101', 'success'],
            [3, 'view_document', 'Accessed employee handbook', 'Mozilla/5.0 (Windows NT 10.0)', '192.168.1.102', 'success'],
            [1, 'approve_leave', 'Approved leave request for employee ID 2', 'Mozilla/5.0 (Windows NT 10.0)', '192.168.1.100', 'success']
        ];
        foreach ($activities as $activity) {
            $stmt->execute($activity);
        }
        
        // 0. Insert sample attendance
        $stmt = $pdo->prepare("INSERT INTO attendance (user_id, attendance_date, recorded_at, notes, attendance_symbol) VALUES (?, ?, ?, ?, ?)");
        $attendance = [
            [5, '2024-03-01', '2024-03-01 08:15:00', 'On time', 'P'],
            [6, '2024-03-01', '2024-03-01 08:00:00', 'On time', 'P'],
            [7, '2024-03-01', '2024-03-01 08:20:00', 'On time', 'P'],
            [8, '2024-03-01', '2024-03-01 08:00:00', 'On time', 'P'],
            [9, '2024-03-01', '2024-03-01 08:25:00', 'On time', 'P']
        ];
        foreach ($attendance as $record) {
            $stmt->execute($record);
        }
        
        // Commit transaction
        $pdo->commit();
        echo "Database setup completed successfully!";
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        throw $e;
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 