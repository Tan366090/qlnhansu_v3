<?php
// Prevent any output before JSON response
ob_start();

session_start();
require_once 'OpenAIHelper.php';

// Bật báo lỗi trong quá trình phát triển
error_reporting(E_ALL);
ini_set('display_errors', 0); // Tắt hiển thị lỗi trực tiếp

// Set header to JSON
header('Content-Type: application/json; charset=utf-8');

// Function to send JSON response
function sendJsonResponse($success, $data = null, $error = null) {
    ob_clean(); // Clear any previous output
    echo json_encode([
        'success' => $success,
        'response' => $data,
        'error' => $error
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    sendJsonResponse(false, null, 'Vui lòng đăng nhập để sử dụng tính năng này');
}

// Hàm ghi log
function writeLog($message) {
    $logFile = 'chat_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

try {
    // Load database configuration
    $configPath = __DIR__ . '/config/database.php';
    if (!file_exists($configPath)) {
        throw new Exception("Không tìm thấy file cấu hình database");
    }
    
    $dbConfig = require $configPath;
    
    if (!is_array($dbConfig)) {
        throw new Exception("File cấu hình database không hợp lệ");
    }
    
    // Kết nối database
    $conn = new mysqli(
        $dbConfig['host'],
        $dbConfig['username'],
        $dbConfig['password'],
        $dbConfig['dbname']
    );
    
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        throw new Exception("Kết nối database thất bại: " . $conn->connect_error);
    }
    $conn->set_charset($dbConfig['charset']);

    // Nhận tin nhắn từ người dùng
    $userMessage = $_POST['message'] ?? '';

    if (empty($userMessage)) {
        throw new Exception('Không có tin nhắn');
    }

    // Ghi log tin nhắn người dùng
    writeLog("User message: $userMessage");

    // Xử lý các câu hỏi phổ biến
    $response = '';
    $userMessage = strtolower(trim($userMessage));
    
    // Mở rộng các từ khóa để nhận diện câu hỏi
    $keywords = [
        'tổng số nhân viên' => ['tổng số nhân viên', 'bao nhiêu nhân viên', 'số lượng nhân viên'],
        'thông tin phòng ban' => ['thông tin phòng ban', 'danh sách phòng ban', 'các phòng ban'],
        'thống kê lương' => ['thống kê lương', 'báo cáo lương', 'thông tin lương'],
        'thống kê nghỉ phép' => ['thống kê nghỉ phép', 'báo cáo nghỉ phép', 'thông tin nghỉ phép'],
        'nhân viên mới' => ['nhân viên mới', 'tuyển dụng mới', 'người mới'],
        'thông tin chi tiết nhân viên' => ['thông tin chi tiết nhân viên', 'profile nhân viên', 'hồ sơ nhân viên'],
        'lịch sử lương' => ['lịch sử lương', 'thay đổi lương', 'điều chỉnh lương'],
        'thưởng và phúc lợi' => ['thưởng', 'phúc lợi', 'bonus', 'allowance'],
        'đánh giá hiệu suất' => ['đánh giá', 'hiệu suất', 'kpi', 'performance'],
        'đào tạo và phát triển' => ['đào tạo', 'phát triển', 'training', 'development'],
        'cơ cấu tổ chức' => ['cơ cấu tổ chức', 'sơ đồ tổ chức', 'organizational structure'],
        'ngân sách phòng ban' => ['ngân sách', 'chi phí phòng ban', 'department budget'],
        'thông tin đào tạo' => ['thông tin đào tạo', 'khóa học', 'chứng chỉ', 'training'],
        'đánh giá và kpi' => ['đánh giá', 'kpi', 'hiệu suất', 'performance'],
        'phúc lợi và bảo hiểm' => ['phúc lợi', 'bảo hiểm', 'allowance', 'insurance'],
        'quản lý tài liệu' => ['tài liệu', 'document', 'hồ sơ', 'file'],
        'thay đổi tổ chức' => ['thay đổi tổ chức', 'reorganization', 'restructure'],
        'quan hệ nhân viên' => ['quan hệ nhân viên', 'disciplinary', 'grievance'],
        'quản lý nghỉ việc' => ['nghỉ việc', 'exit', 'thôi việc', 'resignation']
    ];

    $found = false;
    foreach ($keywords as $key => $variations) {
        foreach ($variations as $variation) {
            if (strpos($userMessage, $variation) !== false) {
                $found = true;
                switch ($key) {
                    case 'tổng số nhân viên':
                        try {
                            // Truy vấn chi tiết hơn về nhân viên
                            $sql = "SELECT 
                                COUNT(*) as total,
                                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_count,
                                SUM(CASE WHEN status = 'on_leave' THEN 1 ELSE 0 END) as on_leave_count,
                                SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_count
                                FROM employees";
                            
                            error_log("Executing SQL: " . $sql);
                            
                            $result = $conn->query($sql);
                            if ($result === false) {
                                error_log("Query failed: " . $conn->error);
                                throw new Exception("Lỗi truy vấn: " . $conn->error);
                            }
                            
                            if ($result && $row = $result->fetch_assoc()) {
                                $response = "Thống kê nhân viên:\n" .
                                          "- Tổng số nhân viên: " . number_format($row['total']) . "\n" .
                                          "- Đang làm việc: " . number_format($row['active_count']) . "\n" .
                                          "- Đang nghỉ phép: " . number_format($row['on_leave_count']) . "\n" .
                                          "- Không hoạt động: " . number_format($row['inactive_count']);
                            } else {
                                error_log("No results found");
                                throw new Exception("Không tìm thấy dữ liệu nhân viên");
                            }
                        } catch (Exception $e) {
                            error_log("Error in employee count query: " . $e->getMessage());
                            throw $e;
                        }
                        break;

                    case 'thông tin phòng ban':
                        // Truy vấn chi tiết về phòng ban và nhân viên
                        $sql = "SELECT 
                            d.name as department_name,
                            COUNT(e.id) as employee_count,
                            d.description,
                            d.created_at,
                            (SELECT name FROM employees WHERE id = d.manager_id) as manager_name
                            FROM departments d
                            LEFT JOIN employees e ON d.id = e.department_id
                            GROUP BY d.id, d.name, d.description, d.created_at, d.manager_id";
                        $result = $conn->query($sql);
                        if ($result) {
                            $tableData = [
                                'headers' => ['Phòng ban', 'Số NV', 'Mô tả', 'Người quản lý', 'Ngày thành lập'],
                                'rows' => [],
                                'cellClasses' => ['', 'number', '', '', '']
                            ];
                            
                            while ($row = $result->fetch_assoc()) {
                                $tableData['rows'][] = [
                                    $row['department_name'],
                                    number_format($row['employee_count']),
                                    $row['description'],
                                    $row['manager_name'] ?? 'Chưa có',
                                    date('d/m/Y', strtotime($row['created_at']))
                                ];
                            }
                            
                            $response = "[TABLE]" . json_encode($tableData) . "[/TABLE]";
                        } else {
                            throw new Exception("Không thể truy vấn thông tin phòng ban");
                        }
                        break;

                    case 'thống kê lương':
                        // Truy vấn thống kê lương
                        $sql = "SELECT 
                            d.name as department_name,
                            COUNT(DISTINCT e.id) as employee_count,
                            COALESCE(AVG(p.net_salary), 0) as avg_salary,
                            COALESCE(MIN(p.net_salary), 0) as min_salary,
                            COALESCE(MAX(p.net_salary), 0) as max_salary,
                            COALESCE(SUM(p.net_salary), 0) as total_salary
                            FROM departments d
                            INNER JOIN employees e ON d.id = e.department_id
                            LEFT JOIN (
                                SELECT p1.*
                                FROM payroll p1
                                INNER JOIN (
                                    SELECT employee_id, MAX(pay_period_start) as latest_period
                                    FROM payroll
                                    WHERE status = 'paid'
                                    GROUP BY employee_id
                                ) p2 ON p1.employee_id = p2.employee_id 
                                AND p1.pay_period_start = p2.latest_period
                            ) p ON e.id = p.employee_id
                            GROUP BY d.id, d.name
                            HAVING COUNT(DISTINCT e.id) > 0
                            ORDER BY d.name";
                        
                        error_log("Executing salary stats SQL: " . $sql);
                        
                        $result = $conn->query($sql);
                        if ($result) {
                            $tableData = [
                                'headers' => ['Phòng ban', 'Số NV', 'Lương TB', 'Lương thấp nhất', 'Lương cao nhất', 'Tổng lương'],
                                'rows' => [],
                                'cellClasses' => ['', 'number', 'number', 'number', 'number', 'number']
                            ];
                            
                            $hasData = false;
                            while ($row = $result->fetch_assoc()) {
                                $hasData = true;
                                $tableData['rows'][] = [
                                    $row['department_name'],
                                    number_format($row['employee_count']),
                                    $row['avg_salary'] > 0 ? number_format($row['avg_salary'], 0, ',', '.') . ' VNĐ' : 'Chưa có dữ liệu',
                                    $row['min_salary'] > 0 ? number_format($row['min_salary'], 0, ',', '.') . ' VNĐ' : 'Chưa có dữ liệu',
                                    $row['max_salary'] > 0 ? number_format($row['max_salary'], 0, ',', '.') . ' VNĐ' : 'Chưa có dữ liệu',
                                    $row['total_salary'] > 0 ? number_format($row['total_salary'], 0, ',', '.') . ' VNĐ' : 'Chưa có dữ liệu'
                                ];
                            }
                            
                            if (!$hasData) {
                                $tableData['rows'][] = [
                                    'Không có dữ liệu lương',
                                    '-',
                                    '-',
                                    '-',
                                    '-',
                                    '-'
                                ];
                            }
                            
                            $response = "[TABLE]" . json_encode($tableData) . "[/TABLE]";
                            
                            // Thêm thông tin debug
                            error_log("Salary stats data: " . print_r($tableData, true));
                        } else {
                            error_log("Salary stats query failed: " . $conn->error);
                            throw new Exception("Không thể truy vấn thông tin lương: " . $conn->error);
                        }
                        break;

                    case 'thống kê nghỉ phép':
                        // Truy vấn chi tiết về nghỉ phép
                        $sql = "SELECT 
                            COUNT(*) as tong_don,
                            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as da_duyet,
                            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as cho_duyet,
                            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as tu_choi,
                            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as huy_don,
                            SUM(leave_duration_days) as tong_ngay_nghi,
                            (SELECT COUNT(DISTINCT employee_id) FROM leaves 
                             WHERE MONTH(start_date) = MONTH(CURRENT_DATE())) as so_nv_nghi_phep
                            FROM leaves 
                            WHERE MONTH(start_date) = MONTH(CURRENT_DATE())";
                        $result = $conn->query($sql);
                        if ($result && $row = $result->fetch_assoc()) {
                            $tableData = [
                                'headers' => ['Chỉ số', 'Giá trị'],
                                'rows' => [
                                    ['Tổng số đơn', number_format($row['tong_don'])],
                                    ['Đã duyệt', number_format($row['da_duyet'])],
                                    ['Chờ duyệt', number_format($row['cho_duyet'])],
                                    ['Từ chối', number_format($row['tu_choi'])],
                                    ['Hủy đơn', number_format($row['huy_don'])],
                                    ['Tổng số ngày nghỉ', number_format($row['tong_ngay_nghi'], 1) . ' ngày'],
                                    ['Số nhân viên nghỉ phép', number_format($row['so_nv_nghi_phep'])]
                                ],
                                'cellClasses' => ['', 'number']
                            ];
                            $response = "[TABLE]" . json_encode($tableData) . "[/TABLE]";
                        } else {
                            throw new Exception("Không thể truy vấn thông tin nghỉ phép");
                        }
                        break;

                    case 'nhân viên mới':
                        // Truy vấn chi tiết về nhân viên mới
                        $sql = "SELECT 
                            e.name as ho_ten,
                            e.hire_date as ngay_vao_lam,
                            d.name as phong_ban,
                            p.name as vi_tri,
                            e.employee_code as ma_nhan_vien,
                            e.email as email
                            FROM employees e
                            LEFT JOIN departments d ON e.department_id = d.id
                            LEFT JOIN positions p ON e.position_id = p.id
                            WHERE e.hire_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
                            ORDER BY e.hire_date DESC
                            LIMIT 5";
                        $result = $conn->query($sql);
                        if ($result) {
                            $response = "Danh sách 5 nhân viên mới nhất trong 30 ngày qua:\n";
                            while ($row = $result->fetch_assoc()) {
                                $response .= "\n- " . $row['ho_ten'] . " (Mã NV: " . $row['ma_nhan_vien'] . ")\n" .
                                           "  + Phòng ban: " . $row['phong_ban'] . "\n" .
                                           "  + Vị trí: " . $row['vi_tri'] . "\n" .
                                           "  + Ngày vào: " . date('d/m/Y', strtotime($row['ngay_vao_lam'])) . "\n" .
                                           "  + Email: " . $row['email'];
                            }
                        } else {
                            throw new Exception("Không thể truy vấn thông tin nhân viên mới");
                        }
                        break;

                    case 'thông tin chi tiết nhân viên':
                        $sql = "SELECT 
                            e.id, e.name, e.email, e.phone, e.employee_code,
                            d.name as department_name,
                            p.name as position_name,
                            up.date_of_birth, up.gender, up.phone_number,
                            up.permanent_address, up.current_address,
                            up.emergency_contact_name, up.emergency_contact_phone,
                            c.contract_type, c.start_date as contract_start_date,
                            c.end_date as contract_end_date,
                            c.salary as base_salary
                        FROM employees e
                        LEFT JOIN departments d ON e.department_id = d.id
                        LEFT JOIN positions p ON e.position_id = p.id
                        LEFT JOIN user_profiles up ON e.user_id = up.user_id
                        LEFT JOIN contracts c ON e.id = c.employee_id
                        WHERE e.status = 'active'
                        ORDER BY e.name";
                        
                        $result = $conn->query($sql);
                        $data = [];
                        while($row = $result->fetch_assoc()) {
                            $data[] = $row;
                        }
                        
                        $response = [
                            'type' => 'table',
                            'headers' => ['Mã NV', 'Họ tên', 'Email', 'SĐT', 'Phòng ban', 'Chức vụ', 'Ngày sinh', 'Giới tính', 'Địa chỉ', 'Lương cơ bản'],
                            'rows' => array_map(function($row) {
                                return [
                                    $row['employee_code'],
                                    $row['name'],
                                    $row['email'],
                                    $row['phone'],
                                    $row['department_name'],
                                    $row['position_name'],
                                    $row['date_of_birth'],
                                    $row['gender'],
                                    $row['current_address'],
                                    number_format($row['base_salary']) . ' VNĐ'
                                ];
                            }, $data)
                        ];
                        break;

                    case 'lịch sử lương':
                        $sql = "SELECT 
                            e.name as employee_name,
                            sh.effective_date,
                            sh.previous_salary,
                            sh.new_salary,
                            sh.reason,
                            sh.created_at
                            FROM salary_history sh
                            JOIN employees e ON sh.employee_id = e.id
                            ORDER BY sh.effective_date DESC
                            LIMIT 10";
                        $result = $conn->query($sql);
                        if ($result) {
                            $tableData = [
                                'headers' => ['Nhân viên', 'Ngày hiệu lực', 'Lương cũ', 'Lương mới', 'Lý do', 'Ngày ghi nhận'],
                                'rows' => [],
                                'cellClasses' => ['', 'date', 'number', 'number', '', 'date']
                            ];
                            
                            while ($row = $result->fetch_assoc()) {
                                $tableData['rows'][] = [
                                    $row['employee_name'],
                                    date('d/m/Y', strtotime($row['effective_date'])),
                                    number_format($row['previous_salary'], 0, ',', '.') . ' VNĐ',
                                    number_format($row['new_salary'], 0, ',', '.') . ' VNĐ',
                                    $row['reason'],
                                    date('d/m/Y', strtotime($row['created_at']))
                                ];
                            }
                            
                            $response = "[TABLE]" . json_encode($tableData) . "[/TABLE]";
                        }
                        break;

                    case 'thưởng và phúc lợi':
                        $sql = "SELECT 
                            e.name as employee_name,
                            b.bonus_type,
                            b.amount,
                            b.effective_date,
                            b.reason,
                            b.status
                            FROM bonuses b
                            JOIN employees e ON b.employee_id = e.id
                            ORDER BY b.effective_date DESC
                            LIMIT 10";
                        $result = $conn->query($sql);
                        if ($result) {
                            $tableData = [
                                'headers' => ['Nhân viên', 'Loại thưởng', 'Số tiền', 'Ngày hiệu lực', 'Lý do', 'Trạng thái'],
                                'rows' => [],
                                'cellClasses' => ['', '', 'number', 'date', '', '']
                            ];
                            
                            while ($row = $result->fetch_assoc()) {
                                $tableData['rows'][] = [
                                    $row['employee_name'],
                                    $row['bonus_type'],
                                    number_format($row['amount'], 0, ',', '.') . ' VNĐ',
                                    date('d/m/Y', strtotime($row['effective_date'])),
                                    $row['reason'],
                                    $row['status']
                                ];
                            }
                            
                            $response = "[TABLE]" . json_encode($tableData) . "[/TABLE]";
                        }
                        break;

                    case 'cơ cấu tổ chức':
                        $sql = "SELECT 
                            d.name as department_name,
                            d.description,
                            COUNT(e.id) as employee_count,
                            (SELECT name FROM employees WHERE id = d.manager_id) as manager_name,
                            p.name as parent_department
                            FROM departments d
                            LEFT JOIN employees e ON d.id = e.department_id
                            LEFT JOIN departments p ON d.parent_id = p.id
                            GROUP BY d.id, d.name, d.description, d.manager_id, p.name
                            ORDER BY d.name";
                        $result = $conn->query($sql);
                        if ($result) {
                            $tableData = [
                                'headers' => ['Phòng ban', 'Mô tả', 'Số nhân viên', 'Quản lý', 'Phòng ban cha'],
                                'rows' => [],
                                'cellClasses' => ['', '', 'number', '', '']
                            ];
                            
                            while ($row = $result->fetch_assoc()) {
                                $tableData['rows'][] = [
                                    $row['department_name'],
                                    $row['description'],
                                    number_format($row['employee_count']),
                                    $row['manager_name'] ?? 'Chưa có',
                                    $row['parent_department'] ?? 'Không có'
                                ];
                            }
                            
                            $response = "[TABLE]" . json_encode($tableData) . "[/TABLE]";
                        }
                        break;

                    case 'thông tin đào tạo':
                        $sql = "SELECT 
                            e.name as employee_name,
                            tc.name as course_name,
                            tr.registration_date,
                            tr.status as course_status,
                            tr.completion_date,
                            tr.score,
                            c.name as certificate_name,
                            c.issuing_organization,
                            c.issue_date,
                            c.expiry_date
                        FROM employees e
                        LEFT JOIN training_registrations tr ON e.id = tr.employee_id
                        LEFT JOIN training_courses tc ON tr.course_id = tc.id
                        LEFT JOIN certificates c ON e.id = c.employee_id
                        WHERE e.status = 'active'
                        ORDER BY e.name, tr.registration_date DESC";
                        
                        $result = $conn->query($sql);
                        if ($result) {
                            $tableData = [
                                'headers' => ['Nhân viên', 'Khóa học', 'Ngày đăng ký', 'Trạng thái', 'Ngày hoàn thành', 'Điểm', 'Chứng chỉ', 'Tổ chức cấp', 'Ngày cấp', 'Ngày hết hạn'],
                                'rows' => [],
                                'cellClasses' => ['', '', 'date', '', 'date', 'number', '', '', 'date', 'date']
                            ];
                            
                            while ($row = $result->fetch_assoc()) {
                                $tableData['rows'][] = [
                                    $row['employee_name'],
                                    $row['course_name'],
                                    date('d/m/Y', strtotime($row['registration_date'])),
                                    $row['course_status'],
                                    $row['completion_date'] ? date('d/m/Y', strtotime($row['completion_date'])) : '-',
                                    $row['score'] ? number_format($row['score'], 1) : '-',
                                    $row['certificate_name'],
                                    $row['issuing_organization'],
                                    $row['issue_date'] ? date('d/m/Y', strtotime($row['issue_date'])) : '-',
                                    $row['expiry_date'] ? date('d/m/Y', strtotime($row['expiry_date'])) : '-'
                                ];
                            }
                            
                            $response = "[TABLE]" . json_encode($tableData) . "[/TABLE]";
                        }
                        break;

                    case 'đánh giá và kpi':
                        $sql = "SELECT 
                            e.name as employee_name,
                            p.review_period_start,
                            p.review_period_end,
                            p.performance_score,
                            p.strengths,
                            p.areas_for_improvement,
                            k.metric_name,
                            k.target_value,
                            k.actual_value,
                            k.unit
                        FROM employees e
                        LEFT JOIN performances p ON e.id = p.employee_id
                        LEFT JOIN kpi k ON e.id = k.employee_id
                        WHERE e.status = 'active'
                        ORDER BY e.name, p.review_period_start DESC";
                        
                        $result = $conn->query($sql);
                        if ($result) {
                            $tableData = [
                                'headers' => ['Nhân viên', 'Kỳ đánh giá', 'Điểm đánh giá', 'Điểm mạnh', 'Cần cải thiện', 'Chỉ số KPI', 'Mục tiêu', 'Thực tế', 'Đơn vị'],
                                'rows' => [],
                                'cellClasses' => ['', 'date', 'number', '', '', '', 'number', 'number', '']
                            ];
                            
                            while ($row = $result->fetch_assoc()) {
                                $tableData['rows'][] = [
                                    $row['employee_name'],
                                    date('d/m/Y', strtotime($row['review_period_start'])) . ' - ' . 
                                    date('d/m/Y', strtotime($row['review_period_end'])),
                                    $row['performance_score'] ? number_format($row['performance_score'], 1) : '-',
                                    $row['strengths'],
                                    $row['areas_for_improvement'],
                                    $row['metric_name'],
                                    $row['target_value'],
                                    $row['actual_value'],
                                    $row['unit']
                                ];
                            }
                            
                            $response = "[TABLE]" . json_encode($tableData) . "[/TABLE]";
                        }
                        break;

                    case 'phúc lợi và bảo hiểm':
                        $sql = "SELECT 
                            e.name as employee_name,
                            b.name as benefit_name,
                            b.type as benefit_type,
                            b.amount as benefit_amount,
                            i.insurance_type,
                            i.policy_number,
                            i.provider,
                            i.start_date as insurance_start,
                            i.end_date as insurance_end,
                            i.employee_contribution,
                            i.employer_contribution
                        FROM employees e
                        LEFT JOIN benefits b ON e.id = b.employee_id
                        LEFT JOIN insurance i ON e.id = i.employee_id
                        WHERE e.status = 'active'
                        ORDER BY e.name";
                        
                        $result = $conn->query($sql);
                        if ($result) {
                            $tableData = [
                                'headers' => ['Nhân viên', 'Phúc lợi', 'Loại', 'Số tiền', 'Loại BH', 'Số hợp đồng', 'Nhà cung cấp', 'Ngày bắt đầu', 'Ngày kết thúc', 'Đóng góp NV', 'Đóng góp CTY'],
                                'rows' => [],
                                'cellClasses' => ['', '', '', 'number', '', '', '', 'date', 'date', 'number', 'number']
                            ];
                            
                            while ($row = $result->fetch_assoc()) {
                                $tableData['rows'][] = [
                                    $row['employee_name'],
                                    $row['benefit_name'],
                                    $row['benefit_type'],
                                    $row['benefit_amount'] ? number_format($row['benefit_amount'], 0, ',', '.') . ' VNĐ' : '-',
                                    $row['insurance_type'],
                                    $row['policy_number'],
                                    $row['provider'],
                                    $row['insurance_start'] ? date('d/m/Y', strtotime($row['insurance_start'])) : '-',
                                    $row['insurance_end'] ? date('d/m/Y', strtotime($row['insurance_end'])) : '-',
                                    $row['employee_contribution'] ? number_format($row['employee_contribution'], 0, ',', '.') . ' VNĐ' : '-',
                                    $row['employer_contribution'] ? number_format($row['employer_contribution'], 0, ',', '.') . ' VNĐ' : '-'
                                ];
                            }
                            
                            $response = "[TABLE]" . json_encode($tableData) . "[/TABLE]";
                        }
                        break;

                    case 'quản lý tài liệu':
                        $sql = "SELECT 
                            e.name as employee_name,
                            d.document_type,
                            d.document_name,
                            d.file_path,
                            d.upload_date,
                            d.expiry_date,
                            d.status,
                            d.uploaded_by
                        FROM employees e
                        LEFT JOIN documents d ON e.id = d.employee_id
                        WHERE e.status = 'active'
                        ORDER BY e.name, d.upload_date DESC";
                        
                        $result = $conn->query($sql);
                        if ($result) {
                            $tableData = [
                                'headers' => ['Nhân viên', 'Loại tài liệu', 'Tên tài liệu', 'Đường dẫn', 'Ngày tải lên', 'Ngày hết hạn', 'Trạng thái', 'Người tải lên'],
                                'rows' => [],
                                'cellClasses' => ['', '', '', '', 'date', 'date', '', '']
                            ];
                            
                            while ($row = $result->fetch_assoc()) {
                                $tableData['rows'][] = [
                                    $row['employee_name'],
                                    $row['document_type'],
                                    $row['document_name'],
                                    $row['file_path'],
                                    date('d/m/Y', strtotime($row['upload_date'])),
                                    $row['expiry_date'] ? date('d/m/Y', strtotime($row['expiry_date'])) : '-',
                                    $row['status'],
                                    $row['uploaded_by']
                                ];
                            }
                            
                            $response = "[TABLE]" . json_encode($tableData) . "[/TABLE]";
                        }
                        break;

                    case 'thay đổi tổ chức':
                        $sql = "SELECT 
                            oc.change_type,
                            oc.department_name,
                            oc.previous_structure,
                            oc.new_structure,
                            oc.effective_date,
                            oc.reason,
                            oc.approved_by,
                            oc.status
                        FROM organizational_changes oc
                        ORDER BY oc.effective_date DESC";
                        
                        $result = $conn->query($sql);
                        if ($result) {
                            $tableData = [
                                'headers' => ['Loại thay đổi', 'Phòng ban', 'Cấu trúc cũ', 'Cấu trúc mới', 'Ngày hiệu lực', 'Lý do', 'Người phê duyệt', 'Trạng thái'],
                                'rows' => [],
                                'cellClasses' => ['', '', '', '', 'date', '', '', '']
                            ];
                            
                            while ($row = $result->fetch_assoc()) {
                                $tableData['rows'][] = [
                                    $row['change_type'],
                                    $row['department_name'],
                                    $row['previous_structure'],
                                    $row['new_structure'],
                                    date('d/m/Y', strtotime($row['effective_date'])),
                                    $row['reason'],
                                    $row['approved_by'],
                                    $row['status']
                                ];
                            }
                            
                            $response = "[TABLE]" . json_encode($tableData) . "[/TABLE]";
                        }
                        break;

                    case 'quan hệ nhân viên':
                        $sql = "SELECT 
                            e.name as employee_name,
                            er.incident_type,
                            er.incident_date,
                            er.description,
                            er.action_taken,
                            er.resolution,
                            er.status,
                            er.handled_by
                        FROM employees e
                        LEFT JOIN employee_relations er ON e.id = er.employee_id
                        WHERE e.status = 'active'
                        ORDER BY er.incident_date DESC";
                        
                        $result = $conn->query($sql);
                        if ($result) {
                            $tableData = [
                                'headers' => ['Nhân viên', 'Loại sự việc', 'Ngày xảy ra', 'Mô tả', 'Hành động', 'Giải quyết', 'Trạng thái', 'Người xử lý'],
                                'rows' => [],
                                'cellClasses' => ['', '', 'date', '', '', '', '', '']
                            ];
                            
                            while ($row = $result->fetch_assoc()) {
                                $tableData['rows'][] = [
                                    $row['employee_name'],
                                    $row['incident_type'],
                                    date('d/m/Y', strtotime($row['incident_date'])),
                                    $row['description'],
                                    $row['action_taken'],
                                    $row['resolution'],
                                    $row['status'],
                                    $row['handled_by']
                                ];
                            }
                            
                            $response = "[TABLE]" . json_encode($tableData) . "[/TABLE]";
                        }
                        break;

                    case 'quản lý nghỉ việc':
                        $sql = "SELECT 
                            e.name as employee_name,
                            ex.resignation_date,
                            ex.last_working_day,
                            ex.reason,
                            ex.exit_interview_date,
                            ex.exit_interview_conducted_by,
                            ex.knowledge_transfer_status,
                            ex.asset_return_status,
                            ex.status
                        FROM employees e
                        LEFT JOIN exits ex ON e.id = ex.employee_id
                        WHERE ex.resignation_date IS NOT NULL
                        ORDER BY ex.resignation_date DESC";
                        
                        $result = $conn->query($sql);
                        if ($result) {
                            $tableData = [
                                'headers' => ['Nhân viên', 'Ngày nghỉ việc', 'Ngày làm việc cuối', 'Lý do', 'Ngày phỏng vấn', 'Người phỏng vấn', 'Chuyển giao', 'Tài sản', 'Trạng thái'],
                                'rows' => [],
                                'cellClasses' => ['', 'date', 'date', '', 'date', '', '', '', '']
                            ];
                            
                            while ($row = $result->fetch_assoc()) {
                                $tableData['rows'][] = [
                                    $row['employee_name'],
                                    date('d/m/Y', strtotime($row['resignation_date'])),
                                    date('d/m/Y', strtotime($row['last_working_day'])),
                                    $row['reason'],
                                    $row['exit_interview_date'] ? date('d/m/Y', strtotime($row['exit_interview_date'])) : '-',
                                    $row['exit_interview_conducted_by'],
                                    $row['knowledge_transfer_status'],
                                    $row['asset_return_status'],
                                    $row['status']
                                ];
                            }
                            
                            $response = "[TABLE]" . json_encode($tableData) . "[/TABLE]";
                        }
                        break;
                }
                break 2;
            }
        }
    }

    if (!$found) {
        // Sử dụng OpenAIHelper cho các câu hỏi khác
        $prompt = "Bạn là trợ lý ảo của hệ thống quản lý nhân sự. Hãy trả lời câu hỏi sau một cách ngắn gọn và chính xác. Nếu câu hỏi không liên quan đến quản lý nhân sự, hãy thông báo rằng bạn chỉ có thể trả lời các câu hỏi về quản lý nhân sự. Câu hỏi: " . $userMessage;
        
        $response = OpenAIHelper::ask($prompt);
        
        // Ghi log câu trả lời từ AI
        writeLog("AI Response: $response");
    }

    // Đóng kết nối database
    $conn->close();

    // Trả về kết quả
    sendJsonResponse(true, $response);

} catch (Exception $e) {
    error_log("Error in process_chat.php: " . $e->getMessage());
    sendJsonResponse(false, null, $e->getMessage());
} finally {
    if (isset($conn)) {
        $conn->close();
    }
    ob_end_flush(); // Clean up output buffer
} 

// Thông tin đào tạo
if (preg_match('/thông tin đào tạo|khóa học|chứng chỉ/i', $userMessage)) {
    $sql = "SELECT 
                e.name as employee_name,
                tc.name as course_name,
                tr.registration_date,
                tr.status as course_status,
                tr.completion_date,
                tr.score,
                c.name as certificate_name,
                c.issuing_organization,
                c.issue_date,
                c.expiry_date
            FROM employees e
            LEFT JOIN training_registrations tr ON e.id = tr.employee_id
            LEFT JOIN training_courses tc ON tr.course_id = tc.id
            LEFT JOIN certificates c ON e.id = c.employee_id
            WHERE e.status = 'active'
            ORDER BY e.name, tr.registration_date DESC";
    
    $result = $conn->query($sql);
    $data = [];
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    $response = [
        'type' => 'table',
        'headers' => ['Nhân viên', 'Khóa học', 'Ngày đăng ký', 'Trạng thái', 'Ngày hoàn thành', 'Điểm', 'Chứng chỉ', 'Tổ chức cấp', 'Ngày cấp', 'Ngày hết hạn'],
        'rows' => array_map(function($row) {
            return [
                $row['employee_name'],
                $row['course_name'],
                $row['registration_date'],
                $row['course_status'],
                $row['completion_date'],
                $row['score'],
                $row['certificate_name'],
                $row['issuing_organization'],
                $row['issue_date'],
                $row['expiry_date']
            ];
        }, $data)
    ];
}

// Thông tin đánh giá và KPI
if (preg_match('/đánh giá|kpi|hiệu suất/i', $userMessage)) {
    $sql = "SELECT 
                e.name as employee_name,
                p.review_period_start,
                p.review_period_end,
                p.performance_score,
                p.strengths,
                p.areas_for_improvement,
                k.metric_name,
                k.target_value,
                k.actual_value,
                k.unit
            FROM employees e
            LEFT JOIN performances p ON e.id = p.employee_id
            LEFT JOIN kpi k ON e.id = k.employee_id
            WHERE e.status = 'active'
            ORDER BY e.name, p.review_period_start DESC";
    
    $result = $conn->query($sql);
    $data = [];
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    $response = [
        'type' => 'table',
        'headers' => ['Nhân viên', 'Kỳ đánh giá', 'Điểm đánh giá', 'Điểm mạnh', 'Cần cải thiện', 'Chỉ số KPI', 'Mục tiêu', 'Thực tế', 'Đơn vị'],
        'rows' => array_map(function($row) {
            return [
                $row['employee_name'],
                $row['review_period_start'] . ' - ' . $row['review_period_end'],
                $row['performance_score'],
                $row['strengths'],
                $row['areas_for_improvement'],
                $row['metric_name'],
                $row['target_value'],
                $row['actual_value'],
                $row['unit']
            ];
        }, $data)
    ];
}

// Thông tin phúc lợi
if (preg_match('/phúc lợi|bảo hiểm|allowance/i', $userMessage)) {
    $sql = "SELECT 
                e.name as employee_name,
                b.name as benefit_name,
                b.type as benefit_type,
                b.amount as benefit_amount,
                i.insurance_type,
                i.policy_number,
                i.provider,
                i.start_date as insurance_start,
                i.end_date as insurance_end,
                i.employee_contribution,
                i.employer_contribution
            FROM employees e
            LEFT JOIN benefits b ON e.id = b.employee_id
            LEFT JOIN insurance i ON e.id = i.employee_id
            WHERE e.status = 'active'
            ORDER BY e.name";
    
    $result = $conn->query($sql);
    $data = [];
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    $response = [
        'type' => 'table',
        'headers' => ['Nhân viên', 'Phúc lợi', 'Loại', 'Số tiền', 'Loại BH', 'Số hợp đồng', 'Nhà cung cấp', 'Ngày bắt đầu', 'Ngày kết thúc', 'Đóng góp NV', 'Đóng góp CTY'],
        'rows' => array_map(function($row) {
            return [
                $row['employee_name'],
                $row['benefit_name'],
                $row['benefit_type'],
                number_format($row['benefit_amount']) . ' VNĐ',
                $row['insurance_type'],
                $row['policy_number'],
                $row['provider'],
                $row['insurance_start'],
                $row['insurance_end'],
                number_format($row['employee_contribution']) . ' VNĐ',
                number_format($row['employer_contribution']) . ' VNĐ'
            ];
        }, $data)
    ];
} 