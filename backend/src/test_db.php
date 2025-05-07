<?php
// Bật hiển thị lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Kiểm tra xem PDO đã được cài đặt chưa
if (!extension_loaded('pdo')) {
    die('PDO extension is not loaded');
}

if (!extension_loaded('pdo_mysql')) {
    die('PDO MySQL extension is not loaded');
}

require_once __DIR__ . '/config/database.php';

try {
    $conn = Database::getConnection();
    echo "Database connection successful!\n";
    
    // Test query
    $stmt = $conn->query("SELECT COUNT(*) as count FROM payroll");
    $result = $stmt->fetch();
    echo "Number of payroll records: " . $result['count'] . "\n";
    
} catch (Exception $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
} 