<?php
class DataAnalyzer {
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
    
    // Phân tích tương quan
    public function analyzeCorrelations() {
        $correlations = [];
        
        // Tương quan giữa lương và hiệu suất
        $correlations['salary_performance'] = $this->analyzeSalaryPerformanceCorrelation();
        
        // Tương quan giữa đào tạo và hiệu suất
        $correlations['training_performance'] = $this->analyzeTrainingPerformanceCorrelation();
        
        // Tương quan giữa thâm niên và lương
        $correlations['tenure_salary'] = $this->analyzeTenureSalaryCorrelation();
        
        return $correlations;
    }
    
    // Phân tích dự đoán
    public function analyzePredictions() {
        $predictions = [];
        
        // Dự đoán tỷ lệ nghỉ việc
        $predictions['turnover'] = $this->predictTurnoverRate();
        
        // Dự đoán nhu cầu tuyển dụng
        $predictions['recruitment'] = $this->predictRecruitmentNeeds();
        
        // Dự đoán ngân sách lương
        $predictions['salary_budget'] = $this->predictSalaryBudget();
        
        return $predictions;
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
    
    private function analyzeSalaryPerformanceCorrelation() {
        $query = "SELECT 
                    p.performance_score,
                    py.net_salary
                 FROM performances p
                 JOIN payroll py ON p.employee_id = py.employee_id
                 WHERE p.review_period_start >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
                 AND py.pay_period_start >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    private function analyzeTrainingPerformanceCorrelation() {
        $query = "SELECT 
                    tc.duration as training_hours,
                    p.performance_score
                 FROM training_courses tc
                 JOIN performances p ON tc.employee_id = p.employee_id
                 WHERE tc.completion_date >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
                 AND p.review_period_start >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    private function analyzeTenureSalaryCorrelation() {
        $query = "SELECT 
                    TIMESTAMPDIFF(YEAR, e.join_date, NOW()) as tenure,
                    p.net_salary
                 FROM employees e
                 JOIN payroll p ON e.id = p.employee_id
                 WHERE e.status = 'active'
                 AND p.pay_period_start >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    private function predictTurnoverRate() {
        $query = "SELECT 
                    d.name as department,
                    COUNT(CASE WHEN e.end_date >= DATE_SUB(NOW(), INTERVAL 3 MONTH) THEN 1 END) * 100.0 / 
                    COUNT(*) as turnover_rate
                 FROM departments d
                 LEFT JOIN employees e ON d.id = e.department_id
                 WHERE d.status = 'active'
                 GROUP BY d.id, d.name";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    private function predictRecruitmentNeeds() {
        $query = "SELECT 
                    d.name as department,
                    COUNT(*) as current_employees,
                    COUNT(CASE WHEN e.end_date >= DATE_SUB(NOW(), INTERVAL 3 MONTH) THEN 1 END) as leaving_employees,
                    COUNT(CASE WHEN e.join_date >= DATE_SUB(NOW(), INTERVAL 3 MONTH) THEN 1 END) as new_employees
                 FROM departments d
                 LEFT JOIN employees e ON d.id = e.department_id
                 WHERE d.status = 'active'
                 GROUP BY d.id, d.name";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    private function predictSalaryBudget() {
        $query = "SELECT 
                    d.name as department,
                    AVG(p.net_salary) as avg_salary,
                    COUNT(e.id) as employee_count,
                    AVG(p.net_salary) * COUNT(e.id) as total_budget
                 FROM departments d
                 LEFT JOIN employees e ON d.id = e.department_id
                 LEFT JOIN payroll p ON e.id = p.employee_id
                 WHERE d.status = 'active'
                 AND e.status = 'active'
                 AND p.pay_period_start >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
                 GROUP BY d.id, d.name";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?> 