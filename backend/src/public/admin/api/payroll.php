<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set error log path
$logFile = __DIR__ . '/../../../logs/payroll_error.log';
ini_set('error_log', $logFile);

// Create logs directory if it doesn't exist
if (!file_exists(dirname($logFile))) {
    mkdir(dirname($logFile), 0777, true);
}

// Log the start of the script
error_log("=== Starting Payroll API Request ===");
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Request URI: " . $_SERVER['REQUEST_URI']);

try {
    // Log the current directory
    error_log("Current directory: " . __DIR__);
    
    // Kiểm tra file config tồn tại
    $databaseFile = __DIR__ . '/../../../config/database.php';
    $functionsFile = __DIR__ . '/../../../config/functions.php';
    
    error_log("Looking for database file at: " . $databaseFile);
    error_log("Looking for functions file at: " . $functionsFile);
    
    if (!file_exists($databaseFile)) {
        throw new Exception("Không tìm thấy file database.php tại: " . $databaseFile);
    }
    if (!file_exists($functionsFile)) {
        throw new Exception("Không tìm thấy file functions.php tại: " . $functionsFile);
    }

    require_once $databaseFile;
    require_once $functionsFile;
    error_log("Successfully loaded database.php and functions.php");

    header('Content-Type: application/json');

    // Xử lý CORS
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    // Kết nối database
    error_log("Attempting to connect to database...");
    $conn = Database::getConnection();
    
    if (!$conn) {
        throw new Exception("Không thể kết nối đến database");
    }
    error_log("Successfully connected to database");

    // Test kết nối database
    try {
        error_log("Testing database connection...");
        $testQuery = "SELECT 1";
        $stmt = $conn->prepare($testQuery);
        $stmt->execute();
        error_log("Database connection test successful");
    } catch (PDOException $e) {
        error_log("Database connection test failed: " . $e->getMessage());
        throw new Exception("Lỗi kết nối database: " . $e->getMessage());
    }

    // Debug thông tin request
    error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
    error_log("Request URI: " . $_SERVER['REQUEST_URI']);
    error_log("GET params: " . print_r($_GET, true));

    // Lấy method và path
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $pathParts = explode('/', trim($path, '/'));

    // Debug path parts
    error_log("Path parts: " . print_r($pathParts, true));

    // Xử lý các endpoints
    switch ($method) {
        case 'GET':
            if (isset($_GET['action'])) {
                switch ($_GET['action']) {
                    case 'years':
                        getPayrollYears($conn);
                        break;
                    case 'components':
                        getSalaryComponents($conn);
                        break;
                    case 'approval-history':
                        if (isset($_GET['id'])) {
                            getPayrollApprovalHistory($conn, $_GET['id']);
                        } else {
                            http_response_code(400);
                            echo json_encode(['success' => false, 'message' => 'Thiếu tham số id']);
                        }
                        break;
                    case 'calculate':
                        if (isset($_GET['employee_id']) && isset($_GET['start_date']) && isset($_GET['end_date'])) {
                            try {
                                $result = calculateSalary($conn, $_GET['employee_id'], $_GET['start_date'], $_GET['end_date']);
                                echo json_encode(['success' => true, 'data' => $result]);
                            } catch (Exception $e) {
                                http_response_code(400);
                                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                            }
                        } else {
                            http_response_code(400);
                            echo json_encode(['success' => false, 'message' => 'Thiếu tham số bắt buộc']);
                        }
                        break;
                    case 'department-report':
                        if (isset($_GET['department_id']) && isset($_GET['month']) && isset($_GET['year'])) {
                            try {
                                exportDepartmentPayrollReport($conn, $_GET['department_id'], $_GET['month'], $_GET['year']);
                            } catch (Exception $e) {
                                http_response_code(400);
                                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                            }
                        } else {
                            http_response_code(400);
                            echo json_encode(['success' => false, 'message' => 'Thiếu tham số bắt buộc']);
                        }
                        break;
                    case 'getEmployeePayroll':
                        $employeeCode = $_GET['employeeCode'] ?? '';
                        
                        if (empty($employeeCode)) {
                            echo json_encode(['success' => false, 'message' => 'Mã nhân viên không được để trống']);
                            exit;
                        }

                        try {
                            // Lấy thông tin nhân viên
                            $stmt = $conn->prepare("
                                SELECT e.*, d.name as department_name, p.name as position_name 
                                FROM employees e
                                LEFT JOIN departments d ON e.department_id = d.id
                                LEFT JOIN positions p ON e.position_id = p.id
                                WHERE e.employee_code = ?
                            ");
                            $stmt->execute([$employeeCode]);
                            $employee = $stmt->fetch(PDO::FETCH_ASSOC);

                            if (!$employee) {
                                echo json_encode(['success' => false, 'message' => 'Không tìm thấy nhân viên']);
                                exit;
                            }

                            // Lấy thông tin lương thưởng
                            $stmt = $conn->prepare("
                                SELECT p.*, 
                                       e.employee_code, e.full_name as employee_name,
                                       d.name as department_name,
                                       pos.name as position_name
                                FROM payroll p
                                JOIN employees e ON p.employee_id = e.id
                                LEFT JOIN departments d ON e.department_id = d.id
                                LEFT JOIN positions pos ON e.position_id = pos.id
                                WHERE e.employee_code = ?
                                ORDER BY p.pay_period_start DESC
                            ");
                            $stmt->execute([$employeeCode]);
                            $payrolls = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            // Format dữ liệu trả về
                            $formattedPayrolls = array_map(function($payroll) {
                                return [
                                    'id' => $payroll['payroll_id'],
                                    'employee' => [
                                        'id' => $payroll['employee_id'],
                                        'code' => $payroll['employee_code'],
                                        'name' => $payroll['employee_name'],
                                        'department' => $payroll['department_name'],
                                        'position' => $payroll['position_name']
                                    ],
                                    'period' => [
                                        'start' => $payroll['pay_period_start'],
                                        'end' => $payroll['pay_period_end'],
                                        'month' => date('m/Y', strtotime($payroll['pay_period_start'])),
                                        'work_days' => number_format($payroll['work_days_payable'], 1)
                                    ],
                                    'salary' => [
                                        'base' => number_format($payroll['base_salary_period'], 0, ',', '.'),
                                        'allowances' => number_format($payroll['allowances_total'], 0, ',', '.'),
                                        'bonuses' => number_format($payroll['bonuses_total'], 0, ',', '.'),
                                        'deductions' => number_format($payroll['deductions_total'], 0, ',', '.'),
                                        'gross' => number_format($payroll['gross_salary'], 0, ',', '.'),
                                        'tax' => number_format($payroll['tax_deduction'], 0, ',', '.'),
                                        'insurance' => number_format($payroll['insurance_deduction'], 0, ',', '.'),
                                        'net' => number_format($payroll['net_salary'], 0, ',', '.')
                                    ],
                                    'status' => [
                                        'code' => $payroll['status'],
                                        'text' => getStatusText($payroll['status'])
                                    ],
                                    'created_at' => $payroll['created_at'],
                                    'created_by' => [
                                        'username' => $payroll['created_by']
                                    ],
                                    'notes' => $payroll['notes']
                                ];
                            }, $payrolls);

                            echo json_encode([
                                'success' => true,
                                'data' => [
                                    'employee' => [
                                        'id' => $employee['id'],
                                        'code' => $employee['employee_code'],
                                        'name' => $employee['full_name'],
                                        'department' => $employee['department_name'],
                                        'position' => $employee['position_name']
                                    ],
                                    'payrolls' => $formattedPayrolls
                                ]
                            ]);
                        } catch (PDOException $e) {
                            echo json_encode(['success' => false, 'message' => 'Lỗi khi lấy thông tin: ' . $e->getMessage()]);
                        }
                        exit;
                    case 'searchEmployee':
                        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                            $employeeCode = $_GET['employeeCode'] ?? '';
                            
                            if (empty($employeeCode)) {
                                echo json_encode([
                                    'success' => false,
                                    'message' => 'Mã nhân viên không được để trống'
                                ]);
                                exit;
                            }

                            try {
                                // Lấy thông tin nhân viên
                                $stmt = $conn->prepare("
                                    SELECT 
                                        e.id,
                                        e.employee_code,
                                        e.name,
                                        e.email,
                                        e.phone,
                                        d.name as department_name,
                                        p.name as position_name,
                                        e.hire_date,
                                        e.contract_type,
                                        e.contract_start_date,
                                        e.contract_end_date,
                                        e.base_salary,
                                        e.status
                                    FROM employees e
                                    LEFT JOIN departments d ON e.department_id = d.id
                                    LEFT JOIN positions p ON e.position_id = p.id
                                    WHERE e.employee_code = ?
                                ");
                                $stmt->execute([$employeeCode]);
                                $employee = $stmt->fetch();

                                if (!$employee) {
                                    echo json_encode([
                                        'success' => false,
                                        'message' => 'Không tìm thấy nhân viên'
                                    ]);
                                    exit;
                                }

                                // Lấy lịch sử lương
                                $stmt = $conn->prepare("
                                    SELECT 
                                        payroll_id,
                                        pay_period_start,
                                        pay_period_end,
                                        base_salary_period as basic_salary,
                                        allowances_total as allowances,
                                        bonuses_total as bonuses,
                                        deductions_total as deductions,
                                        net_salary,
                                        status,
                                        generated_at as created_at
                                    FROM payroll
                                    WHERE employee_id = ?
                                    ORDER BY pay_period_start DESC
                                ");
                                $stmt->execute([$employee['id']]);
                                $payrollHistory = $stmt->fetchAll();

                                echo json_encode([
                                    'success' => true,
                                    'data' => [
                                        'employee' => $employee,
                                        'payrollHistory' => $payrollHistory
                                    ]
                                ]);
                            } catch (PDOException $e) {
                                echo json_encode([
                                    'success' => false,
                                    'message' => 'Lỗi khi lấy thông tin: ' . $e->getMessage()
                                ]);
                            }
                        }
                        break;
                    default:
                        if (isset($_GET['id'])) {
                            getPayrollById($conn, $_GET['id']);
                        } else {
                            getPayrolls($conn);
                        }
                        break;
                }
            } else {
                getPayrolls($conn);
            }
            break;

        case 'POST':
            if (isset($_GET['action'])) {
                switch ($_GET['action']) {
                    case 'approve':
                        if (isset($_GET['id'])) {
                            $data = json_decode(file_get_contents('php://input'), true);
                            try {
                                $result = processPayrollApproval(
                                    $conn,
                                    $_GET['id'],
                                    $data['approver_id'],
                                    $data['action'],
                                    $data['comments'] ?? ''
                                );
                                echo json_encode(['success' => true, 'message' => 'Phê duyệt thành công']);
                            } catch (Exception $e) {
                                http_response_code(400);
                                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                            }
                        } else {
                            http_response_code(400);
                            echo json_encode(['success' => false, 'message' => 'Thiếu tham số id']);
                        }
                        break;
                    case 'export':
                        exportPayrolls($conn);
                        break;
                    default:
                        createPayroll($conn);
                        break;
                }
            } else {
                createPayroll($conn);
            }
            break;

        case 'PUT':
            if (isset($_GET['id'])) {
                // PUT /payroll.php?id={id} - Cập nhật phiếu lương
                updatePayroll($conn, $_GET['id']);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Thiếu tham số id']);
            }
            break;

        case 'DELETE':
            if (isset($_GET['id'])) {
                // DELETE /payroll.php?id={id} - Xóa phiếu lương
                deletePayroll($conn, $_GET['id']);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Thiếu tham số id']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method không được hỗ trợ']);
            break;
    }
} catch (Exception $e) {
    error_log("Error in payroll API: " . $e->getMessage());
    handleErrorResponse($e);
}

// Hàm lấy danh sách lương thưởng
function getPayrolls($conn) {
    try {
        // Get pagination parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;

        // Get total count
        $countStmt = $conn->prepare("SELECT COUNT(*) FROM payroll");
        $countStmt->execute();
        $totalItems = $countStmt->fetchColumn();
        $totalPages = ceil($totalItems / $limit);

        // Get payroll data with related information
        $stmt = $conn->prepare("
            SELECT 
                p.*,
                e.id as employee_id,
                e.employee_code,
                CASE 
                    WHEN e.name IS NOT NULL AND e.name != '' THEN e.name
                    WHEN e.email IS NOT NULL AND e.email != '' THEN e.email
                    ELSE e.employee_code
                END as employee_name,
                d.name as department_name,
                pos.name as position_name,
                u.user_id as created_by_id,
                u.username as created_by_username,
                u.email as created_by_email
            FROM payroll p
            LEFT JOIN employees e ON p.employee_id = e.id
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN positions pos ON e.position_id = pos.id
            LEFT JOIN users u ON p.generated_by_user_id = u.user_id
            ORDER BY p.generated_at DESC
            LIMIT :limit OFFSET :offset
        ");

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $payrolls = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format the response
        $formattedPayrolls = array_map(function($payroll) {
            return [
                'id' => $payroll['payroll_id'],
                'employee' => [
                    'id' => $payroll['employee_id'],
                    'code' => $payroll['employee_code'],
                    'name' => $payroll['employee_name'],
                    'department' => $payroll['department_name'],
                    'position' => $payroll['position_name']
                ],
                'period' => [
                    'start' => $payroll['pay_period_start'],
                    'end' => $payroll['pay_period_end'],
                    'month' => date('m/Y', strtotime($payroll['pay_period_start'])),
                    'work_days' => number_format($payroll['work_days_payable'], 1)
                ],
                'salary' => [
                    'base' => number_format($payroll['base_salary_period'], 0, ',', '.'),
                    'allowances' => number_format($payroll['allowances_total'], 0, ',', '.'),
                    'bonuses' => number_format($payroll['bonuses_total'], 0, ',', '.'),
                    'deductions' => number_format($payroll['deductions_total'], 0, ',', '.'),
                    'gross' => number_format($payroll['gross_salary'], 0, ',', '.'),
                    'tax' => number_format($payroll['tax_deduction'], 0, ',', '.'),
                    'insurance' => number_format($payroll['insurance_deduction'], 0, ',', '.'),
                    'net' => number_format($payroll['net_salary'], 0, ',', '.')
                ],
                'payment' => [
                    'date' => $payroll['payment_date'],
                    'method' => $payroll['payment_method'] ?? 'bank_transfer',
                    'method_text' => $payroll['payment_method'] === 'bank_transfer' ? 'Chuyển khoản' : 'Tiền mặt',
                    'reference' => $payroll['payment_reference'] ?? 'PAY-' . $payroll['payroll_id'] . '-' . date('Ymd', strtotime($payroll['payment_date']))
                ],
                'status' => [
                    'code' => $payroll['status'],
                    'text' => $payroll['status'] === 'paid' ? 'Đã thanh toán' : 'Chờ thanh toán'
                ],
                'created_by' => [
                    'id' => $payroll['created_by_id'],
                    'username' => $payroll['created_by_username'],
                    'email' => $payroll['created_by_email']
                ],
                'created_at' => $payroll['generated_at'],
                'notes' => $payroll['notes']
            ];
        }, $payrolls);

        echo json_encode([
            'success' => true,
            'data' => $formattedPayrolls,
            'totalItems' => $totalItems,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ]);
    } catch (Exception $e) {
        error_log("Error in getPayrolls: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi khi lấy dữ liệu lương']);
    }
}

// Hàm lấy chi tiết một phiếu lương
function getPayrollById($conn, $id) {
    // Lấy thông tin cơ bản
    $query = "SELECT p.*, e.employee_code, e.full_name as employee_name, 
                     d.name as department_name
              FROM payroll p
              LEFT JOIN employees e ON p.employee_id = e.id
              LEFT JOIN departments d ON e.department_id = d.id
              WHERE p.payroll_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$id]);
    $payroll = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payroll) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy phiếu lương']);
        return;
    }

    // Lấy chi tiết các thành phần lương
    $detailsQuery = "SELECT pd.*, sc.name as component_name, sc.type
                    FROM payroll_details pd
                    LEFT JOIN salary_components sc ON pd.component_id = sc.component_id
                    WHERE pd.payroll_id = ?";
    
    $stmt = $conn->prepare($detailsQuery);
    $stmt->execute([$id]);
    $payroll['details'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Lấy thông tin phê duyệt
    $approvalsQuery = "SELECT pa.*, u.full_name as approver_name
                      FROM payroll_approvals pa
                      LEFT JOIN users u ON pa.approver_id = u.id
                      WHERE pa.payroll_id = ?
                      ORDER BY pa.approval_level";
    
    $stmt = $conn->prepare($approvalsQuery);
    $stmt->execute([$id]);
    $payroll['approval_steps'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $payroll
    ]);
}

// Hàm phê duyệt/từ chối phiếu lương
function approvePayroll($conn, $id) {
    $data = json_decode(file_get_contents('php://input'), true);
    $status = $data['status'] ?? '';
    $comments = $data['comments'] ?? '';

    if (!in_array($status, ['APPROVED', 'REJECTED'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Trạng thái không hợp lệ']);
        return;
    }

    // Lấy thông tin người dùng hiện tại (từ session hoặc token)
    $currentUserId = getCurrentUserId(); // Implement this function based on your auth system

    // Kiểm tra quyền phê duyệt
    $checkQuery = "SELECT approval_level FROM payroll_approvals 
                  WHERE payroll_id = ? AND approver_id = ? AND status = 'pending'
                  ORDER BY approval_level ASC LIMIT 1";
    
    $stmt = $conn->prepare($checkQuery);
    $stmt->execute([$id, $currentUserId]);
    $approval = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$approval) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Bạn không có quyền phê duyệt phiếu lương này']);
        return;
    }

    // Cập nhật trạng thái phê duyệt
    $updateQuery = "UPDATE payroll_approvals 
                   SET status = ?, comments = ?, approved_at = NOW()
                   WHERE payroll_id = ? AND approval_level = ?";
    
    $stmt = $conn->prepare($updateQuery);
    $stmt->execute([$status, $comments, $id, $approval['approval_level']]);

    // Nếu bị từ chối, cập nhật trạng thái phiếu lương
    if ($status === 'REJECTED') {
        $updatePayrollQuery = "UPDATE payroll SET status = 'rejected' WHERE payroll_id = ?";
        $stmt = $conn->prepare($updatePayrollQuery);
        $stmt->execute([$id]);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Cập nhật trạng thái phê duyệt thành công'
    ]);
}

// Hàm xử lý export Excel
function exportPayrolls($conn) {
    try {
        // Enable error reporting
        error_reporting(E_ALL);
        ini_set('display_errors', 0);
        ini_set('log_errors', 1);
        ini_set('error_log', __DIR__ . '/../../../../logs/php_errors.log');

        // Log the request
        error_log("Export request received: " . print_r($_POST, true));

        // Lấy dữ liệu từ request
        $search = $_POST['search'] ?? '';
        $department = $_POST['department'] ?? '';
        $month = $_POST['month'] ?? date('m');
        $year = $_POST['year'] ?? date('Y');

        // Log the parameters
        error_log("Export parameters: search=$search, department=$department, month=$month, year=$year");

        // Xây dựng câu truy vấn
        $sql = "SELECT 
                    p.*,
                    e.employee_code,
                    e.name as employee_name,
                    d.name as department_name,
                    pos.name as position_name
                FROM payroll p
                LEFT JOIN employees e ON p.employee_id = e.id
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions pos ON e.position_id = pos.id
                WHERE 1=1";

        $params = array();

        if (!empty($search)) {
            $sql .= " AND (e.employee_code LIKE ? OR e.name LIKE ?)";
            $searchParam = "%$search%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (!empty($department)) {
            $sql .= " AND e.department_id = ?";
            $params[] = $department;
        }

        if (!empty($month) && !empty($year)) {
            $sql .= " AND MONTH(p.pay_period_start) = ? AND YEAR(p.pay_period_start) = ?";
            $params[] = $month;
            $params[] = $year;
        }

        $sql .= " ORDER BY p.pay_period_start DESC, e.employee_code ASC";

        // Log the SQL query
        error_log("Export SQL: $sql");
        error_log("Export parameters: " . print_r($params, true));

        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->execute($params);
        } else {
            $stmt->execute();
        }
        $payrolls = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Log the results
        error_log("Export results count: " . count($payrolls));

        if (empty($payrolls)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Không có dữ liệu để xuất']);
            exit;
        }

        // Tạo file Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Thiết lập header
        $sheet->setCellValue('A1', 'Mã nhân viên');
        $sheet->setCellValue('B1', 'Họ và tên');
        $sheet->setCellValue('C1', 'Phòng ban');
        $sheet->setCellValue('D1', 'Chức vụ');
        $sheet->setCellValue('E1', 'Kỳ lương');
        $sheet->setCellValue('F1', 'Lương cơ bản');
        $sheet->setCellValue('G1', 'Phụ cấp');
        $sheet->setCellValue('H1', 'Thưởng');
        $sheet->setCellValue('I1', 'Khấu trừ');
        $sheet->setCellValue('J1', 'Tổng lương');
        $sheet->setCellValue('K1', 'Thuế TNCN');
        $sheet->setCellValue('L1', 'Bảo hiểm');
        $sheet->setCellValue('M1', 'Thực lĩnh');
        $sheet->setCellValue('N1', 'Trạng thái');
        $sheet->setCellValue('O1', 'Ghi chú');

        // Định dạng header
        $headerStyle = $sheet->getStyle('A1:O1');
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('CCCCCC');

        // Điền dữ liệu
        $row = 2;
        foreach ($payrolls as $payroll) {
            $sheet->setCellValue('A' . $row, $payroll['employee_code']);
            $sheet->setCellValue('B' . $row, $payroll['employee_name']);
            $sheet->setCellValue('C' . $row, $payroll['department_name']);
            $sheet->setCellValue('D' . $row, $payroll['position_name']);
            $sheet->setCellValue('E' . $row, date('m/Y', strtotime($payroll['pay_period_start'])));
            $sheet->setCellValue('F' . $row, $payroll['base_salary_period']);
            $sheet->setCellValue('G' . $row, $payroll['allowances_total']);
            $sheet->setCellValue('H' . $row, $payroll['bonuses_total']);
            $sheet->setCellValue('I' . $row, $payroll['deductions_total']);
            $sheet->setCellValue('J' . $row, $payroll['gross_salary']);
            $sheet->setCellValue('K' . $row, $payroll['tax_deduction']);
            $sheet->setCellValue('L' . $row, $payroll['insurance_deduction']);
            $sheet->setCellValue('M' . $row, $payroll['net_salary']);
            $sheet->setCellValue('N' . $row, getStatusText($payroll['status']));
            $sheet->setCellValue('O' . $row, $payroll['notes']);
            $row++;
        }

        // Định dạng cột số
        $numberColumns = ['F', 'G', 'H', 'I', 'J', 'K', 'L', 'M'];
        foreach ($numberColumns as $col) {
            $sheet->getStyle($col . '2:' . $col . ($row - 1))->getNumberFormat()->setFormatCode('#,##0');
        }

        // Tự động điều chỉnh độ rộng cột
        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Tạo file Excel
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'payroll_export_' . date('Ymd_His') . '.xlsx';
        $filepath = __DIR__ . '/../../../../temp/' . $filename;
        
        // Đảm bảo thư mục temp tồn tại
        if (!file_exists(__DIR__ . '/../../../../temp')) {
            mkdir(__DIR__ . '/../../../../temp', 0777, true);
        }
        
        $writer->save($filepath);

        // Log file creation
        error_log("Excel file created at: $filepath");

        // Trả về file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        unlink($filepath); // Xóa file sau khi gửi
        exit;

    } catch (Exception $e) {
        error_log("Export error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Lỗi khi xuất file Excel: ' . $e->getMessage()]);
        exit;
    }
}

// Hàm tạo phiếu lương mới
function createPayroll($conn) {
    try {
        // Validate input
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            throw new Exception('Invalid JSON data');
        }

        // Required fields
        $requiredFields = ['employee_id', 'pay_period_start', 'pay_period_end', 'basic_salary'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        // Validate dates
        $startDate = strtotime($data['pay_period_start']);
        $endDate = strtotime($data['pay_period_end']);
        if (!$startDate || !$endDate || $startDate > $endDate) {
            throw new Exception('Invalid date range');
        }

        // Begin transaction
        $conn->beginTransaction();

        // Insert payroll record
        $query = "INSERT INTO payroll (employee_id, pay_period_start, pay_period_end, basic_salary, 
                                     allowances, bonuses, deductions, net_salary, status, created_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?)";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([
            $data['employee_id'],
            $data['pay_period_start'],
            $data['pay_period_end'],
            $data['basic_salary'],
            $data['allowances'] ?? 0,
            $data['bonuses'] ?? 0,
            $data['deductions'] ?? 0,
            $data['net_salary'] ?? $data['basic_salary'],
            getCurrentUserId()
        ]);

        $payrollId = $conn->lastInsertId();

        // Insert payroll details if provided
        if (isset($data['details']) && is_array($data['details'])) {
            $detailsQuery = "INSERT INTO payroll_details (payroll_id, component_id, amount, type)
                           VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($detailsQuery);
            
            foreach ($data['details'] as $detail) {
                $stmt->execute([
                    $payrollId,
                    $detail['component_id'],
                    $detail['amount'],
                    $detail['type']
                ]);
            }
        }

        // Create approval steps
        createApprovalSteps($conn, $payrollId);

        // Commit transaction
        $conn->commit();

        // Log activity
        logActivity(getCurrentUserId(), 'CREATE_PAYROLL', "Created new payroll for employee ID: {$data['employee_id']}");

        echo json_encode([
            'success' => true,
            'message' => 'Tạo phiếu lương thành công',
            'data' => ['id' => $payrollId]
        ]);

    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("Error in createPayroll: " . $e->getMessage());
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

// Hàm cập nhật phiếu lương
function updatePayroll($conn, $id) {
    try {
        // Validate input
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            throw new Exception('Invalid JSON data');
        }

        // Check if payroll exists and is editable
        $checkQuery = "SELECT status FROM payroll WHERE id = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->execute([$id]);
        $payroll = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$payroll) {
            throw new Exception('Payroll not found');
        }

        if ($payroll['status'] !== 'draft') {
            throw new Exception('Cannot edit payroll that is not in draft status');
        }

        // Begin transaction
        $conn->beginTransaction();

        // Update payroll record
        $updateFields = [];
        $params = [];
        
        $allowedFields = ['pay_period_start', 'pay_period_end', 'basic_salary', 
                         'allowances', 'bonuses', 'deductions', 'net_salary'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateFields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (!empty($updateFields)) {
            $params[] = $id;
            $query = "UPDATE payroll SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute($params);
        }

        // Update payroll details if provided
        if (isset($data['details']) && is_array($data['details'])) {
            // Delete existing details
            $deleteQuery = "DELETE FROM payroll_details WHERE payroll_id = ?";
            $stmt = $conn->prepare($deleteQuery);
            $stmt->execute([$id]);

            // Insert new details
            $detailsQuery = "INSERT INTO payroll_details (payroll_id, component_id, amount, type)
                           VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($detailsQuery);
            
            foreach ($data['details'] as $detail) {
                $stmt->execute([
                    $id,
                    $detail['component_id'],
                    $detail['amount'],
                    $detail['type']
                ]);
            }
        }

        // Commit transaction
        $conn->commit();

        // Log activity
        logActivity(getCurrentUserId(), 'UPDATE_PAYROLL', "Updated payroll ID: $id");

        echo json_encode([
            'success' => true,
            'message' => 'Cập nhật phiếu lương thành công'
        ]);

    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("Error in updatePayroll: " . $e->getMessage());
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

// Hàm xóa phiếu lương
function deletePayroll($conn, $id) {
    try {
        // Check if payroll exists and is deletable
        $checkQuery = "SELECT status FROM payroll WHERE id = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->execute([$id]);
        $payroll = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$payroll) {
            throw new Exception('Payroll not found');
        }

        if ($payroll['status'] !== 'draft') {
            throw new Exception('Cannot delete payroll that is not in draft status');
        }

        // Begin transaction
        $conn->beginTransaction();

        // Delete payroll details
        $deleteDetailsQuery = "DELETE FROM payroll_details WHERE payroll_id = ?";
        $stmt = $conn->prepare($deleteDetailsQuery);
        $stmt->execute([$id]);

        // Delete approval steps
        $deleteApprovalsQuery = "DELETE FROM payroll_approvals WHERE payroll_id = ?";
        $stmt = $conn->prepare($deleteApprovalsQuery);
        $stmt->execute([$id]);

        // Delete payroll
        $deletePayrollQuery = "DELETE FROM payroll WHERE id = ?";
        $stmt = $conn->prepare($deletePayrollQuery);
        $stmt->execute([$id]);

        // Commit transaction
        $conn->commit();

        // Log activity
        logActivity(getCurrentUserId(), 'DELETE_PAYROLL', "Deleted payroll ID: $id");

        echo json_encode([
            'success' => true,
            'message' => 'Xóa phiếu lương thành công'
        ]);

    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("Error in deletePayroll: " . $e->getMessage());
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

// Hàm lấy lịch sử phê duyệt
function getPayrollApprovalHistory($conn, $id) {
    try {
        $query = "SELECT pa.*, u.full_name as approver_name
                 FROM payroll_approvals pa
                 LEFT JOIN users u ON pa.approver_id = u.id
                 WHERE pa.payroll_id = ?
                 ORDER BY pa.approval_level";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([$id]);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => $history
        ]);

    } catch (Exception $e) {
        error_log("Error in getPayrollApprovalHistory: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

// Hàm tạo các bước phê duyệt
function createApprovalSteps($conn, $payrollId) {
    try {
        // Get approval levels from configuration
        $approvalLevels = getApprovalLevels(); // Implement this function based on your system

        $query = "INSERT INTO payroll_approvals (payroll_id, approval_level, approver_id, status)
                 VALUES (?, ?, ?, 'pending')";
        $stmt = $conn->prepare($query);

        foreach ($approvalLevels as $level) {
            $stmt->execute([
                $payrollId,
                $level['level'],
                $level['approver_id']
            ]);
        }

    } catch (Exception $e) {
        throw new Exception("Error creating approval steps: " . $e->getMessage());
    }
}

// Thêm vào cuối file
function getPayrollYears($conn) {
    try {
        $query = "SELECT DISTINCT YEAR(pay_period_start) as year FROM payroll ORDER BY year DESC";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $years = $stmt->fetchAll(PDO::FETCH_COLUMN);

        echo json_encode([
            'success' => true,
            'data' => $years
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi lấy danh sách năm: ' . $e->getMessage()
        ]);
    }
}

// Hàm tính toán lương tự động
function calculateSalary($conn, $employeeId, $periodStart, $periodEnd) {
    try {
        // Lấy thông tin nhân viên
        $employeeQuery = "SELECT e.*, d.name as department_name 
                         FROM employees e 
                         LEFT JOIN departments d ON e.department_id = d.id 
                         WHERE e.id = ?";
        $stmt = $conn->prepare($employeeQuery);
        $stmt->execute([$employeeId]);
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$employee) {
            throw new Exception('Không tìm thấy thông tin nhân viên');
        }

        // Tính số ngày công thực tế
        $workDaysQuery = "SELECT COUNT(*) as work_days 
                         FROM attendance 
                         WHERE employee_id = ? 
                         AND date BETWEEN ? AND ?
                         AND status = 'present'";
        $stmt = $conn->prepare($workDaysQuery);
        $stmt->execute([$employeeId, $periodStart, $periodEnd]);
        $workDays = $stmt->fetch(PDO::FETCH_ASSOC)['work_days'];

        // Lấy các thành phần lương
        $componentsQuery = "SELECT sc.*, ec.amount 
                          FROM employee_salary_components ec
                          JOIN salary_components sc ON ec.component_id = sc.id
                          WHERE ec.employee_id = ? AND sc.is_active = 1";
        $stmt = $conn->prepare($componentsQuery);
        $stmt->execute([$employeeId]);
        $components = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Tính lương cơ bản theo ngày công
        $basicSalary = $employee['basic_salary'] * ($workDays / 22); // Giả sử 22 ngày công/tháng

        // Tính các khoản phụ cấp
        $allowances = 0;
        $bonuses = 0;
        $deductions = 0;

        foreach ($components as $component) {
            switch ($component['type']) {
                case 'allowance':
                    $allowances += $component['amount'];
                    break;
                case 'bonus':
                    $bonuses += $component['amount'];
                    break;
                case 'deduction':
                    $deductions += $component['amount'];
                    break;
            }
        }

        // Tính bảo hiểm xã hội (8.5% lương cơ bản)
        $insurance = $basicSalary * 0.085;

        // Tính thuế thu nhập cá nhân
        $taxableIncome = $basicSalary + $allowances + $bonuses - $insurance;
        $tax = calculateTax($taxableIncome);

        // Tính lương thực lĩnh
        $netSalary = $basicSalary + $allowances + $bonuses - $deductions - $insurance - $tax;

        return [
            'employee' => $employee,
            'work_days' => $workDays,
            'basic_salary' => $basicSalary,
            'allowances' => $allowances,
            'bonuses' => $bonuses,
            'deductions' => $deductions,
            'insurance' => $insurance,
            'tax' => $tax,
            'net_salary' => $netSalary
        ];
    } catch (Exception $e) {
        throw new Exception("Lỗi tính lương: " . $e->getMessage());
    }
}

// Hàm tính thuế thu nhập cá nhân
function calculateTax($taxableIncome) {
    // Bảng thuế suất theo bậc
    $taxBrackets = [
        ['threshold' => 5000000, 'rate' => 0.05],
        ['threshold' => 10000000, 'rate' => 0.1],
        ['threshold' => 18000000, 'rate' => 0.15],
        ['threshold' => 32000000, 'rate' => 0.2],
        ['threshold' => 52000000, 'rate' => 0.25],
        ['threshold' => 80000000, 'rate' => 0.3],
        ['threshold' => PHP_FLOAT_MAX, 'rate' => 0.35]
    ];

    $tax = 0;
    $remainingIncome = $taxableIncome;
    $previousThreshold = 0;

    foreach ($taxBrackets as $bracket) {
        if ($taxableIncome > $previousThreshold) {
            $taxableAmount = min($remainingIncome, $bracket['threshold'] - $previousThreshold);
            $tax += $taxableAmount * $bracket['rate'];
            $remainingIncome -= $taxableAmount;
        }
        $previousThreshold = $bracket['threshold'];
    }

    return $tax;
}

// Hàm xử lý phê duyệt lương theo nhiều cấp
function processPayrollApproval($conn, $payrollId, $approverId, $action, $comments = '') {
    try {
        $conn->beginTransaction();

        // Lấy thông tin phê duyệt hiện tại
        $query = "SELECT pa.*, p.status as payroll_status 
                 FROM payroll_approvals pa
                 JOIN payroll p ON pa.payroll_id = p.id
                 WHERE pa.payroll_id = ? AND pa.approver_id = ?
                 AND pa.status = 'pending'
                 ORDER BY pa.approval_level ASC
                 LIMIT 1";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([$payrollId, $approverId]);
        $approval = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$approval) {
            throw new Exception('Không tìm thấy bước phê duyệt hoặc đã được phê duyệt');
        }

        // Cập nhật trạng thái phê duyệt
        $updateQuery = "UPDATE payroll_approvals 
                       SET status = ?, comments = ?, approved_at = NOW()
                       WHERE payroll_id = ? AND approval_level = ?";
        
        $stmt = $conn->prepare($updateQuery);
        $stmt->execute([$action, $comments, $payrollId, $approval['approval_level']]);

        // Nếu bị từ chối, cập nhật trạng thái phiếu lương
        if ($action === 'REJECTED') {
            $updatePayrollQuery = "UPDATE payroll SET status = 'rejected' WHERE id = ?";
            $stmt = $conn->prepare($updatePayrollQuery);
            $stmt->execute([$payrollId]);
        } else {
            // Kiểm tra xem có phải bước phê duyệt cuối cùng không
            $checkQuery = "SELECT COUNT(*) as remaining 
                          FROM payroll_approvals 
                          WHERE payroll_id = ? AND status = 'pending'";
            
            $stmt = $conn->prepare($checkQuery);
            $stmt->execute([$payrollId]);
            $remaining = $stmt->fetch(PDO::FETCH_ASSOC)['remaining'];

            if ($remaining === 0) {
                // Cập nhật trạng thái phiếu lương thành đã duyệt
                $updatePayrollQuery = "UPDATE payroll SET status = 'approved' WHERE id = ?";
                $stmt = $conn->prepare($updatePayrollQuery);
                $stmt->execute([$payrollId]);
            }
        }

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollBack();
        throw new Exception("Lỗi xử lý phê duyệt: " . $e->getMessage());
    }
}

// Hàm xuất báo cáo lương theo phòng ban
function exportDepartmentPayrollReport($conn, $departmentId, $month, $year) {
    try {
        // Lấy thông tin phòng ban
        $deptQuery = "SELECT name FROM departments WHERE id = ?";
        $stmt = $conn->prepare($deptQuery);
        $stmt->execute([$departmentId]);
        $department = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$department) {
            throw new Exception('Không tìm thấy thông tin phòng ban');
        }

        // Lấy danh sách lương của nhân viên trong phòng ban
        $query = "SELECT p.*, e.code as employee_code, e.full_name as employee_name,
                        e.position, p.work_days, p.basic_salary, p.allowances,
                        p.bonuses, p.deductions, p.insurance, p.tax, p.net_salary
                 FROM payroll p
                 JOIN employees e ON p.employee_id = e.id
                 WHERE e.department_id = ?
                 AND MONTH(p.period_start) = ?
                 AND YEAR(p.period_start) = ?
                 ORDER BY e.code";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([$departmentId, $month, $year]);
        $payrolls = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($payrolls)) {
            throw new Exception('Không có dữ liệu lương cho phòng ban này');
        }

        // Tạo file Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = [
            'A1' => 'Mã NV',
            'B1' => 'Họ tên',
            'C1' => 'Chức vụ',
            'D1' => 'Ngày công',
            'E1' => 'Lương cơ bản',
            'F1' => 'Phụ cấp',
            'G1' => 'Thưởng',
            'H1' => 'Khấu trừ',
            'I1' => 'Bảo hiểm',
            'J1' => 'Thuế TNCN',
            'K1' => 'Thực lĩnh'
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Fill data
        $row = 2;
        $totalBasic = 0;
        $totalAllowances = 0;
        $totalBonuses = 0;
        $totalDeductions = 0;
        $totalInsurance = 0;
        $totalTax = 0;
        $totalNet = 0;

        foreach ($payrolls as $payroll) {
            $sheet->setCellValue('A' . $row, $payroll['employee_code']);
            $sheet->setCellValue('B' . $row, $payroll['employee_name']);
            $sheet->setCellValue('C' . $row, $payroll['position']);
            $sheet->setCellValue('D' . $row, $payroll['work_days']);
            $sheet->setCellValue('E' . $row, $payroll['basic_salary']);
            $sheet->setCellValue('F' . $row, $payroll['allowances']);
            $sheet->setCellValue('G' . $row, $payroll['bonuses']);
            $sheet->setCellValue('H' . $row, $payroll['deductions']);
            $sheet->setCellValue('I' . $row, $payroll['insurance']);
            $sheet->setCellValue('J' . $row, $payroll['tax']);
            $sheet->setCellValue('K' . $row, $payroll['net_salary']);

            $totalBasic += $payroll['basic_salary'];
            $totalAllowances += $payroll['allowances'];
            $totalBonuses += $payroll['bonuses'];
            $totalDeductions += $payroll['deductions'];
            $totalInsurance += $payroll['insurance'];
            $totalTax += $payroll['tax'];
            $totalNet += $payroll['net_salary'];

            $row++;
        }

        // Add totals
        $sheet->setCellValue('A' . $row, 'TỔNG CỘNG');
        $sheet->setCellValue('E' . $row, $totalBasic);
        $sheet->setCellValue('F' . $row, $totalAllowances);
        $sheet->setCellValue('G' . $row, $totalBonuses);
        $sheet->setCellValue('H' . $row, $totalDeductions);
        $sheet->setCellValue('I' . $row, $totalInsurance);
        $sheet->setCellValue('J' . $row, $totalTax);
        $sheet->setCellValue('K' . $row, $totalNet);

        // Format cells
        $moneyColumns = ['E', 'F', 'G', 'H', 'I', 'J', 'K'];
        foreach ($moneyColumns as $col) {
            $sheet->getStyle($col . '2:' . $col . $row)->getNumberFormat()
                ->setFormatCode('#,##0');
        }

        // Auto-size columns
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="payroll_report_' . $department['name'] . '_' . $month . '_' . $year . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;

    } catch (Exception $e) {
        throw new Exception("Lỗi xuất báo cáo: " . $e->getMessage());
    }
}
?> 