<?php
// ...existing code...

// Kiểm tra quyền truy cập
session_start();
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['message' => 'User not authenticated']);
    exit();
}

if ($_SESSION['user']['role_name'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['message' => 'Insufficient permissions']);
    exit();
}

// Xử lý logic API
echo json_encode(['message' => 'Access granted']);

// ...existing code...
?>