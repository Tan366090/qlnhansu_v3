<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../../../../config/database.php';

class UserAPI {
    private $conn;
    private $requestMethod;
    private $userId;

    public function __construct($requestMethod, $userId = null) {
        $this->conn = Database::getInstance()->getConnection();
        $this->requestMethod = $requestMethod;
        $this->userId = $userId;
    }

    public function processRequest() {
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->userId) {
                    $response = $this->getUser($this->userId);
                } else {
                    $response = $this->getAllUsers();
                }
                break;
            case 'POST':
                $response = $this->createUser();
                break;
            case 'PUT':
                $response = $this->updateUser($this->userId);
                break;
            case 'DELETE':
                $response = $this->deleteUser($this->userId);
                break;
            default:
                $response = $this->notFoundResponse();
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function getAllUsers() {
        $query = "SELECT u.*, r.name as role_name, d.name as department_name 
                 FROM users u 
                 LEFT JOIN roles r ON u.role_id = r.id 
                 LEFT JOIN departments d ON u.department_id = d.id
                 ORDER BY u.created_at DESC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'status_code_header' => 'HTTP/1.1 200 OK',
                'body' => json_encode([
                    'success' => true,
                    'data' => $result
                ])
            ];
        } catch (PDOException $e) {
            return [
                'status_code_header' => 'HTTP/1.1 500 Internal Server Error',
                'body' => json_encode([
                    'success' => false,
                    'message' => 'Database error: ' . $e->getMessage()
                ])
            ];
        }
    }

    private function getUser($id) {
        $query = "SELECT u.*, r.name as role_name, d.name as department_name 
                 FROM users u 
                 LEFT JOIN roles r ON u.role_id = r.id 
                 LEFT JOIN departments d ON u.department_id = d.id
                 WHERE u.id = :id";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return $this->notFoundResponse();
            }
            
            // Lấy thông tin profile
            $profile = $this->getUserProfile($id);
            if ($profile) {
                $result['profile'] = $profile;
            }
            
            // Lấy thông tin quyền
            $permissions = $this->getUserPermissions($id);
            if ($permissions) {
                $result['permissions'] = $permissions;
            }
            
            return [
                'status_code_header' => 'HTTP/1.1 200 OK',
                'body' => json_encode([
                    'success' => true,
                    'data' => $result
                ])
            ];
        } catch (PDOException $e) {
            return [
                'status_code_header' => 'HTTP/1.1 500 Internal Server Error',
                'body' => json_encode([
                    'success' => false,
                    'message' => 'Database error: ' . $e->getMessage()
                ])
            ];
        }
    }

    private function getUserProfile($userId) {
        $query = "SELECT * FROM user_profiles WHERE user_id = :user_id";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    private function getUserPermissions($userId) {
        $query = "SELECT p.* FROM role_permissions rp 
                 JOIN permissions p ON rp.permission_id = p.id 
                 JOIN roles r ON rp.role_id = r.id 
                 JOIN users u ON u.role_id = r.id 
                 WHERE u.id = :user_id";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    private function createUser() {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        
        if (!$this->validateUser($input)) {
            return $this->unprocessableEntityResponse();
        }
        
        try {
            $this->conn->beginTransaction();
            
            // Tạo user
            $query = "INSERT INTO users (
                username, email, password, role_id, department_id, 
                status, is_active, last_login, created_at, updated_at
            ) VALUES (
                :username, :email, :password, :role_id, :department_id,
                :status, :is_active, :last_login, NOW(), NOW()
            )";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':username' => $input['username'],
                ':email' => $input['email'],
                ':password' => password_hash($input['password'], PASSWORD_DEFAULT),
                ':role_id' => $input['role_id'],
                ':department_id' => $input['department_id'] ?? null,
                ':status' => $input['status'] ?? 'active',
                ':is_active' => $input['is_active'] ?? 1,
                ':last_login' => null
            ]);
            
            $userId = $this->conn->lastInsertId();
            
            // Tạo profile
            if (isset($input['profile'])) {
                $this->createUserProfile($userId, $input['profile']);
            }
            
            $this->conn->commit();
            
            return [
                'status_code_header' => 'HTTP/1.1 201 Created',
                'body' => json_encode([
                    'success' => true,
                    'message' => 'User created successfully',
                    'id' => $userId
                ])
            ];
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return [
                'status_code_header' => 'HTTP/1.1 500 Internal Server Error',
                'body' => json_encode([
                    'success' => false,
                    'message' => 'Database error: ' . $e->getMessage()
                ])
            ];
        }
    }

    private function createUserProfile($userId, $profile) {
        $query = "INSERT INTO user_profiles (
            user_id, full_name, phone, address, avatar, 
            gender, date_of_birth, marital_status, 
            emergency_contact, emergency_phone, 
            created_at, updated_at
        ) VALUES (
            :user_id, :full_name, :phone, :address, :avatar,
            :gender, :date_of_birth, :marital_status,
            :emergency_contact, :emergency_phone,
            NOW(), NOW()
        )";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':user_id' => $userId,
            ':full_name' => $profile['full_name'] ?? null,
            ':phone' => $profile['phone'] ?? null,
            ':address' => $profile['address'] ?? null,
            ':avatar' => $profile['avatar'] ?? null,
            ':gender' => $profile['gender'] ?? null,
            ':date_of_birth' => $profile['date_of_birth'] ?? null,
            ':marital_status' => $profile['marital_status'] ?? null,
            ':emergency_contact' => $profile['emergency_contact'] ?? null,
            ':emergency_phone' => $profile['emergency_phone'] ?? null
        ]);
    }

    private function updateUser($id) {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        
        if (!$this->validateUser($input)) {
            return $this->unprocessableEntityResponse();
        }
        
        try {
            $this->conn->beginTransaction();
            
            // Cập nhật user
            $query = "UPDATE users 
                     SET username = :username,
                         email = :email,
                         role_id = :role_id,
                         department_id = :department_id,
                         status = :status,
                         is_active = :is_active,
                         updated_at = NOW()
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':id' => $id,
                ':username' => $input['username'],
                ':email' => $input['email'],
                ':role_id' => $input['role_id'],
                ':department_id' => $input['department_id'] ?? null,
                ':status' => $input['status'],
                ':is_active' => $input['is_active'] ?? 1
            ]);
            
            if ($stmt->rowCount() === 0) {
                return $this->notFoundResponse();
            }
            
            // Cập nhật profile
            if (isset($input['profile'])) {
                $this->updateUserProfile($id, $input['profile']);
            }
            
            $this->conn->commit();
            
            return [
                'status_code_header' => 'HTTP/1.1 200 OK',
                'body' => json_encode([
                    'success' => true,
                    'message' => 'User updated successfully'
                ])
            ];
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return [
                'status_code_header' => 'HTTP/1.1 500 Internal Server Error',
                'body' => json_encode([
                    'success' => false,
                    'message' => 'Database error: ' . $e->getMessage()
                ])
            ];
        }
    }

    private function updateUserProfile($userId, $profile) {
        $query = "UPDATE user_profiles 
                 SET full_name = :full_name,
                     phone = :phone,
                     address = :address,
                     avatar = :avatar,
                     gender = :gender,
                     date_of_birth = :date_of_birth,
                     marital_status = :marital_status,
                     emergency_contact = :emergency_contact,
                     emergency_phone = :emergency_phone,
                     updated_at = NOW()
                 WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':user_id' => $userId,
            ':full_name' => $profile['full_name'] ?? null,
            ':phone' => $profile['phone'] ?? null,
            ':address' => $profile['address'] ?? null,
            ':avatar' => $profile['avatar'] ?? null,
            ':gender' => $profile['gender'] ?? null,
            ':date_of_birth' => $profile['date_of_birth'] ?? null,
            ':marital_status' => $profile['marital_status'] ?? null,
            ':emergency_contact' => $profile['emergency_contact'] ?? null,
            ':emergency_phone' => $profile['emergency_phone'] ?? null
        ]);
    }

    private function deleteUser($id) {
        try {
            $this->conn->beginTransaction();
            
            // Xóa profile
            $query = "DELETE FROM user_profiles WHERE user_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $id]);
            
            // Xóa user
            $query = "DELETE FROM users WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $id]);
            
            if ($stmt->rowCount() === 0) {
                return $this->notFoundResponse();
            }
            
            $this->conn->commit();
            
            return [
                'status_code_header' => 'HTTP/1.1 200 OK',
                'body' => json_encode([
                    'success' => true,
                    'message' => 'User deleted successfully'
                ])
            ];
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return [
                'status_code_header' => 'HTTP/1.1 500 Internal Server Error',
                'body' => json_encode([
                    'success' => false,
                    'message' => 'Database error: ' . $e->getMessage()
                ])
            ];
        }
    }

    private function validateUser($input) {
        if (!isset($input['username']) || !isset($input['email']) || !isset($input['role_id'])) {
            return false;
        }
        return true;
    }

    private function unprocessableEntityResponse() {
        return [
            'status_code_header' => 'HTTP/1.1 422 Unprocessable Entity',
            'body' => json_encode([
                'success' => false,
                'message' => 'Invalid input'
            ])
        ];
    }

    private function notFoundResponse() {
        return [
            'status_code_header' => 'HTTP/1.1 404 Not Found',
            'body' => json_encode([
                'success' => false,
                'message' => 'Not Found'
            ])
        ];
    }
}

// Get request method and user ID
$requestMethod = $_SERVER['REQUEST_METHOD'];
$userId = isset($_GET['id']) ? $_GET['id'] : null;

// Create and process the API
$api = new UserAPI($requestMethod, $userId);
$api->processRequest();
?> 