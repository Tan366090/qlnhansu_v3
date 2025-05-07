<?php
namespace App\Models;

class Leave extends BaseModel {
    protected $table = 'leaves';
    
    protected $fillable = [
        'employee_id',
        'leave_type',
        'start_date',
        'end_date',
        'leave_duration_days',
        'reason',
        'status',
        'approved_by_user_id',
        'approver_comments',
        'attachment_url'
    ];
    
    public function getWithDetails($id = null) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT l.*, 
                   e.employee_code,
                   up.full_name,
                   d.name as department_name,
                   up2.full_name as approver_name
            FROM leaves l
            JOIN employees e ON l.employee_id = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            JOIN departments d ON e.department_id = d.id
            LEFT JOIN users u ON l.approved_by_user_id = u.id
            LEFT JOIN user_profiles up2 ON u.id = up2.user_id
            WHERE l.status != 'deleted'
        ";
        
        if ($id) {
            $sql .= " AND l.id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        }
        
        $sql .= " ORDER BY l.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getByEmployee($employeeId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT l.*, 
                   e.employee_code,
                   up.full_name,
                   d.name as department_name,
                   up2.full_name as approver_name
            FROM leaves l
            JOIN employees e ON l.employee_id = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            JOIN departments d ON e.department_id = d.id
            LEFT JOIN users u ON l.approved_by_user_id = u.id
            LEFT JOIN user_profiles up2 ON u.id = up2.user_id
            WHERE l.employee_id = ? AND l.status != 'deleted'
            ORDER BY l.created_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$employeeId]);
        return $stmt->fetchAll();
    }
    
    public function getPendingRequests() {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT l.*, 
                   e.employee_code,
                   up.full_name,
                   d.name as department_name
            FROM leaves l
            JOIN employees e ON l.employee_id = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            JOIN departments d ON e.department_id = d.id
            WHERE l.status = 'pending'
            ORDER BY l.created_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function createNotification($type, $leave_id, $user_id, $title, $message) {
        $conn = $this->db->getConnection();
        
        $sql = "
            INSERT INTO notifications (
                user_id,
                title,
                message,
                type,
                related_entity_type,
                related_entity_id,
                created_at
            ) VALUES (
                :user_id,
                :title,
                :message,
                :type,
                'leave',
                :leave_id,
                NOW()
            )
        ";
        
        $stmt = $conn->prepare($sql);
        return $stmt->execute([
            ':user_id' => $user_id,
            ':title' => $title,
            ':message' => $message,
            ':type' => $type,
            ':leave_id' => $leave_id
        ]);
    }
    
    public function approve($id, $userId, $comments = null) {
        $conn = $this->db->getConnection();
        
        // Get leave request details
        $leave = $this->getWithDetails($id);
        if (!$leave) {
            return false;
        }
        
        $sql = "
            UPDATE {$this->table}
            SET status = 'approved',
                approved_by_user_id = ?,
                approver_comments = ?,
                updated_at = NOW()
            WHERE id = ? AND status = 'pending'
        ";
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([$userId, $comments, $id]);
        
        if ($result) {
            // Create notification for employee
            $this->createNotification(
                'leave_approved',
                $id,
                $leave['employee_id'],
                'Đơn nghỉ phép đã được duyệt',
                "Đơn nghỉ phép của bạn từ {$leave['start_date']} đến {$leave['end_date']} đã được duyệt."
            );
            
            // Log activity
            $this->logActivity(
                $userId,
                'APPROVE_LEAVE',
                "Đã duyệt đơn nghỉ phép của nhân viên {$leave['full_name']}",
                'LeaveRequest',
                $id,
                'success'
            );
        }
        
        return $result;
    }
    
    public function reject($id, $userId, $comments) {
        $conn = $this->db->getConnection();
        
        // Get leave request details
        $leave = $this->getWithDetails($id);
        if (!$leave) {
            return false;
        }
        
        $sql = "
            UPDATE {$this->table}
            SET status = 'rejected',
                approved_by_user_id = ?,
                approver_comments = ?,
                updated_at = NOW()
            WHERE id = ? AND status = 'pending'
        ";
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([$userId, $comments, $id]);
        
        if ($result) {
            // Create notification for employee
            $this->createNotification(
                'leave_rejected',
                $id,
                $leave['employee_id'],
                'Đơn nghỉ phép bị từ chối',
                "Đơn nghỉ phép của bạn từ {$leave['start_date']} đến {$leave['end_date']} đã bị từ chối."
            );
            
            // Log activity
            $this->logActivity(
                $userId,
                'REJECT_LEAVE',
                "Đã từ chối đơn nghỉ phép của nhân viên {$leave['full_name']}",
                'LeaveRequest',
                $id,
                'warning'
            );
        }
        
        return $result;
    }
    
    public function cancel($id, $employeeId) {
        $conn = $this->db->getConnection();
        
        // Get leave request details
        $leave = $this->getWithDetails($id);
        if (!$leave) {
            return false;
        }
        
        $sql = "
            UPDATE {$this->table}
            SET status = 'cancelled',
                updated_at = NOW()
            WHERE id = ? AND employee_id = ? AND status = 'pending'
        ";
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([$id, $employeeId]);
        
        if ($result) {
            // Create notification for approver if exists
            if ($leave['approved_by_user_id']) {
                $this->createNotification(
                    'leave_cancelled',
                    $id,
                    $leave['approved_by_user_id'],
                    'Đơn nghỉ phép đã bị hủy',
                    "Đơn nghỉ phép của nhân viên {$leave['full_name']} đã bị hủy."
                );
            }
            
            // Log activity
            $this->logActivity(
                $employeeId,
                'CANCEL_LEAVE',
                "Đã hủy đơn nghỉ phép",
                'LeaveRequest',
                $id,
                'info'
            );
        }
        
        return $result;
    }
    
    public function checkOverlap($employeeId, $startDate, $endDate, $excludeId = null) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT COUNT(*) as count
            FROM {$this->table}
            WHERE employee_id = ?
            AND status IN ('pending', 'approved')
            AND ((start_date BETWEEN ? AND ?)
                OR (end_date BETWEEN ? AND ?))
        ";
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$employeeId, $startDate, $endDate, $startDate, $endDate, $excludeId]);
        } else {
            $stmt = $conn->prepare($sql);
            $stmt->execute([$employeeId, $startDate, $endDate, $startDate, $endDate]);
        }
        
        return $stmt->fetchColumn() > 0;
    }
    
    public function calculateDuration($startDate, $endDate) {
        $start = strtotime($startDate);
        $end = strtotime($endDate);
        $days = ($end - $start) / (60 * 60 * 24) + 1;
        return round($days, 1);
    }

    private function logActivity($userId, $type, $description, $targetEntity, $targetEntityId, $status) {
        $conn = $this->db->getConnection();
        
        $sql = "
            INSERT INTO activities (
                user_id,
                type,
                description,
                target_entity,
                target_entity_id,
                status,
                user_agent,
                ip_address,
                created_at
            ) VALUES (
                :user_id,
                :type,
                :description,
                :target_entity,
                :target_entity_id,
                :status,
                :user_agent,
                :ip_address,
                NOW()
            )
        ";
        
        $stmt = $conn->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':type' => $type,
            ':description' => $description,
            ':target_entity' => $targetEntity,
            ':target_entity_id' => $targetEntityId,
            ':status' => $status,
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }
} 