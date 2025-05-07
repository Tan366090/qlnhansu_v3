<?php
// Bật hiển thị lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting test...<br>";

try {
    echo "Loading Database.php...<br>";
    require_once __DIR__ . '/config/Database.php';
    echo "Database.php loaded successfully<br>";
    
    echo "Getting database instance...<br>";
    $db = Database::getInstance();
    echo "Database instance created<br>";
    
    echo "Getting connection...<br>";
    $conn = $db->getConnection();
    echo "Connection established<br>";
    
    echo "Testing query...<br>";
    $stmt = $conn->query("SELECT 1");
    $result = $stmt->fetch();
    
    if ($result) {
        echo "Query successful!<br>";
    } else {
        echo "Query failed!<br>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
    echo "Trace: " . $e->getTraceAsString();
} 