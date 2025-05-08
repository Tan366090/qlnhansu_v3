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
        if (!isset($_GET['employee'])) {
            handleApiError('Thiếu tham số employee', 400);
        }
        
        $employee_code = $_GET['employee'];
        writeLog("Fetching qualifications for employee code: $employee_code");
        
        try {
            // Kiểm tra xem nhân viên có tồn tại không
            $check_query = "SELECT id FROM employees WHERE employee_code = ?";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->execute([$employee_code]);
            
            if ($check_stmt->rowCount() === 0) {
                handleApiError('Không tìm thấy nhân viên với mã: ' . $employee_code, 404);
            }
            
            // Query để lấy thông tin đầy đủ về bằng cấp, chứng chỉ và khóa học
            $query = "SELECT DISTINCT
                e.employee_code,
                e.name as employee_name,
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
            WHERE e.employee_code = ?
            ORDER BY 
                d.graduation_date DESC,
                c.issue_date DESC,
                tr.completion_date DESC";
            
            $stmt = $conn->prepare($query);
            $stmt->execute([$employee_code]);
            $qualifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            writeLog("Found " . count($qualifications) . " qualification records");
            
            // Tổ chức dữ liệu thành các nhóm
            $result = [
                'employee' => [
                    'code' => $qualifications[0]['employee_code'] ?? '',
                    'name' => $qualifications[0]['employee_name'] ?? ''
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
                        'status' => $row['course_status'],
                        'completion_date' => $row['completion_date'],
                        'score' => $row['course_score'],
                        'evaluation' => [
                            'content_rating' => $row['rating_content'],
                            'instructor_rating' => $row['rating_instructor'],
                            'materials_rating' => $row['rating_materials'],
                            'comments' => $row['evaluation_comments']
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
        
    default:
        handleApiError('Action không hợp lệ', 400);
        break;
} 