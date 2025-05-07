<?php
namespace App\Controllers;

use App\Utils\ResponseHandler;
use App\Config\Database;
use App\Utils\RBACHandler;
use App\Utils\Logger;

class ActivityController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function index($page = 1, $perPage = 10) {
        try {
            $conn = $this->db->getConnection();
            
            // Get total count
            $sql = "SELECT COUNT(*) FROM activities 
                    WHERE status = 'active'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            
            $total = $stmt->fetchColumn();
            $totalPages = ceil($total / $perPage);
            $offset = ($page - 1) * $perPage;
            
            // Get paginated activities with user details
            $sql = "SELECT a.*, u.name as user_name, u.email as user_email 
                    FROM activities a 
                    LEFT JOIN users u ON a.user_id = u.id 
                    WHERE a.status = 'active' 
                    ORDER BY a.created_at DESC 
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            
            $activities = $stmt->fetchAll();
            
            return ResponseHandler::success([
                'activities' => $activities,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'total_pages' => $totalPages
                ]
            ], 'Activities retrieved successfully');
        } catch (\Exception $e) {
            Logger::error("Failed to get activities: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function show($id) {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "SELECT a.*, u.name as user_name, u.email as user_email 
                    FROM activities a 
                    LEFT JOIN users u ON a.user_id = u.id 
                    WHERE a.id = :id AND a.status = 'active'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            $activity = $stmt->fetch();
            
            if (!$activity) {
                return ResponseHandler::error('Activity not found', 404);
            }
            
            return ResponseHandler::success($activity, 'Activity retrieved successfully');
        } catch (\Exception $e) {
            Logger::error("Failed to get activity: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function getByUser($userId, $page = 1, $perPage = 10) {
        try {
            $conn = $this->db->getConnection();
            
            // Get total count
            $sql = "SELECT COUNT(*) FROM activities 
                    WHERE user_id = :user_id AND status = 'active'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            
            $total = $stmt->fetchColumn();
            $totalPages = ceil($total / $perPage);
            $offset = ($page - 1) * $perPage;
            
            // Get paginated activities
            $sql = "SELECT * FROM activities 
                    WHERE user_id = :user_id AND status = 'active' 
                    ORDER BY created_at DESC 
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
            $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            
            $activities = $stmt->fetchAll();
            
            return ResponseHandler::success([
                'activities' => $activities,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'total_pages' => $totalPages
                ]
            ], 'User activities retrieved successfully');
        } catch (\Exception $e) {
            Logger::error("Failed to get user activities: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function getByType($type, $page = 1, $perPage = 10) {
        try {
            $conn = $this->db->getConnection();
            
            // Get total count
            $sql = "SELECT COUNT(*) FROM activities 
                    WHERE type = :type AND status = 'active'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':type' => $type]);
            
            $total = $stmt->fetchColumn();
            $totalPages = ceil($total / $perPage);
            $offset = ($page - 1) * $perPage;
            
            // Get paginated activities
            $sql = "SELECT a.*, u.name as user_name, u.email as user_email 
                    FROM activities a 
                    LEFT JOIN users u ON a.user_id = u.id 
                    WHERE a.type = :type AND a.status = 'active' 
                    ORDER BY a.created_at DESC 
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':type', $type);
            $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            
            $activities = $stmt->fetchAll();
            
            return ResponseHandler::success([
                'activities' => $activities,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'total_pages' => $totalPages
                ]
            ], 'Activities by type retrieved successfully');
        } catch (\Exception $e) {
            Logger::error("Failed to get activities by type: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function logActivity($userId, $type, $description, $userAgent = null, $ipAddress = null) {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "INSERT INTO activities (user_id, type, description, 
                    user_agent, ip_address, status) 
                    VALUES (:user_id, :type, :description, 
                    :user_agent, :ip_address, 'active')";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':type' => $type,
                ':description' => $description,
                ':user_agent' => $userAgent,
                ':ip_address' => $ipAddress
            ]);
            
            $activityId = $conn->lastInsertId();
            
            Logger::info("Activity logged for user: {$userId}");
            
            return ResponseHandler::success([
                'id' => $activityId
            ], 'Activity logged successfully');
        } catch (\Exception $e) {
            Logger::error("Failed to log activity: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function delete($id) {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "UPDATE activities 
                    SET status = 'deleted', 
                        deleted_at = NOW() 
                    WHERE id = :id AND status = 'active'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            if ($stmt->rowCount() === 0) {
                return ResponseHandler::error('Activity not found', 404);
            }
            
            Logger::info("Activity deleted: {$id}");
            
            return ResponseHandler::success(null, 'Activity deleted successfully');
        } catch (\Exception $e) {
            Logger::error("Failed to delete activity: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
} 