<?php
header('Content-Type: application/json');
require_once '../config.php';
require_once '../middleware/auth.php';

// Kiểm tra quyền truy cập
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Lấy danh sách quyền
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $conn->prepare("
            SELECT p.*, r.role_name 
            FROM permissions p 
            LEFT JOIN roles r ON p.role_id = r.id
            ORDER BY p.id
        ");
        $stmt->execute();
        $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $permissions]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

// Cập nhật quyền
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['role_id']) || !isset($data['permissions'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }
    
    try {
        $conn->beginTransaction();
        
        // Xóa quyền cũ
        $stmt = $conn->prepare("DELETE FROM permissions WHERE role_id = ?");
        $stmt->execute([$data['role_id']]);
        
        // Thêm quyền mới
        $stmt = $conn->prepare("
            INSERT INTO permissions (role_id, module, can_view, can_create, can_edit, can_delete) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($data['permissions'] as $permission) {
            $stmt->execute([
                $data['role_id'],
                $permission['module'],
                $permission['can_view'] ?? 0,
                $permission['can_create'] ?? 0,
                $permission['can_edit'] ?? 0,
                $permission['can_delete'] ?? 0
            ]);
        }
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Permissions updated successfully']);
    } catch (PDOException $e) {
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
