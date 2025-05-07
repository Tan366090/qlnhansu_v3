<?php
namespace App\Models;

use PDO;
use PDOException;

class Position extends BaseModel {
    protected $table = 'positions';
    protected $primaryKey = 'position_id';

    public function createPosition($name, $description, $departmentId, $level, $baseSalary) {
        try {
            $data = [
                'position_name' => $name,
                'description' => $description,
                'department_id' => $departmentId,
                'level' => $level,
                'base_salary' => $baseSalary,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $positionId = $this->create($data);
            return [
                'success' => true,
                'position_id' => $positionId
            ];
        } catch (PDOException $e) {
            error_log("Create Position Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Lỗi hệ thống'
            ];
        }
    }

    public function updatePosition($positionId, $name, $description, $departmentId, $level, $baseSalary) {
        try {
            $data = [
                'position_name' => $name,
                'description' => $description,
                'department_id' => $departmentId,
                'level' => $level,
                'base_salary' => $baseSalary,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            return $this->update($positionId, $data);
        } catch (PDOException $e) {
            error_log("Update Position Error: " . $e->getMessage());
            return false;
        }
    }

    public function updatePositionStatus($positionId, $status) {
        try {
            $data = [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            return $this->update($positionId, $data);
        } catch (PDOException $e) {
            error_log("Update Position Status Error: " . $e->getMessage());
            return false;
        }
    }

    public function getPositionDetails($positionId) {
        try {
            $query = "SELECT p.*, d.department_name 
                     FROM {$this->table} p
                     JOIN departments d ON p.department_id = d.department_id
                     WHERE p.position_id = ?";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$positionId]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Position Details Error: " . $e->getMessage());
            return false;
        }
    }

    public function getDepartmentPositions($departmentId, $status = null) {
        try {
            $query = "SELECT p.*, d.department_name 
                     FROM {$this->table} p
                     JOIN departments d ON p.department_id = d.department_id
                     WHERE p.department_id = ?";
            $params = [$departmentId];

            if ($status) {
                $query .= " AND p.status = ?";
                $params[] = $status;
            }

            $query .= " ORDER BY p.level ASC, p.position_name ASC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Department Positions Error: " . $e->getMessage());
            return [];
        }
    }

    public function getPositionEmployees($positionId, $status = null) {
        try {
            $query = "SELECT e.*, d.department_name 
                     FROM employees e
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

    public function getPositionHierarchy($departmentId = null) {
        try {
            $query = "SELECT p.*, d.department_name 
                     FROM {$this->table} p
                     JOIN departments d ON p.department_id = d.department_id
                     WHERE p.status = 'active'";
            
            $params = [];
            if ($departmentId) {
                $query .= " AND p.department_id = ?";
                $params[] = $departmentId;
            }

            $query .= " ORDER BY p.level ASC, p.position_name ASC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Position Hierarchy Error: " . $e->getMessage());
            return [];
        }
    }

    public function getPositionStats($positionId) {
        try {
            $query = "SELECT 
                        COUNT(DISTINCT e.employee_id) as total_employees,
                        COUNT(DISTINCT CASE WHEN e.status = 'active' THEN e.employee_id END) as active_employees,
                        COUNT(DISTINCT CASE WHEN e.status = 'inactive' THEN e.employee_id END) as inactive_employees,
                        AVG(e.salary) as average_salary,
                        MIN(e.salary) as min_salary,
                        MAX(e.salary) as max_salary
                     FROM positions p
                     LEFT JOIN employees e ON p.position_id = e.position_id
                     WHERE p.position_id = ?";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$positionId]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Position Stats Error: " . $e->getMessage());
            return false;
        }
    }

    public function searchPositions($keyword, $departmentId = null) {
        try {
            $query = "SELECT p.*, d.department_name 
                     FROM {$this->table} p
                     JOIN departments d ON p.department_id = d.department_id
                     WHERE p.position_name LIKE ? OR p.description LIKE ?";
            $params = ["%$keyword%", "%$keyword%"];

            if ($departmentId) {
                $query .= " AND p.department_id = ?";
                $params[] = $departmentId;
            }

            $query .= " ORDER BY p.level ASC, p.position_name ASC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Search Positions Error: " . $e->getMessage());
            return [];
        }
    }
} 