<?php
namespace App\Controllers;

use App\Utils\ResponseHandler;
use App\Config\Database;
use App\Utils\RBACHandler;
use App\Utils\Logger;

class PasswordResetController {
    private $db;
    private const TOKEN_EXPIRY = 1 * 60 * 60; // 1 hour in seconds
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function requestReset($email) {
        try {
            $conn = $this->db->getConnection();
            
            // Check if user exists
            $sql = "SELECT id FROM users WHERE email = :email";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':email' => $email]);
            
            $user = $stmt->fetch();
            
            if (!$user) {
                return ResponseHandler::error('User not found', 404);
            }
            
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + self::TOKEN_EXPIRY);
            
            $conn->beginTransaction();
            
            // Invalidate any existing tokens for this user
            $sql = "UPDATE password_reset_tokens 
                    SET status = 'expired' 
                    WHERE user_id = :user_id AND status = 'active'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':user_id' => $user['id']]);
            
            // Create new token
            $sql = "INSERT INTO password_reset_tokens (user_id, token, 
                    expires_at, status) 
                    VALUES (:user_id, :token, 
                    :expires_at, 'active')";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':user_id' => $user['id'],
                ':token' => $token,
                ':expires_at' => $expiresAt
            ]);
            
            $tokenId = $conn->lastInsertId();
            
            $conn->commit();
            
            Logger::info("Password reset token generated for user: {$user['id']}");
            
            return ResponseHandler::success([
                'token' => $token,
                'expires_at' => $expiresAt
            ], 'Password reset token generated successfully');
        } catch (\Exception $e) {
            $conn->rollBack();
            Logger::error("Failed to generate password reset token: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function verifyToken($token) {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "SELECT * FROM password_reset_tokens 
                    WHERE token = :token AND status = 'active' 
                    AND expires_at > NOW()";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':token' => $token]);
            
            $reset = $stmt->fetch();
            
            if (!$reset) {
                return ResponseHandler::error('Invalid or expired token', 400);
            }
            
            return ResponseHandler::success([
                'user_id' => $reset['user_id'],
                'expires_at' => $reset['expires_at']
            ], 'Token is valid');
        } catch (\Exception $e) {
            Logger::error("Failed to verify password reset token: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function resetPassword($token, $newPassword) {
        try {
            $conn = $this->db->getConnection();
            
            $conn->beginTransaction();
            
            // Get token details
            $sql = "SELECT * FROM password_reset_tokens 
                    WHERE token = :token AND status = 'active' 
                    AND expires_at > NOW()";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':token' => $token]);
            
            $reset = $stmt->fetch();
            
            if (!$reset) {
                $conn->rollBack();
                return ResponseHandler::error('Invalid or expired token', 400);
            }
            
            // Update user's password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $sql = "UPDATE users 
                    SET password = :password 
                    WHERE id = :user_id";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':password' => $hashedPassword,
                ':user_id' => $reset['user_id']
            ]);
            
            // Mark token as used
            $sql = "UPDATE password_reset_tokens 
                    SET status = 'used', 
                        used_at = NOW() 
                    WHERE id = :id";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $reset['id']]);
            
            $conn->commit();
            
            Logger::info("Password reset for user: {$reset['user_id']}");
            
            return ResponseHandler::success(null, 'Password reset successfully');
        } catch (\Exception $e) {
            $conn->rollBack();
            Logger::error("Failed to reset password: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function cleanupExpiredTokens() {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "UPDATE password_reset_tokens 
                    SET status = 'expired' 
                    WHERE status = 'active' 
                    AND expires_at <= NOW()";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            
            $affectedRows = $stmt->rowCount();
            
            Logger::info("Cleaned up {$affectedRows} expired password reset tokens");
            
            return ResponseHandler::success([
                'expired_tokens_cleaned' => $affectedRows
            ], 'Expired tokens cleaned up successfully');
        } catch (\Exception $e) {
            Logger::error("Failed to cleanup expired tokens: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
} 