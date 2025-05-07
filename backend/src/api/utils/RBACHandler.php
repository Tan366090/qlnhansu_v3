<?php
namespace App\Utils;

class RBACHandler {
    private static $db;
    
    public static function init() {
        self::$db = \App\Config\Database::getInstance();
    }
    
    public static function hasPermission($userId, $permission) {
        try {
            if (!isset(self::$db)) {
                self::init();
            }
            
            $conn = self::$db->getConnection();
            
            $sql = "SELECT COUNT(*) as count 
                    FROM user_roles ur 
                    JOIN role_permissions rp ON ur.role_id = rp.role_id 
                    JOIN permissions p ON rp.permission_id = p.id 
                    WHERE ur.user_id = :user_id AND p.name = :permission";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':permission' => $permission
            ]);
            
            $result = $stmt->fetch();
            return $result['count'] > 0;
        } catch (\Exception $e) {
            Logger::error("Permission check failed: " . $e->getMessage());
            return false;
        }
    }
    
    public static function getRoles($userId) {
        try {
            if (!isset(self::$db)) {
                self::init();
            }
            
            $conn = self::$db->getConnection();
            
            $sql = "SELECT r.* FROM roles r 
                    JOIN user_roles ur ON r.id = ur.role_id 
                    WHERE ur.user_id = :user_id";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            Logger::error("Get roles failed: " . $e->getMessage());
            return [];
        }
    }
    
    public static function getPermissions($userId) {
        try {
            if (!isset(self::$db)) {
                self::init();
            }
            
            $conn = self::$db->getConnection();
            
            $sql = "SELECT DISTINCT p.* FROM permissions p 
                    JOIN role_permissions rp ON p.id = rp.permission_id 
                    JOIN user_roles ur ON rp.role_id = ur.role_id 
                    WHERE ur.user_id = :user_id";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            Logger::error("Get permissions failed: " . $e->getMessage());
            return [];
        }
    }
    
    public static function assignRole($userId, $roleId) {
        try {
            if (!isset(self::$db)) {
                self::init();
            }
            
            $conn = self::$db->getConnection();
            
            $sql = "INSERT INTO user_roles (user_id, role_id) 
                    VALUES (:user_id, :role_id)";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':role_id' => $roleId
            ]);
            
            return true;
        } catch (\Exception $e) {
            Logger::error("Role assignment failed: " . $e->getMessage());
            return false;
        }
    }
    
    public static function removeRole($userId, $roleId) {
        try {
            if (!isset(self::$db)) {
                self::init();
            }
            
            $conn = self::$db->getConnection();
            
            $sql = "DELETE FROM user_roles 
                    WHERE user_id = :user_id AND role_id = :role_id";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':role_id' => $roleId
            ]);
            
            return true;
        } catch (\Exception $e) {
            Logger::error("Role removal failed: " . $e->getMessage());
            return false;
        }
    }
    
    public static function logAudit($userId, $action, $details = []) {
        try {
            if (!isset(self::$db)) {
                self::init();
            }
            
            $conn = self::$db->getConnection();
            
            $sql = "INSERT INTO audit_logs (user_id, action, details, created_at) 
                    VALUES (:user_id, :action, :details, NOW())";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':action' => $action,
                ':details' => json_encode($details)
            ]);
            
            return true;
        } catch (\Exception $e) {
            Logger::error("Audit logging failed: " . $e->getMessage());
            return false;
        }
    }
} 