<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../../../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT id, name, department_id FROM positions ORDER BY name ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $positions = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $positions[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'department_id' => $row['department_id']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $positions
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
} 