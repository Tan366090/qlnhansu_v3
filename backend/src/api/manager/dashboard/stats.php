<?php
require_once '../../middleware/CORSMiddleware.php';
require_once '../../config/Database.php';
require_once __DIR__ . '/../../app/Models/Employee.php';
require_once __DIR__ . '/../../app/Models/Project.php';
require_once __DIR__ . '/../../app/Models/Task.php';
require_once __DIR__ . '/../../app/Models/Performance.php';
require_once __DIR__ . '/../../config/database.php';

use App\Models\Performance;

// Handle CORS
CORSMiddleware::handleRequest();

// Set content type to JSON
header('Content-Type: application/json');

try {
    $database = new Database();
    $db = $database->connect();
    
    $employee = new Employee($db);
    $project = new Project($db);
    $task = new Task($db);
    $performance = new Performance($db);
    
    // Get manager ID from session or request
    $managerId = $_SESSION['user_id'] ?? null;
    if (!$managerId) {
        throw new Exception('Manager ID not found');
    }
    
    // Get statistics
    $stats = [
        'team_size' => $employee->getTeamSize($managerId),
        'projects_count' => $project->getActiveProjectsCount($managerId),
        'tasks_completed' => $task->getCompletedTasksCount($managerId),
        'team_performance' => $performance->getTeamAverageScore($managerId),
        
        // Project progress
        'project_progress' => $project->getProjectProgress($managerId),
        
        // Task distribution
        'task_distribution' => [
            'completed' => $task->getTaskCountByStatus($managerId, 'completed'),
            'in_progress' => $task->getTaskCountByStatus($managerId, 'in_progress'),
            'pending' => $task->getTaskCountByStatus($managerId, 'pending'),
            'overdue' => $task->getTaskCountByStatus($managerId, 'overdue')
        ],
        
        // Team performance by category
        'team_performance_categories' => [
            'work_quality' => $performance->getTeamAverageByCategory($managerId, 'work_quality'),
            'productivity' => $performance->getTeamAverageByCategory($managerId, 'productivity'),
            'communication' => $performance->getTeamAverageByCategory($managerId, 'communication'),
            'teamwork' => $performance->getTeamAverageByCategory($managerId, 'teamwork'),
            'initiative' => $performance->getTeamAverageByCategory($managerId, 'initiative')
        ],
        
        // Team attendance
        'team_attendance' => $employee->getTeamAttendance($managerId),
        
        // Recent activities
        'recent_activities' => [
            [
                'type' => 'task_completed',
                'title' => 'Task Completed',
                'description' => 'Frontend Development for Project X completed',
                'timestamp' => '2024-03-15 10:30:00'
            ],
            [
                'type' => 'project_milestone',
                'title' => 'Project Milestone Reached',
                'description' => 'Phase 1 of Project Y completed',
                'timestamp' => '2024-03-14 15:45:00'
            ],
            [
                'type' => 'performance_review',
                'title' => 'Performance Reviews Due',
                'description' => 'Monthly performance reviews pending for 3 team members',
                'timestamp' => '2024-03-13 09:00:00'
            ]
        ]
    ];
    
    echo json_encode($stats);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 