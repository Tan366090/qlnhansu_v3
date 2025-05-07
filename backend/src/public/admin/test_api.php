<?php
require_once __DIR__ . '/../../services/DataStore.php';

use App\Services\DataStore;

header('Content-Type: application/json');

try {
    $dataStore = DataStore::getInstance();
    
    // Test database connection
    $employees = $dataStore->getData('employees');
    $departments = $dataStore->getData('departments');
    
    echo json_encode([
        'success' => true,
        'message' => 'API is working correctly',
        'data' => [
            'employees_count' => count($employees),
            'departments_count' => count($departments)
        ]
    ]);
} catch (Exception $e) {
    error_log("API test failed: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'API test failed: ' . $e->getMessage()
    ]);
}
?> 