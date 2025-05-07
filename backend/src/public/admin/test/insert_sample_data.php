<?php
require_once '../../config/database.php';

class SampleDataInserter {
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

    private function logMessage($message) {
        echo "<div style='margin: 10px 0; padding: 10px; background: #f8f9fa; border: 1px solid #ddd;'>";
        echo $message;
        echo "</div>";
    }

    private function logError($message) {
        echo "<div style='margin: 10px 0; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24;'>";
        echo "Lỗi: " . $message;
        echo "</div>";
    }

    private function logSuccess($message) {
        echo "<div style='margin: 10px 0; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; color: #155724;'>";
        echo $message;
        echo "</div>";
    }

    private function getEmployeeIds() {
        $query = "SELECT emp_id FROM employees ORDER BY RAND() LIMIT 20";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function getReviewerIds() {
        $query = "SELECT user_id FROM users WHERE role = 'manager' ORDER BY RAND() LIMIT 20";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function getPositionIds() {
        $query = "SELECT position_id FROM job_positions ORDER BY RAND() LIMIT 5";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function insertPerformanceData() {
        try {
            $this->logMessage("Đang thêm dữ liệu hiệu suất...");
            
            $employeeIds = $this->getEmployeeIds();
            $reviewerIds = $this->getReviewerIds();
            
            if (empty($employeeIds) || empty($reviewerIds)) {
                throw new Exception("Không tìm thấy emp_id hoặc reviewer_user_id");
            }

            $stmt = $this->conn->prepare("
                INSERT INTO performances (
                    emp_id, reviewer_user_id, evaluation_date, score, created_at, updated_at
                ) VALUES (?, ?, ?, ?, NOW(), NOW())
            ");

            $count = 0;
            for ($i = 0; $i < 20; $i++) {
                $employeeId = $employeeIds[array_rand($employeeIds)];
                $reviewerId = $reviewerIds[array_rand($reviewerIds)];
                $score = rand(5, 10);
                
                // Tạo ngày đánh giá ngẫu nhiên trong năm 2025
                $year = 2025;
                $month = rand(1, 12);
                $day = rand(1, 28);
                $evaluationDate = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));

                $stmt->execute([
                    $employeeId,
                    $reviewerId,
                    $evaluationDate,
                    $score
                ]);
                $count++;
            }
            $this->logSuccess("Đã thêm $count bản ghi vào bảng performances");
        } catch (Exception $e) {
            $this->logError("Lỗi khi thêm dữ liệu performances: " . $e->getMessage());
        }
    }

    private function insertPayrollData() {
        try {
            $this->logMessage("Đang thêm dữ liệu lương...");
            
            $employeeIds = $this->getEmployeeIds();
            if (empty($employeeIds)) {
                throw new Exception("Không tìm thấy emp_id");
            }

            $stmt = $this->conn->prepare("
                INSERT INTO payroll (
                    emp_id, payment_date, basic_salary, deductions, net_salary, status,
                    created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");

            $count = 0;
            for ($i = 0; $i < 15; $i++) {
                $employeeId = $employeeIds[array_rand($employeeIds)];
                $basicSalary = rand(5000000, 15000000);
                $deductions = rand(0, 1000000);
                $netSalary = $basicSalary - $deductions;
                $status = ['draft', 'pending', 'approved', 'paid'][rand(0, 3)];
                
                // Tạo ngày ngẫu nhiên trong năm 2025
                $year = 2025;
                $month = rand(1, 12);
                $day = rand(1, 28);
                $paymentDate = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));

                $stmt->execute([
                    $employeeId,
                    $paymentDate,
                    $basicSalary,
                    $deductions,
                    $netSalary,
                    $status
                ]);
                $count++;
            }
            $this->logSuccess("Đã thêm $count bản ghi vào bảng payroll");
        } catch (Exception $e) {
            $this->logError("Lỗi khi thêm dữ liệu payroll: " . $e->getMessage());
        }
    }

    public function insertLeaveData() {
        try {
            $this->logMessage("Đang thêm dữ liệu nghỉ phép...");
            
            // Lấy danh sách nhân viên
            $query = "SELECT id FROM employees ORDER BY RAND() LIMIT 10";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $employeeIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (empty($employeeIds)) {
                $this->logError("Không tìm thấy nhân viên nào");
                return;
            }

            // Thêm dữ liệu nghỉ phép
            $query = "INSERT INTO leaves (employee_id, leave_type, start_date, end_date, reason, status, leave_duration_days, created_at, updated_at)
                     VALUES (?, ?, ?, ?, ?, 'approved', ?, NOW(), NOW())";
            
            $stmt = $this->conn->prepare($query);
            $count = 0;
            $leaveTypes = ['annual', 'sick', 'maternity', 'unpaid'];
            
            foreach ($employeeIds as $employeeId) {
                foreach ($leaveTypes as $leaveType) {
                    $startDate = "2025-" . str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT) . "-" . str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT);
                    $endDate = date('Y-m-d', strtotime($startDate . ' +' . rand(1, 5) . ' days'));
                    $reason = "Lý do nghỉ " . $leaveType;
                    $duration = rand(1, 5);
                    
                    $stmt->execute([$employeeId, $leaveType, $startDate, $endDate, $reason, $duration]);
                    $count++;
                }
            }
            
            $this->logSuccess("Đã thêm " . $count . " bản ghi nghỉ phép");
        } catch (Exception $e) {
            $this->logError($e->getMessage());
        }
    }

    private function insertJobApplicationData() {
        try {
            $this->logMessage("Đang thêm dữ liệu tuyển dụng...");
            
            $positionIds = $this->getPositionIds();
            if (empty($positionIds)) {
                throw new Exception("Không tìm thấy position_id");
            }

            $stmt = $this->conn->prepare("
                INSERT INTO job_applications (
                    position_id, first_name, last_name, email, phone,
                    resume_url, cover_letter_url, source, status, applied_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");

            $count = 0;
            for ($i = 0; $i < 15; $i++) {
                $positionId = $positionIds[array_rand($positionIds)];
                $firstName = "Ứng viên " . ($i + 1);
                $lastName = "Thử nghiệm";
                $email = "ungvien" . ($i + 1) . rand(1000, 9999) . "@example.com"; // Thêm số ngẫu nhiên để tránh trùng email
                $phone = "09" . rand(10000000, 99999999);
                $resumeUrl = "http://example.com/resume" . ($i + 1) . ".pdf";
                $coverLetterUrl = "http://example.com/cover" . ($i + 1) . ".pdf";
                $source = ['LinkedIn', 'Website', 'Referral', 'JobStreet'][rand(0, 3)];
                $status = ['new', 'reviewing', 'shortlisted', 'interviewing', 'assessment', 'offered', 'hired', 'rejected', 'withdrawn'][rand(0, 8)];

                $stmt->execute([
                    $positionId,
                    $firstName,
                    $lastName,
                    $email,
                    $phone,
                    $resumeUrl,
                    $coverLetterUrl,
                    $source,
                    $status
                ]);
                $count++;
            }
            $this->logSuccess("Đã thêm $count bản ghi vào bảng job_applications");
        } catch (Exception $e) {
            $this->logError("Lỗi khi thêm dữ liệu job_applications: " . $e->getMessage());
        }
    }

    private function insertTrainingData() {
        try {
            $this->logMessage("Đang thêm dữ liệu đào tạo...");
            
            // Thêm khóa đào tạo
            $stmt = $this->conn->prepare("
                INSERT INTO training_courses (
                    name, description, start_date, end_date, location, max_participants,
                    created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");

            $courses = [
                ['Quản lý dự án Agile', 'Khóa học Agile cơ bản', '2025-01-15', '2025-01-17', 'Phòng họp A', 20],
                ['Lập trình Python', 'Khóa học Python nâng cao', '2025-02-01', '2025-02-03', 'Phòng họp B', 15],
                ['Kỹ năng giao tiếp', 'Đào tạo kỹ năng mềm', '2025-03-10', '2025-03-12', 'Phòng họp C', 25],
                ['Quản lý tài chính', 'Khóa học cho quản lý', '2025-04-05', '2025-04-07', 'Phòng họp D', 30]
            ];

            $courseIds = [];
            foreach ($courses as $course) {
                $stmt->execute($course);
                $courseIds[] = $this->conn->lastInsertId();
            }
            $this->logSuccess("Đã thêm " . count($courseIds) . " khóa đào tạo");

            // Thêm đăng ký khóa học
            $employeeIds = $this->getEmployeeIds();
            if (empty($employeeIds)) {
                throw new Exception("Không tìm thấy emp_id");
            }

            $stmt = $this->conn->prepare("
                INSERT INTO training_registrations (
                    course_id, emp_id, registration_date, status,
                    created_at, updated_at
                ) VALUES (?, ?, NOW(), ?, NOW(), NOW())
            ");

            $count = 0;
            foreach ($courseIds as $courseId) {
                // Mỗi khóa học có 10 đăng ký
                for ($i = 0; $i < 10; $i++) {
                    $employeeId = $employeeIds[array_rand($employeeIds)];
                    $status = ['registered', 'completed', 'cancelled'][rand(0, 2)];
                    $stmt->execute([$courseId, $employeeId, $status]);
                    $count++;
                }
            }
            $this->logSuccess("Đã thêm $count đăng ký khóa học");
        } catch (Exception $e) {
            $this->logError("Lỗi khi thêm dữ liệu training: " . $e->getMessage());
        }
    }

    private function insertAssetData() {
        try {
            $this->logMessage("Đang thêm dữ liệu tài sản...");
            
            $stmt = $this->conn->prepare("
                INSERT INTO assets (
                    asset_code, name, category, description, serial_number, purchase_date, purchase_cost, current_value, status, location, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");

            $assets = [
                ['AST001', 'Máy tính xách tay Dell', 'Thiết bị IT', 'Máy tính xách tay Dell XPS 15', 'SN12345', '2025-01-10', 25000000, 25000000, 'available', 'Phòng IT', '2025-01-10 00:00:00', '2025-01-10 00:00:00'],
                ['AST002', 'Máy tính để bàn HP', 'Thiết bị IT', 'Máy tính để bàn HP EliteDesk', 'SN12346', '2025-01-15', 20000000, 20000000, 'assigned', 'Phòng Kế toán', '2025-01-15 00:00:00', '2025-01-15 00:00:00'],
                ['AST003', 'Máy in Canon', 'Văn phòng', 'Máy in Canon LBP2900', 'SN12347', '2025-02-01', 5000000, 5000000, 'maintenance', 'Phòng Hành chính', '2025-02-01 00:00:00', '2025-02-01 00:00:00'],
                ['AST004', 'Máy chiếu Epson', 'Thiết bị trình chiếu', 'Máy chiếu Epson EB-X05', 'SN12348', '2025-02-10', 15000000, 15000000, 'available', 'Phòng Họp', '2025-02-10 00:00:00', '2025-02-10 00:00:00'],
                ['AST005', 'Điện thoại Samsung', 'Thiết bị di động', 'Điện thoại Samsung Galaxy S21', 'SN12349', '2025-03-01', 20000000, 20000000, 'assigned', 'Phòng Kinh doanh', '2025-03-01 00:00:00', '2025-03-01 00:00:00'],
                ['AST006', 'Máy quét Fujitsu', 'Văn phòng', 'Máy quét Fujitsu fi-7160', 'SN12350', '2025-03-15', 10000000, 10000000, 'available', 'Phòng Hành chính', '2025-03-15 00:00:00', '2025-03-15 00:00:00'],
                ['AST007', 'Máy fax Brother', 'Văn phòng', 'Máy fax Brother FAX-2840', 'SN12351', '2025-04-01', 3000000, 3000000, 'maintenance', 'Phòng Kế toán', '2025-04-01 00:00:00', '2025-04-01 00:00:00'],
                ['AST008', 'Máy chủ Dell', 'Thiết bị IT', 'Máy chủ Dell PowerEdge R740', 'SN12352', '2025-04-10', 50000000, 50000000, 'available', 'Phòng IT', '2025-04-10 00:00:00', '2025-04-10 00:00:00'],
                ['AST009', 'Switch Cisco', 'Thiết bị mạng', 'Switch Cisco Catalyst 2960', 'SN12353', '2025-04-15', 10000000, 10000000, 'assigned', 'Phòng IT', '2025-04-15 00:00:00', '2025-04-15 00:00:00'],
                ['AST010', 'Router TP-Link', 'Thiết bị mạng', 'Router TP-Link Archer C7', 'SN12354', '2025-04-20', 2000000, 2000000, 'available', 'Phòng IT', '2025-04-20 00:00:00', '2025-04-20 00:00:00']
            ];

            $count = 0;
            foreach ($assets as $asset) {
                $stmt->execute([
                    'asset_code' => $asset[0],
                    'name' => $asset[1],
                    'category' => $asset[2],
                    'description' => $asset[3],
                    'serial_number' => $asset[4],
                    'purchase_date' => $asset[5],
                    'purchase_cost' => $asset[6],
                    'current_value' => $asset[7],
                    'status' => $asset[8],
                    'location' => $asset[9],
                    'created_at' => $asset[10],
                    'updated_at' => $asset[11]
                ]);
                $count++;
            }
            $this->logSuccess("Đã thêm $count tài sản");
        } catch (Exception $e) {
            $this->logError("Lỗi khi thêm dữ liệu assets: " . $e->getMessage());
        }
    }

    public function insertAllData() {
        echo "<div style='max-width: 800px; margin: 0 auto; padding: 20px;'>";
        echo "<h1 style='text-align: center;'>Thêm dữ liệu mẫu</h1>";
        
        $this->insertPerformanceData();
        $this->insertPayrollData();
        $this->insertLeaveData();
        $this->insertJobApplicationData();
        $this->insertTrainingData();
        $this->insertAssetData();
        
        echo "</div>";
    }
}

// Chạy chương trình
$inserter = new SampleDataInserter();
$inserter->insertAllData();
?> 