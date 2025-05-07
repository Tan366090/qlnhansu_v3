<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../config/database.php';

// Kết nối database
$database = new Database();
$db = $database->getConnection();

// Xử lý request
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        try {
            // Lấy danh sách recent items từ database
            $query = "SELECT * FROM recent_items ORDER BY timestamp DESC LIMIT 10";
            $stmt = $db->prepare($query);
            $stmt->execute();
            
            $recent_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Nếu không có dữ liệu, trả về dữ liệu mặc định
            if (empty($recent_items)) {
                $recent_items = [
                    [
                        'id' => 1,
                        'title' => 'Dashboard',
                        'url' => 'dashboard.html',
                        'timestamp' => date('Y-m-d H:i:s')
                    ],
                    [
                        'id' => 2,
                        'title' => 'Danh sách nhân viên',
                        'url' => 'employees/list.html',
                        'timestamp' => date('Y-m-d H:i:s')
                    ],
                    [
                        'id' => 3,
                        'title' => 'Chấm công',
                        'url' => 'attendance/check.html',
                        'timestamp' => date('Y-m-d H:i:s')
                    ]
                ];
            }
            
            echo json_encode($recent_items);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
        break;
        
    case 'POST':
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['title']) || !isset($data['url'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }
            
            $query = "INSERT INTO recent_items (title, url, timestamp) VALUES (:title, :url, NOW())";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':title', $data['title']);
            $stmt->bindParam(':url', $data['url']);
            $stmt->execute();
            
            echo json_encode(['message' => 'Recent item added successfully']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?> 