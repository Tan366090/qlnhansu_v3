<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../app/Models/Training.php';
require_once __DIR__ . '/../../app/Models/Employee.php';

try {
    $database = new Database();
    $db = $database->connect();
    
    // Get upcoming training sessions
    $query = "SELECT * FROM training_sessions 
              WHERE start_date >= CURDATE() 
              ORDER BY start_date ASC 
              LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the response
    $formattedSessions = array_map(function($session) {
        return [
            'id' => $session['id'],
            'title' => $session['title'],
            'start_date' => $session['start_date'],
            'end_date' => $session['end_date'],
            'trainer' => $session['trainer'],
            'location' => $session['location']
        ];
    }, $sessions);
    
    echo json_encode([
        'success' => true,
        'sessions' => $formattedSessions
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching training sessions',
        'error' => $e->getMessage()
    ]);
} 