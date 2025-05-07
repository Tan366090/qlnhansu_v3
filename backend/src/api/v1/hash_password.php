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
    
    // Plain password to hash
    $plainPassword = '123456';
    $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
    
    echo "Plain password: $plainPassword\n";
    echo "Hashed password: $hashedPassword\n";
    echo "Hash length: " . strlen($hashedPassword) . " characters\n\n";
    
    // Update the user's password
    $query = "UPDATE users SET password_hash = :password_hash WHERE username = 'john.doe' OR email = 'john.doe@example.com'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':password_hash', $hashedPassword);
    
    if ($stmt->execute()) {
        echo "Password updated successfully for john.doe!\n";
        
        // Get updated user info
        $checkQuery = "SELECT username, email, password_hash FROM users WHERE username = 'john.doe' OR email = 'john.doe@example.com'";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->execute();
        $user = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        echo "\nUpdated user information:\n";
        echo "Username: " . $user['username'] . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Password hash (first 20 chars): " . substr($user['password_hash'], 0, 20) . "...\n";
        
        // Verify the new password
        echo "\nVerifying password: " . (password_verify($plainPassword, $user['password_hash']) ? "Success!" : "Failed!") . "\n";
    } else {
        echo "Failed to update password. Error: " . implode(" ", $stmt->errorInfo()) . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 