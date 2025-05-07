<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../services/DataStore.php';

use App\Services\DataStore;

try {
    $dataStore = DataStore::getInstance();
    
    // Kiểm tra trạng thái cache
    $cacheStatus = $dataStore->checkStorageStatus();
    
    // Xóa cache nếu cần
    if (isset($_GET['clear_cache']) && $_GET['clear_cache'] == '1') {
        $dataStore->clearAllCache();
        $cacheStatus = $dataStore->checkStorageStatus();
        $cacheStatus['message'] = 'Cache đã được xóa thành công';
    }
    
    $result = [
        'success' => true,
        'cache_status' => $cacheStatus
    ];
    
} catch (Exception $e) {
    $result = [
        'success' => false,
        'message' => 'Kiểm tra cache thất bại: ' . $e->getMessage()
    ];
}

header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?> 