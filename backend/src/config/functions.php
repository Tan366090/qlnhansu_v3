<?php
// Hàm lấy thông tin người dùng hiện tại
function getCurrentUserId() {
    // TODO: Implement proper authentication
    return 1; // Tạm thời trả về ID 1 (admin)
}

// Hàm lấy username từ user_id
function getUsername($userId) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn() ?: 'Unknown';
    } catch (Exception $e) {
        error_log("Error getting username: " . $e->getMessage());
        return 'Unknown';
    }
}

// Hàm lấy email từ user_id
function getEmail($userId) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn() ?: 'Unknown';
    } catch (Exception $e) {
        error_log("Error getting email: " . $e->getMessage());
        return 'Unknown';
    }
}

// Hàm chuyển đổi phương thức thanh toán
function getPaymentMethodText($method) {
    $methods = [
        'bank_transfer' => 'Chuyển khoản',
        'cash' => 'Tiền mặt',
        'check' => 'Séc'
    ];
    return $methods[$method] ?? $method;
}

// Hàm chuyển đổi trạng thái
function getStatusText($status) {
    $statuses = [
        'draft' => 'Nháp',
        'pending' => 'Chờ duyệt',
        'approved' => 'Đã duyệt',
        'rejected' => 'Từ chối',
        'paid' => 'Đã thanh toán'
    ];
    return $statuses[$status] ?? $status;
}

// Hàm ghi log hoạt động
function logActivity($userId, $type, $description) {
    global $conn;
    try {
        $stmt = $conn->prepare("INSERT INTO activities (user_id, type, description, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$userId, $type, $description]);
    } catch (Exception $e) {
        error_log("Error logging activity: " . $e->getMessage());
    }
}

// Hàm kiểm tra quyền truy cập
function checkPermission($userId, $permission) {
    global $conn;
    try {
        $stmt = $conn->prepare("
            SELECT COUNT(*) 
            FROM user_permissions up
            JOIN permissions p ON up.permission_id = p.id
            WHERE up.user_id = ? AND p.name = ?
        ");
        $stmt->execute([$userId, $permission]);
        return $stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        error_log("Error checking permission: " . $e->getMessage());
        return false;
    }
}

// Hàm format số tiền
function formatMoney($amount) {
    return number_format($amount, 0, ',', '.');
}

// Hàm validate ngày tháng
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Hàm lấy cấu hình hệ thống
function getConfig($key) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT value FROM system_configs WHERE config_key = ?");
        $stmt->execute([$key]);
        return $stmt->fetchColumn();
    } catch (Exception $e) {
        error_log("Error getting config: " . $e->getMessage());
        return null;
    }
}

// Hàm lấy danh sách phòng ban
function getDepartments() {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT id, name FROM departments WHERE is_active = 1 ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting departments: " . $e->getMessage());
        return [];
    }
}

// Hàm lấy danh sách chức vụ
function getPositions() {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT id, name FROM positions WHERE is_active = 1 ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting positions: " . $e->getMessage());
        return [];
    }
}

// Hàm lấy thông tin nhân viên
function getEmployee($employeeId) {
    global $conn;
    try {
        $stmt = $conn->prepare("
            SELECT e.*, d.name as department_name, p.name as position_name
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN positions p ON e.position_id = p.id
            WHERE e.id = ?
        ");
        $stmt->execute([$employeeId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting employee: " . $e->getMessage());
        return null;
    }
}

// Hàm lấy lịch sử lương của nhân viên
function getEmployeeSalaryHistory($employeeId) {
    global $conn;
    try {
        $stmt = $conn->prepare("
            SELECT p.*, u.username as created_by_username
            FROM payroll p
            LEFT JOIN users u ON p.created_by = u.id
            WHERE p.employee_id = ?
            ORDER BY p.period_start DESC
        ");
        $stmt->execute([$employeeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting salary history: " . $e->getMessage());
        return [];
    }
}

// Hàm lấy danh sách thành phần lương
function getSalaryComponents() {
    global $conn;
    try {
        $stmt = $conn->prepare("
            SELECT * FROM salary_components 
            WHERE is_active = 1 
            ORDER BY type, name
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting salary components: " . $e->getMessage());
        return [];
    }
}

// Hàm lấy cấu hình phê duyệt
function getApprovalLevels() {
    global $conn;
    try {
        $stmt = $conn->prepare("
            SELECT * FROM approval_levels 
            WHERE is_active = 1 
            ORDER BY level
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting approval levels: " . $e->getMessage());
        return [];
    }
}

// Hàm tạo mã tự động
function generateCode($prefix, $table, $column) {
    global $conn;
    try {
        $year = date('Y');
        $stmt = $conn->prepare("
            SELECT MAX(CAST(SUBSTRING($column, LENGTH(?) + 1) AS UNSIGNED)) as max_num
            FROM $table
            WHERE $column LIKE ?
        ");
        $pattern = $prefix . $year . '%';
        $stmt->execute([$prefix . $year, $pattern]);
        $maxNum = $stmt->fetchColumn() ?: 0;
        return $prefix . $year . str_pad($maxNum + 1, 4, '0', STR_PAD_LEFT);
    } catch (Exception $e) {
        error_log("Error generating code: " . $e->getMessage());
        return null;
    }
}

// Hàm gửi email thông báo
function sendEmail($to, $subject, $body) {
    // TODO: Implement email sending
    error_log("Email would be sent to: $to, Subject: $subject");
    return true;
}

// Hàm tạo PDF
function generatePDF($html, $filename) {
    // TODO: Implement PDF generation
    error_log("PDF would be generated: $filename");
    return true;
}

// Hàm tạo Excel
function generateExcel($data, $filename) {
    // TODO: Implement Excel generation
    error_log("Excel would be generated: $filename");
    return true;
}

// Hàm validate dữ liệu đầu vào
function validateInput($data, $rules) {
    $errors = [];
    foreach ($rules as $field => $rule) {
        if (strpos($rule, 'required') !== false && (!isset($data[$field]) || empty($data[$field]))) {
            $errors[$field] = 'Trường này là bắt buộc';
        }
        if (isset($data[$field])) {
            if (strpos($rule, 'numeric') !== false && !is_numeric($data[$field])) {
                $errors[$field] = 'Giá trị phải là số';
            }
            if (strpos($rule, 'date') !== false && !validateDate($data[$field])) {
                $errors[$field] = 'Định dạng ngày không hợp lệ';
            }
            if (strpos($rule, 'email') !== false && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = 'Email không hợp lệ';
            }
        }
    }
    return $errors;
}

// Hàm xử lý lỗi
function handleException($e) {
    error_log($e->getMessage());
    return [
        'success' => false,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ];
}

// Hàm trả về response
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Hàm xử lý lỗi và trả về response
function handleErrorResponse($e) {
    $error = handleException($e);
    sendResponse($error, 500);
}
?> 