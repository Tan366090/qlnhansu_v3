<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';

// Kết nối database
$database = new Database();
$db = $database->getConnection();

// Xử lý tìm kiếm đơn giản
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = isset($_GET['query']) ? $_GET['query'] : '';
    
    if (empty($query)) {
        echo json_encode([]);
        exit;
    }

    try {
        // Tìm kiếm trong bảng nhân viên
        $stmt = $db->prepare("
            SELECT 
                'employee' as type,
                id,
                CONCAT(first_name, ' ', last_name) as title,
                'Nhân viên' as category,
                CONCAT('Mã NV: ', employee_code, ' - Phòng ban: ', department_name) as description,
                CONCAT('/employees/view.php?id=', id) as url
            FROM employees 
            WHERE 
                first_name LIKE :query OR 
                last_name LIKE :query OR 
                employee_code LIKE :query OR
                department_name LIKE :query
            LIMIT 5
        ");
        $searchQuery = "%$query%";
        $stmt->bindParam(':query', $searchQuery);
        $stmt->execute();
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Tìm kiếm trong bảng phòng ban
        $stmt = $db->prepare("
            SELECT 
                'department' as type,
                id,
                name as title,
                'Phòng ban' as category,
                CONCAT('Mã phòng: ', department_code) as description,
                CONCAT('/departments/view.php?id=', id) as url
            FROM departments 
            WHERE 
                name LIKE :query OR 
                department_code LIKE :query
            LIMIT 5
        ");
        $stmt->bindParam(':query', $searchQuery);
        $stmt->execute();
        $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Tìm kiếm trong bảng tài liệu
        $stmt = $db->prepare("
            SELECT 
                'document' as type,
                id,
                title,
                'Tài liệu' as category,
                CONCAT('Loại: ', document_type) as description,
                CONCAT('/documents/view.php?id=', id) as url
            FROM documents 
            WHERE 
                title LIKE :query OR 
                document_type LIKE :query
            LIMIT 5
        ");
        $stmt->bindParam(':query', $searchQuery);
        $stmt->execute();
        $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Kết hợp kết quả
        $results = array_merge($employees, $departments, $documents);

        // Sắp xếp kết quả theo độ liên quan
        usort($results, function($a, $b) use ($query) {
            $aScore = 0;
            $bScore = 0;
            
            // Tăng điểm nếu từ khóa xuất hiện trong tiêu đề
            if (stripos($a['title'], $query) !== false) $aScore += 2;
            if (stripos($b['title'], $query) !== false) $bScore += 2;
            
            // Tăng điểm nếu từ khóa xuất hiện trong mô tả
            if (stripos($a['description'], $query) !== false) $aScore += 1;
            if (stripos($b['description'], $query) !== false) $bScore += 1;
            
            return $bScore - $aScore;
        });

        echo json_encode($results);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

// Xử lý tìm kiếm nâng cao
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

        $stmt = $db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($results);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}
?> 