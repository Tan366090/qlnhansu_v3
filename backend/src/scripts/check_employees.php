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

    // Check employees table
    $sql = "SELECT * FROM employees";
    $result = $conn->query($sql);
    
    if ($result) {
        echo "Current employees in database:\n";
        while ($row = $result->fetch_assoc()) {
            echo "Employee Code: " . $row['employee_code'] . 
                 ", User ID: " . $row['user_id'] . 
                 ", Status: " . $row['status'] . "\n";
        }
    } else {
        throw new Exception("Error querying employees: " . $conn->error);
    }

    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 