<?php
namespace App\Models;

use PDO;
use PDOException;

class Leave extends BaseModel {
    protected $table = 'leaves';
    protected $primaryKey = 'id';

    public function createLeave($employeeId, $leaveType, $startDate, $endDate, $duration, $reason, $attachmentUrl = null) {
        try {
            $data = [
                'employee_id' => $employeeId,
                'leave_type' => $leaveType,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'leave_duration_days' => $duration,
                'reason' => $reason,
                'status' => 'pending',
                'attachment_url' => $attachmentUrl,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $leaveId = $this->create($data);
            return [
                'success' => true,
                'leave_id' => $leaveId
            ];
        } catch (PDOException $e) {
            error_log("Create Leave Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Lỗi hệ thống'
            ];
        }
    }

    public function updateLeave($leaveId, $leaveType, $startDate, $endDate, $reason) {
        try {
            $data = [
                'leave_type' => $leaveType,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'reason' => $reason,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            return $this->update($leaveId, $data);
        } catch (PDOException $e) {
            error_log("Update Leave Error: " . $e->getMessage());
            return false;
        }
    }

    public function updateLeaveStatus($leaveId, $status, $approvedByUserId = null, $approverComments = null) {
        try {
            $data = [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($approvedByUserId !== null) {
                $data['approved_by_user_id'] = $approvedByUserId;
            }

            if ($approverComments !== null) {
                $data['approver_comments'] = $approverComments;
            }

            return $this->update($leaveId, $data);
        } catch (PDOException $e) {
            error_log("Update Leave Status Error: " . $e->getMessage());
            return false;
        }
    }

    public function getLeaves($employeeId = null, $status = null) {
        try {
            $query = "SELECT l.*, e.full_name as employee_name, a.full_name as approver_name 
                     FROM {$this->table} l
                     JOIN employees e ON l.employee_id = e.employee_id
                     LEFT JOIN employees a ON l.approved_by_user_id = a.employee_id
                     WHERE 1=1";
            $params = [];

            if ($employeeId) {
                $query .= " AND l.employee_id = ?";
                $params[] = $employeeId;
            }

            if ($status) {
                $query .= " AND l.status = ?";
                $params[] = $status;
            }

            $query .= " ORDER BY l.created_at DESC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Leaves Error: " . $e->getMessage());
            return [];
        }
    }

    public function getLeaveStats($employeeId = null) {
        try {
            $query = "SELECT 
                        COUNT(DISTINCT l.id) as total_leaves,
                        COUNT(DISTINCT l.employee_id) as employees_with_leaves,
                        COUNT(DISTINCT CASE WHEN l.status = 'approved' THEN l.id END) as approved_leaves,
                        COUNT(DISTINCT CASE WHEN l.status = 'rejected' THEN l.id END) as rejected_leaves,
                        COUNT(DISTINCT CASE WHEN l.status = 'pending' THEN l.id END) as pending_leaves,
                        AVG(l.leave_duration_days) as average_duration
                     FROM {$this->table} l";
            
            $params = [];
            if ($employeeId) {
                $query .= " WHERE l.employee_id = ?";
                $params[] = $employeeId;
            }

            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Leave Stats Error: " . $e->getMessage());
            return false;
        }
    }

    public function searchLeaves($keyword, $departmentId = null) {
        try {
            $query = "SELECT l.*, e.full_name as employee_name, e.employee_code, d.department_name 
                     FROM {$this->table} l
                     JOIN employees e ON l.employee_id = e.employee_id
                     JOIN departments d ON e.department_id = d.department_id
                     WHERE (e.full_name LIKE ? OR l.leave_type LIKE ? OR l.reason LIKE ?)";
            $params = ["%$keyword%", "%$keyword%", "%$keyword%"];

            if ($departmentId) {
                $query .= " AND e.department_id = ?";
                $params[] = $departmentId;
            }

            $query .= " ORDER BY e.full_name ASC, l.start_date DESC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Search Leaves Error: " . $e->getMessage());
            return [];
        }
    }
} 