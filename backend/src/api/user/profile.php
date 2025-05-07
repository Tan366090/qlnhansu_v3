<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../config/database.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if it's a GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // For now, we'll return a sample profile
        // In a real application, you would get this from the database based on the authenticated user
        $profile = [
            'id' => 1,
            'username' => 'admin',
            'full_name' => 'Administrator',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'department' => 'IT Department',
            'position' => 'System Administrator',
            'avatar' => 'https://ui-avatars.com/api/?name=Administrator&background=random',
            'last_login' => date('Y-m-d H:i:s'),
            'permissions' => [
                'dashboard' => true,
                'employees' => true,
                'departments' => true,
                'positions' => true,
                'tasks' => true,
                'leaves' => true,
                'payroll' => true,
                'trainings' => true,
                'settings' => true
            ]
        ];

        echo json_encode([
            'status' => 'success',
            'data' => $profile
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Server error: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed'
    ]);
} 