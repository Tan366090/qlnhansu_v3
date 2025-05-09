<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';

try {
    // Log start of execution
    error_log("Starting HR department chart API execution");

    $db = new Database();
    $conn = $db->getConnection();
    error_log("Database connection established");

    // Get employee count by department
    $query = "SELECT 
        d.name as department_name,
        COUNT(e.id) as employee_count
        FROM departments d
        LEFT JOIN employees e ON d.id = e.department_id
        WHERE e.status = 'active' OR e.status IS NULL
        GROUP BY d.id, d.name
        ORDER BY employee_count DESC";

    error_log("Executing query: " . $query);
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Query executed successfully. Result count: " . count($result));

    if (empty($result)) {
        error_log("No data found in departments or employees table");
        throw new Exception("No data available for departments");
    }

    // Define colors for each department
    $departmentColors = [
        'IT' => '#4e73df',           // Blue
        'HR' => '#1cc88a',           // Green
        'Finance' => '#f6c23e',      // Yellow
        'Marketing' => '#e74a3b',    // Red
        'Sales' => '#36b9cc',        // Cyan
        'Operations' => '#858796',   // Gray
        'Legal' => '#5a5c69',        // Dark Gray
        'R&D' => '#f8f9fc',          // Light Gray
        'Customer Service' => '#4e73df', // Blue
        'Logistics' => '#1cc88a'     // Green
    ];

    // Format the response
    $response = [
        'success' => true,
        'data' => [
            'departments' => $result,
            'colors' => $departmentColors
        ]
    ];

    echo json_encode($response);

} catch(Exception $e) {
    error_log("Error in HR department chart API: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 