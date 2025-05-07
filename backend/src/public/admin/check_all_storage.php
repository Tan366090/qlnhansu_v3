<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Kết nối trực tiếp đến database
    $pdo = new PDO(
        "mysql:host=localhost;dbname=qlnhansu;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // Lấy danh sách tất cả các bảng
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    $result = [];
    foreach ($tables as $table) {
        // Đếm số lượng bản ghi
        $count = $pdo->query("SELECT COUNT(*) as count FROM `$table`")->fetch()['count'];
        
        // Lấy 5 bản ghi đầu tiên
        $sample = $pdo->query("SELECT * FROM `$table` LIMIT 5")->fetchAll();
        
        $result[$table] = [
            'total_records' => $count,
            'sample_data' => $sample
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $result
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 