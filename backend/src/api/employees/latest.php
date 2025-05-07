<?php
header('Content-Type: application/json');
require_once '../../config/database.php';
require_once '../../middlewares/auth.php';

// Verify user is logged in and is a manager
checkAuth();
checkRole('manager');

try {
    $db = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("
        SELECT 
            u.id as employee_id,
            CONCAT(up.first_name, ' ', up.last_name) as name,
            p.title as position,
            u.created_at as join_date
        FROM users u
        LEFT JOIN user_profiles up ON u.id = up.user_id
        LEFT JOIN positions p ON u.position_id = p.id
        WHERE u.role = 'employee'
        ORDER BY u.created_at DESC
        LIMIT 5
    ");
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $employees
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi tải danh sách nhân viên: ' . $e->getMessage()
    ]);
} 