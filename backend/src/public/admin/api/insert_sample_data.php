<?php
header('Content-Type: application/json');

try {
    $db = new PDO('mysql:host=localhost;dbname=qlnhansu', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Temporarily disable foreign key checks
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");

    // Delete existing data in correct order
    $db->exec("DELETE FROM interviews");
    $db->exec("DELETE FROM job_applications");
    $db->exec("DELETE FROM training_registrations");
    $db->exec("DELETE FROM training_courses");
    $db->exec("DELETE FROM payroll");
    $db->exec("DELETE FROM job_positions");

    // Re-enable foreign key checks
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");

    // First, insert job positions
    $jobPositions = [
        [NULL, 5, 'Lập trình viên PHP', 1, 'Phát triển ứng dụng web', 'Kinh nghiệm PHP, MySQL', 'Phát triển và bảo trì ứng dụng web', 15000000.00, 25000000.00, 'open', '2023-01-01', NULL, NULL],
        [NULL, 6, 'Lập trình viên Python', 1, 'Phát triển ứng dụng AI', 'Kinh nghiệm Python, Machine Learning', 'Phát triển ứng dụng AI', 18000000.00, 28000000.00, 'open', '2023-01-01', NULL, NULL],
        [NULL, 7, 'Nhân viên Marketing', 2, 'Quảng cáo và tiếp thị', 'Kinh nghiệm Digital Marketing', 'Lập kế hoạch và thực hiện chiến dịch marketing', 10000000.00, 15000000.00, 'open', '2023-01-01', NULL, NULL],
        [NULL, 8, 'Kế toán', 3, 'Quản lý tài chính', 'Kinh nghiệm kế toán', 'Quản lý sổ sách và báo cáo tài chính', 12000000.00, 18000000.00, 'open', '2023-01-01', NULL, NULL],
        [NULL, 9, 'Nhân sự', 4, 'Quản lý nhân sự', 'Kinh nghiệm HR', 'Tuyển dụng và quản lý nhân sự', 10000000.00, 15000000.00, 'open', '2023-01-01', NULL, NULL]
    ];

    $jobPositionStmt = $db->prepare("INSERT INTO job_positions (campaign_id, position_id, title_override, department_id, description, requirements, responsibilities, salary_range_min, salary_range_max, status, posting_date, closing_date, hiring_manager_user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($jobPositions as $position) {
        $jobPositionStmt->execute($position);
    }

    // Get all job position IDs
    $jobPositionIds = $db->query("SELECT id FROM job_positions")->fetchAll(PDO::FETCH_COLUMN);

    // Insert sample payroll data for 2023 and 2024
    $payrollData = [
        // 2023 data - Mức lương có biến động
        ['2023-01-01', '2023-01-31', 22.0, 25000000.00, 25000000.00, 22000000.00],
        ['2023-02-01', '2023-02-28', 20.0, 23000000.00, 23000000.00, 20240000.00],
        ['2023-03-01', '2023-03-31', 22.0, 27000000.00, 27000000.00, 23760000.00],
        ['2023-04-01', '2023-04-30', 21.0, 26000000.00, 26000000.00, 22880000.00],
        ['2023-05-01', '2023-05-31', 22.0, 28000000.00, 28000000.00, 24640000.00],
        ['2023-06-01', '2023-06-30', 21.0, 24000000.00, 24000000.00, 21120000.00],
        ['2023-07-01', '2023-07-31', 22.0, 29000000.00, 29000000.00, 25520000.00],
        ['2023-08-01', '2023-08-31', 22.0, 26000000.00, 26000000.00, 22880000.00],
        ['2023-09-01', '2023-09-30', 21.0, 27000000.00, 27000000.00, 23760000.00],
        ['2023-10-01', '2023-10-31', 22.0, 25000000.00, 25000000.00, 22000000.00],
        ['2023-11-01', '2023-11-30', 21.0, 28000000.00, 28000000.00, 24640000.00],
        ['2023-12-01', '2023-12-31', 22.0, 30000000.00, 30000000.00, 26400000.00],
        // 2024 data - Mức lương tăng 10% so với 2023
        ['2024-01-01', '2024-01-31', 22.0, 27500000.00, 27500000.00, 24200000.00],
        ['2024-02-01', '2024-02-29', 20.0, 25300000.00, 25300000.00, 22264000.00],
        ['2024-03-01', '2024-03-31', 22.0, 29700000.00, 29700000.00, 26136000.00],
        ['2024-04-01', '2024-04-30', 21.0, 28600000.00, 28600000.00, 25168000.00]
    ];

    $payrollStmt = $db->prepare("INSERT INTO payroll (employee_id, pay_period_start, pay_period_end, work_days_payable, base_salary_period, gross_salary, net_salary, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Get active employees
    $activeEmployees = $db->query("SELECT id FROM employees WHERE status = 'active'")->fetchAll(PDO::FETCH_COLUMN);
    
    // First, delete existing payroll records for these employees and periods
    $deletePayrollStmt = $db->prepare("DELETE FROM payroll WHERE employee_id = ? AND pay_period_start = ? AND pay_period_end = ?");
    
    foreach ($payrollData as $data) {
        foreach ($activeEmployees as $employeeId) {
            // Delete existing record if any
            $deletePayrollStmt->execute([$employeeId, $data[0], $data[1]]);
            
            // Insert new record
            $payrollStmt->execute([
                $employeeId,
                $data[0],
                $data[1],
                $data[2],
                $data[3],
                $data[4],
                $data[5],
                'paid'
            ]);
        }
    }

    // Insert sample job applications with large variations
    $jobApplications = [];
    $statuses = ['new', 'interviewing', 'hired', 'rejected', 'withdrawn'];
    
    // Tháng 1/2023: 1000 đơn, 970 phỏng vấn, 50 tuyển dụng
    for ($i = 0; $i < 1000; $i++) {
        $status = 'new';
        if ($i < 970) $status = 'interviewing';
        if ($i < 50) $status = 'hired';
        
        $jobApplications[] = [
            $jobPositionIds[$i % count($jobPositionIds)], // Use valid job position ID
            'Nguyễn', 'A' . $i, 'nguyena' . $i . '@gmail.com', '091111111' . $i, 
            $status, '2023-01-15'
        ];
    }
    
    // Tháng 2/2023: 800 đơn, 750 phỏng vấn, 40 tuyển dụng
    for ($i = 0; $i < 800; $i++) {
        $status = 'new';
        if ($i < 750) $status = 'interviewing';
        if ($i < 40) $status = 'hired';
        
        $jobApplications[] = [
            $jobPositionIds[$i % count($jobPositionIds)], // Use valid job position ID
            'Trần', 'B' . $i, 'tranb' . $i . '@gmail.com', '091222222' . $i, 
            $status, '2023-02-15'
        ];
    }
    
    // Tháng 3/2023: 1200 đơn, 1100 phỏng vấn, 60 tuyển dụng
    for ($i = 0; $i < 1200; $i++) {
        $status = 'new';
        if ($i < 1100) $status = 'interviewing';
        if ($i < 60) $status = 'hired';
        
        $jobApplications[] = [
            $jobPositionIds[$i % count($jobPositionIds)], // Use valid job position ID
            'Lê', 'C' . $i, 'lec' . $i . '@gmail.com', '091333333' . $i, 
            $status, '2023-03-15'
        ];
    }

    // Insert job applications
    $applicationStmt = $db->prepare("INSERT INTO job_applications (job_position_id, first_name, last_name, email, phone, status, applied_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($jobApplications as $application) {
        $applicationStmt->execute($application);
    }

    // Insert sample interviews for job applications
    $interviewStmt = $db->prepare("INSERT INTO interviews (job_application_id, interviewer_employee_id, interview_datetime, duration_minutes, type, status) VALUES (?, ?, ?, ?, ?, ?)");
    
    // Get hired and interviewing applications
    $applications = $db->query("SELECT id, applied_at FROM job_applications WHERE status IN ('hired', 'interviewing')")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($applications as $application) {
        $interviewDate = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($application['applied_at'])));
        $interviewStmt->execute([
            $application['id'],
            3, // Assuming employee_id 3 is an interviewer
            $interviewDate,
            60,
            ['Technical', 'HR', 'Panel'][array_rand(['Technical', 'HR', 'Panel'])],
            ['completed', 'scheduled'][array_rand(['completed', 'scheduled'])]
        ]);
    }

    // Insert sample training courses with varying participant numbers
    $trainingCourses = [
        // Tháng 1/2023: 2 khóa, 150 người tham gia
        ['Khóa đào tạo PHP nâng cao', 'Nâng cao kỹ năng lập trình PHP', 16, 5000000.00, 'active', '2023-01-15', '2023-01-20'],
        ['Khóa đào tạo Quản lý dự án', 'Kỹ năng quản lý dự án Agile', 12, 3000000.00, 'active', '2023-01-20', '2023-01-25'],
        
        // Tháng 3/2023: 1 khóa, 50 người tham gia
        ['Khóa đào tạo Python cơ bản', 'Lập trình Python cho người mới bắt đầu', 24, 4000000.00, 'active', '2023-03-10', '2023-03-15'],
        
        // Tháng 6/2023: 3 khóa, 200 người tham gia
        ['Khóa đào tạo Machine Learning', 'Cơ bản về Machine Learning', 20, 6000000.00, 'active', '2023-06-05', '2023-06-10'],
        ['Khóa đào tạo Digital Marketing', 'Chiến lược Digital Marketing', 15, 4500000.00, 'active', '2023-06-15', '2023-06-20'],
        ['Khóa đào tạo Kỹ năng mềm', 'Kỹ năng giao tiếp và làm việc nhóm', 8, 2500000.00, 'active', '2023-06-25', '2023-06-30'],
        
        // Tháng 9/2023: 1 khóa, 30 người tham gia
        ['Khóa đào tạo DevOps', 'Triển khai và vận hành hệ thống', 18, 7000000.00, 'active', '2023-09-10', '2023-09-15'],
        
        // Tháng 11/2023: 2 khóa, 100 người tham gia
        ['Khóa đào tạo ReactJS', 'Phát triển ứng dụng web với React', 20, 5500000.00, 'active', '2023-11-05', '2023-11-10'],
        ['Khóa đào tạo Quản lý thời gian', 'Kỹ năng quản lý thời gian hiệu quả', 10, 2000000.00, 'active', '2023-11-20', '2023-11-25']
    ];

    $courseStmt = $db->prepare("INSERT INTO training_courses (name, description, duration, cost, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($trainingCourses as $course) {
        $courseStmt->execute([
            $course[0],
            $course[1],
            $course[2],
            $course[3],
            $course[4],
            $course[5],
            $course[6]
        ]);
    }

    // Get all course IDs
    $courseIds = $db->query("SELECT id FROM training_courses")->fetchAll(PDO::FETCH_COLUMN);

    // Insert training registrations
    $registrationStmt = $db->prepare("INSERT INTO training_registrations (employee_id, course_id, registration_date, status, completion_date, score, feedback) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    // Get all employee IDs
    $employeeIds = $db->query("SELECT id FROM employees")->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($trainingCourses as $index => $course) {
        $courseId = $courseIds[$index];
        $participantCount = $course[2]; // duration (using as max participants for sample data)
        $startDate = $course[5]; // created_at (using as start date)
        
        // Randomly select employees for this course
        shuffle($employeeIds);
        $selectedEmployees = array_slice($employeeIds, 0, $participantCount);
        
        foreach ($selectedEmployees as $employeeId) {
            $registrationDate = date('Y-m-d', strtotime('-1 week', strtotime($startDate)));
            $completionDate = date('Y-m-d', strtotime('+1 day', strtotime($course[6])));
            $score = rand(70, 100);
            $status = ['registered', 'attended', 'completed'][array_rand(['registered', 'attended', 'completed'])];
            
            $registrationStmt->execute([
                $employeeId,
                $courseId,
                $registrationDate,
                $status,
                $status === 'completed' ? $completionDate : null,
                $status === 'completed' ? $score : null,
                $status === 'completed' ? 'Khóa học rất hữu ích' : null
            ]);
        }
    }

    // Insert training evaluations
    $evaluationStmt = $db->prepare("INSERT INTO training_evaluations (registration_id, evaluator_employee_id, evaluation_date, rating_content, rating_instructor, rating_materials, comments) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    // Get completed registrations
    $completedRegistrations = $db->query("SELECT id, employee_id, course_id FROM training_registrations WHERE status = 'completed'")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($completedRegistrations as $registration) {
        $evaluationStmt->execute([
            $registration['id'],
            $registration['employee_id'],
            date('Y-m-d', strtotime('+1 day', strtotime($registration['completion_date']))),
            rand(4, 5),
            rand(4, 5),
            rand(4, 5),
            'Khóa học rất hữu ích và thực tế'
        ]);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Sample data inserted successfully'
    ]);

} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} 