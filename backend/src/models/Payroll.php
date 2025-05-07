<?php
namespace App\Models;

class Payroll {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new \App\Config\Database();
        $this->conn = $this->db->getConnection();
    }

    // Lấy danh sách phiếu lương
    public function getAll($filters = []) {
        $query = "SELECT p.*, e.code as employee_code, e.full_name as employee_name, 
                         d.name as department_name
                  FROM payroll p
                  LEFT JOIN employees e ON p.employee_id = e.id
                  LEFT JOIN departments d ON e.department_id = d.id
                  WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['search'])) {
            $query .= " AND (e.code LIKE ? OR e.full_name LIKE ?)";
            $params[] = "%{$filters['search']}%";
            $params[] = "%{$filters['search']}%";
        }
        
        if (!empty($filters['department'])) {
            $query .= " AND d.id = ?";
            $params[] = $filters['department'];
        }
        
        if (!empty($filters['month']) && !empty($filters['year'])) {
            $query .= " AND MONTH(p.period_start) = ? AND YEAR(p.period_start) = ?";
            $params[] = $filters['month'];
            $params[] = $filters['year'];
        }

        if (!empty($filters['limit'])) {
            $query .= " LIMIT ? OFFSET ?";
            $params[] = $filters['limit'];
            $params[] = ($filters['page'] - 1) * $filters['limit'];
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Lấy chi tiết một phiếu lương
    public function getById($id) {
        $query = "SELECT p.*, e.code as employee_code, e.full_name as employee_name, 
                         d.name as department_name
                  FROM payroll p
                  LEFT JOIN employees e ON p.employee_id = e.id
                  LEFT JOIN departments d ON e.department_id = d.id
                  WHERE p.id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    // Tạo phiếu lương mới
    public function create($data) {
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

        return $this->conn->lastInsertId();
    }

    // Cập nhật phiếu lương
    public function update($id, $data) {
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
        return $stmt->execute([
            $data['period_start'],
            $data['period_end'],
            $data['basic_salary'],
            $data['allowances'] ?? 0,
            $data['bonuses'] ?? 0,
            $data['deductions'] ?? 0,
            $data['net_salary'] ?? $data['basic_salary'],
            $id
        ]);
    }

    // Xóa phiếu lương
    public function delete($id) {
        $query = "DELETE FROM payroll WHERE id = ? AND status = 'draft'";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    // Lấy chi tiết thành phần lương
    public function getDetails($payrollId) {
        $query = "SELECT pd.*, sc.name as component_name, sc.type
                 FROM payroll_details pd
                 LEFT JOIN salary_components sc ON pd.component_id = sc.id
                 WHERE pd.payroll_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$payrollId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Lấy lịch sử phê duyệt
    public function getApprovalHistory($payrollId) {
        $query = "SELECT pa.*, u.full_name as approver_name
                 FROM payroll_approvals pa
                 LEFT JOIN users u ON pa.approver_id = u.id
                 WHERE pa.payroll_id = ?
                 ORDER BY pa.approval_level";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$payrollId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Cập nhật trạng thái phê duyệt
    public function updateApprovalStatus($payrollId, $approvalLevel, $status, $comments = '') {
        $query = "UPDATE payroll_approvals 
                 SET status = ?, comments = ?, approved_at = NOW()
                 WHERE payroll_id = ? AND approval_level = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$status, $comments, $payrollId, $approvalLevel]);
    }

    // Cập nhật trạng thái phiếu lương
    public function updateStatus($id, $status) {
        $query = "UPDATE payroll SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$status, $id]);
    }
} 