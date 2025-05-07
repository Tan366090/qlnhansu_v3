<?php
// Bật báo lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Kiểm tra API đăng nhập...\n\n";

$api_url = 'http://localhost/QLNhanSu_version1/backend/src/api/auth/login.php';

function testLogin($username, $password) {
    global $api_url;
    
    echo "Test đăng nhập với username: $username\n";
    
    $data = json_encode([
        'username' => $username,
        'password' => $password
    ]);
    
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data)
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        echo "Lỗi CURL: " . curl_error($ch) . "\n";
    } else {
        echo "HTTP Status Code: " . $http_code . "\n";
        echo "Response: " . $response . "\n";
    }
    
    curl_close($ch);
    echo "----------------------------------------\n\n";
}

// Test case 1: Đăng nhập với tài khoản admin
testLogin('admin', 'admin123');

// Test case 2: Đăng nhập với tài khoản manager
testLogin('manager', 'manager123');

// Test case 3: Đăng nhập với tài khoản employee
testLogin('employee1', 'employee123');

// Test case 4: Đăng nhập với mật khẩu sai
testLogin('admin', 'wrongpassword');

// Test case 5: Đăng nhập với username không tồn tại
testLogin('nonexistent', 'password'); 