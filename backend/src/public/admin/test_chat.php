<?php
// Bật hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kiểm tra file headers.php
if (!file_exists('headers.php')) {
    die("Lỗi: Không tìm thấy file headers.php");
}

include 'headers.php';

// Kiểm tra kết nối database
function testDatabaseConnection() {
    try {
        $conn = new mysqli("localhost", "root", "", "qlnhansu");
        if ($conn->connect_error) {
            return [
                'status' => 'error',
                'message' => "Kết nối database thất bại: " . $conn->connect_error
            ];
        }
        return [
            'status' => 'success',
            'message' => "Kết nối database thành công"
        ];
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => "Lỗi kết nối database: " . $e->getMessage()
        ];
    }
}

// Kiểm tra các bảng cần thiết
function testRequiredTables() {
    try {
        $conn = new mysqli("localhost", "root", "", "qlnhansu");
        $required_tables = ['nhanvien', 'phongban', 'nghiphep'];
        $missing_tables = [];
        
        foreach ($required_tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result->num_rows == 0) {
                $missing_tables[] = $table;
            }
        }
        
        if (empty($missing_tables)) {
            return [
                'status' => 'success',
                'message' => "Tất cả các bảng cần thiết đã tồn tại"
            ];
        }
        
        return [
            'status' => 'error',
            'message' => "Thiếu các bảng: " . implode(', ', $missing_tables)
        ];
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => "Lỗi kiểm tra bảng: " . $e->getMessage()
        ];
    }
}

// Kiểm tra xử lý tin nhắn
function testMessageProcessing() {
    try {
        $test_messages = [
            'xin chào' => 'greeting',
            'tổng số nhân viên' => 'employee_count',
            'thông tin phòng ban' => 'department_info',
            'thống kê lương' => 'salary_stats',
            'nghỉ phép tháng này' => 'leave_stats'
        ];
        
        $results = [];
        foreach ($test_messages as $message => $type) {
            try {
                $conn = new mysqli("localhost", "root", "", "qlnhansu");
                ob_start();
                $_POST['message'] = $message; // Thêm message vào POST
                include 'process_chat.php';
                $response = ob_get_clean();
                
                $results[$type] = [
                    'message' => $message,
                    'response' => $response,
                    'status' => !empty($response) ? 'success' : 'error'
                ];
            } catch (Exception $e) {
                $results[$type] = [
                    'message' => $message,
                    'response' => "Lỗi: " . $e->getMessage(),
                    'status' => 'error'
                ];
            }
        }
        
        return $results;
    } catch (Exception $e) {
        return [
            'error' => [
                'message' => 'Lỗi chung',
                'response' => $e->getMessage(),
                'status' => 'error'
            ]
        ];
    }
}

// Thực hiện các test
try {
    $tests = [
        'database_connection' => testDatabaseConnection(),
        'required_tables' => testRequiredTables(),
        'message_processing' => testMessageProcessing()
    ];
} catch (Exception $e) {
    die("Lỗi khi thực hiện test: " . $e->getMessage());
}

// Hiển thị kết quả
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Chat Box - QLNS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <style>
        .test-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        .test-section {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .test-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .test-status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
        }
        .status-success {
            background: #d4edda;
            color: #155724;
        }
        .status-error {
            background: #f8d7da;
            color: #721c24;
        }
        .test-details {
            margin-top: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .message-test {
            margin: 5px 0;
            padding: 5px;
            border-left: 3px solid #007bff;
        }
        .error-message {
            color: #dc3545;
            background: #f8d7da;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h2 class="mb-4">Kiểm tra Chat Box</h2>
        
        <!-- Test kết nối database -->
        <div class="test-section">
            <div class="test-header">
                <h5 class="mb-0">Kết nối Database</h5>
                <span class="test-status <?php echo $tests['database_connection']['status'] === 'success' ? 'status-success' : 'status-error'; ?>">
                    <?php echo $tests['database_connection']['status'] === 'success' ? 'Thành công' : 'Lỗi'; ?>
                </span>
            </div>
            <div class="test-details">
                <?php echo $tests['database_connection']['message']; ?>
            </div>
        </div>
        
        <!-- Test bảng dữ liệu -->
        <div class="test-section">
            <div class="test-header">
                <h5 class="mb-0">Kiểm tra Bảng Dữ liệu</h5>
                <span class="test-status <?php echo $tests['required_tables']['status'] === 'success' ? 'status-success' : 'status-error'; ?>">
                    <?php echo $tests['required_tables']['status'] === 'success' ? 'Thành công' : 'Lỗi'; ?>
                </span>
            </div>
            <div class="test-details">
                <?php echo $tests['required_tables']['message']; ?>
            </div>
        </div>
        
        <!-- Test xử lý tin nhắn -->
        <div class="test-section">
            <div class="test-header">
                <h5 class="mb-0">Kiểm tra Xử lý Tin nhắn</h5>
            </div>
            <div class="test-details">
                <?php foreach ($tests['message_processing'] as $type => $result): ?>
                    <div class="message-test">
                        <strong>Loại tin nhắn:</strong> <?php echo $type; ?><br>
                        <strong>Tin nhắn test:</strong> "<?php echo $result['message']; ?>"<br>
                        <strong>Trạng thái:</strong> 
                        <span class="test-status <?php echo $result['status'] === 'success' ? 'status-success' : 'status-error'; ?>">
                            <?php echo $result['status'] === 'success' ? 'Thành công' : 'Lỗi'; ?>
                        </span><br>
                        <strong>Phản hồi:</strong><br>
                        <pre><?php echo htmlspecialchars($result['response']); ?></pre>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Nút chạy lại test -->
        <div class="text-center mt-4">
            <a href="test_chat.php" class="btn btn-primary">
                <i class="fas fa-sync-alt"></i> Chạy lại test
            </a>
            <a href="chat_box.php" class="btn btn-secondary ms-2">
                <i class="fas fa-comments"></i> Quay lại Chat Box
            </a>
        </div>
    </div>
</body>
</html> 