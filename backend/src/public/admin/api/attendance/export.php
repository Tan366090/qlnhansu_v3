<?php
require_once '../../../config/database.php';
require_once '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Lấy các tham số từ request
$dateFilter = isset($_GET['date']) ? $_GET['date'] : 'today';
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : '';
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Tính toán ngày bắt đầu và kết thúc dựa trên bộ lọc
$dateRange = getDateRange($dateFilter, $startDate, $endDate);

// Xây dựng câu truy vấn
$query = "SELECT 
            e.employee_code,
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

// Thêm sắp xếp
$query .= " ORDER BY a.attendance_date DESC, a.check_in_time DESC";

try {
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Tạo file Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Đặt tiêu đề cho các cột
    $sheet->setCellValue('A1', 'Mã nhân viên');
    $sheet->setCellValue('B1', 'Họ tên');
    $sheet->setCellValue('C1', 'Phòng ban');
    $sheet->setCellValue('D1', 'Ngày');
    $sheet->setCellValue('E1', 'Giờ vào');
    $sheet->setCellValue('F1', 'Giờ ra');
    $sheet->setCellValue('G1', 'Trạng thái');
    $sheet->setCellValue('H1', 'Ghi chú');

    // Định dạng tiêu đề
    $sheet->getStyle('A1:H1')->getFont()->setBold(true);
    $sheet->getStyle('A1:H1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
    $sheet->getStyle('A1:H1')->getFill()->getStartColor()->setRGB('CCCCCC');

    // Điền dữ liệu
    $row = 2;
    foreach ($records as $record) {
        $sheet->setCellValue('A' . $row, $record['employee_code']);
        $sheet->setCellValue('B' . $row, $record['employee_name']);
        $sheet->setCellValue('C' . $row, $record['department']);
        $sheet->setCellValue('D' . $row, $record['date']);
        $sheet->setCellValue('E' . $row, $record['check_in']);
        $sheet->setCellValue('F' . $row, $record['check_out']);
        $sheet->setCellValue('G' . $row, $record['status']);
        $sheet->setCellValue('H' . $row, $record['notes']);
        $row++;
    }

    // Tự động điều chỉnh độ rộng cột
    foreach (range('A', 'H') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Tạo file Excel
    $writer = new Xlsx($spreadsheet);
    $filename = 'attendance_export_' . date('Y-m-d_H-i-s') . '.xlsx';

    // Gửi file về client
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer->save('php://output');
    exit;

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi xuất dữ liệu: ' . $e->getMessage()
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