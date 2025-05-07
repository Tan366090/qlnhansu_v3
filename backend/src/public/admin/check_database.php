<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../services/DataStore.php';

use App\Services\DataStore;

try {
    // Khởi tạo DataStore
    $dataStore = DataStore::getInstance();
    
    // Kiểm tra trạng thái cache của DataStore
    $cacheStatus = $dataStore->checkStorageStatus();
    
    // Xóa cache nếu cần
    if (isset($_GET['clear_cache']) && $_GET['clear_cache'] == '1') {
        $dataStore->clearAllCache();
        $cacheStatus = $dataStore->checkStorageStatus();
        $cacheStatus['message'] = 'Cache đã được xóa thành công';
    }
    
    $result = [
        'success' => true,
        'data_store_status' => [
            'cache_size' => $cacheStatus['cache_size'],
            'cache_time' => $cacheStatus['cache_time'],
            'database_connection' => $cacheStatus['database_connection'],
            'database_name' => $cacheStatus['database_name'],
            'cache_items' => $cacheStatus['cache_items']
        ]
    ];
    
} catch (Exception $e) {
    $result = [
        'success' => false,
        'message' => 'Kiểm tra DataStore thất bại: ' . $e->getMessage()
    ];
}

header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?> 