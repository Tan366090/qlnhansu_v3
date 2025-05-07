<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';

try {
    // Test database connection
    $db = new PDO("mysql:host=localhost;dbname=qlnhansu", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test queries
    $queries = [
        'users' => "SELECT COUNT(*) as count FROM users",
        'employees' => "SELECT COUNT(*) as count FROM employees",
        'departments' => "SELECT COUNT(*) as count FROM departments",
        'positions' => "SELECT COUNT(*) as count FROM positions",
        'user_profiles' => "SELECT COUNT(*) as count FROM user_profiles"
    ];
    
    $results = [];
    foreach ($queries as $table => $query) {
        $stmt = $db->query($query);
        $results[$table] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }
    
    // Test sample data retrieval
    $sampleData = [
        'users' => $db->query("SELECT user_id, username, email FROM users LIMIT 3")->fetchAll(PDO::FETCH_ASSOC),
        'departments' => $db->query("SELECT id, name FROM departments LIMIT 3")->fetchAll(PDO::FETCH_ASSOC),
        'positions' => $db->query("SELECT id, name FROM positions LIMIT 3")->fetchAll(PDO::FETCH_ASSOC)
    ];
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Connection successful',
        'counts' => $results,
        'sample_data' => $sampleData
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Connection failed: ' . $e->getMessage()
    ]);
} 