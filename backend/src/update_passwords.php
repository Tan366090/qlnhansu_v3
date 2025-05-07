<?php
require_once __DIR__ . '/backend/src/config/Database.php';

try {
    // Database connection
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Get all users
    $query = "SELECT user_id, username FROM users";
    $stmt = $conn->query($query);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Update each user's password
    $updateQuery = "UPDATE users SET password_hash = :password_hash WHERE user_id = :user_id";
    $updateStmt = $conn->prepare($updateQuery);

    foreach ($users as $user) {
        // Use '123456' as the password for all users
        $password = '123456';
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $updateStmt->execute([
            ':password_hash' => $password_hash,
            ':user_id' => $user['user_id']
        ]);

        echo "Updated password for user: " . $user['username'] . "\n";
    }

    echo "\nAll passwords have been updated successfully!\n";
    echo "You can now login with:\n";
    echo "Username: [username from the users table]\n";
    echo "Password: 123456\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 