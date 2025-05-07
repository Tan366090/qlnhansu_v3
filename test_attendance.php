<?php
// Kết nối database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "qlnhansu";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("set names utf8");
    
    // Lấy thống kê chấm công
    $sql = "SELECT 
        DATE(a.attendance_date) as work_date,
        (SELECT COUNT(*) FROM employees WHERE status = 'active') as total_employees,
        COUNT(DISTINCT CASE 
            WHEN a.attendance_symbol = 'P' AND TIME(a.check_in_time) <= TIME(ws.start_time) THEN a.employee_id 
            ELSE NULL 
        END) as on_time_employees,
        COUNT(DISTINCT CASE 
            WHEN a.attendance_symbol = 'P' AND TIME(a.check_in_time) > TIME(ws.start_time) THEN a.employee_id 
            ELSE NULL 
        END) as late_employees,
        COUNT(DISTINCT CASE 
            WHEN a.attendance_symbol = 'A' THEN a.employee_id 
            ELSE NULL 
        END) as absent_employees
    FROM 
        attendance a
    LEFT JOIN 
        work_schedules ws ON a.employee_id = ws.employee_id AND a.attendance_date = ws.work_date
    WHERE 
        a.attendance_date = CURDATE()
    GROUP BY 
        a.attendance_date";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Nếu không có dữ liệu cho ngày hôm nay, trả về 0
    if (!$result) {
        $result = [
            'work_date' => date('Y-m-d'),
            'total_employees' => 0,
            'on_time_employees' => 0,
            'late_employees' => 0,
            'absent_employees' => 0
        ];
    }
    
    // Tính tỷ lệ đi làm đúng giờ
    $onTimePercentage = 0;
    if ($result['total_employees'] > 0) {
        $onTimePercentage = round(($result['on_time_employees'] / $result['total_employees']) * 100, 1);
    }
    
    // Trả về dữ liệu dạng JSON
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => [
            'totalEmployees' => (int)$result['total_employees'],
            'presentToday' => (int)($result['on_time_employees'] + $result['late_employees']),
            'absentToday' => (int)$result['absent_employees'],
            'onTimePercentage' => (float)$onTimePercentage
        ]
    ]);

} catch(PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$conn = null;
?> 