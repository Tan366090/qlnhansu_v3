<?php
namespace App\Models;

use PDO;
use PDOException;

class Department extends BaseModel {
    protected $table = 'departments';
    protected $primaryKey = 'department_id';

    public function createDepartment($name, $description, $managerId, $parentId = null) {
        try {
            $data = [
                'department_name' => $name,
                'description' => $description,
                'manager_id' => $managerId,
                'parent_id' => $parentId,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $departmentId = $this->create($data);
            return [
                'success' => true,
                'department_id' => $departmentId
            ];
        } catch (PDOException $e) {
            error_log("Create Department Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Lỗi hệ thống'
            ];
        }
    }

    public function updateDepartment($departmentId, $name, $description, $managerId, $parentId = null) {
        try {
            $data = [
                'department_name' => $name,
                'description' => $description,
                'manager_id' => $managerId,
                'parent_id' => $parentId,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            return $this->update($departmentId, $data);
        } catch (PDOException $e) {
            error_log("Update Department Error: " . $e->getMessage());
            return false;
        }
    }

    public function updateDepartmentStatus($departmentId, $status) {
        try {
            $data = [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            return $this->update($departmentId, $data);
        } catch (PDOException $e) {
            error_log("Update Department Status Error: " . $e->getMessage());
            return false;
        }
    }

    public function getDepartmentDetails($departmentId) {
        try {
            $query = "SELECT d.*, e.full_name as manager_name, 
                     pd.department_name as parent_department_name
                     FROM {$this->table} d
                     LEFT JOIN employees e ON d.manager_id = e.employee_id
                     LEFT JOIN departments pd ON d.parent_id = pd.department_id
                     WHERE d.department_id = ?";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$departmentId]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Department Details Error: " . $e->getMessage());
            return false;
        }
    }

    public function getDepartmentEmployees($departmentId, $status = null) {
        try {
            $query = "SELECT e.*, p.position_name, d.department_name 
                     FROM employees e
                     JOIN positions p ON e.position_id = p.position_id
                     JOIN departments d ON e.department_id = d.department_id
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

    public function getSubDepartments($departmentId) {
        try {
            $query = "SELECT d.*, e.full_name as manager_name 
                     FROM {$this->table} d
                     LEFT JOIN employees e ON d.manager_id = e.employee_id
                     WHERE d.parent_id = ?
                     ORDER BY d.department_name ASC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$departmentId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Sub Departments Error: " . $e->getMessage());
            return [];
        }
    }

    public function getDepartmentHierarchy($departmentId = null) {
        try {
            if ($departmentId) {
                $query = "WITH RECURSIVE dept_hierarchy AS (
                            SELECT d.*, 0 as level
                            FROM {$this->table} d
                            WHERE d.department_id = ?
                            
                            UNION ALL
                            
                            SELECT d.*, dh.level + 1
                            FROM {$this->table} d
                            JOIN dept_hierarchy dh ON d.parent_id = dh.department_id
                        )
                        SELECT dh.*, e.full_name as manager_name
                        FROM dept_hierarchy dh
                        LEFT JOIN employees e ON dh.manager_id = e.employee_id
                        ORDER BY dh.level, dh.department_name";
                $stmt = $this->db->getConnection()->prepare($query);
                $stmt->execute([$departmentId]);
            } else {
                $query = "WITH RECURSIVE dept_hierarchy AS (
                            SELECT d.*, 0 as level
                            FROM {$this->table} d
                            WHERE d.parent_id IS NULL
                            
                            UNION ALL
                            
                            SELECT d.*, dh.level + 1
                            FROM {$this->table} d
                            JOIN dept_hierarchy dh ON d.parent_id = dh.department_id
                        )
                        SELECT dh.*, e.full_name as manager_name
                        FROM dept_hierarchy dh
                        LEFT JOIN employees e ON dh.manager_id = e.employee_id
                        ORDER BY dh.level, dh.department_name";
                $stmt = $this->db->getConnection()->prepare($query);
                $stmt->execute();
            }

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Department Hierarchy Error: " . $e->getMessage());
            return [];
        }
    }

    public function getDepartmentStats($departmentId) {
        try {
            $query = "SELECT 
                        COUNT(DISTINCT e.employee_id) as total_employees,
                        COUNT(DISTINCT CASE WHEN e.status = 'active' THEN e.employee_id END) as active_employees,
                        COUNT(DISTINCT CASE WHEN e.status = 'inactive' THEN e.employee_id END) as inactive_employees,
                        COUNT(DISTINCT p.project_id) as total_projects,
                        COUNT(DISTINCT CASE WHEN p.status = 'in_progress' THEN p.project_id END) as active_projects
                     FROM departments d
                     LEFT JOIN employees e ON d.department_id = e.department_id
                     LEFT JOIN projects p ON d.department_id = p.department_id
                     WHERE d.department_id = ?";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$departmentId]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Department Stats Error: " . $e->getMessage());
            return false;
        }
    }

    public function searchDepartments($keyword) {
        try {
            $query = "SELECT d.*, e.full_name as manager_name 
                     FROM {$this->table} d
                     LEFT JOIN employees e ON d.manager_id = e.employee_id
                     WHERE d.department_name LIKE ? OR d.description LIKE ?
                     ORDER BY d.department_name ASC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute(["%$keyword%", "%$keyword%"]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Search Departments Error: " . $e->getMessage());
            return [];
        }
    }
} 