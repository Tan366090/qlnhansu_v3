<?php
namespace App\Models;

class UserProfile extends BaseModel {
    protected $table = 'user_profiles';
    
    protected $fillable = [
        'user_id',
        'full_name',
        'phone_number',
        'email',
        'date_of_birth',
        'gender',
        'permanent_address',
        'current_address',
        'bank_account_number',
        'bank_name',
        'tax_code',
        'avatar_url',
        'signature_url',
        'status'
    ];
    
    public function getWithDetails($userId = null) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT up.*,
                   u.username,
                   u.role_id,
                   r.name as role_name,
                   e.employee_code,
                   e.department_id,
                   d.name as department_name,
                   e.position_id,
                   p.name as position_name
            FROM user_profiles up
            JOIN users u ON up.user_id = u.id
            JOIN roles r ON u.role_id = r.id
            LEFT JOIN employees e ON u.id = e.user_id
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN positions p ON e.position_id = p.id
            WHERE 1=1
        ";
        
        if ($userId) {
            $sql .= " AND up.user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$userId]);
            return $stmt->fetch();
        }
        
        $sql .= " ORDER BY up.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getByDepartment($departmentId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT up.*,
                   u.username,
                   u.role_id,
                   r.name as role_name,
                   e.employee_code,
                   e.department_id,
                   d.name as department_name,
                   e.position_id,
                   p.name as position_name
            FROM user_profiles up
            JOIN users u ON up.user_id = u.id
            JOIN roles r ON u.role_id = r.id
            JOIN employees e ON u.id = e.user_id
            JOIN departments d ON e.department_id = d.id
            LEFT JOIN positions p ON e.position_id = p.id
            WHERE e.department_id = ?
            ORDER BY up.created_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$departmentId]);
        return $stmt->fetchAll();
    }
    
    public function getByPosition($positionId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT up.*,
                   u.username,
                   u.role_id,
                   r.name as role_name,
                   e.employee_code,
                   e.department_id,
                   d.name as department_name,
                   e.position_id,
                   p.name as position_name
            FROM user_profiles up
            JOIN users u ON up.user_id = u.id
            JOIN roles r ON u.role_id = r.id
            JOIN employees e ON u.id = e.user_id
            JOIN departments d ON e.department_id = d.id
            JOIN positions p ON e.position_id = p.id
            WHERE e.position_id = ?
            ORDER BY up.created_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$positionId]);
        return $stmt->fetchAll();
    }
    
    public function updateProfileStatus($userId, $status) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE user_profiles
            SET status = ?,
                updated_at = NOW()
            WHERE user_id = ?
        ");
        
        return $stmt->execute([$status, $userId]);
    }
    
    public function updateAvatar($userId, $avatarUrl) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE user_profiles
            SET avatar_url = ?,
                updated_at = NOW()
            WHERE user_id = ?
        ");
        
        return $stmt->execute([$avatarUrl, $userId]);
    }
    
    public function updateSignature($userId, $signatureUrl) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE user_profiles
            SET signature_url = ?,
                updated_at = NOW()
            WHERE user_id = ?
        ");
        
        return $stmt->execute([$signatureUrl, $userId]);
    }
    
    public function searchProfiles($query) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT up.*,
                   u.username,
                   u.role_id,
                   r.name as role_name,
                   e.employee_code,
                   e.department_id,
                   d.name as department_name,
                   e.position_id,
                   p.name as position_name
            FROM user_profiles up
            JOIN users u ON up.user_id = u.id
            JOIN roles r ON u.role_id = r.id
            LEFT JOIN employees e ON u.id = e.user_id
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN positions p ON e.position_id = p.id
            WHERE up.full_name LIKE ?
            OR up.phone_number LIKE ?
            OR up.email LIKE ?
            OR e.employee_code LIKE ?
            ORDER BY up.created_at DESC
        ";
        
        $searchTerm = "%{$query}%";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
} 