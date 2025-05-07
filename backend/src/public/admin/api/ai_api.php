<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../../../../config/database.php';

class AIAnalysisAPI {
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
                    case 'hr-trends':
                        $response = $this->getHRTrends();
                        break;
                    case 'sentiment':
                        $response = $this->getSentimentAnalysis();
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

    private function getHRTrends() {
        try {
            // Lấy dữ liệu xu hướng nhân sự
            $query = "SELECT 
                        DATE_FORMAT(created_at, '%Y-%m') as month,
                        COUNT(*) as total_employees,
                        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_employees,
                        SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_employees
                    FROM users 
                    GROUP BY month 
                    ORDER BY month DESC 
                    LIMIT 12";
            
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

    private function getSentimentAnalysis() {
        try {
            // Lấy dữ liệu phân tích tâm lý
            $query = "SELECT 
                        e.employee_id,
                        u.username,
                        e.sentiment_score,
                        e.feedback,
                        e.created_at
                    FROM employee_sentiment e
                    JOIN users u ON e.employee_id = u.id
                    ORDER BY e.created_at DESC
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
$api = new AIAnalysisAPI($requestMethod, $endpoint);
$api->processRequest();
?> 