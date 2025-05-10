<?php
include 'headers.php';

// Kết nối database
$conn = new mysqli("localhost", "root", "", "qlnhansu");

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Nhận tin nhắn từ người dùng
$message = $_POST['message'] ?? '';

// Hàm xử lý ngôn ngữ tự nhiên
if (!function_exists('processNaturalLanguage')) {
    function processNaturalLanguage($message) {
        $message = trim(mb_strtolower($message, 'UTF-8'));
        
        // Loại bỏ dấu câu và ký tự đặc biệt
        $message = preg_replace('/[^\p{L}\p{N}\s]/u', '', $message);
        
        // Tách từ khóa
        $keywords = explode(' ', $message);
        
        return [
            'original' => $message,
            'keywords' => $keywords,
            'contains' => function($word) use ($message) {
                return strpos($message, $word) !== false;
            }
        ];
    }
}

// Hàm tạo bảng HTML từ kết quả SQL
if (!function_exists('createTableFromSQL')) {
    function createTableFromSQL($result) {
        if (!$result || $result->num_rows === 0) {
            return "Không tìm thấy dữ liệu.";
        }

        $html = '<table class="table table-striped table-sm">';
        
        // Header
        $html .= '<thead><tr>';
        $fields = $result->fetch_fields();
        foreach ($fields as $field) {
            $html .= '<th>' . htmlspecialchars($field->name) . '</th>';
        }
        $html .= '</tr></thead>';
        
        // Body
        $html .= '<tbody>';
        $result->data_seek(0);
        while ($row = $result->fetch_assoc()) {
            $html .= '<tr>';
            foreach ($row as $value) {
                $html .= '<td>' . htmlspecialchars($value) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        
        return $html;
    }
}

// Xử lý tin nhắn và tạo câu trả lời
if (!function_exists('processMessage')) {
    function processMessage($message, $conn) {
        $nlp = processNaturalLanguage($message);
        
        // Xử lý ping để kiểm tra kết nối
        if ($message === 'ping') {
            return 'pong';
        }
        
        // Xử lý câu chào hỏi
        $greetings = ['xin chào', 'chào', 'hello', 'hi', 'chào bạn', 'chào bot'];
        foreach ($greetings as $greeting) {
            if ($nlp['contains']($greeting)) {
                return "Xin chào! Tôi là trợ lý ảo của hệ thống quản lý nhân sự. Tôi có thể giúp bạn:\n" .
                       "- Xem tổng số nhân viên\n" .
                       "- Thông tin phòng ban\n" .
                       "- Thông tin lương\n" .
                       "- Thông tin nghỉ phép\n" .
                       "- Danh sách nhân viên mới\n" .
                       "- Thống kê theo phòng ban\n" .
                       "Bạn cần tôi giúp gì không?";
            }
        }
        
        // Nhận diện câu hỏi về tổng số nhân viên
        if ($nlp['contains']('nhân viên') && (
            $nlp['contains']('tổng') || 
            $nlp['contains']('bao nhiêu') || 
            $nlp['contains']('số lượng') || 
            $nlp['contains']('đếm')
        )) {
            $sql = "SELECT COUNT(*) as total FROM employees";
            $result = $conn->query($sql);
            if ($result) {
                $row = $result->fetch_assoc();
                return "Tổng số nhân viên hiện tại là: " . $row['total'] . " người.";
            }
            return "Xin lỗi, không thể truy vấn thông tin nhân viên lúc này.";
        }
        
        // Nhận diện câu hỏi về phòng ban
        if (($nlp['contains']('phòng ban') || $nlp['contains']('bộ phận')) && (
            $nlp['contains']('bao nhiêu') || 
            $nlp['contains']('tổng') || 
            $nlp['contains']('số lượng') || 
            $nlp['contains']('liệt kê')
        )) {
            $sql = "SELECT d.name as tenphongban, COUNT(e.id) as sonhanvien, 
                    AVG(e.base_salary) as luongtrungbinh 
                    FROM departments d 
                    LEFT JOIN employees e ON d.id = e.department_id 
                    GROUP BY d.id";
            $result = $conn->query($sql);
            if ($result) {
                return "Thông tin các phòng ban:\n" . createTableFromSQL($result);
            }
            return "Xin lỗi, không thể truy vấn thông tin phòng ban lúc này.";
        }
        
        // Nhận diện câu hỏi về lương
        if (($nlp['contains']('lương') || $nlp['contains']('thu nhập')) && (
            $nlp['contains']('trung bình') || 
            $nlp['contains']('bao nhiêu') || 
            $nlp['contains']('mức')
        )) {
            $sql = "SELECT 
                    MIN(base_salary) as luongthapnhat,
                    AVG(base_salary) as luongtrungbinh,
                    MAX(base_salary) as luongcaonhat
                    FROM employees";
            $result = $conn->query($sql);
            if ($result) {
                $row = $result->fetch_assoc();
                return "Thông tin về lương:\n" .
                       "- Lương thấp nhất: " . number_format($row['luongthapnhat']) . " VNĐ\n" .
                       "- Lương trung bình: " . number_format($row['luongtrungbinh']) . " VNĐ\n" .
                       "- Lương cao nhất: " . number_format($row['luongcaonhat']) . " VNĐ";
            }
            return "Xin lỗi, không thể truy vấn thông tin lương lúc này.";
        }
        
        // Nhận diện câu hỏi về nghỉ phép
        if (($nlp['contains']('nghỉ phép') || $nlp['contains']('đơn nghỉ')) && (
            $nlp['contains']('bao nhiêu') || 
            $nlp['contains']('tổng') || 
            $nlp['contains']('số lượng')
        )) {
            $sql = "SELECT 
                    COUNT(*) as tongdon,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as daduyet,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as choduyet
                    FROM leaves 
                    WHERE MONTH(start_date) = MONTH(CURRENT_DATE())";
            $result = $conn->query($sql);
            if ($result) {
                $row = $result->fetch_assoc();
                return "Thống kê đơn nghỉ phép tháng này:\n" .
                       "- Tổng số đơn: " . $row['tongdon'] . "\n" .
                       "- Đã duyệt: " . $row['daduyet'] . "\n" .
                       "- Chờ duyệt: " . $row['choduyet'];
            }
            return "Xin lỗi, không thể truy vấn thông tin nghỉ phép lúc này.";
        }

        // Nhận diện câu hỏi về nhân viên mới
        if ($nlp['contains']('nhân viên mới') || $nlp['contains']('tuyển dụng')) {
            $sql = "SELECT name as hoten, hire_date as ngayvaolam, department_id as phongban_id 
                    FROM employees 
                    WHERE hire_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
                    ORDER BY hire_date DESC";
            $result = $conn->query($sql);
            if ($result) {
                return "Danh sách nhân viên mới trong 30 ngày qua:\n" . createTableFromSQL($result);
            }
            return "Xin lỗi, không thể truy vấn thông tin nhân viên mới lúc này.";
        }
        
        // Gợi ý thông minh nếu không hiểu
        $suggest = "Bạn có thể hỏi tôi về:\n" .
                   "- Tổng số nhân viên\n" .
                   "- Thông tin chi tiết phòng ban\n" .
                   "- Thống kê lương\n" .
                   "- Thống kê nghỉ phép\n" .
                   "- Danh sách nhân viên mới\n" .
                   "Hoặc hỏi các thông tin khác về nhân sự!";
        return "Xin lỗi, tôi chưa hiểu ý bạn. $suggest";
    }
}

// Xử lý và trả về kết quả
$response = processMessage($message, $conn);
echo $response;

$conn->close();
?> 