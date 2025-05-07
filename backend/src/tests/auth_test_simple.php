<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering
ob_start();

echo "=== Authentication Test ===\n\n";

try {
    // Load required files
    echo "Loading required files...\n";
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../api/middleware/CORSMiddleware.php';
    
    // Test database connection
    echo "Testing database connection...\n";
    $database = Database::getInstance();
    $db = $database->getConnection();
    echo "Database connection successful!\n";
    
    // Test CORS middleware
    echo "\nTesting CORS middleware...\n";
    $cors = new CORSMiddleware();
    $cors->handleCORS();
    echo "CORS headers set successfully!\n";
    
    // Test user authentication
    echo "\nTesting user authentication...\n";
    $username = 'test_user';
    $password = 'test_password';
    
    // Get employee role_id
    $stmt = $db->prepare("SELECT role_id FROM roles WHERE role_name = ?");
    $stmt->execute(['employee']);
    $role = $stmt->fetch();
    
    if (!$role) {
        throw new Exception("Employee role not found in database");
    }
    
    // Check if test user exists
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "Test user exists!\n";
        
        // Test password verification
        if (md5($password) === $user['password_hash']) {
            echo "Password verification successful!\n";
        } else {
            echo "Password verification failed!\n";
        }
    } else {
        echo "Test user does not exist, creating...\n";
        
        // Create test user
        $stmt = $db->prepare("INSERT INTO users (username, password_hash, email, role_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $username,
            md5($password),
            'test@example.com',
            $role['role_id']
        ]);
        echo "Test user created successfully!\n";
    }
    
    // Test session handling
    echo "\nTesting session handling...\n";
    session_start();
    $_SESSION['user'] = [
        'id' => 1,
        'username' => 'test_user',
        'role' => 'employee'
    ];
    echo "Session created successfully!\n";
    
    // Cleanup
    echo "\nCleaning up...\n";
    $stmt = $db->prepare("DELETE FROM users WHERE username = ?");
    $stmt->execute([$username]);
    echo "Test user removed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Error type: " . get_class($e) . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed.\n";

// End output buffering and flush
ob_end_flush();
?> 