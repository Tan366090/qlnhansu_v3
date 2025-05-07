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
                k.id,
                k.employee_id,
                e.full_name,
                k.completion_rate,
                k.created_at
            FROM kpi_records k
            LEFT JOIN employees e ON k.employee_id = e.id
            ORDER BY k.created_at DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute();

    $kpiRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the data
    $formattedKpiRecords = array_map(function($record) {
        return [
            'id' => $record['id'],
            'employee_id' => $record['employee_id'],
            'employee_name' => $record['full_name'],
            'completion_rate' => $record['completion_rate'],
            'created_at' => $record['created_at']
        ];
    }, $kpiRecords);

    echo json_encode([
        'success' => true,
        'data' => $formattedKpiRecords
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 