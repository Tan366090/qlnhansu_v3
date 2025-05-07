<?php
// Bật báo lỗi chi tiết
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cấu hình CORS chi tiết hơn
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: https://localhost');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// Xử lý preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Kiểm tra HTTPS
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'HTTPS is required'
    ]);
    exit();
}

// Kiểm tra authentication
function checkAuth() {
    // Cho phép test trong môi trường development
    if ($_SERVER['REMOTE_ADDR'] === '127.0.0.1' || $_SERVER['REMOTE_ADDR'] === '::1') {
        return true;
    }

    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized: No token provided']);
        exit();
    }

    $token = str_replace('Bearer ', '', $headers['Authorization']);
    if (empty($token)) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized: Invalid token']);
        exit();
    }

    return $token;
}

// Kết nối database
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        $configPath = __DIR__ . '/../../config/database.php';
        if (!file_exists($configPath)) {
            throw new Exception("Database configuration file not found");
        }

        $dbConfig = require $configPath;
        
        $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 5
        ];

        $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $options);
    }

    return $pdo;
}

// Lấy thống kê tổng quan
function getDashboardStats() {
    try {
        $pdo = getDBConnection();
        
        // Lấy số lượng nhân viên
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM employees");
        $totalEmployees = $stmt->fetch()['total'];
        
        // Lấy số lượng nhân viên đang làm việc
        $stmt = $pdo->query("SELECT COUNT(*) as active FROM employees WHERE status = 'active'");
        $activeEmployees = $stmt->fetch()['active'];
        
        // Lấy số lượng phòng ban
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM departments");
        $totalDepartments = $stmt->fetch()['total'];
        
        // Lấy tổng lương tháng này
        $stmt = $pdo->query("SELECT SUM(amount) as total FROM payroll WHERE MONTH(payment_date) = MONTH(CURRENT_DATE())");
        $totalSalary = $stmt->fetch()['total'];
        
        return [
            'employees' => [
                'total' => $totalEmployees,
                'active' => $activeEmployees
            ],
            'departments' => $totalDepartments,
            'salary' => $totalSalary
        ];
    } catch (Exception $e) {
        throw new Exception("Error getting dashboard stats: " . $e->getMessage());
    }
}

// Lấy dữ liệu chấm công
function getAttendanceData($period = 'week') {
    try {
        $pdo = getDBConnection();
        $sql = "SELECT 
                    DATE(attendance_date) as date,
                    COUNT(*) as total,
                    SUM(CASE WHEN attendance_symbol = 'P' THEN 1 ELSE 0 END) as present,
                    SUM(CASE WHEN attendance_symbol = 'Ô' THEN 1 ELSE 0 END) as sick,
                    SUM(CASE WHEN attendance_symbol = 'Cô' THEN 1 ELSE 0 END) as child_care,
                    SUM(CASE WHEN attendance_symbol = 'TS' THEN 1 ELSE 0 END) as maternity,
                    SUM(CASE WHEN attendance_symbol = 'T' THEN 1 ELSE 0 END) as work_accident,
                    SUM(CASE WHEN attendance_symbol = 'CN' THEN 1 ELSE 0 END) as sunday,
                    SUM(CASE WHEN attendance_symbol = 'NL' THEN 1 ELSE 0 END) as holiday,
                    SUM(CASE WHEN attendance_symbol = 'NB' THEN 1 ELSE 0 END) as compensatory,
                    SUM(CASE WHEN attendance_symbol = '1/2K' THEN 1 ELSE 0 END) as half_day_unpaid,
                    SUM(CASE WHEN attendance_symbol = 'K' THEN 1 ELSE 0 END) as unpaid,
                    SUM(CASE WHEN attendance_symbol = 'N' THEN 1 ELSE 0 END) as stopped,
                    SUM(CASE WHEN attendance_symbol = '1/2P' THEN 1 ELSE 0 END) as half_day_leave,
                    SUM(CASE WHEN attendance_symbol = 'NN' THEN 1 ELSE 0 END) as half_day_work
                FROM attendance 
                WHERE attendance_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 1 $period)
                GROUP BY DATE(attendance_date)
                ORDER BY date";
                
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        throw new Exception("Error getting attendance data: " . $e->getMessage());
    }
}

// Lấy dữ liệu phòng ban
function getDepartmentData() {
    try {
        $pdo = getDBConnection();
        $sql = "SELECT 
                    d.name,
                    COUNT(e.id) as total_employees,
                    COUNT(CASE WHEN e.status = 'active' THEN 1 END) as active_employees,
                    COUNT(CASE WHEN e.status = 'probation' THEN 1 END) as probation_employees,
                    COUNT(CASE WHEN e.status = 'inactive' THEN 1 END) as inactive_employees,
                    COUNT(CASE WHEN e.status = 'on_leave' THEN 1 END) as on_leave_employees
                FROM departments d
                LEFT JOIN employees e ON e.department_id = d.id
                GROUP BY d.id, d.name";
                
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        throw new Exception("Error getting department data: " . $e->getMessage());
    }
}

// Lấy danh sách nhân viên mới nhất
function getRecentEmployees($limit = 10) {
    try {
        $pdo = getDBConnection();
        $sql = "SELECT 
                    e.*,
                    d.name as department_name,
                    p.name as position_name
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions p ON e.position_id = p.id
                ORDER BY e.created_at DESC
                LIMIT $limit";
                
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        throw new Exception("Error getting recent employees: " . $e->getMessage());
    }
}

// Xử lý request
try {
    // Kiểm tra authentication
    checkAuth();
    
    // Kết nối database
    $db = new PDO('mysql:host=localhost;dbname=qlnhansu', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Lấy endpoint từ request
    $endpoint = $_GET['endpoint'] ?? '';
    $period = $_GET['period'] ?? 'week';

    // Nếu không có endpoint, trả về danh sách các endpoint có sẵn
    if (empty($endpoint)) {
        echo json_encode([
            'success' => true,
            'endpoints' => [
                'attendance' => 'Get attendance data',
                'departments' => 'Get department data'
            ]
        ]);
        exit();
    }

    switch ($endpoint) {
        case 'attendance':
            // Lấy dữ liệu chấm công theo period
            $query = "SELECT 
                        DATE(attendance_date) as date,
                        COUNT(*) as total,
                        SUM(CASE WHEN attendance_symbol = 'P' THEN 1 ELSE 0 END) as present,
                        SUM(CASE WHEN attendance_symbol = 'Ô' THEN 1 ELSE 0 END) as sick,
                        SUM(CASE WHEN attendance_symbol = 'Cô' THEN 1 ELSE 0 END) as child_care,
                        SUM(CASE WHEN attendance_symbol = 'TS' THEN 1 ELSE 0 END) as maternity,
                        SUM(CASE WHEN attendance_symbol = 'T' THEN 1 ELSE 0 END) as work_accident,
                        SUM(CASE WHEN attendance_symbol = 'CN' THEN 1 ELSE 0 END) as sunday,
                        SUM(CASE WHEN attendance_symbol = 'NL' THEN 1 ELSE 0 END) as holiday,
                        SUM(CASE WHEN attendance_symbol = 'NB' THEN 1 ELSE 0 END) as compensatory,
                        SUM(CASE WHEN attendance_symbol = '1/2K' THEN 1 ELSE 0 END) as half_day_unpaid,
                        SUM(CASE WHEN attendance_symbol = 'K' THEN 1 ELSE 0 END) as unpaid,
                        SUM(CASE WHEN attendance_symbol = 'N' THEN 1 ELSE 0 END) as stopped,
                        SUM(CASE WHEN attendance_symbol = '1/2P' THEN 1 ELSE 0 END) as half_day_leave,
                        SUM(CASE WHEN attendance_symbol = 'NN' THEN 1 ELSE 0 END) as half_day_work
                    FROM attendance 
                    WHERE attendance_date >= DATE_SUB(CURRENT_DATE, INTERVAL 1 " . strtoupper($period) . ")
                    GROUP BY DATE(attendance_date)
                    ORDER BY date ASC";

            $stmt = $db->prepare($query);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Format dữ liệu cho chart
            $result = [
                'labels' => array_map(function($item) { return $item['date']; }, $data),
                'datasets' => [
                    [
                        'label' => 'Có mặt',
                        'data' => array_map(function($item) { return $item['present']; }, $data),
                        'borderColor' => 'rgb(75, 192, 192)',
                        'tension' => 0.1
                    ],
                    [
                        'label' => 'Nghỉ ốm',
                        'data' => array_map(function($item) { return $item['sick']; }, $data),
                        'borderColor' => 'rgb(255, 99, 132)',
                        'tension' => 0.1
                    ],
                    [
                        'label' => 'Nghỉ không lương',
                        'data' => array_map(function($item) { return $item['unpaid']; }, $data),
                        'borderColor' => 'rgb(255, 205, 86)',
                        'tension' => 0.1
                    ]
                ]
            ];

            echo json_encode([
                'success' => true,
                'data' => $result
            ]);
            break;

        case 'departments':
            // Lấy dữ liệu phòng ban
            $query = "SELECT 
                        d.id as department_id,
                        d.name,
                        d.description,
                        d.manager_id,
                        d.parent_id,
                        COUNT(e.id) as employee_count,
                        COUNT(CASE WHEN e.status = 'active' THEN 1 END) as active_employees,
                        COUNT(CASE WHEN e.status = 'inactive' THEN 1 END) as inactive_employees
                    FROM departments d
                    LEFT JOIN employees e ON d.id = e.department_id
                    GROUP BY d.id, d.name, d.description, d.manager_id, d.parent_id
                    ORDER BY employee_count DESC";

            $stmt = $db->prepare($query);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid endpoint. Available endpoints: attendance, departments'
            ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?> 