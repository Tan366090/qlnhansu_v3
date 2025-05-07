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

// Query to find duplicates
$sql = "SELECT user_id, COUNT(*) as count 
        FROM user_profiles 
        GROUP BY user_id 
        HAVING count > 1";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "Found duplicate user profiles:\n";
    while($row = $result->fetch_assoc()) {
        echo "User ID: " . $row["user_id"] . " - Count: " . $row["count"] . "\n";
        
        // Get details of duplicates for this user
        $details_sql = "SELECT * FROM user_profiles WHERE user_id = " . $row["user_id"];
        $details_result = $conn->query($details_sql);
        
        echo "Details:\n";
        while($detail = $details_result->fetch_assoc()) {
            echo "Profile ID: " . $detail["profile_id"] . 
                 " - Full Name: " . $detail["full_name"] . 
                 " - Created At: " . $detail["created_at"] . "\n";
        }
        echo "\n";
    }
} else {
    echo "No duplicate user profiles found";
}

$conn->close();
?> 