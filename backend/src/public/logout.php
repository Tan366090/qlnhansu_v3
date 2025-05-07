<?php
// Bật hiển thị lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Đảm bảo header JSON được gửi trước
header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../backend/src/config/SessionManager.php';
    
    $sessionManager = SessionManager::getInstance();
    $sessionManager->init();
    $sessionManager->destroy();
    
    echo json_encode([
        'success' => true,
        'message' => 'Đăng xuất thành công'
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} 