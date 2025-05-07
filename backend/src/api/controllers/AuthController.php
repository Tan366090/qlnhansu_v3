<?php
namespace App\Controllers;

use App\Utils\ResponseHandler;
use App\Config\Database;

class AuthController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function login() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $conn = $this->db->getConnection();
            
            // Get user by username
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
            $stmt->execute([$data['username']]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return ResponseHandler::sendUnauthorized('Invalid credentials');
            }
            
            // Remove sensitive data
            unset($user['password']);
            
            return ResponseHandler::sendSuccess([
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return ResponseHandler::sendServerError($e->getMessage());
        }
    }
    
    public function logout() {
        return ResponseHandler::sendSuccess(['message' => 'Logged out successfully']);
    }
}
?> 