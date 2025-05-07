<?php
namespace App\Controllers;

use App\Utils\ResponseHandler;
use App\Config\Database;
use App\Utils\RBACHandler;
use App\Utils\Logger;

class LeaveController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function index() {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "SELECT l.*, e.name as employee_name, e.employee_code,
                    d.name as department_name, m.name as manager_name 
                    FROM leaves l 
                    JOIN employees e ON l.employee_id = e.id 
                    JOIN departments d ON e.department_id = d.id 
                    LEFT JOIN employees m ON l.approved_by = m.id 
                    WHERE l.status != 'deleted' 
                    ORDER BY l.created_at DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            
            $leaves = $stmt->fetchAll();
            
            return ResponseHandler::success($leaves);
        } catch (\Exception $e) {
            Logger::error("Failed to fetch leaves: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function show($id) {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "SELECT l.*, e.name as employee_name, e.employee_code,
                    d.name as department_name, m.name as manager_name 
                    FROM leaves l 
                    JOIN employees e ON l.employee_id = e.id 
                    JOIN departments d ON e.department_id = d.id 
                    LEFT JOIN employees m ON l.approved_by = m.id 
                    WHERE l.id = :id AND l.status != 'deleted'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            $leave = $stmt->fetch();
            
            if (!$leave) {
                return ResponseHandler::error('Leave request not found', 404);
            }
            
            return ResponseHandler::success($leave);
        } catch (\Exception $e) {
            Logger::error("Failed to fetch leave: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function store() {
        try {
            $conn = $this->db->getConnection();
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['employee_id']) || empty($data['start_date']) || 
                empty($data['end_date']) || empty($data['type']) || 
                empty($data['reason'])) {
                return ResponseHandler::error('Missing required fields');
            }
            
            // Validate date range
            $startDate = strtotime($data['start_date']);
            $endDate = strtotime($data['end_date']);
            
            if ($startDate > $endDate) {
                return ResponseHandler::error('End date must be after start date');
            }
            
            $conn->beginTransaction();
            
            // Check for overlapping leaves
            $sql = "SELECT COUNT(*) as count FROM leaves 
                    WHERE employee_id = :employee_id 
                    AND status IN ('pending', 'approved') 
                    AND ((start_date BETWEEN :start_date AND :end_date) 
                    OR (end_date BETWEEN :start_date AND :end_date))";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':employee_id' => $data['employee_id'],
                ':start_date' => $data['start_date'],
                ':end_date' => $data['end_date']
            ]);
            
            $result = $stmt->fetch();
            if ($result['count'] > 0) {
                $conn->rollBack();
                return ResponseHandler::error('Overlapping leave request exists');
            }
            
            $sql = "INSERT INTO leaves (employee_id, start_date, end_date, 
                    type, reason, status) 
                    VALUES (:employee_id, :start_date, :end_date, 
                    :type, :reason, 'pending')";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':employee_id' => $data['employee_id'],
                ':start_date' => $data['start_date'],
                ':end_date' => $data['end_date'],
                ':type' => $data['type'],
                ':reason' => $data['reason']
            ]);
            
            $leaveId = $conn->lastInsertId();
            
            $conn->commit();
            
            Logger::info("New leave request created: {$leaveId}");
            
            return ResponseHandler::success(['id' => $leaveId], 'Leave request created successfully');
        } catch (\Exception $e) {
            $conn->rollBack();
            Logger::error("Failed to create leave request: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function update($id) {
        try {
            $conn = $this->db->getConnection();
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['start_date']) || empty($data['end_date']) || 
                empty($data['type']) || empty($data['reason'])) {
                return ResponseHandler::error('Missing required fields');
            }
            
            // Validate date range
            $startDate = strtotime($data['start_date']);
            $endDate = strtotime($data['end_date']);
            
            if ($startDate > $endDate) {
                return ResponseHandler::error('End date must be after start date');
            }
            
            $conn->beginTransaction();
            
            // Check if leave can be updated
            $sql = "SELECT status FROM leaves 
                    WHERE id = :id AND status != 'deleted'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            $leave = $stmt->fetch();
            
            if (!$leave) {
                $conn->rollBack();
                return ResponseHandler::error('Leave request not found', 404);
            }
            
            if ($leave['status'] != 'pending') {
                $conn->rollBack();
                return ResponseHandler::error('Only pending leave requests can be updated');
            }
            
            // Check for overlapping leaves
            $sql = "SELECT COUNT(*) as count FROM leaves 
                    WHERE employee_id = (SELECT employee_id FROM leaves WHERE id = :id) 
                    AND id != :id 
                    AND status IN ('pending', 'approved') 
                    AND ((start_date BETWEEN :start_date AND :end_date) 
                    OR (end_date BETWEEN :start_date AND :end_date))";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':start_date' => $data['start_date'],
                ':end_date' => $data['end_date']
            ]);
            
            $result = $stmt->fetch();
            if ($result['count'] > 0) {
                $conn->rollBack();
                return ResponseHandler::error('Overlapping leave request exists');
            }
            
            $sql = "UPDATE leaves 
                    SET start_date = :start_date, 
                        end_date = :end_date, 
                        type = :type, 
                        reason = :reason 
                    WHERE id = :id AND status = 'pending'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':start_date' => $data['start_date'],
                ':end_date' => $data['end_date'],
                ':type' => $data['type'],
                ':reason' => $data['reason']
            ]);
            
            if ($stmt->rowCount() === 0) {
                $conn->rollBack();
                return ResponseHandler::error('Leave request not found or cannot be updated', 404);
            }
            
            $conn->commit();
            
            Logger::info("Leave request updated: {$id}");
            
            return ResponseHandler::success(null, 'Leave request updated successfully');
        } catch (\Exception $e) {
            $conn->rollBack();
            Logger::error("Failed to update leave request: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function destroy($id) {
        try {
            $conn = $this->db->getConnection();
            
            $conn->beginTransaction();
            
            $sql = "UPDATE leaves 
                    SET status = 'deleted' 
                    WHERE id = :id AND status = 'pending'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            if ($stmt->rowCount() === 0) {
                $conn->rollBack();
                return ResponseHandler::error('Leave request not found or cannot be deleted', 404);
            }
            
            $conn->commit();
            
            Logger::info("Leave request deleted: {$id}");
            
            return ResponseHandler::success(null, 'Leave request deleted successfully');
        } catch (\Exception $e) {
            $conn->rollBack();
            Logger::error("Failed to delete leave request: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function approve($id, $managerId) {
        try {
            $conn = $this->db->getConnection();
            
            $conn->beginTransaction();
            
            $sql = "UPDATE leaves 
                    SET status = 'approved', 
                        approved_by = :manager_id, 
                        approved_at = NOW() 
                    WHERE id = :id AND status = 'pending'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':manager_id' => $managerId
            ]);
            
            if ($stmt->rowCount() === 0) {
                $conn->rollBack();
                return ResponseHandler::error('Leave request not found or cannot be approved', 404);
            }
            
            $conn->commit();
            
            Logger::info("Leave request approved: {$id} by manager {$managerId}");
            
            return ResponseHandler::success(null, 'Leave request approved successfully');
        } catch (\Exception $e) {
            $conn->rollBack();
            Logger::error("Failed to approve leave request: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function reject($id, $managerId, $reason) {
        try {
            $conn = $this->db->getConnection();
            
            $conn->beginTransaction();
            
            $sql = "UPDATE leaves 
                    SET status = 'rejected', 
                        approved_by = :manager_id, 
                        approved_at = NOW(), 
                        rejection_reason = :reason 
                    WHERE id = :id AND status = 'pending'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':manager_id' => $managerId,
                ':reason' => $reason
            ]);
            
            if ($stmt->rowCount() === 0) {
                $conn->rollBack();
                return ResponseHandler::error('Leave request not found or cannot be rejected', 404);
            }
            
            $conn->commit();
            
            Logger::info("Leave request rejected: {$id} by manager {$managerId}");
            
            return ResponseHandler::success(null, 'Leave request rejected successfully');
        } catch (\Exception $e) {
            $conn->rollBack();
            Logger::error("Failed to reject leave request: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function getByEmployee($employeeId) {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "SELECT l.*, e.name as employee_name, e.employee_code,
                    d.name as department_name, m.name as manager_name 
                    FROM leaves l 
                    JOIN employees e ON l.employee_id = e.id 
                    JOIN departments d ON e.department_id = d.id 
                    LEFT JOIN employees m ON l.approved_by = m.id 
                    WHERE l.employee_id = :employee_id AND l.status != 'deleted' 
                    ORDER BY l.created_at DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':employee_id' => $employeeId]);
            
            $leaves = $stmt->fetchAll();
            
            return ResponseHandler::success($leaves);
        } catch (\Exception $e) {
            Logger::error("Failed to fetch employee leaves: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function getPendingRequests() {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "SELECT l.*, e.name as employee_name, e.employee_code,
                    d.name as department_name 
                    FROM leaves l 
                    JOIN employees e ON l.employee_id = e.id 
                    JOIN departments d ON e.department_id = d.id 
                    WHERE l.status = 'pending' 
                    ORDER BY l.created_at DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            
            $leaves = $stmt->fetchAll();
            
            return ResponseHandler::success($leaves);
        } catch (\Exception $e) {
            Logger::error("Failed to fetch pending leave requests: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
} 