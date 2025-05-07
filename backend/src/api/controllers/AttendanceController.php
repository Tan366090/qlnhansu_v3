<?php
namespace App\Controllers;

use App\Utils\ResponseHandler;
use App\Config\Database;
use App\Models\Attendance;

class AttendanceController {
    private $db;
    private $attendanceModel;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->attendanceModel = new Attendance();
    }
    
    public function checkIn() {
        try {
            $user = $_SESSION['user'];
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            if (empty($user['id'])) {
                return ResponseHandler::sendUnauthorized('User not authenticated');
            }
            
            // Check if already checked in
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("
                SELECT * FROM attendance 
                WHERE employee_id = ? AND date = CURDATE() AND check_in IS NOT NULL
            ");
            $stmt->execute([$user['id']]);
            if ($stmt->fetch()) {
                return ResponseHandler::sendError('Already checked in for today', 400);
            }
            
            // Record check-in
            $this->attendanceModel->checkIn($user['id']);
            
            // Get check-in details
            $stmt = $conn->prepare("
                SELECT * FROM attendance 
                WHERE employee_id = ? AND date = CURDATE()
            ");
            $stmt->execute([$user['id']]);
            $attendance = $stmt->fetch();
            
            return ResponseHandler::sendSuccess($attendance, 'Checked in successfully');
        } catch (\Exception $e) {
            return ResponseHandler::sendServerError($e->getMessage());
        }
    }
    
    public function checkOut() {
        try {
            $user = $_SESSION['user'];
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            if (empty($user['id'])) {
                return ResponseHandler::sendUnauthorized('User not authenticated');
            }
            
            // Check if already checked out
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("
                SELECT * FROM attendance 
                WHERE employee_id = ? AND date = CURDATE() AND check_out IS NOT NULL
            ");
            $stmt->execute([$user['id']]);
            if ($stmt->fetch()) {
                return ResponseHandler::sendError('Already checked out for today', 400);
            }
            
            // Record check-out
            $this->attendanceModel->checkOut($user['id']);
            
            // Calculate working hours
            $stmt = $conn->prepare("
                SELECT TIMESTAMPDIFF(HOUR, check_in, check_out) as working_hours
                FROM attendance 
                WHERE employee_id = ? AND date = CURDATE()
            ");
            $stmt->execute([$user['id']]);
            $result = $stmt->fetch();
            
            // Update status based on working hours
            $status = 'present';
            if ($result['working_hours'] < 8) {
                $status = 'half-day';
            }
            
            $stmt = $conn->prepare("
                UPDATE attendance 
                SET status = ?, working_hours = ?
                WHERE employee_id = ? AND date = CURDATE()
            ");
            $stmt->execute([$status, $result['working_hours'], $user['id']]);
            
            // Get final attendance record
            $stmt = $conn->prepare("
                SELECT * FROM attendance 
                WHERE employee_id = ? AND date = CURDATE()
            ");
            $stmt->execute([$user['id']]);
            $attendance = $stmt->fetch();
            
            return ResponseHandler::sendSuccess($attendance, 'Checked out successfully');
        } catch (\Exception $e) {
            return ResponseHandler::sendServerError($e->getMessage());
        }
    }
    
    public function getHistory() {
        try {
            $user = $_SESSION['user'];
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            if (empty($user['id'])) {
                return ResponseHandler::sendUnauthorized('User not authenticated');
            }
            
            $startDate = $data['start_date'] ?? date('Y-m-01');
            $endDate = $data['end_date'] ?? date('Y-m-t');
            
            // Get attendance history
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("
                SELECT a.*, 
                       TIMESTAMPDIFF(HOUR, a.check_in, a.check_out) as working_hours,
                       e.name as employee_name
                FROM attendance a
                JOIN employees e ON a.employee_id = e.id
                WHERE a.employee_id = ? 
                AND a.date BETWEEN ? AND ?
                ORDER BY a.date DESC
            ");
            $stmt->execute([$user['id'], $startDate, $endDate]);
            $history = $stmt->fetchAll();
            
            // Calculate statistics
            $totalDays = count($history);
            $presentDays = count(array_filter($history, function($record) {
                return $record['status'] === 'present';
            }));
            $lateDays = count(array_filter($history, function($record) {
                return strtotime($record['check_in']) > strtotime('09:00:00');
            }));
            
            return ResponseHandler::sendSuccess([
                'history' => $history,
                'statistics' => [
                    'total_days' => $totalDays,
                    'present_days' => $presentDays,
                    'late_days' => $lateDays,
                    'attendance_rate' => $totalDays > 0 ? ($presentDays / $totalDays) * 100 : 0
                ]
            ]);
        } catch (\Exception $e) {
            return ResponseHandler::sendServerError($e->getMessage());
        }
    }
    
    public function getStatistics() {
        try {
            $user = $_SESSION['user'];
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate input
            if (empty($user['id'])) {
                return ResponseHandler::sendUnauthorized('User not authenticated');
            }
            
            $month = $data['month'] ?? date('m');
            $year = $data['year'] ?? date('Y');
            
            // Get monthly statistics
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("
                SELECT 
                    COUNT(*) as total_days,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                    SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days,
                    SUM(CASE WHEN status = 'half-day' THEN 1 ELSE 0 END) as half_days,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                    AVG(working_hours) as avg_working_hours
                FROM attendance 
                WHERE employee_id = ? 
                AND YEAR(date) = ? 
                AND MONTH(date) = ?
            ");
            $stmt->execute([$user['id'], $year, $month]);
            $statistics = $stmt->fetch();
            
            // Get daily details
            $stmt = $conn->prepare("
                SELECT date, status, working_hours
                FROM attendance 
                WHERE employee_id = ? 
                AND YEAR(date) = ? 
                AND MONTH(date) = ?
                ORDER BY date
            ");
            $stmt->execute([$user['id'], $year, $month]);
            $dailyDetails = $stmt->fetchAll();
            
            return ResponseHandler::sendSuccess([
                'statistics' => $statistics,
                'daily_details' => $dailyDetails
            ]);
        } catch (\Exception $e) {
            return ResponseHandler::sendServerError($e->getMessage());
        }
    }
} 