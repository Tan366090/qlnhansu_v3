<?php
require_once __DIR__ . '/../config/database.php';

try {
    $config = require __DIR__ . '/../config/database.php';
    
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $db = new PDO($dsn, $config['username'], $config['password'], $config['options']);
    
    // Cập nhật cấu trúc bảng employees
    $sql = "ALTER TABLE employees MODIFY COLUMN name VARCHAR(100) NULL";
    $db->exec($sql);
    
    echo "Cập nhật cấu trúc bảng employees thành công!\n";
    
} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage() . "\n";
} 