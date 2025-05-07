<?php
namespace App\Models;

class Attendance extends BaseModel {
    protected $table = 'attendance';
    
    protected $fillable = [
        'employee_id',
        'attendance_date',
        'check_in_time',
        'check_out_time',
        'work_duration_hours',
        'attendance_symbol',
        'notes',
        'source'
    ];
    
    public function checkIn($employeeId, $date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        $conn = $this->db->getConnection();
        
        // Check if already checked in
        $stmt = $conn->prepare("
            SELECT * FROM {$this->table} 
            WHERE employee_id = ? AND attendance_date = ? AND check_in_time IS NOT NULL
        ");
        $stmt->execute([$employeeId, $date]);
        if ($stmt->fetch()) {
            throw new \Exception('Already checked in for today');
        }
        
        // Insert or update check-in
        $stmt = $conn->prepare("
            INSERT INTO {$this->table} (
                employee_id, attendance_date, check_in_time, 
                source, created_at, updated_at
            ) VALUES (
                ?, ?, NOW(), 'manual', NOW(), NOW()
            )
            ON DUPLICATE KEY UPDATE 
                check_in_time = NOW(),
                updated_at = NOW()
        ");
        
        return $stmt->execute([$employeeId, $date]);
    }
    
    public function checkOut($employeeId, $date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        $conn = $this->db->getConnection();
        
        // Check if already checked out
        $stmt = $conn->prepare("
            SELECT * FROM {$this->table} 
            WHERE employee_id = ? AND attendance_date = ? AND check_out_time IS NOT NULL
        ");
        $stmt->execute([$employeeId, $date]);
        if ($stmt->fetch()) {
            throw new \Exception('Already checked out for today');
        }
        
        // Calculate work duration
        $stmt = $conn->prepare("
            SELECT check_in_time FROM {$this->table}
            WHERE employee_id = ? AND attendance_date = ?
        ");
        $stmt->execute([$employeeId, $date]);
        $checkIn = $stmt->fetchColumn();
        
        if (!$checkIn) {
            throw new \Exception('No check-in record found');
        }
        
        $workDuration = $this->calculateWorkDuration($checkIn, date('H:i:s'));
        
        // Update check-out
        $stmt = $conn->prepare("
            UPDATE {$this->table} 
            SET check_out_time = NOW(),
                work_duration_hours = ?,
                updated_at = NOW()
            WHERE employee_id = ? AND attendance_date = ?
        ");
        
        return $stmt->execute([$workDuration, $employeeId, $date]);
    }
    
    public function getMonthlyReport($employeeId, $year, $month) {
        $conn = $this->db->getConnection();
        
        $startDate = date('Y-m-01', strtotime("{$year}-{$month}-01"));
        $endDate = date('Y-m-t', strtotime($startDate));
        
        $sql = "
            SELECT a.*, e.employee_code, up.full_name
            FROM attendance a
            JOIN employees e ON a.employee_id = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            WHERE a.employee_id = ?
            AND a.attendance_date BETWEEN ? AND ?
            ORDER BY a.attendance_date
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$employeeId, $startDate, $endDate]);
        return $stmt->fetchAll();
    }
    
    public function getDepartmentReport($departmentId, $startDate, $endDate) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT a.*, e.employee_code, up.full_name, d.name as department_name
            FROM attendance a
            JOIN employees e ON a.employee_id = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            JOIN departments d ON e.department_id = d.id
            WHERE e.department_id = ?
            AND a.attendance_date BETWEEN ? AND ?
            ORDER BY a.attendance_date, up.full_name
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$departmentId, $startDate, $endDate]);
        return $stmt->fetchAll();
    }
    
    public function getLateEmployees($date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT a.*, e.employee_code, up.full_name, d.name as department_name
            FROM attendance a
            JOIN employees e ON a.employee_id = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            JOIN departments d ON e.department_id = d.id
            WHERE a.attendance_date = ?
            AND TIME(a.check_in_time) > '08:30:00'
            ORDER BY a.check_in_time
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$date]);
        return $stmt->fetchAll();
    }
    
    private function calculateWorkDuration($checkIn, $checkOut) {
        $checkInTime = strtotime($checkIn);
        $checkOutTime = strtotime($checkOut);
        $duration = ($checkOutTime - $checkInTime) / 3600; // Convert to hours
        return round($duration, 2);
    }
} 