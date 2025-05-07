<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // Tổng số nhân viên
    $query = "SELECT COUNT(*) as total FROM employees";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $totalEmployees = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Số nhân viên đang hoạt động
    $query = "SELECT COUNT(*) as active FROM employees WHERE status = 'active'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $activeEmployees = $stmt->fetch(PDO::FETCH_ASSOC)['active'];

    // Số nhân viên không hoạt động
    $query = "SELECT COUNT(*) as inactive FROM employees WHERE status = 'inactive'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $inactiveEmployees = $stmt->fetch(PDO::FETCH_ASSOC)['inactive'];

    // Số đơn xin nghỉ phép chờ duyệt
    $query = "SELECT COUNT(*) as pending FROM leave_requests WHERE status = 'pending'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $pendingLeaves = $stmt->fetch(PDO::FETCH_ASSOC)['pending'];

    // Tỷ lệ chấm công hôm nay
    $today = date('Y-m-d');
    $query = "SELECT 
        (SELECT COUNT(*) FROM attendance WHERE date = :today AND status = 'present') / 
        (SELECT COUNT(*) FROM employees WHERE status = 'active') * 100 as attendance_rate";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':today', $today);
    $stmt->execute();
    $todayAttendance = round($stmt->fetch(PDO::FETCH_ASSOC)['attendance_rate'], 2);

    // Tổng quỹ lương tháng
    $currentMonth = date('Y-m');
    $query = "SELECT SUM(salary) as total_salary FROM employees WHERE status = 'active'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $totalSalary = $stmt->fetch(PDO::FETCH_ASSOC)['total_salary'];

    // Trả về dữ liệu dưới dạng JSON
    echo json_encode([
        "totalEmployees" => $totalEmployees,
        "activeEmployees" => $activeEmployees,
        "inactiveEmployees" => $inactiveEmployees,
        "pendingLeaves" => $pendingLeaves,
        "todayAttendance" => $todayAttendance . "%",
        "totalSalary" => number_format($totalSalary, 0, ',', '.') . " VNĐ"
    ]);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Lỗi khi lấy dữ liệu thống kê: " . $e->getMessage()]);
}
?> 