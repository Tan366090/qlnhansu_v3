<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../services/DataStore.php';

use App\Services\DataStore;

header('Content-Type: application/json');

try {
    $dataStore = DataStore::getInstance();
    
    // Danh sách 33 bảng cần kiểm tra
    $tables = [
        'employees' => 'Nhân viên',
        'departments' => 'Phòng ban',
        'positions' => 'Chức vụ',
        'performances' => 'Đánh giá hiệu suất',
        'payroll' => 'Bảng lương',
        'leaves' => 'Nghỉ phép',
        'trainings' => 'Đào tạo',
        'tasks' => 'Công việc',
        'contracts' => 'Hợp đồng',
        'certificates' => 'Bằng cấp',
        'equipment' => 'Thiết bị',
        'documents' => 'Tài liệu',
        'projects' => 'Dự án',
        'recruitment' => 'Tuyển dụng',
        'benefits' => 'Phúc lợi',
        'attendance' => 'Chấm công',
        'salaries' => 'Lương',
        'evaluations' => 'Đánh giá',
        'kpi' => 'KPI',
        'insurance' => 'Bảo hiểm',
        'policies' => 'Chính sách',
        'onboarding' => 'Onboarding',
        'interviews' => 'Phỏng vấn',
        'candidates' => 'Ứng viên',
        'job_positions' => 'Vị trí công việc',
        'training_courses' => 'Khóa đào tạo',
        'training_registrations' => 'Đăng ký đào tạo',
        'training_evaluations' => 'Đánh giá đào tạo',
        'project_tasks' => 'Công việc dự án',
        'project_resources' => 'Tài nguyên dự án',
        'equipment_assignments' => 'Cấp phát thiết bị',
        'document_versions' => 'Phiên bản tài liệu',
        'system_logs' => 'Nhật ký hệ thống'
    ];
    
    $results = [];
    
    foreach ($tables as $table => $description) {
        try {
            $exists = $dataStore->tableExists($table);
            $results[$table] = [
                'description' => $description,
                'exists' => $exists,
                'status' => $exists ? 'Bảng tồn tại' : 'Bảng không tồn tại'
            ];
        } catch (Exception $e) {
            $results[$table] = [
                'description' => $description,
                'error' => $e->getMessage(),
                'status' => 'Lỗi kiểm tra'
            ];
        }
    }
    
    // Tổng hợp kết quả
    $summary = [
        'total_tables' => count($tables),
        'tables_exist' => count(array_filter($results, function($r) { 
            return isset($r['exists']) && $r['exists']; 
        })),
        'tables_not_exist' => count(array_filter($results, function($r) { 
            return isset($r['exists']) && !$r['exists']; 
        })),
        'tables_with_errors' => count(array_filter($results, function($r) { 
            return isset($r['error']); 
        }))
    ];
    
    echo json_encode([
        'success' => true,
        'summary' => $summary,
        'details' => $results
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Kiểm tra bảng thất bại: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?> 