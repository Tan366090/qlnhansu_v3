<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Get query parameters
    $date = isset($_GET['date']) ? $_GET['date'] : 'today';
    $status = isset($_GET['status']) ? $_GET['status'] : 'all';

    // Log the parameters
    error_log("Date: " . $date . ", Status: " . $status);

    // Build the base query
    $query = "SELECT 
                e.employee_code as employee_id,
                e.employee_code as employee_name,
                d.name as department_name,
                a.check_in_time,
                a.check_out_time,
                a.attendance_symbol as status,
                a.work_duration_hours,
                a.notes,
                a.recorded_at,
                a.source
            FROM attendance a
            JOIN employees e ON a.employee_id = e.id
            JOIN departments d ON e.department_id = d.id";

    // Add date filter
    if ($date === 'today') {
        $query .= " WHERE DATE(a.attendance_date) = CURDATE()";
    } elseif ($date === 'week') {
        $query .= " WHERE YEARWEEK(a.attendance_date) = YEARWEEK(CURDATE())";
    } elseif ($date === 'month') {
        $query .= " WHERE MONTH(a.attendance_date) = MONTH(CURDATE()) AND YEAR(a.attendance_date) = YEAR(CURDATE())";
    }

    // Add status filter if not 'all'
    if ($status !== 'all') {
        $query .= $date === 'today' ? " AND" : " WHERE";
        $query .= " a.attendance_symbol = :status";
    }

    $query .= " ORDER BY a.attendance_date DESC, a.check_in_time DESC";

    // Log the query
    error_log("SQL Query: " . $query);

    $stmt = $conn->prepare($query);
    
    // Bind status parameter if needed
    if ($status !== 'all') {
        $stmt->bindParam(':status', $status);
    }

    $stmt->execute();
    $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Log the results
    error_log("Number of records found: " . count($attendance));

    echo json_encode([
        'success' => true,
        'data' => $attendance
    ]);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?> 