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

// Lấy danh sách tab
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $conn->prepare("
            SELECT t.*, u.username as created_by_name 
            FROM tabs t
            LEFT JOIN users u ON t.created_by = u.id
            ORDER BY t.order_index ASC
        ");
        $stmt->execute();
        $tabs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Lọc các tab theo quyền truy cập
        $filteredTabs = array_filter($tabs, function($tab) {
            return hasPermission($tab['module'], 'view');
        });
        
        echo json_encode(['success' => true, 'data' => array_values($filteredTabs)]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

// Tạo tab mới
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['title']) || !isset($data['module'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }
    
    try {
        // Kiểm tra quyền tạo tab
        if (!hasPermission($data['module'], 'create')) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            exit;
        }
        
        // Lấy order_index cao nhất
        $stmt = $conn->prepare("SELECT MAX(order_index) as max_order FROM tabs");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $orderIndex = ($result['max_order'] ?? 0) + 1;
        
        // Tạo tab mới
        $stmt = $conn->prepare("
            INSERT INTO tabs (title, module, content, order_index, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['title'],
            $data['module'],
            $data['content'] ?? '',
            $orderIndex,
            $_SESSION['user_id']
        ]);
        
        $tabId = $conn->lastInsertId();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Tab created successfully',
            'data' => [
                'id' => $tabId,
                'title' => $data['title'],
                'module' => $data['module'],
                'order_index' => $orderIndex
            ]
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

// Xóa tab
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['tab_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing tab_id parameter']);
        exit;
    }
    
    try {
        // Kiểm tra quyền xóa
        $stmt = $conn->prepare("SELECT module FROM tabs WHERE id = ?");
        $stmt->execute([$data['tab_id']]);
        $tab = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$tab) {
            http_response_code(404);
            echo json_encode(['error' => 'Tab not found']);
            exit;
        }
        
        if (!hasPermission($tab['module'], 'delete')) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            exit;
        }
        
        // Xóa tab
        $stmt = $conn->prepare("DELETE FROM tabs WHERE id = ?");
        $stmt->execute([$data['tab_id']]);
        
        echo json_encode(['success' => true, 'message' => 'Tab deleted successfully']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} 