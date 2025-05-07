<?php
require_once '../../middleware/CORSMiddleware.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Models/Employee.php';
require_once __DIR__ . '/../../app/Models/Attendance.php';
require_once __DIR__ . '/../../app/Models/Leave.php';
require_once __DIR__ . '/../../app/Models/Performance.php';
require_once __DIR__ . '/../../app/Models/Task.php';

use App\Models\Attendance;
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
    $attendance = new Attendance($db);
    $leave = new Leave($db);
    $performance = new Performance($db);
    $task = new Task($db);
    
    // Get employee ID from session or request
    $employeeId = $_SESSION['user_id'] ?? null;
    if (!$employeeId) {
        throw new Exception('Employee ID not found');
    }
    
    // Get statistics
    $stats = [
        // Attendance statistics
        'attendance' => [
            'on_time_count' => $attendance->getOnTimeCount($employeeId),
            'late_count' => $attendance->getLateCount($employeeId),
            'absent_count' => $attendance->getAbsentCount($employeeId),
            'attendance_rate' => $attendance->getAttendanceRate($employeeId)
        ],
        
        // Leave statistics
        'leave' => [
            'total_balance' => $leave->getLeaveBalance($employeeId),
            'used_leaves' => $leave->getUsedLeaves($employeeId),
            'pending_requests' => $leave->getPendingRequests($employeeId),
            'approved_requests' => $leave->getApprovedRequests($employeeId)
        ],
        
        // Performance metrics
        'performance' => [
            'current_score' => $performance->getCurrentScore($employeeId),
            'previous_score' => $performance->getPreviousScore($employeeId),
            'categories' => [
                'work_quality' => $performance->getCategoryScore($employeeId, 'work_quality'),
                'productivity' => $performance->getCategoryScore($employeeId, 'productivity'),
                'communication' => $performance->getCategoryScore($employeeId, 'communication'),
                'teamwork' => $performance->getCategoryScore($employeeId, 'teamwork'),
                'initiative' => $performance->getCategoryScore($employeeId, 'initiative')
            ]
        ],
        
        // Task statistics
        'tasks' => [
            'completed' => $task->getCompletedCount($employeeId),
            'in_progress' => $task->getInProgressCount($employeeId),
            'pending' => $task->getPendingCount($employeeId),
            'overdue' => $task->getOverdueCount($employeeId)
        ],
        
        // Time distribution
        'time_distribution' => [
            'meetings' => 15,
            'project_work' => 45,
            'training' => 10,
            'breaks' => 30
        ],
        
        // Recent activities
        'recent_activities' => [
            [
                'type' => 'attendance',
                'title' => 'Clock In',
                'description' => 'Clocked in at 8:30 AM',
                'timestamp' => '2024-03-15 08:30:00'
            ],
            [
                'type' => 'task',
                'title' => 'Task Completed',
                'description' => 'Completed UI Design for Homepage',
                'timestamp' => '2024-03-14 16:45:00'
            ],
            [
                'type' => 'leave',
                'title' => 'Leave Request',
                'description' => 'Annual leave request approved',
                'timestamp' => '2024-03-13 11:20:00'
            ]
        ]
    ];
    
    echo json_encode($stats);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 