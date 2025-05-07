<?php
require_once 'public/config/session.php';
require_once 'public/config/roles.php';
require_once 'public/middleware/auth.php';

// Khởi tạo session với các tham số bảo mật
ini_set('session.cookie_path', '/QLNhanSu_version1/');
ini_set('session.cookie_domain', '');
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.cookie_samesite', 'Lax');

session_start();

// Kiểm tra nếu đã đăng nhập
if (isset($_SESSION['user_id']) && isset($_SESSION['username']) && isset($_SESSION['role'])) {
    $role = $_SESSION['role'];
    $roles = require 'public/config/roles.php';
    
    if (isset($roles[$role])) {
        header("Location: /QLNhanSu_version1/public/dashboard.html");
        exit();
    }
}

// Chuyển hướng đến trang login.new.html
header("Location: /QLNhanSu_version1/public/login.new.html");
exit();
?> 