<?php
require_once '../../config/database.php';

class DashboardChartsTest {
    private $db;
    private $conn;

    public function __construct() {
        try {
            $this->db = new Database();
            $this->conn = $this->db->getConnection();
        } catch (Exception $e) {
            die("Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage());
        }
    }

    private function displayNoDataMessage($title) {
        echo "<div style='color: #666; padding: 10px; background: #f5f5f5; border: 1px solid #ddd; margin: 10px 0;'>";
        echo "<h3>$title</h3>";
        echo "<p>Không có dữ liệu trong năm hiện tại.</p>";
        echo "</div>";
    }

    private function displayError($title, $error) {
        echo "<div style='color: #721c24; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; margin: 10px 0;'>";
        echo "<h3>$title</h3>";
        echo "<p>Lỗi: " . $error . "</p>";
        echo "</div>";
    }

    public function testPerformanceData() {
        echo "<h2>Kiểm tra dữ liệu Hiệu suất nhân viên</h2>";
        try {
            $query = "SELECT 
                QUARTER(evaluation_date) as quarter,
                AVG(score) as avg_score,
                COUNT(*) as total_records,
                MIN(evaluation_date) as first_record,
                MAX(evaluation_date) as last_record
                FROM performances 
                WHERE YEAR(evaluation_date) = YEAR(CURRENT_DATE)
                GROUP BY QUARTER(evaluation_date)
                ORDER BY quarter";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($results)) {
                $this->displayNoDataMessage("Hiệu suất nhân viên");
                return;
            }

            echo "<table border='1' cellpadding='5' style='width: 100%; border-collapse: collapse;'>";
            echo "<tr style='background: #f8f9fa;'><th>Quý</th><th>Điểm trung bình</th><th>Số bản ghi</th><th>Bản ghi đầu tiên</th><th>Bản ghi gần nhất</th></tr>";
            foreach ($results as $row) {
                echo "<tr>";
                echo "<td>Q" . $row['quarter'] . "</td>";
                echo "<td>" . round($row['avg_score'], 2) . "</td>";
                echo "<td>" . $row['total_records'] . "</td>";
                echo "<td>" . $row['first_record'] . "</td>";
                echo "<td>" . $row['last_record'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";

            // Hiển thị cảnh báo nếu thiếu dữ liệu cho một số quý
            $quarters = array_column($results, 'quarter');
            $missingQuarters = array_diff([1, 2, 3, 4], $quarters);
            if (!empty($missingQuarters)) {
                echo "<div style='color: #856404; padding: 10px; background: #fff3cd; border: 1px solid #ffeeba; margin: 10px 0;'>";
                echo "<p>Cảnh báo: Thiếu dữ liệu cho các quý: " . implode(', ', $missingQuarters) . "</p>";
                echo "</div>";
            }
        } catch (Exception $e) {
            $this->displayError("Hiệu suất nhân viên", $e->getMessage());
        }
    }

    public function testSalaryData() {
        echo "<h2>Kiểm tra dữ liệu Chi phí lương</h2>";
        try {
            $query = "SELECT 
                DATE_FORMAT(payment_date, '%Y-%m') as month,
                SUM(net_salary) as total_salary,
                COUNT(*) as total_records,
                MIN(payment_date) as first_payment,
                MAX(payment_date) as last_payment
                FROM payroll 
                WHERE YEAR(payment_date) = YEAR(CURRENT_DATE)
                GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
                ORDER BY month";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($results)) {
                $this->displayNoDataMessage("Chi phí lương");
                return;
            }

            echo "<table border='1' cellpadding='5' style='width: 100%; border-collapse: collapse;'>";
            echo "<tr style='background: #f8f9fa;'><th>Tháng</th><th>Tổng lương</th><th>Số bản ghi</th><th>Thanh toán đầu tiên</th><th>Thanh toán gần nhất</th></tr>";
            foreach ($results as $row) {
                echo "<tr>";
                echo "<td>" . $row['month'] . "</td>";
                echo "<td>" . number_format($row['total_salary'], 0, ',', '.') . "đ</td>";
                echo "<td>" . $row['total_records'] . "</td>";
                echo "<td>" . $row['first_payment'] . "</td>";
                echo "<td>" . $row['last_payment'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } catch (Exception $e) {
            $this->displayError("Chi phí lương", $e->getMessage());
        }
    }

    public function testLeaveData() {
        echo "<h2>Kiểm tra dữ liệu Nghỉ phép</h2>";
        try {
            $query = "SELECT 
                leave_type,
                COUNT(*) as count,
                MIN(start_date) as first_leave,
                MAX(start_date) as last_leave,
                SUM(DATEDIFF(end_date, start_date) + 1) as total_days
                FROM leaves 
                WHERE YEAR(start_date) = YEAR(CURRENT_DATE)
                GROUP BY leave_type";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($results)) {
                $this->displayNoDataMessage("Nghỉ phép");
                return;
            }

            echo "<table border='1' cellpadding='5' style='width: 100%; border-collapse: collapse;'>";
            echo "<tr style='background: #f8f9fa;'><th>Loại nghỉ</th><th>Số lượng</th><th>Tổng ngày nghỉ</th><th>Nghỉ đầu tiên</th><th>Nghỉ gần nhất</th></tr>";
            foreach ($results as $row) {
                echo "<tr>";
                echo "<td>" . $row['leave_type'] . "</td>";
                echo "<td>" . $row['count'] . "</td>";
                echo "<td>" . $row['total_days'] . "</td>";
                echo "<td>" . $row['first_leave'] . "</td>";
                echo "<td>" . $row['last_leave'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } catch (Exception $e) {
            $this->displayError("Nghỉ phép", $e->getMessage());
        }
    }

    public function testRecruitmentData() {
        echo "<h2>Kiểm tra dữ liệu Tuyển dụng</h2>";
        try {
            $query = "SELECT 
                status,
                COUNT(*) as count,
                MIN(application_date) as first_application,
                MAX(application_date) as last_application,
                COUNT(DISTINCT position_id) as total_positions
                FROM job_applications 
                WHERE YEAR(application_date) = YEAR(CURRENT_DATE)
                GROUP BY status";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($results)) {
                $this->displayNoDataMessage("Tuyển dụng");
                return;
            }

            echo "<table border='1' cellpadding='5' style='width: 100%; border-collapse: collapse;'>";
            echo "<tr style='background: #f8f9fa;'><th>Trạng thái</th><th>Số lượng</th><th>Số vị trí</th><th>Đơn đầu tiên</th><th>Đơn gần nhất</th></tr>";
            foreach ($results as $row) {
                echo "<tr>";
                echo "<td>" . $row['status'] . "</td>";
                echo "<td>" . $row['count'] . "</td>";
                echo "<td>" . $row['total_positions'] . "</td>";
                echo "<td>" . $row['first_application'] . "</td>";
                echo "<td>" . $row['last_application'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } catch (Exception $e) {
            $this->displayError("Tuyển dụng", $e->getMessage());
        }
    }

    public function testTrainingData() {
        echo "<h2>Kiểm tra dữ liệu Đào tạo</h2>";
        try {
            $query = "SELECT 
                course_type as category,
                COUNT(tr.id) as participant_count,
                COUNT(DISTINCT tc.id) as course_count,
                MIN(tc.start_date) as first_course,
                MAX(tc.start_date) as last_course,
                AVG(DATEDIFF(tc.end_date, tc.start_date)) as avg_duration
                FROM training_courses tc
                LEFT JOIN training_registrations tr ON tc.id = tr.course_id
                WHERE YEAR(tc.start_date) = YEAR(CURRENT_DATE)
                GROUP BY tc.course_type";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($results)) {
                $this->displayNoDataMessage("Đào tạo");
                return;
            }

            echo "<table border='1' cellpadding='5' style='width: 100%; border-collapse: collapse;'>";
            echo "<tr style='background: #f8f9fa;'><th>Loại khóa học</th><th>Số người tham gia</th><th>Số khóa học</th><th>Thời lượng trung bình (ngày)</th><th>Khóa đầu tiên</th><th>Khóa gần nhất</th></tr>";
            foreach ($results as $row) {
                echo "<tr>";
                echo "<td>" . $row['category'] . "</td>";
                echo "<td>" . $row['participant_count'] . "</td>";
                echo "<td>" . $row['course_count'] . "</td>";
                echo "<td>" . round($row['avg_duration'], 1) . "</td>";
                echo "<td>" . $row['first_course'] . "</td>";
                echo "<td>" . $row['last_course'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } catch (Exception $e) {
            $this->displayError("Đào tạo", $e->getMessage());
        }
    }

    public function testAssetsData() {
        echo "<h2>Kiểm tra dữ liệu Tài sản</h2>";
        try {
            $query = "SELECT 
                status,
                COUNT(*) as count,
                MIN(created_at) as first_asset,
                MAX(created_at) as last_asset,
                SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available_count,
                SUM(CASE WHEN status = 'assigned' THEN 1 ELSE 0 END) as assigned_count,
                SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) as maintenance_count
                FROM assets 
                GROUP BY status";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($results)) {
                $this->displayNoDataMessage("Tài sản");
                return;
            }

            echo "<table border='1' cellpadding='5' style='width: 100%; border-collapse: collapse;'>";
            echo "<tr style='background: #f8f9fa;'><th>Trạng thái</th><th>Số lượng</th><th>Tài sản đầu tiên</th><th>Tài sản gần nhất</th><th>Đang sẵn sàng</th><th>Đã giao</th><th>Đang bảo trì</th></tr>";
            foreach ($results as $row) {
                echo "<tr>";
                echo "<td>" . $row['status'] . "</td>";
                echo "<td>" . $row['count'] . "</td>";
                echo "<td>" . $row['first_asset'] . "</td>";
                echo "<td>" . $row['last_asset'] . "</td>";
                echo "<td>" . $row['available_count'] . "</td>";
                echo "<td>" . $row['assigned_count'] . "</td>";
                echo "<td>" . $row['maintenance_count'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } catch (Exception $e) {
            $this->displayError("Tài sản", $e->getMessage());
        }
    }

    public function runAllTests() {
        echo "<div style='max-width: 1200px; margin: 0 auto; padding: 20px;'>";
        echo "<h1 style='color: #333; text-align: center;'>Kiểm tra dữ liệu Dashboard Charts</h1>";
        echo "<p style='text-align: center; color: #666;'>Thời gian kiểm tra: " . date('Y-m-d H:i:s') . "</p>";
        
        $this->testPerformanceData();
        $this->testSalaryData();
        $this->testLeaveData();
        $this->testRecruitmentData();
        $this->testTrainingData();
        $this->testAssetsData();
        
        echo "</div>";
    }
}

// Chạy các test
$test = new DashboardChartsTest();
$test->runAllTests();
?> 