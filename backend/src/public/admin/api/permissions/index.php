<?php
header('Content-Type: application/json');
require_once '../../../../config/Database.php';
require_once '../../../../middleware/AuthMiddleware.php';

$auth = new AuthMiddleware();
$db = Database::getInstance();
$conn = $db->getConnection();

// Kiểm tra quyền admin
if (!$auth->isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$request = $_REQUEST;

switch ($method) {
    case 'GET':
        // Lấy danh sách phân quyền
        if (isset($request['id'])) {
            $stmt = $conn->prepare("SELECT * FROM permissions WHERE id = ?");
            $stmt->execute([$request['id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $stmt = $conn->query("SELECT * FROM permissions");
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        echo json_encode($result);
        break;

    case 'POST':
        // Thêm phân quyền mới
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $conn->prepare("INSERT INTO permissions (name, description, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$data['name'], $data['description']]);
        echo json_encode(['success' => true, 'id' => $conn->lastInsertId()]);
        break;

    case 'PUT':
        // Cập nhật phân quyền
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $conn->prepare("UPDATE permissions SET name = ?, description = ? WHERE id = ?");
        $stmt->execute([$data['name'], $data['description'], $request['id']]);
        echo json_encode(['success' => true]);
        break;

    case 'DELETE':
        // Xóa phân quyền
        $stmt = $conn->prepare("DELETE FROM permissions WHERE id = ?");
        $stmt->execute([$request['id']]);
        echo json_encode(['success' => true]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
} 