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

// Check if backup table exists
$table_check = $conn->query("SHOW TABLES LIKE 'user_profiles_backup'");
if ($table_check->num_rows == 0) {
    die("Backup table does not exist!\n");
}

// Count total profiles in backup
$count_result = $conn->query("SELECT COUNT(*) as count FROM user_profiles_backup");
$count_row = $count_result->fetch_assoc();
echo "Total profiles in backup: " . $count_row['count'] . "\n\n";

// Show duplicates in backup
$duplicates = $conn->query("SELECT user_id, COUNT(*) as count 
                           FROM user_profiles_backup 
                           GROUP BY user_id 
                           HAVING count > 1");

echo "Users with multiple profiles in backup:\n";
while ($row = $duplicates->fetch_assoc()) {
    echo "User ID: " . $row['user_id'] . " has " . $row['count'] . " profiles\n";
    
    // Show details of these profiles
    $profiles = $conn->query("SELECT * FROM user_profiles_backup WHERE user_id = " . $row['user_id']);
    while ($profile = $profiles->fetch_assoc()) {
        echo "  Profile ID: " . $profile['profile_id'] . 
             ", Full Name: " . $profile['full_name'] . 
             ", Created: " . $profile['created_at'] . "\n";
    }
    echo "\n";
}

$conn->close();
?> 