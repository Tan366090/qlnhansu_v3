<?php
require_once __DIR__ . '/../../config/Database.php';

try {
    $database = Database::getInstance();
    $db = $database->getConnection();

    // Check users table
    $stmt = $db->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Users table columns:\n";
    print_r($columns);

    // Check roles table
    $stmt = $db->query("DESCRIBE roles");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\nRoles table columns:\n";
    print_r($columns);

    // Check sample data
    $stmt = $db->query("SELECT * FROM users LIMIT 1");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "\nSample user data:\n";
    print_r($user);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} 