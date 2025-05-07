<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: text/plain; charset=utf-8');

echo "Attempting to hash existing passwords...\n\n";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Select users with plain text passwords (adjust length check if needed)
    // WARNING: This is a basic check. It assumes passwords needing hashing are short and not already looking like hashes.
    // A more robust check might be needed for complex scenarios.
    $stmt = $conn->prepare("SELECT user_id, password_hash FROM users WHERE LENGTH(password_hash) < 60"); 
    $stmt->execute();
    $usersToUpdate = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($usersToUpdate) === 0) {
        echo "No users found with potentially plain text passwords needing hashing.\n";
        exit;
    }

    echo "Found " . count($usersToUpdate) . " user(s) to update.\n";

    $updateStmt = $conn->prepare("UPDATE users SET password_hash = :hashed_password WHERE user_id = :user_id");

    $updatedCount = 0;
    foreach ($usersToUpdate as $user) {
        $userId = $user['user_id'];
        $plainPassword = $user['password_hash']; // Assuming this column currently holds the plain password

        // Hash the password
        $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

        if ($hashedPassword === false) {
            echo "Failed to hash password for user ID: {$userId}\n";
            continue;
        }

        // Update the database
        $updateStmt->bindParam(':hashed_password', $hashedPassword);
        $updateStmt->bindParam(':user_id', $userId);
        
        if ($updateStmt->execute()) {
            echo "Successfully hashed and updated password for user ID: {$userId}\n";
            $updatedCount++;
        } else {
            echo "Failed to update password for user ID: {$userId}. Error: " . implode(" ", $updateStmt->errorInfo()) . "\n";
        }
    }

    echo "\nFinished hashing passwords. {$updatedCount} user(s) updated.\n";

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "General Error: " . $e->getMessage() . "\n";
}
?> 