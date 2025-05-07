<?php
namespace App\Models;

use PDO;
use PDOException;

class Salary extends BaseModel {
    protected $table = 'salaries';
    protected $primaryKey = 'salary_id';

    public function createSalary($employeeId, $month, $year, $baseSalary, $allowances = 0, $deductions = 0, $bonuses = 0, $notes = null) {
        try {
            $data = [
                'employee_id' => $employeeId,
                'month' => $month,
                'year' => $year,
                'base_salary' => $baseSalary,
                'allowances' => $allowances,
                'deductions' => $deductions,
                'bonuses' => $bonuses,
                'total_salary' => $baseSalary + $allowances + $bonuses - $deductions,
                'notes' => $notes,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $salaryId = $this->create($data);
            return [
                'success' => true,
                'salary_id' => $salaryId
            ];
        } catch (PDOException $e) {
            error_log("Create Salary Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Lỗi hệ thống'
            ];
        }
    }

    public function updateSalary($salaryId, $baseSalary, $allowances = null, $deductions = null, $bonuses = null, $notes = null) {
        try {
            $data = [
                'base_salary' => $baseSalary,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($allowances !== null) {
                $data['allowances'] = $allowances;
            }

            if ($deductions !== null) {
                $data['deductions'] = $deductions;
            }

            if ($bonuses !== null) {
                $data['bonuses'] = $bonuses;
            }

            if ($notes !== null) {
                $data['notes'] = $notes;
            }

            // Recalculate total salary
            $data['total_salary'] = $baseSalary + 
                ($allowances ?? $this->getSalaryDetails($salaryId)['allowances']) + 
                ($bonuses ?? $this->getSalaryDetails($salaryId)['bonuses']) - 
                ($deductions ?? $this->getSalaryDetails($salaryId)['deductions']);

            return $this->update($salaryId, $data);
        } catch (PDOException $e) {
            error_log("Update Salary Error: " . $e->getMessage());
            return false;
        }
    }

    public function getSalaryDetails($salaryId) {
        try {
            $query = "SELECT s.*, e.full_name as employee_name, e.employee_code, p.position_name, d.department_name 
                     FROM {$this->table} s
                     JOIN employees e ON s.employee_id = e.employee_id
                     JOIN positions p ON e.position_id = p.position_id
                     JOIN departments d ON e.department_id = d.department_id
                     WHERE s.salary_id = ?";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$salaryId]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Salary Details Error: " . $e->getMessage());
            return false;
        }
    }

    public function getEmployeeSalaries($employeeId, $year = null) {
        try {
            $query = "SELECT s.* 
                     FROM {$this->table} s
                     WHERE s.employee_id = ?";
            $params = [$employeeId];

            if ($year) {
                $query .= " AND s.year = ?";
                $params[] = $year;
            }

            $query .= " ORDER BY s.year DESC, s.month DESC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Employee Salaries Error: " . $e->getMessage());
            return [];
        }
    }

    public function getDepartmentSalaries($departmentId, $month = null, $year = null) {
        try {
            $query = "SELECT s.*, e.full_name as employee_name, e.employee_code 
                     FROM {$this->table} s
                     JOIN employees e ON s.employee_id = e.employee_id
                     WHERE e.department_id = ?";
            $params = [$departmentId];

            if ($month) {
                $query .= " AND s.month = ?";
                $params[] = $month;
            }

            if ($year) {
                $query .= " AND s.year = ?";
                $params[] = $year;
            }

            $query .= " ORDER BY e.full_name ASC, s.year DESC, s.month DESC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Department Salaries Error: " . $e->getMessage());
            return [];
        }
    }

    public function getSalaryStats($departmentId = null, $year = null) {
        try {
            $query = "SELECT 
                        COUNT(DISTINCT s.salary_id) as total_records,
                        COUNT(DISTINCT s.employee_id) as employees_with_records,
                        SUM(s.base_salary) as total_base_salary,
                        SUM(s.allowances) as total_allowances,
                        SUM(s.deductions) as total_deductions,
                        SUM(s.bonuses) as total_bonuses,
                        SUM(s.total_salary) as total_paid,
                        AVG(s.total_salary) as average_salary
                     FROM {$this->table} s";
            
            $params = [];
            if ($departmentId) {
                $query .= " JOIN employees e ON s.employee_id = e.employee_id
                          WHERE e.department_id = ?";
                $params[] = $departmentId;
            }

            if ($year) {
                $query .= " AND s.year = ?";
                $params[] = $year;
            }

            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Salary Stats Error: " . $e->getMessage());
            return false;
        }
    }

    public function searchSalaries($keyword, $departmentId = null, $year = null) {
        try {
            $query = "SELECT s.*, e.full_name as employee_name, e.employee_code, d.department_name 
                     FROM {$this->table} s
                     JOIN employees e ON s.employee_id = e.employee_id
                     JOIN departments d ON e.department_id = d.department_id
                     WHERE (e.full_name LIKE ? OR e.employee_code LIKE ? OR s.notes LIKE ?)";
            $params = ["%$keyword%", "%$keyword%", "%$keyword%"];

            if ($departmentId) {
                $query .= " AND e.department_id = ?";
                $params[] = $departmentId;
            }

            if ($year) {
                $query .= " AND s.year = ?";
                $params[] = $year;
            }

            $query .= " ORDER BY e.full_name ASC, s.year DESC, s.month DESC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Search Salaries Error: " . $e->getMessage());
            return [];
        }
    }
} 