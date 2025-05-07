<?php
header('Content-Type: application/json');
require_once '../../../config/database.php';

class ChartsAPI {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Get attendance trends data
    public function getAttendanceTrends($period = 'month') {
        $interval = $this->getInterval($period);
        
        $query = "SELECT 
                    DATE(a.attendance_date) as date,
                    COUNT(DISTINCT a.employee_id) as total_employees,
                    SUM(CASE WHEN a.attendance_symbol = 'P' THEN 1 ELSE 0 END) as present,
                    SUM(CASE WHEN a.attendance_symbol = 'A' THEN 1 ELSE 0 END) as absent,
                    SUM(CASE WHEN a.attendance_symbol = 'L' THEN 1 ELSE 0 END) as late,
                    SUM(CASE WHEN a.attendance_symbol = 'H' THEN 1 ELSE 0 END) as half_day
                FROM attendance a
                WHERE a.attendance_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                GROUP BY DATE(a.attendance_date)
                ORDER BY date ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $interval);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return $data;
    }

    // Get department distribution data
    public function getDepartmentDistribution() {
        $query = "SELECT 
                    d.name as department,
                    COUNT(DISTINCT e.id) as employee_count
                FROM departments d
                LEFT JOIN employees e ON d.id = e.department_id
                GROUP BY d.id, d.name
                ORDER BY employee_count DESC";

        $result = $this->conn->query($query);
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return $data;
    }

    // Helper function to get interval days based on period
    private function getInterval($period) {
        switch ($period) {
            case 'week':
                return 7;
            case 'month':
                return 30;
            case 'quarter':
                return 90;
            default:
                return 30;
        }
    }
}

// Initialize database connection
$database = new Database();
$conn = $database->getConnection();

// Create API instance
$api = new ChartsAPI($conn);

// Handle API requests
$action = $_GET['action'] ?? '';
$period = $_GET['period'] ?? 'month';

switch ($action) {
    case 'attendance_trends':
        $data = $api->getAttendanceTrends($period);
        echo json_encode($data);
        break;

    case 'department_distribution':
        $data = $api->getDepartmentDistribution();
        echo json_encode($data);
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?> 