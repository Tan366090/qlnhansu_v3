<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once '../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Initialize response array
    $response = [
        'success' => true,
        'data' => []
    ];

    // 1. Attendance Data (Last 7 days)
    $attendanceQuery = "SELECT 
        attendance_date as day,
        COUNT(*) as total,
        SUM(CASE WHEN attendance_symbol = 'P' THEN 1 ELSE 0 END) as present
        FROM attendance 
        WHERE attendance_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY attendance_date
        ORDER BY day DESC";
    
    $stmt = $conn->prepare($attendanceQuery);
    $stmt->execute();
    $response['data']['attendance'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Department Distribution
    $departmentQuery = "SELECT 
        d.name as department_name,
        COUNT(e.id) as employee_count
        FROM departments d
        LEFT JOIN employees e ON d.id = e.department_id
        GROUP BY d.id, d.name";
    
    $stmt = $conn->prepare($departmentQuery);
    $stmt->execute();
    $response['data']['departments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Performance Data (Last 4 quarters)
    $performanceQuery = "SELECT 
        QUARTER(review_date) as quarter,
        AVG(performance_score) as avg_score
        FROM performances
        WHERE review_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
        GROUP BY QUARTER(review_date)
        ORDER BY quarter";
    
    $stmt = $conn->prepare($performanceQuery);
    $stmt->execute();
    $response['data']['performance'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Salary Data (Last 6 months)
    $salaryQuery = "SELECT 
        CONCAT(payroll_year, '-', LPAD(payroll_month, 2, '0')) as month,
        SUM(total_salary) as total_salary
        FROM payroll
        WHERE (payroll_year = YEAR(CURDATE()) AND payroll_month >= MONTH(CURDATE()) - 6)
           OR (payroll_year = YEAR(CURDATE()) - 1 AND payroll_month >= MONTH(CURDATE()) + 6)
        GROUP BY payroll_year, payroll_month
        ORDER BY payroll_year, payroll_month";
    
    $stmt = $conn->prepare($salaryQuery);
    $stmt->execute();
    $response['data']['salary'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Leave Statistics
    $leaveQuery = "SELECT 
        type,
        COUNT(*) as count
        FROM leave_requests
        WHERE status = 'approved'
        AND start_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
        GROUP BY type";
    
    $stmt = $conn->prepare($leaveQuery);
    $stmt->execute();
    $response['data']['leaves'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 6. Recruitment Status
    $recruitmentQuery = "SELECT 
        status,
        COUNT(*) as count
        FROM candidates
        GROUP BY status";
    
    $stmt = $conn->prepare($recruitmentQuery);
    $stmt->execute();
    $response['data']['recruitment'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 7. Training Statistics
    $trainingQuery = "SELECT 
        category,
        COUNT(DISTINCT employee_id) as participant_count
        FROM training_sessions
        WHERE session_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY category";
    
    $stmt = $conn->prepare($trainingQuery);
    $stmt->execute();
    $response['data']['training'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 8. Asset Status
    $assetsQuery = "SELECT 
        status,
        COUNT(*) as count
        FROM assets
        GROUP BY status";
    
    $stmt = $conn->prepare($assetsQuery);
    $stmt->execute();
    $response['data']['assets'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the response
    echo json_encode($response);

} catch (PDOException $e) {
    // Handle database errors
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    // Handle other errors
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
} 