<?php
require_once 'OpenAIHelper.php';
class ResponseGenerator {
    public function generate($intent, $data, $query = '') {
        switch ($intent) {
            case 'salary':
                // Ví dụ trả về dữ liệu biểu đồ
                return json_encode([
                    'type' => 'chart',
                    'title' => 'Biểu đồ lương',
                    'data' => array_column($data, 'salary'),
                    'labels' => array_column($data, 'name'),
                    'image_url' => '/charts/salary_chart.png'
                ]);
            case 'department':
                $resp = "Các phòng ban:\n";
                foreach ($data as $row) {
                    $resp .= "- {$row['department']}\n";
                }
                return $resp;
            case 'leave':
                $resp = "Thông tin nghỉ phép:\n";
                foreach ($data as $row) {
                    $resp .= "- {$row['name']}: {$row['leave_days']} ngày\n";
                }
                return $resp;
            case 'training':
                $resp = "Các khóa đào tạo:\n";
                foreach ($data as $row) {
                    $resp .= "- {$row['course']}\n";
                }
                return $resp;
            case 'general':
                // Gọi OpenAI API để sinh câu trả lời tự nhiên
                return OpenAIHelper::ask($query);
            default:
                return $data[0]['message'] ?? 'Không có dữ liệu.';
        }
    }
} 