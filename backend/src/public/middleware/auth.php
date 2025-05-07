<?php
// Bật hiển thị lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Sửa lại đường dẫn require
require_once __DIR__ . '/../../backend/src/config/SessionManager.php';

$sessionManager = \App\Config\SessionManager::getInstance();
$sessionManager->init();

try {
    // Kiểm tra session
    if (!$sessionManager->isAuthenticated()) {
        header('Location: /QLNhanSu/backend/src/public/login_new.html');
        exit;
    }
    
    // Lấy thông tin người dùng từ session
    $user = $sessionManager->getCurrentUser();
    
    if (!$user) {
        throw new Exception('Không tìm thấy thông tin người dùng');
    }
    
    // Lấy cấu hình roles
    $roles = require __DIR__ . '/../config/roles.php';
    $role = strtolower($user['role']);
    
    if (!isset($roles[$role])) {
        throw new Exception('Vai trò không hợp lệ');
    }
    
    // Kiểm tra quyền truy cập
    $requestedPath = $_SERVER['REQUEST_URI'];
    $allowedPaths = $roles[$role]['permissions'];
    $restrictedPaths = $roles[$role]['restricted_paths'];
    
    // Kiểm tra xem đường dẫn có bị hạn chế không
    foreach ($restrictedPaths as $restrictedPath) {
        if (fnmatch($restrictedPath, $requestedPath)) {
            throw new Exception('Bạn không có quyền truy cập trang này');
        }
    }
    
    // Nếu có quyền truy cập, cho phép tiếp tục
    return true;
    
} catch (Exception $e) {
    // Nếu có lỗi, xóa session và chuyển hướng về trang đăng nhập
    if (isset($sessionManager)) {
        $sessionManager->destroy();
    }
    
    header('Location: /QLNhanSu/backend/src/public/login_new.html');
    exit;
} 