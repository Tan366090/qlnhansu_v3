<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    $response = ['success' => true, 'data' => []];

    // Kiểm tra và lấy dữ liệu cho từng biểu đồ
    try {
        // 1. Hiệu suất nhân viên (Performance Chart)
        $performanceQuery = "SELECT 
            QUARTER(review_date) as quarter,
            AVG(score) as avg_score
            FROM performances 
            WHERE YEAR(review_date) = YEAR(CURRENT_DATE)
            GROUP BY QUARTER(review_date)
            ORDER BY quarter";
        $performanceStmt = $conn->prepare($performanceQuery);
        $performanceStmt->execute();
        $response['data']['performance'] = $performanceStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $response['data']['performance'] = [];
        error_log("Error fetching performance data: " . $e->getMessage());
    }

    try {
        // 2. Chi phí lương (Salary Chart)
        $salaryQuery = "SELECT 
            DATE_FORMAT(payment_date, '%Y-%m') as month,
            SUM(net_salary) as total_salary
            FROM payroll 
            WHERE YEAR(payment_date) = YEAR(CURRENT_DATE)
            GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
            ORDER BY month";
        $salaryStmt = $conn->prepare($salaryQuery);
        $salaryStmt->execute();
        $response['data']['salary'] = $salaryStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $response['data']['salary'] = [];
        error_log("Error fetching salary data: " . $e->getMessage());
    }

    try {
        // 3. Thống kê nghỉ phép (Leave Chart)
        $leaveQuery = "SELECT 
            leave_type,
            COUNT(*) as count
            FROM leaves 
            WHERE YEAR(start_date) = YEAR(CURRENT_DATE)
            GROUP BY leave_type";
        $leaveStmt = $conn->prepare($leaveQuery);
        $leaveStmt->execute();
        $response['data']['leaves'] = $leaveStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $response['data']['leaves'] = [];
        error_log("Error fetching leave data: " . $e->getMessage());
    }

    try {
        // 4. Tuyển dụng (Recruitment Chart)
        $recruitmentQuery = "SELECT 
            status,
            COUNT(*) as count
            FROM job_applications 
            WHERE YEAR(applied_at) = YEAR(CURRENT_DATE)
            GROUP BY status";
        $recruitmentStmt = $conn->prepare($recruitmentQuery);
        $recruitmentStmt->execute();
        $response['data']['recruitment'] = $recruitmentStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $response['data']['recruitment'] = [];
        error_log("Error fetching recruitment data: " . $e->getMessage());
    }

    try {
        // 5. Đào tạo (Training Chart)
        $trainingQuery = "SELECT 
            category,
            COUNT(*) as participant_count
            FROM training_courses 
            WHERE YEAR(start_date) = YEAR(CURRENT_DATE)
            GROUP BY category";
        $trainingStmt = $conn->prepare($trainingQuery);
        $trainingStmt->execute();
        $response['data']['training'] = $trainingStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $response['data']['training'] = [];
        error_log("Error fetching training data: " . $e->getMessage());
    }

    try {
        // 6. Quản lý tài sản (Assets Chart)
        $assetsQuery = "SELECT 
            status,
            COUNT(*) as count
            FROM assets 
            GROUP BY status";
        $assetsStmt = $conn->prepare($assetsQuery);
        $assetsStmt->execute();
        $response['data']['assets'] = $assetsStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $response['data']['assets'] = [];
        error_log("Error fetching assets data: " . $e->getMessage());
    }

    echo json_encode($response);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 