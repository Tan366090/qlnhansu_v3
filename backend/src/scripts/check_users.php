<?php
require_once __DIR__ . '/../config/Database.php';

try {
    echo "Đang kết nối đến database...\n";
    $database = Database::getInstance();
    $conn = $database->getConnection();
    echo "Kết nối thành công!\n";

    // Lấy tất cả người dùng
    echo "Đang kiểm tra người dùng...\n";
    $query = "SELECT user_id, username, password_hash FROM users";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Tìm thấy " . count($users) . " người dùng trong database\n";
    
    if (count($users) > 0) {
        echo "\nThông tin chi tiết:\n";
        foreach ($users as $user) {
            echo "User ID: " . $user['user_id'] . "\n";
            echo "Username: " . $user['username'] . "\n";
            echo "Password hash length: " . strlen($user['password_hash']) . "\n";
            echo "Password hash: " . $user['password_hash'] . "\n";
            echo "-------------------\n";
        }
    }

} catch (PDOException $e) {
    echo "Lỗi database: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} catch (Exception $e) {
    echo "Lỗi khác: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?> 