<?php
class AdvancedQueries {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // Phân tích xu hướng nhân sự
    public function analyzeHRTrends($timeframe = 'month') {
        $trends = [];
        
        // Phân tích xu hướng tuyển dụng
        $trends['recruitment'] = $this->analyzeRecruitmentTrends($timeframe);
        
        // Phân tích xu hướng nghỉ việc
        $trends['turnover'] = $this->analyzeTurnoverTrends($timeframe);
        
        // Phân tích xu hướng đánh giá
        $trends['performance'] = $this->analyzePerformanceTrends($timeframe);
        
        // Phân tích xu hướng lương
        $trends['salary'] = $this->analyzeSalaryTrends($timeframe);
        
        return $trends;
    }
    
    // Phân tích hiệu suất phòng ban
    public function analyzeDepartmentPerformance() {
        $performance = [];
        
        // Lấy danh sách phòng ban
        $departments = $this->getDepartments();
        
        foreach ($departments as $dept) {
            $performance[$dept['id']] = [
                'name' => $dept['name'],
                'employee_count' => $this->getEmployeeCount($dept['id']),
                'avg_salary' => $this->getAverageSalary($dept['id']),
                'avg_performance' => $this->getAveragePerformance($dept['id']),
                'attendance_rate' => $this->getAttendanceRate($dept['id']),
                'training_hours' => $this->getTrainingHours($dept['id'])
            ];
        }
        
        return $performance;
    }
    
    // Phân tích chi tiết nhân viên
    public function analyzeEmployeeDetails($employeeId) {
        $details = [];
        
        // Thông tin cơ bản
        $details['basic_info'] = $this->getEmployeeBasicInfo($employeeId);
        
        // Lịch sử công việc
        $details['work_history'] = $this->getWorkHistory($employeeId);
        
        // Đánh giá hiệu suất
        $details['performance'] = $this->getPerformanceHistory($employeeId);
        
        // Lịch sử lương
        $details['salary_history'] = $this->getSalaryHistory($employeeId);
        
        // Lịch sử đào tạo
        $details['training_history'] = $this->getTrainingHistory($employeeId);
        
        return $details;
    }
    
    // Các phương thức hỗ trợ
    private function getDepartments() {
        $query = "SELECT * FROM departments WHERE status = 'active'";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    private function getEmployeeCount($departmentId) {
        $query = "SELECT COUNT(*) as count FROM employees WHERE department_id = ? AND status = 'active'";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $departmentId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['count'];
    }
    
    private function getAverageSalary($departmentId) {
        $query = "SELECT AVG(net_salary) as avg_salary 
                 FROM payroll p 
                 JOIN employees e ON p.employee_id = e.id 
                 WHERE e.department_id = ? AND e.status = 'active'";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $departmentId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['avg_salary'];
    }
    
    private function getAveragePerformance($departmentId) {
        $query = "SELECT AVG(performance_score) as avg_score 
                 FROM performances p 
                 JOIN employees e ON p.employee_id = e.id 
                 WHERE e.department_id = ? AND e.status = 'active'";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $departmentId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['avg_score'];
    }
    
    private function getAttendanceRate($departmentId) {
        $query = "SELECT 
                    (COUNT(CASE WHEN attendance_symbol = 'P' THEN 1 END) * 100.0 / COUNT(*)) as attendance_rate 
                 FROM attendance a 
                 JOIN employees e ON a.employee_id = e.id 
                 WHERE e.department_id = ? AND e.status = 'active'";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $departmentId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['attendance_rate'];
    }
    
    private function getTrainingHours($departmentId) {
        $query = "SELECT SUM(tc.duration) as total_hours 
                 FROM training_courses tc 
                 JOIN employees e ON tc.employee_id = e.id 
                 WHERE e.department_id = ? AND e.status = 'active'";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $departmentId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['total_hours'];
    }
    
    private function analyzeRecruitmentTrends($timeframe) {
        $query = "SELECT 
                    DATE_FORMAT(join_date, '%Y-%m') as month,
                    COUNT(*) as new_employees
                 FROM employees 
                 WHERE join_date >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
                 GROUP BY month
                 ORDER BY month";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    private function analyzeTurnoverTrends($timeframe) {
        $query = "SELECT 
                    DATE_FORMAT(end_date, '%Y-%m') as month,
                    COUNT(*) as leaving_employees
                 FROM employees 
                 WHERE end_date >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
                 GROUP BY month
                 ORDER BY month";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    private function analyzePerformanceTrends($timeframe) {
        $query = "SELECT 
                    DATE_FORMAT(review_period_start, '%Y-%m') as month,
                    AVG(performance_score) as avg_score
                 FROM performances 
                 WHERE review_period_start >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
                 GROUP BY month
                 ORDER BY month";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    private function analyzeSalaryTrends($timeframe) {
        $query = "SELECT 
                    DATE_FORMAT(pay_period_start, '%Y-%m') as month,
                    AVG(net_salary) as avg_salary
                 FROM payroll 
                 WHERE pay_period_start >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
                 GROUP BY month
                 ORDER BY month";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?> 