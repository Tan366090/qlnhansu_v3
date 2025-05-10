<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
require_once __DIR__ . '/vendor/autoload.php';
use Phpml\Math\Statistic\Mean;
use Phpml\Math\Statistic\StandardDeviation;
use Phpml\FeatureExtraction\TfIdfTransformer;
use Phpml\Tokenization\WhitespaceTokenizer;
use Phpml\Classification\SVC;
use Phpml\Dataset\ArrayDataset;
use Phpml\Preprocessing\Normalizer;
use Phpml\Clustering\KMeans;
use Phpml\Association\Apriori;

class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        $this->conn = new mysqli("localhost", "root", "", "qlnhansu");
        $this->conn->set_charset("utf8");
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function query($sql) {
        $result = $this->conn->query($sql);
        if (!$result) {
            throw new Exception("Query failed: " . $this->conn->error);
        }
        return $result;
    }
    
    public function fetch($result) {
        return $result->fetch_assoc();
    }
    
    public function fetchAll($result) {
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
}

class ChatEngine {
    private $conn;
    private $tfidf;
    private $tokenizer;
    private $classifier;
    private $normalizer;
    private $context = [];
    private $conversationHistory = [];
    private $tables = [
        'employees' => ['name', 'email', 'phone', 'employee_code', 'position_id', 'department_id'],
        'departments' => ['name', 'description'],
        'positions' => ['name', 'description'],
        'users' => ['username', 'email'],
        'user_profiles' => ['full_name', 'phone_number', 'date_of_birth', 'gender', 'current_address'],
        'leaves' => ['employee_id', 'start_date', 'end_date', 'reason', 'status'],
        'attendance' => ['employee_id', 'attendance_date', 'check_in_time', 'check_out_time', 'attendance_symbol'],
        'payroll' => ['employee_id', 'pay_period_start', 'pay_period_end', 'base_salary_period', 'allowances_total', 'bonuses_total', 'gross_salary', 'net_salary'],
        'training_courses' => ['name', 'description', 'duration', 'cost', 'status'],
        'performances' => ['employee_id', 'review_period_start', 'review_period_end', 'performance_score', 'reviewer_comments']
    ];
    
    // Mở rộng từ điển từ đồng nghĩa
    private $synonyms = [
        'nhân viên' => ['nhân viên', 'người lao động', 'công nhân viên', 'nhân sự', 'cán bộ', 'nhân viên công ty', 'người làm việc', 'nhân viên chính thức', 'nhân viên thử việc'],
        'phòng ban' => ['phòng ban', 'bộ phận', 'phòng', 'ban', 'đơn vị', 'phòng làm việc', 'phòng chức năng', 'phòng nghiệp vụ', 'phòng chuyên môn'],
        'lương' => ['lương', 'thu nhập', 'lương bổng', 'tiền lương', 'lương tháng', 'thu nhập hàng tháng', 'lương cơ bản', 'lương gross', 'lương net', 'lương thưởng'],
        'nghỉ phép' => ['nghỉ phép', 'nghỉ việc', 'nghỉ', 'vắng mặt', 'nghỉ không lương', 'nghỉ có lương', 'nghỉ ốm', 'nghỉ thai sản', 'nghỉ phép năm'],
        'chức vụ' => ['chức vụ', 'vị trí', 'chức danh', 'công việc', 'nhiệm vụ', 'vai trò', 'cấp bậc', 'chức năng', 'trách nhiệm'],
        'tuyển dụng' => ['tuyển dụng', 'tuyển', 'tuyển mới', 'tuyển nhân viên', 'tuyển người', 'tuyển nhân sự', 'tuyển dụng nhân tài', 'tuyển dụng nhân viên mới'],
        'đánh giá' => ['đánh giá', 'xếp loại', 'nhận xét', 'phân loại', 'đánh giá hiệu suất', 'đánh giá công việc', 'đánh giá nhân viên', 'đánh giá KPI'],
        'đào tạo' => ['đào tạo', 'huấn luyện', 'đào tạo nhân viên', 'khóa học', 'đào tạo chuyên môn', 'đào tạo kỹ năng', 'đào tạo nội bộ', 'đào tạo nâng cao'],
        'kỷ luật' => ['kỷ luật', 'vi phạm', 'cảnh cáo', 'khiển trách', 'xử phạt', 'kỷ luật lao động', 'vi phạm nội quy'],
        'thưởng' => ['thưởng', 'phụ cấp', 'trợ cấp', 'phúc lợi', 'tiền thưởng', 'thưởng dự án', 'thưởng thành tích', 'thưởng tháng 13'],
        'hợp đồng' => ['hợp đồng', 'thỏa thuận', 'cam kết', 'điều khoản', 'thời hạn', 'hợp đồng lao động', 'hợp đồng thử việc', 'hợp đồng chính thức'],
        'bảo hiểm' => ['bảo hiểm', 'bảo hiểm xã hội', 'bảo hiểm y tế', 'bảo hiểm thất nghiệp', 'bảo hiểm nhân thọ', 'bảo hiểm tai nạn'],
        'chấm công' => ['chấm công', 'điểm danh', 'giờ làm việc', 'thời gian làm việc', 'tăng ca', 'làm thêm giờ'],
        'khen thưởng' => ['khen thưởng', 'khen ngợi', 'tuyên dương', 'vinh danh', 'thưởng nóng', 'thưởng đột xuất'],
        'nghỉ việc' => ['nghỉ việc', 'thôi việc', 'nghỉ hưu', 'nghỉ không lương', 'nghỉ tạm thời', 'nghỉ dài hạn']
    ];
    
    // Thêm từ điển các từ khóa thời gian
    private $timeKeywords = [
        'năm nay' => ['năm nay', 'năm hiện tại', 'năm 2024'],
        'năm ngoái' => ['năm ngoái', 'năm 2023'],
        'tháng này' => ['tháng này', 'tháng hiện tại'],
        'tháng trước' => ['tháng trước', 'tháng vừa rồi'],
        'tuần này' => ['tuần này', 'tuần hiện tại'],
        'tuần trước' => ['tuần trước', 'tuần vừa rồi'],
        'hôm nay' => ['hôm nay', 'ngày hôm nay'],
        'hôm qua' => ['hôm qua', 'ngày hôm qua']
    ];
    
    // Thêm từ điển các từ khóa so sánh
    private $comparisonKeywords = [
        'cao nhất' => ['cao nhất', 'lớn nhất', 'nhiều nhất', 'tối đa'],
        'thấp nhất' => ['thấp nhất', 'ít nhất', 'tối thiểu'],
        'trung bình' => ['trung bình', 'bình quân', 'trung bình cộng'],
        'tăng' => ['tăng', 'tăng lên', 'tăng thêm', 'tăng trưởng'],
        'giảm' => ['giảm', 'giảm xuống', 'giảm đi', 'suy giảm']
    ];
    
    // Mở rộng dữ liệu huấn luyện
    private $trainingData = [
        // Câu hỏi về số lượng
        ['có bao nhiêu nhân viên', 'count'],
        ['tổng số phòng ban là mấy', 'count'],
        ['số lượng nhân viên mới', 'count'],
        ['có mấy phòng ban', 'count'],
        ['tổng số nhân viên năm nay', 'count'],
        ['số người nghỉ phép tháng này', 'count'],
        ['bao nhiêu nhân viên đang thử việc', 'count'],
        ['số lượng nhân viên nghỉ việc', 'count'],
        ['có bao nhiêu nhân viên tăng ca', 'count'],
        ['số người được khen thưởng', 'count'],
        
        // Câu hỏi về danh sách
        ['liệt kê nhân viên', 'list'],
        ['kể tên các phòng ban', 'list'],
        ['danh sách nhân viên mới', 'list'],
        ['cho biết các chức vụ', 'list'],
        ['danh sách nhân viên phòng IT', 'list'],
        ['liệt kê khóa đào tạo năm nay', 'list'],
        ['danh sách nhân viên nghỉ phép', 'list'],
        ['liệt kê các dự án đang thực hiện', 'list'],
        ['danh sách nhân viên được khen thưởng', 'list'],
        ['cho biết các chương trình đào tạo', 'list'],
        
        // Câu hỏi về chi tiết
        ['thông tin về nhân viên', 'detail'],
        ['chi tiết phòng ban', 'detail'],
        ['mô tả về chức vụ', 'detail'],
        ['thông tin chi tiết', 'detail'],
        ['thông tin lương của nhân viên', 'detail'],
        ['chi tiết đánh giá nhân viên', 'detail'],
        ['thông tin hợp đồng lao động', 'detail'],
        ['chi tiết chấm công', 'detail'],
        ['thông tin bảo hiểm', 'detail'],
        ['chi tiết khen thưởng', 'detail'],
        
        // Câu hỏi về thống kê
        ['lương trung bình', 'statistics'],
        ['thống kê nghỉ phép', 'statistics'],
        ['tổng lương', 'statistics'],
        ['trung bình đánh giá', 'statistics'],
        ['lương cao nhất phòng IT', 'statistics'],
        ['thống kê đánh giá theo phòng ban', 'statistics'],
        ['thống kê tăng ca', 'statistics'],
        ['tỷ lệ nghỉ việc', 'statistics'],
        ['thống kê đào tạo', 'statistics'],
        ['thống kê khen thưởng', 'statistics'],
        
        // Câu hỏi về so sánh
        ['so sánh lương giữa các phòng', 'comparison'],
        ['phòng nào có nhiều nhân viên nhất', 'comparison'],
        ['nhân viên nào có lương cao nhất', 'comparison'],
        ['phòng nào có tỷ lệ nghỉ phép cao nhất', 'comparison'],
        ['so sánh hiệu suất làm việc', 'comparison'],
        ['phòng nào có nhiều dự án nhất', 'comparison'],
        ['so sánh tỷ lệ tăng ca', 'comparison'],
        ['phòng nào có nhiều nhân viên mới', 'comparison'],
        ['so sánh tỷ lệ đánh giá', 'comparison'],
        ['phòng nào có nhiều khóa đào tạo', 'comparison'],
        
        // Câu hỏi về xu hướng
        ['xu hướng tuyển dụng năm nay', 'trend'],
        ['tỷ lệ tăng lương theo thời gian', 'trend'],
        ['biểu đồ đánh giá nhân viên', 'trend'],
        ['thống kê nghỉ phép theo tháng', 'trend'],
        ['xu hướng nghỉ việc', 'trend'],
        ['tỷ lệ tăng ca theo thời gian', 'trend'],
        ['xu hướng đào tạo', 'trend'],
        ['thống kê khen thưởng theo quý', 'trend'],
        ['xu hướng đánh giá KPI', 'trend'],
        ['tỷ lệ nhân viên mới theo tháng', 'trend']
    ];
    
    private $suggestedQuestions = [
        'count' => [
            'Bạn có muốn xem chi tiết danh sách không?',
            'Bạn có muốn biết thêm thông tin về {subject} không?',
            'Bạn có muốn xem thống kê theo thời gian không?'
        ],
        'list' => [
            'Bạn có muốn xem thông tin chi tiết về {item} không?',
            'Bạn có muốn biết thêm về {subject} không?',
            'Bạn có muốn so sánh các {subject} không?'
        ],
        'detail' => [
            'Bạn có muốn xem thông tin liên quan không?',
            'Bạn có muốn biết thêm về {subject} không?',
            'Bạn có muốn xem lịch sử {subject} không?'
        ],
        'statistics' => [
            'Bạn có muốn xem biểu đồ thống kê không?',
            'Bạn có muốn so sánh với thời gian khác không?',
            'Bạn có muốn xem chi tiết từng {subject} không?'
        ],
        'comparison' => [
            'Bạn có muốn xem chi tiết so sánh không?',
            'Bạn có muốn biết nguyên nhân của sự khác biệt không?',
            'Bạn có muốn xem xu hướng thay đổi không?'
        ],
        'trend' => [
            'Bạn có muốn xem dự báo xu hướng không?',
            'Bạn có muốn biết nguyên nhân của xu hướng này không?',
            'Bạn có muốn so sánh với các giai đoạn khác không?'
        ]
    ];
    
    private $relatedTopics = [
        'nhân viên' => ['lương', 'đánh giá', 'nghỉ phép', 'đào tạo'],
        'phòng ban' => ['nhân viên', 'lương', 'đánh giá', 'ngân sách'],
        'lương' => ['thưởng', 'phụ cấp', 'tăng lương', 'bảo hiểm'],
        'đánh giá' => ['khen thưởng', 'kỷ luật', 'đào tạo', 'thăng chức'],
        'nghỉ phép' => ['lương', 'bảo hiểm', 'chế độ', 'quy định'],
        'đào tạo' => ['chi phí', 'hiệu quả', 'chứng chỉ', 'kỹ năng']
    ];
    
    public function __construct() {
        // Kết nối database
        $this->conn = new mysqli("localhost", "root", "", "qlnhansu");
        $this->conn->set_charset("utf8");
        
        // Khởi tạo các công cụ xử lý ngôn ngữ
        $this->tfidf = new TfIdfTransformer();
        $this->tokenizer = new WhitespaceTokenizer();
        $this->normalizer = new Normalizer();
        
        // Huấn luyện classifier
        $this->trainClassifier();
    }
    
    private function trainClassifier() {
        $samples = [];
        $labels = [];
        
        foreach ($this->trainingData as $data) {
            $samples[] = $this->preprocessText($data[0]);
            $labels[] = $data[1];
        }
        
        $dataset = new ArrayDataset($samples, $labels);
        $this->classifier = new SVC();
        $this->classifier->train($dataset->getSamples(), $dataset->getTargets());
    }
    
    private function preprocessText($text) {
        // Chuyển về chữ thường
        $text = mb_strtolower($text, 'UTF-8');
        
        // Loại bỏ dấu câu
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        
        // Tokenize
        $tokens = $this->tokenizer->tokenize($text);
        
        // Thay thế từ đồng nghĩa và xử lý từ khóa thời gian
        $processedTokens = [];
        foreach ($tokens as $token) {
            // Xử lý từ đồng nghĩa
            $replaced = false;
            foreach ($this->synonyms as $mainWord => $synonyms) {
                if (in_array($token, $synonyms)) {
                    $processedTokens[] = $mainWord;
                    $replaced = true;
                    break;
                }
            }
            
            // Xử lý từ khóa thời gian
            if (!$replaced) {
                foreach ($this->timeKeywords as $mainWord => $keywords) {
                    if (in_array($token, $keywords)) {
                        $processedTokens[] = $mainWord;
                        $replaced = true;
                        break;
                    }
                }
            }
            
            // Xử lý từ khóa so sánh
            if (!$replaced) {
                foreach ($this->comparisonKeywords as $mainWord => $keywords) {
                    if (in_array($token, $keywords)) {
                        $processedTokens[] = $mainWord;
                        $replaced = true;
                        break;
                    }
                }
            }
            
            if (!$replaced) {
                $processedTokens[] = $token;
            }
        }
        
        return $processedTokens;
    }
    
    private function extractTimeContext($query) {
        $timeContext = [];
        $tokens = $this->preprocessText($query);
        
        foreach ($tokens as $token) {
            foreach ($this->timeKeywords as $mainWord => $keywords) {
                if (in_array($token, $keywords)) {
                    $timeContext[] = $mainWord;
                }
            }
        }
        
        return $timeContext;
    }
    
    private function extractComparisonContext($query) {
        $comparisonContext = [];
        $tokens = $this->preprocessText($query);
        
        foreach ($tokens as $token) {
            foreach ($this->comparisonKeywords as $mainWord => $keywords) {
                if (in_array($token, $keywords)) {
                    $comparisonContext[] = $mainWord;
                }
            }
        }
        
        return $comparisonContext;
    }
    
    private function buildQuery($table, $columns, $conditions) {
        $query = "SELECT * FROM $table";
        $whereConditions = [];
        $params = [];
        $types = '';
        
        foreach ($conditions as $column => $value) {
            if (in_array($column, $columns)) {
                $whereConditions[] = "$column = ?";
                $params[] = $value;
                $types .= 's';
            }
        }
        
        if (!empty($whereConditions)) {
            $query .= " WHERE " . implode(' AND ', $whereConditions);
        }
        
        return ['query' => $query, 'params' => $params, 'types' => $types];
    }
    
    private function getRelevantData($query) {
        // Tiền xử lý câu hỏi
        $processedQuery = $this->preprocessText($query);
        
        // Trích xuất ngữ cảnh
        $timeContext = $this->extractTimeContext($query);
        $comparisonContext = $this->extractComparisonContext($query);
        
        // Lưu ngữ cảnh
        $this->context = [
            'time' => $timeContext,
            'comparison' => $comparisonContext
        ];
        
        $relevantData = [];
        
        // Tìm kiếm trong tất cả các bảng
        foreach ($this->tables as $table => $columns) {
            $tableData = $this->searchInTable($table, $columns, $processedQuery);
            if (!empty($tableData)) {
                $relevantData[$table] = $tableData;
            }
        }
        
        // Lưu lịch sử hội thoại
        $this->conversationHistory[] = [
            'query' => $query,
            'processed_query' => $processedQuery,
            'context' => $this->context,
            'data' => $relevantData
        ];
        
        return $relevantData;
    }
    
    private function searchInTable($table, $columns, $queryTokens) {
        $results = [];
        
        // Xử lý đặc biệt cho câu hỏi về số lượng nhân viên
        if ($table === 'employees' && $this->isCountQuery($queryTokens)) {
            $query = "SELECT e.*, p.name as position_name, d.name as department_name 
                     FROM employees e 
                     LEFT JOIN positions p ON e.position_id = p.id 
                     LEFT JOIN departments d ON e.department_id = d.id 
                     WHERE e.status = 'active'";
            
            $result = $this->conn->query($query);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $results[] = $row;
                }
            }
            return $results;
        }
        
        // Xử lý đặc biệt cho câu hỏi về phòng ban
        if ($table === 'departments') {
            $query = "SELECT d.*, COUNT(e.id) as employee_count 
                     FROM departments d 
                     LEFT JOIN employees e ON d.id = e.department_id 
                     GROUP BY d.id";
            
            $result = $this->conn->query($query);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $results[] = $row;
                }
            }
            return $results;
        }
        
        // Xử lý đặc biệt cho câu hỏi về lương
        if ($table === 'payroll') {
            $query = "SELECT p.*, e.name as employee_name, d.name as department_name 
                     FROM payroll p 
                     JOIN employees e ON p.employee_id = e.id 
                     LEFT JOIN departments d ON e.department_id = d.id";
            
            $result = $this->conn->query($query);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $results[] = $row;
                }
            }
            return $results;
        }
        
        // Xử lý đặc biệt cho câu hỏi về nghỉ phép
        if ($table === 'leaves') {
            $query = "SELECT l.*, e.name as employee_name, d.name as department_name 
                     FROM leaves l 
                     JOIN employees e ON l.employee_id = e.id 
                     LEFT JOIN departments d ON e.department_id = d.id";
            
            $result = $this->conn->query($query);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $results[] = $row;
                }
            }
            return $results;
        }
        
        // Xác định các bảng cần join
        $joins = [];
        $selectColumns = [];
        
        if ($table === 'employees') {
            $joins[] = "LEFT JOIN positions p ON e.position_id = p.id";
            $joins[] = "LEFT JOIN departments d ON e.department_id = d.id";
            $selectColumns = [
                'e.*',
                'p.name as position_name',
                'd.name as department_name'
            ];
        } else {
            $selectColumns = ['*'];
        }
        
        // Tạo câu query động dựa trên các cột
        $whereConditions = [];
        foreach ($columns as $column) {
            if ($table === 'employees') {
                if ($column === 'position_id') {
                    $whereConditions[] = "p.name LIKE ?";
                } elseif ($column === 'department_id') {
                    $whereConditions[] = "d.name LIKE ?";
                } else {
                    $whereConditions[] = "e.$column LIKE ?";
                }
            } else {
                $whereConditions[] = "$column LIKE ?";
            }
        }
        $whereClause = implode(' OR ', $whereConditions);
        
        // Xây dựng câu query hoàn chỉnh
        $query = "SELECT " . implode(', ', $selectColumns) . " FROM $table";
        if ($table === 'employees') {
            $query = "SELECT " . implode(', ', $selectColumns) . " FROM employees e";
        }
        $query .= " " . implode(' ', $joins);
        $query .= " WHERE $whereClause";
        
        $stmt = $this->conn->prepare($query);
        
        if ($stmt) {
            // Bind parameters với các từ đồng nghĩa và ngữ cảnh
            $searchPatterns = [];
            foreach ($queryTokens as $token) {
                // Thêm từ gốc
                $searchPatterns[] = "%$token%";
                
                // Thêm các từ đồng nghĩa
                foreach ($this->synonyms as $mainWord => $synonyms) {
                    if ($token === $mainWord) {
                        foreach ($synonyms as $synonym) {
                            $searchPatterns[] = "%$synonym%";
                        }
                    }
                }
            }
            
            // Thêm điều kiện thời gian nếu có
            if (!empty($this->context['time'])) {
                foreach ($this->context['time'] as $timeKeyword) {
                    $searchPatterns[] = "%$timeKeyword%";
                }
            }
            
            $params = array_fill(0, count($columns), implode(' OR ', $searchPatterns));
            $types = str_repeat('s', count($columns));
            $stmt->bind_param($types, ...$params);
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                // Tính độ tương đồng cho mỗi dòng
                $rowText = implode(' ', array_map(function($col) use ($row) {
                    return $row[$col] ?? '';
                }, $columns));
                
                $rowTokens = $this->preprocessText($rowText);
                $similarity = $this->calculateSimilarity($queryTokens, $rowTokens);
                
                if ($similarity > 0.3) {
                    $results[] = $row;
                }
            }
            
            $stmt->close();
        }
        
        return $results;
    }
    
    private function calculateSimilarity($tokens1, $tokens2) {
        // Tính Jaccard similarity
        $intersection = array_intersect($tokens1, $tokens2);
        $union = array_unique(array_merge($tokens1, $tokens2));
        
        if (count($union) == 0) {
            return 0;
        }
        
        return count($intersection) / count($union);
    }
    
    private function analyzeIntent($query) {
        // Chuyển về chữ thường
        $query = mb_strtolower($query, 'UTF-8');
        
        // Kiểm tra chào hỏi
        if ($this->isGreeting($query)) {
            return [
                'type' => 'greeting'
            ];
        }
        
        // Phân tích từ khóa
        $tokens = $this->preprocessText($query);
        
        // Trích xuất ngữ cảnh thời gian
        $timeContext = $this->extractTimeContext($query);
        
        // Trích xuất ngữ cảnh so sánh
        $comparisonContext = $this->extractComparisonContext($query);
        
        // Xác định loại câu hỏi
        $type = 'default';
        $topics = [];
        
        // Kiểm tra từ khóa về nhân viên
        if (preg_match('/(có|tổng số|số lượng|bao nhiêu|mấy).*(nhân viên)/', $query)) {
            $type = 'employee_count';
            $topics[] = 'employee';
        }
        
        // Kiểm tra từ khóa về phòng ban
        if (preg_match('/(thống kê|số lượng|bao nhiêu).*(phòng ban)/', $query)) {
            $type = 'department_stats';
            $topics[] = 'department';
        }
        
        // Kiểm tra từ khóa về lương
        if (preg_match('/(lương|thu nhập).*(trung bình|cao nhất|thấp nhất)/', $query)) {
            $type = 'salary_stats';
            $topics[] = 'salary';
        }
        
        // Kiểm tra từ khóa về nghỉ phép
        if (preg_match('/(thống kê|số lượng|bao nhiêu).*(nghỉ phép)/', $query)) {
            $type = 'leave_stats';
            $topics[] = 'leave';
        }
        
        // Kiểm tra câu hỏi phức tạp
        if (count($topics) > 1 || preg_match('/(so sánh|phân tích|thống kê)/', $query)) {
            $type = 'complex_query';
        }
        
        return [
            'type' => $type,
            'topics' => $topics,
            'time' => $timeContext,
            'comparison' => $comparisonContext
        ];
    }
    
    private function handleGreeting() {
        return [
            'success' => true,
            'response' => "Xin chào! 😊 Rất vui được trò chuyện với bạn. Bạn đang cần mình giúp gì hôm nay? 🚀",
            'relevant_data' => [],
            'context' => [
                'time' => [],
                'comparison' => []
            ]
        ];
    }
    
    private function handleEmployeeCount() {
        try {
            $db = Database::getInstance();
            $sql = "SELECT COUNT(*) as total FROM employees WHERE status = 'active'";
            $result = $db->query($sql);
            $data = $db->fetch($result);
            
            return [
                'success' => true,
                'response' => "Hiện tại có " . $data['total'] . " nhân viên trong công ty.",
                'relevant_data' => [
                    'employees' => $data
                ],
                'context' => [
                    'time' => [],
                    'comparison' => []
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Có lỗi xảy ra khi đếm số nhân viên: ' . $e->getMessage()
            ];
        }
    }
    
    private function handleDepartmentStats() {
        try {
            $db = Database::getInstance();
            $sql = "SELECT d.*, COUNT(e.id) as employee_count 
                    FROM departments d 
                    LEFT JOIN employees e ON d.id = e.department_id 
                    WHERE e.status = 'active' OR e.status IS NULL
                    GROUP BY d.id";
            $result = $db->query($sql);
            $departments = $db->fetchAll($result);
            
            $response = "Thống kê số lượng nhân viên theo phòng ban:\n\n";
            foreach ($departments as $dept) {
                $count = isset($dept['employee_count']) ? $dept['employee_count'] : 0;
                $response .= "- " . $dept['name'] . ": " . $count . " nhân viên\n";
            }
            
            return [
                'success' => true,
                'response' => $response,
                'relevant_data' => [
                    'departments' => $departments
                ],
                'context' => [
                    'time' => [],
                    'comparison' => []
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Có lỗi xảy ra khi thống kê phòng ban: ' . $e->getMessage()
            ];
        }
    }
    
    private function handleSalaryStats($intent) {
        try {
            $db = Database::getInstance();
            $sql = "SELECT p.*, e.name as employee_name, d.name as department_name 
                    FROM payroll p 
                    JOIN employees e ON p.employee_id = e.id 
                    LEFT JOIN departments d ON e.department_id = d.id";
            $result = $db->query($sql);
            $payroll_data = $db->fetchAll($result);
            
            if (empty($payroll_data)) {
                return [
                    'success' => false,
                    'error' => 'Không tìm thấy dữ liệu lương'
                ];
            }
            
            // Tính toán thống kê
            $salaries = array_column($payroll_data, 'net_salary');
            $avg_salary = array_sum($salaries) / count($salaries);
            $max_salary = max($salaries);
            $min_salary = min($salaries);
            
            $response = "Thống kê lương:\n";
            $response .= "- Lương trung bình: " . number_format($avg_salary) . " VNĐ\n";
            $response .= "- Lương cao nhất: " . number_format($max_salary) . " VNĐ\n";
            $response .= "- Lương thấp nhất: " . number_format($min_salary) . " VNĐ\n";
            
            return [
                'success' => true,
                'response' => $response,
                'relevant_data' => [
                    'payroll' => $payroll_data
                ],
                'context' => [
                    'time' => $intent['time'] ?? [],
                    'comparison' => $intent['comparison'] ?? []
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Có lỗi xảy ra khi xử lý dữ liệu lương: ' . $e->getMessage()
            ];
        }
    }
    
    private function handleLeaveStats() {
        try {
            $db = Database::getInstance();
            $sql = "SELECT l.*, e.name as employee_name, d.name as department_name 
                    FROM leaves l 
                    JOIN employees e ON l.employee_id = e.id 
                    LEFT JOIN departments d ON e.department_id = d.id";
            $leaves = $db->query($sql)->fetchAll();
            
            $total_days = 0;
            foreach ($leaves as $leave) {
                $total_days += $leave['leave_duration_days'];
            }
            
            $response = "Thống kê nghỉ phép:\n";
            $response .= "- Tổng số ngày nghỉ: " . $total_days . " ngày\n";
            $response .= "- Trung bình: " . number_format($total_days / count($leaves), 1) . " ngày/nhân viên\n";
            
            return [
                'success' => true,
                'response' => $response,
                'relevant_data' => [
                    'leaves' => $leaves
                ],
                'context' => [
                    'time' => [],
                    'comparison' => []
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Có lỗi xảy ra khi thống kê nghỉ phép: ' . $e->getMessage()
            ];
        }
    }
    
    private function generateResponse($query, $relevantData) {
        // Phân tích loại câu hỏi
        $questionType = $this->analyzeQuestionType($query);
        
        // Nếu là chào hỏi
        if ($this->isGreeting($query)) {
            $response = "Xin chào! 😊 Rất vui được trò chuyện với bạn. Bạn đang cần mình giúp gì hôm nay? 🚀";
            return $response;
        }
        
        // Tạo câu trả lời chính
        $response = "";
        switch ($questionType) {
            case 'count':
                $response = $this->generateCountResponse($relevantData);
                break;
            case 'list':
                $response = $this->generateListResponse($relevantData);
                break;
            case 'detail':
                $response = $this->generateDetailResponse($relevantData);
                break;
            case 'statistics':
                $response = $this->generateStatisticsResponse($relevantData);
                break;
            case 'comparison':
                $response = $this->generateComparisonResponse($relevantData);
                break;
            case 'trend':
                $response = $this->generateTrendResponse($relevantData);
                break;
            default:
                $response = $this->generateDefaultResponse($relevantData);
        }
        
        // Thêm ngữ cảnh vào câu trả lời
        if (!empty($this->context['time'])) {
            $response = "Trong " . implode(', ', $this->context['time']) . ":\n" . $response;
        }
        
        // Tạo câu hỏi tiếp theo
        $followUpQuestions = $this->generateFollowUpQuestions($query, $questionType, $relevantData);
        
        // Thêm câu hỏi tiếp theo vào câu trả lời
        if (!empty($followUpQuestions)) {
            $response .= "\n\nBạn có thể hỏi thêm:\n";
            foreach ($followUpQuestions as $question) {
                $response .= "- $question\n";
            }
        }
        
        if (empty($response)) {
            $response = "Xin lỗi, tôi không tìm thấy thông tin liên quan đến câu hỏi của bạn.";
        }
        
        // Thêm emoji cảm xúc phù hợp
        $emoji = $this->getEmotionEmoji($questionType, $response);
        $response .= ' ' . $emoji;
        
        return $response;
    }
    
    private function generateCountResponse($data) {
        $response = "";
        
        // Xử lý đặc biệt cho câu hỏi về số lượng nhân viên
        if (isset($data['employees'])) {
            $count = count($data['employees']);
            $response .= "Hiện tại có $count nhân viên trong hệ thống.\n\n";
            
            // Thêm thông tin chi tiết về nhân viên
            $response .= "Danh sách nhân viên:\n";
            foreach ($data['employees'] as $employee) {
                $response .= "- " . $employee['name'] . " (" . $employee['employee_code'] . ")\n";
            }
            return $response;
        }
        
        // Xử lý đặc biệt cho câu hỏi về số lượng phòng ban
        if (isset($data['departments'])) {
            $count = count($data['departments']);
            $response .= "Có $count phòng ban trong công ty:\n\n";
            
            foreach ($data['departments'] as $dept) {
                $response .= "- " . $dept['name'] . " (" . $dept['employee_count'] . " nhân viên)\n";
            }
            return $response;
        }
        
        return $response;
    }
    
    private function generateListResponse($data) {
        $response = "";
        
        // Xử lý danh sách phòng ban
        if (isset($data['departments'])) {
            $response .= "Danh sách phòng ban:\n";
            foreach ($data['departments'] as $dept) {
                $response .= "- " . $dept['name'] . " (" . $dept['employee_count'] . " nhân viên)\n";
            }
            return $response;
        }
        
        // Xử lý danh sách nhân viên
        if (isset($data['employees'])) {
            $response .= "Danh sách nhân viên:\n";
            foreach ($data['employees'] as $employee) {
                $response .= "- " . $employee['name'] . " (" . $employee['employee_code'] . ")\n";
                $response .= "  + Phòng ban: " . $employee['department_name'] . "\n";
                $response .= "  + Chức vụ: " . $employee['position_name'] . "\n";
            }
            return $response;
        }
        
        return $response;
    }
    
    private function generateDetailResponse($data) {
        $response = "Chi tiết thông tin:\n\n";
        foreach ($data as $table => $records) {
            foreach ($records as $record) {
                $response .= "Thông tin từ bảng $table:\n";
                foreach ($record as $key => $value) {
                    if (!empty($value)) {
                        $response .= "- $key: $value\n";
                    }
                }
                $response .= "\n";
            }
        }
        return $response;
    }
    
    private function generateStatisticsResponse($data) {
        $response = "";
        
        // Xử lý thống kê lương
        if (isset($data['payroll'])) {
            $response .= "Thống kê lương:\n";
            $salaries = array_column($data['payroll'], 'net_salary');
            $avgSalary = array_sum($salaries) / count($salaries);
            $maxSalary = max($salaries);
            $minSalary = min($salaries);
            
            $response .= "- Lương trung bình: " . number_format($avgSalary, 0, ',', '.') . " VNĐ\n";
            $response .= "- Lương cao nhất: " . number_format($maxSalary, 0, ',', '.') . " VNĐ\n";
            $response .= "- Lương thấp nhất: " . number_format($minSalary, 0, ',', '.') . " VNĐ\n\n";
        }
        
        // Xử lý thống kê nghỉ phép
        if (isset($data['leaves'])) {
            $response .= "Thống kê nghỉ phép:\n";
            $leaves = array_column($data['leaves'], 'leave_duration_days');
            $totalLeaves = array_sum($leaves);
            $avgLeaves = $totalLeaves / count($leaves);
            
            $response .= "- Tổng số ngày nghỉ: $totalLeaves ngày\n";
            $response .= "- Trung bình: " . number_format($avgLeaves, 1) . " ngày/nhân viên\n\n";
        }
        
        return $response;
    }
    
    private function generateComparisonResponse($data) {
        $response = "";
        
        // Xử lý so sánh lương giữa các phòng
        if (isset($data['payroll']) && isset($data['departments'])) {
            $response .= "So sánh lương giữa các phòng:\n";
            $deptSalaries = [];
            
            foreach ($data['payroll'] as $payroll) {
                $deptId = $payroll['department_id'];
                if (!isset($deptSalaries[$deptId])) {
                    $deptSalaries[$deptId] = [];
                }
                $deptSalaries[$deptId][] = $payroll['net_salary'];
            }
            
            foreach ($deptSalaries as $deptId => $salaries) {
                $deptName = $this->getDepartmentName($deptId, $data['departments']);
                $avgSalary = array_sum($salaries) / count($salaries);
                $response .= "- " . $deptName . ": " . number_format($avgSalary, 0, ',', '.') . " VNĐ\n";
            }
        }
        
        // Xử lý so sánh số lượng nhân viên
        if (isset($data['departments'])) {
            $response .= "\nSo sánh số lượng nhân viên:\n";
            usort($data['departments'], function($a, $b) {
                return $b['employee_count'] - $a['employee_count'];
            });
            
            foreach ($data['departments'] as $dept) {
                $response .= "- " . $dept['name'] . ": " . $dept['employee_count'] . " nhân viên\n";
            }
        }
        
        return $response;
    }
    
    private function getDepartmentName($deptId, $departments) {
        foreach ($departments as $dept) {
            if ($dept['id'] == $deptId) {
                return $dept['name'];
            }
        }
        return "Không xác định";
    }
    
    private function generateTrendResponse($data) {
        $response = "";
        
        // Xử lý xu hướng nghỉ phép theo tháng
        if (isset($data['leaves'])) {
            $response .= "Thống kê nghỉ phép theo tháng:\n";
            $monthlyLeaves = [];
            
            foreach ($data['leaves'] as $leave) {
                $month = date('m/Y', strtotime($leave['start_date']));
                if (!isset($monthlyLeaves[$month])) {
                    $monthlyLeaves[$month] = 0;
                }
                $monthlyLeaves[$month] += $leave['leave_duration_days'];
            }
            
            ksort($monthlyLeaves);
            foreach ($monthlyLeaves as $month => $days) {
                $response .= "- Tháng $month: $days ngày\n";
            }
        }
        
        return $response;
    }
    
    private function generateDefaultResponse($data) {
        return $this->generateListResponse($data);
    }
    
    private function formatRecord($record) {
        $formatted = [];
        foreach ($record as $key => $value) {
            if (!empty($value)) {
                // Định dạng số tiền
                if (strpos($key, 'salary') !== false || strpos($key, 'amount') !== false) {
                    $value = number_format($value, 0, ',', '.') . ' VNĐ';
                }
                // Định dạng ngày tháng
                else if (strpos($key, 'date') !== false || strpos($key, 'time') !== false) {
                    $value = date('d/m/Y', strtotime($value));
                }
                $formatted[] = "$key: $value";
            }
        }
        return implode(', ', $formatted);
    }
    
    private function getNumericFields($record) {
        $numericFields = [];
        foreach ($record as $key => $value) {
            if (is_numeric($value)) {
                $numericFields[] = $key;
            }
        }
        return $numericFields;
    }
    
    private function isCountQuery($tokens) {
        $countKeywords = ['bao nhiêu', 'số lượng', 'tổng số', 'có mấy', 'có bao nhiêu'];
        foreach ($tokens as $token) {
            if (in_array($token, $countKeywords)) {
                return true;
            }
        }
        return false;
    }
    
    public function processQuery($query) {
        // Kiểm tra tin nhắn rỗng
        if (empty(trim($query))) {
            return [
                'success' => false,
                'response' => 'Lỗi: Tin nhắn không được để trống',
                'error' => 'Lỗi: Tin nhắn không được để trống'
            ];
        }

        // Phân tích câu hỏi
        $intent = $this->analyzeIntent($query);
        
        // Xử lý theo intent
        switch ($intent['type']) {
            case 'greeting':
                return $this->handleGreeting();
            
            case 'employee_count':
                return $this->handleEmployeeCount();
            
            case 'department_stats':
                return $this->handleDepartmentStats();
            
            case 'salary_stats':
                return $this->handleSalaryStats($intent);
            
            case 'leave_stats':
                return $this->handleLeaveStats();
            
            case 'complex_query':
                return $this->handleComplexQuery($intent);
            
            default:
                // Nếu không phải câu chào hỏi, tìm kiếm thông tin liên quan
                $relevantData = $this->getRelevantData($query);
                $response = $this->generateResponse($query, $relevantData);
                
                return [
                    'success' => true,
                    'response' => $response,
                    'relevant_data' => $relevantData,
                    'context' => $this->context
                ];
        }
    }
    
    private function handleComplexQuery($intent) {
        try {
            $db = Database::getInstance();
            
            // Lấy dữ liệu từ các bảng
            $result = $db->query("SELECT * FROM departments");
            $departments = $db->fetchAll($result);
            
            $result = $db->query("SELECT p.*, e.department_id 
                                 FROM payroll p 
                                 LEFT JOIN employees e ON p.employee_id = e.id");
            $payroll_data = $db->fetchAll($result);
            
            $result = $db->query("SELECT l.*, e.department_id 
                                 FROM leaves l 
                                 LEFT JOIN employees e ON l.employee_id = e.id");
            $leave_data = $db->fetchAll($result);
            
            // Tạo câu trả lời
            $response = "";
            
            if (in_array('salary', $intent['topics'])) {
                $response .= $this->generateSalaryResponse($payroll_data, $departments);
            }
            
            if (in_array('leave', $intent['topics'])) {
                $response .= $this->generateLeaveResponse($leave_data, $departments);
            }
            
            if (in_array('department', $intent['topics'])) {
                $response .= $this->generateDepartmentResponse($departments);
            }
            
            return [
                'success' => true,
                'response' => $response,
                'relevant_data' => [
                    'departments' => $departments,
                    'payroll' => $payroll_data,
                    'leaves' => $leave_data
                ],
                'context' => [
                    'time' => $intent['time'] ?? [],
                    'comparison' => $intent['comparison'] ?? []
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Có lỗi xảy ra khi xử lý câu hỏi phức tạp: ' . $e->getMessage()
            ];
        }
    }
    
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }

    private function isGreeting($query) {
        $greetings = ['xin chào', 'chào', 'hello', 'hi', 'hey'];
        $query = mb_strtolower($query, 'UTF-8');
        foreach ($greetings as $greet) {
            if (strpos($query, $greet) !== false) return true;
        }
        return false;
    }

    private function generateFollowUpQuestions($query, $questionType, $relevantData) {
        $followUpQuestions = [];
        
        // Lấy câu hỏi gợi ý dựa trên loại câu hỏi
        if (isset($this->suggestedQuestions[$questionType])) {
            $suggestions = $this->suggestedQuestions[$questionType];
            
            // Tìm các chủ đề liên quan trong câu hỏi
            $subjects = $this->extractSubjects($query);
            
            foreach ($suggestions as $suggestion) {
                // Thay thế {subject} và {item} bằng chủ đề thực tế
                $question = $suggestion;
                foreach ($subjects as $subject) {
                    $question = str_replace('{subject}', $subject, $question);
                    $question = str_replace('{item}', $subject, $question);
                }
                $followUpQuestions[] = $question;
            }
        }
        
        // Thêm câu hỏi liên quan dựa trên chủ đề
        foreach ($this->extractSubjects($query) as $subject) {
            if (isset($this->relatedTopics[$subject])) {
                foreach ($this->relatedTopics[$subject] as $relatedTopic) {
                    $followUpQuestions[] = "Bạn có muốn biết thêm về $relatedTopic không?";
                }
            }
        }
        
        // Thêm câu hỏi dựa trên dữ liệu tìm được
        foreach ($relevantData as $table => $records) {
            if (!empty($records)) {
                $firstRecord = $records[0];
                foreach ($firstRecord as $key => $value) {
                    if (!empty($value) && !in_array($key, ['id', 'created_at', 'updated_at'])) {
                        $followUpQuestions[] = "Bạn có muốn xem thêm thông tin về $key không?";
                    }
                }
            }
        }
        
        // Loại bỏ các câu hỏi trùng lặp
        $followUpQuestions = array_unique($followUpQuestions);
        
        // Giới hạn số lượng câu hỏi gợi ý
        return array_slice($followUpQuestions, 0, 3);
    }

    private function extractSubjects($query) {
        $subjects = [];
        $tokens = $this->preprocessText($query);
        
        foreach ($tokens as $token) {
            foreach ($this->synonyms as $mainWord => $synonyms) {
                if (in_array($token, $synonyms)) {
                    $subjects[] = $mainWord;
                    break;
                }
            }
        }
        
        return array_unique($subjects);
    }

    private function getEmotionEmoji($questionType, $response) {
        $emojis = [
            'greeting' => ['😊', '👋', '🤗'],
            'count' => ['🔢', '📊', '😊'],
            'list' => ['📋', '📝', '😃'],
            'detail' => ['🔍', '📄', '🙂'],
            'statistics' => ['📈', '📊', '🤓'],
            'comparison' => ['⚖️', '🤔', '🔎'],
            'trend' => ['📈', '🚀', '📉'],
            'error' => ['😢', '⚠️', '🙁'],
            'default' => ['🤖', '💬']
        ];
        if (stripos($response, 'xin lỗi') !== false) return '😢';
        if (isset($emojis[$questionType])) {
            return $emojis[$questionType][array_rand($emojis[$questionType])];
        }
        return $emojis['default'][array_rand($emojis['default'])];
    }

    private function generateDepartmentResponse($departments) {
        $response = "Thống kê số lượng nhân viên theo phòng ban:\n\n";
        foreach ($departments as $dept) {
            $response .= "- " . $dept['name'] . ": " . $dept['employee_count'] . " nhân viên\n";
        }
        return $response;
    }

    private function generateSalaryResponse($payroll_data, $departments) {
        $response = "Thống kê lương:\n";
        
        // Tính toán thống kê chung
        $salaries = array_column($payroll_data, 'net_salary');
        $avg_salary = array_sum($salaries) / count($salaries);
        $max_salary = max($salaries);
        $min_salary = min($salaries);
        
        $response .= "- Lương trung bình: " . number_format($avg_salary) . " VNĐ\n";
        $response .= "- Lương cao nhất: " . number_format($max_salary) . " VNĐ\n";
        $response .= "- Lương thấp nhất: " . number_format($min_salary) . " VNĐ\n\n";
        
        // Thống kê theo phòng ban
        $dept_salaries = [];
        foreach ($payroll_data as $payroll) {
            $dept_id = $payroll['department_id'];
            if (!isset($dept_salaries[$dept_id])) {
                $dept_salaries[$dept_id] = [];
            }
            $dept_salaries[$dept_id][] = $payroll['net_salary'];
        }
        
        $response .= "Thống kê lương theo phòng ban:\n";
        foreach ($dept_salaries as $dept_id => $salaries) {
            $dept_name = $this->getDepartmentName($dept_id, $departments);
            $dept_avg = array_sum($salaries) / count($salaries);
            $response .= "- " . $dept_name . ": " . number_format($dept_avg) . " VNĐ\n";
        }
        
        return $response;
    }

    private function generateLeaveResponse($leave_data, $departments) {
        $response = "Thống kê nghỉ phép:\n";
        
        // Tính toán thống kê chung
        $total_days = 0;
        foreach ($leave_data as $leave) {
            $total_days += $leave['leave_duration_days'];
        }
        
        $response .= "- Tổng số ngày nghỉ: " . $total_days . " ngày\n";
        $response .= "- Trung bình: " . number_format($total_days / count($leave_data), 1) . " ngày/nhân viên\n\n";
        
        // Thống kê theo phòng ban
        $dept_leaves = [];
        foreach ($leave_data as $leave) {
            $dept_id = $leave['department_id'];
            if (!isset($dept_leaves[$dept_id])) {
                $dept_leaves[$dept_id] = 0;
            }
            $dept_leaves[$dept_id] += $leave['leave_duration_days'];
        }
        
        $response .= "Thống kê nghỉ phép theo phòng ban:\n";
        foreach ($dept_leaves as $dept_id => $days) {
            $dept_name = $this->getDepartmentName($dept_id, $departments);
            $response .= "- " . $dept_name . ": " . $days . " ngày\n";
        }
        
        return $response;
    }
}

// Xử lý request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $query = $_POST['query'] ?? '';
    
    if (empty($query)) {
        echo json_encode(['success' => false, 'error' => 'Query không được để trống']);
        exit;
    }
    
    $chatEngine = new ChatEngine();
    $result = $chatEngine->processQuery($query);
    
    header('Content-Type: application/json');
    echo json_encode($result);
}
?> 