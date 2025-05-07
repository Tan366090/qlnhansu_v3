<?php

class Attendance {
    private $conn;
    private $table_name = "attendance";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAttendanceList($date) {
        $query = "SELECT 
                    e.employee_code,
                    e.full_name,
                    d.name as department_name,
                    p.name as position_name,
                    a.check_in_time,
                    a.check_out_time,
                    a.work_duration_hours,
                    a.attendance_symbol,
                    a.notes,
                    a.source
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions p ON e.position_id = p.id
                LEFT JOIN attendance a ON e.id = a.employee_id AND a.attendance_date = :date
                ORDER BY e.employee_code";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":date", $date);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function checkIn($employee_id, $attendance_symbol = 'P', $notes = null) {
        $query = "INSERT INTO " . $this->table_name . " 
                (employee_id, attendance_date, check_in_time, attendance_symbol, notes, source) 
                VALUES 
                (:employee_id, CURDATE(), NOW(), :attendance_symbol, :notes, 'manual')";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":employee_id", $employee_id);
        $stmt->bindParam(":attendance_symbol", $attendance_symbol);
        $stmt->bindParam(":notes", $notes);

        return $stmt->execute();
    }

    public function checkOut($employee_id) {
        $query = "UPDATE " . $this->table_name . " 
                SET check_out_time = NOW(),
                    work_duration_hours = TIMESTAMPDIFF(HOUR, check_in_time, NOW())
                WHERE employee_id = :employee_id 
                AND attendance_date = CURDATE() 
                AND check_out_time IS NULL";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":employee_id", $employee_id);

        return $stmt->execute();
    }
} 