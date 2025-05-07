<?php
require_once __DIR__ . '/../services/DataStore.php';

use App\Services\DataStore;

try {
    $dataStore = DataStore::getInstance();
    
    // Get dashboard statistics
    $stats = [
        'total_employees' => count($dataStore->getData('employees')),
        'active_employees' => count($dataStore->getData('employees', ['status' => 'active'])),
        'departments' => $dataStore->getData('departments'),
        'recent_activities' => $dataStore->getData('activities', [], true) // Force refresh for recent activities
    ];

    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);
} catch (Exception $e) {
    error_log("Error getting dashboard stats: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error getting dashboard statistics'
    ]);
}
?> 