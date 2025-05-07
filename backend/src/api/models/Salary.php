<?php
namespace App\Models;

class Salary extends BaseModel {
    protected $table = 'salary_history';
    
    protected $fillable = [
        'employee_id',
        'effective_date',
        'previous_salary',
        'new_salary',
        'salary_currency',
        'reason',
        'decision_attachment_url',
        'recorded_by_user_id'
    ];
    
    public function getWithDetails($id = null) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT s.*, 
                   e.employee_code,
                   up.full_name,
                   d.name as department_name,
                   p.name as position_name,
                   up2.full_name as recorded_by_name
            FROM salary_history s
            JOIN employees e ON s.employee_id = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            JOIN departments d ON e.department_id = d.id
            JOIN positions p ON e.position_id = p.id
            LEFT JOIN users u ON s.recorded_by_user_id = u.id
            LEFT JOIN user_profiles up2 ON u.id = up2.user_id
            WHERE 1=1
        ";
        
        if ($id) {
            $sql .= " AND s.salary_history_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        }
        
        $sql .= " ORDER BY s.effective_date DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getByEmployee($employeeId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT s.*, 
                   e.employee_code,
                   up.full_name,
                   d.name as department_name,
                   p.name as position_name,
                   up2.full_name as recorded_by_name
            FROM salary_history s
            JOIN employees e ON s.employee_id = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            JOIN departments d ON e.department_id = d.id
            JOIN positions p ON e.position_id = p.id
            LEFT JOIN users u ON s.recorded_by_user_id = u.id
            LEFT JOIN user_profiles up2 ON u.id = up2.user_id
            WHERE s.employee_id = ?
            ORDER BY s.effective_date DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$employeeId]);
        return $stmt->fetchAll();
    }
    
    public function getCurrentSalary($employeeId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT s.*, 
                   e.employee_code,
                   up.full_name,
                   d.name as department_name,
                   p.name as position_name,
                   up2.full_name as recorded_by_name
            FROM salary_history s
            JOIN employees e ON s.employee_id = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            JOIN departments d ON e.department_id = d.id
            JOIN positions p ON e.position_id = p.id
            LEFT JOIN users u ON s.recorded_by_user_id = u.id
            LEFT JOIN user_profiles up2 ON u.id = up2.user_id
            WHERE s.employee_id = ?
            ORDER BY s.effective_date DESC
            LIMIT 1
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$employeeId]);
        return $stmt->fetch();
    }
    
    public function getDepartmentSalaries($departmentId, $date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT s.*, 
                   e.employee_code,
                   up.full_name,
                   d.name as department_name,
                   p.name as position_name,
                   up2.full_name as recorded_by_name
            FROM salary_history s
            JOIN employees e ON s.employee_id = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            JOIN departments d ON e.department_id = d.id
            JOIN positions p ON e.position_id = p.id
            LEFT JOIN users u ON s.recorded_by_user_id = u.id
            LEFT JOIN user_profiles up2 ON u.id = up2.user_id
            WHERE e.department_id = ?
            AND s.effective_date <= ?
            AND s.salary_history_id IN (
                SELECT MAX(s2.salary_history_id)
                FROM salary_history s2
                WHERE s2.employee_id = s.employee_id
                AND s2.effective_date <= ?
                GROUP BY s2.employee_id
            )
            ORDER BY up.full_name
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$departmentId, $date, $date]);
        return $stmt->fetchAll();
    }
    
    public function getSalaryChanges($startDate, $endDate) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT s.*, 
                   e.employee_code,
                   up.full_name,
                   d.name as department_name,
                   p.name as position_name,
                   up2.full_name as recorded_by_name
            FROM salary_history s
            JOIN employees e ON s.employee_id = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            JOIN departments d ON e.department_id = d.id
            JOIN positions p ON e.position_id = p.id
            LEFT JOIN users u ON s.recorded_by_user_id = u.id
            LEFT JOIN user_profiles up2 ON u.id = up2.user_id
            WHERE s.effective_date BETWEEN ? AND ?
            ORDER BY s.effective_date DESC, up.full_name
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }
    
    public function calculateSalaryIncrease($employeeId, $newSalary) {
        $currentSalary = $this->getCurrentSalary($employeeId);
        if (!$currentSalary) {
            return 0;
        }
        
        $increase = ($newSalary - $currentSalary['new_salary']) / $currentSalary['new_salary'] * 100;
        return round($increase, 2);
    }
} 