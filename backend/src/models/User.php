<?php
require_once __DIR__ . '/BaseModel.php';

class User extends BaseModel {
    public function __construct() {
        parent::__construct();
        $this->table_name = 'users';
    }
    
    public function findByUsername($username) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function findByEmail($email) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function createUser($data) {
        // Hash password before saving
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }
        
        // Set default values
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->create($data);
    }
    
    public function updateUser($id, $data) {
        // Hash password if it's being updated
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->update($id, $data);
    }
    
    public function verifyPassword($user_id, $password) {
        $query = "SELECT password_hash FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return password_verify($password, $result['password_hash']);
        }
        return false;
    }
    
    public function updateLastLogin($user_id) {
        $query = "UPDATE " . $this->table_name . " 
                SET last_login = :last_login 
                WHERE id = :id";
                
        $stmt = $this->conn->prepare($query);
        $now = date('Y-m-d H:i:s');
        $stmt->bindParam(':last_login', $now);
        $stmt->bindParam(':id', $user_id);
        
        return $stmt->execute();
    }
    
    public function getUsersWithRoles($limit = null, $offset = null) {
        $query = "SELECT u.*, r.role_name 
                FROM " . $this->table_name . " u 
                LEFT JOIN roles r ON u.role_id = r.id";
                
        if ($limit !== null) {
            $query .= " LIMIT :limit";
            if ($offset !== null) {
                $query .= " OFFSET :offset";
            }
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($limit !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            if ($offset !== null) {
                $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            }
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?> 