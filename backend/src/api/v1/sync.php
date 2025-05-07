<?php
require_once __DIR__ . '/../services/DataStore.php';

use App\Services\DataStore;

header('Content-Type: application/json');

try {
    $dataStore = DataStore::getInstance();
    
    // Check if it's a GET request to get data
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $table = $_GET['table'] ?? null;
        
        if ($table) {
            $data = $dataStore->getTableData($table);
            if ($data === false) {
                throw new Exception("Failed to get data for table: $table");
            }
            echo json_encode(['success' => true, 'data' => $data]);
        } else {
            throw new Exception("Table name is required");
        }
    }
    // Check if it's a POST request to sync data
    else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $result = $dataStore->syncData();
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Data synced successfully']);
        } else {
            throw new Exception("Failed to sync data");
        }
    }
    // Check if it's a DELETE request to clear cache
    else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $result = $dataStore->clearCache();
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Cache cleared successfully']);
        } else {
            throw new Exception("Failed to clear cache");
        }
    }
    else {
        throw new Exception("Method not allowed");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?> 