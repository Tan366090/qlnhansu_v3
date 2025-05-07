<?php
namespace App\Controllers;

use App\Utils\ResponseHandler;
use App\Config\Database;
use App\Utils\RBACHandler;
use App\Utils\Logger;

class EmailVerificationController {
    private $db;
    private const TOKEN_EXPIRY = 24 * 60 * 60; // 24 hours in seconds
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function generateToken($userId, $email) {
        try {
            $conn = $this->db->getConnection();
            
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + self::TOKEN_EXPIRY);
            
            $conn->beginTransaction();
            
            // Invalidate any existing tokens for this user
            $sql = "UPDATE email_verification_tokens 
                    SET status = 'expired' 
                    WHERE user_id = :user_id AND status = 'active'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            
            // Create new token
            $sql = "INSERT INTO email_verification_tokens (user_id, email, token, 
                    expires_at, status) 
                    VALUES (:user_id, :email, :token, 
                    :expires_at, 'active')";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':email' => $email,
                ':token' => $token,
                ':expires_at' => $expiresAt
            ]);
            
            $tokenId = $conn->lastInsertId();
            
            $conn->commit();
            
            Logger::info("Email verification token generated for user: {$userId}");
            
            return ResponseHandler::success([
                'token' => $token,
                'expires_at' => $expiresAt
            ], 'Verification token generated successfully');
        } catch (\Exception $e) {
            $conn->rollBack();
            Logger::error("Failed to generate verification token: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function verifyToken($token) {
        try {
            $conn = $this->db->getConnection();
            
            $conn->beginTransaction();
            
            $sql = "SELECT * FROM email_verification_tokens 
                    WHERE token = :token AND status = 'active' 
                    AND expires_at > NOW()";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':token' => $token]);
            
            $verification = $stmt->fetch();
            
            if (!$verification) {
                $conn->rollBack();
                return ResponseHandler::error('Invalid or expired token', 400);
            }
            
            // Mark token as used
            $sql = "UPDATE email_verification_tokens 
                    SET status = 'used', 
                        verified_at = NOW() 
                    WHERE id = :id";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $verification['id']]);
            
            // Update user's email verification status
            $sql = "UPDATE users 
                    SET email_verified_at = NOW() 
                    WHERE id = :user_id";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':user_id' => $verification['user_id']]);
            
            $conn->commit();
            
            Logger::info("Email verified for user: {$verification['user_id']}");
            
            return ResponseHandler::success(null, 'Email verified successfully');
        } catch (\Exception $e) {
            $conn->rollBack();
            Logger::error("Failed to verify email: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function resendVerification($userId) {
        try {
            $conn = $this->db->getConnection();
            
            // Get user's email
            $sql = "SELECT email FROM users WHERE id = :user_id";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            
            $user = $stmt->fetch();
            
            if (!$user) {
                return ResponseHandler::error('User not found', 404);
            }
            
            // Check if email is already verified
            $sql = "SELECT email_verified_at FROM users 
                    WHERE id = :user_id AND email_verified_at IS NOT NULL";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            
            if ($stmt->fetch()) {
                return ResponseHandler::error('Email already verified', 400);
            }
            
            // Generate new token
            return $this->generateToken($userId, $user['email']);
        } catch (\Exception $e) {
            Logger::error("Failed to resend verification: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function checkVerificationStatus($userId) {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "SELECT email_verified_at FROM users 
                    WHERE id = :user_id";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            
            $user = $stmt->fetch();
            
            if (!$user) {
                return ResponseHandler::error('User not found', 404);
            }
            
            return ResponseHandler::success([
                'is_verified' => !empty($user['email_verified_at']),
                'verified_at' => $user['email_verified_at']
            ]);
        } catch (\Exception $e) {
            Logger::error("Failed to check verification status: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function cleanupExpiredTokens() {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "UPDATE email_verification_tokens 
                    SET status = 'expired' 
                    WHERE status = 'active' 
                    AND expires_at <= NOW()";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            
            $affectedRows = $stmt->rowCount();
            
            Logger::info("Cleaned up {$affectedRows} expired verification tokens");
            
            return ResponseHandler::success([
                'expired_tokens_cleaned' => $affectedRows
            ], 'Expired tokens cleaned up successfully');
        } catch (\Exception $e) {
            Logger::error("Failed to cleanup expired tokens: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
} 