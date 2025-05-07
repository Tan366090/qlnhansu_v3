<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../app/Models/Employee.php';
require_once __DIR__ . '/../../app/Models/Department.php';

try {
    $database = new Database();
    $db = $database->connect();
    
    // Get total employees count
    $query = "SELECT COUNT(*) as count FROM employees WHERE status = 'active'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'count' => $result['count']
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching employees count',
        'error' => $e->getMessage()
    ]);
} 