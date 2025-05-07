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

    // Query the salary_history table
    $query = "
        SELECT 
            sh.salary_history_id AS history_id,
            sh.user_id AS employee_id,
            u.username AS employee_name,
            sh.salary_coefficient,
            sh.salary_level,
            sh.effective_date,
            sh.job_position,
            sh.department
        FROM salary_history sh
        LEFT JOIN users u ON sh.user_id = u.user_id
    ";
    $result = $conn->query($query);

    if (!$result) {
        throw new Exception("Query execution failed: " . $conn->error);
    }

    $salaryHistory = [];
    while ($row = $result->fetch_assoc()) {
        $salaryHistory[] = $row;
    }

    // Return the data as JSON
    echo json_encode($salaryHistory);

    $conn->close();
} catch (Exception $e) {
    // Log the error message for debugging
    error_log("Error in getSalaryHistory.php: " . $e->getMessage());

    // Return a 500 response with the error message
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
