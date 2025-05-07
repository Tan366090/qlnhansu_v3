<?php
require_once '../../middleware/CORSMiddleware.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Models/Employee.php';
require_once __DIR__ . '/../../app/Models/Leave.php';
require_once __DIR__ . '/../../app/Models/Performance.php';

use App\Models\Leave;
use App\Models\Performance;

// Handle CORS
CORSMiddleware::handleRequest();

// Set content type to JSON
header('Content-Type: application/json');

try {
    $database = new Database();
    $db = $database->connect();
    
    $employee = new Employee($db);
    $leave = new Leave($db);
    $performance = new Performance($db);
    
    // Get statistics
    $stats = [
        'total_employees' => $employee->getTotalEmployees(),
        'new_employees' => $employee->getNewEmployeesThisMonth(),
        'turnover_rate' => round(($employee->getResignedEmployeesThisMonth() / $employee->getTotalEmployees()) * 100, 2),
        'attendance_rate' => 95.5, // This should be calculated from actual attendance data
        
        // Contract status distribution
        'contract_status' => [
            'permanent' => $employee->getEmployeeCountByContractStatus('Permanent'),
            'contract' => $employee->getEmployeeCountByContractStatus('Contract'),
            'probation' => $employee->getEmployeeCountByContractStatus('Probation')
        ],
        
        // Leave trends for last 6 months
        'leave_trends' => $leave->getMonthlyLeaveTrends(6),
        
        // Education level distribution
        'education_levels' => $employee->getEducationDistribution(),
        
        // Average performance scores
        'performance_scores' => [
            'work_quality' => $performance->getAverageScoreByCategory('work_quality'),
            'productivity' => $performance->getAverageScoreByCategory('productivity'),
            'communication' => $performance->getAverageScoreByCategory('communication'),
            'teamwork' => $performance->getAverageScoreByCategory('teamwork'),
            'initiative' => $performance->getAverageScoreByCategory('initiative')
        ],
        
        // Recent activities
        'recent_activities' => [
            [
                'type' => 'new_hire',
                'title' => 'New Employee Hired',
                'description' => 'John Doe joined as Software Developer',
                'timestamp' => '2024-03-15 09:00:00'
            ],
            [
                'type' => 'leave_request',
                'title' => 'Leave Request Approved',
                'description' => 'Annual leave request approved for Jane Smith',
                'timestamp' => '2024-03-14 14:30:00'
            ],
            [
                'type' => 'performance_review',
                'title' => 'Performance Review Completed',
                'description' => 'Q1 performance review completed for Development team',
                'timestamp' => '2024-03-13 16:45:00'
            ]
        ]
    ];
    
    echo json_encode($stats);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 