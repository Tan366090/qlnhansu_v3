<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Get mobile app stats
    $query = "SELECT 
                id,
                user_id,
                action_type,
                created_at
            FROM mobile_app_stats
            ORDER BY created_at DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute();

    $mobileStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the data
    $formattedStats = array_map(function($stat) {
        return [
            'id' => $stat['id'],
            'user_id' => $stat['user_id'],
            'action_type' => $stat['action_type'],
            'created_at' => $stat['created_at']
        ];
    }, $mobileStats);

    echo json_encode([
        'success' => true,
        'data' => $formattedStats
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 