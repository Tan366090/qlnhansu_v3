<?php
header('Content-Type: application/json');
require_once '../../config/database.php';
require_once '../../middlewares/auth.php';
require_once __DIR__ . '/../../app/Models/Employee.php';
require_once __DIR__ . '/../../app/Models/Project.php';
require_once __DIR__ . '/../../app/Models/Task.php';

// Kiểm tra xác thực người dùng
checkAuth();
checkRole('manager');

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Tổng số nhân viên
    $stmt = $conn->prepare("SELECT COUNT(*) as total_employees FROM users WHERE role = 'employee'");
    $stmt->execute();
    $totalEmployees = $stmt->fetch(PDO::FETCH_ASSOC)['total_employees'];

    // Đơn nghỉ phép chờ duyệt
    $stmt = $conn->prepare("SELECT COUNT(*) as pending_leaves FROM leave_requests WHERE status = 'pending'");
    $stmt->execute();
    $pendingLeaves = $stmt->fetch(PDO::FETCH_ASSOC)['pending_leaves'];

    // Nhân viên đi làm hôm nay
    $today = date('Y-m-d');
    $stmt = $conn->prepare("SELECT COUNT(*) as today_attendance FROM attendance WHERE DATE(check_in) = ?");
    $stmt->execute([$today]);
    $todayAttendance = $stmt->fetch(PDO::FETCH_ASSOC)['today_attendance'];

    // Tổng quỹ lương tháng
    $currentMonth = date('Y-m');
    $stmt = $conn->prepare("SELECT SUM(salary) as total_salary FROM salaries WHERE DATE_FORMAT(payment_date, '%Y-%m') = ?");
    $stmt->execute([$currentMonth]);
    $totalSalary = $stmt->fetch(PDO::FETCH_ASSOC)['total_salary'];

    echo json_encode([
        'success' => true,
        'data' => [
            'totalEmployees' => $totalEmployees,
            'pendingLeaves' => $pendingLeaves,
            'todayAttendance' => $todayAttendance,
            'totalSalary' => $totalSalary
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi hệ thống, vui lòng thử lại sau.'
    ]);
}
?>
