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
    $db = getDBConnection();
    
    // Lấy dữ liệu tuyển dụng
    $stmt = $db->prepare("
        SELECT 
            DATE_FORMAT(hire_date, '%Y-%m') as month,
            COUNT(*) as count
        FROM employees
        WHERE hire_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(hire_date, '%Y-%m')
        ORDER BY month
    ");
    $stmt->execute();
    $hiringData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Lấy dữ liệu nghỉ việc
    $stmt = $db->prepare("
        SELECT 
            DATE_FORMAT(resignation_date, '%Y-%m') as month,
            COUNT(*) as count
        FROM employee_resignations
        WHERE resignation_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(resignation_date, '%Y-%m')
        ORDER BY month
    ");
    $stmt->execute();
    $attritionData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Lấy dữ liệu hiệu suất trung bình
    $stmt = $db->prepare("
        SELECT 
            DATE_FORMAT(review_date, '%Y-%m') as month,
            AVG(rating) as avg_performance
        FROM performance_reviews
        WHERE review_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(review_date, '%Y-%m')
        ORDER BY month
    ");
    $stmt->execute();
    $performanceData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Chuẩn bị dữ liệu cho biểu đồ
    $labels = [];
    $hiring = [];
    $attrition = [];
    $performance = [];

    // Tạo mảng tháng
    for ($i = 11; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        $labels[] = $month;
        
        // Tìm dữ liệu cho tháng này
        $hiringCount = 0;
        $attritionCount = 0;
        $performanceScore = 0;

        foreach ($hiringData as $data) {
            if ($data['month'] === $month) {
                $hiringCount = $data['count'];
                break;
            }
        }

        foreach ($attritionData as $data) {
            if ($data['month'] === $month) {
                $attritionCount = $data['count'];
                break;
            }
        }

        foreach ($performanceData as $data) {
            if ($data['month'] === $month) {
                $performanceScore = $data['avg_performance'];
                break;
            }
        }

        $hiring[] = $hiringCount;
        $attrition[] = $attritionCount;
        $performance[] = $performanceScore;
    }

    // Tạo insights
    $insights = [];
    
    // Phân tích xu hướng tuyển dụng
    $hiringTrend = calculateTrend($hiring);
    if ($hiringTrend > 0.1) {
        $insights[] = [
            'type' => 'positive',
            'text' => 'Tốc độ tuyển dụng đang tăng trưởng tích cực'
        ];
    } elseif ($hiringTrend < -0.1) {
        $insights[] = [
            'type' => 'warning',
            'text' => 'Tốc độ tuyển dụng đang giảm, cần xem xét nguyên nhân'
        ];
    }

    // Phân tích tỷ lệ nghỉ việc
    $attritionRate = array_sum($attrition) / array_sum($hiring);
    if ($attritionRate > 0.2) {
        $insights[] = [
            'type' => 'warning',
            'text' => 'Tỷ lệ nghỉ việc cao, cần cải thiện môi trường làm việc'
        ];
    }

    // Phân tích hiệu suất
    $performanceTrend = calculateTrend($performance);
    if ($performanceTrend > 0.1) {
        $insights[] = [
            'type' => 'positive',
            'text' => 'Hiệu suất làm việc đang được cải thiện'
        ];
    }

    // Trả về kết quả
    echo json_encode([
        'labels' => $labels,
        'hiring' => $hiring,
        'attrition' => $attrition,
        'performance' => $performance,
        'insights' => $insights
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

// Hàm tính xu hướng
function calculateTrend($data) {
    if (count($data) < 2) return 0;
    
    $sumX = 0;
    $sumY = 0;
    $sumXY = 0;
    $sumX2 = 0;
    
    $n = count($data);
    for ($i = 0; $i < $n; $i++) {
        $sumX += $i;
        $sumY += $data[$i];
        $sumXY += $i * $data[$i];
        $sumX2 += $i * $i;
    }
    
    $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
    return $slope;
} 