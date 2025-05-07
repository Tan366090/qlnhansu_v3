<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Get all training records
    $query = "SELECT 
                t.id,
                t.title,
                t.description,
                t.start_date,
                t.end_date,
                t.trainer,
                t.location,
                t.max_participants,
                t.status,
                COUNT(tp.id) as current_participants,
                t.created_at,
                t.updated_at
            FROM trainings t
            LEFT JOIN training_participants tp ON t.id = tp.training_id
            GROUP BY t.id
            ORDER BY t.start_date DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute();

    $trainings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the data
    $formattedTrainings = array_map(function($train) {
        return [
            'id' => $train['id'],
            'title' => $train['title'],
            'description' => $train['description'],
            'start_date' => $train['start_date'],
            'end_date' => $train['end_date'],
            'trainer' => $train['trainer'],
            'location' => $train['location'],
            'max_participants' => $train['max_participants'],
            'current_participants' => $train['current_participants'],
            'status' => $train['status'],
            'created_at' => $train['created_at'],
            'updated_at' => $train['updated_at']
        ];
    }, $trainings);

    echo json_encode([
        'success' => true,
        'data' => $formattedTrainings
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} 