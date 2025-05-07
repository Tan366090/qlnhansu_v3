<?php
// Start output buffering
ob_start();

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once '../../config/database.php';
require_once '../../middleware/auth.php';
require_once '../../vendor/autoload.php'; // Require PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

try {
    // Verify user is logged in
    Auth::requireAuth();

    $database = new Database();
    $db = $database->getConnection();

    // Get filter parameters
    $startDate = $_GET['start_date'] ?? null;
    $endDate = $_GET['end_date'] ?? null;
    $status = $_GET['status'] ?? null;
    $leaveType = $_GET['leave_type'] ?? null;
    $employeeId = $_GET['employee_id'] ?? null;

    // Build query
    $query = "
        SELECT 
            l.id,
            l.employee_id,
            e.employee_code,
            up.full_name as employee_name,
            d.name as department_name,
            l.leave_type,
            l.start_date,
            l.end_date,
            l.leave_duration_days,
            l.reason,
            l.status,
            up2.full_name as approver_name,
            l.approver_comments,
            l.created_at,
            l.updated_at
        FROM leaves l
        JOIN employees e ON l.employee_id = e.id
        JOIN user_profiles up ON e.user_id = up.user_id
        JOIN departments d ON e.department_id = d.id
        LEFT JOIN users u ON l.approved_by_user_id = u.user_id
        LEFT JOIN user_profiles up2 ON u.user_id = up2.user_id
        WHERE 1=1
    ";

    $params = [];

    if ($startDate && $endDate) {
        $query .= " AND l.start_date BETWEEN :start_date AND :end_date";
        $params[':start_date'] = $startDate;
        $params[':end_date'] = $endDate;
    }

    if ($status) {
        $query .= " AND l.status = :status";
        $params[':status'] = $status;
    }

    if ($leaveType) {
        $query .= " AND l.leave_type = :leave_type";
        $params[':leave_type'] = $leaveType;
    }

    if ($employeeId) {
        $query .= " AND l.employee_id = :employee_id";
        $params[':employee_id'] = $employeeId;
    }

    $query .= " ORDER BY l.created_at DESC";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $leaves = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Create new Spreadsheet object
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set document properties
    $spreadsheet->getProperties()
        ->setCreator('HR System')
        ->setLastModifiedBy('HR System')
        ->setTitle('Leave Requests Report')
        ->setSubject('Leave Requests Report')
        ->setDescription('Leave requests report generated on ' . date('Y-m-d H:i:s'));

    // Set column headers
    $headers = [
        'A1' => 'Mã đơn',
        'B1' => 'Mã nhân viên',
        'C1' => 'Tên nhân viên',
        'D1' => 'Phòng ban',
        'E1' => 'Loại nghỉ phép',
        'F1' => 'Ngày bắt đầu',
        'G1' => 'Ngày kết thúc',
        'H1' => 'Số ngày',
        'I1' => 'Lý do',
        'J1' => 'Trạng thái',
        'K1' => 'Người duyệt',
        'L1' => 'Ý kiến phê duyệt',
        'M1' => 'Ngày tạo',
        'N1' => 'Ngày cập nhật'
    ];

    foreach ($headers as $cell => $value) {
        $sheet->setCellValue($cell, $value);
    }

    // Style the header row
    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
        ],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => '4C00FC'],
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
            ],
        ],
    ];

    $sheet->getStyle('A1:N1')->applyFromArray($headerStyle);

    // Add data rows
    $row = 2;
    foreach ($leaves as $leave) {
        $sheet->setCellValue('A' . $row, $leave['id']);
        $sheet->setCellValue('B' . $row, $leave['employee_code']);
        $sheet->setCellValue('C' . $row, $leave['employee_name']);
        $sheet->setCellValue('D' . $row, $leave['department_name']);
        $sheet->setCellValue('E' . $row, $leave['leave_type']);
        $sheet->setCellValue('F' . $row, date('d/m/Y H:i', strtotime($leave['start_date'])));
        $sheet->setCellValue('G' . $row, date('d/m/Y H:i', strtotime($leave['end_date'])));
        $sheet->setCellValue('H' . $row, $leave['leave_duration_days']);
        $sheet->setCellValue('I' . $row, $leave['reason']);
        $sheet->setCellValue('J' . $row, $leave['status']);
        $sheet->setCellValue('K' . $row, $leave['approver_name']);
        $sheet->setCellValue('L' . $row, $leave['approver_comments']);
        $sheet->setCellValue('M' . $row, date('d/m/Y H:i', strtotime($leave['created_at'])));
        $sheet->setCellValue('N' . $row, date('d/m/Y H:i', strtotime($leave['updated_at'])));
        $row++;
    }

    // Auto-size columns
    foreach (range('A', 'N') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Style data rows
    $dataStyle = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
            ],
        ],
        'alignment' => [
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
    ];

    $sheet->getStyle('A2:N' . ($row - 1))->applyFromArray($dataStyle);

    // Set headers for download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="leave_requests_' . date('Y-m-d') . '.xlsx"');
    header('Cache-Control: max-age=0');

    // Save file to PHP output
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (Exception $e) {
    // Clear output buffer and return error
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
    exit;
}
?> 