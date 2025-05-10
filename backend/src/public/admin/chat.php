<?php
header('Content-Type: application/json');
require_once 'ChatEngine.php';

$conn = new mysqli("localhost", "root", "", "qlnhansu");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$chatEngine = new ChatEngine($conn);

// Lấy userId và query từ request (GET hoặc POST)
$userId = $_POST['user_id'] ?? $_GET['user_id'] ?? session_id();
$query = $_POST['query'] ?? $_GET['query'] ?? '';

if (empty($query)) {
    echo json_encode(['success' => false, 'error' => 'Missing query']);
    exit;
}

$response = $chatEngine->chat($userId, $query);

// Nếu response là JSON (biểu đồ, đa phương tiện), trả về luôn
$data = json_decode($response, true);
if (is_array($data) && isset($data['type'])) {
    echo json_encode(['success' => true, 'data' => $data]);
} else {
    echo json_encode(['success' => true, 'response' => $response]);
} 