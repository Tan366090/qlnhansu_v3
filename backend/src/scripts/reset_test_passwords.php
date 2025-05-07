<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../api/models/User.php';

echo "Bắt đầu cập nhật mật khẩu cho tài khoản test...\n";

try {
    $userModel = new User();

    // Reset admin password
    if ($userModel->updatePassword(1, 'admin123')) {
        echo "Đã cập nhật mật khẩu cho admin\n";
    }

    // Reset manager password
    if ($userModel->updatePassword(2, 'manager123')) {
        echo "Đã cập nhật mật khẩu cho manager\n";
    }

    // Reset employee passwords
    for ($i = 3; $i <= 10; $i++) {
        $username = "employee" . ($i - 2);
        if ($userModel->updatePassword($i, 'employee123')) {
            echo "Đã cập nhật mật khẩu cho {$username}\n";
        }
    }

    echo "\nHoàn tất cập nhật mật khẩu!\n";
    echo "Thông tin đăng nhập:\n";
    echo "- Admin: admin/admin123\n";
    echo "- Manager: manager/manager123\n";
    echo "- Employees: employee1-8/employee123\n";

} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage() . "\n";
} 