<?php
require_once 'backend/src/public/admin/chat_engine.php';

// Test kết nối database
try {
    $chatEngine = new ChatEngine();
    echo "Kết nối database thành công!\n";
    
    // Test một số câu hỏi cơ bản
    $testQueries = [
        "Có bao nhiêu nhân viên?",
        "Danh sách phòng ban",
        "Thông tin về nhân viên",
        "Lương trung bình của nhân viên"
    ];
    
    foreach ($testQueries as $query) {
        echo "\nTest query: " . $query . "\n";
        $result = $chatEngine->processQuery($query);
        echo "Response: " . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }
    
} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage() . "\n";
}
?> 