<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    // 1. Thống kê nhân sự
    $hrQuery = "SELECT 
        COUNT(*) as total_employees,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_employees,
        SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_employees,
        SUM(CASE WHEN status = 'probation' THEN 1 ELSE 0 END) as probation_employees
        FROM employees";
    $hrStmt = $conn->prepare($hrQuery);
    $hrStmt->execute();
    $hrData = $hrStmt->fetch(PDO::FETCH_ASSOC);

    // 2. Thống kê tài chính
    $financeQuery = "SELECT 
        MONTH(payment_date) as month,
        SUM(net_salary) as total_salary
        FROM payroll 
        GROUP BY MONTH(payment_date)
        ORDER BY month";
    $financeStmt = $conn->prepare($financeQuery);
    $financeStmt->execute();
    $financeData = $financeStmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Thống kê đào tạo
    $trainingQuery = "SELECT 
        MONTH(tc.created_at) as month,
        COUNT(DISTINCT tc.id) as total_courses,
        COUNT(DISTINCT tr.id) as total_participants,
        SUM(CASE WHEN tr.status = 'completed' THEN 1 ELSE 0 END) as completed_participants,
        SUM(CASE WHEN tr.status = 'attended' THEN 1 ELSE 0 END) as attended_participants,
        AVG(tr.score) as avg_score,
        SUM(tc.cost) as total_cost
        FROM training_courses tc
        LEFT JOIN training_registrations tr ON tc.id = tr.course_id
        GROUP BY MONTH(tc.created_at)
        ORDER BY month";
    $trainingStmt = $conn->prepare($trainingQuery);
    $trainingStmt->execute();
    $trainingData = $trainingStmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Thống kê tuyển dụng - Tạm thời trả về mảng rỗng vì bảng trống
    $recruitmentData = [];

    // Format data
    $response = [
        'success' => true,
        'data' => [
            'hr' => [
                'total_employees' => (int)$hrData['total_employees'],
                'active_employees' => (string)$hrData['active_employees'],
                'inactive_employees' => (string)$hrData['inactive_employees'],
                'probation_employees' => (string)$hrData['probation_employees']
            ],
            'finance' => array_map(function($item) {
                return [
                    'month' => (int)$item['month'],
                    'total_salary' => number_format($item['total_salary'], 2, '.', '')
                ];
            }, $financeData),
            'training' => array_map(function($item) {
                return [
                    'month' => (int)$item['month'],
                    'total_courses' => (int)$item['total_courses'],
                    'total_participants' => (int)$item['total_participants'],
                    'completed_participants' => (int)$item['completed_participants'],
                    'attended_participants' => (int)$item['attended_participants'],
                    'avg_score' => number_format($item['avg_score'], 1, '.', ''),
                    'total_cost' => number_format($item['total_cost'], 2, '.', '')
                ];
            }, $trainingData),
            'recruitment' => [] // Mảng rỗng vì bảng job_applications trống
        ]
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 