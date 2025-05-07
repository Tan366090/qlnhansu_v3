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
                s.*,
                e.full_name,
                e.employee_code,
                d.department_name,
                p.position_name
            FROM salaries s
            LEFT JOIN employees e ON s.employee_id = e.employee_id
            LEFT JOIN departments d ON e.department_id = d.department_id
            LEFT JOIN positions p ON e.position_id = p.position_id
            ORDER BY s.salary_date DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute();

    $salaries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the data
    $formattedSalaries = array_map(function($sal) {
        return [
            'id' => $sal['salary_id'],
            'employee_id' => $sal['employee_code'],
            'employee_name' => $sal['full_name'],
            'department' => $sal['department_name'],
            'position' => $sal['position_name'],
            'basic_salary' => $sal['basic_salary'],
            'allowances' => $sal['allowances'],
            'deductions' => $sal['deductions'],
            'net_salary' => $sal['net_salary'],
            'salary_date' => $sal['salary_date'],
            'status' => $sal['status']
        ];
    }, $salaries);

    echo json_encode([
        'success' => true,
        'data' => $formattedSalaries
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 