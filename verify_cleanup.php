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

// First check if the table exists and has any rows
$check_table = "SHOW TABLES LIKE 'user_profiles'";
$table_result = $conn->query($check_table);

if ($table_result->num_rows == 0) {
    die("Error: table 'user_profiles' does not exist!\n");
}

// Get total count of profiles
$total_sql = "SELECT COUNT(*) as total FROM user_profiles";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
echo "Total number of profiles in database: " . $total_row['total'] . "\n\n";

// Check how many profiles each user has
$sql = "SELECT user_id, COUNT(*) as profile_count
        FROM user_profiles
        GROUP BY user_id
        ORDER BY user_id";

$result = $conn->query($sql);

if (!$result) {
    die("Error executing query: " . $conn->error . "\n");
}

echo "Current profile counts per user:\n";
echo "------------------------------\n";

if ($result->num_rows == 0) {
    echo "No user profiles found in the database!\n";
} else {
    while ($row = $result->fetch_assoc()) {
        echo "User ID: " . $row['user_id'] . " has " . $row['profile_count'] . " profile(s)\n";
    }
}

// Check for any remaining duplicates
$duplicate_check = "SELECT user_id, COUNT(*) as count 
                   FROM user_profiles 
                   GROUP BY user_id 
                   HAVING count > 1";

$duplicate_result = $conn->query($duplicate_check);

if (!$duplicate_result) {
    die("Error checking for duplicates: " . $conn->error . "\n");
}

if ($duplicate_result->num_rows > 0) {
    echo "\nWARNING: Found users with multiple profiles:\n";
    while ($row = $duplicate_result->fetch_assoc()) {
        echo "User ID: " . $row['user_id'] . " has " . $row['count'] . " profiles\n";
    }
} else {
    echo "\nSuccess: No duplicate profiles found!\n";
}

// Show a sample of actual profiles
echo "\nSample of profiles in database:\n";
echo "-----------------------------\n";
$sample_sql = "SELECT * FROM user_profiles LIMIT 5";
$sample_result = $conn->query($sample_sql);

if ($sample_result->num_rows > 0) {
    while ($row = $sample_result->fetch_assoc()) {
        echo "Profile ID: " . $row['profile_id'] . ", User ID: " . $row['user_id'] . "\n";
    }
} else {
    echo "No profiles found!\n";
}

$conn->close();
?> 