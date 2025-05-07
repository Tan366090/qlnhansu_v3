<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../config/database.php';

class APITest {
    private $conn;
    private $results = [];

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function testEmployees() {
        try {
            $query = "SELECT 
                        e.*,
                        d.department_name,
                        p.position_name,
                        s.salary_amount
                    FROM employees e
                    LEFT JOIN departments d ON e.department_id = d.department_id
                    LEFT JOIN positions p ON e.position_id = p.position_id
                    LEFT JOIN salaries s ON e.employee_id = s.employee_id
                    ORDER BY e.employee_id DESC
                    LIMIT 5";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->results['employees'] = [
                'success' => true,
                'count' => count($data),
                'sample_data' => $data
            ];
        } catch (PDOException $e) {
            $this->results['employees'] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function testAttendance() {
        try {
            $query = "SELECT 
                        a.*,
                        e.full_name,
                        e.employee_code,
                        d.department_name
                    FROM attendance a
                    LEFT JOIN employees e ON a.employee_id = e.employee_id
                    LEFT JOIN departments d ON e.department_id = d.department_id
                    ORDER BY a.attendance_date DESC
                    LIMIT 5";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->results['attendance'] = [
                'success' => true,
                'count' => count($data),
                'sample_data' => $data
            ];
        } catch (PDOException $e) {
            $this->results['attendance'] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function testDepartments() {
        try {
            $query = "SELECT 
                        d.*,
                        COUNT(e.employee_id) as employee_count,
                        m.full_name as manager_name
                    FROM departments d
                    LEFT JOIN employees e ON d.department_id = e.department_id
                    LEFT JOIN employees m ON d.manager_id = m.employee_id
                    GROUP BY d.department_id
                    ORDER BY d.department_name";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->results['departments'] = [
                'success' => true,
                'count' => count($data),
                'sample_data' => $data
            ];
        } catch (PDOException $e) {
            $this->results['departments'] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function testSalaries() {
        try {
            $query = "SELECT 
                        s.*,
                        e.full_name,
                        e.employee_code,
                        d.department_name,
                        p.position_name
                    FROM salaries s
                    LEFT JOIN employees e ON s.employee_id = e.employee_id
                    LEFT JOIN departments d ON e.department_id = d.department_id
                    LEFT JOIN positions p ON e.position_id = p.position_id
                    ORDER BY s.salary_date DESC
                    LIMIT 5";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->results['salaries'] = [
                'success' => true,
                'count' => count($data),
                'sample_data' => $data
            ];
        } catch (PDOException $e) {
            $this->results['salaries'] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function testDashboardStats() {
        try {
            // Total employees
            $query = "SELECT COUNT(*) as total FROM employees";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $totalEmployees = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Active employees
            $query = "SELECT COUNT(*) as active FROM employees WHERE status = 'active'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $activeEmployees = $stmt->fetch(PDO::FETCH_ASSOC)['active'];

            // Today's attendance
            $query = "SELECT COUNT(*) as present FROM attendance 
                     WHERE DATE(attendance_date) = CURDATE() AND status = 'present'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $todayAttendance = $stmt->fetch(PDO::FETCH_ASSOC)['present'];

            // Total salary this month
            $query = "SELECT SUM(net_salary) as total FROM salaries 
                     WHERE MONTH(salary_date) = MONTH(CURDATE()) 
                     AND YEAR(salary_date) = YEAR(CURDATE())";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $monthlySalary = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            $this->results['dashboard_stats'] = [
                'success' => true,
                'data' => [
                    'total_employees' => $totalEmployees,
                    'active_employees' => $activeEmployees,
                    'today_attendance' => $todayAttendance,
                    'monthly_salary' => $monthlySalary
                ]
            ];
        } catch (PDOException $e) {
            $this->results['dashboard_stats'] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function getResults() {
        return $this->results;
    }
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    $test = new APITest($conn);
    
    // Run all tests
    $test->testEmployees();
    $test->testAttendance();
    $test->testDepartments();
    $test->testSalaries();
    $test->testDashboardStats();

    echo json_encode([
        'success' => true,
        'results' => $test->getResults()
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 