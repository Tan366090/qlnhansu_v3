<?php
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'qlnhansu';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get list of tables
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tables in database:\n";
    print_r($tables);
    echo "\n";

    // Get structure of each table
    foreach ($tables as $table) {
        echo "\nStructure of $table:\n";
        $stmt = $conn->query("DESCRIBE $table");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        print_r($columns);
    }

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 