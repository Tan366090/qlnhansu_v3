<?php
// Bật hiển thị lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log function
function logError($message) {
    error_log("[Update Passwords Debug] " . $message);
}

try {
    logError("Starting password update process for all users");
    
    require_once __DIR__ . '/config/Database.php';
    logError("Database.php loaded successfully");
    
    $db = Database::getInstance();
    $conn = $db->getConnection();
    logError("Database connection established");
    
    // Lấy danh sách tất cả người dùng
    $query = "SELECT user_id, username FROM users";
    $stmt = $conn->query($query);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    logError("Found " . count($users) . " users to update");
    
    // Mật khẩu mặc định cho tất cả người dùng
    $defaultPassword = '123456';
    
    // Cập nhật mật khẩu cho từng người dùng
    $updateQuery = "UPDATE users 
                   SET password_hash = :password,
                       password_salt = :salt
                   WHERE user_id = :user_id";
    
    $updateStmt = $conn->prepare($updateQuery);
    $totalUpdated = 0;
    
    foreach ($users as $user) {
        $salt = bin2hex(random_bytes(32));
        $hashedPassword = hash('sha256', $defaultPassword . $salt);
        
        $updateStmt->bindParam(':password', $hashedPassword);
        $updateStmt->bindParam(':salt', $salt);
        $updateStmt->bindParam(':user_id', $user['user_id']);
        
        if ($updateStmt->execute()) {
            $totalUpdated++;
            echo "Đã cập nhật mật khẩu cho user: " . $user['username'] . "<br>";
        } else {
            echo "Lỗi khi cập nhật mật khẩu cho user: " . $user['username'] . "<br>";
        }
    }
    
    echo "<br>Tổng số người dùng đã được cập nhật: " . $totalUpdated . "<br>";
    echo "Mật khẩu mặc định cho tất cả người dùng là: 123456<br>";
    echo "Vui lòng thông báo cho người dùng đổi mật khẩu sau khi đăng nhập!<br>";
    
} catch (Exception $e) {
    logError("Exception: " . $e->getMessage());
    echo "Lỗi: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
    echo "Trace: " . $e->getTraceAsString();
} 