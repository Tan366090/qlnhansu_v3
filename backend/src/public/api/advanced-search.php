<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';

// Kết nối database
$database = new Database();
$db = $database->getConnection();

// Chỉ xử lý POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Lấy dữ liệu từ request body
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request data']);
    exit;
}

try {
    $keyword = isset($data['keyword']) ? $data['keyword'] : '';
    $category = isset($data['category']) ? $data['category'] : 'all';
    $dateFrom = isset($data['dateFrom']) ? $data['dateFrom'] : '';
    $dateTo = isset($data['dateTo']) ? $data['dateTo'] : '';
    $status = isset($data['status']) ? $data['status'] : 'all';
    $sortBy = isset($data['sortBy']) ? $data['sortBy'] : 'relevance';

    $whereClauses = [];
    $params = [];

    // Xây dựng điều kiện tìm kiếm
    if (!empty($keyword)) {
        $whereClauses[] = "(title LIKE :keyword OR description LIKE :keyword)";
        $params[':keyword'] = "%$keyword%";
    }

    if ($category !== 'all') {
        $whereClauses[] = "type = :category";
        $params[':category'] = $category;
    }

    if (!empty($dateFrom)) {
        $whereClauses[] = "created_at >= :dateFrom";
        $params[':dateFrom'] = $dateFrom;
    }

    if (!empty($dateTo)) {
        $whereClauses[] = "created_at <= :dateTo";
        $params[':dateTo'] = $dateTo;
    }

    if ($status !== 'all') {
        $whereClauses[] = "status = :status";
        $params[':status'] = $status;
    }

    $whereClause = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

    // Xây dựng câu truy vấn
    $sql = "
        SELECT 
            type,
            id,
            title,
            category,
            description,
            url,
            created_at,
            status
        FROM (
            SELECT 
                'employee' as type,
                id,
                CONCAT(first_name, ' ', last_name) as title,
                'Nhân viên' as category,
                CONCAT('Mã NV: ', employee_code, ' - Phòng ban: ', department_name) as description,
                CONCAT('/employees/view.php?id=', id) as url,
                created_at,
                status
            FROM employees
            UNION ALL
            SELECT 
                'department' as type,
                id,
                name as title,
                'Phòng ban' as category,
                CONCAT('Mã phòng: ', department_code) as description,
                CONCAT('/departments/view.php?id=', id) as url,
                created_at,
                status
            FROM departments
            UNION ALL
            SELECT 
                'document' as type,
                id,
                title,
                'Tài liệu' as category,
                CONCAT('Loại: ', document_type) as description,
                CONCAT('/documents/view.php?id=', id) as url,
                created_at,
                status
            FROM documents
        ) as combined
        $whereClause
    ";

    // Thêm sắp xếp
    switch ($sortBy) {
        case 'date':
            $sql .= " ORDER BY created_at DESC";
            break;
        case 'name':
            $sql .= " ORDER BY title ASC";
            break;
        default:
            $sql .= " ORDER BY 
                CASE 
                    WHEN title LIKE :keyword THEN 1
                    WHEN description LIKE :keyword THEN 2
                    ELSE 3
                END";
    }

    // Thực hiện truy vấn
    $stmt = $db->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Thêm thông tin phân trang
    $totalResults = count($results);
    $page = isset($data['page']) ? (int)$data['page'] : 1;
    $perPage = isset($data['perPage']) ? (int)$data['perPage'] : 10;
    $totalPages = ceil($totalResults / $perPage);

    // Phân trang kết quả
    $offset = ($page - 1) * $perPage;
    $results = array_slice($results, $offset, $perPage);

    // Trả về kết quả với thông tin phân trang
    echo json_encode([
        'results' => $results,
        'pagination' => [
            'total' => $totalResults,
            'perPage' => $perPage,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 