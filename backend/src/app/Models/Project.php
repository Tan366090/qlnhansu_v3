<?php
namespace App\Models;

use PDO;
use PDOException;

class Project extends BaseModel {
    protected $table = 'projects';
    protected $primaryKey = 'project_id';

    public function createProject($name, $description, $startDate, $endDate, $managerId, $departmentId, $priority, $budget) {
        try {
            $data = [
                'name' => $name,
                'description' => $description,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'manager_id' => $managerId,
                'department_id' => $departmentId,
                'priority' => $priority,
                'budget' => $budget,
                'status' => 'planning',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $projectId = $this->create($data);
            return [
                'success' => true,
                'project_id' => $projectId
            ];
        } catch (PDOException $e) {
            error_log("Create Project Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Lỗi hệ thống'
            ];
        }
    }

    public function updateProject($projectId, $name, $description, $startDate, $endDate, $managerId, $departmentId, $priority, $budget) {
        try {
            $data = [
                'name' => $name,
                'description' => $description,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'manager_id' => $managerId,
                'department_id' => $departmentId,
                'priority' => $priority,
                'budget' => $budget,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            return $this->update($projectId, $data);
        } catch (PDOException $e) {
            error_log("Update Project Error: " . $e->getMessage());
            return false;
        }
    }

    public function updateProjectStatus($projectId, $status) {
        try {
            $data = [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            return $this->update($projectId, $data);
        } catch (PDOException $e) {
            error_log("Update Project Status Error: " . $e->getMessage());
            return false;
        }
    }

    public function assignEmployee($projectId, $employeeId, $role) {
        try {
            // Kiểm tra xem nhân viên đã được gán chưa
            $query = "SELECT COUNT(*) as count FROM project_members 
                     WHERE project_id = ? AND employee_id = ?";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$projectId, $employeeId]);

            if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
                return [
                    'success' => false,
                    'error' => 'Nhân viên đã được gán vào dự án'
                ];
            }

            // Gán nhân viên vào dự án
            $data = [
                'project_id' => $projectId,
                'employee_id' => $employeeId,
                'role' => $role,
                'joined_at' => date('Y-m-d H:i:s')
            ];

            $this->db->getConnection()->prepare("INSERT INTO project_members (project_id, employee_id, role, joined_at) VALUES (?, ?, ?, ?)")
                ->execute([$projectId, $employeeId, $role, date('Y-m-d H:i:s')]);

            return [
                'success' => true,
                'message' => 'Gán nhân viên thành công'
            ];
        } catch (PDOException $e) {
            error_log("Assign Employee Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Lỗi hệ thống'
            ];
        }
    }

    public function removeEmployee($projectId, $employeeId) {
        try {
            $query = "DELETE FROM project_members 
                     WHERE project_id = ? AND employee_id = ?";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$projectId, $employeeId]);

            return [
                'success' => true,
                'message' => 'Xóa nhân viên khỏi dự án thành công'
            ];
        } catch (PDOException $e) {
            error_log("Remove Employee Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Lỗi hệ thống'
            ];
        }
    }

    public function getProjectDetails($projectId) {
        try {
            $query = "SELECT p.*, e.full_name as manager_name, d.department_name 
                     FROM {$this->table} p
                     JOIN employees e ON p.manager_id = e.employee_id
                     JOIN departments d ON p.department_id = d.department_id
                     WHERE p.project_id = ?";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$projectId]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Project Details Error: " . $e->getMessage());
            return false;
        }
    }

    public function getProjectMembers($projectId) {
        try {
            $query = "SELECT pm.*, e.full_name, e.employee_code, d.department_name 
                     FROM project_members pm
                     JOIN employees e ON pm.employee_id = e.employee_id
                     JOIN departments d ON e.department_id = d.department_id
                     WHERE pm.project_id = ?
                     ORDER BY pm.joined_at ASC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$projectId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Project Members Error: " . $e->getMessage());
            return [];
        }
    }

    public function getEmployeeProjects($employeeId, $status = null) {
        try {
            $query = "SELECT p.*, e.full_name as manager_name, pm.role 
                     FROM {$this->table} p
                     JOIN employees e ON p.manager_id = e.employee_id
                     JOIN project_members pm ON p.project_id = pm.project_id
                     WHERE pm.employee_id = ?";
            $params = [$employeeId];

            if ($status) {
                $query .= " AND p.status = ?";
                $params[] = $status;
            }

            $query .= " ORDER BY p.start_date DESC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Employee Projects Error: " . $e->getMessage());
            return [];
        }
    }

    public function getDepartmentProjects($departmentId, $status = null) {
        try {
            $query = "SELECT p.*, e.full_name as manager_name, 
                     (SELECT COUNT(*) FROM project_members WHERE project_id = p.project_id) as member_count
                     FROM {$this->table} p
                     JOIN employees e ON p.manager_id = e.employee_id
                     WHERE p.department_id = ?";
            $params = [$departmentId];

            if ($status) {
                $query .= " AND p.status = ?";
                $params[] = $status;
            }

            $query .= " ORDER BY p.start_date DESC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Department Projects Error: " . $e->getMessage());
            return [];
        }
    }

    public function getProjectProgress($projectId) {
        try {
            $query = "SELECT 
                        COUNT(*) as total_tasks,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
                        AVG(progress) as average_progress
                     FROM project_tasks
                     WHERE project_id = ?";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$projectId]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Project Progress Error: " . $e->getMessage());
            return false;
        }
    }

    public function getProjectTimeline($projectId) {
        try {
            $query = "SELECT pt.*, e.full_name as assigned_to_name 
                     FROM project_tasks pt
                     JOIN employees e ON pt.assigned_to = e.employee_id
                     WHERE pt.project_id = ?
                     ORDER BY pt.due_date ASC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$projectId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Project Timeline Error: " . $e->getMessage());
            return [];
        }
    }
} 