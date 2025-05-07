<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once __DIR__ . '/../config/database.php';

// Set content type to plain text for easy reading
header('Content-Type: text/plain');

try {
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    // Get user information with hidden password length
    $query = "SELECT user_id, username, email, 
              CASE 
                WHEN LENGTH(password_hash) > 40 THEN CONCAT(LEFT(password_hash, 10), '...', RIGHT(password_hash, 10), ' (', LENGTH(password_hash), ' chars)') 
                ELSE password_hash 
              END as password_info,
              role_id, is_active 
              FROM users 
              WHERE email = 'john.doe@example.com' OR username = 'john.doe'";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "User found:\n";
        echo "User ID: " . $row['user_id'] . "\n";
        echo "Username: " . $row['username'] . "\n";
        echo "Email: " . $row['email'] . "\n";
        echo "Password info: " . $row['password_info'] . "\n";
        echo "Role ID: " . $row['role_id'] . "\n";
        echo "Active: " . ($row['is_active'] ? 'Yes' : 'No') . "\n\n";
        
        // Test password verification with '123456'
        $testPassword = '123456';
        $storedHash = $row['password_hash'] ?? '';
        
        if (strlen($storedHash) < 20) {
            echo "WARNING: Password is likely stored in plain text or using a weak hash method.\n";
            echo "Password matches '123456': " . ($storedHash === $testPassword ? 'Yes' : 'No') . "\n";
            
            // Generate proper hash for comparison
            echo "\nProper password hash would be: \n";
            echo password_hash($testPassword, PASSWORD_DEFAULT) . " (" . strlen(password_hash($testPassword, PASSWORD_DEFAULT)) . " chars)";
        } else {
            echo "Testing password verification with '123456': ";
            echo (password_verify($testPassword, $storedHash) ? 'Valid password' : 'Invalid password') . "\n";
            
            // If verification fails with 123456, try hashing 123456 and compare the actual stored hash
            if (!password_verify($testPassword, $storedHash)) {
                echo "\nStored hash doesn't match for '123456'.\n";
                echo "Try running the hash_existing_passwords.php script again.\n";
            }
        }
    } else {
        echo "No user found with email 'john.doe@example.com' or username 'john.doe'";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 