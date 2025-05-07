<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

$searchTerm = isset($_GET['q']) ? $_GET['q'] : '';

if (empty($searchTerm)) {
    echo json_encode([]);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("
        SELECT employee_id, full_name, position_id, department_id 
        FROM employees 
        WHERE (employee_id LIKE ? OR full_name LIKE ?) 
        AND status = 'active'
        LIMIT 10
    ");

    $searchPattern = "%$searchTerm%";
    $stmt->bind_param("ss", $searchPattern, $searchPattern);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $employees = [];

    while ($row = $result->fetch_assoc()) {
        $employees[] = [
            'employee_id' => $row['employee_id'],
            'full_name' => $row['full_name'],
            'position_id' => $row['position_id'],
            'department_id' => $row['department_id']
        ];
    }

    echo json_encode($employees);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 