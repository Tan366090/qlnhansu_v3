<?php
namespace App\Models;

use PDO;
use PDOException;

class Notification extends BaseModel {
    protected $table = 'notifications';
    protected $primaryKey = 'notification_id';

    public function createNotification($userId, $title, $message, $type = 'info', $status = 'unread', $link = null) {
        try {
            $data = [
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'status' => $status,
                'link' => $link,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $notificationId = $this->create($data);
            return [
                'success' => true,
                'notification_id' => $notificationId
            ];
        } catch (PDOException $e) {
            error_log("Create Notification Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Lỗi hệ thống'
            ];
        }
    }

    public function updateNotification($notificationId, $title = null, $message = null, $type = null, $status = null, $link = null) {
        try {
            $data = [
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($title !== null) {
                $data['title'] = $title;
            }
            if ($message !== null) {
                $data['message'] = $message;
            }
            if ($type !== null) {
                $data['type'] = $type;
            }
            if ($status !== null) {
                $data['status'] = $status;
            }
            if ($link !== null) {
                $data['link'] = $link;
            }

            return $this->update($notificationId, $data);
        } catch (PDOException $e) {
            error_log("Update Notification Error: " . $e->getMessage());
            return false;
        }
    }

    public function updateNotificationStatus($notificationId, $status) {
        try {
            $data = [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            return $this->update($notificationId, $data);
        } catch (PDOException $e) {
            error_log("Update Notification Status Error: " . $e->getMessage());
            return false;
        }
    }

    public function getNotificationDetails($notificationId) {
        try {
            $query = "SELECT n.*, u.username, u.full_name 
                     FROM {$this->table} n
                     JOIN users u ON n.user_id = u.user_id
                     WHERE n.notification_id = ?";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$notificationId]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Notification Details Error: " . $e->getMessage());
            return false;
        }
    }

    public function getUserNotifications($userId, $status = null, $limit = 10) {
        try {
            $query = "SELECT n.* 
                     FROM {$this->table} n
                     WHERE n.user_id = ?";
            $params = [$userId];

            if ($status) {
                $query .= " AND n.status = ?";
                $params[] = $status;
            }

            $query .= " ORDER BY n.created_at DESC LIMIT ?";
            $params[] = $limit;

            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get User Notifications Error: " . $e->getMessage());
            return [];
        }
    }

    public function getUnreadNotifications($userId) {
        try {
            $query = "SELECT n.* 
                     FROM {$this->table} n
                     WHERE n.user_id = ? AND n.status = 'unread'
                     ORDER BY n.created_at DESC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$userId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Unread Notifications Error: " . $e->getMessage());
            return [];
        }
    }

    public function markAllAsRead($userId) {
        try {
            $query = "UPDATE {$this->table} 
                     SET status = 'read', updated_at = NOW()
                     WHERE user_id = ? AND status = 'unread'";
            $stmt = $this->db->getConnection()->prepare($query);
            return $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log("Mark All As Read Error: " . $e->getMessage());
            return false;
        }
    }

    public function getNotificationStats($userId = null) {
        try {
            $query = "SELECT 
                        COUNT(DISTINCT n.notification_id) as total_notifications,
                        COUNT(DISTINCT CASE WHEN n.status = 'unread' THEN n.notification_id END) as unread_notifications,
                        COUNT(DISTINCT CASE WHEN n.status = 'read' THEN n.notification_id END) as read_notifications,
                        COUNT(DISTINCT n.type) as notification_types
                     FROM {$this->table} n";
            
            $params = [];
            if ($userId) {
                $query .= " WHERE n.user_id = ?";
                $params[] = $userId;
            }

            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Notification Stats Error: " . $e->getMessage());
            return false;
        }
    }

    public function searchNotifications($keyword, $userId = null, $type = null) {
        try {
            $query = "SELECT n.*, u.username, u.full_name 
                     FROM {$this->table} n
                     JOIN users u ON n.user_id = u.user_id
                     WHERE (n.title LIKE ? OR n.message LIKE ?)";
            $params = ["%$keyword%", "%$keyword%"];

            if ($userId) {
                $query .= " AND n.user_id = ?";
                $params[] = $userId;
            }

            if ($type) {
                $query .= " AND n.type = ?";
                $params[] = $type;
            }

            $query .= " ORDER BY n.created_at DESC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Search Notifications Error: " . $e->getMessage());
            return [];
        }
    }
} 