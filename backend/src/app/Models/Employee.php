<?php
namespace App\Models;

use PDO;
use PDOException;

class Employee extends BaseModel {
    protected $table = 'employees';
    protected $primaryKey = 'employee_id';

    public function createEmployee($data) {
        try {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['status'] = 'active';

            $employeeId = $this->create($data);
            return [
                'success' => true,
                'employee_id' => $employeeId
            ];
        } catch (PDOException $e) {
            error_log("Create Employee Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Lỗi hệ thống'
            ];
        }
    }

    public function updateEmployee($employeeId, $data) {
        try {
            $data['updated_at'] = date('Y-m-d H:i:s');
            return $this->update($employeeId, $data);
        } catch (PDOException $e) {
            error_log("Update Employee Error: " . $e->getMessage());
            return false;
        }
    }

    public function updateEmployeeStatus($employeeId, $status) {
        try {
            $data = [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            return $this->update($employeeId, $data);
        } catch (PDOException $e) {
            error_log("Update Employee Status Error: " . $e->getMessage());
            return false;
        }
    }

    public function getEmployeeDetails($employeeId) {
        try {
            $query = "SELECT e.*, d.department_name, p.position_name, p.level as position_level 
                     FROM {$this->table} e
                     JOIN departments d ON e.department_id = d.department_id
                     JOIN positions p ON e.position_id = p.position_id
                     WHERE e.employee_id = ?";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$employeeId]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Employee Details Error: " . $e->getMessage());
            return false;
        }
    }

    public function getDepartmentEmployees($departmentId, $status = null) {
        try {
            $query = "SELECT e.*, p.position_name, p.level as position_level 
                     FROM {$this->table} e
                     JOIN positions p ON e.position_id = p.position_id
                     WHERE e.department_id = ?";
            $params = [$departmentId];

            if ($status) {
                $query .= " AND e.status = ?";
                $params[] = $status;
            }

            $query .= " ORDER BY e.full_name ASC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Department Employees Error: " . $e->getMessage());
            return [];
        }
    }

    public function getPositionEmployees($positionId, $status = null) {
        try {
            $query = "SELECT e.*, d.department_name 
                     FROM {$this->table} e
                     JOIN departments d ON e.department_id = d.department_id
                     WHERE e.position_id = ?";
            $params = [$positionId];

            if ($status) {
                $query .= " AND e.status = ?";
                $params[] = $status;
            }

            $query .= " ORDER BY e.full_name ASC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Position Employees Error: " . $e->getMessage());
            return [];
        }
    }

    public function getEmployeeHistory($employeeId) {
        try {
            $query = "SELECT eh.*, e.full_name as updated_by_name 
                     FROM employee_history eh
                     JOIN employees e ON eh.updated_by = e.employee_id
                     WHERE eh.employee_id = ?
                     ORDER BY eh.updated_at DESC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$employeeId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Employee History Error: " . $e->getMessage());
            return [];
        }
    }

    public function getEmployeeDocuments($employeeId) {
        try {
            $query = "SELECT ed.*, d.document_name, d.document_type 
                     FROM employee_documents ed
                     JOIN documents d ON ed.document_id = d.document_id
                     WHERE ed.employee_id = ?
                     ORDER BY ed.uploaded_at DESC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$employeeId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Employee Documents Error: " . $e->getMessage());
            return [];
        }
    }

    public function getEmployeeTrainings($employeeId) {
        try {
            $query = "SELECT et.*, t.training_name, t.start_date, t.end_date 
                     FROM employee_trainings et
                     JOIN trainings t ON et.training_id = t.training_id
                     WHERE et.employee_id = ?
                     ORDER BY t.start_date DESC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$employeeId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Employee Trainings Error: " . $e->getMessage());
            return [];
        }
    }

    public function getEmployeePerformance($employeeId) {
        try {
            $query = "SELECT pr.*, e.full_name as reviewer_name 
                     FROM performance_reviews pr
                     JOIN employees e ON pr.reviewer_id = e.employee_id
                     WHERE pr.employee_id = ?
                     ORDER BY pr.review_date DESC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$employeeId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Employee Performance Error: " . $e->getMessage());
            return [];
        }
    }

    public function searchEmployees($keyword, $departmentId = null, $positionId = null) {
        try {
            $query = "SELECT e.*, d.department_name, p.position_name 
                     FROM {$this->table} e
                     JOIN departments d ON e.department_id = d.department_id
                     JOIN positions p ON e.position_id = p.position_id
                     WHERE (e.full_name LIKE ? OR e.employee_code LIKE ? OR e.email LIKE ?)";
            $params = ["%$keyword%", "%$keyword%", "%$keyword%"];

            if ($departmentId) {
                $query .= " AND e.department_id = ?";
                $params[] = $departmentId;
            }

            if ($positionId) {
                $query .= " AND e.position_id = ?";
                $params[] = $positionId;
            }

            $query .= " ORDER BY e.full_name ASC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Search Employees Error: " . $e->getMessage());
            return [];
        }
    }
} 