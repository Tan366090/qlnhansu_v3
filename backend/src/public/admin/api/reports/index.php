<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Database configuration
$db_host = 'localhost';
$db_name = 'qlnhansu';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));

// Handle different HTTP methods
switch($method) {
    case 'GET':
        if (isset($path_parts[5]) && $path_parts[5] === 'attendance') {
            // Get attendance report
            $month = isset($_GET['month']) ? $_GET['month'] : date('m');
            $year = isset($_GET['year']) ? $_GET['year'] : date('Y');
            
            $stmt = $pdo->prepare("
                SELECT 
                    a.user_id,
                    u.full_name,
                    COUNT(CASE WHEN a.attendance_symbol = 'P' THEN 1 END) as present_days,
                    COUNT(CASE WHEN a.attendance_symbol = 'A' THEN 1 END) as absent_days,
                    COUNT(CASE WHEN a.attendance_symbol = 'L' THEN 1 END) as late_days,
                    COUNT(*) as total_days
                FROM attendance a
                JOIN users u ON a.user_id = u.id
                WHERE MONTH(a.attendance_date) = ? AND YEAR(a.attendance_date) = ?
                GROUP BY a.user_id, u.full_name
            ");
            $stmt->execute([$month, $year]);
            $report = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'month' => $month,
                'year' => $year,
                'data' => $report
            ]);
        } else if (isset($path_parts[5]) && $path_parts[5] === 'leave') {
            // Get leave report
            $month = isset($_GET['month']) ? $_GET['month'] : date('m');
            $year = isset($_GET['year']) ? $_GET['year'] : date('Y');
            $department_id = isset($_GET['department_id']) ? $_GET['department_id'] : null;
            
            $sql = "
                SELECT 
                    lr.user_id,
                    u.full_name,
                    d.name as department_name,
                    lt.type_name as leave_type,
                    COUNT(*) as total_requests,
                    SUM(CASE WHEN lr.status = 'approved' THEN 1 ELSE 0 END) as approved_requests,
                    SUM(CASE WHEN lr.status = 'rejected' THEN 1 ELSE 0 END) as rejected_requests,
                    SUM(CASE WHEN lr.status = 'pending' THEN 1 ELSE 0 END) as pending_requests
                FROM leave_requests lr
                JOIN users u ON lr.user_id = u.id
                JOIN departments d ON u.department_id = d.id
                JOIN leave_types lt ON lr.leave_type = lt.id
                WHERE MONTH(lr.start_date) = ? AND YEAR(lr.start_date) = ?
            ";
            
            $params = [$month, $year];
            
            if ($department_id) {
                $sql .= " AND d.id = ?";
                $params[] = $department_id;
            }
            
            $sql .= " GROUP BY lr.user_id, u.full_name, d.name, lt.type_name";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $report = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'month' => $month,
                'year' => $year,
                'department_id' => $department_id,
                'data' => $report
            ]);
        } else if (isset($path_parts[5]) && $path_parts[5] === 'salary') {
            // Get salary report
            $month = isset($_GET['month']) ? $_GET['month'] : date('m');
            $year = isset($_GET['year']) ? $_GET['year'] : date('Y');
            
            $stmt = $pdo->prepare("
                SELECT 
                    s.user_id,
                    u.full_name,
                    d.name as department_name,
                    p.name as position_name,
                    s.basic_salary,
                    s.allowance,
                    s.bonus,
                    s.deduction,
                    s.net_salary,
                    s.payment_date
                FROM salaries s
                JOIN users u ON s.user_id = u.id
                JOIN departments d ON u.department_id = d.id
                JOIN positions p ON u.position_id = p.id
                WHERE MONTH(s.payment_date) = ? AND YEAR(s.payment_date) = ?
            ");
            $stmt->execute([$month, $year]);
            $report = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'month' => $month,
                'year' => $year,
                'data' => $report
            ]);
        } else if (isset($path_parts[5]) && $path_parts[5] === 'employee') {
            // Get employee report
            $department_id = isset($_GET['department_id']) ? $_GET['department_id'] : null;
            
            $sql = "
                SELECT 
                    u.id,
                    u.full_name,
                    u.email,
                    u.phone,
                    d.name as department_name,
                    p.name as position_name,
                    u.join_date,
                    u.status,
                    COUNT(DISTINCT c.id) as total_contracts,
                    COUNT(DISTINCT lr.id) as total_leave_requests,
                    COUNT(DISTINCT a.id) as total_attendance_records
                FROM users u
                JOIN departments d ON u.department_id = d.id
                JOIN positions p ON u.position_id = p.id
                LEFT JOIN contracts c ON u.id = c.user_id
                LEFT JOIN leave_requests lr ON u.id = lr.user_id
                LEFT JOIN attendance a ON u.id = a.user_id
            ";
            
            $params = [];
            
            if ($department_id) {
                $sql .= " WHERE d.id = ?";
                $params[] = $department_id;
            }
            
            $sql .= " GROUP BY u.id, u.full_name, u.email, u.phone, d.name, p.name, u.join_date, u.status";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $report = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'department_id' => $department_id,
                'data' => $report
            ]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?> 