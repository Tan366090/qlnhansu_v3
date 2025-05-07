<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Get total employees
    $query = "SELECT COUNT(*) as total FROM employees WHERE status = 'active'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $totalEmployees = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get active employees
    $query = "SELECT COUNT(*) as active FROM employees WHERE status = 'active'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $activeEmployees = $stmt->fetch(PDO::FETCH_ASSOC)['active'];

    // Get pending leaves
    $query = "SELECT COUNT(*) as pending FROM leave_requests WHERE status = 'pending'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $pendingLeaves = $stmt->fetch(PDO::FETCH_ASSOC)['pending'];

    // Get monthly salary
    $query = "SELECT SUM(salary) as total FROM employees WHERE status = 'active'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $monthlySalary = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    echo json_encode([
        'success' => true,
        'data' => [
            'totalEmployees' => $totalEmployees,
            'activeEmployees' => $activeEmployees,
            'pendingLeaves' => $pendingLeaves,
            'monthlySalary' => $monthlySalary
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} 