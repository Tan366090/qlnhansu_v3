<?php
require_once '../../middleware/CORSMiddleware.php';
require_once '../../config/Database.php';
require_once '../../models/Employee.php';

// Handle CORS
CORSMiddleware::handleRequest();

// Set content type to JSON
header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

$employee = new Employee($db);

try {
    // Lấy tổng số nhân viên
    $totalEmployees = $employee->getTotalEmployees();
    
    // Lấy số nhân viên mới trong tháng
    $newEmployees = $employee->getNewEmployeesThisMonth();
    
    // Lấy số nhân viên nghỉ việc trong tháng
    $resignedEmployees = $employee->getResignedEmployeesThisMonth();
    
    // Tính tỷ lệ nghỉ việc
    $resignationRate = $totalEmployees > 0 ? 
        round(($resignedEmployees / $totalEmployees) * 100, 2) : 0;
    
    // Lấy thống kê theo phòng ban
    $departmentStats = $employee->getDepartmentStats();
    
    // Lấy thống kê theo chức vụ
    $positionStats = $employee->getPositionStats();
    
    // Lấy xu hướng tuyển dụng
    $hiringTrend = $employee->getHiringTrend();
    
    // Lấy phân bố độ tuổi
    $ageDistribution = $employee->getAgeDistribution();
    
    echo json_encode([
        'totalEmployees' => $totalEmployees,
        'newEmployees' => $newEmployees,
        'resignedEmployees' => $resignedEmployees,
        'resignationRate' => $resignationRate,
        'departments' => $departmentStats,
        'positions' => $positionStats,
        'hiringTrend' => $hiringTrend,
        'ageDistribution' => $ageDistribution
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 