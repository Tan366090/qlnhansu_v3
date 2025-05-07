<?php
// Đảm bảo không có output nào trước khi set header
ob_start();

// Bắt đầu session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tắt hiển thị lỗi
error_reporting(0);
ini_set('display_errors', 0);

// Kiểm tra file cấu hình database
if (!file_exists(__DIR__ . '/../../config/database.php')) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Database configuration file not found'
    ]);
    exit;
}

// Include file cấu hình database
require_once __DIR__ . '/../../config/database.php';

try {
    // Kiểm tra kết nối database
    if (!$db) {
        throw new Exception("Database connection failed");
    }

    // Kiểm tra bảng activities
    $checkTable = $db->query("SHOW TABLES LIKE 'activities'");
    if ($checkTable->rowCount() == 0) {
        throw new Exception("Activities table does not exist");
    }

    // Lấy tham số từ request
    $recent = isset($_GET['recent']) ? (int)$_GET['recent'] : 0;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

    // Query để lấy hoạt động gần đây
    $query = "SELECT 
                id,
                user_id,
                type,
                description,
                target_entity,
                target_entity_id,
                status,
                user_agent,
                ip_address,
                created_at
            FROM activities
            ORDER BY created_at DESC
            LIMIT :limit";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format activities
    $formattedActivities = array_map(function($activity) {
        return [
            'id' => $activity['id'],
            'user' => [
                'id' => $activity['user_id']
            ],
            'type' => $activity['type'],
            'description' => $activity['description'],
            'target' => [
                'entity' => $activity['target_entity'],
                'id' => $activity['target_entity_id']
            ],
            'status' => $activity['status'],
            'userAgent' => $activity['user_agent'],
            'ipAddress' => $activity['ip_address'],
            'icon' => getActivityIcon($activity['type']),
            'timeAgo' => getTimeAgo($activity['created_at']),
            'timestamp' => $activity['created_at']
        ];
    }, $activities);

    // Lấy thống kê
    $statsQuery = "SELECT 
        COUNT(*) as total_activities,
        COUNT(DISTINCT user_id) as total_users,
        SUM(CASE WHEN type = 'LOGIN' THEN 1 ELSE 0 END) as login_count,
        SUM(CASE WHEN type = 'CREATE_PROJECT' THEN 1 ELSE 0 END) as created_projects,
        SUM(CASE WHEN type = 'COMPLETE_TASK' THEN 1 ELSE 0 END) as completed_tasks
    FROM activities";
    
    $stats = $db->query($statsQuery)->fetch(PDO::FETCH_ASSOC);

    // Xóa output buffer và trả về JSON
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'activities' => $formattedActivities,
        'statistics' => [
            'totalActivities' => (int)$stats['total_activities'],
            'totalUsers' => (int)$stats['total_users'],
            'loginCount' => (int)$stats['login_count'],
            'createdProjects' => (int)$stats['created_projects'],
            'completedTasks' => (int)$stats['completed_tasks']
        ]
    ]);
    exit;

} catch (Exception $e) {
    // Xóa output buffer và trả về lỗi JSON
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
    exit;
}

// Helper function to get activity icon
function getActivityIcon($type) {
    $icons = [
        'LOGIN' => 'fa-sign-in-alt',
        'LOGOUT' => 'fa-sign-out-alt',
        'UPDATE_PROFILE' => 'fa-user-edit',
        'CREATE_LEAVE' => 'fa-calendar-plus',
        'APPROVE_LEAVE' => 'fa-calendar-check',
        'UPLOAD_DOCUMENT' => 'fa-file-upload',
        'ASSIGN_ASSET' => 'fa-box',
        'GENERATE_REPORT' => 'fa-file-alt',
        'CREATE_PROJECT' => 'fa-project-diagram',
        'COMPLETE_TASK' => 'fa-check-circle'
    ];
    return $icons[$type] ?? 'fa-info-circle';
}

// Helper function to get time ago
function getTimeAgo($timestamp) {
    $time = strtotime($timestamp);
    $now = time();
    $diff = $now - $time;

    if ($diff < 60) {
        return 'Vừa xong';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' phút trước';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' giờ trước';
    } elseif ($diff < 2592000) {
        $days = floor($diff / 86400);
        return $days . ' ngày trước';
    } else {
        $months = floor($diff / 2592000);
        return $months . ' tháng trước';
    }
}
?> 