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
        if (isset($path_parts[5])) {
            switch($path_parts[5]) {
                case 'overview':
                    // Get overview statistics
                    $stats = [];
                    
                    // Total employees
                    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role != 'admin'");
                    $stats['total_employees'] = $stmt->fetchColumn();
                    
                    // Total departments
                    $stmt = $pdo->query("SELECT COUNT(*) FROM departments");
                    $stats['total_departments'] = $stmt->fetchColumn();
                    
                    // Total positions
                    $stmt = $pdo->query("SELECT COUNT(*) FROM positions");
                    $stats['total_positions'] = $stmt->fetchColumn();
                    
                    // Total active contracts
                    $stmt = $pdo->query("SELECT COUNT(*) FROM contracts WHERE status = 'active'");
                    $stats['active_contracts'] = $stmt->fetchColumn();
                    
                    // Total leave requests this month
                    $stmt = $pdo->query("
                        SELECT COUNT(*) 
                        FROM leave_requests 
                        WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
                        AND YEAR(created_at) = YEAR(CURRENT_DATE())
                    ");
                    $stats['monthly_leave_requests'] = $stmt->fetchColumn();
                    
                    echo json_encode($stats);
                    break;

                case 'attendance':
                    // Get attendance statistics
                    $month = isset($_GET['month']) ? $_GET['month'] : date('m');
                    $year = isset($_GET['year']) ? $_GET['year'] : date('Y');
                    
                    $stmt = $pdo->prepare("
                        SELECT 
                            COUNT(CASE WHEN attendance_symbol = 'P' THEN 1 END) as present,
                            COUNT(CASE WHEN attendance_symbol = 'A' THEN 1 END) as absent,
                            COUNT(CASE WHEN attendance_symbol = 'L' THEN 1 END) as late
                        FROM attendance
                        WHERE MONTH(attendance_date) = ? AND YEAR(attendance_date) = ?
                    ");
                    $stmt->execute([$month, $year]);
                    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
                    break;

                case 'leave':
                    // Get leave statistics by department
                    $month = isset($_GET['month']) ? $_GET['month'] : date('m');
                    $year = isset($_GET['year']) ? $_GET['year'] : date('Y');
                    
                    $stmt = $pdo->prepare("
                        SELECT 
                            d.name as department,
                            COUNT(lr.id) as total_requests,
                            COUNT(CASE WHEN lr.status = 'approved' THEN 1 END) as approved,
                            COUNT(CASE WHEN lr.status = 'pending' THEN 1 END) as pending,
                            COUNT(CASE WHEN lr.status = 'rejected' THEN 1 END) as rejected
                        FROM leave_requests lr
                        JOIN users u ON lr.user_id = u.id
                        JOIN departments d ON u.department_id = d.id
                        WHERE MONTH(lr.created_at) = ? AND YEAR(lr.created_at) = ?
                        GROUP BY d.id, d.name
                    ");
                    $stmt->execute([$month, $year]);
                    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
                    break;

                case 'salary':
                    // Get salary statistics
                    $month = isset($_GET['month']) ? $_GET['month'] : date('m');
                    $year = isset($_GET['year']) ? $_GET['year'] : date('Y');
                    
                    $stmt = $pdo->prepare("
                        SELECT 
                            d.name as department,
                            COUNT(s.id) as total_employees,
                            SUM(s.basic_salary) as total_basic_salary,
                            SUM(s.allowances) as total_allowances,
                            SUM(s.bonuses) as total_bonuses,
                            SUM(s.deductions) as total_deductions,
                            SUM(s.net_salary) as total_net_salary
                        FROM salaries s
                        JOIN users u ON s.user_id = u.id
                        JOIN departments d ON u.department_id = d.id
                        WHERE MONTH(s.payment_date) = ? AND YEAR(s.payment_date) = ?
                        GROUP BY d.id, d.name
                    ");
                    $stmt->execute([$month, $year]);
                    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
                    break;

                case 'employee':
                    // Get employee statistics by department
                    $stmt = $pdo->query("
                        SELECT 
                            d.name as department,
                            COUNT(u.id) as total_employees,
                            COUNT(CASE WHEN u.gender = 'male' THEN 1 END) as male,
                            COUNT(CASE WHEN u.gender = 'female' THEN 1 END) as female,
                            AVG(TIMESTAMPDIFF(YEAR, u.date_of_birth, CURDATE())) as avg_age
                        FROM users u
                        JOIN departments d ON u.department_id = d.id
                        WHERE u.role != 'admin'
                        GROUP BY d.id, d.name
                    ");
                    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
                    break;

                default:
                    http_response_code(404);
                    echo json_encode(['error' => 'Endpoint not found']);
                    break;
            }
        } else {
            // Get all dashboard data
            $dashboard = [];
            
            // Overview stats
            $stmt = $pdo->query("
                SELECT 
                    (SELECT COUNT(*) FROM users WHERE role != 'admin') as total_employees,
                    (SELECT COUNT(*) FROM departments) as total_departments,
                    (SELECT COUNT(*) FROM positions) as total_positions,
                    (SELECT COUNT(*) FROM contracts WHERE status = 'active') as active_contracts,
                    (SELECT COUNT(*) FROM leave_requests 
                     WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
                     AND YEAR(created_at) = YEAR(CURRENT_DATE())) as monthly_leave_requests
            ");
            $dashboard['overview'] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Recent activities
            $stmt = $pdo->query("
                SELECT sl.*, u.full_name as user_name 
                FROM system_logs sl
                LEFT JOIN users u ON sl.user_id = u.id
                ORDER BY sl.created_at DESC 
                LIMIT 10
            ");
            $dashboard['recent_activities'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Pending leave requests
            $stmt = $pdo->query("
                SELECT lr.*, u.full_name as employee_name, d.name as department_name
                FROM leave_requests lr
                JOIN users u ON lr.user_id = u.id
                JOIN departments d ON u.department_id = d.id
                WHERE lr.status = 'pending'
                ORDER BY lr.created_at DESC
                LIMIT 5
            ");
            $dashboard['pending_leave_requests'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode($dashboard);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?> 