<?php
namespace App\Controllers;

use App\Utils\ResponseHandler;
use App\Config\Database;

class SalaryController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function index() {
        try {
            $conn = $this->db->getConnection();
            
            // Get all salaries with employee details
            $stmt = $conn->prepare("
                SELECT s.*, 
                       e.name as employee_name,
                       e.employee_code,
                       d.name as department_name,
                       p.name as position_name
                FROM salaries s
                JOIN employees e ON s.employee_id = e.id
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions p ON e.position_id = p.id
                WHERE s.status = 'active'
                ORDER BY s.created_at DESC
            ");
            $stmt->execute();
            $salaries = $stmt->fetchAll();
            
            return ResponseHandler::sendSuccess($salaries);
        } catch (\Exception $e) {
            return ResponseHandler::sendServerError($e->getMessage());
        }
    }
    
    public function show($id) {
        try {
            $conn = $this->db->getConnection();
            
            // Get salary details with employee information
            $stmt = $conn->prepare("
                SELECT s.*, 
                       e.name as employee_name,
                       e.employee_code,
                       d.name as department_name,
                       p.name as position_name
                FROM salaries s
                JOIN employees e ON s.employee_id = e.id
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions p ON e.position_id = p.id
                WHERE s.id = ? AND s.status = 'active'
            ");
            $stmt->execute([$id]);
            $salary = $stmt->fetch();
            
            if (!$salary) {
                return ResponseHandler::sendError('Salary record not found', 404);
            }
            
            return ResponseHandler::sendSuccess($salary);
        } catch (\Exception $e) {
            return ResponseHandler::sendServerError($e->getMessage());
        }
    }
    
    public function store() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            if (empty($data['employee_id']) || empty($data['basic_salary'])) {
                return ResponseHandler::sendError('Employee ID and basic salary are required', 400);
            }
            
            $conn = $this->db->getConnection();
            $conn->beginTransaction();
            
            // Calculate total salary
            $totalSalary = $this->calculateTotalSalary($data);
            
            // Insert salary record
            $stmt = $conn->prepare("
                INSERT INTO salaries (
                    employee_id, basic_salary, allowances, deductions,
                    total_salary, effective_date, status, created_at, updated_at
                ) VALUES (
                    :employee_id, :basic_salary, :allowances, :deductions,
                    :total_salary, :effective_date, 'active', NOW(), NOW()
                )
            ");
            
            $stmt->execute([
                'employee_id' => $data['employee_id'],
                'basic_salary' => $data['basic_salary'],
                'allowances' => json_encode($data['allowances'] ?? []),
                'deductions' => json_encode($data['deductions'] ?? []),
                'total_salary' => $totalSalary,
                'effective_date' => $data['effective_date'] ?? date('Y-m-d')
            ]);
            
            $salaryId = $conn->lastInsertId();
            
            // Get created salary record
            $stmt = $conn->prepare("
                SELECT s.*, 
                       e.name as employee_name,
                       e.employee_code,
                       d.name as department_name,
                       p.name as position_name
                FROM salaries s
                JOIN employees e ON s.employee_id = e.id
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions p ON e.position_id = p.id
                WHERE s.id = ?
            ");
            $stmt->execute([$salaryId]);
            $salary = $stmt->fetch();
            
            $conn->commit();
            
            return ResponseHandler::sendSuccess($salary, 'Salary record created successfully', 201);
        } catch (\Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            return ResponseHandler::sendServerError($e->getMessage());
        }
    }
    
    public function update($id) {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            if (empty($data['basic_salary'])) {
                return ResponseHandler::sendError('Basic salary is required', 400);
            }
            
            $conn = $this->db->getConnection();
            $conn->beginTransaction();
            
            // Calculate total salary
            $totalSalary = $this->calculateTotalSalary($data);
            
            // Update salary record
            $stmt = $conn->prepare("
                UPDATE salaries 
                SET basic_salary = :basic_salary,
                    allowances = :allowances,
                    deductions = :deductions,
                    total_salary = :total_salary,
                    effective_date = :effective_date,
                    updated_at = NOW()
                WHERE id = :id
            ");
            
            $stmt->execute([
                'id' => $id,
                'basic_salary' => $data['basic_salary'],
                'allowances' => json_encode($data['allowances'] ?? []),
                'deductions' => json_encode($data['deductions'] ?? []),
                'total_salary' => $totalSalary,
                'effective_date' => $data['effective_date'] ?? date('Y-m-d')
            ]);
            
            // Get updated salary record
            $stmt = $conn->prepare("
                SELECT s.*, 
                       e.name as employee_name,
                       e.employee_code,
                       d.name as department_name,
                       p.name as position_name
                FROM salaries s
                JOIN employees e ON s.employee_id = e.id
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions p ON e.position_id = p.id
                WHERE s.id = ?
            ");
            $stmt->execute([$id]);
            $salary = $stmt->fetch();
            
            $conn->commit();
            
            return ResponseHandler::sendSuccess($salary, 'Salary record updated successfully');
        } catch (\Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            return ResponseHandler::sendServerError($e->getMessage());
        }
    }
    
    public function destroy($id) {
        try {
            $conn = $this->db->getConnection();
            $conn->beginTransaction();
            
            // Soft delete salary record
            $stmt = $conn->prepare("UPDATE salaries SET status = 'inactive' WHERE id = ?");
            $stmt->execute([$id]);
            
            $conn->commit();
            
            return ResponseHandler::sendSuccess([], 'Salary record deleted successfully');
        } catch (\Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            return ResponseHandler::sendServerError($e->getMessage());
        }
    }
    
    public function generatePayroll($month, $year) {
        try {
            $conn = $this->db->getConnection();
            $conn->beginTransaction();
            
            // Get all active employees
            $stmt = $conn->prepare("
                SELECT e.*, s.*
                FROM employees e
                JOIN salaries s ON e.id = s.employee_id
                WHERE e.status = 'active' 
                AND s.status = 'active'
            ");
            $stmt->execute();
            $employees = $stmt->fetchAll();
            
            $payroll = [];
            foreach ($employees as $employee) {
                // Calculate attendance-based deductions
                $attendanceDeductions = $this->calculateAttendanceDeductions($employee['id'], $month, $year);
                
                // Calculate total salary for the month
                $monthlySalary = $this->calculateMonthlySalary($employee, $attendanceDeductions);
                
                $payroll[] = [
                    'employee_id' => $employee['id'],
                    'employee_name' => $employee['name'],
                    'employee_code' => $employee['employee_code'],
                    'basic_salary' => $employee['basic_salary'],
                    'allowances' => json_decode($employee['allowances'], true),
                    'deductions' => array_merge(
                        json_decode($employee['deductions'], true),
                        $attendanceDeductions
                    ),
                    'total_salary' => $monthlySalary,
                    'month' => $month,
                    'year' => $year
                ];
            }
            
            $conn->commit();
            
            return ResponseHandler::sendSuccess($payroll, 'Payroll generated successfully');
        } catch (\Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            return ResponseHandler::sendServerError($e->getMessage());
        }
    }
    
    private function calculateTotalSalary($data) {
        $totalSalary = $data['basic_salary'];
        
        // Add allowances
        if (!empty($data['allowances'])) {
            foreach ($data['allowances'] as $allowance) {
                $totalSalary += $allowance['amount'];
            }
        }
        
        // Subtract deductions
        if (!empty($data['deductions'])) {
            foreach ($data['deductions'] as $deduction) {
                $totalSalary -= $deduction['amount'];
            }
        }
        
        return $totalSalary;
    }
    
    private function calculateAttendanceDeductions($employeeId, $month, $year) {
        $conn = $this->db->getConnection();
        
        // Get attendance records for the month
        $stmt = $conn->prepare("
            SELECT COUNT(*) as absent_days,
                   COUNT(CASE WHEN status = 'late' THEN 1 END) as late_days,
                   COUNT(CASE WHEN status = 'half-day' THEN 1 END) as half_days
            FROM attendance
            WHERE employee_id = ?
            AND MONTH(date) = ?
            AND YEAR(date) = ?
        ");
        $stmt->execute([$employeeId, $month, $year]);
        $attendance = $stmt->fetch();
        
        // Calculate deductions based on attendance
        $deductions = [];
        if ($attendance['absent_days'] > 0) {
            $deductions[] = [
                'type' => 'absent_days',
                'description' => 'Absent days deduction',
                'amount' => $attendance['absent_days'] * 500000 // Example: 500,000 VND per absent day
            ];
        }
        
        if ($attendance['late_days'] > 0) {
            $deductions[] = [
                'type' => 'late_days',
                'description' => 'Late days deduction',
                'amount' => $attendance['late_days'] * 200000 // Example: 200,000 VND per late day
            ];
        }
        
        if ($attendance['half_days'] > 0) {
            $deductions[] = [
                'type' => 'half_days',
                'description' => 'Half-day deduction',
                'amount' => $attendance['half_days'] * 250000 // Example: 250,000 VND per half-day
            ];
        }
        
        return $deductions;
    }
    
    private function calculateMonthlySalary($employee, $attendanceDeductions) {
        $totalSalary = $employee['basic_salary'];
        
        // Add allowances
        $allowances = json_decode($employee['allowances'], true);
        if (!empty($allowances)) {
            foreach ($allowances as $allowance) {
                $totalSalary += $allowance['amount'];
            }
        }
        
        // Subtract regular deductions
        $deductions = json_decode($employee['deductions'], true);
        if (!empty($deductions)) {
            foreach ($deductions as $deduction) {
                $totalSalary -= $deduction['amount'];
            }
        }
        
        // Subtract attendance-based deductions
        foreach ($attendanceDeductions as $deduction) {
            $totalSalary -= $deduction['amount'];
        }
        
        return $totalSalary;
    }
} 