<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
header('Access-Control-Allow-Origin: *');

// Đường dẫn đến file chat_data.txt
$chatDataFile = __DIR__ . '/chat_data.txt';

// Hàm để đọc và trả về dữ liệu từ file
function getChatData() {
    global $chatDataFile;
    if (file_exists($chatDataFile)) {
        $data = file_get_contents($chatDataFile);
        return $data;
    }
    return json_encode(['error' => 'File not found']);
}

// Trả về dữ liệu
echo getChatData();
?> 