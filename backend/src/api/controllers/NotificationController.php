<?php
namespace App\Controllers;

use App\Utils\ResponseHandler;
use App\Config\Database;
use App\Utils\RBACHandler;
use App\Utils\Logger;

class NotificationController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function index($userId, $page = 1, $perPage = 10) {
        try {
            $conn = $this->db->getConnection();
            
            // Get total count
            $sql = "SELECT COUNT(*) FROM notifications 
                    WHERE user_id = :user_id AND status = 'active'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            
            $total = $stmt->fetchColumn();
            $totalPages = ceil($total / $perPage);
            $offset = ($page - 1) * $perPage;
            
            // Get paginated notifications
            $sql = "SELECT * FROM notifications 
                    WHERE user_id = :user_id AND status = 'active' 
                    ORDER BY created_at DESC 
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
            $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            
            $notifications = $stmt->fetchAll();
            
            return ResponseHandler::success([
                'notifications' => $notifications,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'total_pages' => $totalPages
                ]
            ], 'Notifications retrieved successfully');
        } catch (\Exception $e) {
            Logger::error("Failed to get notifications: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function show($id) {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "SELECT * FROM notifications 
                    WHERE id = :id AND status = 'active'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            $notification = $stmt->fetch();
            
            if (!$notification) {
                return ResponseHandler::error('Notification not found', 404);
            }
            
            return ResponseHandler::success($notification, 'Notification retrieved successfully');
        } catch (\Exception $e) {
            Logger::error("Failed to get notification: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function create($userId, $type, $title, $message, $data = null) {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "INSERT INTO notifications (user_id, type, title, 
                    message, data, status) 
                    VALUES (:user_id, :type, :title, 
                    :message, :data, 'active')";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':type' => $type,
                ':title' => $title,
                ':message' => $message,
                ':data' => $data ? json_encode($data) : null
            ]);
            
            $notificationId = $conn->lastInsertId();
            
            Logger::info("Notification created for user: {$userId}");
            
            return ResponseHandler::success([
                'id' => $notificationId
            ], 'Notification created successfully');
        } catch (\Exception $e) {
            Logger::error("Failed to create notification: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function markAsRead($id) {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "UPDATE notifications 
                    SET is_read = 1, 
                        read_at = NOW() 
                    WHERE id = :id AND status = 'active'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            if ($stmt->rowCount() === 0) {
                return ResponseHandler::error('Notification not found', 404);
            }
            
            Logger::info("Notification marked as read: {$id}");
            
            return ResponseHandler::success(null, 'Notification marked as read');
        } catch (\Exception $e) {
            Logger::error("Failed to mark notification as read: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function markAllAsRead($userId) {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "UPDATE notifications 
                    SET is_read = 1, 
                        read_at = NOW() 
                    WHERE user_id = :user_id 
                    AND status = 'active' 
                    AND is_read = 0";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            
            $affectedRows = $stmt->rowCount();
            
            Logger::info("Marked {$affectedRows} notifications as read for user: {$userId}");
            
            return ResponseHandler::success([
                'notifications_marked' => $affectedRows
            ], 'All notifications marked as read');
        } catch (\Exception $e) {
            Logger::error("Failed to mark all notifications as read: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function delete($id) {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "UPDATE notifications 
                    SET status = 'deleted', 
                        deleted_at = NOW() 
                    WHERE id = :id AND status = 'active'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            if ($stmt->rowCount() === 0) {
                return ResponseHandler::error('Notification not found', 404);
            }
            
            Logger::info("Notification deleted: {$id}");
            
            return ResponseHandler::success(null, 'Notification deleted successfully');
        } catch (\Exception $e) {
            Logger::error("Failed to delete notification: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function getUnreadCount($userId) {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "SELECT COUNT(*) FROM notifications 
                    WHERE user_id = :user_id 
                    AND status = 'active' 
                    AND is_read = 0";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            
            $count = $stmt->fetchColumn();
            
            return ResponseHandler::success([
                'unread_count' => $count
            ], 'Unread count retrieved successfully');
        } catch (\Exception $e) {
            Logger::error("Failed to get unread count: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
} 