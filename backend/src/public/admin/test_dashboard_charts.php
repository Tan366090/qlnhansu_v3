<?php
// Khởi tạo session và gán quyền admin
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['user_role'] = 'admin';

// Include the API file directly
require_once __DIR__ . '/../api/dashboard_charts.php';

// Get the output buffer contents
$response = ob_get_clean();
$data = json_decode($response, true);

if (!$data || !isset($data['success']) || !$data['success']) {
    die('Error: ' . ($data['error'] ?? 'Unknown error'));
}

$chartData = $data['data'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Dashboard Charts</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            width: 100%;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .data-container {
            margin: 20px;
            padding: 20px;
            background: #f5f5f5;
            border-radius: 5px;
        }
        pre {
            background: #f8f8f8;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>Test Dashboard Charts</h1>

    <!-- Hiển thị dữ liệu thô -->
    <div class="data-container">
        <h2>Raw Data</h2>
        <pre><?php print_r($chartData); ?></pre>
    </div>

    <!-- Biểu đồ chấm công -->
    <div class="chart-container">
        <h2>Attendance Chart</h2>
        <canvas id="attendanceChart"></canvas>
    </div>

    <!-- Biểu đồ phòng ban -->
    <div class="chart-container">
        <h2>Department Chart</h2>
        <canvas id="departmentChart"></canvas>
    </div>

    <!-- Biểu đồ hiệu suất -->
    <div class="chart-container">
        <h2>Performance Chart</h2>
        <canvas id="performanceChart"></canvas>
    </div>

    <!-- Biểu đồ lương -->
    <div class="chart-container">
        <h2>Salary Chart</h2>
        <canvas id="salaryChart"></canvas>
    </div>

    <!-- Biểu đồ nghỉ phép -->
    <div class="chart-container">
        <h2>Leave Chart</h2>
        <canvas id="leaveChart"></canvas>
    </div>

    <!-- Biểu đồ tuyển dụng -->
    <div class="chart-container">
        <h2>Recruitment Chart</h2>
        <canvas id="recruitmentChart"></canvas>
    </div>

    <!-- Biểu đồ đào tạo -->
    <div class="chart-container">
        <h2>Training Chart</h2>
        <canvas id="trainingChart"></canvas>
    </div>

    <!-- Biểu đồ tài sản -->
    <div class="chart-container">
        <h2>Assets Chart</h2>
        <canvas id="assetsChart"></canvas>
    </div>

    <script>
        // Khởi tạo các biểu đồ
        document.addEventListener('DOMContentLoaded', function() {
            const chartData = <?php echo json_encode($chartData); ?>;

            // 1. Attendance Chart
            new Chart(document.getElementById('attendanceChart'), {
                type: 'line',
                data: {
                    labels: chartData.attendance.map(item => item.day),
                    datasets: [{
                        label: 'Tỷ lệ đi làm',
                        data: chartData.attendance.map(item => 
                            Math.round((item.present / item.total) * 100)
                        ),
                        borderColor: '#4CAF50',
                        backgroundColor: 'rgba(76, 175, 80, 0.1)',
                        tension: 0.1,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    return `${context.dataset.label}: ${context.raw}%`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });

            // 2. Department Chart
            new Chart(document.getElementById('departmentChart'), {
                type: 'doughnut',
                data: {
                    labels: chartData.departments.map(item => item.department_name),
                    datasets: [{
                        data: chartData.departments.map(item => item.employee_count),
                        backgroundColor: [
                            '#4CAF50', '#2196F3', '#FFC107', '#9C27B0', '#F44336'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.label}: ${context.raw} nhân viên`;
                                }
                            }
                        }
                    }
                }
            });

            // 3. Performance Chart
            new Chart(document.getElementById('performanceChart'), {
                type: 'bar',
                data: {
                    labels: chartData.performance.map(item => `Q${item.quarter}`),
                    datasets: [{
                        label: 'Hiệu suất trung bình',
                        data: chartData.performance.map(item => item.avg_score),
                        backgroundColor: '#2196F3'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 5,
                            ticks: {
                                callback: function(value) {
                                    return value.toFixed(2);
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.raw.toFixed(2)} điểm`;
                                }
                            }
                        }
                    }
                }
            });

            // 4. Salary Chart
            new Chart(document.getElementById('salaryChart'), {
                type: 'line',
                data: {
                    labels: chartData.salary.map(item => item.month),
                    datasets: [{
                        label: 'Tổng chi phí lương',
                        data: chartData.salary.map(item => item.total_salary),
                        borderColor: '#FFC107',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return new Intl.NumberFormat('vi-VN', {
                                        style: 'currency',
                                        currency: 'VND',
                                        minimumFractionDigits: 0
                                    }).format(value);
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return new Intl.NumberFormat('vi-VN', {
                                        style: 'currency',
                                        currency: 'VND',
                                        minimumFractionDigits: 0
                                    }).format(context.raw);
                                }
                            }
                        }
                    }
                }
            });

            // 5. Leave Chart
            new Chart(document.getElementById('leaveChart'), {
                type: 'bar',
                data: {
                    labels: chartData.leaves.map(item => item.type),
                    datasets: [{
                        data: chartData.leaves.map(item => item.count),
                        backgroundColor: ['#4CAF50', '#2196F3', '#FFC107']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.raw} ngày`;
                                }
                            }
                        }
                    }
                }
            });

            // 6. Recruitment Chart
            new Chart(document.getElementById('recruitmentChart'), {
                type: 'doughnut',
                data: {
                    labels: chartData.recruitment.map(item => item.status),
                    datasets: [{
                        data: chartData.recruitment.map(item => item.count),
                        backgroundColor: ['#4CAF50', '#2196F3', '#F44336', '#FFC107']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.label}: ${context.raw} ứng viên`;
                                }
                            }
                        }
                    }
                }
            });

            // 7. Training Chart
            new Chart(document.getElementById('trainingChart'), {
                type: 'bar',
                data: {
                    labels: chartData.training.map(item => item.category),
                    datasets: [{
                        label: 'Số người tham gia',
                        data: chartData.training.map(item => item.participant_count),
                        backgroundColor: '#9C27B0'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.raw} người tham gia`;
                                }
                            }
                        }
                    }
                }
            });

            // 8. Assets Chart
            new Chart(document.getElementById('assetsChart'), {
                type: 'doughnut',
                data: {
                    labels: chartData.assets.map(item => item.status),
                    datasets: [{
                        data: chartData.assets.map(item => item.count),
                        backgroundColor: [
                            '#4CAF50', // Đang sử dụng
                            '#FFC107', // Bảo trì
                            '#F44336', // Hỏng
                            '#9E9E9E'  // Thanh lý
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.raw / total) * 100).toFixed(1);
                                    return `${context.label}: ${context.raw} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html> 