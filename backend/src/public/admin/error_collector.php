<?php
// Bật báo cáo lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Thiết lập header JSON
header('Content-Type: application/json');

// Hàm kiểm tra lỗi JavaScript
function checkJavaScriptErrors() {
    $errors = [];
    
    // Kiểm tra các file JavaScript
    $jsFiles = [
        '../js/utils/ui_utils.js',
        '../js/dashboard.js',
        '../js/modules/dashboard_features.js',
        '../js/modules/recent-menu.js',
        '../js/modules/export-data.js',
        '../js/modules/dark-mode.js',
        '../js/main.js',
        '../js/modules/loading-overlay.js',
        '../js/modules/notification-handler.js',
        '../js/modules/gamification.js',
        '../js/modules/activity-filter.js',
        '../js/modules/mobile-stats.js',
        '../js/modules/ai-analysis.js'
    ];

    foreach ($jsFiles as $file) {
        if (!file_exists($file)) {
            $errors[] = [
                'type' => 'JavaScript',
                'message' => "File không tồn tại: $file",
                'severity' => 'high'
            ];
        } else {
            // Kiểm tra cú pháp JavaScript
            $content = file_get_contents($file);
            if (strpos($content, 'SyntaxError') !== false) {
                $errors[] = [
                    'type' => 'JavaScript',
                    'message' => "Lỗi cú pháp trong file: $file",
                    'severity' => 'high'
                ];
            }
        }
    }

    return $errors;
}

// Hàm kiểm tra lỗi API
function checkAPIErrors() {
    $errors = [];
    
    // Danh sách các endpoint API cần kiểm tra
    $endpoints = [
        '/api/employees',
        '/api/departments',
        '/api/positions',
        '/api/performances',
        '/api/payroll',
        '/api/leaves',
        '/api/trainings',
        '/api/tasks',
        '/api/mobile-stats'
    ];

    foreach ($endpoints as $endpoint) {
        $url = "http://localhost/qlnhansu_V3/backend/src/public$endpoint";
        $response = @file_get_contents($url);
        
        if ($response === false) {
            $errors[] = [
                'type' => 'API',
                'message' => "Không thể kết nối đến API: $endpoint",
                'severity' => 'high'
            ];
        } else {
            // Kiểm tra response có phải là JSON không
            json_decode($response);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $errors[] = [
                    'type' => 'API',
                    'message' => "API trả về dữ liệu không phải JSON: $endpoint",
                    'severity' => 'high'
                ];
            }
        }
    }

    return $errors;
}

// Hàm kiểm tra lỗi tài nguyên
function checkResourceErrors() {
    $errors = [];
    
    // Kiểm tra các file CSS
    $cssFiles = [
        '../css/fontawesome/all.min.css',
        'css/vendor/bootstrap.min.css',
        'css/vendor/google-fonts.css',
        'css/admin-dashboard.css'
    ];

    foreach ($cssFiles as $file) {
        if (!file_exists($file)) {
            $errors[] = [
                'type' => 'Resource',
                'message' => "File CSS không tồn tại: $file",
                'severity' => 'medium'
            ];
        }
    }

    // Kiểm tra các file hình ảnh
    $imageFiles = [
        './male.png'
    ];

    foreach ($imageFiles as $file) {
        if (!file_exists($file)) {
            $errors[] = [
                'type' => 'Resource',
                'message' => "File hình ảnh không tồn tại: $file",
                'severity' => 'medium'
            ];
        }
    }

    return $errors;
}

// Hàm kiểm tra lỗi cấu trúc HTML
function checkHTMLErrors() {
    $errors = [];
    
    $htmlFile = 'dashboard_admin_V1.php';
    $content = file_get_contents($htmlFile);
    
    // Kiểm tra các thẻ HTML cần thiết
    $requiredTags = [
        '<!DOCTYPE html>',
        '<html',
        '<head>',
        '<body>',
        '<meta charset="UTF-8">',
        '<title>'
    ];

    foreach ($requiredTags as $tag) {
        if (strpos($content, $tag) === false) {
            $errors[] = [
                'type' => 'HTML',
                'message' => "Thiếu thẻ HTML bắt buộc: $tag",
                'severity' => 'high'
            ];
        }
    }

    return $errors;
}

// Hàm kiểm tra lỗi kết nối database
function checkDatabaseErrors() {
    $errors = [];
    
    try {
        $db = new PDO('mysql:host=localhost;dbname=qlnhansu', 'root', '');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Kiểm tra các bảng cần thiết
        $requiredTables = [
            'employees',
            'departments',
            'positions',
            'performances',
            'payroll',
            'leaves',
            'trainings',
            'tasks'
        ];

        foreach ($requiredTables as $table) {
            $stmt = $db->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() == 0) {
                $errors[] = [
                    'type' => 'Database',
                    'message' => "Bảng không tồn tại: $table",
                    'severity' => 'high'
                ];
            }
        }
    } catch (PDOException $e) {
        $errors[] = [
            'type' => 'Database',
            'message' => "Lỗi kết nối database: " . $e->getMessage(),
            'severity' => 'critical'
        ];
    }

    return $errors;
}

// Thu thập tất cả các lỗi
$allErrors = [
    'javascript' => checkJavaScriptErrors(),
    'api' => checkAPIErrors(),
    'resource' => checkResourceErrors(),
    'html' => checkHTMLErrors(),
    'database' => checkDatabaseErrors()
];

// Tính tổng số lỗi
$totalErrors = 0;
foreach ($allErrors as $category => $errors) {
    $totalErrors += count($errors);
}

// Tạo response
$response = [
    'status' => $totalErrors > 0 ? 'error' : 'success',
    'total_errors' => $totalErrors,
    'errors' => $allErrors,
    'timestamp' => date('Y-m-d H:i:s')
];

// Hiển thị kết quả
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?> 