<?php
// Thiết lập header để trả về JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

try {
    // Dữ liệu mẫu cho menu gần đây
    $recentItems = [
        [
            'title' => 'Dashboard',
            'url' => 'dashboard.html',
            'icon' => 'fas fa-home'
        ],
        [
            'title' => 'Danh sách nhân viên',
            'url' => 'employees/list.html',
            'icon' => 'fas fa-users'
        ],
        [
            'title' => 'Chấm công',
            'url' => 'attendance/check.html',
            'icon' => 'fas fa-clock'
        ],
        [
            'title' => 'Lương',
            'url' => 'salary/list.html',
            'icon' => 'fas fa-money-bill-wave'
        ],
        [
            'title' => 'Nghỉ phép',
            'url' => 'leave/register.html',
            'icon' => 'fas fa-calendar-alt'
        ]
    ];

    // Trả về dữ liệu dưới dạng JSON
    echo json_encode($recentItems);
} catch (Exception $e) {
    // Xử lý lỗi nếu có
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
} 