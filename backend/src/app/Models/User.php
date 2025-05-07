<?php
namespace App\Models;

class User {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function create($username, $password, $email, $role, $status) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare("INSERT INTO users (username, password, email, role, status) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$username, $hashedPassword, $email, $role, $status]);
    }
    
    public function updatePassword($username, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE username = ?");
        return $stmt->execute([$hashedPassword, $username]);
    }
    
    public function exists($username) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->rowCount() > 0;
    }
} 