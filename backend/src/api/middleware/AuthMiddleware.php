<?php
namespace App\Middleware;

use Exception;
use App\Config\Database;
use App\Utils\ResponseHandler;

class AuthMiddleware {
    private $db;
    private $rateLimit = 5; // Số lần đăng nhập tối đa trong 1 phút
    private $rateLimitWindow = 60; // Thời gian cửa sổ (giây)
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function checkRateLimit($ip) {
        $conn = $this->db->getConnection();
        $now = time();
        $windowStart = $now - $this->rateLimitWindow;
        
        // Xóa các bản ghi cũ
        $stmt = $conn->prepare("DELETE FROM login_attempts WHERE timestamp < ?");
        $stmt->execute([$windowStart]);
        
        // Đếm số lần đăng nhập trong cửa sổ
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM login_attempts WHERE ip = ? AND timestamp > ?");
        $stmt->execute([$ip, $windowStart]);
        $result = $stmt->fetch();
        
        if ($result['count'] >= $this->rateLimit) {
            throw new Exception('Too many login attempts. Please try again later.', 429);
        }
        
        // Ghi nhận lần đăng nhập
        $stmt = $conn->prepare("INSERT INTO login_attempts (ip, timestamp) VALUES (?, ?)");
        $stmt->execute([$ip, $now]);
    }
    
    public function authenticate() {
        // Get token from header
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? '';
        
        if (empty($token)) {
            throw new Exception('Authorization token is required', 401);
        }
        
        // Remove 'Bearer ' prefix if present
        $token = str_replace('Bearer ', '', $token);
        
        try {
            $conn = $this->db->getConnection();
            
            // Get user by token
            $stmt = $conn->prepare("SELECT * FROM users WHERE remember_token = ? AND token_expires_at > NOW() AND status = 'active' LIMIT 1");
            $stmt->execute([$token]);
            $user = $stmt->fetch();
            
            if (!$user) {
                // Kiểm tra refresh token
                $stmt = $conn->prepare("SELECT * FROM users WHERE refresh_token = ? AND refresh_token_expires_at > NOW() AND status = 'active' LIMIT 1");
                $stmt->execute([$token]);
                $user = $stmt->fetch();
                
                if ($user) {
                    // Tạo token mới
                    $newToken = bin2hex(random_bytes(32));
                    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 day'));
                    
                    $stmt = $conn->prepare("UPDATE users SET remember_token = ?, token_expires_at = ? WHERE id = ?");
                    $stmt->execute([$newToken, $expiresAt, $user['id']]);
                    
                    // Set header với token mới
                    header('X-New-Token: ' . $newToken);
                    header('X-Token-Expires-At: ' . $expiresAt);
                } else {
                    throw new Exception('Invalid or expired token', 401);
                }
            }
            
            // Remove sensitive data
            unset($user['password']);
            unset($user['remember_token']);
            unset($user['token_expires_at']);
            unset($user['refresh_token']);
            unset($user['refresh_token_expires_at']);
            
            // Store user in session
            $_SESSION['user'] = $user;
            
            return true;
        } catch (Exception $e) {
            throw new Exception('Authentication failed: ' . $e->getMessage(), 401);
        }
    }

    public static function requireAuth() {
        SessionHelper::requireAuth();
    }

    public static function requireRole($roles) {
        SessionHelper::requireRole($roles);
    }

    public static function getCurrentUser() {
        return SessionHelper::getCurrentUser();
    }

    public static function isAuthenticated() {
        return SessionHelper::isAuthenticated();
    }
} 