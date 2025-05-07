<?php
namespace App\Models;

use PDO;
use PDOException;

class Degree extends BaseModel {
    protected $table = 'degrees';
    protected $primaryKey = 'degree_id';

    public function createDegree($employeeId, $degreeName, $institution, $fieldOfStudy, $graduationYear, $grade, $documentPath = null) {
        try {
            $data = [
                'employee_id' => $employeeId,
                'degree_name' => $degreeName,
                'institution' => $institution,
                'field_of_study' => $fieldOfStudy,
                'graduation_year' => $graduationYear,
                'grade' => $grade,
                'document_path' => $documentPath,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $degreeId = $this->create($data);
            return [
                'success' => true,
                'degree_id' => $degreeId
            ];
        } catch (PDOException $e) {
            error_log("Create Degree Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Lỗi hệ thống'
            ];
        }
    }

    public function updateDegree($degreeId, $degreeName, $institution, $fieldOfStudy, $graduationYear, $grade, $documentPath = null) {
        try {
            $data = [
                'degree_name' => $degreeName,
                'institution' => $institution,
                'field_of_study' => $fieldOfStudy,
                'graduation_year' => $graduationYear,
                'grade' => $grade,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($documentPath !== null) {
                $data['document_path'] = $documentPath;
            }

            return $this->update($degreeId, $data);
        } catch (PDOException $e) {
            error_log("Update Degree Error: " . $e->getMessage());
            return false;
        }
    }

    public function updateDegreeStatus($degreeId, $status) {
        try {
            $data = [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            return $this->update($degreeId, $data);
        } catch (PDOException $e) {
            error_log("Update Degree Status Error: " . $e->getMessage());
            return false;
        }
    }

    public function getDegreeDetails($degreeId) {
        try {
            $query = "SELECT d.*, e.full_name as employee_name 
                     FROM {$this->table} d
                     JOIN employees e ON d.employee_id = e.employee_id
                     WHERE d.degree_id = ?";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$degreeId]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Degree Details Error: " . $e->getMessage());
            return false;
        }
    }

    public function getEmployeeDegrees($employeeId, $status = null) {
        try {
            $query = "SELECT d.* 
                     FROM {$this->table} d
                     WHERE d.employee_id = ?";
            $params = [$employeeId];

            if ($status) {
                $query .= " AND d.status = ?";
                $params[] = $status;
            }

            $query .= " ORDER BY d.graduation_year DESC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Employee Degrees Error: " . $e->getMessage());
            return [];
        }
    }

    public function getDepartmentDegrees($departmentId, $status = null) {
        try {
            $query = "SELECT d.*, e.full_name as employee_name, e.employee_code 
                     FROM {$this->table} d
                     JOIN employees e ON d.employee_id = e.employee_id
                     WHERE e.department_id = ?";
            $params = [$departmentId];

            if ($status) {
                $query .= " AND d.status = ?";
                $params[] = $status;
            }

            $query .= " ORDER BY e.full_name ASC, d.graduation_year DESC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Department Degrees Error: " . $e->getMessage());
            return [];
        }
    }

    public function getDegreeStats($departmentId = null) {
        try {
            $query = "SELECT 
                        COUNT(DISTINCT d.degree_id) as total_degrees,
                        COUNT(DISTINCT d.employee_id) as employees_with_degrees,
                        AVG(d.grade) as average_grade,
                        MIN(d.grade) as min_grade,
                        MAX(d.grade) as max_grade,
                        COUNT(DISTINCT CASE WHEN d.field_of_study = 'Computer Science' THEN d.degree_id END) as cs_degrees,
                        COUNT(DISTINCT CASE WHEN d.field_of_study = 'Business Administration' THEN d.degree_id END) as business_degrees
                     FROM {$this->table} d";
            
            $params = [];
            if ($departmentId) {
                $query .= " JOIN employees e ON d.employee_id = e.employee_id
                          WHERE e.department_id = ?";
                $params[] = $departmentId;
            }

            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Degree Stats Error: " . $e->getMessage());
            return false;
        }
    }

    public function searchDegrees($keyword, $departmentId = null) {
        try {
            $query = "SELECT d.*, e.full_name as employee_name, e.employee_code, dep.department_name 
                     FROM {$this->table} d
                     JOIN employees e ON d.employee_id = e.employee_id
                     JOIN departments dep ON e.department_id = dep.department_id
                     WHERE (d.degree_name LIKE ? OR d.institution LIKE ? OR d.field_of_study LIKE ?)";
            $params = ["%$keyword%", "%$keyword%", "%$keyword%"];

            if ($departmentId) {
                $query .= " AND e.department_id = ?";
                $params[] = $departmentId;
            }

            $query .= " ORDER BY e.full_name ASC, d.graduation_year DESC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Search Degrees Error: " . $e->getMessage());
            return [];
        }
    }
} 