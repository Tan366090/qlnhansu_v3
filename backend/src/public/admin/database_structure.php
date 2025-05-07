<?php
// Bật báo cáo lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Kiểm tra file config
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

    // Lấy danh sách các bảng
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo json_encode([
            'success' => false,
            'message' => 'No tables found in database'
        ]);
        exit;
    }
    
    $databaseStructure = [];
    
    foreach ($tables as $table) {
        // Lấy thông tin cột của mỗi bảng
        $columns = $conn->query("SHOW COLUMNS FROM $table")->fetchAll(PDO::FETCH_ASSOC);
        
        // Lấy thông tin khóa ngoại
        $foreignKeys = $conn->query("
            SELECT 
                TABLE_NAME,
                COLUMN_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME
            FROM
                INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE
                REFERENCED_TABLE_SCHEMA = 'qlnhansu'
                AND TABLE_NAME = '$table'
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        $databaseStructure[$table] = [
            'columns' => $columns,
            'foreign_keys' => $foreignKeys
        ];
    }
    
    echo json_encode([
        'success' => true,
        'database' => 'qlnhansu',
        'structure' => $databaseStructure
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