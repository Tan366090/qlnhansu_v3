<?php
namespace App\Models;

class Position extends BaseModel {
    protected $table = 'positions';
    
    protected $fillable = [
        'name',
        'description',
        'department_id',
        'level',
        'min_salary',
        'max_salary',
        'requirements',
        'responsibilities',
        'status',
        'is_management',
        'reporting_to'
    ];
    
    public function getWithDetails($positionId = null) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT p.*,
                   d.name as department_name,
                   r.name as reporting_position_name,
                   COUNT(e.id) as employee_count
            FROM positions p
            JOIN departments d ON p.department_id = d.id
            LEFT JOIN positions r ON p.reporting_to = r.id
            LEFT JOIN employees e ON p.id = e.position_id
            WHERE 1=1
        ";
        
        if ($positionId) {
            $sql .= " AND p.id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$positionId]);
            return $stmt->fetch();
        }
        
        $sql .= " GROUP BY p.id ORDER BY p.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getByDepartment($departmentId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT p.*,
                   d.name as department_name,
                   r.name as reporting_position_name,
                   COUNT(e.id) as employee_count
            FROM positions p
            JOIN departments d ON p.department_id = d.id
            LEFT JOIN positions r ON p.reporting_to = r.id
            LEFT JOIN employees e ON p.id = e.position_id
            WHERE p.department_id = ?
            GROUP BY p.id
            ORDER BY p.created_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$departmentId]);
        return $stmt->fetchAll();
    }
    
    public function getPositionEmployees($positionId) {
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
                   m.full_name as manager_name
            FROM employees e
            JOIN users u ON e.user_id = u.id
            JOIN roles r ON u.role_id = r.id
            JOIN user_profiles up ON u.id = up.user_id
            JOIN departments d ON e.department_id = d.id
            LEFT JOIN user_profiles m ON e.manager_id = m.user_id
            WHERE e.position_id = ?
            ORDER BY e.created_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$positionId]);
        return $stmt->fetchAll();
    }
    
    public function getSubordinatePositions($positionId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT p.*,
                   d.name as department_name,
                   r.name as reporting_position_name,
                   COUNT(e.id) as employee_count
            FROM positions p
            JOIN departments d ON p.department_id = d.id
            LEFT JOIN positions r ON p.reporting_to = r.id
            LEFT JOIN employees e ON p.id = e.position_id
            WHERE p.reporting_to = ?
            GROUP BY p.id
            ORDER BY p.created_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$positionId]);
        return $stmt->fetchAll();
    }
    
    public function updatePositionStatus($positionId, $status) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE positions
            SET status = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([$status, $positionId]);
    }
    
    public function updatePositionSalary($positionId, $minSalary, $maxSalary) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE positions
            SET min_salary = ?,
                max_salary = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([$minSalary, $maxSalary, $positionId]);
    }
    
    public function updatePositionRequirements($positionId, $requirements) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE positions
            SET requirements = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([$requirements, $positionId]);
    }
    
    public function updatePositionResponsibilities($positionId, $responsibilities) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE positions
            SET responsibilities = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([$responsibilities, $positionId]);
    }
    
    public function getPositionHistory($positionId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT ph.*,
                   e.employee_code,
                   up.full_name,
                   d.name as department_name
            FROM position_history ph
            JOIN employees e ON ph.employee_id = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            JOIN departments d ON e.department_id = d.id
            WHERE ph.position_id = ?
            ORDER BY ph.start_date DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$positionId]);
        return $stmt->fetchAll();
    }
    
    public function searchPositions($query) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT p.*,
                   d.name as department_name,
                   r.name as reporting_position_name,
                   COUNT(e.id) as employee_count
            FROM positions p
            JOIN departments d ON p.department_id = d.id
            LEFT JOIN positions r ON p.reporting_to = r.id
            LEFT JOIN employees e ON p.id = e.position_id
            WHERE p.name LIKE ?
            OR p.description LIKE ?
            OR d.name LIKE ?
            GROUP BY p.id
            ORDER BY p.created_at DESC
        ";
        
        $searchTerm = "%{$query}%";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
} 