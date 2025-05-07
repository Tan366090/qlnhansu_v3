<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Employee.php';
require_once __DIR__ . '/../models/Department.php';
require_once __DIR__ . '/../models/Position.php';

class EmployeeController {
    private $db;
    private $employeeModel;
    private $departmentModel;
    private $positionModel;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->employeeModel = new Employee($this->db);
        $this->departmentModel = new Department($this->db);
        $this->positionModel = new Position($this->db);
    }

    public function getEmployees() {
        try {
            // Get query parameters
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $pageSize = isset($_GET['pageSize']) ? (int)$_GET['pageSize'] : 10;
            $search = isset($_GET['search']) ? $_GET['search'] : '';
            $department = isset($_GET['department']) ? $_GET['department'] : '';
            $status = isset($_GET['status']) ? $_GET['status'] : '';

            // Calculate offset
            $offset = ($page - 1) * $pageSize;

            // Build query
            $query = "SELECT e.*, d.name as department_name, p.name as position_name 
                     FROM employees e 
                     LEFT JOIN departments d ON e.department_id = d.id 
                     LEFT JOIN positions p ON e.position_id = p.id 
                     WHERE 1=1";
            $params = [];

            if (!empty($search)) {
                $query .= " AND (e.full_name LIKE ? OR e.employee_id LIKE ? OR e.email LIKE ?)";
                $searchParam = "%$search%";
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($department)) {
                $query .= " AND e.department_id = ?";
                $params[] = $department;
            }

            if (!empty($status)) {
                $query .= " AND e.status = ?";
                $params[] = $status;
            }

            // Get total count
            $countQuery = "SELECT COUNT(*) as total FROM ($query) as count_table";
            $stmt = $this->db->prepare($countQuery);
            $stmt->execute($params);
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Add pagination
            $query .= " ORDER BY e.created_at DESC LIMIT ? OFFSET ?";
            $params[] = $pageSize;
            $params[] = $offset;

            // Execute query
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Return response
            echo json_encode([
                'success' => true,
                'data' => [
                    'employees' => $employees,
                    'total' => $total,
                    'page' => $page,
                    'pageSize' => $pageSize,
                    'totalPages' => ceil($total / $pageSize)
                ]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Lỗi khi lấy danh sách nhân viên: ' . $e->getMessage()
            ]);
        }
    }

    public function createEmployee() {
        try {
            // Get POST data
            $data = json_decode(file_get_contents('php://input'), true);

            // Validate required fields
            $requiredFields = ['full_name', 'gender', 'birth_date', 'phone', 'email', 'address', 'department_id', 'position_id', 'username', 'password'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Trường $field là bắt buộc");
                }
            }

            // Generate employee ID
            $data['employee_id'] = $this->generateEmployeeId();

            // Hash password
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

            // Set default values
            $data['status'] = 'active';
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');

            // Insert employee
            $this->employeeModel->create($data);

            echo json_encode([
                'success' => true,
                'message' => 'Thêm nhân viên thành công'
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function updateEmployee($id) {
        try {
            // Get PUT data
            $data = json_decode(file_get_contents('php://input'), true);

            // Validate employee exists
            $employee = $this->employeeModel->getById($id);
            if (!$employee) {
                throw new Exception('Nhân viên không tồn tại');
            }

            // Update employee
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->employeeModel->update($id, $data);

            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật nhân viên thành công'
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function deleteEmployee($id) {
        try {
            // Validate employee exists
            $employee = $this->employeeModel->getById($id);
            if (!$employee) {
                throw new Exception('Nhân viên không tồn tại');
            }

            // Delete employee
            $this->employeeModel->delete($id);

            echo json_encode([
                'success' => true,
                'message' => 'Xóa nhân viên thành công'
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function exportEmployees() {
        try {
            // Get query parameters
            $search = isset($_GET['search']) ? $_GET['search'] : '';
            $department = isset($_GET['department']) ? $_GET['department'] : '';
            $status = isset($_GET['status']) ? $_GET['status'] : '';

            // Build query
            $query = "SELECT e.*, d.name as department_name, p.name as position_name 
                     FROM employees e 
                     LEFT JOIN departments d ON e.department_id = d.id 
                     LEFT JOIN positions p ON e.position_id = p.id 
                     WHERE 1=1";
            $params = [];

            if (!empty($search)) {
                $query .= " AND (e.full_name LIKE ? OR e.employee_id LIKE ? OR e.email LIKE ?)";
                $searchParam = "%$search%";
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($department)) {
                $query .= " AND e.department_id = ?";
                $params[] = $department;
            }

            if (!empty($status)) {
                $query .= " AND e.status = ?";
                $params[] = $status;
            }

            // Execute query
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Generate Excel file
            $filename = 'danh_sach_nhan_vien_' . date('Y-m-d') . '.xlsx';
            $this->generateExcel($employees, $filename);

            // Set headers for download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            // Output file
            readfile($filename);
            unlink($filename);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Lỗi khi xuất file Excel: ' . $e->getMessage()
            ]);
        }
    }

    private function generateEmployeeId() {
        // Generate a unique employee ID (e.g., NV-YYYYMMDD-XXXX)
        $prefix = 'NV';
        $date = date('Ymd');
        $random = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        return $prefix . '-' . $date . '-' . $random;
    }

    private function generateExcel($data, $filename) {
        require_once __DIR__ . '/../vendor/autoload.php';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = [
            'Mã NV', 'Họ tên', 'Giới tính', 'Ngày sinh', 'SĐT', 'Email',
            'Địa chỉ', 'Phòng ban', 'Vị trí', 'Trạng thái', 'Ngày tạo'
        ];
        $sheet->fromArray($headers, NULL, 'A1');

        // Set data
        $row = 2;
        foreach ($data as $employee) {
            $sheet->fromArray([
                $employee['employee_id'],
                $employee['full_name'],
                $employee['gender'] === 'male' ? 'Nam' : ($employee['gender'] === 'female' ? 'Nữ' : 'Khác'),
                date('d/m/Y', strtotime($employee['birth_date'])),
                $employee['phone'],
                $employee['email'],
                $employee['address'],
                $employee['department_name'],
                $employee['position_name'],
                $employee['status'] === 'active' ? 'Đang làm việc' : 'Đã nghỉ việc',
                date('d/m/Y H:i', strtotime($employee['created_at']))
            ], NULL, 'A' . $row);
            $row++;
        }

        // Auto size columns
        foreach (range('A', 'K') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Save file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($filename);
    }

    public function addEmployee($data) {
        try {
            // Bắt đầu transaction
            $this->db->beginTransaction();

            // 1. Thêm thông tin người dùng
            $userQuery = "INSERT INTO users (email, password, role_id) VALUES (?, ?, ?)";
            $userStmt = $this->db->prepare($userQuery);
            $userStmt->execute([
                $data['email'],
                password_hash('123456', PASSWORD_DEFAULT), // Mật khẩu mặc định
                2 // Role nhân viên
            ]);
            $userId = $this->db->lastInsertId();

            // 2. Thêm thông tin nhân viên
            $employeeQuery = "INSERT INTO employees (
                user_id, employee_code, department_id, position_id, 
                hire_date, status, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $employeeStmt = $this->db->prepare($employeeQuery);
            $employeeStmt->execute([
                $userId,
                $data['employeeCode'],
                $data['departmentId'],
                $data['positionId'],
                $data['hireDate'],
                'active'
            ]);
            $employeeId = $this->db->lastInsertId();

            // 3. Thêm thông tin hợp đồng
            $contractQuery = "INSERT INTO contracts (
                employee_id, contract_type, start_date, salary, 
                status, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
            
            $contractStmt = $this->db->prepare($contractQuery);
            $contractStmt->execute([
                $employeeId,
                $data['contractType'],
                $data['contractStartDate'],
                $data['baseSalary'],
                'active'
            ]);

            // 4. Thêm thông tin gia đình
            if (!empty($data['familyMembers'])) {
                $familyQuery = "INSERT INTO family_members (
                    employee_id, member_name, relationship, 
                    date_of_birth, occupation, is_dependent
                ) VALUES (?, ?, ?, ?, ?, ?)";
                
                $familyStmt = $this->db->prepare($familyQuery);
                foreach ($data['familyMembers'] as $member) {
                    $familyStmt->execute([
                        $employeeId,
                        $member['name'],
                        $member['relationship'],
                        $member['birthday'] ?: null,
                        $member['occupation'] ?: null,
                        $member['isDependent']
                    ]);
                }
            }

            // Commit transaction
            $this->db->commit();
            return [
                'success' => true,
                'message' => 'Thêm nhân viên thành công',
                'employeeId' => $employeeId
            ];

        } catch (Exception $e) {
            // Rollback transaction nếu có lỗi
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Lỗi khi thêm nhân viên: ' . $e->getMessage()
            ];
        }
    }

    public function getDepartments() {
        try {
            $query = "SELECT id, name FROM departments WHERE status = 'active' ORDER BY name";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function getPositions() {
        try {
            $query = "SELECT id, name FROM positions WHERE status = 'active' ORDER BY name";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function getContractTypes() {
        try {
            $query = "SELECT id, name, description FROM contract_types WHERE status = 'active' ORDER BY name";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function generateEmployeeCode() {
        try {
            // Lấy năm hiện tại
            $year = date('Y');
            
            // Lấy số thứ tự cuối cùng của năm
            $query = "SELECT MAX(CAST(SUBSTRING(employee_code, 8) AS UNSIGNED)) as last_number 
                     FROM employees 
                     WHERE employee_code LIKE :year_prefix";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['year_prefix' => "NV{$year}%"]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Tạo mã mới
            $nextNumber = ($result['last_number'] ?? 0) + 1;
            return "NV{$year}" . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        } catch (Exception $e) {
            return "NV" . date('Y') . "0001";
        }
    }
} 