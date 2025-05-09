<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    $response = ['success' => true, 'data' => []];

    // Tổng số nhân viên
    try {
        $totalEmployeesQuery = "SELECT COUNT(*) as total FROM employees WHERE status = 'active'";
        $totalEmployeesStmt = $conn->prepare($totalEmployeesQuery);
        $totalEmployeesStmt->execute();
        $response['data']['totalEmployees'] = $totalEmployeesStmt->fetch(PDO::FETCH_ASSOC)['total'];
    } catch (PDOException $e) {
        $response['data']['totalEmployees'] = 0;
        error_log("Error fetching total employees: " . $e->getMessage());
    }

    // Số nhân viên có mặt hôm nay
    try {
        $presentTodayQuery = "SELECT COUNT(DISTINCT a.employee_id) as present 
            FROM attendance a 
            INNER JOIN employees e ON a.employee_id = e.id 
            WHERE DATE(a.check_in) = CURRENT_DATE 
            AND a.status = 'present' 
            AND e.status = 'active'";
        $presentTodayStmt = $conn->prepare($presentTodayQuery);
        $presentTodayStmt->execute();
        $response['data']['presentToday'] = $presentTodayStmt->fetch(PDO::FETCH_ASSOC)['present'];
    } catch (PDOException $e) {
        $response['data']['presentToday'] = 0;
        error_log("Error fetching present today: " . $e->getMessage());
    }

    // Số nhân viên vắng mặt hôm nay
    try {
        $absentTodayQuery = "SELECT COUNT(DISTINCT a.employee_id) as absent 
            FROM attendance a 
            INNER JOIN employees e ON a.employee_id = e.id 
            WHERE DATE(a.check_in) = CURRENT_DATE 
            AND a.status = 'absent' 
            AND e.status = 'active'";
        $absentTodayStmt = $conn->prepare($absentTodayQuery);
        $absentTodayStmt->execute();
        $response['data']['absentToday'] = $absentTodayStmt->fetch(PDO::FETCH_ASSOC)['absent'];
    } catch (PDOException $e) {
        $response['data']['absentToday'] = 0;
        error_log("Error fetching absent today: " . $e->getMessage());
    }

    // Tỷ lệ đi làm đúng giờ
    try {
        $onTimeQuery = "SELECT 
            COUNT(CASE WHEN TIME(a.check_in) <= '08:30:00' THEN 1 END) * 100.0 / COUNT(*) as on_time_percentage
            FROM attendance a 
            INNER JOIN employees e ON a.employee_id = e.id 
            WHERE DATE(a.check_in) = CURRENT_DATE 
            AND a.status = 'present' 
            AND e.status = 'active'";
        $onTimeStmt = $conn->prepare($onTimeQuery);
        $onTimeStmt->execute();
        $response['data']['onTimePercentage'] = round($onTimeStmt->fetch(PDO::FETCH_ASSOC)['on_time_percentage'], 1);
    } catch (PDOException $e) {
        $response['data']['onTimePercentage'] = 0;
        error_log("Error fetching on-time percentage: " . $e->getMessage());
    }

    echo json_encode($response);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 