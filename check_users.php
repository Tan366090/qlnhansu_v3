<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "qlnhansu";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Count total users
$count_result = $conn->query("SELECT COUNT(*) as count FROM users");
$count_row = $count_result->fetch_assoc();
echo "Total users in database: " . $count_row['count'] . "\n\n";

if ($count_row['count'] > 0) {
    // Show sample of users
    echo "Sample of users:\n";
    echo "---------------\n";
    $users = $conn->query("SELECT * FROM users LIMIT 5");
    while ($user = $users->fetch_assoc()) {
        echo "User ID: " . $user['user_id'] . 
             ", Username: " . $user['username'] . 
             ", Email: " . $user['email'] . 
             ", Status: " . $user['status'] . "\n";
    }
}

$conn->close();
?> 