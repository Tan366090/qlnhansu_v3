<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../config/database.php';

try {
    // Connect to the database
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Check if the bonuses table exists
    $checkTableQuery = "SHOW TABLES LIKE 'bonuses'";
    $checkTableResult = $conn->query($checkTableQuery);
    if ($checkTableResult->num_rows === 0) {
        throw new Exception("Table 'bonuses' does not exist in the database.");
    }

    // Query the bonuses table
    $query = "SELECT id, employee_name, value, reason, effective_date FROM bonuses";
    $result = $conn->query($query);

    if (!$result) {
        throw new Exception("Query execution failed: " . $conn->error);
    }

    $bonuses = [];
    while ($row = $result->fetch_assoc()) {
        $bonuses[] = $row;
    }

    // Return the data as JSON
    echo json_encode($bonuses);

    $conn->close();
} catch (Exception $e) {
    // Log the error message for debugging
    error_log("Error in getBonuses.php: " . $e->getMessage());

    // Return a 500 response with the error message
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
