<?php
// Bật hiển thị lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log function
function logError($message) {
    error_log("[Update Admin Password Debug] " . $message);
}

try {
    logError("Starting admin password update");
    
    require_once __DIR__ . '/config/Database.php';
    logError("Database.php loaded successfully");
    
    $db = Database::getInstance();
    $conn = $db->getConnection();
    logError("Database connection established");
    
    // Mật khẩu mới
    $password = 'admin123';
    $salt = bin2hex(random_bytes(32));
    $hashedPassword = hash('sha256', $password . $salt);
    
    logError("Generated new password hash and salt");
    logError("Password: " . $password);
    logError("Salt: " . $salt);
    logError("Hashed Password: " . $hashedPassword);
    
    // Cập nhật mật khẩu cho admin
    $query = "UPDATE users 
              SET password_hash = :password,
                  password_salt = :salt
              WHERE username = 'admin'";
    
    logError("Preparing query: " . $query);
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':salt', $salt);
    
    logError("Executing update query");
    
    if ($stmt->execute()) {
        $rowCount = $stmt->rowCount();
        logError("Update successful. Rows affected: " . $rowCount);
        
        echo "Đã cập nhật mật khẩu cho tài khoản admin thành công!<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
        echo "Salt: " . $salt . "<br>";
        echo "Hashed Password: " . $hashedPassword . "<br>";
        echo "Rows affected: " . $rowCount . "<br>";
        
        // Kiểm tra lại mật khẩu
        $checkQuery = "SELECT password_hash, password_salt FROM users WHERE username = 'admin'";
        $checkStmt = $conn->query($checkQuery);
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        $testHash = hash('sha256', $password . $result['password_salt']);
        echo "<br>Kiểm tra mật khẩu:<br>";
        echo "Hash trong database: " . $result['password_hash'] . "<br>";
        echo "Hash của mật khẩu thử: " . $testHash . "<br>";
        echo "So sánh: " . ($testHash === $result['password_hash'] ? "Khớp" : "Không khớp") . "<br>";
        
    } else {
        $error = $stmt->errorInfo();
        logError("Update failed. Error: " . print_r($error, true));
        echo "Có lỗi xảy ra khi cập nhật mật khẩu:<br>";
        echo "Error Code: " . $error[0] . "<br>";
        echo "Error Message: " . $error[2] . "<br>";
    }
    
} catch (Exception $e) {
    logError("Exception: " . $e->getMessage());
    echo "Lỗi: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
    echo "Trace: " . $e->getTraceAsString();
} 