<?php

// Bật error reporting để xem tất cả lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Đặt header JSON và bắt đầu output buffering
header('Content-Type: application/json');
ob_start();

// Hàm trả về JSON response
function jsonResponse($success, $data = null, $error = null) {
    $response = ['success' => $success];
    if ($data) $response['data'] = $data;
    if ($error) $response['error'] = $error;
    echo json_encode($response);
    exit;
}

// Hàm kiểm tra email hợp lệ
function isValidEmail($email) {
    if (empty($email)) {
        return false;
    }
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Hàm kiểm tra số điện thoại hợp lệ
function isValidPhone($phone) {
    if (empty($phone)) {
        return false;
    }
    // Cho phép số điện thoại có 10 hoặc 11 chữ số
    return preg_match('/^[0-9]{10,11}$/', $phone);
}

// Hàm kiểm tra ngày hợp lệ
function isValidDate($date) {
    if (empty($date)) {
        return false;
    }
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

// Hàm kiểm tra phòng ban tồn tại
function departmentExists($db, $department_id) {
    try {
        if (empty($department_id)) {
            return false;
        }
        $stmt = $db->prepare("SELECT id FROM departments WHERE id = ?");
        $stmt->execute([$department_id]);
        return $stmt->fetch() !== false;
    } catch (PDOException $e) {
        throw new Exception("Lỗi khi kiểm tra phòng ban: " . $e->getMessage());
    }
}

// Hàm kiểm tra vị trí tồn tại
function positionExists($db, $position_id) {
    try {
        if (empty($position_id)) {
            return false;
        }
        $stmt = $db->prepare("SELECT id FROM positions WHERE id = ?");
        $stmt->execute([$position_id]);
        return $stmt->fetch() !== false;
    } catch (PDOException $e) {
        throw new Exception("Lỗi khi kiểm tra vị trí: " . $e->getMessage());
    }
}

// Hàm kiểm tra loại hợp đồng hợp lệ
function isValidContractType($type) {
    if (empty($type)) {
        return false;
    }
    $validTypes = ['Permanent', 'Fixed-Term', 'Intern'];
    return in_array($type, $validTypes);
}

// Hàm tạo mã nhân viên tự động
function generateEmployeeCode($db, $department_id) {
    try {
        $stmt = $db->prepare("SELECT name FROM departments WHERE id = ?");
        $stmt->execute([$department_id]);
        $dept = $stmt->fetch();
        if (!$dept) {
            throw new Exception("Không tìm thấy phòng ban với ID: $department_id");
        }
        $deptCode = strtoupper(substr($dept['name'], 0, 3));
        
        $stmt = $db->prepare("SELECT MAX(CAST(SUBSTRING(employee_code, -3) AS UNSIGNED)) as max_num 
                             FROM employees 
                             WHERE employee_code LIKE ?");
        $stmt->execute(["EMP-$deptCode-%"]);
        $result = $stmt->fetch();
        $nextNum = ($result['max_num'] ?? 0) + 1;
        
        return "EMP-$deptCode-" . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
    } catch (PDOException $e) {
        throw new Exception("Lỗi khi tạo mã nhân viên: " . $e->getMessage());
    }
}

try {
    // Kiểm tra file config
    $configFile = __DIR__ . '/../../../config/database.php';
    if (!file_exists($configFile)) {
        throw new Exception("Không tìm thấy file cấu hình database tại: " . $configFile);
    }
    
    // Load cấu hình database
    $dbConfig = require $configFile;
    if (!is_array($dbConfig)) {
        throw new Exception("File cấu hình database không hợp lệ");
    }
    
    // Kiểm tra các thông tin cần thiết
    $requiredKeys = ['host', 'dbname', 'username', 'password'];
    foreach ($requiredKeys as $key) {
        if (!isset($dbConfig[$key])) {
            throw new Exception("Thiếu thông tin cấu hình: $key");
        }
    }
    
    // Kết nối database
    try {
        $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
        $db = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
    } catch (PDOException $e) {
        throw new Exception("Lỗi kết nối database: " . $e->getMessage());
    }

    // Kiểm tra phương thức request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, null, 'Phương thức không hợp lệ. Chỉ chấp nhận POST');
    }

    // Kiểm tra file
    if (!isset($_FILES['file'])) {
        jsonResponse(false, null, 'Không có file được tải lên');
    }

    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $errorMessage = match($_FILES['file']['error']) {
            UPLOAD_ERR_INI_SIZE => 'File vượt quá kích thước cho phép',
            UPLOAD_ERR_FORM_SIZE => 'File vượt quá kích thước form cho phép',
            UPLOAD_ERR_PARTIAL => 'File chỉ được tải lên một phần',
            UPLOAD_ERR_NO_FILE => 'Không có file được tải lên',
            UPLOAD_ERR_NO_TMP_DIR => 'Thiếu thư mục tạm',
            UPLOAD_ERR_CANT_WRITE => 'Không thể ghi file',
            UPLOAD_ERR_EXTENSION => 'File bị dừng bởi extension',
            default => 'Lỗi không xác định khi tải file'
        };
        jsonResponse(false, null, $errorMessage);
    }

    // Kiểm tra loại file
    if ($_FILES['file']['type'] !== 'text/plain') {
        jsonResponse(false, null, 'Chỉ chấp nhận file .txt');
    }
    
    $file = $_FILES['file']['tmp_name'];
    if (!file_exists($file)) {
        throw new Exception("File tạm không tồn tại");
    }
    
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        throw new Exception("Không thể đọc file");
    }
    
    if (empty($lines)) {
        jsonResponse(false, null, 'File trống hoặc không có dữ liệu');
    }
    
    $employees = [];
    $currentEmployee = null;
    $errors = [];
    
    foreach ($lines as $lineNumber => $line) {
        $parts = explode('|', $line);
        
        if ($parts[0] === 'EMP') {
            if ($currentEmployee) {
                $employees[] = $currentEmployee;
            }
            
            // Kiểm tra số lượng trường
            if (count($parts) !== 13) {
                $errors[] = "Dòng $lineNumber: Định dạng không hợp lệ (thiếu hoặc thừa trường)";
                continue;
            }
            
            // Validate dữ liệu
            $validationErrors = [];
            
            if (!isValidEmail($parts[3])) {
                $validationErrors[] = "Email không hợp lệ";
            }
            if (!isValidPhone($parts[4])) {
                $validationErrors[] = "Số điện thoại phải có 10 hoặc 11 chữ số";
            }
            if (!isValidDate($parts[5])) {
                $validationErrors[] = "Ngày sinh không hợp lệ";
            }
            if (!departmentExists($db, $parts[7])) {
                $validationErrors[] = "Phòng ban không tồn tại";
            }
            if (!positionExists($db, $parts[8])) {
                $validationErrors[] = "Vị trí không tồn tại";
            }
            if (!isValidContractType($parts[9])) {
                $validationErrors[] = "Loại hợp đồng không hợp lệ. Chỉ được nhập: Permanent, Fixed-Term, Intern";
            }
            if (!is_numeric($parts[10]) || $parts[10] <= 0) {
                $validationErrors[] = "Lương không hợp lệ";
            }
            if (!isValidDate($parts[11])) {
                $validationErrors[] = "Ngày bắt đầu không hợp lệ";
            }
            if ($parts[12] !== '' && !isValidDate($parts[12])) {
                $validationErrors[] = "Ngày kết thúc không hợp lệ";
            }
            
            if (!empty($validationErrors)) {
                $errors[] = [
                    'employee' => $parts[1],
                    'line' => $lineNumber,
                    'errors' => $validationErrors
                ];
                continue;
            }
            
            $currentEmployee = [
                'full_name' => $parts[1],
                'email' => $parts[3],
                'phone' => $parts[4],
                'birthday' => $parts[5],
                'address' => $parts[6],
                'department_id' => $parts[7],
                'position_id' => $parts[8],
                'contract_type' => $parts[9],
                'salary' => $parts[10],
                'start_date' => $parts[11],
                'end_date' => $parts[12],
                'family_members' => []
            ];
            
        } elseif ($parts[0] === 'FAM' && $currentEmployee) {
            if (count($parts) !== 6) {
                $errors[] = "Dòng $lineNumber: Định dạng thành viên gia đình không hợp lệ (thiếu hoặc thừa trường)";
                continue;
            }
            
            $validationErrors = [];
            if (empty($parts[1])) {
                $validationErrors[] = "Tên thành viên không được để trống";
            }
            if (empty($parts[2])) {
                $validationErrors[] = "Mối quan hệ không được để trống";
            }
            if (!empty($parts[3]) && !isValidDate($parts[3])) {
                $validationErrors[] = "Ngày sinh thành viên không hợp lệ";
            }
            
            if (!empty($validationErrors)) {
                $errors[] = [
                    'employee' => $currentEmployee['full_name'],
                    'line' => $lineNumber,
                    'errors' => $validationErrors
                ];
                continue;
            }
            
            $currentEmployee['family_members'][] = [
                'name' => $parts[1],
                'relationship' => $parts[2],
                'birthday' => $parts[3],
                'occupation' => $parts[4],
                'is_dependent' => $parts[5] === '1'
            ];
        }
    }
    
    // Thêm nhân viên cuối cùng
    if ($currentEmployee) {
        $employees[] = $currentEmployee;
    }
    
    // Nếu có lỗi, trả về danh sách lỗi
    if (!empty($errors)) {
        jsonResponse(false, ['errors' => $errors]);
    }
    
    // Kiểm tra cấu trúc bảng employees
    $stmt = $db->query("DESCRIBE employees");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    error_log("Employees table columns: " . print_r($columns, true));

    // Kiểm tra và thêm cột name nếu chưa có
    $stmt = $db->query("SHOW COLUMNS FROM employees LIKE 'name'");
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE employees ADD COLUMN name VARCHAR(100) NOT NULL AFTER user_id");
    }

    // Bắt đầu transaction
    $db->beginTransaction();
    
    try {
        foreach ($employees as $employee) {
            // Tạo mã nhân viên
            $employeeCode = generateEmployeeCode($db, $employee['department_id']);
            
            // Thêm vào bảng users
            $stmt = $db->prepare("INSERT INTO users (username, email, password_hash, role_id, is_active, requires_password_change, created_at, updated_at) 
                                VALUES (?, ?, ?, 3, 1, 1, NOW(), NOW())");
            $stmt->execute([
                $employee['full_name'] . '_' . uniqid(), // Ensure unique username
                $employee['email'],
                password_hash('123456', PASSWORD_DEFAULT) // Mật khẩu mặc định
            ]);
            $userId = $db->lastInsertId();
            
            // Thêm vào bảng user_profiles
            $stmt = $db->prepare("INSERT INTO user_profiles (user_id, full_name, date_of_birth, phone_number, permanent_address) 
                                VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $userId,
                $employee['full_name'],
                $employee['birthday'],
                $employee['phone'],
                $employee['address']
            ]);
            
            // Thêm vào bảng employees
            $stmt = $db->prepare("INSERT INTO employees (user_id, name, email, phone, employee_code, department_id, position_id, hire_date, contract_type, contract_start_date, contract_end_date, base_salary, status) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
            $stmt->execute([
                $userId,
                $employee['full_name'] ?? null,
                $employee['email'],
                $employee['phone'],
                $employeeCode,
                $employee['department_id'],
                $employee['position_id'],
                $employee['start_date'],
                $employee['contract_type'],
                $employee['start_date'],
                $employee['end_date'],
                $employee['salary']
            ]);
            $employeeId = $db->lastInsertId();
            
            // Thêm thành viên gia đình
            foreach ($employee['family_members'] as $member) {
                $stmt = $db->prepare("INSERT INTO family_members (employee_id, name, relationship, date_of_birth, occupation, is_dependent) 
                                    VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $employeeId,
                    $member['name'],
                    $member['relationship'],
                    $member['birthday'],
                    $member['occupation'],
                    $member['is_dependent']
                ]);
            }
        }
        
        $db->commit();
        
        jsonResponse(true, ['message' => 'Đã thêm ' . count($employees) . ' nhân viên thành công']);
        
    } catch (Exception $e) {
        $db->rollBack();
        error_log("Import error: " . $e->getMessage());
        error_log("SQL State: " . $e->getCode());
        error_log("Employee data: " . print_r($employee, true));
        throw new Exception('Lỗi khi thêm dữ liệu: ' . $e->getMessage());
    }
    
} catch (Exception $e) {
    jsonResponse(false, null, $e->getMessage());
}

// Xóa output buffer và gửi response
ob_end_flush(); 