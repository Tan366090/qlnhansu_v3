<?php
// Bật error reporting để debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cấu hình CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Xử lý preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Kiểm tra kết nối database
try {
    require_once __DIR__ . '/../config/Database.php';
    $database = new Database();
    $conn = $database->getConnection();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi kết nối database: ' . $e->getMessage()
    ]);
    exit();
}

class EmployeeAPI {
    private $conn;
    private $table_name = "employees";

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    // Lấy danh sách nhân viên với phân trang và bộ lọc
    public function getEmployees() {
        try {
            // Lấy các tham số từ query string
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
            $search = isset($_GET['search']) ? $_GET['search'] : '';
            $department_id = isset($_GET['department_id']) ? $_GET['department_id'] : '';
            $position_id = isset($_GET['position_id']) ? $_GET['position_id'] : '';
            $status = isset($_GET['status']) ? $_GET['status'] : '';

            // Tính toán offset
            $offset = ($page - 1) * $per_page;

            // Xây dựng câu query với join các bảng cần thiết
            $query = "SELECT 
                        e.id,
                        e.employee_code,
                        e.status,
                        e.hire_date,
                        e.termination_date,
                        e.name,
                        u.username,
                        u.email,
                        up.date_of_birth as birth_date,
                        up.phone_number,
                        up.gender,
                        d.name as department_name,
                        p.name as position_name
                     FROM {$this->table_name} e
                     LEFT JOIN users u ON e.user_id = u.user_id
                     LEFT JOIN user_profiles up ON u.user_id = up.user_id
                     LEFT JOIN departments d ON e.department_id = d.id
                     LEFT JOIN positions p ON e.position_id = p.id
                     WHERE 1=1";

            $params = [];

            // Thêm điều kiện tìm kiếm
            if (!empty($search)) {
                $query .= " AND (u.username LIKE ? OR e.employee_code LIKE ? OR u.email LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }

            // Thêm điều kiện phòng ban
            if (!empty($department_id)) {
                $query .= " AND e.department_id = ?";
                $params[] = $department_id;
            }

            // Thêm điều kiện chức vụ
            if (!empty($position_id)) {
                $query .= " AND e.position_id = ?";
                $params[] = $position_id;
            }

            // Thêm điều kiện trạng thái
            if (!empty($status)) {
                $query .= " AND e.status = ?";
                $params[] = $status;
            }

            // Lấy tổng số bản ghi
            $count_query = "SELECT COUNT(*) as total FROM ({$query}) as count_table";
            $stmt = $this->conn->prepare($count_query);
            $stmt->execute($params);
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Thêm phân trang
            $query .= " ORDER BY e.created_at DESC LIMIT ? OFFSET ?";
            $params[] = $per_page;
            $params[] = $offset;

            // Thực thi query
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Trả về kết quả
            echo json_encode([
                'success' => true,
                'data' => $employees,
                'total' => $total,
                'page' => $page,
                'per_page' => $per_page
            ]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách nhân viên: ' . $e->getMessage()
            ]);
        }
    }

    // Thêm nhân viên mới
    public function addEmployee() {
        try {
            // Lấy dữ liệu từ request body
            $data = json_decode(file_get_contents('php://input'), true);
            
            $employee = $data['employees'][0];
            
            if (!$employee) {
                throw new Exception('Dữ liệu không hợp lệ');
            }

            // Validate dữ liệu
            $required_fields = ['fullName', 'phone', 'department', 'position', 'startDate', 'email'];
            foreach ($required_fields as $field) {
                if (empty($employee[$field])) {
                    throw new Exception("Trường {$field} là bắt buộc");
                }
            }

            // Validate email
            if (!empty($employee['email']) && !filter_var($employee['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email không hợp lệ');
            }

            // Validate phone
            if (!preg_match('/^[0-9]{10,11}$/', $employee['phone'])) {
                throw new Exception('Số điện thoại không hợp lệ');
            }

            // Bắt đầu transaction
            $this->conn->beginTransaction();

            try {
                // Tạo mã nhân viên tự động nếu không có
                $employee_code = $employee['employee_code'] ?? $this->generateEmployeeCode();

                file_put_contents('log.txt', print_r($employee['email'], true));

                // Thêm user
                $user_query = "INSERT INTO users (email, username, password_hash, role_id) VALUES (?, ?, ?, ?)";
                $stmt = $this->conn->prepare($user_query);
                $stmt->execute([
                    $employee['email'],
                    $employee['email'], // Using email as username
                    password_hash('123456', PASSWORD_DEFAULT), // Mật khẩu mặc định
                    2 // role_id = 2 cho nhân viên
                ]);
                
                $user_id = $this->conn->lastInsertId();

                // Thêm user profile
                $profile_query = "INSERT INTO user_profiles (user_id, full_name, phone_number, date_of_birth, gender, permanent_address) 
                                VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $this->conn->prepare($profile_query);
                $stmt->execute([
                    $user_id,
                    $employee['fullName'],
                    $employee['phone'],
                    $employee['birthDate'] ?? null,
                    $employee['gender'] ?? 'other',
                    $employee['address'] ?? null
                ]);

                // Thêm employee
                $employee_query = "INSERT INTO employees (user_id, email, employee_code, department_id, position_id, hire_date, status, contract_type, base_salary, contract_start_date, contract_end_date) 
                                VALUES (?, ?, ?, ?, ?, ?, 'active', ?, ?, ?, ?)";
                $stmt = $this->conn->prepare($employee_query);
                $stmt->execute([
                    $user_id,
                    $employee['email'],
                    $employee_code,
                    $employee['department'],
                    $employee['position'],
                    $employee['startDate'],
                    $employee['contract_type'] ?? 'Permanent',
                    $employee['base_salary'] ?? 0,
                    $employee['contract_start_date'] ?? $employee['startDate'],
                    $employee['contract_end_date'] ?? null
                ]);
                $employee_id = $this->conn->lastInsertId();

                // Commit transaction
                $this->conn->commit();

                echo json_encode([
                    'success' => true,
                    'message' => 'Thêm nhân viên thành công',
                    'data' => [
                        'id' => $employee_id,
                        'employee_code' => $employee_code
                    ]
                ]);

            } catch (Exception $e) {
                // Rollback transaction nếu có lỗi
                $this->conn->rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
        }
    }

    // Hàm tạo mã nhân viên tự động
    private function generateEmployeeCode() {
        $prefix = 'NV';
        $random = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $date = date('ym');
        return $prefix . $date . $random;
    }

    // Hàm tạo email tự động
    private function generateEmail($fullName) {
        $name = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $this->removeAccents($fullName)));
        $random = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
        return $name . $random . '@company.com';
    }

    // Hàm xóa dấu tiếng Việt
    private function removeAccents($str) {
        $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
        $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
        $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
        $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
        $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
        $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
        $str = preg_replace("/(đ)/", 'd', $str);
        $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $str);
        $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $str);
        $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $str);
        $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $str);
        $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $str);
        $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str);
        $str = preg_replace("/(Đ)/", 'D', $str);
        return $str;
    }

    // Xóa nhân viên
    public function deleteEmployee($id) {
        try {
            // Kiểm tra nhân viên có tồn tại không
            $check_query = "SELECT id FROM {$this->table_name} WHERE id = ?";
            $stmt = $this->conn->prepare($check_query);
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Nhân viên không tồn tại'
                ]);
                return;
            }

            // Xóa nhân viên
            $query = "DELETE FROM {$this->table_name} WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);

            echo json_encode([
                'success' => true,
                'message' => 'Xóa nhân viên thành công'
            ]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi khi xóa nhân viên: ' . $e->getMessage()
            ]);
        }
    }
}

// Xử lý request
$api = new EmployeeAPI();
$method = $_SERVER['REQUEST_METHOD'];

if (isset($_GET['action']) && $_GET['action'] === 'quick_search' && !empty($_GET['search'])) {
    $search = $_GET['search'];
    $query = "SELECT e.id, e.employee_code, e.name
              FROM employees e
              WHERE e.employee_code LIKE ? OR e.name LIKE ?";
    $stmt = $conn->prepare($query);
    $like = "%$search%";
    $stmt->execute([$like, $like]);
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        'success' => true,
        'data' => $employees
    ]);
    exit();
}

if (isset($_GET['action']) && $_GET['action'] === 'employee_qualifications' && !empty($_GET['employee_id'])) {
    $employee_id = $_GET['employee_id'];
    
    // Query to get all qualifications
    $query = "SELECT 
        e.employee_code,
        e.name as employee_name,
        -- Degrees
        d.degree_name,
        d.major,
        d.institution as degree_institution,
        d.graduation_date,
        d.gpa,
        d.attachment_url as degree_attachment,
        -- Certificates
        c.name as certificate_name,
        c.issuing_organization,
        c.issue_date as certificate_issue_date,
        c.expiry_date as certificate_expiry_date,
        c.credential_id,
        c.file_url as certificate_file,
        -- Training courses
        tc.name as course_name,
        tr.status as course_status,
        tr.completion_date,
        tr.score as course_score,
        te.rating_content,
        te.rating_instructor,
        te.rating_materials,
        te.comments as evaluation_comments
    FROM employees e
    LEFT JOIN degrees d ON e.id = d.employee_id
    LEFT JOIN certificates c ON e.id = c.employee_id
    LEFT JOIN training_registrations tr ON e.id = tr.employee_id
    LEFT JOIN training_courses tc ON tr.course_id = tc.id
    LEFT JOIN training_evaluations te ON tr.id = te.registration_id
    WHERE e.id = ?
    ORDER BY 
        d.graduation_date DESC,
        c.issue_date DESC,
        tr.completion_date DESC";
        
    $stmt = $conn->prepare($query);
    $stmt->execute([$employee_id]);
    $qualifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $qualifications
    ]);
    exit();
}

switch ($method) {
    case 'GET':
        $api->getEmployees();
        break;
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        file_put_contents('log.txt', print_r($data, true));

        $employee = $data['employees'][0];

        // Log the received data for debugging
        error_log("Received data: " . print_r($data, true));

        if (empty($employee['name'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Tên nhân viên không được để trống.']);
            exit;
        }

        if (!filter_var($employee['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email không hợp lệ.']);
            exit;
        }

        if (!preg_match('/^\d{10,15}$/', $employee['phone'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Số điện thoại không hợp lệ.']);
            exit;
        }

        $api->addEmployee();
        break;
    case 'DELETE':
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if ($id) {
            $api->deleteEmployee($id);
        } else {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Thiếu tham số id'
            ]);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Phương thức không được hỗ trợ'
        ]);
        break;
}