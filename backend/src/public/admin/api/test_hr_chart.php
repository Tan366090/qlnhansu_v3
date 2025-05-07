<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../../config/database.php';

try {
    // Test database connection
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Get employee counts
    $sql = "SELECT 
        COUNT(CASE WHEN status = 'active' THEN 1 END) as active,
        COUNT(CASE WHEN status = 'inactive' THEN 1 END) as inactive,
        COUNT(CASE WHEN status = 'probation' THEN 1 END) as probation
        FROM employees";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Debug information
    $debug = [
        'query' => $sql,
        'result' => $result,
        'error' => null
    ];

    // Prepare response
    $response = [
        'success' => true,
        'data' => [
            'hr' => [
                'active' => (int)$result['active'],
                'inactive' => (int)$result['inactive'],
                'probation' => (int)$result['probation']
            ]
        ],
        'debug' => $debug
    ];

    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    $response = [
        'success' => false,
        'error' => [
            'message' => 'Database error: ' . $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ],
        'debug' => [
            'query' => $sql ?? 'No query executed',
            'error' => $e->getMessage()
        ]
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => [
            'message' => 'General error: ' . $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT);
} 