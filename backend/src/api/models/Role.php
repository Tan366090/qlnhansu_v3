<?php
namespace App\Models;

class Role extends BaseModel {
    protected $table = 'roles';
    
    protected $fillable = [
        'name',
        'description',
        'permissions',
        'status'
    ];
    
    public function getWithDetails($id = null) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT r.*, 
                   COUNT(u.id) as user_count
            FROM roles r
            LEFT JOIN users u ON r.id = u.role_id
            WHERE 1=1
        ";
        
        if ($id) {
            $sql .= " AND r.id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        }
        
        $sql .= " GROUP BY r.id ORDER BY r.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getActiveRoles() {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT r.*, 
                   COUNT(u.id) as user_count
            FROM roles r
            LEFT JOIN users u ON r.id = u.role_id
            WHERE r.status = 'active'
            GROUP BY r.id
            ORDER BY r.created_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getRoleUsers($roleId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT u.*, 
                   up.full_name,
                   up.phone_number,
                   up.email,
                   d.name as department_name,
                   p.name as position_name
            FROM users u
            JOIN user_profiles up ON u.id = up.user_id
            LEFT JOIN employees e ON u.id = e.user_id
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN positions p ON e.position_id = p.id
            WHERE u.role_id = ?
            ORDER BY u.created_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$roleId]);
        return $stmt->fetchAll();
    }
    
    public function updateRolePermissions($roleId, $permissions) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE roles
            SET permissions = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([json_encode($permissions), $roleId]);
    }
    
    public function updateRoleStatus($roleId, $status) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE roles
            SET status = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([$status, $roleId]);
    }
    
    public function checkPermission($roleId, $permission) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            SELECT permissions
            FROM roles
            WHERE id = ?
        ");
        
        $stmt->execute([$roleId]);
        $result = $stmt->fetch();
        
        if (!$result) {
            return false;
        }
        
        $permissions = json_decode($result['permissions'], true);
        return in_array($permission, $permissions);
    }
    
    public function searchRoles($query) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT r.*, 
                   COUNT(u.id) as user_count
            FROM roles r
            LEFT JOIN users u ON r.id = u.role_id
            WHERE r.name LIKE ?
            OR r.description LIKE ?
            GROUP BY r.id
            ORDER BY r.created_at DESC
        ";
        
        $searchTerm = "%{$query}%";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
} 