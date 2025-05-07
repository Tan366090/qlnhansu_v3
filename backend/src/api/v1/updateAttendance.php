<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Get database connection
    $db = require_once '../config/database.php';
    if (!$db) {
        throw new Exception("Database connection failed");
    }

    // Get JSON input
    $jsonInput = file_get_contents("php://input");
    if (!$jsonInput) {
        throw new Exception("No input received");
    }

    $input = json_decode($jsonInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON: " . json_last_error_msg());
    }
    
    // Log received data for debugging
    error_log("Received data: " . print_r($input, true));

    // Validate input
    if (!isset($input['id']) || empty($input['id'])) {
        throw new Exception("Missing or invalid ID");
    }

    if (!isset($input['attendance_date']) || !isset($input['attendance_symbol'])) {
        throw new Exception("Missing required fields");
    }

    // Sanitize and validate data
    $id = filter_var($input['id'], FILTER_SANITIZE_NUMBER_INT);
    $date = filter_var($input['attendance_date'], FILTER_SANITIZE_STRING);
    $symbol = filter_var($input['attendance_symbol'], FILTER_SANITIZE_STRING);
    $notes = isset($input['notes']) ? filter_var($input['notes'], FILTER_SANITIZE_STRING) : '';

    // Log sanitized data
    error_log("Sanitized data - ID: $id, Date: $date, Symbol: $symbol, Notes: $notes");

    // Prepare and execute query
    $query = "UPDATE attendance SET attendance_date = ?, attendance_symbol = ?, notes = ? WHERE attendance_id = ?"; // Updated column name
    $stmt = $db->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Database preparation failed: " . $db->error);
    }

    $stmt->bind_param('sssi', $date, $symbol, $notes, $id);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update record: " . $stmt->error);
    }

    // Check if any rows were affected
    if ($stmt->affected_rows === 0) {
        throw new Exception("No record found with ID: " . $id);
    }

    $response = [
        'success' => true,
        'message' => 'Cập nhật chấm công thành công',
        'affected_rows' => $stmt->affected_rows,
        'id' => $id
    ];

    echo json_encode($response);
    exit();

} catch (Exception $e) {
    error_log("Error in updateAttendance.php: " . $e->getMessage());
    
    $errorResponse = [
        'success' => false,
        'error' => $e->getMessage()
    ];
    
    http_response_code(500);
    echo json_encode($errorResponse);
    exit();
}
?>