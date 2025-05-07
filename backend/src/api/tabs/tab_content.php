<?php
header('Content-Type: application/json');
require_once '../config.php';
require_once '../middleware/auth.php';

// Kiểm tra quyền truy cập
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Lấy nội dung tab theo ID
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['tab_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing tab_id parameter']);
        exit;
    }
    
    try {
        $stmt = $conn->prepare("
            SELECT t.*, u.username as created_by_name 
            FROM tabs t
            LEFT JOIN users u ON t.created_by = u.id
            WHERE t.id = ?
        ");
        $stmt->execute([$_GET['tab_id']]);
        $tab = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$tab) {
            http_response_code(404);
            echo json_encode(['error' => 'Tab not found']);
            exit;
        }
        
        // Kiểm tra quyền truy cập tab
        if (!hasPermission($tab['module'], 'view')) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            exit;
        }
        
        echo json_encode(['success' => true, 'data' => $tab]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

// Cập nhật nội dung tab
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['tab_id']) || !isset($data['content'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }
    
    try {
        // Kiểm tra quyền chỉnh sửa
        $stmt = $conn->prepare("SELECT module FROM tabs WHERE id = ?");
        $stmt->execute([$data['tab_id']]);
        $tab = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$tab) {
            http_response_code(404);
            echo json_encode(['error' => 'Tab not found']);
            exit;
        }
        
        if (!hasPermission($tab['module'], 'edit')) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            exit;
        }
        
        // Cập nhật nội dung
        $stmt = $conn->prepare("
            UPDATE tabs 
            SET content = ?, updated_at = NOW(), updated_by = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $data['content'],
            $_SESSION['user_id'],
            $data['tab_id']
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Tab content updated successfully']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} 