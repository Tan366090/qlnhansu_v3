<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

class EmployeesAPI {
    private $conn;
    private $table_name = "employees";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy danh sách nhân viên
    public function getEmployees($params = []) {
        try {
            $query = "SELECT e.*, u.username, u.email, d.name as department_name, p.name as position_name 
                     FROM " . $this->table_name . " e
                     LEFT JOIN users u ON e.user_id = u.user_id
                     LEFT JOIN departments d ON e.department_id = d.id
                     LEFT JOIN positions p ON e.position_id = p.id
                     WHERE 1=1";
            
            // Thêm điều kiện tìm kiếm
            if (!empty($params['search'])) {
                $search = $params['search'];
                $query .= " AND (u.username LIKE '%$search%' OR u.email LIKE '%$search%')";
            }

            // Thêm điều kiện phòng ban
            if (!empty($params['department_id'])) {
                $department_id = $params['department_id'];
                $query .= " AND e.department_id = $department_id";
            }

            // Thêm điều kiện vị trí
            if (!empty($params['position_id'])) {
                $position_id = $params['position_id'];
                $query .= " AND e.position_id = $position_id";
            }

            // Thêm điều kiện trạng thái
            if (!empty($params['status'])) {
                $status = $params['status'];
                $query .= " AND e.status = '$status'";
            }

            // Thêm phân trang
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            $query .= " ORDER BY e.created_at DESC LIMIT $limit OFFSET $offset";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'status' => 'success',
                'data' => $employees,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $this->getTotalEmployees($params)
                ]
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Lấy thông tin chi tiết nhân viên
    public function getEmployee($id) {
        try {
            $query = "SELECT e.*, u.username, u.email, d.name as department_name, p.name as position_name 
                     FROM " . $this->table_name . " e
                     LEFT JOIN users u ON e.user_id = u.user_id
                     LEFT JOIN departments d ON e.department_id = d.id
                     LEFT JOIN positions p ON e.position_id = p.id
                     WHERE e.id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $employee = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($employee) {
                // Lấy thông tin hồ sơ người dùng
                $profile = $this->getUserProfile($employee['user_id']);
                $employee['profile'] = $profile;

                // Lấy thông tin hợp đồng
                $contracts = $this->getEmployeeContracts($id);
                $employee['contracts'] = $contracts;

                // Lấy thông tin gia đình
                $family = $this->getEmployeeFamily($id);
                $employee['family'] = $family;

                // Lấy chứng chỉ
                $certificates = $this->getEmployeeCertificates($id);
                $employee['certificates'] = $certificates;

                // Lấy bằng cấp
                $degrees = $this->getEmployeeDegrees($id);
                $employee['degrees'] = $degrees;

                return [
                    'status' => 'success',
                    'data' => $employee
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Employee not found'
                ];
            }
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Thêm nhân viên mới
    public function createEmployee($data) {
        try {
            // Bắt đầu transaction
            $this->conn->beginTransaction();

            // 1. Tạo user account
            $userQuery = "INSERT INTO users (username, email, password_hash, role_id, is_active)
                         VALUES (:username, :email, :password_hash, :role_id, 1)";
            
            $stmt = $this->conn->prepare($userQuery);
            $stmt->execute([
                'username' => $data['username'],
                'email' => $data['email'],
                'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
                'role_id' => $data['role_id']
            ]);
            
            $user_id = $this->conn->lastInsertId();

            // 2. Tạo employee record
            $employeeQuery = "INSERT INTO " . $this->table_name . "
                            (user_id, employee_code, department_id, position_id, hire_date, 
                             termination_date, status)
                            VALUES
                            (:user_id, :employee_code, :department_id, :position_id, :hire_date,
                             :termination_date, :status)";

            $stmt = $this->conn->prepare($employeeQuery);
            $stmt->execute([
                'user_id' => $user_id,
                'employee_code' => $data['employee_code'],
                'department_id' => $data['department_id'],
                'position_id' => $data['position_id'],
                'hire_date' => $data['hire_date'],
                'termination_date' => $data['termination_date'] ?? null,
                'status' => $data['status'] ?? 'active'
            ]);

            $employee_id = $this->conn->lastInsertId();

            // 3. Tạo user profile
            $profileQuery = "INSERT INTO user_profiles 
                           (user_id, full_name, date_of_birth, gender, phone_number, 
                            permanent_address, current_address, emergency_contact_name,
                            emergency_contact_phone, bank_account_number, bank_name,
                            tax_code, nationality, ethnicity, religion, marital_status,
                            id_card_number, id_card_issue_date, id_card_issue_place)
                           VALUES
                           (:user_id, :full_name, :date_of_birth, :gender, :phone_number,
                            :permanent_address, :current_address, :emergency_contact_name,
                            :emergency_contact_phone, :bank_account_number, :bank_name,
                            :tax_code, :nationality, :ethnicity, :religion, :marital_status,
                            :id_card_number, :id_card_issue_date, :id_card_issue_place)";

            $stmt = $this->conn->prepare($profileQuery);
            $stmt->execute([
                'user_id' => $user_id,
                'full_name' => $data['full_name'],
                'date_of_birth' => $data['date_of_birth'],
                'gender' => $data['gender'],
                'phone_number' => $data['phone_number'],
                'permanent_address' => $data['permanent_address'],
                'current_address' => $data['current_address'],
                'emergency_contact_name' => $data['emergency_contact_name'],
                'emergency_contact_phone' => $data['emergency_contact_phone'],
                'bank_account_number' => $data['bank_account_number'],
                'bank_name' => $data['bank_name'],
                'tax_code' => $data['tax_code'],
                'nationality' => $data['nationality'],
                'ethnicity' => $data['ethnicity'],
                'religion' => $data['religion'],
                'marital_status' => $data['marital_status'],
                'id_card_number' => $data['id_card_number'],
                'id_card_issue_date' => $data['id_card_issue_date'],
                'id_card_issue_place' => $data['id_card_issue_place']
            ]);

            // Commit transaction
            $this->conn->commit();

            return [
                'status' => 'success',
                'message' => 'Employee created successfully',
                'employee_id' => $employee_id,
                'user_id' => $user_id
            ];
        } catch(PDOException $e) {
            // Rollback transaction nếu có lỗi
            $this->conn->rollBack();
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Cập nhật thông tin nhân viên
    public function updateEmployee($id, $data) {
        try {
            // Bắt đầu transaction
            $this->conn->beginTransaction();

            // 1. Cập nhật employee record
            $employeeQuery = "UPDATE " . $this->table_name . "
                            SET department_id = :department_id,
                                position_id = :position_id,
                                hire_date = :hire_date,
                                termination_date = :termination_date,
                                status = :status,
                                updated_at = CURRENT_TIMESTAMP
                            WHERE id = :id";

            $stmt = $this->conn->prepare($employeeQuery);
            $stmt->execute([
                'id' => $id,
                'department_id' => $data['department_id'],
                'position_id' => $data['position_id'],
                'hire_date' => $data['hire_date'],
                'termination_date' => $data['termination_date'] ?? null,
                'status' => $data['status']
            ]);

            // 2. Cập nhật user profile
            $profileQuery = "UPDATE user_profiles 
                           SET full_name = :full_name,
                               date_of_birth = :date_of_birth,
                               gender = :gender,
                               phone_number = :phone_number,
                               permanent_address = :permanent_address,
                               current_address = :current_address,
                               emergency_contact_name = :emergency_contact_name,
                               emergency_contact_phone = :emergency_contact_phone,
                               bank_account_number = :bank_account_number,
                               bank_name = :bank_name,
                               tax_code = :tax_code,
                               nationality = :nationality,
                               ethnicity = :ethnicity,
                               religion = :religion,
                               marital_status = :marital_status,
                               id_card_number = :id_card_number,
                               id_card_issue_date = :id_card_issue_date,
                               id_card_issue_place = :id_card_issue_place,
                               updated_at = CURRENT_TIMESTAMP
                           WHERE user_id = (SELECT user_id FROM employees WHERE id = :id)";

            $stmt = $this->conn->prepare($profileQuery);
            $stmt->execute([
                'id' => $id,
                'full_name' => $data['full_name'],
                'date_of_birth' => $data['date_of_birth'],
                'gender' => $data['gender'],
                'phone_number' => $data['phone_number'],
                'permanent_address' => $data['permanent_address'],
                'current_address' => $data['current_address'],
                'emergency_contact_name' => $data['emergency_contact_name'],
                'emergency_contact_phone' => $data['emergency_contact_phone'],
                'bank_account_number' => $data['bank_account_number'],
                'bank_name' => $data['bank_name'],
                'tax_code' => $data['tax_code'],
                'nationality' => $data['nationality'],
                'ethnicity' => $data['ethnicity'],
                'religion' => $data['religion'],
                'marital_status' => $data['marital_status'],
                'id_card_number' => $data['id_card_number'],
                'id_card_issue_date' => $data['id_card_issue_date'],
                'id_card_issue_place' => $data['id_card_issue_place']
            ]);

            // Commit transaction
            $this->conn->commit();

            return [
                'status' => 'success',
                'message' => 'Employee updated successfully'
            ];
        } catch(PDOException $e) {
            // Rollback transaction nếu có lỗi
            $this->conn->rollBack();
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Xóa nhân viên
    public function deleteEmployee($id) {
        try {
            // Bắt đầu transaction
            $this->conn->beginTransaction();

            // 1. Lấy user_id của nhân viên
            $query = "SELECT user_id FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                throw new Exception('Employee not found');
            }

            $user_id = $result['user_id'];

            // 2. Xóa các bản ghi liên quan
            $this->deleteRelatedRecords($id);

            // 3. Xóa employee record
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            // 4. Xóa user profile
            $query = "DELETE FROM user_profiles WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            // 5. Xóa user account
            $query = "DELETE FROM users WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            // Commit transaction
            $this->conn->commit();

            return [
                'status' => 'success',
                'message' => 'Employee deleted successfully'
            ];
        } catch(Exception $e) {
            // Rollback transaction nếu có lỗi
            $this->conn->rollBack();
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Các hàm helper
    private function getUserProfile($user_id) {
        $query = "SELECT * FROM user_profiles WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getEmployeeContracts($employee_id) {
        $query = "SELECT * FROM contracts WHERE employee_id = :employee_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':employee_id', $employee_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getEmployeeFamily($employee_id) {
        $query = "SELECT * FROM family_members WHERE employee_id = :employee_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':employee_id', $employee_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getEmployeeCertificates($employee_id) {
        $query = "SELECT * FROM certificates WHERE employee_id = :employee_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':employee_id', $employee_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getEmployeeDegrees($employee_id) {
        $query = "SELECT * FROM degrees WHERE employee_id = :employee_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':employee_id', $employee_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getTotalEmployees($params) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " e WHERE 1=1";
        
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query .= " AND EXISTS (SELECT 1 FROM users u WHERE e.user_id = u.user_id 
                      AND (u.username LIKE '%$search%' OR u.email LIKE '%$search%'))";
        }

        if (!empty($params['department_id'])) {
            $department_id = $params['department_id'];
            $query .= " AND e.department_id = $department_id";
        }

        if (!empty($params['position_id'])) {
            $position_id = $params['position_id'];
            $query .= " AND e.position_id = $position_id";
        }

        if (!empty($params['status'])) {
            $status = $params['status'];
            $query .= " AND e.status = '$status'";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    private function deleteRelatedRecords($employee_id) {
        // Xóa các bản ghi liên quan
        $tables = [
            'contracts',
            'family_members',
            'certificates',
            'degrees',
            'attendance',
            'leaves',
            'work_schedules',
            'salary_history',
            'performances',
            'kpi',
            'training_registrations',
            'asset_assignments',
            'project_resources'
        ];

        foreach ($tables as $table) {
            $query = "DELETE FROM $table WHERE employee_id = :employee_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':employee_id', $employee_id);
            $stmt->execute();
        }
    }
}

// Xử lý request
$database = new Database();
$db = $database->getConnection();
$api = new EmployeesAPI($db);

$method = $_SERVER['REQUEST_METHOD'];
$response = [];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            $response = $api->getEmployee($_GET['id']);
        } else {
            $response = $api->getEmployees($_GET);
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $response = $api->createEmployee($data);
        break;
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $_GET['id'];
        $response = $api->updateEmployee($id, $data);
        break;
    case 'DELETE':
        $id = $_GET['id'];
        $response = $api->deleteEmployee($id);
        break;
    default:
        $response = [
            'status' => 'error',
            'message' => 'Method not allowed'
        ];
        break;
}

echo json_encode($response); 