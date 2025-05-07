<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Check config file
$configFile = __DIR__ . '/../config/database.php';
if (!file_exists($configFile)) {
    echo json_encode([
        'success' => false,
        'message' => 'Config file not found: ' . $configFile
    ]);
    exit;
}

require_once $configFile;

try {
    $db = new Database();
    $conn = $db->getConnection();

    if (!$conn) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to connect to database'
        ]);
        exit;
    }

    // Get all tables
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo json_encode([
            'success' => false,
            'message' => 'No tables found in database'
        ]);
        exit;
    }
    
    $databaseData = [];
    
    foreach ($tables as $table) {
        // Get all data from each table
        $stmt = $conn->prepare("SELECT * FROM $table");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $databaseData[$table] = $data;
    }
    
    echo json_encode([
        'success' => true,
        'database' => 'qlnhansu',
        'data' => $databaseData
    ], JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'error_code' => $e->getCode(),
        'error_file' => $e->getFile(),
        'error_line' => $e->getLine()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'General error: ' . $e->getMessage(),
        'error_file' => $e->getFile(),
        'error_line' => $e->getLine()
    ]);
}
?> 