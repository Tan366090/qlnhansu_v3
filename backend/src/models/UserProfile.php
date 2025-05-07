<?php
require_once __DIR__ . '/BaseModel.php';

class UserProfile extends BaseModel {
    public function __construct() {
        parent::__construct();
        $this->table_name = 'user_profiles';
    }
    
    public function getProfileByUserId($user_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function createProfile($data) {
        // Set default values
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        // Get next available profile_id
        $query = "SELECT COALESCE(MAX(profile_id), 0) + 1 as next_id FROM " . $this->table_name;
        $stmt = $this->conn->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $data['profile_id'] = $result['next_id'];
        
        return $this->create($data);
    }
    
    public function updateProfile($profile_id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($profile_id, $data);
    }
    
    public function getProfileWithUserInfo($profile_id) {
        $query = "SELECT p.*, u.username, u.email, u.role_id 
                FROM " . $this->table_name . " p 
                LEFT JOIN users u ON p.user_id = u.id 
                WHERE p.profile_id = :profile_id";
                
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':profile_id', $profile_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function searchProfiles($search_term, $limit = null, $offset = null) {
        $query = "SELECT p.*, u.username, u.email 
                FROM " . $this->table_name . " p 
                LEFT JOIN users u ON p.user_id = u.id 
                WHERE p.full_name LIKE :search_term 
                OR p.phone_number LIKE :search_term 
                OR p.id_card_number LIKE :search_term";
                
        if ($limit !== null) {
            $query .= " LIMIT :limit";
            if ($offset !== null) {
                $query .= " OFFSET :offset";
            }
        }
        
        $stmt = $this->conn->prepare($query);
        $search_term = "%" . $search_term . "%";
        $stmt->bindParam(':search_term', $search_term);
        
        if ($limit !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            if ($offset !== null) {
                $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            }
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getProfilesByDepartment($department_id, $limit = null, $offset = null) {
        $query = "SELECT p.*, u.username, u.email, e.position_id 
                FROM " . $this->table_name . " p 
                LEFT JOIN users u ON p.user_id = u.id 
                LEFT JOIN employees e ON u.id = e.user_id 
                WHERE e.department_id = :department_id";
                
        if ($limit !== null) {
            $query .= " LIMIT :limit";
            if ($offset !== null) {
                $query .= " OFFSET :offset";
            }
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':department_id', $department_id);
        
        if ($limit !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            if ($offset !== null) {
                $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            }
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function updateAvatar($profile_id, $avatar_url) {
        $query = "UPDATE " . $this->table_name . " 
                SET avatar_url = :avatar_url, 
                    updated_at = :updated_at 
                WHERE profile_id = :profile_id";
                
        $stmt = $this->conn->prepare($query);
        $now = date('Y-m-d H:i:s');
        $stmt->bindParam(':avatar_url', $avatar_url);
        $stmt->bindParam(':updated_at', $now);
        $stmt->bindParam(':profile_id', $profile_id);
        
        return $stmt->execute();
    }
} 