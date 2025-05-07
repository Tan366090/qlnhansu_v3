<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../vendor/autoload.php';

// Kết nối đến Node.js server
$nodeServer = 'http://localhost:3000';

// Xử lý request
$method = $_SERVER['REQUEST_METHOD'];
$endpoint = $_GET['endpoint'] ?? '';

switch ($method) {
    case 'GET':
        switch ($endpoint) {
            case 'hr-trends':
                $response = file_get_contents($nodeServer . '/api/ai/hr-trends');
                echo $response;
                break;
            case 'sentiment':
                $response = file_get_contents($nodeServer . '/api/ai/sentiment');
                echo $response;
                break;
            default:
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
                break;
        }
        break;
    case 'OPTIONS':
        http_response_code(200);
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
} 