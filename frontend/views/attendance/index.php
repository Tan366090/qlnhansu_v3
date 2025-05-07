<?php
require_once '../../config/config.php';
require_once '../../controllers/AttendanceController.php';

$attendanceController = new AttendanceController($db);

// Get filters from request
$filters = [
    'start_date' => $_GET['start_date'] ?? '',
    'end_date' => $_GET['end_date'] ?? '',
    'employee_code' => $_GET['employee_code'] ?? '',
    'full_name' => $_GET['full_name'] ?? ''
];

// Get attendance list
$attendanceList = $attendanceController->getAttendanceList($filters);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://code.jquery.com; img-src 'self' data:;">
    <title>Quản lý chấm công</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container-fluid py-4">
        <h2 class="mb-4">Quản lý chấm công</h2>
        
        <!-- HR Statistics Chart -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Thống kê nhân sự</h5>
                        <canvas id="hrChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Từ ngày</label>
                        <input type="date" class="form-control" name="start_date" value="<?= $filters['start_date'] ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Đến ngày</label>
                        <input type="date" class="form-control" name="end_date" value="<?= $filters['end_date'] ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Mã nhân viên</label>
                        <input type="text" class="form-control" name="employee_code" value="<?= $filters['employee_code'] ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Họ tên</label>
                        <input type="text" class="form-control" name="full_name" value="<?= $filters['full_name'] ?>">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Tìm kiếm
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i> Làm mới
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Attendance List -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Ngày</th>
                                <th>Mã NV</th>
                                <th>Họ tên</th>
                                <th>Giờ vào</th>
                                <th>Giờ ra</th>
                                <th>Thời gian làm việc</th>
                                <th>Trạng thái</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendanceList as $attendance): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($attendance['attendance_date'])) ?></td>
                                <td><?= htmlspecialchars($attendance['employee_code']) ?></td>
                                <td><?= htmlspecialchars($attendance['full_name']) ?></td>
                                <td><?= $attendance['check_in_time'] ? date('H:i', strtotime($attendance['check_in_time'])) : '-' ?></td>
                                <td><?= $attendance['check_out_time'] ? date('H:i', strtotime($attendance['check_out_time'])) : '-' ?></td>
                                <td><?= $attendance['work_duration_hours'] ? $attendance['work_duration_hours'] . ' giờ' : '-' ?></td>
                                <td>
                                    <?php
                                    $statusClass = '';
                                    switch ($attendance['attendance_symbol']) {
                                        case 'P':
                                            $statusClass = 'success';
                                            $statusText = 'Có mặt';
                                            break;
                                        case 'A':
                                            $statusClass = 'danger';
                                            $statusText = 'Vắng mặt';
                                            break;
                                        case 'L':
                                            $statusClass = 'warning';
                                            $statusText = 'Nghỉ phép';
                                            break;
                                        case 'WFH':
                                            $statusClass = 'info';
                                            $statusText = 'Làm việc từ xa';
                                            break;
                                        default:
                                            $statusClass = 'secondary';
                                            $statusText = 'Chưa xác định';
                                    }
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>"><?= $statusText ?></span>
                                </td>
                                <td><?= htmlspecialchars($attendance['notes'] ?? '') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Fetch HR data from API
            fetch('../../api/dashboard.php')
                .then(response => {
                    console.log('API Response status:', response.status);
                    console.log('API Response headers:', response.headers);
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('API Response data:', data);
                    
                    if (data.success) {
                        const hrData = data.data.hr;
                        console.log('HR Data:', hrData);
                        
                        const chartContainer = document.getElementById('hrChart');
                        if (!chartContainer) {
                            console.error('Chart container not found');
                            return;
                        }
                        
                        // Create pie chart
                        const ctx = chartContainer.getContext('2d');
                        new Chart(ctx, {
                            type: 'pie',
                            data: {
                                labels: ['Nhân viên đang làm việc', 'Nhân viên nghỉ việc', 'Nhân viên thử việc'],
                                datasets: [{
                                    data: [
                                        parseInt(hrData.active_employees),
                                        parseInt(hrData.inactive_employees),
                                        parseInt(hrData.probation_employees)
                                    ],
                                    backgroundColor: [
                                        'rgba(75, 192, 192, 0.7)',
                                        'rgba(255, 99, 132, 0.7)',
                                        'rgba(255, 206, 86, 0.7)'
                                    ],
                                    borderColor: [
                                        'rgba(75, 192, 192, 1)',
                                        'rgba(255, 99, 132, 1)',
                                        'rgba(255, 206, 86, 1)'
                                    ],
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        position: 'bottom'
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                const label = context.label || '';
                                                const value = context.raw || 0;
                                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                const percentage = Math.round((value / total) * 100);
                                                return `${label}: ${value} người (${percentage}%)`;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    } else {
                        console.error('API returned unsuccessful response:', data);
                        const errorMessage = data.message || 'Không thể tải dữ liệu nhân sự';
                        const chartContainer = document.getElementById('hrChart');
                        if (chartContainer && chartContainer.parentElement) {
                            chartContainer.parentElement.innerHTML = `
                                <div class="alert alert-danger">
                                    <h5>Lỗi khi tải dữ liệu</h5>
                                    <p>${errorMessage}</p>
                                    <p>Mã lỗi: ${data.error_code || 'Không xác định'}</p>
                                </div>
                            `;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching HR data:', error);
                    const chartContainer = document.getElementById('hrChart');
                    if (chartContainer && chartContainer.parentElement) {
                        chartContainer.parentElement.innerHTML = `
                            <div class="alert alert-danger">
                                <h5>Lỗi kết nối</h5>
                                <p>Không thể kết nối đến máy chủ</p>
                                <p>Chi tiết lỗi: ${error.message}</p>
                                <p>Vui lòng kiểm tra:</p>
                                <ul>
                                    <li>Đường dẫn API: ../../api/dashboard.php</li>
                                    <li>Kết nối mạng</li>
                                    <li>Console để xem thông tin lỗi chi tiết</li>
                                </ul>
                            </div>
                        `;
                    }
                });
        });
    </script>
</body>
</html> 