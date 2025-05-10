<?php
class DataRetriever {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }
    public function getData($intent, $tokens) {
        // Tạm thời trả về dữ liệu mẫu, sau này tối ưu hóa truy vấn
        switch ($intent) {
            case 'salary':
                return [['name' => 'Nguyen Van A', 'salary' => 1000], ['name' => 'Tran Thi B', 'salary' => 1200]];
            case 'department':
                return [['department' => 'IT'], ['department' => 'HR']];
            case 'leave':
                return [['name' => 'Nguyen Van A', 'leave_days' => 2]];
            case 'training':
                return [['course' => 'Kỹ năng mềm']];
            default:
                return [['message' => 'Không tìm thấy dữ liệu phù hợp']];
        }
    }
} 