<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Get all leave records
    $query = "SELECT 
                l.id,
                l.employee_id,
                e.full_name as employee_name,
                l.start_date,
                l.end_date,
                l.type,
                l.status,
                l.reason,
                l.created_at,
                l.updated_at
            FROM leaves l
            LEFT JOIN employees e ON l.employee_id = e.id
            ORDER BY l.start_date DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute();

    $leaves = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the data
    $formattedLeaves = array_map(function($leave) {
        return [
            'id' => $leave['id'],
            'employee_id' => $leave['employee_id'],
            'employee_name' => $leave['employee_name'],
            'start_date' => $leave['start_date'],
            'end_date' => $leave['end_date'],
            'type' => $leave['type'],
            'status' => $leave['status'],
            'reason' => $leave['reason'],
            'created_at' => $leave['created_at'],
            'updated_at' => $leave['updated_at']
        ];
    }, $leaves);

    echo json_encode([
        'success' => true,
        'data' => $formattedLeaves
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} 