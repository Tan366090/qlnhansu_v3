<?php
namespace App\Models;

class Performance extends BaseModel {
    protected $table = 'performance_reviews';
    
    protected $fillable = [
        'employee_id',
        'review_period_start',
        'review_period_end',
        'reviewer_id',
        'performance_rating',
        'competency_rating',
        'achievements',
        'improvement_areas',
        'goals',
        'status',
        'review_date',
        'next_review_date'
    ];
    
    public function getWithDetails($id = null) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT pr.*, 
                   e.employee_code,
                   up.full_name as employee_name,
                   d.name as department_name,
                   p.name as position_name,
                   up2.full_name as reviewer_name
            FROM performance_reviews pr
            JOIN employees e ON pr.employee_id = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            JOIN departments d ON e.department_id = d.id
            JOIN positions p ON e.position_id = p.id
            LEFT JOIN users u ON pr.reviewer_id = u.id
            LEFT JOIN user_profiles up2 ON u.id = up2.user_id
            WHERE 1=1
        ";
        
        if ($id) {
            $sql .= " AND pr.id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        }
        
        $sql .= " ORDER BY pr.review_date DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getByEmployee($employeeId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT pr.*, 
                   e.employee_code,
                   up.full_name as employee_name,
                   d.name as department_name,
                   p.name as position_name,
                   up2.full_name as reviewer_name
            FROM performance_reviews pr
            JOIN employees e ON pr.employee_id = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            JOIN departments d ON e.department_id = d.id
            JOIN positions p ON e.position_id = p.id
            LEFT JOIN users u ON pr.reviewer_id = u.id
            LEFT JOIN user_profiles up2 ON u.id = up2.user_id
            WHERE pr.employee_id = ?
            ORDER BY pr.review_date DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$employeeId]);
        return $stmt->fetchAll();
    }
    
    public function getByDepartment($departmentId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT pr.*, 
                   e.employee_code,
                   up.full_name as employee_name,
                   d.name as department_name,
                   p.name as position_name,
                   up2.full_name as reviewer_name
            FROM performance_reviews pr
            JOIN employees e ON pr.employee_id = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            JOIN departments d ON e.department_id = d.id
            JOIN positions p ON e.position_id = p.id
            LEFT JOIN users u ON pr.reviewer_id = u.id
            LEFT JOIN user_profiles up2 ON u.id = up2.user_id
            WHERE e.department_id = ?
            ORDER BY pr.review_date DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$departmentId]);
        return $stmt->fetchAll();
    }
    
    public function getPendingReviews() {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT pr.*, 
                   e.employee_code,
                   up.full_name as employee_name,
                   d.name as department_name,
                   p.name as position_name,
                   up2.full_name as reviewer_name
            FROM performance_reviews pr
            JOIN employees e ON pr.employee_id = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            JOIN departments d ON e.department_id = d.id
            JOIN positions p ON e.position_id = p.id
            LEFT JOIN users u ON pr.reviewer_id = u.id
            LEFT JOIN user_profiles up2 ON u.id = up2.user_id
            WHERE pr.status = 'pending'
            ORDER BY pr.review_date DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getOverdueReviews() {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT pr.*, 
                   e.employee_code,
                   up.full_name as employee_name,
                   d.name as department_name,
                   p.name as position_name,
                   up2.full_name as reviewer_name
            FROM performance_reviews pr
            JOIN employees e ON pr.employee_id = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            JOIN departments d ON e.department_id = d.id
            JOIN positions p ON e.position_id = p.id
            LEFT JOIN users u ON pr.reviewer_id = u.id
            LEFT JOIN user_profiles up2 ON u.id = up2.user_id
            WHERE pr.status = 'pending'
            AND pr.review_date < NOW()
            ORDER BY pr.review_date DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getUpcomingReviews() {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT pr.*, 
                   e.employee_code,
                   up.full_name as employee_name,
                   d.name as department_name,
                   p.name as position_name,
                   up2.full_name as reviewer_name
            FROM performance_reviews pr
            JOIN employees e ON pr.employee_id = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            JOIN departments d ON e.department_id = d.id
            JOIN positions p ON e.position_id = p.id
            LEFT JOIN users u ON pr.reviewer_id = u.id
            LEFT JOIN user_profiles up2 ON u.id = up2.user_id
            WHERE pr.status = 'pending'
            AND pr.review_date >= NOW()
            ORDER BY pr.review_date ASC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getPerformanceHistory($employeeId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT pr.*, 
                   e.employee_code,
                   up.full_name as employee_name,
                   d.name as department_name,
                   p.name as position_name,
                   up2.full_name as reviewer_name
            FROM performance_reviews pr
            JOIN employees e ON pr.employee_id = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            JOIN departments d ON e.department_id = d.id
            JOIN positions p ON e.position_id = p.id
            LEFT JOIN users u ON pr.reviewer_id = u.id
            LEFT JOIN user_profiles up2 ON u.id = up2.user_id
            WHERE pr.employee_id = ?
            AND pr.status = 'completed'
            ORDER BY pr.review_date DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$employeeId]);
        return $stmt->fetchAll();
    }
    
    public function getDepartmentPerformance($departmentId, $startDate, $endDate) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT pr.*, 
                   e.employee_code,
                   up.full_name as employee_name,
                   d.name as department_name,
                   p.name as position_name,
                   up2.full_name as reviewer_name
            FROM performance_reviews pr
            JOIN employees e ON pr.employee_id = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            JOIN departments d ON e.department_id = d.id
            JOIN positions p ON e.position_id = p.id
            LEFT JOIN users u ON pr.reviewer_id = u.id
            LEFT JOIN user_profiles up2 ON u.id = up2.user_id
            WHERE e.department_id = ?
            AND pr.review_date BETWEEN ? AND ?
            AND pr.status = 'completed'
            ORDER BY pr.review_date DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$departmentId, $startDate, $endDate]);
        return $stmt->fetchAll();
    }
    
    public function getPerformanceMetrics($departmentId = null, $startDate = null, $endDate = null) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT 
                AVG(pr.performance_rating) as avg_performance_rating,
                AVG(pr.competency_rating) as avg_competency_rating,
                COUNT(pr.id) as total_reviews,
                COUNT(CASE WHEN pr.performance_rating >= 4 THEN 1 END) as high_performers,
                COUNT(CASE WHEN pr.performance_rating <= 2 THEN 1 END) as low_performers
            FROM performance_reviews pr
            JOIN employees e ON pr.employee_id = e.id
            WHERE pr.status = 'completed'
        ";
        
        $params = [];
        
        if ($departmentId) {
            $sql .= " AND e.department_id = ?";
            $params[] = $departmentId;
        }
        
        if ($startDate) {
            $sql .= " AND pr.review_date >= ?";
            $params[] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND pr.review_date <= ?";
            $params[] = $endDate;
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
} 