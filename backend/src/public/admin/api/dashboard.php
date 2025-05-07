<?php
header('Content-Type: application/json');
require_once '../../../config/database.php';

class DashboardAPI {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Get work schedule for current month
    public function getWorkSchedule() {
        $query = "SELECT 
                    ws.work_date,
                    ws.start_time,
                    ws.end_time,
                    ws.schedule_type,
                    e.id as employee_id,
                    up.full_name as employee_name,
                    d.name as department_name
                FROM work_schedules ws
                JOIN employees e ON ws.employee_id = e.id
                JOIN user_profiles up ON e.user_id = up.user_id
                JOIN departments d ON e.department_id = d.id
                WHERE MONTH(ws.work_date) = MONTH(CURRENT_DATE())
                AND YEAR(ws.work_date) = YEAR(CURRENT_DATE())
                ORDER BY ws.work_date, ws.start_time";

        $result = $this->conn->query($query);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    // Get tasks for current month
    public function getTasks() {
        $query = "SELECT 
                    t.id,
                    t.title,
                    t.description,
                    t.due_date,
                    t.priority,
                    t.status,
                    up.full_name as assigned_to,
                    d.name as department_name
                FROM tasks t
                LEFT JOIN employees e ON t.assigned_to_employee_id = e.id
                LEFT JOIN user_profiles up ON e.user_id = up.user_id
                LEFT JOIN departments d ON e.department_id = d.id
                WHERE MONTH(t.due_date) = MONTH(CURRENT_DATE())
                AND YEAR(t.due_date) = YEAR(CURRENT_DATE())
                ORDER BY t.due_date, t.priority";

        $result = $this->conn->query($query);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    // Get backup logs
    public function getBackupLogs() {
        $query = "SELECT 
                    bl.backup_type,
                    bl.file_path,
                    bl.file_size_bytes,
                    bl.status,
                    bl.created_at,
                    up.full_name as created_by
                FROM backup_logs bl
                LEFT JOIN users u ON bl.created_by_user_id = u.user_id
                LEFT JOIN user_profiles up ON u.user_id = up.user_id
                ORDER BY bl.created_at DESC
                LIMIT 10";

        $result = $this->conn->query($query);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    // Get team chat activities
    public function getTeamChat() {
        $query = "SELECT 
                    a.id,
                    a.description,
                    a.created_at,
                    up.full_name as user_name,
                    d.name as department_name
                FROM activities a
                LEFT JOIN users u ON a.user_id = u.user_id
                LEFT JOIN user_profiles up ON u.user_id = up.user_id
                LEFT JOIN employees e ON u.user_id = e.user_id
                LEFT JOIN departments d ON e.department_id = d.id
                WHERE a.type = 'CHAT'
                ORDER BY a.created_at DESC
                LIMIT 20";

        $result = $this->conn->query($query);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
}

// Initialize database connection
$database = new Database();
$conn = $database->getConnection();

// Create API instance
$api = new DashboardAPI($conn);

// Handle API requests
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'work_schedule':
        $data = $api->getWorkSchedule();
        echo json_encode($data);
        break;

    case 'tasks':
        $data = $api->getTasks();
        echo json_encode($data);
        break;

    case 'backup_logs':
        $data = $api->getBackupLogs();
        echo json_encode($data);
        break;

    case 'team_chat':
        $data = $api->getTeamChat();
        echo json_encode($data);
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?> 