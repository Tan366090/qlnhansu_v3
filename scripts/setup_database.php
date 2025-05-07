<?php
require_once __DIR__ . '/../backend/src/config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Đọc file SQL
    $sql = file_get_contents(__DIR__ . '/../database/schema.sql');

    // Thực thi các câu lệnh SQL
    $conn->exec($sql);

    echo "Database đã được tạo và cập nhật thành công!\n";
} catch (PDOException $e) {
    echo "Lỗi khi tạo database: " . $e->getMessage() . "\n";
} 