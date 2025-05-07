<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Reset all passwords to default
    $updateQuery = "UPDATE users SET password_hash = '12345'";
    $conn->exec($updateQuery);

    echo "Successfully reset all passwords to default (12345)\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 