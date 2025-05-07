<?php
require_once __DIR__ . '/../config/Database.php';

try {
    echo "Đang kết nối đến database...\n";
    $database = Database::getInstance();
    $conn = $database->getConnection();
    echo "Kết nối thành công!\n";

    // Lấy tất cả người dùng
    echo "Đang tìm người dùng...\n";
    $query = "SELECT user_id, username FROM users";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Tìm thấy " . count($users) . " người dùng trong database\n";

    // Tạo MD5 hash của 123456
    $md5Password = md5('123456');
    echo "MD5 hash của '123456' là: " . $md5Password . "\n";

    $updated = 0;
    foreach ($users as $user) {
        echo "Đang cập nhật mật khẩu cho user_id: " . $user['user_id'] . " (username: " . $user['username'] . ")\n";
        
        $updateQuery = "UPDATE users SET password_hash = :password WHERE user_id = :id";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bindParam(':password', $md5Password);
        $updateStmt->bindParam(':id', $user['user_id']);
        $updateStmt->execute();
        $updated++;
    }

    echo "Hoàn tất! Đã cập nhật $updated mật khẩu sang MD5\n";
    
    // Kiểm tra lại
    $query = "SELECT user_id, username, password_hash FROM users";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nThông tin mật khẩu sau khi cập nhật:\n";
    foreach ($users as $user) {
        echo "User ID: " . $user['user_id'] . "\n";
        echo "Username: " . $user['username'] . "\n";
        echo "Password hash: " . $user['password_hash'] . "\n";
        echo "-------------------\n";
    }
    
} catch (PDOException $e) {
    echo "Lỗi database: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} catch (Exception $e) {
    echo "Lỗi khác: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?> 