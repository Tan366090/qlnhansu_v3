<?php
// Bật hiển thị lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Kiểm tra xem DataStore.php có tồn tại không
$dataStorePath = __DIR__ . '/../../services/DataStore.php';
if (!file_exists($dataStorePath)) {
    die(json_encode([
        'success' => false,
        'message' => 'DataStore.php not found at: ' . $dataStorePath
    ]));
}

require_once $dataStorePath;

use App\Services\DataStore;

header('Content-Type: application/json');

try {
    // Kiểm tra xem class DataStore có tồn tại không
    if (!class_exists('App\Services\DataStore')) {
        throw new Exception('DataStore class not found');
    }

    $dataStore = DataStore::getInstance();
    
    // Lấy dữ liệu từ các bảng chính
    $tables = [
        'employees',
        'departments',
        'positions',
        'performances',
        'payroll',
        'leaves',
        'trainings',
        'tasks'
    ];
    
    $results = [];
    foreach ($tables as $table) {
        try {
            $data = $dataStore->getData($table);
            $results[$table] = [
                'count' => count($data),
                'sample' => array_slice($data, 0, 3) // Lấy 3 bản ghi đầu tiên làm mẫu
            ];
        } catch (Exception $e) {
            $results[$table] = [
                'error' => $e->getMessage()
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $results
    ]);
} catch (Exception $e) {
    error_log("Storage test failed: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Storage test failed: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?> 