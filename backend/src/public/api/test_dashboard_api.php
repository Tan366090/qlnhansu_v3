<?php
// Bật báo lỗi chi tiết
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cấu hình CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Debug information
$debug = [
    'request' => $_GET,
    'server' => $_SERVER,
    'error' => null
];

// Kết nối database
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $host = 'localhost';
            $dbname = 'qlnhansu';
            $username = 'root';
            $password = '';
            
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Test query
            $test = $pdo->query("SELECT 1");
            if (!$test) {
                throw new Exception("Database connection test failed");
            }
            
        } catch (PDOException $e) {
            $debug['error'] = "Database connection failed: " . $e->getMessage();
            return null;
        }
    }
    
    return $pdo;
}

// Lấy dữ liệu chấm công
function getAttendanceData($period = 'week') {
    global $debug;
    
    try {
        $pdo = getDBConnection();
        if (!$pdo) {
            throw new Exception("No database connection");
        }
        
        // Xác định khoảng thời gian
        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime("-7 days")); // Luôn lấy 7 ngày gần nhất
        
        $debug['query'] = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'period' => $period,
            'current_date' => date('Y-m-d H:i:s')
        ];
        
        // Kiểm tra xem có dữ liệu trong khoảng thời gian này không
        $checkSql = "SELECT COUNT(*) as count FROM attendance 
                    WHERE attendance_date BETWEEN :start_date AND :end_date";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        $count = $checkStmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        $debug['data_check'] = [
            'total_records' => $count,
            'sql' => $checkSql,
            'params' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]
        ];
        
        if ($count == 0) {
            return [
                'success' => true,
                'data' => [],
                'message' => 'No attendance records found for the specified period',
                'debug' => $debug
            ];
        }
        
        $sql = "SELECT 
                    DATE(attendance_date) as date,
                    COUNT(CASE WHEN attendance_symbol = 'P' THEN 1 END) as present,
                    COUNT(CASE WHEN attendance_symbol = 'Ô' THEN 1 END) as sick,
                    COUNT(CASE WHEN attendance_symbol = 'Cô' THEN 1 END) as child_care,
                    COUNT(CASE WHEN attendance_symbol = 'TS' THEN 1 END) as maternity,
                    COUNT(CASE WHEN attendance_symbol = 'T' THEN 1 END) as work_accident,
                    COUNT(CASE WHEN attendance_symbol = 'CN' THEN 1 END) as sunday,
                    COUNT(CASE WHEN attendance_symbol = 'NL' THEN 1 END) as holiday,
                    COUNT(CASE WHEN attendance_symbol = 'NB' THEN 1 END) as compensatory,
                    COUNT(CASE WHEN attendance_symbol = '1/2K' THEN 1 END) as half_day_unpaid,
                    COUNT(CASE WHEN attendance_symbol = 'K' THEN 1 END) as unpaid,
                    COUNT(CASE WHEN attendance_symbol = 'N' THEN 1 END) as stopped,
                    COUNT(CASE WHEN attendance_symbol = '1/2P' THEN 1 END) as half_day_leave,
                    COUNT(CASE WHEN attendance_symbol = 'NN' THEN 1 END) as half_day_work
                FROM attendance 
                WHERE attendance_date BETWEEN :start_date AND :end_date
                GROUP BY DATE(attendance_date)
                ORDER BY date ASC";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'data' => $data,
            'debug' => $debug
        ];
    } catch (Exception $e) {
        $debug['error'] = $e->getMessage();
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'debug' => $debug
        ];
    }
}

// Lấy dữ liệu phân bổ phòng ban
function getDepartmentData() {
    global $debug;
    
    try {
        $pdo = getDBConnection();
        if (!$pdo) {
            throw new Exception("No database connection");
        }
        
        $sql = "SELECT 
                    d.department_name,
                    COUNT(e.id) as total_employees,
                    COUNT(CASE WHEN e.status = 'active' THEN 1 END) as active_employees,
                    COUNT(CASE WHEN e.status = 'probation' THEN 1 END) as probation_employees,
                    COUNT(CASE WHEN e.status = 'inactive' THEN 1 END) as inactive_employees,
                    COUNT(CASE WHEN e.status = 'on_leave' THEN 1 END) as on_leave_employees
                FROM departments d
                LEFT JOIN employees e ON d.id = e.department_id
                GROUP BY d.id, d.department_name
                ORDER BY d.department_name";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'data' => $data,
            'debug' => $debug
        ];
    } catch (Exception $e) {
        $debug['error'] = $e->getMessage();
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'debug' => $debug
        ];
    }
}

function insertTestData() {
    global $debug;
    
    try {
        $pdo = getDBConnection();
        if (!$pdo) {
            throw new Exception("No database connection");
        }
        
        // Xóa dữ liệu cũ nếu có
        $pdo->exec("DELETE FROM attendance");
        
        // Tạo dữ liệu mẫu cho 7 ngày gần nhất
        $symbols = ['P', 'Ô', 'Cô', 'TS', 'T', 'CN', 'NL', 'NB', '1/2K', 'K', 'N', '1/2P', 'NN'];
        $currentDate = date('Y-m-d');
        
        for ($i = 0; $i < 7; $i++) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $numRecords = rand(5, 10); // Số lượng bản ghi mỗi ngày
            
            for ($j = 0; $j < $numRecords; $j++) {
                $symbol = $symbols[array_rand($symbols)];
                $userId = rand(1, 10); // Giả sử có 10 user
                $recordedAt = date('Y-m-d H:i:s', strtotime("$date " . rand(8, 17) . ":" . rand(0, 59)));
                
                $sql = "INSERT INTO attendance (user_id, attendance_date, recorded_at, attendance_symbol, created_at) 
                        VALUES (:user_id, :attendance_date, :recorded_at, :attendance_symbol, NOW())";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'user_id' => $userId,
                    'attendance_date' => $date,
                    'recorded_at' => $recordedAt,
                    'attendance_symbol' => $symbol
                ]);
            }
        }
        
        return [
            'success' => true,
            'message' => 'Test data inserted successfully'
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Xử lý request
$endpoint = $_GET['endpoint'] ?? '';

try {
    switch ($endpoint) {
        case 'attendance':
            $period = $_GET['period'] ?? 'week';
            echo json_encode(getAttendanceData($period), JSON_PRETTY_PRINT);
            break;
            
        case 'departments':
            echo json_encode(getDepartmentData(), JSON_PRETTY_PRINT);
            break;
            
        case 'insert_test_data':
            echo json_encode(insertTestData(), JSON_PRETTY_PRINT);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'error' => 'Invalid endpoint',
                'debug' => $debug
            ], JSON_PRETTY_PRINT);
            break;
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug' => $debug
    ], JSON_PRETTY_PRINT);
}
?> 