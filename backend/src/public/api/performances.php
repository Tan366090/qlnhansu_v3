<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Max-Age: 3600');
    header('Access-Control-Allow-Credentials: true');
    http_response_code(200);
    exit();
}

// Set CORS headers for all responses
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Max-Age: 3600');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Get all performance records
    $query = "SELECT 
                p.id,
                p.employee_id,
                e.full_name as employee_name,
                p.evaluation_date,
                p.performance_score,
                p.strengths,
                p.areas_for_improvement,
                p.goals,
                p.created_at,
                p.updated_at
            FROM performances p
            LEFT JOIN employees e ON p.employee_id = e.id
            ORDER BY p.evaluation_date DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute();

    $performances = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the data
    $formattedPerformances = array_map(function($perf) {
        return [
            'id' => $perf['id'],
            'employee_id' => $perf['employee_id'],
            'employee_name' => $perf['employee_name'],
            'evaluation_date' => $perf['evaluation_date'],
            'performance_score' => $perf['performance_score'],
            'strengths' => $perf['strengths'],
            'areas_for_improvement' => $perf['areas_for_improvement'],
            'goals' => $perf['goals'],
            'created_at' => $perf['created_at'],
            'updated_at' => $perf['updated_at']
        ];
    }, $performances);

    echo json_encode([
        'success' => true,
        'data' => $formattedPerformances
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} 