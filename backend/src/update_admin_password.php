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
    $newPassword = 'admin123';
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    
    // Cập nhật mật khẩu admin
    $query = "UPDATE users SET password_hash = :password_hash, password_salt = '' WHERE username = 'admin'";
    $stmt = $conn->prepare($query);
    $stmt->execute(['password_hash' => $hashedPassword]);
    
    if ($stmt->rowCount() > 0) {
        echo "Cập nhật mật khẩu admin thành công!<br>";
        echo "Mật khẩu mới: " . $newPassword . "<br>";
        echo "Hash mới: " . $hashedPassword . "<br>";
    } else {
        echo "Không tìm thấy tài khoản admin để cập nhật!<br>";
    }
    
} catch (Exception $e) {
    logError("Exception: " . $e->getMessage());
    echo "Lỗi: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
    echo "Trace: " . $e->getTraceAsString();
} 