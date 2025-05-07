<?php
namespace App\Models;

class Department extends BaseModel {
    protected $table = 'departments';
    
    protected $fillable = [
        'name',
        'description',
        'manager_id',
        'parent_id',
        'status',
        'budget',
        'location',
        'contact_phone',
        'contact_email'
    ];
    
    public function getWithDetails($departmentId = null) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT d.*,
                   m.full_name as manager_name,
                   p.name as parent_name,
                   COUNT(e.id) as employee_count
            FROM departments d
            LEFT JOIN user_profiles m ON d.manager_id = m.user_id
            LEFT JOIN departments p ON d.parent_id = p.id
            LEFT JOIN employees e ON d.id = e.department_id
            WHERE 1=1
        ";
        
        if ($departmentId) {
            $sql .= " AND d.id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$departmentId]);
            return $stmt->fetch();
        }
        
        $sql .= " GROUP BY d.id ORDER BY d.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getSubDepartments($departmentId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT d.*,
                   m.full_name as manager_name,
                   p.name as parent_name,
                   COUNT(e.id) as employee_count
            FROM departments d
            LEFT JOIN user_profiles m ON d.manager_id = m.user_id
            LEFT JOIN departments p ON d.parent_id = p.id
            LEFT JOIN employees e ON d.id = e.department_id
            WHERE d.parent_id = ?
            GROUP BY d.id
            ORDER BY d.created_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$departmentId]);
        return $stmt->fetchAll();
    }
    
    public function getDepartmentEmployees($departmentId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT e.*,
                   u.username,
                   u.role_id,
                   r.name as role_name,
                   up.full_name,
                   up.phone_number,
                   up.email,
                   p.name as position_name,
                   m.full_name as manager_name
            FROM employees e
            JOIN users u ON e.user_id = u.id
            JOIN roles r ON u.role_id = r.id
            JOIN user_profiles up ON u.id = up.user_id
            JOIN positions p ON e.position_id = p.id
            LEFT JOIN user_profiles m ON e.manager_id = m.user_id
            WHERE e.department_id = ?
            ORDER BY e.created_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$departmentId]);
        return $stmt->fetchAll();
    }
    
    public function getDepartmentPositions($departmentId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT p.*,
                   COUNT(e.id) as employee_count
            FROM positions p
            LEFT JOIN employees e ON p.id = e.position_id AND e.department_id = ?
            GROUP BY p.id
            ORDER BY p.created_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$departmentId]);
        return $stmt->fetchAll();
    }
    
    public function updateDepartmentManager($departmentId, $managerId) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE departments
            SET manager_id = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([$managerId, $departmentId]);
    }
    
    public function updateDepartmentStatus($departmentId, $status) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE departments
            SET status = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([$status, $departmentId]);
    }
    
    public function updateDepartmentBudget($departmentId, $budget) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE departments
            SET budget = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([$budget, $departmentId]);
    }
    
    public function getDepartmentProjects($departmentId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT p.*,
                   m.full_name as manager_name,
                   COUNT(t.id) as task_count,
                   COUNT(DISTINCT pm.employee_id) as member_count
            FROM projects p
            LEFT JOIN user_profiles m ON p.manager_id = m.user_id
            LEFT JOIN tasks t ON p.id = t.project_id
            LEFT JOIN project_members pm ON p.id = pm.project_id
            WHERE p.department_id = ?
            GROUP BY p.id
            ORDER BY p.created_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$departmentId]);
        return $stmt->fetchAll();
    }
    
    public function searchDepartments($query) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT d.*,
                   m.full_name as manager_name,
                   p.name as parent_name,
                   COUNT(e.id) as employee_count
            FROM departments d
            LEFT JOIN user_profiles m ON d.manager_id = m.user_id
            LEFT JOIN departments p ON d.parent_id = p.id
            LEFT JOIN employees e ON d.id = e.department_id
            WHERE d.name LIKE ?
            OR d.description LIKE ?
            OR d.location LIKE ?
            GROUP BY d.id
            ORDER BY d.created_at DESC
        ";
        
        $searchTerm = "%{$query}%";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
} 