<?php
require_once 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ReportGenerator {
    private $conn;
    private $spreadsheet;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->spreadsheet = new Spreadsheet();
    }
    
    // Tạo báo cáo tổng hợp nhân sự
    public function generateHRReport($startDate, $endDate) {
        $sheet = $this->spreadsheet->getActiveSheet();
        $sheet->setTitle('Báo cáo nhân sự');
        
        // Tiêu đề
        $sheet->setCellValue('A1', 'BÁO CÁO TỔNG HỢP NHÂN SỰ');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Thời gian báo cáo
        $sheet->setCellValue('A2', 'Thời gian: ' . $startDate . ' - ' . $endDate);
        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Headers
        $headers = ['STT', 'Phòng ban', 'Tổng số NV', 'NV mới', 'NV nghỉ việc', 'Tỷ lệ nghỉ', 'Tỷ lệ tăng', 'Ghi chú'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '4', $header);
            $sheet->getStyle($col . '4')->getFont()->setBold(true);
            $col++;
        }
        
        // Dữ liệu
        $departments = $this->getDepartmentData($startDate, $endDate);
        $row = 5;
        foreach ($departments as $index => $dept) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $dept['name']);
            $sheet->setCellValue('C' . $row, $dept['total_employees']);
            $sheet->setCellValue('D' . $row, $dept['new_employees']);
            $sheet->setCellValue('E' . $row, $dept['leaving_employees']);
            $sheet->setCellValue('F' . $row, $dept['turnover_rate'] . '%');
            $sheet->setCellValue('G' . $row, $dept['growth_rate'] . '%');
            $sheet->setCellValue('H' . $row, $dept['notes']);
            $row++;
        }
        
        // Định dạng bảng
        $this->formatTable($sheet, 'A4:H' . ($row - 1));
        
        return $this->spreadsheet;
    }
    
    // Tạo báo cáo lương
    public function generateSalaryReport($month, $year) {
        $sheet = $this->spreadsheet->getActiveSheet();
        $sheet->setTitle('Báo cáo lương');
        
        // Tiêu đề
        $sheet->setCellValue('A1', 'BÁO CÁO LƯƠNG THÁNG ' . $month . '/' . $year);
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Headers
        $headers = ['STT', 'Mã NV', 'Họ tên', 'Phòng ban', 'Lương cơ bản', 'Phụ cấp', 'Thưởng', 'Khấu trừ', 'Thực lãnh', 'Ghi chú'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '3', $header);
            $sheet->getStyle($col . '3')->getFont()->setBold(true);
            $col++;
        }
        
        // Dữ liệu
        $salaries = $this->getSalaryData($month, $year);
        $row = 4;
        foreach ($salaries as $index => $salary) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $salary['employee_code']);
            $sheet->setCellValue('C' . $row, $salary['full_name']);
            $sheet->setCellValue('D' . $row, $salary['department_name']);
            $sheet->setCellValue('E' . $row, $salary['basic_salary']);
            $sheet->setCellValue('F' . $row, $salary['allowances']);
            $sheet->setCellValue('G' . $row, $salary['bonus']);
            $sheet->setCellValue('H' . $row, $salary['deductions']);
            $sheet->setCellValue('I' . $row, $salary['net_salary']);
            $sheet->setCellValue('J' . $row, $salary['notes']);
            $row++;
        }
        
        // Định dạng bảng
        $this->formatTable($sheet, 'A3:J' . ($row - 1));
        
        // Tổng cộng
        $sheet->setCellValue('A' . $row, 'TỔNG CỘNG');
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->setCellValue('E' . $row, '=SUM(E4:E' . ($row - 1) . ')');
        $sheet->setCellValue('F' . $row, '=SUM(F4:F' . ($row - 1) . ')');
        $sheet->setCellValue('G' . $row, '=SUM(G4:G' . ($row - 1) . ')');
        $sheet->setCellValue('H' . $row, '=SUM(H4:H' . ($row - 1) . ')');
        $sheet->setCellValue('I' . $row, '=SUM(I4:I' . ($row - 1) . ')');
        $sheet->getStyle('A' . $row . ':J' . $row)->getFont()->setBold(true);
        
        return $this->spreadsheet;
    }
    
    // Tạo báo cáo đánh giá
    public function generatePerformanceReport($quarter, $year) {
        $sheet = $this->spreadsheet->getActiveSheet();
        $sheet->setTitle('Báo cáo đánh giá');
        
        // Tiêu đề
        $sheet->setCellValue('A1', 'BÁO CÁO ĐÁNH GIÁ QUÝ ' . $quarter . ' NĂM ' . $year);
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Headers
        $headers = ['STT', 'Mã NV', 'Họ tên', 'Phòng ban', 'Điểm đánh giá', 'Xếp loại', 'Khen thưởng', 'Ghi chú'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '3', $header);
            $sheet->getStyle($col . '3')->getFont()->setBold(true);
            $col++;
        }
        
        // Dữ liệu
        $performances = $this->getPerformanceData($quarter, $year);
        $row = 4;
        foreach ($performances as $index => $perf) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $perf['employee_code']);
            $sheet->setCellValue('C' . $row, $perf['full_name']);
            $sheet->setCellValue('D' . $row, $perf['department_name']);
            $sheet->setCellValue('E' . $row, $perf['performance_score']);
            $sheet->setCellValue('F' . $row, $perf['rating']);
            $sheet->setCellValue('G' . $row, $perf['reward']);
            $sheet->setCellValue('H' . $row, $perf['notes']);
            $row++;
        }
        
        // Định dạng bảng
        $this->formatTable($sheet, 'A3:H' . ($row - 1));
        
        return $this->spreadsheet;
    }
    
    // Các phương thức hỗ trợ
    private function formatTable($sheet, $range) {
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($range)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    }
    
    private function getDepartmentData($startDate, $endDate) {
        $query = "SELECT 
                    d.name,
                    COUNT(DISTINCT e.id) as total_employees,
                    COUNT(DISTINCT CASE WHEN e.join_date BETWEEN ? AND ? THEN e.id END) as new_employees,
                    COUNT(DISTINCT CASE WHEN e.end_date BETWEEN ? AND ? THEN e.id END) as leaving_employees
                 FROM departments d
                 LEFT JOIN employees e ON d.id = e.department_id
                 WHERE d.status = 'active'
                 GROUP BY d.id, d.name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ssss', $startDate, $endDate, $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $row['turnover_rate'] = $row['total_employees'] > 0 ? 
                round(($row['leaving_employees'] / $row['total_employees']) * 100, 2) : 0;
            $row['growth_rate'] = $row['total_employees'] > 0 ? 
                round(($row['new_employees'] / $row['total_employees']) * 100, 2) : 0;
            $row['notes'] = '';
            $data[] = $row;
        }
        
        return $data;
    }
    
    private function getSalaryData($month, $year) {
        $query = "SELECT 
                    e.employee_code,
                    e.full_name,
                    d.name as department_name,
                    p.basic_salary,
                    p.allowances,
                    p.bonus,
                    p.deductions,
                    p.net_salary,
                    p.notes
                 FROM payroll p
                 JOIN employees e ON p.employee_id = e.id
                 JOIN departments d ON e.department_id = d.id
                 WHERE MONTH(p.pay_period_start) = ? AND YEAR(p.pay_period_start) = ?
                 ORDER BY d.name, e.employee_code";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ii', $month, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    private function getPerformanceData($quarter, $year) {
        $query = "SELECT 
                    e.employee_code,
                    e.full_name,
                    d.name as department_name,
                    p.performance_score,
                    p.rating,
                    p.reward,
                    p.notes
                 FROM performances p
                 JOIN employees e ON p.employee_id = e.id
                 JOIN departments d ON e.department_id = d.id
                 WHERE QUARTER(p.review_period_start) = ? AND YEAR(p.review_period_start) = ?
                 ORDER BY d.name, e.employee_code";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ii', $quarter, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?> 