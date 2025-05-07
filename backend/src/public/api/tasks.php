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
                t.id,
                t.title,
                t.description,
                t.employee_id,
                e.full_name as employee_name,
                t.project_id,
                p.name as project_name,
                t.start_date,
                t.end_date,
                t.priority,
                t.status,
                t.progress,
                t.created_at,
                t.updated_at
            FROM tasks t
            LEFT JOIN employees e ON t.employee_id = e.id
            LEFT JOIN projects p ON t.project_id = p.id
            ORDER BY t.created_at DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute();

    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the data
    $formattedTasks = array_map(function($task) {
        return [
            'id' => $task['id'],
            'title' => $task['title'],
            'description' => $task['description'],
            'employee_id' => $task['employee_id'],
            'employee_name' => $task['employee_name'],
            'project_id' => $task['project_id'],
            'project_name' => $task['project_name'],
            'start_date' => $task['start_date'],
            'end_date' => $task['end_date'],
            'priority' => $task['priority'],
            'status' => $task['status'],
            'progress' => $task['progress'],
            'created_at' => $task['created_at'],
            'updated_at' => $task['updated_at']
        ];
    }, $tasks);

    echo json_encode([
        'success' => true,
        'data' => $formattedTasks
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} 