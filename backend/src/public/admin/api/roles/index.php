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
        // Lấy danh sách vai trò
        if (isset($request['id'])) {
            $stmt = $conn->prepare("SELECT * FROM roles WHERE id = ?");
            $stmt->execute([$request['id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $stmt = $conn->query("SELECT * FROM roles");
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        echo json_encode($result);
        break;

    case 'POST':
        // Thêm vai trò mới
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $conn->prepare("INSERT INTO roles (name, description, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$data['name'], $data['description']]);
        echo json_encode(['success' => true, 'id' => $conn->lastInsertId()]);
        break;

    case 'PUT':
        // Cập nhật vai trò
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $conn->prepare("UPDATE roles SET name = ?, description = ? WHERE id = ?");
        $stmt->execute([$data['name'], $data['description'], $request['id']]);
        echo json_encode(['success' => true]);
        break;

    case 'DELETE':
        // Xóa vai trò
        $stmt = $conn->prepare("DELETE FROM roles WHERE id = ?");
        $stmt->execute([$request['id']]);
        echo json_encode(['success' => true]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?> 