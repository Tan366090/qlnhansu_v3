<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Increase memory limit for PDF generation
ini_set('memory_limit', '512M');
ini_set('max_execution_time', '300');

require_once __DIR__ . '/../../services/DataStore.php';

// Kiểm tra và load các thư viện cần thiết
try {
    $autoloadPath = __DIR__ . '/../../../../vendor/autoload.php';
    error_log("Trying to load autoload.php from: " . $autoloadPath);
    
    if (!file_exists($autoloadPath)) {
        throw new Exception('Vui lòng cài đặt các thư viện cần thiết bằng lệnh: composer require phpoffice/phpspreadsheet dompdf/dompdf');
    }
    require_once $autoloadPath;
    
    // Kiểm tra các thư viện đã được load
    if (!class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
        error_log("PhpSpreadsheet class not found");
        throw new Exception('Thư viện PhpSpreadsheet chưa được cài đặt');
    }
    if (!class_exists('Dompdf\\Dompdf')) {
        error_log("Dompdf class not found");
        throw new Exception('Thư viện Dompdf chưa được cài đặt');
    }
    
    error_log("All required libraries loaded successfully");
} catch (Exception $e) {
    error_log("Library loading error: " . $e->getMessage());
    die('<div style="padding: 20px; background-color: #ffebee; border: 1px solid #ffcdd2; border-radius: 4px; margin: 20px; font-size: 16px; color: #c62828;">
            <h3 style="margin: 0 0 10px 0; color: #c62828;">Lỗi cài đặt</h3>
            <p style="margin: 0;">' . htmlspecialchars($e->getMessage()) . '</p>
          </div>');
}

use App\Services\DataStore;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;
use Dompdf\Options;

header('Content-Type: text/html; charset=utf-8');

try {
    $dataStore = DataStore::getInstance();
    
    // Danh sách 56 bảng cần kiểm tra
    $tables = [
        'activities' => 'Hoạt động',
        'asset_assignments' => 'Phân công tài sản',
        'asset_maintenance' => 'Bảo trì tài sản',
        'assets' => 'Tài sản',
        'attendance' => 'Chấm công',
        'audit_logs' => 'Nhật ký kiểm toán',
        'backup_logs' => 'Nhật ký sao lưu',
        'benefits' => 'Phúc lợi',
        'bonuses' => 'Thưởng',
        'certificates' => 'Chứng chỉ',
        'contracts' => 'Hợp đồng',
        'degrees' => 'Bằng cấp',
        'departments' => 'Phòng ban',
        'document_versions' => 'Phiên bản tài liệu',
        'documents' => 'Tài liệu',
        'email_verification_tokens' => 'Token xác thực email',
        'employee_positions' => 'Vị trí nhân viên',
        'employees' => 'Nhân viên',
        'family_members' => 'Thành viên gia đình',
        'holidays' => 'Ngày lễ',
        'insurance' => 'Bảo hiểm',
        'interviews' => 'Phỏng vấn',
        'job_applications' => 'Đơn ứng tuyển',
        'job_positions' => 'Vị trí công việc',
        'kpi' => 'Chỉ số KPI',
        'leaves' => 'Nghỉ phép',
        'login_attempts' => 'Lần đăng nhập',
        'notifications' => 'Thông báo',
        'onboarding' => 'Quy trình nhận việc',
        'password_reset_tokens' => 'Token đặt lại mật khẩu',
        'payroll' => 'Bảng lương',
        'performances' => 'Đánh giá hiệu suất',
        'permissions' => 'Quyền hạn',
        'policies' => 'Chính sách',
        'positions' => 'Chức vụ',
        'project_resources' => 'Tài nguyên dự án',
        'project_tasks' => 'Công việc dự án',
        'projects' => 'Dự án',
        'rate_limits' => 'Giới hạn tỷ lệ',
        'recruitment_campaigns' => 'Chiến dịch tuyển dụng',
        'report_executions' => 'Thực thi báo cáo',
        'report_schedules' => 'Lịch báo cáo',
        'report_templates' => 'Mẫu báo cáo',
        'role_permissions' => 'Quyền hạn theo vai trò',
        'roles' => 'Vai trò',
        'salary_history' => 'Lịch sử lương',
        'sessions' => 'Phiên làm việc',
        'system_logs' => 'Nhật ký hệ thống',
        'system_settings' => 'Cài đặt hệ thống',
        'tasks' => 'Công việc',
        'training_courses' => 'Khóa đào tạo',
        'training_evaluations' => 'Đánh giá đào tạo',
        'training_registrations' => 'Đăng ký đào tạo',
        'user_profiles' => 'Hồ sơ người dùng',
        'users' => 'Người dùng',
        'work_schedules' => 'Lịch làm việc'
    ];
    
    $results = [];
    $totalRecords = 0;
    
    foreach ($tables as $table => $description) {
        try {
            $data = $dataStore->getData($table);
            $count = count($data);
            $totalRecords += $count;
            
            $columns = [];
            if (!empty($data)) {
                $firstRecord = $data[0];
                $columns = array_keys($firstRecord);
            }
            
            $results[$table] = [
                'description' => $description,
                'count' => $count,
                'columns' => $columns,
                'status' => $count > 0 ? 'Có dữ liệu' : 'Không có dữ liệu',
                'sample' => $data
            ];
        } catch (Exception $e) {
            $results[$table] = [
                'description' => $description,
                'error' => $e->getMessage(),
                'status' => 'Lỗi truy cập'
            ];
        }
    }
    
    $summary = [
        'total_tables' => count($tables),
        'total_records' => $totalRecords,
        'tables_with_data' => count(array_filter($results, function($r) { 
            return isset($r['count']) && $r['count'] > 0; 
        })),
        'tables_without_data' => count(array_filter($results, function($r) { 
            return isset($r['count']) && $r['count'] === 0; 
        })),
        'tables_with_errors' => count(array_filter($results, function($r) { 
            return isset($r['error']); 
        }))
    ];
    
    // Xử lý xuất PDF
    if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
        try {
            // Increase memory limit for PDF generation
            ini_set('memory_limit', '2048M');
            ini_set('max_execution_time', '300');
            
            // Check if headers are already sent
            if (headers_sent()) {
                throw new Exception('Headers đã được gửi, không thể xuất file PDF');
            }
            
            error_log("Starting PDF export...");
            
            // Build HTML content with minimal styling
            $html = '<html><head><meta charset="UTF-8"><style>
                body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
                th, td { border: 1px solid #000; padding: 4px; text-align: left; }
                th { background-color: #f2f2f2; }
                .page-break { page-break-after: always; }
                @page { size: landscape; margin: 10mm; }
            </style></head><body>';
            
            // Add overview section with limited data
            $html .= '<h1>Danh sách bảng dữ liệu</h1>';
            $html .= '<table>';
            $html .= '<tr><th>STT</th><th>Tên bảng</th><th>Mô tả</th><th>Số bản ghi</th><th>Trạng thái</th></tr>';
            
            $stt = 1;
            foreach ($results as $table => $result) {
                $html .= '<tr>';
                $html .= '<td>' . $stt++ . '</td>';
                $html .= '<td>' . htmlspecialchars($table) . '</td>';
                $html .= '<td>' . htmlspecialchars($result['description']) . '</td>';
                $html .= '<td>' . (isset($result['count']) ? $result['count'] : '-') . '</td>';
                $html .= '<td>' . (isset($result['error']) ? 'Lỗi' : ($result['count'] > 0 ? 'Có dữ liệu' : 'Trống')) . '</td>';
                $html .= '</tr>';
            }
            $html .= '</table>';
            
            // Process each table's data with limits
            foreach ($results as $table => $result) {
                if (isset($result['sample']) && !empty($result['sample'])) {
                    $html .= '<div class="page-break"></div>';
                    $html .= '<h2>Bảng: ' . htmlspecialchars($table) . '</h2>';
                    $html .= '<p><strong>Mô tả:</strong> ' . htmlspecialchars($result['description']) . '</p>';
                    $html .= '<p><strong>Số bản ghi:</strong> ' . $result['count'] . '</p>';
                    
                    $html .= '<table>';
                    
                    // Add column headers
                    if (!empty($result['sample'])) {
                        $firstRecord = $result['sample'][0];
                        $html .= '<tr>';
                        foreach ($firstRecord as $key => $value) {
                            $html .= '<th>' . htmlspecialchars($key) . '</th>';
                        }
                        $html .= '</tr>';
                        
                        // Limit the number of records to process
                        $maxRecords = 50; // Only process first 50 records
                        $recordsToProcess = array_slice($result['sample'], 0, $maxRecords);
                        
                        foreach ($recordsToProcess as $record) {
                            $html .= '<tr>';
                            foreach ($record as $value) {
                                // Truncate long values to prevent memory issues
                                $displayValue = is_string($value) ? substr($value, 0, 100) : $value;
                                $html .= '<td>' . htmlspecialchars($displayValue ?? '') . '</td>';
                            }
                            $html .= '</tr>';
                        }
                        
                        // Add note if there are more records
                        if (count($result['sample']) > $maxRecords) {
                            $html .= '<tr><td colspan="' . count($firstRecord) . '" style="text-align: center;">';
                            $html .= '... và ' . (count($result['sample']) - $maxRecords) . ' bản ghi khác';
                            $html .= '</td></tr>';
                        }
                    }
                    $html .= '</table>';
                    
                    // Clear memory after processing each table
                    unset($result['sample']);
                    gc_collect_cycles();
                }
            }
            
            $html .= '</body></html>';
            
            // Configure Dompdf with optimized settings
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isPhpEnabled', true);
            $options->set('defaultFont', 'DejaVu Sans');
            $options->set('isRemoteEnabled', false);
            $options->set('isFontSubsettingEnabled', true);
            $options->set('debugKeepTemp', false);
            $options->set('debugCss', false);
            $options->set('debugLayout', false);
            $options->set('debugLayoutLines', false);
            $options->set('debugLayoutBlocks', false);
            $options->set('debugLayoutInline', false);
            $options->set('debugLayoutPaddingBox', false);
            $options->set('isJavascriptEnabled', false);
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isPhpEnabled', true);
            
            $dompdf = new Dompdf($options);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->loadHtml($html);
            $dompdf->render();
            
            $dompdf->stream('danh_sach_bang_va_du_lieu.pdf', array('Attachment' => true));
            error_log("PDF export completed successfully");
            exit;
        } catch (Exception $e) {
            error_log("PDF export error: " . $e->getMessage());
            die('<div style="padding: 20px; background-color: #ffebee; border: 1px solid #ffcdd2; border-radius: 4px; margin: 20px; font-size: 16px; color: #c62828;">
                    <h3 style="margin: 0 0 10px 0; color: #c62828;">Lỗi xuất PDF</h3>
                    <p style="margin: 0;">' . htmlspecialchars($e->getMessage()) . '</p>
                  </div>');
        }
    }
    
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Kiểm tra dữ liệu hệ thống</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/atom-one-dark.min.css">
        <link rel="stylesheet" href="../assets/css/font-awesome/all.min.css">
        <style>
            @font-face {
                font-family: 'Font Awesome 6 Free';
                font-style: normal;
                font-weight: 900;
                src: url('../assets/css/font-awesome/webfonts/fa-solid-900.woff2') format('woff2'),
                     url('../assets/css/font-awesome/webfonts/fa-solid-900.ttf') format('truetype');
            }

            :root {
                --primary-color: #1a73e8;
                --primary-hover: #1557b0;
                --secondary-color: #5f6368;
                --success-color: #34a853;
                --warning-color: #fbbc05;
                --danger-color: #ea4335;
                --light-bg: #f8f9fa;
                --border-color: #dadce0;
                --modal-bg: rgba(0, 0, 0, 0.5);
                --modal-content-bg: #ffffff;
                --modal-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
                --card-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
                --transition: all 0.2s ease-in-out;
            }

            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: 'Google Sans', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                line-height: 1.6;
                color: #202124;
                background-color: var(--light-bg);
                margin: 0;
                padding: 0;
            }

            .container {
                max-width: 1400px;
                margin: 0 auto;
                padding: 20px;
            }

            .header {
                background: linear-gradient(135deg, #1a73e8 0%, #1557b0 100%);
                padding: 30px;
                border-radius: 12px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                margin-bottom: 30px;
                color: white;
                position: relative;
                overflow: hidden;
            }

            .header::before {
                content: '';
                position: absolute;
                top: 0;
                right: 0;
                width: 200px;
                height: 200px;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 50%;
                transform: translate(50%, -50%);
            }

            .header h1 {
                font-size: 28px;
                font-weight: 600;
                margin-bottom: 10px;
                position: relative;
            }

            .header p {
                font-size: 16px;
                opacity: 0.9;
                position: relative;
            }

            .back-btn {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 8px 16px;
                background-color: rgba(255, 255, 255, 0.2);
                color: white;
                text-decoration: none;
                border-radius: 4px;
                margin-top: 15px;
                transition: all 0.3s ease;
            }

            .back-btn:hover {
                background-color: rgba(255, 255, 255, 0.3);
                transform: translateX(-3px);
            }

            .back-btn i {
                font-size: 14px;
            }

            .summary-cards {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
            }

            .summary-card {
                background: white;
                padding: 25px;
                border-radius: 12px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
                transition: all 0.3s ease;
                position: relative;
                overflow: hidden;
                border: 1px solid rgba(0, 0, 0, 0.05);
            }

            .summary-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            }

            .summary-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 4px;
                height: 100%;
                background: var(--primary-color);
            }

            .summary-card h3 {
                color: var(--secondary-color);
                font-size: 14px;
                font-weight: 500;
                margin-bottom: 15px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .summary-card .value {
                font-size: 32px;
                font-weight: 600;
                color: var(--primary-color);
                margin-bottom: 10px;
            }

            .summary-card:nth-child(1)::before { background: #1a73e8; }
            .summary-card:nth-child(2)::before { background: #34a853; }
            .summary-card:nth-child(3)::before { background: #fbbc05; }
            .summary-card:nth-child(4)::before { background: #ea4335; }
            .summary-card:nth-child(5)::before { background: #5f6368; }

            .summary-card:nth-child(1) .value { color: #1a73e8; }
            .summary-card:nth-child(2) .value { color: #34a853; }
            .summary-card:nth-child(3) .value { color: #fbbc05; }
            .summary-card:nth-child(4) .value { color: #ea4335; }
            .summary-card:nth-child(5) .value { color: #5f6368; }

            .controls {
                background: white;
                padding: 20px;
                border-radius: 8px;
                box-shadow: var(--card-shadow);
                margin-bottom: 20px;
                display: flex;
                flex-wrap: wrap;
                gap: 15px;
                align-items: center;
            }

            .search-box {
                flex: 1;
                min-width: 250px;
                position: relative;
            }

            .search-box input {
                width: 100%;
                padding: 10px 15px;
                padding-left: 40px;
                border: 1px solid var(--border-color);
                border-radius: 4px;
                font-size: 14px;
                transition: var(--transition);
            }

            .search-box input:focus {
                outline: none;
                border-color: var(--primary-color);
                box-shadow: 0 0 0 2px rgba(26, 115, 232, 0.1);
            }

            .search-box i {
                position: absolute;
                left: 15px;
                top: 50%;
                transform: translateY(-50%);
                color: var(--secondary-color);
                font-family: 'Font Awesome 6 Free';
                font-weight: 900;
                content: '\f002';
            }

            .filter-controls {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
            }

            .filter-select {
                padding: 10px 15px;
                border: 1px solid var(--border-color);
                border-radius: 4px;
                background-color: white;
                min-width: 150px;
                font-size: 14px;
                color: var(--secondary-color);
                cursor: pointer;
                transition: var(--transition);
            }

            .filter-select:focus {
                outline: none;
                border-color: var(--primary-color);
            }

            .export-buttons {
                display: flex;
                gap: 10px;
            }

            .export-btn {
                background-color: var(--primary-color);
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 4px;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 14px;
                transition: var(--transition);
            }

            .export-btn::before {
                content: '\f019';
                font-family: 'Font Awesome 6 Free';
                font-weight: 900;
            }

            .export-btn:hover {
                background-color: var(--primary-hover);
            }

            /* Loading animation styles */
            .loader {
                animation: rotate 1s infinite;
                height: 20px;
                width: 20px;
                display: none;
            }

            .loader:before,
            .loader:after {
                border-radius: 50%;
                content: "";
                display: block;
                height: 8px;
                width: 8px;
            }

            .loader:before {
                animation: ball1 1s infinite;
                background-color: #fff;
                box-shadow: 12px 0 0 #ff3d00;
                margin-bottom: 4px;
            }

            .loader:after {
                animation: ball2 1s infinite;
                background-color: #ff3d00;
                box-shadow: 12px 0 0 #fff;
            }

            @keyframes rotate {
                0% { transform: rotate(0deg) scale(0.8) }
                50% { transform: rotate(360deg) scale(1.2) }
                100% { transform: rotate(720deg) scale(0.8) }
            }

            @keyframes ball1 {
                0% {
                    box-shadow: 12px 0 0 #ff3d00;
                }
                50% {
                    box-shadow: 0 0 0 #ff3d00;
                    margin-bottom: 0;
                    transform: translate(6px, 6px);
                }
                100% {
                    box-shadow: 12px 0 0 #ff3d00;
                    margin-bottom: 4px;
                }
            }

            @keyframes ball2 {
                0% {
                    box-shadow: 12px 0 0 #fff;
                }
                50% {
                    box-shadow: 0 0 0 #fff;
                    margin-top: -8px;
                    transform: translate(6px, 6px);
                }
                100% {
                    box-shadow: 12px 0 0 #fff;
                    margin-top: 0;
                }
            }

            .export-btn.loading {
                pointer-events: none;
                opacity: 0.7;
            }

            .export-btn.loading .loader {
                display: inline-block;
                margin-right: 8px;
            }

            .export-btn.loading span {
                display: none;
            }

            .table-container {
                background: white;
                border-radius: 8px;
                box-shadow: var(--card-shadow);
                overflow: hidden;
            }

            .data-table {
                width: 100%;
                border-collapse: collapse;
            }

            .data-table th {
                background-color: #f8f9fa;
                color: var(--secondary-color);
                padding: 12px 15px;
                text-align: left;
                font-weight: 500;
                font-size: 16px;
                border-bottom: 1px solid var(--border-color);
                cursor: pointer;
                user-select: none;
                transition: var(--transition);
            }

            .data-table th:hover {
                background-color: #f1f3f4;
            }

            .data-table th i {
                margin-left: 5px;
                opacity: 0.5;
                transition: var(--transition);
                font-family: 'Font Awesome 6 Free';
                font-weight: 900;
            }

            .data-table th.asc i {
                content: '\f0de';
            }

            .data-table th.desc i {
                content: '\f0dd';
            }

            .data-table td {
                padding: 12px 15px;
                border-bottom: 1px solid var(--border-color);
                font-size: 16px;
            }

            .data-table tr:last-child td {
                border-bottom: none;
            }

            .data-table tr:hover {
                background-color: #f8f9fa;
            }

            .status-badge {
                display: inline-block;
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 12px;
                font-weight: 500;
            }

            .status-success {
                background-color: rgba(52, 168, 83, 0.1);
                color: var(--success-color);
            }

            .status-error {
                background-color: rgba(234, 67, 53, 0.1);
                color: var(--danger-color);
            }

            .status-empty {
                background-color: rgba(251, 188, 5, 0.1);
                color: var(--warning-color);
            }

            .pagination {
                display: flex;
                justify-content: center;
                gap: 5px;
                margin-top: 20px;
            }

            .pagination button {
                padding: 8px 12px;
                border: 1px solid var(--border-color);
                background: white;
                border-radius: 4px;
                cursor: pointer;
                transition: var(--transition);
                font-size: 14px;
                color: var(--secondary-color);
            }

            .pagination button:hover:not(:disabled) {
                background-color: #f1f3f4;
                border-color: var(--primary-color);
                color: var(--primary-color);
            }

            .pagination button.active {
                background-color: var(--primary-color);
                color: white;
                border-color: var(--primary-color);
            }

            .pagination button:disabled {
                opacity: 0.5;
                cursor: not-allowed;
            }

            /* Modal styles */
            .modal {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: var(--modal-bg);
                z-index: 1000;
                justify-content: center;
                align-items: center;
                opacity: 0;
                transition: opacity 0.3s ease;
            }

            .modal.active {
                display: flex;
                opacity: 1;
            }

            .modal-content {
                background-color: var(--modal-content-bg);
                border-radius: 8px;
                box-shadow: var(--modal-shadow);
                width: 90%;
                max-width: 800px;
                max-height: 90vh;
                overflow: auto;
                position: relative;
                transform: translateY(-20px);
                transition: transform 0.3s ease;
            }

            .modal.active .modal-content {
                transform: translateY(0);
            }

            .modal-header {
                padding: 20px;
                border-bottom: 1px solid var(--border-color);
            }

            .modal-title {
                color: var(--primary-color);
                font-size: 20px;
                font-weight: 500;
            }

            .close-modal {
                position: absolute;
                top: 15px;
                right: 15px;
                background: none;
                border: none;
                color: var(--secondary-color);
                font-size: 20px;
                cursor: pointer;
                transition: var(--transition);
                font-family: 'Font Awesome 6 Free';
                font-weight: 900;
                content: '\f00d';
            }

            .close-modal:hover {
                color: var(--danger-color);
            }

            .table-details {
                width: 100%;
                margin: 20px;
                border-collapse: collapse;
            }

            .table-details th {
                text-align: left;
                padding: 10px;
                width: 150px;
                color: var(--secondary-color);
                font-weight: 500;
            }

            .table-details td {
                padding: 10px;
                border-bottom: 1px solid var(--border-color);
            }

            .sample-data-container {
                margin: 20px;
            }

            .sample-data-title {
                font-weight: 500;
                margin-bottom: 10px;
                color: var(--secondary-color);
            }

            .sample-data-content {
                background-color: #f8f9fa;
                padding: 15px;
                border-radius: 4px;
                overflow-x: auto;
                max-height: 300px;
            }

            .json-view {
                font-family: 'Consolas', 'Monaco', monospace;
                font-size: 13px;
                line-height: 1.5;
            }

            .view-sample-btn {
                background-color: var(--primary-color);
                color: white;
                border: none;
                padding: 6px 12px;
                border-radius: 4px;
                cursor: pointer;
                font-size: 13px;
                transition: var(--transition);
            }

            .view-sample-btn:hover {
                background-color: var(--primary-hover);
            }

            .sample-modal {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1000;
                justify-content: center;
                align-items: center;
            }

            .sample-modal.active {
                display: flex;
            }

            .sample-modal-content {
                background-color: white;
                padding: 20px;
                border-radius: 8px;
                max-width: 90%;
                max-height: 90vh;
                overflow: auto;
                position: relative;
            }

            .sample-modal-close {
                position: absolute;
                top: 10px;
                right: 10px;
                background: none;
                border: none;
                font-size: 30px;
                cursor: pointer;
                color: #ea4335;
                width: 50px;
                height: 50px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                transition: all 0.3s ease;
            }

            .sample-modal-close:hover {
                background-color: rgba(234, 67, 53, 0.1);
                transform: rotate(90deg);
            }

            .sample-modal-title {
                margin-bottom: 20px;
                color: var(--primary-color);
                font-size: 18px;
                font-weight: 500;
            }

            .sample-table-container {
                max-height: 70vh;
                overflow-y: auto;
            }

            .sample-table {
                width: 100%;
                border-collapse: collapse;
            }

            .sample-table th {
                position: sticky;
                top: 0;
                background: white;
                z-index: 1;
            }

            .sample-table th,
            .sample-table td {
                padding: 10px;
                border: 1px solid var(--border-color);
                text-align: left;
                white-space: nowrap;
            }

            .sample-table tr:hover {
                background-color: #f8f9fa;
            }

            .pagination-controls {
                display: flex;
                justify-content: center;
                align-items: center;
                margin-top: 20px;
                gap: 10px;
            }

            .pagination-btn {
                padding: 8px 12px;
                border: 1px solid var(--border-color);
                background: white;
                border-radius: 4px;
                cursor: pointer;
                transition: var(--transition);
            }

            .pagination-btn:hover:not(:disabled) {
                background-color: #f1f3f4;
                border-color: var(--primary-color);
                color: var(--primary-color);
            }

            .pagination-btn:disabled {
                opacity: 0.5;
                cursor: not-allowed;
            }

            .page-info {
                color: var(--secondary-color);
                font-size: 14px;
            }

            @media (max-width: 768px) {
                .controls {
                    flex-direction: column;
                }

                .search-box {
                    width: 100%;
                }

                .filter-controls {
                    width: 100%;
                }

                .filter-select {
                    flex: 1;
                }

                .export-buttons {
                    width: 100%;
                    justify-content: center;
                }

                .data-table {
                    display: block;
                    overflow-x: auto;
                }

                .modal-content {
                    width: 95%;
                    margin: 10px;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Kiểm tra dữ liệu hệ thống</h1>
                <p>Quản lý và theo dõi trạng thái dữ liệu của các bảng trong hệ thống</p>
                <a href="dashboard_admin_V1.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Quay lại trang chủ
                </a>
            </div>

            <div class="summary-cards">
                <div class="summary-card">
                    <h3>Tổng số bảng</h3>
                    <div class="value"><?php echo $summary['total_tables']; ?></div>
                </div>
                <div class="summary-card">
                    <h3>Tổng số bản ghi</h3>
                    <div class="value"><?php echo $summary['total_records']; ?></div>
                </div>
                <div class="summary-card">
                    <h3>Bảng có dữ liệu</h3>
                    <div class="value"><?php echo $summary['tables_with_data']; ?></div>
                </div>
                <div class="summary-card">
                    <h3>Bảng trống</h3>
                    <div class="value"><?php echo $summary['tables_without_data']; ?></div>
                </div>
                <div class="summary-card">
                    <h3>Bảng lỗi</h3>
                    <div class="value"><?php echo $summary['tables_with_errors']; ?></div>
                </div>
            </div>

            <div class="controls">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Tìm kiếm bảng...">
                </div>
                <div class="filter-controls">
                    <select class="filter-select" id="statusFilter">
                        <option value="">Tất cả trạng thái</option>
                        <option value="Có dữ liệu">Có dữ liệu</option>
                        <option value="Không có dữ liệu">Không có dữ liệu</option>
                        <option value="Lỗi truy cập">Lỗi truy cập</option>
                    </select>
                    <select class="filter-select" id="countFilter">
                        <option value="">Tất cả số lượng</option>
                        <option value="0">Không có bản ghi</option>
                        <option value="1-10">1-10 bản ghi</option>
                        <option value="11-100">11-100 bản ghi</option>
                        <option value="101+">Hơn 100 bản ghi</option>
                    </select>
                </div>
                <div class="export-buttons">
             
                    <button class="export-btn" id="exportPdfBtn">
                        <span class="loader"></span>
                        <span>Xuất PDF</span>
                    </button>
                </div>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th class="sortable" data-sort="name">Tên bảng <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-sort="description">Mô tả <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-sort="count">Số bản ghi <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-sort="status">Trạng thái <i class="fas fa-sort"></i></th>
                            <th>Dữ liệu mẫu</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php 
                        $stt = 1;
                        foreach ($results as $table => $result): 
                        ?>
                        <tr class="clickable-row" data-table="<?php echo htmlspecialchars($table); ?>">
                            <td><?php echo $stt++; ?></td>
                            <td>
                                <span class="table-name"><?php echo htmlspecialchars($table); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($result['description']); ?></td>
                            <td><?php echo isset($result['count']) ? $result['count'] : '-'; ?></td>
                            <td>
                                <?php if (isset($result['error'])): ?>
                                    <span class="status-badge status-error">Lỗi</span>
                                <?php elseif ($result['count'] > 0): ?>
                                    <span class="status-badge status-success">Có dữ liệu</span>
                                <?php else: ?>
                                    <span class="status-badge status-empty">Trống</span>
                                <?php endif; ?>
                            </td>
                            <td class="sample-data">
                                <?php 
                                if (isset($result['sample']) && !empty($result['sample'])) {
                                    echo '<button class="view-sample-btn" data-table="' . htmlspecialchars($table) . '">Xem dữ liệu</button>';
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination" id="pagination">
                <!-- Pagination buttons will be added here by JavaScript -->
            </div>
        </div>

        <!-- Modal hiển thị chi tiết bảng -->
        <div class="modal" id="tableModal">
            <div class="modal-content">
                <button class="close-modal" id="closeModal">&times;</button>
                <div class="modal-header">
                    <h2 class="modal-title" id="modalTitle">Chi tiết bảng</h2>
                </div>
                
                <table class="table-details">
                    <tr>
                        <th>Tên bảng:</th>
                        <td id="detailTableName"></td>
                    </tr>
                    <tr>
                        <th>Mô tả:</th>
                        <td id="detailDescription"></td>
                    </tr>
                    <tr>
                        <th>Số bản ghi:</th>
                        <td id="detailRecordCount"></td>
                    </tr>
                    <tr>
                        <th>Trạng thái:</th>
                        <td id="detailStatus"></td>
                    </tr>
                    <tr>
                        <th>Các cột:</th>
                        <td id="detailColumns"></td>
                    </tr>
                </table>
                
                <div class="sample-data-container">
                    <div class="sample-data-title">Dữ liệu mẫu:</div>
                    <div class="sample-data-content">
                        <pre class="json-view" id="detailSampleData"></pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal hiển thị dữ liệu mẫu -->
        <div class="sample-modal" id="sampleModal">
            <div class="sample-modal-content">
                <button class="sample-modal-close">&times;</button>
                <div class="table-info">
                    <h3 class="sample-modal-title" id="modalTableName"></h3>
                    <div class="table-details">
                        <div class="detail-row">
                            <span class="detail-label">Mô tả:</span>
                            <span class="detail-value" id="modalTableDescription"></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Số bản ghi:</span>
                            <span class="detail-value" id="modalTableCount"></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Trạng thái:</span>
                            <span class="detail-value" id="modalTableStatus"></span>
                        </div>
                    </div>
                </div>
                <div class="sample-table-container">
                    <h4 class="sample-table-title">Dữ liệu bảng</h4>
                    <table class="sample-table">
                        <thead>
                            <tr id="sampleTableHeader"></tr>
                        </thead>
                        <tbody id="sampleTableBody"></tbody>
                    </table>
                </div>
                <div class="pagination-controls">
                    <button class="pagination-btn" id="prevPage" disabled>Trước</button>
                    <span class="page-info" id="pageInfo">Trang 1 / 1</span>
                    <button class="pagination-btn" id="nextPage" disabled>Tiếp</button>
                </div>
            </div>
        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.3.0/papaparse.min.js"></script>
        <script src="js/table-manager.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Khởi tạo dữ liệu cho table-manager.js
                const tableData = <?php echo json_encode($results); ?>;
                const rows = document.querySelectorAll('.clickable-row');
                const modal = document.getElementById('tableModal');
                const closeModal = document.getElementById('closeModal');
                const searchInput = document.getElementById('searchInput');
                const statusFilter = document.getElementById('statusFilter');
                const countFilter = document.getElementById('countFilter');
                const exportPdfBtn = document.getElementById('exportPdfBtn');
                const tableBody = document.getElementById('tableBody');
                const pagination = document.getElementById('pagination');

                // Xử lý sự kiện xuất PDF
                exportPdfBtn.addEventListener('click', function() {
                    // Hiển thị trạng thái loading
                    exportPdfBtn.classList.add('loading');
                    exportPdfBtn.disabled = true;

                    // Tạo iframe ẩn để tải file
                    const iframe = document.createElement('iframe');
                    iframe.style.display = 'none';
                    document.body.appendChild(iframe);

                    // Thêm sự kiện load để biết khi nào file đã tải xong
                    iframe.onload = function() {
                        // Xóa trạng thái loading
                        exportPdfBtn.classList.remove('loading');
                        exportPdfBtn.disabled = false;
                        // Xóa iframe
                        document.body.removeChild(iframe);
                    };

                    // Chuyển hướng iframe đến URL xuất PDF
                    iframe.src = window.location.href + '?export=pdf';
                });

                // Khởi tạo TableManager
                const tableManager = new TableManager({
                    tableData,
                    rows,
                    modal,
                    closeModal,
                    searchInput,
                    statusFilter,
                    countFilter,
                    exportPdfBtn,
                    tableBody,
                    pagination
                });

                // Khởi tạo syntax highlighting
                if (typeof hljs !== 'undefined') {
                    hljs.highlightAll();
                }
            });
        </script>
    </body>
    </html>
    <?php
    
} catch (Exception $e) {
    echo '<div style="padding: 20px; background-color: #ffebee; border: 1px solid #ffcdd2; border-radius: 4px; margin: 20px; font-size: 16px; color: #c62828;">
            <h3 style="margin: 0 0 10px 0; color: #c62828;">Đã xảy ra lỗi</h3>
            <p style="margin: 0;">' . htmlspecialchars($e->getMessage()) . '</p>
          </div>';
}
?> 