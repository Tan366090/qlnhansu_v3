<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = explode('/', $path);
$endpoint = end($path);

switch ($method) {
    case 'GET':
        if ($endpoint === 'attendance') {
            // Báo cáo chấm công
            $start_date = $_GET['start_date'] ?? date('Y-m-01');
            $end_date = $_GET['end_date'] ?? date('Y-m-t');
            
            $query = "SELECT 
                u.id as user_id,
                u.username,
                COUNT(a.attendance_id) as total_attendance,
                SUM(CASE WHEN a.attendance_symbol = 'P' THEN 1 ELSE 0 END) as present_days,
                SUM(CASE WHEN a.attendance_symbol = 'A' THEN 1 ELSE 0 END) as absent_days,
                SUM(CASE WHEN a.attendance_symbol = 'L' THEN 1 ELSE 0 END) as late_days
                FROM users u
                LEFT JOIN attendance a ON u.id = a.user_id
                WHERE a.attendance_date BETWEEN '$start_date' AND '$end_date'
                GROUP BY u.id";
                
            $result = $conn->query($query);
            $report = [];
            while ($row = $result->fetch_assoc()) {
                $report[] = $row;
            }
            echo json_encode([
                'success' => true,
                'data' => $report
            ]);
        } elseif ($endpoint === 'leave') {
            // Báo cáo nghỉ phép
            $start_date = $_GET['start_date'] ?? date('Y-m-01');
            $end_date = $_GET['end_date'] ?? date('Y-m-t');
            
            $query = "SELECT 
                u.id as user_id,
                u.username,
                COUNT(lr.id) as total_leave_requests,
                SUM(CASE WHEN lr.status = 'approved' THEN 1 ELSE 0 END) as approved_requests,
                SUM(CASE WHEN lr.status = 'pending' THEN 1 ELSE 0 END) as pending_requests,
                SUM(CASE WHEN lr.status = 'rejected' THEN 1 ELSE 0 END) as rejected_requests
                FROM users u
                LEFT JOIN leave_requests lr ON u.id = lr.user_id
                WHERE lr.start_date BETWEEN '$start_date' AND '$end_date'
                GROUP BY u.id";
                
            $result = $conn->query($query);
            $report = [];
            while ($row = $result->fetch_assoc()) {
                $report[] = $row;
            }
            echo json_encode([
                'success' => true,
                'data' => $report
            ]);
        } elseif ($endpoint === 'salary') {
            // Báo cáo lương
            $month = $_GET['month'] ?? date('m');
            $year = $_GET['year'] ?? date('Y');
            
            $query = "SELECT 
                u.id as user_id,
                u.username,
                s.basic_salary,
                s.allowance,
                s.bonus,
                s.deduction,
                (s.basic_salary + s.allowance + s.bonus - s.deduction) as total_salary
                FROM users u
                LEFT JOIN salaries s ON u.id = s.user_id
                WHERE MONTH(s.payment_date) = $month AND YEAR(s.payment_date) = $year";
                
            $result = $conn->query($query);
            $report = [];
            while ($row = $result->fetch_assoc()) {
                $report[] = $row;
            }
            echo json_encode([
                'success' => true,
                'data' => $report
            ]);
        } elseif ($endpoint === 'department') {
            // Báo cáo theo phòng ban
            $department_id = $_GET['department_id'] ?? null;
            
            if ($department_id) {
                $query = "SELECT 
                    d.name as department_name,
                    COUNT(u.id) as total_users,
                    AVG(s.basic_salary) as average_salary,
                    SUM(CASE WHEN u.status = 'active' THEN 1 ELSE 0 END) as active_users
                    FROM departments d
                    LEFT JOIN users u ON d.id = u.department_id
                    LEFT JOIN salaries s ON u.id = s.user_id
                    WHERE d.id = $department_id
                    GROUP BY d.id";
            } else {
                $query = "SELECT 
                    d.name as department_name,
                    COUNT(u.id) as total_users,
                    AVG(s.basic_salary) as average_salary,
                    SUM(CASE WHEN u.status = 'active' THEN 1 ELSE 0 END) as active_users
                    FROM departments d
                    LEFT JOIN users u ON d.id = u.department_id
                    LEFT JOIN salaries s ON u.id = s.user_id
                    GROUP BY d.id";
            }
                
            $result = $conn->query($query);
            $report = [];
            while ($row = $result->fetch_assoc()) {
                $report[] = $row;
            }
            echo json_encode([
                'success' => true,
                'data' => $report
            ]);
        }
        break;

    default:
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed'
        ]);
        break;
}
?> 