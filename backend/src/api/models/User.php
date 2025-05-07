<?php
namespace App\Models;

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../auth/SessionHelper.php';

class User extends BaseModel {
    protected $table = 'users';
    
    protected $fillable = [
        'username',
        'email',
        'password',
        'role_id',
        'status',
        'last_login',
        'last_ip',
        'remember_token',
        'email_verified_at'
    ];
    
    public function getWithDetails($id = null) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT u.*, 
                   r.name as role_name,
                   r.permissions,
                   up.full_name,
                   up.phone_number,
                   up.date_of_birth,
                   up.gender,
                   up.permanent_address,
                   up.current_address,
                   up.bank_account_number,
                   up.bank_name,
                   up.tax_code
            FROM users u
            JOIN roles r ON u.role_id = r.id
            LEFT JOIN user_profiles up ON u.id = up.user_id
            WHERE 1=1
        ";
        
        if ($id) {
            $sql .= " AND u.id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        }
        
        $sql .= " ORDER BY u.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getByRole($roleId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT u.*, 
                   r.name as role_name,
                   r.permissions,
                   up.full_name,
                   up.phone_number,
                   up.date_of_birth,
                   up.gender,
                   up.permanent_address,
                   up.current_address,
                   up.bank_account_number,
                   up.bank_name,
                   up.tax_code
            FROM users u
            JOIN roles r ON u.role_id = r.id
            LEFT JOIN user_profiles up ON u.id = up.user_id
            WHERE u.role_id = ?
            ORDER BY u.created_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$roleId]);
        return $stmt->fetchAll();
    }
    
    public function getActiveUsers() {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT u.*, 
                   r.name as role_name,
                   r.permissions,
                   up.full_name,
                   up.phone_number,
                   up.date_of_birth,
                   up.gender,
                   up.permanent_address,
                   up.current_address,
                   up.bank_account_number,
                   up.bank_name,
                   up.tax_code
            FROM users u
            JOIN roles r ON u.role_id = r.id
            LEFT JOIN user_profiles up ON u.id = up.user_id
            WHERE u.status = 'active'
            ORDER BY u.created_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getInactiveUsers() {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT u.*, 
                   r.name as role_name,
                   r.permissions,
                   up.full_name,
                   up.phone_number,
                   up.date_of_birth,
                   up.gender,
                   up.permanent_address,
                   up.current_address,
                   up.bank_account_number,
                   up.bank_name,
                   up.tax_code
            FROM users u
            JOIN roles r ON u.role_id = r.id
            LEFT JOIN user_profiles up ON u.id = up.user_id
            WHERE u.status = 'inactive'
            ORDER BY u.created_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getByDepartment($departmentId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT u.*, 
                   r.name as role_name,
                   r.permissions,
                   up.full_name,
                   up.phone_number,
                   up.date_of_birth,
                   up.gender,
                   up.permanent_address,
                   up.current_address,
                   up.bank_account_number,
                   up.bank_name,
                   up.tax_code,
                   e.employee_code,
                   d.name as department_name,
                   p.name as position_name
            FROM users u
            JOIN roles r ON u.role_id = r.id
            LEFT JOIN user_profiles up ON u.id = up.user_id
            LEFT JOIN employees e ON u.id = e.user_id
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN positions p ON e.position_id = p.id
            WHERE e.department_id = ?
            ORDER BY u.created_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$departmentId]);
        return $stmt->fetchAll();
    }
    
    public function getByPosition($positionId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT u.*, 
                   r.name as role_name,
                   r.permissions,
                   up.full_name,
                   up.phone_number,
                   up.date_of_birth,
                   up.gender,
                   up.permanent_address,
                   up.current_address,
                   up.bank_account_number,
                   up.bank_name,
                   up.tax_code,
                   e.employee_code,
                   d.name as department_name,
                   p.name as position_name
            FROM users u
            JOIN roles r ON u.role_id = r.id
            LEFT JOIN user_profiles up ON u.id = up.user_id
            LEFT JOIN employees e ON u.id = e.user_id
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN positions p ON e.position_id = p.id
            WHERE e.position_id = ?
            ORDER BY u.created_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$positionId]);
        return $stmt->fetchAll();
    }
    
    public function updateUserStatus($userId, $status) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE users
            SET status = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([$status, $userId]);
    }
    
    public function updateUserRole($userId, $roleId) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE users
            SET role_id = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([$roleId, $userId]);
    }
    
    public function updateLastLogin($userId, $ip) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE users
            SET last_login = NOW(),
                last_ip = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([$ip, $userId]);
    }
    
    public function updateRememberToken($userId, $token) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE users
            SET remember_token = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([$token, $userId]);
    }
    
    public function verifyEmail($userId) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE users
            SET email_verified_at = NOW(),
                updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([$userId]);
    }
    
    public function searchUsers($query) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT u.*, 
                   r.name as role_name,
                   r.permissions,
                   up.full_name,
                   up.phone_number,
                   up.date_of_birth,
                   up.gender,
                   up.permanent_address,
                   up.current_address,
                   up.bank_account_number,
                   up.bank_name,
                   up.tax_code,
                   e.employee_code,
                   d.name as department_name,
                   p.name as position_name
            FROM users u
            JOIN roles r ON u.role_id = r.id
            LEFT JOIN user_profiles up ON u.id = up.user_id
            LEFT JOIN employees e ON u.id = e.user_id
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN positions p ON e.position_id = p.id
            WHERE u.username LIKE ?
            OR u.email LIKE ?
            OR up.full_name LIKE ?
            OR up.phone_number LIKE ?
            ORDER BY u.created_at DESC
        ";
        
        $searchTerm = "%{$query}%";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }

    public function authenticate($username, $password) {
        error_log("[User Authentication] Starting authentication for username: " . $username);
        
        try {
            $query = "SELECT u.*, r.name as role_name 
                     FROM users u 
                     LEFT JOIN roles r ON u.role_id = r.id 
                     WHERE u.username = :username AND u.status = 'active'";
            
            error_log("[User Authentication] Query: " . $query);
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                error_log("[User Authentication] User not found: " . $username);
                return ['success' => false, 'error' => 'Tên đăng nhập hoặc mật khẩu không đúng'];
            }
            
            error_log("[User Authentication] User found: " . print_r($user, true));
            
            if (!password_verify($password, $user['password'])) {
                error_log("[User Authentication] Password mismatch for user: " . $username);
                return ['success' => false, 'error' => 'Tên đăng nhập hoặc mật khẩu không đúng'];
            }
            
            error_log("[User Authentication] Authentication successful for user: " . $username);
            
            // Update last login
            $this->updateLastLogin($user['id'], $_SERVER['REMOTE_ADDR']);
            
            return ['success' => true, 'user' => $user];
            
        } catch (Exception $e) {
            error_log("[User Authentication] Exception: " . $e->getMessage());
            return ['success' => false, 'error' => 'Có lỗi xảy ra khi xác thực'];
        }
    }

    private function hashPassword($password, $salt) {
        return hash('sha256', $password . $salt);
    }

    private function incrementLoginAttempts($userId) {
        $query = "UPDATE " . $this->table . " 
                 SET login_attempts = login_attempts + 1,
                     last_login_attempt = NOW()
                 WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
    }

    private function resetLoginAttempts($userId) {
        $query = "UPDATE " . $this->table . " 
                 SET login_attempts = 0,
                     last_login_attempt = NULL
                 WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
    }

    public function getUserById($id) {
        try {
            $query = "SELECT u.*, r.role_name 
                     FROM " . $this->table . " u
                     JOIN roles r ON u.role_id = r.role_id
                     WHERE u.user_id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting user by ID: " . $e->getMessage());
            return false;
        }
    }

    public function updatePassword($userId, $newPassword) {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $query = "UPDATE users 
                     SET password = :password,
                         updated_at = NOW()
                     WHERE id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':user_id', $userId);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating password: " . $e->getMessage());
            return false;
        }
    }

    public function logout() {
        session_destroy();
        session_start();
        session_regenerate_id(true);
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return $this->getWithDetails($_SESSION['user_id']);
        }
        return null;
    }
} 
