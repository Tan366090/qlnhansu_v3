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
                p.id,
                p.name,
                COUNT(e.id) as employee_count
            FROM positions p
            LEFT JOIN employees e ON p.id = e.position_id
            GROUP BY p.id
            ORDER BY p.name";

    $stmt = $conn->prepare($query);
    $stmt->execute();

    $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the data
    $formattedPositions = array_map(function($pos) {
        return [
            'id' => $pos['id'],
            'name' => $pos['name'],
            'employee_count' => $pos['employee_count']
        ];
    }, $positions);

    echo json_encode([
        'success' => true,
        'data' => $formattedPositions
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} 