<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

// Kiểm tra xác thực
if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    // Lấy dữ liệu từ database
    $db = getDBConnection();
    
    // Lấy dữ liệu đánh giá hiệu suất
    $stmt = $db->prepare("
        SELECT 
            COUNT(CASE WHEN rating >= 4 THEN 1 END) as positive,
            COUNT(CASE WHEN rating <= 2 THEN 1 END) as negative,
            COUNT(CASE WHEN rating = 3 THEN 1 END) as neutral
        FROM performance_reviews
        WHERE review_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    ");
    $stmt->execute();
    $sentimentData = $stmt->fetch(PDO::FETCH_ASSOC);

    // Lấy dữ liệu phản hồi nhân viên
    $stmt = $db->prepare("
        SELECT 
            COUNT(CASE WHEN sentiment = 'positive' THEN 1 END) as feedback_positive,
            COUNT(CASE WHEN sentiment = 'negative' THEN 1 END) as feedback_negative,
            COUNT(CASE WHEN sentiment = 'neutral' THEN 1 END) as feedback_neutral
        FROM employee_feedback
        WHERE feedback_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    ");
    $stmt->execute();
    $feedbackData = $stmt->fetch(PDO::FETCH_ASSOC);

    // Tính toán tổng hợp
    $totalPositive = $sentimentData['positive'] + $feedbackData['feedback_positive'];
    $totalNegative = $sentimentData['negative'] + $feedbackData['feedback_negative'];
    $totalNeutral = $sentimentData['neutral'] + $feedbackData['feedback_neutral'];
    $total = $totalPositive + $totalNegative + $totalNeutral;

    // Tính tỷ lệ phần trăm
    $positivePercent = round(($totalPositive / $total) * 100);
    $negativePercent = round(($totalNegative / $total) * 100);
    $neutralPercent = round(($totalNeutral / $total) * 100);

    // Tạo insights
    $insights = [];
    
    if ($positivePercent > 60) {
        $insights[] = [
            'type' => 'positive',
            'text' => 'Tâm lý nhân viên đang rất tích cực, cần duy trì và phát huy'
        ];
    } elseif ($negativePercent > 40) {
        $insights[] = [
            'type' => 'warning',
            'text' => 'Cần chú ý đến tâm lý tiêu cực của nhân viên'
        ];
    }

    if ($neutralPercent > 30) {
        $insights[] = [
            'type' => 'info',
            'text' => 'Cần tăng cường tương tác với nhân viên có tâm lý trung tính'
        ];
    }

    // Trả về kết quả
    echo json_encode([
        'positive' => $positivePercent,
        'negative' => $negativePercent,
        'neutral' => $neutralPercent,
        'total' => $total,
        'insights' => $insights
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
} 