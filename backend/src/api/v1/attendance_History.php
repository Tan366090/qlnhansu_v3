<?php
// Set CORS headers
header("Access-Control-Allow-Origin: http://localhost:4000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

try {
    // Connect to database
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception('Kết nối database thất bại: ' . $conn->connect_error);
    }

    // Set charset
    $conn->set_charset("utf8");

    // Get month and year from query parameters
    $month = isset($_GET['month']) ? intval($_GET['month']) : null;
    $year = isset($_GET['year']) ? intval($_GET['year']) : null;

    // Query to get attendance data
    $sql = "SELECT attendance_id, attendance_date, recorded_at, attendance_symbol, notes FROM attendance";

    if ($month && $year) {
        $sql .= " WHERE MONTH(attendance_date) = $month AND YEAR(attendance_date) = $year";
    }

    $sql .= " ORDER BY attendance_date DESC";

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

    // Prepare response
    $response = [
        'success' => true,
        'data' => $data
    ];

    // Close connection
    $conn->close();

} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => $e->getMessage()
    ];
}

// Send response
echo json_encode($response);
exit();
?>