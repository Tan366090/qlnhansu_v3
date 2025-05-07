<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    $query = "SELECT 
                l.id,
                l.employee_id,
                e.full_name,
                l.start_date,
                l.end_date,
                l.reason,
                l.status
            FROM leave_requests l
            LEFT JOIN employees e ON l.employee_id = e.id
            ORDER BY l.start_date DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute();

    $leaveRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the data
    $formattedLeaveRequests = array_map(function($request) {
        return [
            'id' => $request['id'],
            'employee_id' => $request['employee_id'],
            'employee_name' => $request['full_name'],
            'start_date' => $request['start_date'],
            'end_date' => $request['end_date'],
            'reason' => $request['reason'],
            'status' => $request['status']
        ];
    }, $leaveRequests);

    echo json_encode([
        'success' => true,
        'data' => $formattedLeaveRequests
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 