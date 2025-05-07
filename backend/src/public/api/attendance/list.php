<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Models\AttendanceModel;

try {
    $attendanceModel = new AttendanceModel();
    
    // Lấy tham số từ URL
    $date = $_GET['date'] ?? 'today';
    $status = $_GET['status'] ?? 'all';
    
    // Xử lý ngày
    if ($date === 'today') {
        $date = date('Y-m-d');
    }
    
    // Lấy danh sách điểm danh
    $attendance = $attendanceModel->getAttendanceByDate($date, $status);
    
    echo json_encode([
        'success' => true,
        'data' => $attendance
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 