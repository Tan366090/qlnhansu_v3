<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../app/Models/Employee.php';
require_once __DIR__ . '/../../app/Models/Department.php';
require_once __DIR__ . '/../../app/Models/Position.php';
require_once '../../config/database.php';
require_once '../../middlewares/auth.php';

// Kiểm tra xác thực người dùng
checkAuth();
checkRole('manager');

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Lấy danh sách nhân viên
    $stmt = $conn->prepare("SELECT id, full_name, email, phone, department, position FROM users WHERE role = 'employee'");
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $employees
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi hệ thống, vui lòng thử lại sau.'
    ]);
}
?>
