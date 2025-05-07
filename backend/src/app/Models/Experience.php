<?php
namespace App\Models;

use PDO;
use PDOException;

class Experience extends BaseModel {
    protected $table = 'experiences';
    protected $primaryKey = 'experience_id';

    public function createExperience($employeeId, $companyName, $position, $startDate, $endDate = null, $description = null, $achievements = null) {
        try {
            $data = [
                'employee_id' => $employeeId,
                'company_name' => $companyName,
                'position' => $position,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'description' => $description,
                'achievements' => $achievements,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $experienceId = $this->create($data);
            return [
                'success' => true,
                'experience_id' => $experienceId
            ];
        } catch (PDOException $e) {
            error_log("Create Experience Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Lỗi hệ thống'
            ];
        }
    }

    public function updateExperience($experienceId, $companyName, $position, $startDate, $endDate = null, $description = null, $achievements = null) {
        try {
            $data = [
                'company_name' => $companyName,
                'position' => $position,
                'start_date' => $startDate,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($endDate !== null) {
                $data['end_date'] = $endDate;
            }

            if ($description !== null) {
                $data['description'] = $description;
            }

            if ($achievements !== null) {
                $data['achievements'] = $achievements;
            }

            return $this->update($experienceId, $data);
        } catch (PDOException $e) {
            error_log("Update Experience Error: " . $e->getMessage());
            return false;
        }
    }

    public function updateExperienceStatus($experienceId, $status) {
        try {
            $data = [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            return $this->update($experienceId, $data);
        } catch (PDOException $e) {
            error_log("Update Experience Status Error: " . $e->getMessage());
            return false;
        }
    }

    public function getExperienceDetails($experienceId) {
        try {
            $query = "SELECT e.*, emp.full_name as employee_name 
                     FROM {$this->table} e
                     JOIN employees emp ON e.employee_id = emp.employee_id
                     WHERE e.experience_id = ?";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$experienceId]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Experience Details Error: " . $e->getMessage());
            return false;
        }
    }

    public function getEmployeeExperiences($employeeId, $status = null) {
        try {
            $query = "SELECT e.* 
                     FROM {$this->table} e
                     WHERE e.employee_id = ?";
            $params = [$employeeId];

            if ($status) {
                $query .= " AND e.status = ?";
                $params[] = $status;
            }

            $query .= " ORDER BY e.start_date DESC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Employee Experiences Error: " . $e->getMessage());
            return [];
        }
    }

    public function getDepartmentExperiences($departmentId, $status = null) {
        try {
            $query = "SELECT e.*, emp.full_name as employee_name, emp.employee_code 
                     FROM {$this->table} e
                     JOIN employees emp ON e.employee_id = emp.employee_id
                     WHERE emp.department_id = ?";
            $params = [$departmentId];

            if ($status) {
                $query .= " AND e.status = ?";
                $params[] = $status;
            }

            $query .= " ORDER BY emp.full_name ASC, e.start_date DESC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Department Experiences Error: " . $e->getMessage());
            return [];
        }
    }

    public function getExperienceStats($departmentId = null) {
        try {
            $query = "SELECT 
                        COUNT(DISTINCT e.experience_id) as total_experiences,
                        COUNT(DISTINCT e.employee_id) as employees_with_experience,
                        AVG(DATEDIFF(COALESCE(e.end_date, CURDATE()), e.start_date)/365) as average_years,
                        COUNT(DISTINCT e.company_name) as unique_companies,
                        COUNT(DISTINCT CASE WHEN e.end_date IS NULL THEN e.experience_id END) as current_positions
                     FROM {$this->table} e";
            
            $params = [];
            if ($departmentId) {
                $query .= " JOIN employees emp ON e.employee_id = emp.employee_id
                          WHERE emp.department_id = ?";
                $params[] = $departmentId;
            }

            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Experience Stats Error: " . $e->getMessage());
            return false;
        }
    }

    public function searchExperiences($keyword, $departmentId = null) {
        try {
            $query = "SELECT e.*, emp.full_name as employee_name, emp.employee_code, dep.department_name 
                     FROM {$this->table} e
                     JOIN employees emp ON e.employee_id = emp.employee_id
                     JOIN departments dep ON emp.department_id = dep.department_id
                     WHERE (e.company_name LIKE ? OR e.position LIKE ? OR e.description LIKE ?)";
            $params = ["%$keyword%", "%$keyword%", "%$keyword%"];

            if ($departmentId) {
                $query .= " AND emp.department_id = ?";
                $params[] = $departmentId;
            }

            $query .= " ORDER BY emp.full_name ASC, e.start_date DESC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Search Experiences Error: " . $e->getMessage());
            return [];
        }
    }
} 