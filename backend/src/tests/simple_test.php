<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Simple Test ===\n\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Current Directory: " . __DIR__ . "\n\n";

// Test database connection
try {
    echo "Loading database configuration...\n";
    require_once __DIR__ . '/../config/database.php';
    
    echo "Creating database instance...\n";
    $database = Database::getInstance();
    
    echo "Getting database connection...\n";
    $db = $database->getConnection();
    
    echo "Database connection successful!\n";
    
    echo "Testing query...\n";
    $stmt = $db->query("SELECT 1");
    $result = $stmt->fetch();
    echo "Query test successful!\n";
    
    echo "Testing users table...\n";
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "Number of users: " . $result['count'] . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Error type: " . get_class($e) . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed.\n";
?> 