<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../app/Models/Candidate.php';
require_once __DIR__ . '/../../app/Models/Position.php';

try {
    $database = new Database();
    $db = $database->connect();
    
    // Get new candidates
    $query = "SELECT * FROM candidates WHERE status = 'new' ORDER BY created_at DESC LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the response
    $formattedCandidates = array_map(function($candidate) {
        return [
            'id' => $candidate['id'],
            'name' => $candidate['full_name'],
            'position' => $candidate['position'],
            'application_date' => $candidate['created_at'],
            'status' => ucfirst($candidate['status'])
        ];
    }, $candidates);
    
    echo json_encode([
        'success' => true,
        'data' => $formattedCandidates
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching candidates',
        'error' => $e->getMessage()
    ]);
} 