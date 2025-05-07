<?php
header('Content-Type: application/json');
require_once '../../config/database.php';
require_once '../../models/attendance.php';

// Get date parameter, default to today if not provided
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $attendance = new Attendance($db);
    $result = $attendance->getAttendanceList($date);
    
    echo json_encode([
        'status' => 'success',
        'data' => $result
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} 