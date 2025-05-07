<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "qlnhansu";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Lấy danh sách nhân viên active
    $sql = "SELECT id FROM employees WHERE status = 'active' LIMIT 10";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($employees)) {
        echo "Không tìm thấy nhân viên active nào!";
        exit;
    }
    
    // Xóa dữ liệu chấm công cũ của ngày hôm nay nếu có
    $sql = "DELETE FROM attendance WHERE DATE(attendance_date) = CURDATE()";
    $conn->exec($sql);
    
    // Thêm dữ liệu chấm công mẫu
    $today = date('Y-m-d');
    
    // Nhân viên đi làm đúng giờ (trước 8:00)
    $sql = "INSERT INTO attendance (employee_id, attendance_date, check_in_time, attendance_symbol) 
            VALUES (:employee_id, :date, '07:50:00', 'P')";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['employee_id' => $employees[0], 'date' => $today]);
    $stmt->execute(['employee_id' => $employees[1], 'date' => $today]);
    
    // Nhân viên đi làm trễ (sau 8:00)
    $sql = "INSERT INTO attendance (employee_id, attendance_date, check_in_time, attendance_symbol) 
            VALUES (:employee_id, :date, '08:15:00', 'P')";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['employee_id' => $employees[2], 'date' => $today]);
    $stmt->execute(['employee_id' => $employees[3], 'date' => $today]);
    
    // Nhân viên vắng mặt
    $sql = "INSERT INTO attendance (employee_id, attendance_date, attendance_symbol) 
            VALUES (:employee_id, :date, 'A')";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['employee_id' => $employees[4], 'date' => $today]);
    $stmt->execute(['employee_id' => $employees[5], 'date' => $today]);
    
    echo "Đã thêm dữ liệu chấm công mẫu thành công!\n";
    echo "2 nhân viên đi làm đúng giờ (07:50)\n";
    echo "2 nhân viên đi làm trễ (08:15)\n";
    echo "2 nhân viên vắng mặt\n";
    
    // Kiểm tra kết quả
    $sql = "SELECT 
        COUNT(CASE WHEN attendance_symbol = 'P' AND TIME(check_in_time) <= '08:00:00' THEN 1 END) as on_time,
        COUNT(CASE WHEN attendance_symbol = 'P' AND TIME(check_in_time) > '08:00:00' THEN 1 END) as late,
        COUNT(CASE WHEN attendance_symbol = 'A' THEN 1 END) as absent
    FROM attendance 
    WHERE DATE(attendance_date) = CURDATE()";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "\nKết quả thống kê:\n";
    echo "Đi làm đúng giờ: " . $result['on_time'] . "\n";
    echo "Đi làm trễ: " . $result['late'] . "\n";
    echo "Vắng mặt: " . $result['absent'] . "\n";
    
} catch(PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}

$conn = null;
?> 