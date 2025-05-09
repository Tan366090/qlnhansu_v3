<?php
header('Content-Type: application/json');

try {
    $db = new PDO('mysql:host=localhost;dbname=qlnhansu', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get HR data
    $hrData = $db->query("
        SELECT 
            COUNT(*) as total_employees,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_employees,
            SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_employees,
            SUM(CASE WHEN status = 'probation' THEN 1 ELSE 0 END) as probation_employees
        FROM employees
    ")->fetch(PDO::FETCH_ASSOC);

    /* Commented out Finance data query
    // Get finance data for 2023
    $financeData = $db->query("
        SELECT 
            MONTH(pay_period_start) as month,
            SUM(gross_salary) as total_salary
        FROM payroll 
        WHERE YEAR(pay_period_start) = 2023
        GROUP BY MONTH(pay_period_start)
        ORDER BY month
    ")->fetchAll(PDO::FETCH_ASSOC);
    */

    /* Commented out Training data query
    // Get training data for 2023
    $trainingData = $db->query("
        SELECT 
            MONTH(tc.created_at) as month,
            COUNT(DISTINCT tc.id) as total_courses,
            COUNT(DISTINCT tr.employee_id) as total_participants,
            COUNT(DISTINCT CASE WHEN tr.status = 'completed' THEN tr.employee_id END) as completed_participants,
            COUNT(DISTINCT CASE WHEN tr.status = 'attended' THEN tr.employee_id END) as attended_participants,
            ROUND(AVG(CASE WHEN tr.status = 'completed' THEN tr.score END), 1) as avg_score,
            SUM(tc.cost) as total_cost
        FROM training_courses tc
        LEFT JOIN training_registrations tr ON tc.id = tr.course_id
        WHERE YEAR(tc.created_at) = 2023
        GROUP BY MONTH(tc.created_at)
        ORDER BY month
    ")->fetchAll(PDO::FETCH_ASSOC);
    */

    /* Commented out Recruitment data query
    // Get recruitment data for 2023
    $recruitmentData = $db->query("
        SELECT 
            MONTH(applied_at) as month,
            COUNT(*) as total_applications,
            SUM(CASE WHEN status = 'interviewing' THEN 1 ELSE 0 END) as interviewed,
            SUM(CASE WHEN status = 'hired' THEN 1 ELSE 0 END) as hired
        FROM job_applications
        WHERE YEAR(applied_at) = 2023
        GROUP BY MONTH(applied_at)
        ORDER BY month
    ")->fetchAll(PDO::FETCH_ASSOC);
    */

    // Get debug data
    $debugData = $db->query("
        SELECT 
            MONTH(pay_period_start) as month,
            YEAR(pay_period_start) as year,
            COUNT(*) as record_count,
            SUM(gross_salary) as total_salary,
            status
        FROM payroll
        GROUP BY YEAR(pay_period_start), MONTH(pay_period_start), status
        ORDER BY year DESC, month DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Format the response
    $response = [
        'success' => true,
        'data' => [
            'hr' => $hrData,
            /* Commented out unused chart data
            'finance' => $financeData,
            'training' => $trainingData,
            'recruitment' => $recruitmentData,
            */
            'debug' => $debugData
        ]
    ];

    echo json_encode($response);

} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 