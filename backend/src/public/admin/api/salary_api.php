<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

class SalaryAPI {
    private $conn;
    private $table_name = "payroll";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy danh sách lương
    public function getSalaries($params = []) {
        try {
            $query = "SELECT p.*, u.username, u.email, d.name as department_name 
                     FROM " . $this->table_name . " p
                     LEFT JOIN users u ON p.user_id = u.user_id
                     LEFT JOIN departments d ON u.department_id = d.department_id
                     WHERE 1=1";
            
            // Thêm điều kiện tìm kiếm
            if (!empty($params['search'])) {
                $search = $params['search'];
                $query .= " AND (u.username LIKE '%$search%' OR u.email LIKE '%$search%')";
            }

            // Thêm điều kiện phòng ban
            if (!empty($params['department_id'])) {
                $department_id = $params['department_id'];
                $query .= " AND u.department_id = $department_id";
            }

            // Thêm điều kiện tháng/năm
            if (!empty($params['month'])) {
                $month = $params['month'];
                $query .= " AND p.payroll_month = $month";
            }
            if (!empty($params['year'])) {
                $year = $params['year'];
                $query .= " AND p.payroll_year = $year";
            }

            // Thêm phân trang
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            $query .= " LIMIT $limit OFFSET $offset";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $salaries = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'status' => 'success',
                'data' => $salaries,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $this->getTotalSalaries($params)
                ]
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Lấy chi tiết lương
    public function getSalary($id) {
        try {
            $query = "SELECT p.*, u.username, u.email, d.name as department_name 
                     FROM " . $this->table_name . " p
                     LEFT JOIN users u ON p.user_id = u.user_id
                     LEFT JOIN departments d ON u.department_id = d.department_id
                     WHERE p.payroll_id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $salary = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($salary) {
                // Lấy lịch sử lương
                $history = $this->getSalaryHistory($salary['user_id']);
                $salary['history'] = $history;

                // Lấy thông tin thưởng
                $bonuses = $this->getEmployeeBonuses($salary['user_id']);
                $salary['bonuses'] = $bonuses;

                return [
                    'status' => 'success',
                    'data' => $salary
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Salary record not found'
                ];
            }
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Thêm bản ghi lương
    public function createSalary($data) {
        try {
            $query = "INSERT INTO " . $this->table_name . "
                     (user_id, payroll_month, payroll_year, work_days_actual, 
                      base_salary_at_time, bonuses_total, social_insurance_deduction,
                      other_deductions, total_salary, generated_by_user_id, notes)
                     VALUES
                     (:user_id, :payroll_month, :payroll_year, :work_days_actual,
                      :base_salary_at_time, :bonuses_total, :social_insurance_deduction,
                      :other_deductions, :total_salary, :generated_by_user_id, :notes)";

            $stmt = $this->conn->prepare($query);

            // Tính tổng lương
            $total_salary = $data['base_salary_at_time'] + $data['bonuses_total'] - 
                          $data['social_insurance_deduction'] - $data['other_deductions'];

            // Bind parameters
            $stmt->bindParam(':user_id', $data['user_id']);
            $stmt->bindParam(':payroll_month', $data['payroll_month']);
            $stmt->bindParam(':payroll_year', $data['payroll_year']);
            $stmt->bindParam(':work_days_actual', $data['work_days_actual']);
            $stmt->bindParam(':base_salary_at_time', $data['base_salary_at_time']);
            $stmt->bindParam(':bonuses_total', $data['bonuses_total']);
            $stmt->bindParam(':social_insurance_deduction', $data['social_insurance_deduction']);
            $stmt->bindParam(':other_deductions', $data['other_deductions']);
            $stmt->bindParam(':total_salary', $total_salary);
            $stmt->bindParam(':generated_by_user_id', $data['generated_by_user_id']);
            $stmt->bindParam(':notes', $data['notes']);

            if($stmt->execute()) {
                $payroll_id = $this->conn->lastInsertId();
                
                // Lưu vào lịch sử lương
                $this->saveSalaryHistory($data['user_id'], $total_salary, $data['payroll_year'] . '-' . $data['payroll_month'] . '-01');

                return [
                    'status' => 'success',
                    'message' => 'Salary record created successfully',
                    'payroll_id' => $payroll_id
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Failed to create salary record'
                ];
            }
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Cập nhật lương
    public function updateSalary($id, $data) {
        try {
            $query = "UPDATE " . $this->table_name . "
                     SET work_days_actual = :work_days_actual,
                         base_salary_at_time = :base_salary_at_time,
                         bonuses_total = :bonuses_total,
                         social_insurance_deduction = :social_insurance_deduction,
                         other_deductions = :other_deductions,
                         total_salary = :total_salary,
                         notes = :notes
                     WHERE payroll_id = :id";

            $stmt = $this->conn->prepare($query);

            // Tính tổng lương
            $total_salary = $data['base_salary_at_time'] + $data['bonuses_total'] - 
                          $data['social_insurance_deduction'] - $data['other_deductions'];

            // Bind parameters
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':work_days_actual', $data['work_days_actual']);
            $stmt->bindParam(':base_salary_at_time', $data['base_salary_at_time']);
            $stmt->bindParam(':bonuses_total', $data['bonuses_total']);
            $stmt->bindParam(':social_insurance_deduction', $data['social_insurance_deduction']);
            $stmt->bindParam(':other_deductions', $data['other_deductions']);
            $stmt->bindParam(':total_salary', $total_salary);
            $stmt->bindParam(':notes', $data['notes']);

            if($stmt->execute()) {
                // Cập nhật lịch sử lương
                $this->updateSalaryHistory($id, $total_salary);

                return [
                    'status' => 'success',
                    'message' => 'Salary record updated successfully'
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Failed to update salary record'
                ];
            }
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Lấy lịch sử lương của nhân viên
    public function getSalaryHistory($user_id) {
        try {
            $query = "SELECT * FROM salary_history 
                     WHERE user_id = :user_id 
                     ORDER BY effective_date DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }

    // Lấy danh sách thưởng
    public function getBonuses($params = []) {
        try {
            $query = "SELECT b.*, u.username, u.email 
                     FROM bonuses b
                     LEFT JOIN users u ON b.user_id = u.user_id
                     WHERE 1=1";
            
            // Thêm điều kiện tìm kiếm
            if (!empty($params['search'])) {
                $search = $params['search'];
                $query .= " AND (u.username LIKE '%$search%' OR u.email LIKE '%$search%')";
            }

            // Thêm điều kiện tháng/năm
            if (!empty($params['month'])) {
                $month = $params['month'];
                $query .= " AND MONTH(b.effective_date) = $month";
            }
            if (!empty($params['year'])) {
                $year = $params['year'];
                $query .= " AND YEAR(b.effective_date) = $year";
            }

            // Thêm phân trang
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            $query .= " LIMIT $limit OFFSET $offset";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $bonuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'status' => 'success',
                'data' => $bonuses,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $this->getTotalBonuses($params)
                ]
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Lấy thưởng của nhân viên
    private function getEmployeeBonuses($user_id) {
        $query = "SELECT * FROM bonuses WHERE user_id = :user_id ORDER BY effective_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lưu lịch sử lương
    private function saveSalaryHistory($user_id, $salary, $effective_date) {
        $query = "INSERT INTO salary_history (user_id, effective_date, salary_coefficient, salary_level)
                 VALUES (:user_id, :effective_date, :salary_coefficient, :salary_level)";
        $stmt = $this->conn->prepare($query);
        
        // Tính hệ số lương và mức lương
        $salary_coefficient = $salary / 1000000; // Giả sử mức lương cơ bản là 1 triệu
        $salary_level = $salary_coefficient >= 3 ? 'Senior' : ($salary_coefficient >= 2 ? 'Mid' : 'Junior');
        
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':effective_date', $effective_date);
        $stmt->bindParam(':salary_coefficient', $salary_coefficient);
        $stmt->bindParam(':salary_level', $salary_level);
        $stmt->execute();
    }

    // Cập nhật lịch sử lương
    private function updateSalaryHistory($payroll_id, $salary) {
        $query = "UPDATE salary_history 
                 SET salary_coefficient = :salary_coefficient,
                     salary_level = :salary_level
                 WHERE salary_history_id = :payroll_id";
        $stmt = $this->conn->prepare($query);
        
        // Tính hệ số lương và mức lương
        $salary_coefficient = $salary / 1000000; // Giả sử mức lương cơ bản là 1 triệu
        $salary_level = $salary_coefficient >= 3 ? 'Senior' : ($salary_coefficient >= 2 ? 'Mid' : 'Junior');
        
        $stmt->bindParam(':payroll_id', $payroll_id);
        $stmt->bindParam(':salary_coefficient', $salary_coefficient);
        $stmt->bindParam(':salary_level', $salary_level);
        $stmt->execute();
    }

    // Lấy tổng số bản ghi lương
    private function getTotalSalaries($params) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " p
                 LEFT JOIN users u ON p.user_id = u.user_id
                 WHERE 1=1";
        
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query .= " AND (u.username LIKE '%$search%' OR u.email LIKE '%$search%')";
        }

        if (!empty($params['department_id'])) {
            $department_id = $params['department_id'];
            $query .= " AND u.department_id = $department_id";
        }

        if (!empty($params['month'])) {
            $month = $params['month'];
            $query .= " AND p.payroll_month = $month";
        }
        if (!empty($params['year'])) {
            $year = $params['year'];
            $query .= " AND p.payroll_year = $year";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    // Lấy tổng số bản ghi thưởng
    private function getTotalBonuses($params) {
        $query = "SELECT COUNT(*) as total FROM bonuses b
                 LEFT JOIN users u ON b.user_id = u.user_id
                 WHERE 1=1";
        
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query .= " AND (u.username LIKE '%$search%' OR u.email LIKE '%$search%')";
        }

        if (!empty($params['month'])) {
            $month = $params['month'];
            $query .= " AND MONTH(b.effective_date) = $month";
        }
        if (!empty($params['year'])) {
            $year = $params['year'];
            $query .= " AND YEAR(b.effective_date) = $year";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
}

// Xử lý request
$database = new Database();
$db = $database->getConnection();
$api = new SalaryAPI($db);

$method = $_SERVER['REQUEST_METHOD'];
$response = [];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            $response = $api->getSalary($_GET['id']);
        } else if(isset($_GET['user_id']) && isset($_GET['history'])) {
            $response = $api->getSalaryHistory($_GET['user_id']);
        } else if(isset($_GET['bonuses'])) {
            $response = $api->getBonuses($_GET);
        } else {
            $response = $api->getSalaries($_GET);
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $response = $api->createSalary($data);
        break;
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $_GET['id'];
        $response = $api->updateSalary($id, $data);
        break;
    default:
        $response = [
            'status' => 'error',
            'message' => 'Method not allowed'
        ];
        break;
}

echo json_encode($response); 