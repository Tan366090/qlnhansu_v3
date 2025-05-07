<?php
namespace App\Models;

use PDO;
use PDOException;

class Attendance extends BaseModel {
    protected $table = 'attendance';
    protected $primaryKey = 'attendance_id';

    public function createAttendance($employeeId, $date, $checkIn, $checkOut = null, $status = 'present', $notes = null) {
        try {
            $data = [
                'employee_id' => $employeeId,
                'date' => $date,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'status' => $status,
                'notes' => $notes,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $attendanceId = $this->create($data);
            return [
                'success' => true,
                'attendance_id' => $attendanceId
            ];
        } catch (PDOException $e) {
            error_log("Create Attendance Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Lỗi hệ thống'
            ];
        }
    }

    public function updateAttendance($attendanceId, $checkIn, $checkOut = null, $status = null, $notes = null) {
        try {
            $data = [
                'check_in' => $checkIn,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($checkOut !== null) {
                $data['check_out'] = $checkOut;
            }

            if ($status !== null) {
                $data['status'] = $status;
            }

            if ($notes !== null) {
                $data['notes'] = $notes;
            }

            return $this->update($attendanceId, $data);
        } catch (PDOException $e) {
            error_log("Update Attendance Error: " . $e->getMessage());
            return false;
        }
    }

    public function updateAttendanceStatus($attendanceId, $status, $notes = null) {
        try {
            $data = [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($notes !== null) {
                $data['notes'] = $notes;
            }

            return $this->update($attendanceId, $data);
        } catch (PDOException $e) {
            error_log("Update Attendance Status Error: " . $e->getMessage());
            return false;
        }
    }

    public function getAttendanceDetails($attendanceId) {
        try {
            $query = "SELECT a.*, e.full_name as employee_name 
                     FROM {$this->table} a
                     JOIN employees e ON a.employee_id = e.employee_id
                     WHERE a.attendance_id = ?";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$attendanceId]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Attendance Details Error: " . $e->getMessage());
            return false;
        }
    }

    public function getEmployeeAttendance($employeeId, $startDate = null, $endDate = null) {
        try {
            $query = "SELECT a.* 
                     FROM {$this->table} a
                     WHERE a.employee_id = ?";
            $params = [$employeeId];

            if ($startDate) {
                $query .= " AND a.date >= ?";
                $params[] = $startDate;
            }

            if ($endDate) {
                $query .= " AND a.date <= ?";
                $params[] = $endDate;
            }

            $query .= " ORDER BY a.date DESC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Employee Attendance Error: " . $e->getMessage());
            return [];
        }
    }

    public function getDepartmentAttendance($departmentId, $date = null) {
        try {
            $query = "SELECT a.*, e.full_name as employee_name, e.employee_code 
                     FROM {$this->table} a
                     JOIN employees e ON a.employee_id = e.employee_id
                     WHERE e.department_id = ?";
            $params = [$departmentId];

            if ($date) {
                $query .= " AND a.date = ?";
                $params[] = $date;
            }

            $query .= " ORDER BY e.full_name ASC, a.date DESC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Department Attendance Error: " . $e->getMessage());
            return [];
        }
    }

    public function getAttendanceStats($departmentId = null, $startDate = null, $endDate = null) {
        try {
            $query = "SELECT 
                        COUNT(DISTINCT a.attendance_id) as total_records,
                        COUNT(DISTINCT a.employee_id) as employees_with_records,
                        COUNT(DISTINCT CASE WHEN a.status = 'present' THEN a.attendance_id END) as present_count,
                        COUNT(DISTINCT CASE WHEN a.status = 'absent' THEN a.attendance_id END) as absent_count,
                        COUNT(DISTINCT CASE WHEN a.status = 'late' THEN a.attendance_id END) as late_count,
                        AVG(TIME_TO_SEC(TIMEDIFF(a.check_out, a.check_in))/3600) as average_hours
                     FROM {$this->table} a";
            
            $params = [];
            if ($departmentId) {
                $query .= " JOIN employees e ON a.employee_id = e.employee_id
                          WHERE e.department_id = ?";
                $params[] = $departmentId;
            }

            if ($startDate) {
                $query .= " AND a.date >= ?";
                $params[] = $startDate;
            }

            if ($endDate) {
                $query .= " AND a.date <= ?";
                $params[] = $endDate;
            }

            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Attendance Stats Error: " . $e->getMessage());
            return false;
        }
    }

    public function searchAttendance($keyword, $departmentId = null, $date = null) {
        try {
            $query = "SELECT a.*, e.full_name as employee_name, e.employee_code, d.department_name 
                     FROM {$this->table} a
                     JOIN employees e ON a.employee_id = e.employee_id
                     JOIN departments d ON e.department_id = d.department_id
                     WHERE (e.full_name LIKE ? OR a.status LIKE ? OR a.notes LIKE ?)";
            $params = ["%$keyword%", "%$keyword%", "%$keyword%"];

            if ($departmentId) {
                $query .= " AND e.department_id = ?";
                $params[] = $departmentId;
            }

            if ($date) {
                $query .= " AND a.date = ?";
                $params[] = $date;
            }

            $query .= " ORDER BY e.full_name ASC, a.date DESC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Search Attendance Error: " . $e->getMessage());
            return [];
        }
    }
} 