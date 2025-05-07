<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../services/DataStore.php';

use App\Services\DataStore;

$dataStore = DataStore::getInstance();

// Danh sách các bảng
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

// Lấy dữ liệu từ bảng được chọn (nếu có)
$selectedTable = $_GET['table'] ?? null;
$tableData = [];
$columns = [];

if ($selectedTable && isset($tables[$selectedTable])) {
    try {
        $tableData = $dataStore->getData($selectedTable);
        if (!empty($tableData)) {
            $columns = array_keys($tableData[0]);
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xem dữ liệu hệ thống</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/datatables@1.10.18/media/css/jquery.dataTables.min.css" rel="stylesheet">
    <style>
        .sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding: 20px;
            background-color: #f8f9fa;
            overflow-y: auto;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .table-link {
            display: block;
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            text-decoration: none;
            color: #333;
            transition: background-color 0.3s;
        }
        .table-link:hover {
            background-color: #e9ecef;
        }
        .table-link.active {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <h4 class="mb-4">Danh sách bảng</h4>
                <?php foreach ($tables as $table => $description): ?>
                    <a href="?table=<?= $table ?>" 
                       class="table-link <?= $selectedTable === $table ? 'active' : '' ?>">
                        <?= $description ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Main content -->
            <div class="col-md-9 col-lg-10 main-content">
                <?php if ($selectedTable): ?>
                    <h2 class="mb-4"><?= $tables[$selectedTable] ?></h2>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <?= $error ?>
                        </div>
                    <?php elseif (empty($tableData)): ?>
                        <div class="alert alert-info">
                            Không có dữ liệu trong bảng này.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table id="dataTable" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <?php foreach ($columns as $column): ?>
                                            <th><?= $column ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tableData as $row): ?>
                                        <tr>
                                            <?php foreach ($columns as $column): ?>
                                                <td><?= htmlspecialchars($row[$column] ?? '') ?></td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        Vui lòng chọn một bảng để xem dữ liệu.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Vietnamese.json'
                },
                pageLength: 25,
                order: [[0, 'asc']]
            });
        });
    </script>
</body>
</html> 