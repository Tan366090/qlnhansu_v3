<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Lấy tổng số nhân viên
    $stmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE role_id = 4");
    $totalEmployees = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Lấy tổng số phòng ban
    $stmt = $conn->query("SELECT COUNT(*) as total FROM departments");
    $totalDepartments = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Lấy hiệu suất trung bình từ bảng performances
    $stmt = $conn->query("
        SELECT AVG(performance_score) as avg_score 
        FROM performances pe 
        JOIN employees e ON pe.employee_id = e.id
    ");
    $avgPerformance = round($stmt->fetch(PDO::FETCH_ASSOC)['avg_score'] ?? 0, 2);

    // Lấy tổng lương từ bảng payroll
    $stmt = $conn->query("
        SELECT SUM(total_salary) as total 
        FROM payroll 
        WHERE payroll_month = MONTH(CURRENT_DATE()) 
        AND payroll_year = YEAR(CURRENT_DATE())
    ");
    $totalSalary = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // Lấy thống kê theo phòng ban
    $stmt = $conn->query("
        SELECT d.name, COUNT(e.id) as count 
        FROM departments d 
        LEFT JOIN employees e ON d.id = e.department_id 
        GROUP BY d.id, d.name
    ");
    $departmentStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Lấy thống kê giới tính từ user_profiles
    $stmt = $conn->query("
        SELECT 
            SUM(CASE WHEN gender = 'Male' OR gender = 'Nam' THEN 1 ELSE 0 END) as male,
            SUM(CASE WHEN gender = 'Female' OR gender = 'Nữ' THEN 1 ELSE 0 END) as female
        FROM user_profiles
    ");
    $genderStats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Lấy hiệu suất theo phòng ban
    $stmt = $conn->query("
        SELECT d.name, 
               COALESCE(AVG(pe.performance_score), 0) as avg_score
        FROM departments d
        LEFT JOIN employees e ON d.id = e.department_id
        LEFT JOIN performances pe ON e.id = pe.employee_id
        GROUP BY d.id, d.name
    ");
    $performanceStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Lấy thống kê nghỉ phép 6 tháng gần nhất
    $stmt = $conn->query("
        SELECT 
            DATE_FORMAT(start_date, '%Y-%m') as month,
            COUNT(*) as count
        FROM leaves
        WHERE start_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(start_date, '%Y-%m')
        ORDER BY month
    ");
    $leaveStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Lấy hoạt động gần đây từ bảng activities
    $stmt = $conn->query("
        SELECT 
            created_at as time,
            type as action,
            description as details,
            (SELECT full_name FROM user_profiles WHERE user_id = a.user_id) as user,
            status
        FROM activities a
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Chuẩn bị dữ liệu trả về
    $response = [
        'totalEmployees' => $totalEmployees,
        'totalDepartments' => $totalDepartments,
        'avgPerformance' => $avgPerformance,
        'totalSalary' => $totalSalary,
        'departmentStats' => [
            'labels' => array_column($departmentStats, 'name'),
            'values' => array_column($departmentStats, 'count')
        ],
        'genderStats' => $genderStats,
        'performanceStats' => [
            'labels' => array_column($performanceStats, 'name'),
            'values' => array_map(function($item) {
                return round($item['avg_score'], 2);
            }, $performanceStats)
        ],
        'leaveStats' => [
            'labels' => array_column($leaveStats, 'month'),
            'values' => array_column($leaveStats, 'count')
        ],
        'recentActivities' => array_map(function($activity) {
            return [
                'time' => date('Y-m-d H:i:s', strtotime($activity['time'])),
                'action' => $activity['action'],
                'user' => $activity['user'] ?? 'Unknown',
                'details' => $activity['details'],
                'status' => $activity['status']
            ];
        }, $recentActivities)
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?> 