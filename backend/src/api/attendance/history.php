<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

// Lấy ngày từ query parameter
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Lấy lịch sử chấm công kết hợp với thông tin nhân viên
    $stmt = $conn->prepare("
        SELECT 
            a.attendance_id,
            a.user_id,
            e.full_name as employee_name,
            a.attendance_date,
            a.recorded_at,
            a.attendance_symbol,
            a.notes
        FROM attendance a
        JOIN employees e ON a.user_id = e.employee_id
        WHERE a.attendance_date = ?
        ORDER BY a.recorded_at DESC
    ");

    $stmt->bind_param("s", $date);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $attendanceRecords = [];

    while ($row = $result->fetch_assoc()) {
        $attendanceRecords[] = [
            'attendance_id' => $row['attendance_id'],
            'user_id' => $row['user_id'],
            'employee_name' => $row['employee_name'],
            'attendance_date' => $row['attendance_date'],
            'recorded_at' => $row['recorded_at'],
            'attendance_symbol' => $row['attendance_symbol'],
            'notes' => $row['notes']
        ];
    }

    echo json_encode($attendanceRecords);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 