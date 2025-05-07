<?php
require_once __DIR__ . '/../config/database.php';

$config = require __DIR__ . '/../config/database.php';

try {
    $conn = new mysqli(
        $config['host'],
        $config['username'],
        $config['password'],
        $config['database']
    );
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Read and execute SQL file
    $sql = file_get_contents(__DIR__ . '/../database/insert_dashboard_data.sql');
    
    if ($conn->multi_query($sql)) {
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->next_result());
        
        echo "Dashboard sample data inserted successfully!\n";
    } else {
        throw new Exception("Error executing SQL: " . $conn->error);
    }

    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 