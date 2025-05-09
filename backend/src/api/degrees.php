<?php
// Bật error reporting để debug
error_reporting(E_ALL);
ini_set('display_errors', 1); // Bật hiển thị lỗi để debug

// Tạo thư mục logs nếu chưa tồn tại
$logDir = __DIR__ . '/../logs';
if (!file_exists($logDir)) {
    mkdir($logDir, 0777, true);
}

// Hàm ghi log
function writeLog($message) {
    $logFile = __DIR__ . '/../logs/degrees_api.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    error_log($logMessage, 3, $logFile);
}

// Log request details
writeLog("Request received: " . $_SERVER['REQUEST_METHOD'] . " " . $_SERVER['REQUEST_URI']);
writeLog("GET parameters: " . print_r($_GET, true));

// Cấu hình CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// Hàm xử lý lỗi
function handleApiError($message, $code = 500) {
    writeLog("ERROR: $message");
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'message' => $message,
        'error_details' => error_get_last()
    ]);
    exit();
}

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
    writeLog("Database connection successful");
} catch (Exception $e) {
    writeLog("Database connection error: " . $e->getMessage());
    handleApiError('Lỗi kết nối database: ' . $e->getMessage());
}

// Xử lý request
if (!isset($_GET['action'])) {
    handleApiError('Thiếu tham số action', 400);
}

writeLog("Processing action: " . $_GET['action']);

// Hàm chuyển đổi encoding
function convertEncoding($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = convertEncoding($value);
        }
    } else if (is_string($data)) {
        // Chuyển đổi từ UTF-8 sang UTF-8 (loại bỏ các ký tự không hợp lệ)
        $data = mb_convert_encoding($data, 'UTF-8', 'UTF-8');
    }
    return $data;
}

switch ($_GET['action']) {
    case 'list':
        // Xử lý các tham số tìm kiếm và lọc
        $filters = [];
        $params = [];
        
        // Validate và sanitize input
        function validateDate($date) {
            $d = DateTime::createFromFormat('Y-m-d', $date);
            return $d && $d->format('Y-m-d') === $date;
        }

        function validateNumeric($value, $min = null, $max = null) {
            if (!is_numeric($value)) return false;
            if ($min !== null && $value < $min) return false;
            if ($max !== null && $value > $max) return false;
            return true;
        }

        // Log các tham số đầu vào
        writeLog("Received search parameters: " . print_r($_GET, true));
        
        // Lọc theo nhân viên
        if (isset($_GET['employee'])) {
            if (empty($_GET['employee'])) {
                handleApiError('Mã nhân viên không được để trống', 400);
            }
            $filters[] = "e.employee_code = ?";
            $params[] = $_GET['employee'];
        }
        
        // Lọc theo trạng thái nhân viên
        if (isset($_GET['employee_status'])) {
            $valid_statuses = ['active', 'inactive', 'terminated', 'on_leave'];
            if (!in_array($_GET['employee_status'], $valid_statuses)) {
                handleApiError('Trạng thái nhân viên không hợp lệ', 400);
            }
            $filters[] = "e.status = ?";
            $params[] = $_GET['employee_status'];
        }
        
        // Tìm kiếm bằng cấp
        if (isset($_GET['degree_name'])) {
            $filters[] = "d.degree_name LIKE ?";
            $params[] = "%" . htmlspecialchars($_GET['degree_name']) . "%";
        }
        if (isset($_GET['major'])) {
            $filters[] = "d.major LIKE ?";
            $params[] = "%" . htmlspecialchars($_GET['major']) . "%";
        }
        if (isset($_GET['institution'])) {
            $filters[] = "d.institution LIKE ?";
            $params[] = "%" . htmlspecialchars($_GET['institution']) . "%";
        }
        if (isset($_GET['degree_from_date']) && isset($_GET['degree_to_date'])) {
            if (!validateDate($_GET['degree_from_date']) || !validateDate($_GET['degree_to_date'])) {
                handleApiError('Định dạng ngày không hợp lệ', 400);
            }
            if ($_GET['degree_from_date'] > $_GET['degree_to_date']) {
                handleApiError('Ngày bắt đầu phải nhỏ hơn ngày kết thúc', 400);
            }
            $filters[] = "d.graduation_date BETWEEN ? AND ?";
            $params[] = $_GET['degree_from_date'];
            $params[] = $_GET['degree_to_date'];
        }
        if (isset($_GET['min_gpa'])) {
            if (!validateNumeric($_GET['min_gpa'], 0, 4)) {
                handleApiError('GPA phải từ 0 đến 4', 400);
            }
            $filters[] = "d.gpa >= ?";
            $params[] = $_GET['min_gpa'];
        }
        
        // Tìm kiếm chứng chỉ
        if (isset($_GET['certificate_name'])) {
            $filters[] = "c.name LIKE ?";
            $params[] = "%" . htmlspecialchars($_GET['certificate_name']) . "%";
        }
        if (isset($_GET['issuing_org'])) {
            $filters[] = "c.issuing_organization LIKE ?";
            $params[] = "%" . htmlspecialchars($_GET['issuing_org']) . "%";
        }
        if (isset($_GET['certificate_status'])) {
            if (!in_array($_GET['certificate_status'], ['valid', 'expired'])) {
                handleApiError('Trạng thái chứng chỉ không hợp lệ', 400);
            }
            if ($_GET['certificate_status'] === 'valid') {
                $filters[] = "(c.expiry_date IS NULL OR c.expiry_date > CURDATE())";
            } else {
                $filters[] = "c.expiry_date <= CURDATE()";
            }
        }
        if (isset($_GET['certificate_from_date']) && isset($_GET['certificate_to_date'])) {
            if (!validateDate($_GET['certificate_from_date']) || !validateDate($_GET['certificate_to_date'])) {
                handleApiError('Định dạng ngày không hợp lệ', 400);
            }
            if ($_GET['certificate_from_date'] > $_GET['certificate_to_date']) {
                handleApiError('Ngày bắt đầu phải nhỏ hơn ngày kết thúc', 400);
            }
            $filters[] = "c.issue_date BETWEEN ? AND ?";
            $params[] = $_GET['certificate_from_date'];
            $params[] = $_GET['certificate_to_date'];
        }
        
        // Tìm kiếm khóa học
        if (isset($_GET['course_name'])) {
            $filters[] = "tc.name LIKE ?";
            $params[] = "%" . htmlspecialchars($_GET['course_name']) . "%";
        }
        if (isset($_GET['course_status'])) {
            $valid_statuses = ['registered', 'attended', 'completed', 'failed', 'cancelled'];
            if (!in_array($_GET['course_status'], $valid_statuses)) {
                handleApiError('Trạng thái khóa học không hợp lệ', 400);
            }
            $filters[] = "tr.status = ?";
            $params[] = $_GET['course_status'];
        }
        if (isset($_GET['course_active_status'])) {
            $valid_statuses = ['active', 'inactive', 'draft'];
            if (!in_array($_GET['course_active_status'], $valid_statuses)) {
                handleApiError('Trạng thái hoạt động của khóa học không hợp lệ', 400);
            }
            $filters[] = "tc.status = ?";
            $params[] = $_GET['course_active_status'];
        }
        if (isset($_GET['min_score'])) {
            if (!validateNumeric($_GET['min_score'], 0, 100)) {
                handleApiError('Điểm số phải từ 0 đến 100', 400);
            }
            $filters[] = "tr.score >= ?";
            $params[] = $_GET['min_score'];
        }
        if (isset($_GET['min_rating'])) {
            if (!validateNumeric($_GET['min_rating'], 1, 5)) {
                handleApiError('Đánh giá phải từ 1 đến 5', 400);
            }
            $filters[] = "(te.rating_content >= ? OR te.rating_instructor >= ? OR te.rating_materials >= ?)";
            $params[] = $_GET['min_rating'];
            $params[] = $_GET['min_rating'];
            $params[] = $_GET['min_rating'];
        }
        if (isset($_GET['evaluation_from_date']) && isset($_GET['evaluation_to_date'])) {
            if (!validateDate($_GET['evaluation_from_date']) || !validateDate($_GET['evaluation_to_date'])) {
                handleApiError('Định dạng ngày không hợp lệ', 400);
            }
            if ($_GET['evaluation_from_date'] > $_GET['evaluation_to_date']) {
                handleApiError('Ngày bắt đầu phải nhỏ hơn ngày kết thúc', 400);
            }
            $filters[] = "te.evaluation_date BETWEEN ? AND ?";
            $params[] = $_GET['evaluation_from_date'];
            $params[] = $_GET['evaluation_to_date'];
        }
        
        // Thêm điều kiện WHERE nếu có bộ lọc
        $where_clause = !empty($filters) ? "WHERE " . implode(" AND ", $filters) : "";
        
        try {
            // Log câu query và tham số
            writeLog("Executing query with filters: " . $where_clause);
            writeLog("Query parameters: " . print_r($params, true));
            
            // Query để lấy thông tin đầy đủ về bằng cấp, chứng chỉ và khóa học
            $query = "SELECT DISTINCT
                e.employee_code,
                e.name as employee_name,
                e.status as employee_status,
                -- Thông tin phòng ban
                dpt.id as department_id,
                dpt.name as department_name,
                dpt.description as department_description,
                manager.name as department_manager_name,
                -- Thông tin bằng cấp
                d.degree_id,
                d.degree_name,
                d.major,
                d.institution as degree_institution,
                d.graduation_date,
                d.gpa,
                d.attachment_url as degree_attachment,
                -- Thông tin chứng chỉ
                c.id as certificate_id,
                c.name as certificate_name,
                c.issuing_organization,
                c.issue_date as certificate_issue_date,
                c.expiry_date as certificate_expiry_date,
                c.credential_id,
                c.file_url as certificate_file,
                -- Thông tin khóa học đã tham gia
                tc.id as course_id,
                tc.name as course_name,
                tc.status as course_active_status,
                tc.duration,
                tc.cost,
                tr.status as registration_status,
                tr.registration_date,
                tr.completion_date,
                tr.score as course_score,
                tr.feedback as course_feedback,
                -- Thông tin đánh giá
                te.evaluation_date,
                te.rating_content,
                te.rating_instructor,
                te.rating_materials,
                te.comments as evaluation_comments,
                evaluator.name as evaluator_name
            FROM employees e
            LEFT JOIN departments dpt ON e.department_id = dpt.id
            LEFT JOIN employees manager ON dpt.manager_id = manager.id
            LEFT JOIN degrees d ON e.id = d.employee_id
            LEFT JOIN certificates c ON e.id = c.employee_id
            LEFT JOIN training_registrations tr ON e.id = tr.employee_id
            LEFT JOIN training_courses tc ON tr.course_id = tc.id
            LEFT JOIN training_evaluations te ON tr.id = te.registration_id
            LEFT JOIN employees evaluator ON te.evaluator_employee_id = evaluator.id
            $where_clause
            ORDER BY 
                d.graduation_date DESC,
                c.issue_date DESC,
                tr.completion_date DESC";
            
            $stmt = $conn->prepare($query);
            $stmt->execute($params);
            $qualifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            writeLog("Found " . count($qualifications) . " qualification records");
            
            // Kiểm tra kết quả trống
            if (empty($qualifications)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Không tìm thấy kết quả phù hợp',
                    'data' => [
                        'employee' => [
                            'code' => $_GET['employee'] ?? '',
                            'name' => '',
                            'status' => '',
                            'department' => [
                                'id' => null,
                                'name' => '',
                                'description' => '',
                                'manager' => ''
                            ]
                        ],
                        'degrees' => [],
                        'certificates' => [],
                        'courses' => []
                    ]
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit();
            }
            
            // Tổ chức dữ liệu thành các nhóm
            $result = [
                'employee' => [
                    'code' => $qualifications[0]['employee_code'] ?? '',
                    'name' => $qualifications[0]['employee_name'] ?? '',
                    'status' => $qualifications[0]['employee_status'] ?? '',
                    'department' => [
                        'id' => $qualifications[0]['department_id'] ?? null,
                        'name' => $qualifications[0]['department_name'] ?? '',
                        'description' => $qualifications[0]['department_description'] ?? '',
                        'manager' => $qualifications[0]['department_manager_name'] ?? ''
                    ]
                ],
                'degrees' => [],
                'certificates' => [],
                'courses' => []
            ];

            // Sử dụng mảng tạm để tránh trùng lặp
            $processed_degrees = [];
            $processed_certificates = [];
            $processed_courses = [];

            foreach ($qualifications as $row) {
                // Thêm bằng cấp nếu có và chưa được xử lý
                if (!empty($row['degree_name']) && !in_array($row['degree_id'], $processed_degrees)) {
                    $result['degrees'][] = [
                        'name' => $row['degree_name'],
                        'major' => $row['major'],
                        'institution' => $row['degree_institution'],
                        'graduation_date' => $row['graduation_date'],
                        'gpa' => $row['gpa'],
                        'attachment_url' => $row['degree_attachment']
                    ];
                    $processed_degrees[] = $row['degree_id'];
                }

                // Thêm chứng chỉ nếu có và chưa được xử lý
                if (!empty($row['certificate_name']) && !in_array($row['certificate_id'], $processed_certificates)) {
                    $result['certificates'][] = [
                        'name' => $row['certificate_name'],
                        'issuing_organization' => $row['issuing_organization'],
                        'issue_date' => $row['certificate_issue_date'],
                        'expiry_date' => $row['certificate_expiry_date'],
                        'credential_id' => $row['credential_id'],
                        'file_url' => $row['certificate_file']
                    ];
                    $processed_certificates[] = $row['certificate_id'];
                }

                // Thêm khóa học nếu có và chưa được xử lý
                if (!empty($row['course_name']) && !in_array($row['course_id'], $processed_courses)) {
                    $result['courses'][] = [
                        'name' => $row['course_name'],
                        'status' => $row['registration_status'],
                        'completion_date' => $row['completion_date'],
                        'score' => $row['course_score'],
                        'feedback' => $row['course_feedback'],
                        'evaluation' => [
                            'date' => $row['evaluation_date'],
                            'content_rating' => $row['rating_content'],
                            'instructor_rating' => $row['rating_instructor'],
                            'materials_rating' => $row['rating_materials'],
                            'comments' => $row['evaluation_comments']
                        ],
                        'evaluator' => [
                            'name' => $row['evaluator_name']
                        ]
                    ];
                    $processed_courses[] = $row['course_id'];
                }
            }
            
            writeLog("Successfully processed qualifications data");
            
            // Đảm bảo kết nối database sử dụng UTF-8
            $conn->exec("SET NAMES utf8mb4");
            $conn->exec("SET CHARACTER SET utf8mb4");
            $conn->exec("SET character_set_connection=utf8mb4");
            
            // Chuyển đổi encoding của dữ liệu
            $result = convertEncoding($result);
            
            // Đặt header cho response
            header('Content-Type: application/json; charset=utf-8');
            
            echo json_encode([
                'success' => true,
                'data' => $result
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (PDOException $e) {
            writeLog("Database error: " . $e->getMessage());
            handleApiError('Lỗi khi truy vấn database: ' . $e->getMessage());
        } catch (Exception $e) {
            writeLog("General error: " . $e->getMessage());
            handleApiError('Lỗi không xác định: ' . $e->getMessage());
        }
        break;
        
    case 'departments':
        try {
            $query = "SELECT id, name FROM departments ORDER BY name";
            $stmt = $conn->query($query);
            $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode([
                'success' => true,
                'data' => $departments
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (Exception $e) {
            handleApiError('Lỗi khi truy vấn phòng ban: ' . $e->getMessage());
        }
        exit();
        
    case 'employees':
        try {
            $params = [];
            $where = '';
            if (isset($_GET['department_id']) && is_numeric($_GET['department_id'])) {
                $where = 'WHERE department_id = ?';
                $params[] = $_GET['department_id'];
            }
            $query = "SELECT id, employee_code, name FROM employees $where ORDER BY employee_code";
            $stmt = $conn->prepare($query);
            $stmt->execute($params);
            $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode([
                'success' => true,
                'data' => $employees
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (Exception $e) {
            handleApiError('Lỗi khi truy vấn nhân viên: ' . $e->getMessage());
        }
        exit();
        
    default:
        handleApiError('Action không hợp lệ', 400);
        break;
} 