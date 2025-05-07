<?php
header('Content-Type: application/json');

try {
    // Include the API file directly
    $api_file = __DIR__ . '/api/dashboard_charts.php';
    
    if (!file_exists($api_file)) {
        throw new Exception("API file not found at: " . $api_file);
    }

    // Check database configuration
    $config_file = __DIR__ . '/config/database.php';
    if (!file_exists($config_file)) {
        throw new Exception("Database config file not found at: " . $config_file);
    }

    // Try to include the config file
    $config = include $config_file;
    if (!is_array($config)) {
        throw new Exception("Invalid database configuration format");
    }

    // Start output buffering
    ob_start();
    
    // Include the API file
    include $api_file;
    
    // Get the output
    $output = ob_get_clean();
    
    // Try to decode the output as JSON
    $json_data = json_decode($output, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON response: " . json_last_error_msg() . "\nRaw output: " . $output);
    }

    // Prepare response
    $result = [
        'status' => 200,
        'file_path' => $api_file,
        'file_exists' => file_exists($api_file),
        'config_path' => $config_file,
        'config_exists' => file_exists($config_file),
        'data' => $json_data
    ];

} catch (Exception $e) {
    $result = [
        'status' => 500,
        'error' => $e->getMessage(),
        'file_path' => $api_file ?? 'Not set',
        'file_exists' => isset($api_file) ? file_exists($api_file) : false,
        'config_path' => $config_file ?? 'Not set',
        'config_exists' => isset($config_file) ? file_exists($config_file) : false,
        'trace' => $e->getTraceAsString()
    ];
}

// Output result
echo json_encode($result, JSON_PRETTY_PRINT);
?> 