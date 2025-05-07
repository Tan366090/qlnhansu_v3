<?php
namespace App\Models;

class Employee extends BaseModel {
    protected $table = 'employees';
    
    protected $fillable = [
        'user_id',
        'employee_code',
        'department_id',
        'position_id',
        'employment_type',
        'join_date',
        'contract_start_date',
        'contract_end_date',
        'probation_end_date',
        'manager_id',
        'status',
        'notes'
    ];
    
    public function getWithDetails($employeeId = null) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT e.*,
                   u.username,
                   u.role_id,
                   r.name as role_name,
                   up.full_name,
                   up.phone_number,
                   up.email,
                   d.name as department_name,
                   p.name as position_name,
                   m.full_name as manager_name
            FROM employees e
            JOIN users u ON e.user_id = u.id
            JOIN roles r ON u.role_id = r.id
            JOIN user_profiles up ON u.id = up.user_id
            JOIN departments d ON e.department_id = d.id
            JOIN positions p ON e.position_id = p.id
            LEFT JOIN user_profiles m ON e.manager_id = m.user_id
            WHERE 1=1
        ";
        
        if ($employeeId) {
            $sql .= " AND e.id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$employeeId]);
            return $stmt->fetch();
        }
        
        $sql .= " ORDER BY e.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getByDepartment($departmentId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT e.*,
                   u.username,
                   u.role_id,
                   r.name as role_name,
                   up.full_name,
                   up.phone_number,
                   up.email,
                   d.name as department_name,
                   p.name as position_name,
                   m.full_name as manager_name
            FROM employees e
            JOIN users u ON e.user_id = u.id
            JOIN roles r ON u.role_id = r.id
            JOIN user_profiles up ON u.id = up.user_id
            JOIN departments d ON e.department_id = d.id
            JOIN positions p ON e.position_id = p.id
            LEFT JOIN user_profiles m ON e.manager_id = m.user_id
            WHERE e.department_id = ?
            ORDER BY e.created_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$departmentId]);
        return $stmt->fetchAll();
    }
    
    public function getByPosition($positionId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT e.*,
                   u.username,
                   u.role_id,
                   r.name as role_name,
                   up.full_name,
                   up.phone_number,
                   up.email,
                   d.name as department_name,
                   p.name as position_name,
                   m.full_name as manager_name
            FROM employees e
            JOIN users u ON e.user_id = u.id
            JOIN roles r ON u.role_id = r.id
            JOIN user_profiles up ON u.id = up.user_id
            JOIN departments d ON e.department_id = d.id
            JOIN positions p ON e.position_id = p.id
            LEFT JOIN user_profiles m ON e.manager_id = m.user_id
            WHERE e.position_id = ?
            ORDER BY e.created_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$positionId]);
        return $stmt->fetchAll();
    }
    
    public function getByManager($managerId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT e.*,
                   u.username,
                   u.role_id,
                   r.name as role_name,
                   up.full_name,
                   up.phone_number,
                   up.email,
                   d.name as department_name,
                   p.name as position_name,
                   m.full_name as manager_name
            FROM employees e
            JOIN users u ON e.user_id = u.id
            JOIN roles r ON u.role_id = r.id
            JOIN user_profiles up ON u.id = up.user_id
            JOIN departments d ON e.department_id = d.id
            JOIN positions p ON e.position_id = p.id
            LEFT JOIN user_profiles m ON e.manager_id = m.user_id
            WHERE e.manager_id = ?
            ORDER BY e.created_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$managerId]);
        return $stmt->fetchAll();
    }
    
    public function getActiveEmployees() {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT e.*,
                   u.username,
                   u.role_id,
                   r.name as role_name,
                   up.full_name,
                   up.phone_number,
                   up.email,
                   d.name as department_name,
                   p.name as position_name,
                   m.full_name as manager_name
            FROM employees e
            JOIN users u ON e.user_id = u.id
            JOIN roles r ON u.role_id = r.id
            JOIN user_profiles up ON u.id = up.user_id
            JOIN departments d ON e.department_id = d.id
            JOIN positions p ON e.position_id = p.id
            LEFT JOIN user_profiles m ON e.manager_id = m.user_id
            WHERE e.status = 'active'
            ORDER BY e.created_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function updateEmployeeStatus($employeeId, $status) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE employees
            SET status = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([$status, $employeeId]);
    }
    
    public function updateEmployeeDepartment($employeeId, $departmentId) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE employees
            SET department_id = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([$departmentId, $employeeId]);
    }
    
    public function updateEmployeePosition($employeeId, $positionId) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE employees
            SET position_id = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([$positionId, $employeeId]);
    }
    
    public function updateEmployeeManager($employeeId, $managerId) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE employees
            SET manager_id = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([$managerId, $employeeId]);
    }
    
    public function searchEmployees($query) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT e.*,
                   u.username,
                   u.role_id,
                   r.name as role_name,
                   up.full_name,
                   up.phone_number,
                   up.email,
                   d.name as department_name,
                   p.name as position_name,
                   m.full_name as manager_name
            FROM employees e
            JOIN users u ON e.user_id = u.id
            JOIN roles r ON u.role_id = r.id
            JOIN user_profiles up ON u.id = up.user_id
            JOIN departments d ON e.department_id = d.id
            JOIN positions p ON e.position_id = p.id
            LEFT JOIN user_profiles m ON e.manager_id = m.user_id
            WHERE e.employee_code LIKE ?
            OR up.full_name LIKE ?
            OR up.phone_number LIKE ?
            OR up.email LIKE ?
            ORDER BY e.created_at DESC
        ";
        
        $searchTerm = "%{$query}%";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
} 