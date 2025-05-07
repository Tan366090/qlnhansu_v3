<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log request details
error_log("Request received: " . print_r($_SERVER, true));

require_once 'config.php';

try {
    // Connect to database
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception('Kết nối database thất bại: ' . $conn->connect_error);
    }

    // Set charset
    $conn->set_charset("utf8");

    // Prepare response
    $response = [
        'success' => false,
        'data' => [],
        'error' => '',
        'debug' => [
            'request_method' => $_SERVER['REQUEST_METHOD'],
            'request_uri' => $_SERVER['REQUEST_URI'],
            'query_string' => $_SERVER['QUERY_STRING'] ?? '',
            'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? '',
            'http_host' => $_SERVER['HTTP_HOST'] ?? '',
            'http_referer' => $_SERVER['HTTP_REFERER'] ?? ''
        ]
    ];

    // Get month and year from query parameters
    $month = isset($_GET['month']) ? intval($_GET['month']) : null;
    $year = isset($_GET['year']) ? intval($_GET['year']) : null;

    // Query to get attendance data
    $sql = "SELECT attendance_id, attendance_date, recorded_at, attendance_symbol, notes FROM attendance";

    if ($month && $year) {
        $sql .= " WHERE MONTH(attendance_date) = $month AND YEAR(attendance_date) = $year";
    }

    $sql .= " ORDER BY attendance_date DESC"; // Order by date

    error_log("Executing SQL: " . $sql);

    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception('Lỗi truy vấn: ' . $conn->error);
    }

    // Fetch data
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'attendance_id' => $row['attendance_id'],
            'attendance_date' => $row['attendance_date'],
            'recorded_at' => $row['recorded_at'],
            'attendance_symbol' => $row['attendance_symbol'],
            'notes' => $row['notes']
        ];
    }

    $response['success'] = true;
    $response['data'] = $data;

    // Close connection
    $conn->close();

} catch (Exception $e) {
    error_log("Error in getAttendance.php: " . $e->getMessage());
    $response['success'] = false;
    $response['error'] = $e->getMessage();
}

// Send response
echo json_encode($response);
exit(); // Ensure no additional output