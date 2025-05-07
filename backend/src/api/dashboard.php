<?php
require_once '../config/database.php';
require_once '../config/config.php';

header('Content-Type: application/json');

// Get work schedule
if ($_GET['action'] === 'work_schedule') {
    try {
        $stmt = $conn->prepare("
            SELECT 
                ws.work_date,
                e.full_name as employee_name,
                d.name as department_name,
                ws.start_time,
                ws.end_time,
                ws.schedule_type
            FROM work_schedules ws
            JOIN employees e ON ws.employee_id = e.id
            JOIN departments d ON e.department_id = d.id
            WHERE MONTH(ws.work_date) = 1 AND YEAR(ws.work_date) = 2024
            ORDER BY ws.work_date ASC
        ");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Get tasks
if ($_GET['action'] === 'tasks') {
    try {
        $stmt = $conn->prepare("
            SELECT 
                t.title,
                e.full_name as assigned_to,
                t.due_date,
                t.priority,
                t.status
            FROM tasks t
            JOIN employees e ON t.assigned_to = e.id
            WHERE t.status != 'completed'
            ORDER BY t.due_date ASC
            LIMIT 10
        ");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Get team chat messages
if ($_GET['action'] === 'team_chat') {
    try {
        $stmt = $conn->prepare("
            SELECT 
                u.username as user_name,
                n.description,
                n.created_at
            FROM notifications n
            JOIN users u ON n.user_id = u.id
            WHERE n.type = 'chat'
            ORDER BY n.created_at DESC
            LIMIT 20
        ");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Get backup logs
if ($_GET['action'] === 'backup_logs') {
    try {
        $stmt = $conn->prepare("
            SELECT 
                al.action_type as backup_type,
                al.status,
                al.created_at,
                u.username as created_by
            FROM audit_logs al
            JOIN users u ON al.user_id = u.id
            WHERE al.action_type LIKE '%backup%'
            ORDER BY al.created_at DESC
            LIMIT 10
        ");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Get weather data
if ($_GET['action'] === 'weather') {
    // TODO: Replace with your actual OpenWeatherMap API key
    $apiKey = 'YOUR_ACTUAL_OPENWEATHERMAP_API_KEY';
    $city = 'Hanoi';
    $url = "https://api.openweathermap.org/data/2.5/weather?q={$city}&appid={$apiKey}&units=metric";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    echo $response;
}
?> 