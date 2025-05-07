<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Sử dụng autoload của Composer
require_once __DIR__ . '/vendor/autoload.php';

use App\Models\AttendanceModel;
use App\Models\EmployeeModel;

try {
    // Kiểm tra xem các class có tồn tại không
    if (!class_exists('App\\Models\\AttendanceModel')) {
        throw new Exception("Class AttendanceModel không tồn tại");
    }
    if (!class_exists('App\\Models\\EmployeeModel')) {
        throw new Exception("Class EmployeeModel không tồn tại");
    }

    // Khởi tạo các model
    $attendanceModel = new AttendanceModel();
    $employeeModel = new EmployeeModel();

    // Lấy ngày hôm nay
    $today = date('Y-m-d');

    echo "<pre>";
    echo "=== Kiểm tra hàm lấy chấm công ===\n\n";

    // Test 1: Lấy chấm công hôm nay
    echo "Test 1: Lấy chấm công hôm nay ($today)\n";
    $attendance = $attendanceModel->getAttendanceByDate($today);
    if (!empty($attendance)) {
        echo "Có " . count($attendance) . " bản ghi chấm công hôm nay:\n";
        foreach ($attendance as $record) {
            echo "- Mã NV: " . ($record['employee_code'] ?? 'N/A') . "\n";
            echo "  Tên: " . ($record['employee_name'] ?? 'N/A') . "\n";
            echo "  Phòng ban: " . ($record['department_name'] ?? 'N/A') . "\n";
            echo "  Check-in: " . ($record['check_in_time'] ?? 'N/A') . "\n";
            echo "  Check-out: " . ($record['check_out_time'] ?? 'N/A') . "\n";
            echo "  Trạng thái: " . ($record['attendance_symbol'] ?? 'N/A') . "\n";
            echo "  Ghi chú: " . ($record['notes'] ?? 'N/A') . "\n";
            echo "\n";
        }
    } else {
        echo "Không có dữ liệu chấm công hôm nay\n";
    }

    // Test 2: Lấy chấm công ngày không có dữ liệu
    $futureDate = date('Y-m-d', strtotime('+1 year'));
    echo "\nTest 2: Lấy chấm công ngày không có dữ liệu ($futureDate)\n";
    $attendance = $attendanceModel->getAttendanceByDate($futureDate);
    if (empty($attendance)) {
        echo "Đúng: Không có dữ liệu chấm công cho ngày này\n";
    } else {
        echo "Sai: Có dữ liệu chấm công cho ngày này\n";
    }

    // Test 3: Kiểm tra join với bảng departments
    echo "\nTest 3: Kiểm tra join với bảng departments\n";
    $attendance = $attendanceModel->getAttendanceByDate($today);
    if (!empty($attendance)) {
        $hasDepartment = true;
        foreach ($attendance as $record) {
            if (empty($record['department_name'])) {
                $hasDepartment = false;
                break;
            }
        }
        if ($hasDepartment) {
            echo "Đúng: Tất cả bản ghi đều có thông tin phòng ban\n";
        } else {
            echo "Sai: Có bản ghi thiếu thông tin phòng ban\n";
        }
    } else {
        echo "Không có dữ liệu để kiểm tra\n";
    }

    // Test 4: Kiểm tra thứ tự sắp xếp theo thời gian check-in
    echo "\nTest 4: Kiểm tra thứ tự sắp xếp theo thời gian check-in\n";
    $attendance = $attendanceModel->getAttendanceByDate($today);
    if (!empty($attendance)) {
        $isSorted = true;
        $prevTime = null;
        foreach ($attendance as $record) {
            if ($prevTime !== null && $record['check_in_time'] < $prevTime) {
                $isSorted = false;
                break;
            }
            $prevTime = $record['check_in_time'];
        }
        if ($isSorted) {
            echo "Đúng: Dữ liệu được sắp xếp theo thời gian check-in\n";
        } else {
            echo "Sai: Dữ liệu không được sắp xếp theo thời gian check-in\n";
        }
    } else {
        echo "Không có dữ liệu để kiểm tra\n";
    }
    echo "</pre>";

} catch (Exception $e) {
    echo "<pre>";
    echo "Lỗi: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "</pre>";
} 