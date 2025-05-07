<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Models/Employee.php';

use App\Models\Employee;

$database = new Database();
$db = $database->getConnection();

$employee = new Employee($db);

// Xử lý request GET - Lấy dữ liệu ban đầu
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Lấy thống kê nhân viên theo phòng ban
        $departmentStats = $employee->getDepartmentStats();
        
        // Lấy danh sách nhân viên
        $employees = $employee->getAllEmployees();
        
        echo json_encode([
            'departments' => $departmentStats,
            'employees' => $employees
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Xử lý request POST - Tạo báo cáo theo điều kiện
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Lấy thống kê theo điều kiện
        $departmentStats = $employee->getDepartmentStats($data);
        
        // Lấy danh sách nhân viên theo điều kiện
        $employees = $employee->getEmployeesByFilter($data);
        
        echo json_encode([
            'departments' => $departmentStats,
            'employees' => $employees
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
} 