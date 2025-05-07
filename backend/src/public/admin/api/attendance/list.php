<?php
header('Content-Type: application/json');
require_once '../../../config/database.php';

// Lấy các tham số từ request
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$dateFilter = isset($_GET['date']) ? $_GET['date'] : 'today';
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : '';
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Tính toán ngày bắt đầu và kết thúc dựa trên bộ lọc
$dateRange = getDateRange($dateFilter, $startDate, $endDate);

// Xây dựng câu truy vấn
$query = "SELECT 
            a.attendance_id,
            a.employee_id,
            e.full_name as employee_name,
            d.name as department,
            DATE(a.attendance_date) as date,
            TIME(a.check_in_time) as check_in,
            TIME(a.check_out_time) as check_out,
            a.attendance_symbol as status,
            a.notes
          FROM attendance a
          JOIN employees e ON a.employee_id = e.id
          JOIN departments d ON e.department_id = d.id
          WHERE 1=1";

$params = [];

// Thêm điều kiện ngày
if ($dateRange['start']) {
    $query .= " AND a.attendance_date >= ?";
    $params[] = $dateRange['start'];
}
if ($dateRange['end']) {
    $query .= " AND a.attendance_date <= ?";
    $params[] = $dateRange['end'];
}

// Thêm điều kiện trạng thái
if ($status !== 'all') {
    $query .= " AND a.attendance_symbol = ?";
    $params[] = $status;
}

// Thêm điều kiện tìm kiếm
if ($search) {
    $query .= " AND (e.full_name LIKE ? OR e.employee_code LIKE ? OR e.email LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

// Thêm sắp xếp và phân trang
$query .= " ORDER BY a.attendance_date DESC, a.check_in_time DESC";
$limit = 10;
$offset = ($page - 1) * $limit;
$query .= " LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

try {
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Lấy tổng số bản ghi
    $countQuery = str_replace("SELECT a.attendance_id, a.employee_id, e.full_name as employee_name, d.name as department, DATE(a.attendance_date) as date, TIME(a.check_in_time) as check_in, TIME(a.check_out_time) as check_out, a.attendance_symbol as status, a.notes", "SELECT COUNT(*)", $query);
    $countQuery = preg_replace('/LIMIT \? OFFSET \?$/', '', $countQuery);
    $stmt = $conn->prepare($countQuery);
    $stmt->execute(array_slice($params, 0, -2));
    $totalRecords = $stmt->fetchColumn();
    $totalPages = ceil($totalRecords / $limit);

    echo json_encode([
        'success' => true,
        'records' => $records,
        'totalPages' => $totalPages,
        'currentPage' => $page
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi lấy dữ liệu chấm công: ' . $e->getMessage()
    ]);
}

// Hàm hỗ trợ tính toán khoảng thời gian
function getDateRange($filter, $startDate, $endDate) {
    $today = date('Y-m-d');
    $result = ['start' => null, 'end' => null];

    switch ($filter) {
        case 'today':
            $result['start'] = $today;
            $result['end'] = $today;
            break;
        case 'yesterday':
            $result['start'] = date('Y-m-d', strtotime('-1 day'));
            $result['end'] = $result['start'];
            break;
        case 'week':
            $result['start'] = date('Y-m-d', strtotime('monday this week'));
            $result['end'] = $today;
            break;
        case 'month':
            $result['start'] = date('Y-m-01');
            $result['end'] = $today;
            break;
        case 'custom':
            if ($startDate && $endDate) {
                $result['start'] = $startDate;
                $result['end'] = $endDate;
            }
            break;
    }

    return $result;
}
?> 