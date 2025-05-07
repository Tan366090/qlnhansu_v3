<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use PDOException;
use Exception;

class AttendanceModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAttendanceByDate($date, $status = 'all')
    {
        try {
            $sql = "SELECT 
                a.attendance_id,
                a.employee_id,
                e.employee_code,
                up.full_name as employee_name,
                d.name as department_name,
                a.check_in_time,
                a.check_out_time,
                a.attendance_symbol as status,
                a.notes
            FROM attendance a
            LEFT JOIN employees e ON a.employee_id = e.id
            LEFT JOIN user_profiles up ON e.user_id = up.user_id
            LEFT JOIN departments d ON e.department_id = d.id
            WHERE DATE(a.attendance_date) = :date";

            if ($status !== 'all') {
                $sql .= " AND a.attendance_symbol = :status";
            }

            $sql .= " ORDER BY a.check_in_time ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':date', $date);
            
            if ($status !== 'all') {
                $stmt->bindParam(':status', $status);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error getting attendance data: " . $e->getMessage());
        }
    }

    public function getTodayAttendance()
    {
        return $this->getAttendanceByDate(date('Y-m-d'));
    }
} 