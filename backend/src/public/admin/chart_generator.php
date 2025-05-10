<?php
class ChartGenerator {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // Tạo biểu đồ xu hướng nhân sự
    public function generateHRTrendChart($startDate, $endDate) {
        $data = [
            'labels' => [],
            'datasets' => [
                [
                    'label' => 'Nhân viên mới',
                    'data' => [],
                    'borderColor' => '#4CAF50',
                    'backgroundColor' => 'rgba(76, 175, 80, 0.1)'
                ],
                [
                    'label' => 'Nhân viên nghỉ việc',
                    'data' => [],
                    'borderColor' => '#F44336',
                    'backgroundColor' => 'rgba(244, 67, 54, 0.1)'
                ]
            ]
        ];
        
        $query = "SELECT 
                    DATE_FORMAT(date, '%Y-%m') as month,
                    SUM(CASE WHEN type = 'new' THEN 1 ELSE 0 END) as new_employees,
                    SUM(CASE WHEN type = 'leave' THEN 1 ELSE 0 END) as leaving_employees
                 FROM employee_changes
                 WHERE date BETWEEN ? AND ?
                 GROUP BY month
                 ORDER BY month";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ss', $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $data['labels'][] = $row['month'];
            $data['datasets'][0]['data'][] = $row['new_employees'];
            $data['datasets'][1]['data'][] = $row['leaving_employees'];
        }
        
        return $data;
    }
    
    // Tạo biểu đồ phân bố nhân sự theo phòng ban
    public function generateDepartmentDistributionChart() {
        $data = [
            'labels' => [],
            'datasets' => [
                [
                    'data' => [],
                    'backgroundColor' => [
                        '#4CAF50', '#2196F3', '#FFC107', '#9C27B0', '#FF5722',
                        '#795548', '#607D8B', '#E91E63', '#3F51B5', '#009688'
                    ]
                ]
            ]
        ];
        
        $query = "SELECT 
                    d.name,
                    COUNT(e.id) as employee_count
                 FROM departments d
                 LEFT JOIN employees e ON d.id = e.department_id
                 WHERE d.status = 'active' AND e.status = 'active'
                 GROUP BY d.id, d.name";
        
        $result = $this->conn->query($query);
        
        while ($row = $result->fetch_assoc()) {
            $data['labels'][] = $row['name'];
            $data['datasets'][0]['data'][] = $row['employee_count'];
        }
        
        return $data;
    }
    
    // Tạo biểu đồ lương theo phòng ban
    public function generateSalaryDistributionChart() {
        $data = [
            'labels' => [],
            'datasets' => [
                [
                    'label' => 'Lương trung bình',
                    'data' => [],
                    'backgroundColor' => 'rgba(33, 150, 243, 0.5)',
                    'borderColor' => '#2196F3',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Lương cao nhất',
                    'data' => [],
                    'backgroundColor' => 'rgba(76, 175, 80, 0.5)',
                    'borderColor' => '#4CAF50',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Lương thấp nhất',
                    'data' => [],
                    'backgroundColor' => 'rgba(244, 67, 54, 0.5)',
                    'borderColor' => '#F44336',
                    'borderWidth' => 1
                ]
            ]
        ];
        
        $query = "SELECT 
                    d.name,
                    AVG(p.net_salary) as avg_salary,
                    MAX(p.net_salary) as max_salary,
                    MIN(p.net_salary) as min_salary
                 FROM departments d
                 LEFT JOIN employees e ON d.id = e.department_id
                 LEFT JOIN payroll p ON e.id = p.employee_id
                 WHERE d.status = 'active' AND e.status = 'active'
                 GROUP BY d.id, d.name";
        
        $result = $this->conn->query($query);
        
        while ($row = $result->fetch_assoc()) {
            $data['labels'][] = $row['name'];
            $data['datasets'][0]['data'][] = round($row['avg_salary']);
            $data['datasets'][1]['data'][] = round($row['max_salary']);
            $data['datasets'][2]['data'][] = round($row['min_salary']);
        }
        
        return $data;
    }
    
    // Tạo biểu đồ đánh giá hiệu suất
    public function generatePerformanceChart($quarter, $year) {
        $data = [
            'labels' => ['Xuất sắc', 'Tốt', 'Khá', 'Trung bình', 'Yếu'],
            'datasets' => [
                [
                    'data' => [0, 0, 0, 0, 0],
                    'backgroundColor' => [
                        '#4CAF50', '#8BC34A', '#FFC107', '#FF9800', '#F44336'
                    ]
                ]
            ]
        ];
        
        $query = "SELECT 
                    rating,
                    COUNT(*) as count
                 FROM performances
                 WHERE QUARTER(review_period_start) = ? AND YEAR(review_period_start) = ?
                 GROUP BY rating
                 ORDER BY FIELD(rating, 'Xuất sắc', 'Tốt', 'Khá', 'Trung bình', 'Yếu')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ii', $quarter, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $index = array_search($row['rating'], $data['labels']);
            if ($index !== false) {
                $data['datasets'][0]['data'][$index] = $row['count'];
            }
        }
        
        return $data;
    }
    
    // Tạo biểu đồ xu hướng lương
    public function generateSalaryTrendChart($year) {
        $data = [
            'labels' => ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
            'datasets' => [
                [
                    'label' => 'Lương trung bình',
                    'data' => array_fill(0, 12, 0),
                    'borderColor' => '#2196F3',
                    'backgroundColor' => 'rgba(33, 150, 243, 0.1)'
                ]
            ]
        ];
        
        $query = "SELECT 
                    MONTH(pay_period_start) as month,
                    AVG(net_salary) as avg_salary
                 FROM payroll
                 WHERE YEAR(pay_period_start) = ?
                 GROUP BY month
                 ORDER BY month";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $year);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $data['datasets'][0]['data'][$row['month'] - 1] = round($row['avg_salary']);
        }
        
        return $data;
    }
    
    // Tạo biểu đồ tỷ lệ nghỉ phép
    public function generateLeaveDistributionChart($month, $year) {
        $data = [
            'labels' => ['Nghỉ phép', 'Nghỉ ốm', 'Nghỉ thai sản', 'Nghỉ không lương'],
            'datasets' => [
                [
                    'data' => [0, 0, 0, 0],
                    'backgroundColor' => [
                        '#4CAF50', '#2196F3', '#FFC107', '#F44336'
                    ]
                ]
            ]
        ];
        
        $query = "SELECT 
                    leave_type,
                    COUNT(*) as count
                 FROM leaves
                 WHERE MONTH(start_date) = ? AND YEAR(start_date) = ?
                 GROUP BY leave_type";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ii', $month, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $index = array_search($row['leave_type'], $data['labels']);
            if ($index !== false) {
                $data['datasets'][0]['data'][$index] = $row['count'];
            }
        }
        
        return $data;
    }
}
?> 