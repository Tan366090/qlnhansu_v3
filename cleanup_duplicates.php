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

// Start transaction
$conn->begin_transaction();

try {
    // First, let's see how many duplicates we have
    $count_sql = "SELECT up.user_id, u.username, COUNT(*) as count 
                 FROM user_profiles up
                 JOIN users u ON up.user_id = u.user_id
                 GROUP BY up.user_id 
                 HAVING count > 1";
    
    $count_result = $conn->query($count_sql);
    echo "Found " . $count_result->num_rows . " users with duplicates\n";
    
    while ($row = $count_result->fetch_assoc()) {
        echo "User ID: " . $row['user_id'] . " (username: " . $row['username'] . ") has " . $row['count'] . " profiles\n";
    }

    // Create a backup table just in case
    $conn->query("CREATE TABLE IF NOT EXISTS user_profiles_backup LIKE user_profiles");
    $conn->query("INSERT INTO user_profiles_backup SELECT * FROM user_profiles");
    echo "\nCreated backup of user_profiles table\n";

    // Create a temporary table to store the IDs we want to keep
    $conn->query("CREATE TEMPORARY TABLE keep_profiles AS
                 SELECT MIN(up.profile_id) as profile_id
                 FROM user_profiles up
                 JOIN users u ON up.user_id = u.user_id
                 GROUP BY up.user_id");
    
    echo "Created temporary table with profiles to keep\n";

    // Show what we're about to delete
    $preview_sql = "SELECT COUNT(*) as count 
                   FROM user_profiles 
                   WHERE profile_id NOT IN (SELECT profile_id FROM keep_profiles)";
    
    $preview_result = $conn->query($preview_sql);
    $preview_row = $preview_result->fetch_assoc();
    echo "About to delete " . $preview_row['count'] . " duplicate profiles\n";

    // Delete all profiles that are not in our keep_profiles table
    $delete_sql = "DELETE FROM user_profiles 
                   WHERE profile_id NOT IN (SELECT profile_id FROM keep_profiles)";
    
    $result = $conn->query($delete_sql);
    
    if ($result) {
        $affected_rows = $conn->affected_rows;
        echo "Successfully cleaned up duplicates. Removed $affected_rows duplicate entries.\n";
        
        // Verify the cleanup
        $verify_sql = "SELECT up.user_id, u.username, COUNT(*) as count 
                      FROM user_profiles up
                      JOIN users u ON up.user_id = u.user_id
                      GROUP BY up.user_id 
                      HAVING count > 1";
        
        $verify_result = $conn->query($verify_sql);
        
        if ($verify_result->num_rows == 0) {
            echo "Verification successful: No more duplicates found.\n";
            
            // Show final count of profiles
            $final_count = $conn->query("SELECT COUNT(*) as count FROM user_profiles");
            $final_row = $final_count->fetch_assoc();
            echo "Total profiles remaining: " . $final_row['count'] . "\n";
            
            // Show sample of remaining profiles
            echo "\nSample of remaining profiles:\n";
            $sample = $conn->query("SELECT up.profile_id, up.user_id, u.username, up.full_name 
                                  FROM user_profiles up
                                  JOIN users u ON up.user_id = u.user_id
                                  LIMIT 5");
            
            while ($row = $sample->fetch_assoc()) {
                echo "Profile ID: " . $row['profile_id'] . 
                     ", User ID: " . $row['user_id'] . 
                     ", Username: " . $row['username'] . 
                     ", Full Name: " . $row['full_name'] . "\n";
            }
        } else {
            throw new Exception("Verification failed: Some duplicates still exist!");
        }
    } else {
        throw new Exception("Error during cleanup: " . $conn->error);
    }

    // If we get here, everything worked, so commit the transaction
    $conn->commit();
    echo "\nChanges committed successfully.\n";
    
} catch (Exception $e) {
    // Rollback the transaction on error
    $conn->rollback();
    echo "Error: " . $e->getMessage() . "\n";
    echo "Changes have been rolled back.\n";
}

// Clean up
$conn->query("DROP TEMPORARY TABLE IF EXISTS keep_profiles");
$conn->close();

echo "\nNOTE: A backup of the original data has been saved in 'user_profiles_backup' table.\n";
?> 