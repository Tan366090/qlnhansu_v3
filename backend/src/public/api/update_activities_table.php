<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once '../../config/database.php';
    
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Kiểm tra đường dẫn file SQL
    $sqlFile = '../../sql/update_activities_table.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("Không tìm thấy file SQL: " . $sqlFile);
    }

    // Đọc file SQL
    $sql = file_get_contents($sqlFile);
    if ($sql === false) {
        throw new Exception("Không thể đọc file SQL");
    }
    
    // Thực thi từng câu lệnh SQL riêng biệt
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $result = $conn->exec($statement);
            if ($result === false) {
                $error = $conn->errorInfo();
                throw new Exception("Lỗi SQL: " . $error[2] . "\nCâu lệnh: " . $statement);
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Cập nhật cấu trúc bảng activities thành công'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
} 