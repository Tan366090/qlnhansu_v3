<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Debug: Check if connection is successful
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Debug: Check if tables exist
    $tables = ['departments', 'employees', 'user_profiles'];
    foreach ($tables as $table) {
        $checkTable = $conn->query("SHOW TABLES LIKE '$table'");
        if ($checkTable->rowCount() == 0) {
            throw new Exception("Table '$table' does not exist");
        }
    }

    $query = "SELECT 
                d.id as department_id,
                d.name,
                d.description,
                d.manager_id,
                d.parent_id,
                COUNT(e.id) as employee_count,
                m.full_name as manager_name
            FROM departments d
            LEFT JOIN employees e ON d.id = e.department_id
            LEFT JOIN user_profiles m ON d.manager_id = m.user_id
            GROUP BY d.id
            ORDER BY d.name";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Query preparation failed: " . implode(" ", $conn->errorInfo()));
    }

    $result = $stmt->execute();
    if (!$result) {
        throw new Exception("Query execution failed: " . implode(" ", $stmt->errorInfo()));
    }

    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $departments
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'debug_info' => [
            'error_type' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
?> 