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
                p.status,
                p.created_at
            FROM projects p
            ORDER BY p.created_at DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute();

    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the data
    $formattedProjects = array_map(function($project) {
        return [
            'id' => $project['id'],
            'name' => $project['name'],
            'status' => $project['status'],
            'created_at' => $project['created_at']
        ];
    }, $projects);

    echo json_encode([
        'success' => true,
        'data' => $formattedProjects
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 