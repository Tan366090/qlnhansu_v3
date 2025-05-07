<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Include database configuration
    require_once __DIR__ . '/../../config/database.php';
    
    // Get database connection
    $db = new Database();
    $conn = $db->getConnection();

    // Get today's date in UTC
    $today = gmdate('Y-m-d');
    error_log("Today's date: " . $today);
    
    // Debug query to count attendance records by status for today
    $debugCountByStatusQuery = "SELECT 
                                COUNT(CASE WHEN attendance_symbol = 'P' THEN 1 END) as present_count,
                                COUNT(CASE WHEN attendance_symbol = 'A' THEN 1 END) as absent_count,
                                COUNT(CASE WHEN attendance_symbol = 'L' THEN 1 END) as leave_count,
                                COUNT(CASE WHEN attendance_symbol = 'WFH' THEN 1 END) as wfh_count
                               FROM attendance
                               WHERE DATE(attendance_date) = :attendance_date";
    $debugCountByStatusStmt = $conn->prepare($debugCountByStatusQuery);
    $debugCountByStatusStmt->bindParam(':attendance_date', $today);
    $debugCountByStatusStmt->execute();
    $statusCounts = $debugCountByStatusStmt->fetch(PDO::FETCH_ASSOC);
    error_log("Attendance counts by status: " . json_encode($statusCounts));
    
    // Debug query to count attendance records for today
    $debugCountAttendanceQuery = "SELECT COUNT(*) as total_records
                                 FROM attendance
                                 WHERE DATE(attendance_date) = :attendance_date";
    $debugCountAttendanceStmt = $conn->prepare($debugCountAttendanceQuery);
    $debugCountAttendanceStmt->bindParam(':attendance_date', $today);
    $debugCountAttendanceStmt->execute();
    $attendanceCount = $debugCountAttendanceStmt->fetch(PDO::FETCH_ASSOC)['total_records'];
    error_log("Total attendance records for today: " . $attendanceCount);
    
    // Debug query to check attendance data for today with more details
    $debugAttendanceDetailsQuery = "SELECT 
                                    e.employee_code,
                                    e.full_name,
                                    a.attendance_symbol,
                                    a.check_in_time,
                                    a.check_out_time,
                                    a.attendance_date,
                                    CASE 
                                        WHEN a.attendance_symbol = 'P' AND TIME(a.check_in_time) <= '08:30:00' THEN 'on_time'
                                        WHEN a.attendance_symbol = 'P' AND TIME(a.check_in_time) > '08:30:00' THEN 'late'
                                        WHEN a.attendance_symbol = 'A' THEN 'absent'
                                        WHEN a.attendance_symbol IS NULL THEN 'not_recorded'
                                        ELSE a.attendance_symbol
                                    END as status
                                  FROM employees e
                                  LEFT JOIN attendance a ON e.id = a.employee_id 
                                    AND DATE(a.attendance_date) = :attendance_date
                                  WHERE e.status = 'active'
                                  ORDER BY e.employee_code";
    $debugAttendanceDetailsStmt = $conn->prepare($debugAttendanceDetailsQuery);
    $debugAttendanceDetailsStmt->bindParam(':attendance_date', $today);
    $debugAttendanceDetailsStmt->execute();
    $attendanceDetails = $debugAttendanceDetailsStmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Attendance details: " . json_encode($attendanceDetails));
    
    // Debug query to check attendance data for today
    $debugAttendanceQuery = "SELECT a.*, e.employee_code, e.full_name
                            FROM attendance a
                            JOIN employees e ON a.employee_id = e.id
                            WHERE DATE(a.attendance_date) = :attendance_date";
    $debugAttendanceStmt = $conn->prepare($debugAttendanceQuery);
    $debugAttendanceStmt->bindParam(':attendance_date', $today);
    $debugAttendanceStmt->execute();
    $todayAttendance = $debugAttendanceStmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Today's attendance: " . json_encode($todayAttendance));
    
    // Debug query to check all employees
    $debugAllEmployeesQuery = "SELECT id, employee_code, status, department_id, position_id 
                              FROM employees 
                              ORDER BY id";
    $debugAllEmployeesStmt = $conn->prepare($debugAllEmployeesQuery);
    $debugAllEmployeesStmt->execute();
    $allEmployees = $debugAllEmployeesStmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("All employees: " . json_encode($allEmployees));
    
    // Debug query to check active employees
    $debugEmployeeQuery = "SELECT id, employee_code, status 
                          FROM employees 
                          WHERE status = 'active'";
    $debugEmployeeStmt = $conn->prepare($debugEmployeeQuery);
    $debugEmployeeStmt->execute();
    $activeEmployees = $debugEmployeeStmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Active employees: " . json_encode($activeEmployees));
    
    // Count active employees directly from database
    $employeeQuery = "SELECT COUNT(*) as total_active 
                      FROM employees 
                      WHERE status = 'active'";
    $employeeStmt = $conn->prepare($employeeQuery);
    $employeeStmt->execute();
    $employeeCount = $employeeStmt->fetch(PDO::FETCH_ASSOC)['total_active'];
    error_log("Total active employees: " . $employeeCount);
    
    // Query to get attendance statistics for today
    $attendanceQuery = "SELECT 
        COUNT(CASE WHEN a.attendance_symbol = 'P' THEN 1 END) as present_count,
        COUNT(CASE WHEN a.attendance_symbol = 'A' THEN 1 END) as absent_count,
        COUNT(CASE WHEN a.attendance_symbol = 'P' 
                   AND TIME(a.check_in_time) <= '08:30:00' THEN 1 END) as on_time_count,
        COUNT(CASE WHEN a.attendance_symbol = 'P' 
                   AND TIME(a.check_in_time) > '08:30:00' THEN 1 END) as late_count,
        COUNT(CASE WHEN a.attendance_symbol IS NULL THEN 1 END) as not_recorded_count,
        COUNT(CASE WHEN a.attendance_symbol = 'L' THEN 1 END) as leave_count,
        COUNT(CASE WHEN a.attendance_symbol = 'WFH' THEN 1 END) as wfh_count,
        COUNT(*) as total_count,
        GROUP_CONCAT(CONCAT(e.employee_code, ':', a.attendance_symbol) SEPARATOR ',') as employee_status,
        GROUP_CONCAT(CONCAT(e.employee_code, ':', TIME(a.check_in_time)) SEPARATOR ',') as check_in_times,
        GROUP_CONCAT(CONCAT(e.employee_code, ':', e.status) SEPARATOR ',') as employee_statuses
    FROM employees e
    LEFT JOIN attendance a ON e.id = a.employee_id AND DATE(a.attendance_date) = :attendance_date
    WHERE e.status = 'active'";
    
    error_log("Attendance query: " . $attendanceQuery);
    error_log("Parameters: attendance_date=" . $today);
    
    $stmt = $conn->prepare($attendanceQuery);
    $stmt->bindParam(':attendance_date', $today);
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log("Attendance stats: " . json_encode($stats));
    
    // Calculate percentages
    $presentCount = (int)$stats['present_count'];
    $onTimeCount = (int)$stats['on_time_count'];
    $onTimePercentage = $presentCount > 0 ? round(($onTimeCount / $presentCount) * 100) : 0;
    
    // Debug query to count attendance records by status and employee for today
    $debugCountByStatusAndEmployeeQuery = "SELECT 
                                            e.employee_code,
                                            e.full_name,
                                            a.attendance_symbol,
                                            a.check_in_time,
                                            a.check_out_time
                                          FROM employees e
                                          LEFT JOIN attendance a ON e.id = a.employee_id 
                                            AND DATE(a.attendance_date) = :attendance_date
                                          WHERE e.status = 'active'
                                          ORDER BY e.employee_code";
    $debugCountByStatusAndEmployeeStmt = $conn->prepare($debugCountByStatusAndEmployeeQuery);
    $debugCountByStatusAndEmployeeStmt->bindParam(':attendance_date', $today);
    $debugCountByStatusAndEmployeeStmt->execute();
    $statusAndEmployeeCounts = $debugCountByStatusAndEmployeeStmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Attendance counts by status and employee: " . json_encode($statusAndEmployeeCounts));
    
    // Prepare response
    $response = [
        'success' => true,
        'data' => [
            'totalEmployees' => (int)$employeeCount,
            'presentToday' => $presentCount,
            'absentToday' => (int)$stats['absent_count'],
            'lateToday' => (int)$stats['late_count'],
            'onTimeCount' => $onTimeCount,
            'onTimePercentage' => $onTimePercentage,
            'notRecordedCount' => (int)$stats['not_recorded_count'],
            'leaveCount' => (int)$stats['leave_count'],
            'wfhCount' => (int)$stats['wfh_count'],
            'totalCount' => (int)$stats['total_count'],
            'employeeStatus' => $stats['employee_status'],
            'checkInTimes' => $stats['check_in_times'],
            'employeeStatuses' => $stats['employee_statuses'],
            'debug' => [
                'todayAttendance' => $todayAttendance,
                'attendanceDetails' => $attendanceDetails
            ]
        ]
    ];
    
    error_log("Final response: " . json_encode($response));
    echo json_encode($response);

} catch (Exception $e) {
    error_log("General Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?> 