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

// Cập nhật thứ tự tab
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['tabs']) || !is_array($data['tabs'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request data']);
        exit;
    }
    
    try {
        $conn->beginTransaction();
        
        foreach ($data['tabs'] as $index => $tab) {
            if (!isset($tab['id']) || !isset($tab['order_index'])) {
                $conn->rollBack();
                http_response_code(400);
                echo json_encode(['error' => 'Invalid tab data']);
                exit;
            }
            
            // Kiểm tra quyền cập nhật
            $stmt = $conn->prepare("SELECT module FROM tabs WHERE id = ?");
            $stmt->execute([$tab['id']]);
            $tabData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$tabData) {
                $conn->rollBack();
                http_response_code(404);
                echo json_encode(['error' => 'Tab not found']);
                exit;
            }
            
            if (!hasPermission($tabData['module'], 'update')) {
                $conn->rollBack();
                http_response_code(403);
                echo json_encode(['error' => 'Access denied']);
                exit;
            }
            
            // Cập nhật thứ tự
            $stmt = $conn->prepare("UPDATE tabs SET order_index = ? WHERE id = ?");
            $stmt->execute([$tab['order_index'], $tab['id']]);
        }
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Tab order updated successfully']);
    } catch (PDOException $e) {
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} 