<?php
// Bắt đầu output buffering
ob_start();

// Bật báo lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../../logs/test_login.log');

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../api/models/User.php';
require_once __DIR__ . '/../auth/SessionHelper.php';

echo "Bắt đầu kiểm tra đăng nhập...\n";

// Test case 1: Đăng nhập với tài khoản admin
echo "\nTest case 1: Đăng nhập với tài khoản admin\n";
$userModel = new User();
$result = $userModel->authenticate('admin', 'admin123');

if ($result['success']) {
    echo "Đăng nhập thành công!\n";
    echo "Thông tin user:\n";
    print_r($result['user']);
} else {
    echo "Đăng nhập thất bại: " . $result['error'] . "\n";
}

// Test case 2: Đăng nhập với tài khoản manager
echo "\nTest case 2: Đăng nhập với tài khoản manager\n";
$result = $userModel->authenticate('manager', 'manager123');

if ($result['success']) {
    echo "Đăng nhập thành công!\n";
    echo "Thông tin user:\n";
    print_r($result['user']);
} else {
    echo "Đăng nhập thất bại: " . $result['error'] . "\n";
}

// Test case 3: Đăng nhập với tài khoản employee
echo "\nTest case 3: Đăng nhập với tài khoản employee\n";
$result = $userModel->authenticate('employee1', 'employee123');

if ($result['success']) {
    echo "Đăng nhập thành công!\n";
    echo "Thông tin user:\n";
    print_r($result['user']);
} else {
    echo "Đăng nhập thất bại: " . $result['error'] . "\n";
}

// Test case 4: Đăng nhập với mật khẩu sai
echo "\nTest case 4: Đăng nhập với mật khẩu sai\n";
$result = $userModel->authenticate('admin', 'wrongpassword');

if ($result['success']) {
    echo "Đăng nhập thành công!\n";
    echo "Thông tin user:\n";
    print_r($result['user']);
} else {
    echo "Đăng nhập thất bại: " . $result['error'] . "\n";
}

// Test case 5: Đăng nhập với username không tồn tại
echo "\nTest case 5: Đăng nhập với username không tồn tại\n";
$result = $userModel->authenticate('nonexistent', 'password');

if ($result['success']) {
    echo "Đăng nhập thành công!\n";
    echo "Thông tin user:\n";
    print_r($result['user']);
} else {
    echo "Đăng nhập thất bại: " . $result['error'] . "\n";
}

// Kiểm tra session
echo "\nKiểm tra session:\n";
if (isset($result['user'])) {
    SessionHelper::setUserData($result['user']);
    print_r(SessionHelper::getCurrentUser());
} else {
    echo "Không có user data để kiểm tra session\n";
}

// Kết thúc output buffering
ob_end_flush(); 