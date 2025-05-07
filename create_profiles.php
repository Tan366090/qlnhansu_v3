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
    // Get all users without profiles
    $users = $conn->query("SELECT u.* 
                          FROM users u 
                          LEFT JOIN user_profiles up ON u.user_id = up.user_id
                          WHERE up.profile_id IS NULL");
    
    $users_without_profiles = $users->num_rows;
    echo "Found " . $users_without_profiles . " users without profiles\n\n";
    
    if ($users_without_profiles > 0) {
        while ($user = $users->fetch_assoc()) {
            // Generate a profile for the user
            $full_name = $user['username']; // Using username as full name for now
            
            // Get the next available profile_id
            $next_id_result = $conn->query("SELECT COALESCE(MAX(profile_id), 0) + 1 as next_id FROM user_profiles");
            $next_id_row = $next_id_result->fetch_assoc();
            $profile_id = $next_id_row['next_id'];
            
            $sql = "INSERT INTO user_profiles (
                        profile_id,
                        user_id, 
                        full_name,
                        created_at,
                        updated_at
                    ) VALUES (
                        ?,
                        ?,
                        ?,
                        NOW(),
                        NOW()
                    )";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iis", $profile_id, $user['user_id'], $full_name);
            
            if ($stmt->execute()) {
                echo "Created profile for user ID: " . $user['user_id'] . 
                     " (Username: " . $user['username'] . 
                     ", Profile ID: " . $profile_id . ")\n";
            } else {
                throw new Exception("Failed to create profile for user ID: " . 
                                  $user['user_id'] . " - " . $conn->error);
            }
            
            $stmt->close();
        }
        
        // Verify the profiles were created
        $verify = $conn->query("SELECT u.user_id, u.username, up.profile_id, up.full_name
                               FROM users u
                               LEFT JOIN user_profiles up ON u.user_id = up.user_id
                               ORDER BY u.user_id");
        
        echo "\nVerification of profiles:\n";
        echo "------------------------\n";
        while ($row = $verify->fetch_assoc()) {
            echo "User ID: " . $row['user_id'] . 
                 ", Username: " . $row['username'] . 
                 ", Profile ID: " . ($row['profile_id'] ?? 'MISSING') . 
                 ", Full Name: " . ($row['full_name'] ?? 'MISSING') . "\n";
        }
        
        // Check for any users still missing profiles
        $missing = $conn->query("SELECT COUNT(*) as count
                                FROM users u 
                                LEFT JOIN user_profiles up ON u.user_id = up.user_id
                                WHERE up.profile_id IS NULL");
        $missing_row = $missing->fetch_assoc();
        
        if ($missing_row['count'] > 0) {
            throw new Exception($missing_row['count'] . " users still missing profiles!");
        }
        
        // Commit the transaction
        $conn->commit();
        echo "\nAll profiles created successfully!\n";
        
    } else {
        echo "All users already have profiles.\n";
    }
    
} catch (Exception $e) {
    // Rollback the transaction on error
    $conn->rollback();
    echo "Error: " . $e->getMessage() . "\n";
    echo "Changes have been rolled back.\n";
}

$conn->close();
?> 