<?php
// Start output buffering
ob_start();

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set JSON header
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';
require_once '../../middleware/auth.php';

try {
    // Clear any previous output
    ob_clean();

    // Verify user is logged in and is a manager
    Auth::requireAuth();
    Auth::requireRole('manager');

    $database = new Database();
    $db = $database->getConnection();

    // Lấy hoạt động gần đây từ database
    $query = "
        SELECT 
            a.id,
            a.type,
            a.description,
            a.created_at,
            u.username as user_name,
            u.email as user_email,
            CASE 
                WHEN a.type = 'LOGIN' THEN 'Đăng nhập'
                WHEN a.type = 'UPDATE_PROFILE' THEN 'Cập nhật thông tin'
                WHEN a.type = 'CREATE_LEAVE' THEN 'Tạo đơn nghỉ phép'
                WHEN a.type = 'UPLOAD_DOCUMENT' THEN 'Tải lên tài liệu'
                WHEN a.type = 'APPROVE_LEAVE' THEN 'Duyệt đơn nghỉ phép'
                ELSE a.type
            END as type_name
        FROM activities a
        LEFT JOIN users u ON a.user_id = u.user_id
        ORDER BY a.created_at DESC
        LIMIT 10
    ";

    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format lại thời gian
    foreach ($activities as &$activity) {
        $activity['created_at'] = date('d/m/Y H:i:s', strtotime($activity['created_at']));
    }

    echo json_encode([
        'success' => true,
        'data' => $activities
    ]);
} catch (PDOException $e) {
    // Clear any previous output
    ob_clean();
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi lấy dữ liệu hoạt động: ' . $e->getMessage()
    ]);
}

// End output buffering and send response
ob_end_flush(); 