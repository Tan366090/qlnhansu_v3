<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../../../../config/database.php';

class GamificationAPI {
    private $conn;
    private $requestMethod;
    private $endpoint;

    public function __construct($requestMethod, $endpoint) {
        $this->conn = Database::getInstance()->getConnection();
        $this->requestMethod = $requestMethod;
        $this->endpoint = $endpoint;
    }

    public function processRequest() {
        switch ($this->requestMethod) {
            case 'GET':
                switch ($this->endpoint) {
                    case 'leaderboard':
                        $response = $this->getLeaderboard();
                        break;
                    case 'progress':
                        $response = $this->getProgress();
                        break;
                    default:
                        $response = $this->notFoundResponse();
                        break;
                }
                break;
            default:
                $response = $this->notFoundResponse();
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function getLeaderboard() {
        try {
            // Lấy dữ liệu bảng xếp hạng
            $query = "SELECT 
                        u.id,
                        u.username,
                        u.full_name,
                        d.name as department_name,
                        COALESCE(SUM(p.points), 0) as total_points,
                        COUNT(DISTINCT p.id) as achievements_count
                    FROM users u
                    LEFT JOIN departments d ON u.department_id = d.id
                    LEFT JOIN performance p ON u.id = p.employee_id
                    GROUP BY u.id, u.username, u.full_name, d.name
                    ORDER BY total_points DESC
                    LIMIT 10";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'status_code_header' => 'HTTP/1.1 200 OK',
                'body' => json_encode([
                    'success' => true,
                    'data' => $result
                ])
            ];
        } catch (PDOException $e) {
            return [
                'status_code_header' => 'HTTP/1.1 500 Internal Server Error',
                'body' => json_encode([
                    'success' => false,
                    'message' => 'Database error: ' . $e->getMessage()
                ])
            ];
        }
    }

    private function getProgress() {
        try {
            // Lấy dữ liệu tiến độ
            $query = "SELECT 
                        u.id,
                        u.username,
                        u.full_name,
                        COALESCE(SUM(CASE WHEN p.status = 'completed' THEN 1 ELSE 0 END), 0) as completed_tasks,
                        COALESCE(COUNT(p.id), 0) as total_tasks,
                        COALESCE(SUM(p.points), 0) as total_points
                    FROM users u
                    LEFT JOIN performance p ON u.id = p.employee_id
                    GROUP BY u.id, u.username, u.full_name";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'status_code_header' => 'HTTP/1.1 200 OK',
                'body' => json_encode([
                    'success' => true,
                    'data' => $result
                ])
            ];
        } catch (PDOException $e) {
            return [
                'status_code_header' => 'HTTP/1.1 500 Internal Server Error',
                'body' => json_encode([
                    'success' => false,
                    'message' => 'Database error: ' . $e->getMessage()
                ])
            ];
        }
    }

    private function notFoundResponse() {
        return [
            'status_code_header' => 'HTTP/1.1 404 Not Found',
            'body' => json_encode([
                'success' => false,
                'message' => 'Not Found'
            ])
        ];
    }
}

// Get request method and endpoint
$requestMethod = $_SERVER['REQUEST_METHOD'];
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : '';

// Create and process the API
$api = new GamificationAPI($requestMethod, $endpoint);
$api->processRequest();
?> 