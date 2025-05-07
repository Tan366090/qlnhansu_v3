<?php
header('Content-Type: application/json');

// Load database configuration
require_once '../../config/database.php';

// Initialize database connection
try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'")
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit;
}

// Get the request URI and method
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Parse the request URI to get the table name
$uriParts = explode('/', trim($requestUri, '/'));
$lastPart = end($uriParts);

// Handle GET requests
if ($requestMethod === 'GET') {
    try {
        // If no specific table is requested, return list of all tables
        if ($lastPart === 'database.php') {
            $stmt = $conn->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo json_encode([
                'success' => true,
                'tables' => $tables
            ]);
            exit;
        }

        // If a specific table is requested, get its data
        if (in_array($lastPart, [
            'activities', 'assets', 'attendance', 'benefits', 'certificates',
            'contracts', 'departments', 'documents', 'employees', 'kpi',
            'leaves', 'payroll', 'performances', 'positions', 'projects',
            'salaries', 'training_courses'
        ])) {
            // Get table structure
            $stmt = $conn->query("DESCRIBE $lastPart");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Build query based on table name
            $query = "SELECT * FROM $lastPart";
            
            // Add joins and specific fields for certain tables
            switch($lastPart) {
                case 'attendance':
                    $query = "SELECT a.*, e.full_name as employee_name 
                             FROM attendance a 
                             LEFT JOIN employees e ON a.employee_id = e.id 
                             ORDER BY a.attendance_date DESC";
                    break;
                    
                case 'employees':
                    $query = "SELECT e.*, d.name as department_name, p.name as position_name 
                             FROM employees e 
                             LEFT JOIN departments d ON e.department_id = d.id 
                             LEFT JOIN positions p ON e.position_id = p.id 
                             ORDER BY e.id";
                    break;
                    
                case 'departments':
                    $query = "SELECT d.*, COUNT(e.id) as employee_count 
                             FROM departments d 
                             LEFT JOIN employees e ON d.id = e.department_id 
                             GROUP BY d.id 
                             ORDER BY d.id";
                    break;
                    
                case 'positions':
                    $query = "SELECT p.*, COUNT(e.id) as employee_count 
                             FROM positions p 
                             LEFT JOIN employees e ON p.id = e.position_id 
                             GROUP BY p.id 
                             ORDER BY p.id";
                    break;
                    
                case 'contracts':
                    $query = "SELECT c.*, e.full_name as employee_name, ct.name as contract_type_name 
                             FROM contracts c 
                             LEFT JOIN employees e ON c.employee_id = e.id 
                             LEFT JOIN contract_types ct ON c.contract_type_id = ct.id 
                             ORDER BY c.id";
                    break;
                    
                case 'salaries':
                    $query = "SELECT s.*, e.full_name as employee_name 
                             FROM salaries s 
                             LEFT JOIN employees e ON s.employee_id = e.id 
                             ORDER BY s.id";
                    break;
                    
                case 'leaves':
                    $query = "SELECT l.*, e.full_name as employee_name, lt.name as leave_type_name 
                             FROM leaves l 
                             LEFT JOIN employees e ON l.employee_id = e.id 
                             LEFT JOIN leave_types lt ON l.leave_type_id = lt.id 
                             ORDER BY l.id";
                    break;
                    
                case 'activities':
                    $query = "SELECT a.*, u.username as user_name 
                             FROM activities a 
                             LEFT JOIN users u ON a.user_id = u.id 
                             ORDER BY a.created_at DESC";
                    break;
                    
                case 'assets':
                    $query = "SELECT a.*, e.full_name as assigned_to_name 
                             FROM assets a 
                             LEFT JOIN employees e ON a.assigned_to = e.id 
                             ORDER BY a.id";
                    break;
                    
                case 'benefits':
                    $query = "SELECT b.*, e.full_name as employee_name 
                             FROM benefits b 
                             LEFT JOIN employees e ON b.employee_id = e.id 
                             ORDER BY b.id";
                    break;
                    
                case 'certificates':
                    $query = "SELECT c.*, e.full_name as employee_name 
                             FROM certificates c 
                             LEFT JOIN employees e ON c.employee_id = e.id 
                             ORDER BY c.id";
                    break;
                    
                case 'documents':
                    $query = "SELECT d.*, e.full_name as uploaded_by_name 
                             FROM documents d 
                             LEFT JOIN employees e ON d.uploaded_by = e.id 
                             ORDER BY d.id";
                    break;
                    
                case 'kpi':
                    $query = "SELECT k.*, e.full_name as employee_name 
                             FROM kpi k 
                             LEFT JOIN employees e ON k.employee_id = e.id 
                             ORDER BY k.id";
                    break;
                    
                case 'payroll':
                    $query = "SELECT p.*, e.full_name as employee_name 
                             FROM payroll p 
                             LEFT JOIN employees e ON p.employee_id = e.id 
                             ORDER BY p.id";
                    break;
                    
                case 'performances':
                    $query = "SELECT p.*, e.full_name as employee_name 
                             FROM performances p 
                             LEFT JOIN employees e ON p.employee_id = e.id 
                             ORDER BY p.id";
                    break;
                    
                case 'projects':
                    $query = "SELECT p.*, e.full_name as manager_name 
                             FROM projects p 
                             LEFT JOIN employees e ON p.manager_id = e.id 
                             ORDER BY p.id";
                    break;
                    
                case 'training_courses':
                    $query = "SELECT t.*, e.full_name as instructor_name 
                             FROM training_courses t 
                             LEFT JOIN employees e ON t.instructor_id = e.id 
                             ORDER BY t.id";
                    break;
            }
            
            // Add limit if not specified in the query
            if (!strpos($query, 'LIMIT')) {
                $query .= " LIMIT 100";
            }
            
            // Get table data
            $stmt = $conn->query($query);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format the response
            $response = [
                'success' => true,
                'data' => [
                    'headers' => $columns,
                    'rows' => $rows
                ]
            ];
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
        exit;
    }
}

// If we get here, the request was invalid
echo json_encode([
    'success' => false,
    'message' => 'Invalid request'
]); 