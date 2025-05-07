<?php
namespace App\Models;

use PDO;
use PDOException;

class Skill extends BaseModel {
    protected $table = 'skills';
    protected $primaryKey = 'skill_id';

    public function createSkill($employeeId, $skillName, $proficiencyLevel, $yearsOfExperience, $certified = false, $certificationDate = null) {
        try {
            $data = [
                'employee_id' => $employeeId,
                'skill_name' => $skillName,
                'proficiency_level' => $proficiencyLevel,
                'years_of_experience' => $yearsOfExperience,
                'certified' => $certified,
                'certification_date' => $certificationDate,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $skillId = $this->create($data);
            return [
                'success' => true,
                'skill_id' => $skillId
            ];
        } catch (PDOException $e) {
            error_log("Create Skill Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Lỗi hệ thống'
            ];
        }
    }

    public function updateSkill($skillId, $skillName, $proficiencyLevel, $yearsOfExperience, $certified = false, $certificationDate = null) {
        try {
            $data = [
                'skill_name' => $skillName,
                'proficiency_level' => $proficiencyLevel,
                'years_of_experience' => $yearsOfExperience,
                'certified' => $certified,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($certificationDate !== null) {
                $data['certification_date'] = $certificationDate;
            }

            return $this->update($skillId, $data);
        } catch (PDOException $e) {
            error_log("Update Skill Error: " . $e->getMessage());
            return false;
        }
    }

    public function updateSkillStatus($skillId, $status) {
        try {
            $data = [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            return $this->update($skillId, $data);
        } catch (PDOException $e) {
            error_log("Update Skill Status Error: " . $e->getMessage());
            return false;
        }
    }

    public function getSkillDetails($skillId) {
        try {
            $query = "SELECT s.*, e.full_name as employee_name 
                     FROM {$this->table} s
                     JOIN employees e ON s.employee_id = e.employee_id
                     WHERE s.skill_id = ?";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$skillId]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Skill Details Error: " . $e->getMessage());
            return false;
        }
    }

    public function getEmployeeSkills($employeeId, $status = null) {
        try {
            $query = "SELECT s.* 
                     FROM {$this->table} s
                     WHERE s.employee_id = ?";
            $params = [$employeeId];

            if ($status) {
                $query .= " AND s.status = ?";
                $params[] = $status;
            }

            $query .= " ORDER BY s.proficiency_level DESC, s.years_of_experience DESC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Employee Skills Error: " . $e->getMessage());
            return [];
        }
    }

    public function getDepartmentSkills($departmentId, $status = null) {
        try {
            $query = "SELECT s.*, e.full_name as employee_name, e.employee_code 
                     FROM {$this->table} s
                     JOIN employees e ON s.employee_id = e.employee_id
                     WHERE e.department_id = ?";
            $params = [$departmentId];

            if ($status) {
                $query .= " AND s.status = ?";
                $params[] = $status;
            }

            $query .= " ORDER BY e.full_name ASC, s.proficiency_level DESC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Department Skills Error: " . $e->getMessage());
            return [];
        }
    }

    public function getSkillStats($departmentId = null) {
        try {
            $query = "SELECT 
                        COUNT(DISTINCT s.skill_id) as total_skills,
                        COUNT(DISTINCT s.employee_id) as employees_with_skills,
                        AVG(s.proficiency_level) as average_proficiency,
                        COUNT(DISTINCT CASE WHEN s.certified = 1 THEN s.skill_id END) as certified_skills,
                        COUNT(DISTINCT s.skill_name) as unique_skills
                     FROM {$this->table} s";
            
            $params = [];
            if ($departmentId) {
                $query .= " JOIN employees e ON s.employee_id = e.employee_id
                          WHERE e.department_id = ?";
                $params[] = $departmentId;
            }

            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Skill Stats Error: " . $e->getMessage());
            return false;
        }
    }

    public function searchSkills($keyword, $departmentId = null) {
        try {
            $query = "SELECT s.*, e.full_name as employee_name, e.employee_code, dep.department_name 
                     FROM {$this->table} s
                     JOIN employees e ON s.employee_id = e.employee_id
                     JOIN departments dep ON e.department_id = dep.department_id
                     WHERE s.skill_name LIKE ?";
            $params = ["%$keyword%"];

            if ($departmentId) {
                $query .= " AND e.department_id = ?";
                $params[] = $departmentId;
            }

            $query .= " ORDER BY e.full_name ASC, s.proficiency_level DESC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Search Skills Error: " . $e->getMessage());
            return [];
        }
    }
} 