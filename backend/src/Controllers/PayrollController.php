<?php
namespace App\Controllers;

class PayrollController {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new \App\Config\Database();
        $this->conn = $this->db->getConnection();
    }

    // Lấy danh sách phiếu lương
    public function index() {
        try {
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 10;
            $search = $_GET['search'] ?? '';
            $department = $_GET['department'] ?? '';
            $month = $_GET['month'] ?? '';
            $year = $_GET['year'] ?? '';

            $query = "SELECT 
                p.id,
                p.employee_id,
                e.code as employee_code,
                e.full_name as employee_name,
                d.name as department,
                e.position,
                p.period_start,
                p.period_end,
                p.work_days,
                p.basic_salary,
                p.allowances,
                p.bonuses,
                p.deductions,
                p.gross_salary,
                p.tax,
                p.insurance,
                p.net_salary,
                p.payment_date,
                p.payment_method,
                p.payment_reference,
                p.status,
                p.created_by,
                p.created_at,
                p.notes
            FROM payroll p
            LEFT JOIN employees e ON p.employee_id = e.id
            LEFT JOIN departments d ON e.department_id = d.id
            WHERE 1=1";
            
            $params = [];
            
            if ($search) {
                $query .= " AND (e.code LIKE ? OR e.full_name LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            if ($department) {
                $query .= " AND d.id = ?";
                $params[] = $department;
            }
            
            if ($month && $year) {
                $query .= " AND MONTH(p.period_start) = ? AND YEAR(p.period_start) = ?";
                $params[] = $month;
                $params[] = $year;
            }

            $offset = ($page - 1) * $limit;
            $query .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            $payrolls = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $payrolls,
                'page' => $page,
                'limit' => $limit
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // Lấy chi tiết một phiếu lương
    public function show($id) {
        try {
            $query = "SELECT p.*, e.code as employee_code, e.full_name as employee_name, 
                     d.name as department_name
              FROM payroll p
              LEFT JOIN employees e ON p.employee_id = e.id
              LEFT JOIN departments d ON e.department_id = d.id
              WHERE p.id = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            $payroll = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$payroll) {
                return [
                    'success' => false,
                    'message' => 'Không tìm thấy phiếu lương'
                ];
            }

            return [
                'success' => true,
                'data' => $payroll
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // Tạo phiếu lương mới
    public function store() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$this->validatePayrollData($data)) {
                return [
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ'
                ];
            }

            $this->conn->beginTransaction();

            $query = "INSERT INTO payroll (employee_id, period_start, period_end, basic_salary, 
                                     allowances, bonuses, deductions, net_salary, status, created_by)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                $data['employee_id'],
                $data['period_start'],
                $data['period_end'],
                $data['basic_salary'],
                $data['allowances'] ?? 0,
                $data['bonuses'] ?? 0,
                $data['deductions'] ?? 0,
                $data['net_salary'] ?? $data['basic_salary'],
                getCurrentUserId()
            ]);

            $payrollId = $this->conn->lastInsertId();

            if (isset($data['details']) && is_array($data['details'])) {
                $this->savePayrollDetails($payrollId, $data['details']);
            }

            $this->createApprovalSteps($payrollId);

            $this->conn->commit();

            return [
                'success' => true,
                'message' => 'Tạo phiếu lương thành công',
                'data' => ['id' => $payrollId]
            ];
        } catch (\Exception $e) {
            $this->conn->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // Cập nhật phiếu lương
    public function update($id) {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$this->validatePayrollData($data)) {
                return [
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ'
                ];
            }

            $this->conn->beginTransaction();

            $query = "UPDATE payroll SET 
                     period_start = ?,
                     period_end = ?,
                     basic_salary = ?,
                     allowances = ?,
                     bonuses = ?,
                     deductions = ?,
                     net_salary = ?
                     WHERE id = ? AND status = 'draft'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                $data['period_start'],
                $data['period_end'],
                $data['basic_salary'],
                $data['allowances'] ?? 0,
                $data['bonuses'] ?? 0,
                $data['deductions'] ?? 0,
                $data['net_salary'] ?? $data['basic_salary'],
                $id
            ]);

            if (isset($data['details']) && is_array($data['details'])) {
                $this->updatePayrollDetails($id, $data['details']);
            }

            $this->conn->commit();

            return [
                'success' => true,
                'message' => 'Cập nhật phiếu lương thành công'
            ];
        } catch (\Exception $e) {
            $this->conn->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // Xóa phiếu lương
    public function destroy($id) {
        try {
            $this->conn->beginTransaction();

            // Kiểm tra trạng thái
            $stmt = $this->conn->prepare("SELECT status FROM payroll WHERE id = ?");
            $stmt->execute([$id]);
            $payroll = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$payroll) {
                throw new \Exception('Không tìm thấy phiếu lương');
            }

            if ($payroll['status'] !== 'draft') {
                throw new \Exception('Chỉ có thể xóa phiếu lương ở trạng thái nháp');
            }

            // Xóa chi tiết
            $stmt = $this->conn->prepare("DELETE FROM payroll_details WHERE payroll_id = ?");
            $stmt->execute([$id]);

            // Xóa phê duyệt
            $stmt = $this->conn->prepare("DELETE FROM payroll_approvals WHERE payroll_id = ?");
            $stmt->execute([$id]);

            // Xóa phiếu lương
            $stmt = $this->conn->prepare("DELETE FROM payroll WHERE id = ?");
            $stmt->execute([$id]);

            $this->conn->commit();

            return [
                'success' => true,
                'message' => 'Xóa phiếu lương thành công'
            ];
        } catch (\Exception $e) {
            $this->conn->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // Các phương thức phụ trợ
    private function validatePayrollData($data) {
        $required = ['employee_id', 'period_start', 'period_end', 'basic_salary'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                return false;
            }
        }
        return true;
    }

    private function savePayrollDetails($payrollId, $details) {
        $query = "INSERT INTO payroll_details (payroll_id, component_id, amount, type)
                 VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        
        foreach ($details as $detail) {
            $stmt->execute([
                $payrollId,
                $detail['component_id'],
                $detail['amount'],
                $detail['type']
            ]);
        }
    }

    private function updatePayrollDetails($payrollId, $details) {
        // Xóa chi tiết cũ
        $stmt = $this->conn->prepare("DELETE FROM payroll_details WHERE payroll_id = ?");
        $stmt->execute([$payrollId]);

        // Thêm chi tiết mới
        $this->savePayrollDetails($payrollId, $details);
    }

    private function createApprovalSteps($payrollId) {
        $approvalLevels = $this->getApprovalLevels();
        $query = "INSERT INTO payroll_approvals (payroll_id, approval_level, approver_id, status)
                 VALUES (?, ?, ?, 'pending')";
        $stmt = $this->conn->prepare($query);

        foreach ($approvalLevels as $level) {
            $stmt->execute([
                $payrollId,
                $level['level'],
                $level['approver_id']
            ]);
        }
    }

    private function getApprovalLevels() {
        // Implement logic to get approval levels from configuration
        return [
            ['level' => 1, 'approver_id' => 1], // Example
            ['level' => 2, 'approver_id' => 2]  // Example
        ];
    }
} 