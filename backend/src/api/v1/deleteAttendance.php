<?php
header('Content-Type: application/json');

// Dynamically set Access-Control-Allow-Origin based on the request's Origin header
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
} else {
    header('Access-Control-Allow-Origin: *'); // Fallback for development
}

header('Access-Control-Allow-Methods: DELETE, GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'config.php';

try {
    // Get the attendance_id from the request
    $attendance_id = isset($_GET['id']) ? intval($_GET['id']) : null;

    // Validate input
    if (!$attendance_id) {
        throw new Exception("Missing or invalid parameter: attendance_id");
    }

    // Prepare the SQL statement
    $stmt = $conn->prepare("DELETE FROM attendance WHERE attendance_id = ?");
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }

    $stmt->bind_param("i", $attendance_id);

    // Execute the query
    if (!$stmt->execute()) {
        throw new Exception("Error executing delete query: " . $stmt->error);
    }

    // Check if any row was affected
    if ($stmt->affected_rows === 0) {
        throw new Exception("Attendance record not found with ID: $attendance_id");
    }

    // Fetch remaining records
    $selectStmt = $conn->prepare("SELECT attendance_id, attendance_date, recorded_at, attendance_symbol, notes FROM attendance ORDER BY attendance_date DESC");
    if (!$selectStmt) {
        throw new Exception("Failed to prepare select statement: " . $conn->error);
    }

    $selectStmt->execute();
    $result = $selectStmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);

    // Return success response with remaining data
    echo json_encode([
        'success' => true,
        'message' => 'Attendance record deleted successfully',
        'data' => $data
    ]);

} catch (Exception $e) {
    // Log the error for debugging
    error_log("Error in deleteAttendance.php: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?>
