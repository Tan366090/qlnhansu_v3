<?php
require_once __DIR__ . '/../config/database.php';

class Employee {
    private $conn;
    private $table_name = "employees";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll($page = 1, $limit = 10, $search = '', $department = '', $status = '') {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT 
                    e.id,
                    e.employee_code,
                    e.full_name,
                    d.name as department_name,
                    p.name as position_name,
                    e.salary,
                    e.join_date,
                    e.birth_date,
                    e.phone,
                    e.email,
                    e.address,
                    e.status,
                    e.created_at
                FROM " . $this->table_name . " e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions p ON e.position_id = p.id
                WHERE 1=1";
        
        $params = [];
        
        if($search) {
            $query .= " AND (e.full_name LIKE ? OR e.employee_code LIKE ? OR e.email LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if($department) {
            $query .= " AND d.name = ?";
            $params[] = $department;
        }
        
        if($status) {
            $query .= " AND e.status = ?";
            $params[] = $status;
        }
        
        $query .= " ORDER BY e.id DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Lấy tổng số bản ghi
        $countQuery = "SELECT COUNT(*) as total FROM " . $this->table_name . " e
                      LEFT JOIN departments d ON e.department_id = d.id
                      WHERE 1=1";
        
        if($search) {
            $countQuery .= " AND (e.full_name LIKE ? OR e.employee_code LIKE ? OR e.email LIKE ?)";
        }
        
        if($department) {
            $countQuery .= " AND d.name = ?";
        }
        
        if($status) {
            $countQuery .= " AND e.status = ?";
        }
        
        $countStmt = $this->conn->prepare($countQuery);
        $countStmt->execute($params);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        return [
            'data' => $employees,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }

    public function getById($id) {
        $query = "SELECT 
                    e.*,
                    d.name as department_name,
                    p.name as position_name
                FROM " . $this->table_name . " e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions p ON e.position_id = p.id
                WHERE e.id = ?";
                
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $conn = $this->conn;
        $conn->beginTransaction();
        
        try {
            // Generate employee code
            $currentYear = date('y');
            $currentMonth = date('m');
            
            // Get the last employee code for this month
            $lastCodeQuery = "SELECT employee_code FROM employees 
                            WHERE employee_code LIKE 'NV{$currentYear}{$currentMonth}%' 
                            ORDER BY employee_code DESC LIMIT 1";
            $lastCodeStmt = $conn->query($lastCodeQuery);
            $lastCode = $lastCodeStmt->fetchColumn();
            
            if ($lastCode) {
                $lastNumber = intval(substr($lastCode, -3));
                $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '001';
            }
            
            $employeeCode = "NV{$currentYear}{$currentMonth}{$newNumber}";
            
            // 1. Create user record
            $userQuery = "INSERT INTO users (username, email, password_hash, role_id, is_active) 
                         VALUES (?, ?, ?, ?, 1)";
            $username = strtolower(str_replace(' ', '', $data['full_name']));
            $password = password_hash('default123', PASSWORD_DEFAULT);
            $roleId = 4; // Default employee role
            
            $userStmt = $conn->prepare($userQuery);
            $userStmt->execute([$username, $data['email'], $password, $roleId]);
            $userId = $conn->lastInsertId();
            
            // 2. Create user profile record
            $profileQuery = "INSERT INTO user_profiles (user_id, full_name, phone_number) 
                           VALUES (?, ?, ?)";
            $profileStmt = $conn->prepare($profileQuery);
            $profileStmt->execute([$userId, $data['full_name'], $data['phone_number']]);
            
            // 3. Create employee record
            $employeeQuery = "INSERT INTO employees (user_id, employee_code, department_id, position_id, 
                           hire_date, contract_type, contract_start_date, status) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, 'active')";
            $employeeStmt = $conn->prepare($employeeQuery);
            $employeeStmt->execute([
                $userId,
                $employeeCode,
                $data['department_id'],
                $data['position_id'],
                $data['hire_date'],
                $data['contract_type'],
                $data['contract_start_date']
            ]);
            
            $employeeId = $conn->lastInsertId();
            
            // 4. Get the complete employee information
            $getEmployeeQuery = "SELECT e.*, u.email, up.full_name, up.phone_number as phone,
                               d.name as department_name, p.name as position_name
                               FROM employees e
                               JOIN users u ON e.user_id = u.user_id
                               JOIN user_profiles up ON e.user_id = up.user_id
                               LEFT JOIN departments d ON e.department_id = d.id
                               LEFT JOIN positions p ON e.position_id = p.id
                               WHERE e.id = ?";
            
            $getEmployeeStmt = $conn->prepare($getEmployeeQuery);
            $getEmployeeStmt->execute([$employeeId]);
            $employee = $getEmployeeStmt->fetch(PDO::FETCH_ASSOC);
            
            $conn->commit();
            
            return [
                'success' => true,
                'data' => $employee
            ];
        } catch(PDOException $e) {
            $conn->rollBack();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . "
                SET full_name = ?,
                    department_id = ?,
                    position_id = ?,
                    salary = ?,
                    join_date = ?,
                    birth_date = ?,
                    phone = ?,
                    email = ?,
                    address = ?,
                    status = ?
                WHERE id = ?";
                
        $stmt = $this->conn->prepare($query);
        
        try {
            $stmt->execute([
                $data['full_name'],
                $data['department_id'],
                $data['position_id'],
                $data['salary'],
                $data['join_date'],
                $data['birth_date'],
                $data['phone'],
                $data['email'],
                $data['address'],
                $data['status'],
                $id
            ]);
            
            return ['success' => true];
        } catch(PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        
        try {
            $stmt->execute([$id]);
            return ['success' => true];
        } catch(PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function validate($data) {
        $errors = [];

        // Validate required fields
        $requiredFields = ['full_name', 'gender', 'birth_date', 'phone', 'email', 'address', 'department_id', 'position_id'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $errors[] = "Trường $field là bắt buộc";
            }
        }

        // Validate email format
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email không hợp lệ";
        }

        // Validate phone format (Vietnamese phone number)
        if (!empty($data['phone']) && !preg_match('/^(0|\+84)[0-9]{9,10}$/', $data['phone'])) {
            $errors[] = "Số điện thoại không hợp lệ";
        }

        // Validate birth date
        if (!empty($data['birth_date'])) {
            $birthDate = new DateTime($data['birth_date']);
            $now = new DateTime();
            $age = $now->diff($birthDate)->y;
            
            if ($age < 18) {
                $errors[] = "Nhân viên phải đủ 18 tuổi";
            }
        }

        return $errors;
    }
} 