<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function sendJsonResponse($data, $statusCode = 200) {
    // Set headers
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    
    // Set status code
    http_response_code($statusCode);
    
    // Send JSON response
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Log the request
    error_log("AI Analysis API Request: " . $_SERVER['REQUEST_METHOD'] . " " . $_SERVER['REQUEST_URI']);
    
    require_once __DIR__ . '/../../../config/database.php';

    // Connect to database
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        error_log("Database connection failed");
        sendJsonResponse([
            'success' => false,
            'message' => 'Database connection failed'
        ], 500);
    }

    // Process request
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            try {
                // Get HR trends data
                $query = "SELECT 
                            DATE_FORMAT(hire_date, '%Y-%m') as month,
                            COUNT(*) as total_employees
                        FROM employees 
                        WHERE hire_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                        GROUP BY DATE_FORMAT(hire_date, '%Y-%m')
                        ORDER BY month ASC";
                
                $stmt = $db->prepare($query);
                $stmt->execute();
                $hrTrends = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Get sentiment data
                $query = "SELECT sentiment_score FROM employee_sentiment WHERE analysis_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $scores = $stmt->fetchAll(PDO::FETCH_COLUMN);

                $total = count($scores);
                $positive = 0;
                $neutral = 0;
                $negative = 0;
                foreach ($scores as $score) {
                    if ($score > 0.7) $positive++;
                    elseif ($score >= 0.4) $neutral++;
                    else $negative++;
                }

                $sentimentData = [
                    'positive' => $total > 0 ? round($positive / $total * 100, 2) : 0,
                    'neutral' => $total > 0 ? round($neutral / $total * 100, 2) : 0,
                    'negative' => $total > 0 ? round($negative / $total * 100, 2) : 0
                ];

                // Generate insights
                $insights = [
                    'hrTrends' => [],
                    'sentiment' => []
                ];

                if (count($hrTrends) > 1) {
                    $first = $hrTrends[0]['total_employees'];
                    $last = $hrTrends[count($hrTrends) - 1]['total_employees'];
                    $growthRate = $first > 0 ? (($last - $first) / $first) * 100 : 0;
                    $insights['hrTrends'][] = sprintf(
                        "Tổng số nhân viên %s %.2f%% trong %d tháng qua",
                        $growthRate > 0 ? 'tăng' : 'giảm',
                        abs($growthRate),
                        count($hrTrends)
                    );
                }

                if ($total > 0) {
                    $insights['sentiment'][] = sprintf("%.2f%% nhân viên có tâm lý tích cực", $sentimentData['positive']);
                    $insights['sentiment'][] = sprintf("%.2f%% nhân viên có tâm lý trung lập", $sentimentData['neutral']);
                    $insights['sentiment'][] = sprintf("%.2f%% nhân viên cần được hỗ trợ tâm lý", $sentimentData['negative']);
                }

                $data = [
                    'success' => true,
                    'data' => [
                        'hrTrends' => [
                            'labels' => array_column($hrTrends, 'month'),
                            'values' => array_column($hrTrends, 'total_employees')
                        ],
                        'sentiment' => $sentimentData,
                        'insights' => $insights
                    ]
                ];
                
                sendJsonResponse($data);
            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
                sendJsonResponse([
                    'success' => false,
                    'message' => 'Database error',
                    'error' => $e->getMessage()
                ], 500);
            } catch (Exception $e) {
                error_log("Server error: " . $e->getMessage());
                sendJsonResponse([
                    'success' => false,
                    'message' => 'Server error',
                    'error' => $e->getMessage()
                ], 500);
            }
            break;
            
        case 'POST':
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                
                if (!isset($data['employee_id']) || !isset($data['sentiment_score']) || !isset($data['analysis_date'])) {
                    throw new Exception('Missing required fields');
                }

                $query = "INSERT INTO employee_sentiment (employee_id, sentiment_score, analysis_date, created_at, updated_at) VALUES (:employee_id, :sentiment_score, :analysis_date, NOW(), NOW())";
                
                $stmt = $db->prepare($query);
                $stmt->bindParam(':employee_id', $data['employee_id']);
                $stmt->bindParam(':sentiment_score', $data['sentiment_score']);
                $stmt->bindParam(':analysis_date', $data['analysis_date']);
                $stmt->execute();

                sendJsonResponse([
                    'success' => true,
                    'message' => 'Sentiment data added successfully'
                ]);
            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
                sendJsonResponse([
                    'success' => false,
                    'message' => 'Database error',
                    'error' => $e->getMessage()
                ], 500);
            } catch (Exception $e) {
                error_log("Server error: " . $e->getMessage());
                sendJsonResponse([
                    'success' => false,
                    'message' => 'Server error',
                    'error' => $e->getMessage()
                ], 500);
            }
            break;
            
        default:
            sendJsonResponse([
                'success' => false,
                'message' => 'Method not allowed'
            ], 405);
            break;
    }
} catch (Exception $e) {
    error_log("Fatal error: " . $e->getMessage());
    sendJsonResponse([
        'success' => false,
        'message' => 'Server error',
        'error' => $e->getMessage()
    ], 500);
}
?> 