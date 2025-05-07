<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

class UsersAPI {
    private $conn;
    private $table_name = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy danh sách người dùng
    public function getUsers($params = []) {
        try {
            $query = "SELECT u.*, up.full_name, up.email, up.phone,
                     d.name as department_name, r.name as role_name
                     FROM " . $this->table_name . " u
                     LEFT JOIN user_profiles up ON u.id = up.user_id
                     LEFT JOIN departments d ON up.department_id = d.id
                     LEFT JOIN user_roles ur ON u.id = ur.user_id
                     LEFT JOIN roles r ON ur.role_id = r.id
                     WHERE 1=1";
            
            // Thêm điều kiện tìm kiếm
            if (!empty($params['search'])) {
                $search = $params['search'];
                $query .= " AND (u.username LIKE '%$search%' OR up.full_name LIKE '%$search%' OR up.email LIKE '%$search%')";
            }

            // Thêm điều kiện phòng ban
            if (!empty($params['department_id'])) {
                $department_id = $params['department_id'];
                $query .= " AND up.department_id = $department_id";
            }

            // Thêm điều kiện vai trò
            if (!empty($params['role_id'])) {
                $role_id = $params['role_id'];
                $query .= " AND ur.role_id = $role_id";
            }

            // Thêm điều kiện trạng thái
            if (isset($params['is_active'])) {
                $is_active = $params['is_active'] ? 1 : 0;
                $query .= " AND u.is_active = $is_active";
            }

            // Thêm phân trang
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            $query .= " GROUP BY u.id ORDER BY u.created_at DESC LIMIT $limit OFFSET $offset";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'status' => 'success',
                'data' => $users,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $this->getTotalUsers($params)
                ]
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Lấy thông tin chi tiết người dùng
    public function getUser($id) {
        try {
            // Lấy thông tin cơ bản
            $query = "SELECT u.*, up.*, d.name as department_name
                     FROM " . $this->table_name . " u
                     LEFT JOIN user_profiles up ON u.id = up.user_id
                     LEFT JOIN departments d ON up.department_id = d.id
                     WHERE u.id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Lấy danh sách vai trò
                $roles = $this->getUserRoles($id);
                $user['roles'] = $roles;

                // Lấy danh sách quyền
                $permissions = $this->getUserPermissions($id);
                $user['permissions'] = $permissions;

                return [
                    'status' => 'success',
                    'data' => $user
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'User not found'
                ];
            }
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Thêm người dùng mới
    public function createUser($data) {
        try {
            // Bắt đầu transaction
            $this->conn->beginTransaction();

            // 1. Tạo người dùng
            $query = "INSERT INTO " . $this->table_name . "
                     (username, password_hash, password_salt, is_active)
                     VALUES
                     (:username, :password_hash, :password_salt, :is_active)";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'username' => $data['username'],
                'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
                'password_salt' => bin2hex(random_bytes(16)),
                'is_active' => $data['is_active'] ?? 1
            ]);

            $user_id = $this->conn->lastInsertId();

            // 2. Tạo thông tin hồ sơ
            $query = "INSERT INTO user_profiles
                     (user_id, full_name, email, phone, department_id,
                      avatar_url, current_address, permanent_address,
                      id_number, id_issue_date, id_issue_place,
                      tax_code, insurance_code, bank_account,
                      bank_name, bank_branch)
                     VALUES
                     (:user_id, :full_name, :email, :phone, :department_id,
                      :avatar_url, :current_address, :permanent_address,
                      :id_number, :id_issue_date, :id_issue_place,
                      :tax_code, :insurance_code, :bank_account,
                      :bank_name, :bank_branch)";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'user_id' => $user_id,
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'department_id' => $data['department_id'],
                'avatar_url' => $data['avatar_url'] ?? null,
                'current_address' => $data['current_address'] ?? null,
                'permanent_address' => $data['permanent_address'] ?? null,
                'id_number' => $data['id_number'] ?? null,
                'id_issue_date' => $data['id_issue_date'] ?? null,
                'id_issue_place' => $data['id_issue_place'] ?? null,
                'tax_code' => $data['tax_code'] ?? null,
                'insurance_code' => $data['insurance_code'] ?? null,
                'bank_account' => $data['bank_account'] ?? null,
                'bank_name' => $data['bank_name'] ?? null,
                'bank_branch' => $data['bank_branch'] ?? null
            ]);

            // 3. Gán vai trò
            if (!empty($data['roles'])) {
                $this->assignUserRoles($user_id, $data['roles']);
            }

            // Commit transaction
            $this->conn->commit();

            return [
                'status' => 'success',
                'message' => 'User created successfully',
                'user_id' => $user_id
            ];
        } catch(Exception $e) {
            // Rollback transaction nếu có lỗi
            $this->conn->rollBack();
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Cập nhật thông tin người dùng
    public function updateUser($id, $data) {
        try {
            // Bắt đầu transaction
            $this->conn->beginTransaction();

            // 1. Cập nhật thông tin cơ bản
            $query = "UPDATE " . $this->table_name . "
                     SET is_active = :is_active,
                         updated_at = CURRENT_TIMESTAMP
                     WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'id' => $id,
                'is_active' => $data['is_active'] ?? 1
            ]);

            // 2. Cập nhật thông tin hồ sơ
            $query = "UPDATE user_profiles
                     SET full_name = :full_name,
                         email = :email,
                         phone = :phone,
                         department_id = :department_id,
                         avatar_url = :avatar_url,
                         current_address = :current_address,
                         permanent_address = :permanent_address,
                         id_number = :id_number,
                         id_issue_date = :id_issue_date,
                         id_issue_place = :id_issue_place,
                         tax_code = :tax_code,
                         insurance_code = :insurance_code,
                         bank_account = :bank_account,
                         bank_name = :bank_name,
                         bank_branch = :bank_branch
                     WHERE user_id = :user_id";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'user_id' => $id,
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'department_id' => $data['department_id'],
                'avatar_url' => $data['avatar_url'] ?? null,
                'current_address' => $data['current_address'] ?? null,
                'permanent_address' => $data['permanent_address'] ?? null,
                'id_number' => $data['id_number'] ?? null,
                'id_issue_date' => $data['id_issue_date'] ?? null,
                'id_issue_place' => $data['id_issue_place'] ?? null,
                'tax_code' => $data['tax_code'] ?? null,
                'insurance_code' => $data['insurance_code'] ?? null,
                'bank_account' => $data['bank_account'] ?? null,
                'bank_name' => $data['bank_name'] ?? null,
                'bank_branch' => $data['bank_branch'] ?? null
            ]);

            // 3. Cập nhật vai trò
            if (isset($data['roles'])) {
                $this->updateUserRoles($id, $data['roles']);
            }

            // Commit transaction
            $this->conn->commit();

            return [
                'status' => 'success',
                'message' => 'User updated successfully'
            ];
        } catch(Exception $e) {
            // Rollback transaction nếu có lỗi
            $this->conn->rollBack();
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Xóa người dùng
    public function deleteUser($id) {
        try {
            // Bắt đầu transaction
            $this->conn->beginTransaction();

            // 1. Xóa vai trò
            $query = "DELETE FROM user_roles WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $id);
            $stmt->execute();

            // 2. Xóa hồ sơ
            $query = "DELETE FROM user_profiles WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $id);
            $stmt->execute();

            // 3. Xóa người dùng
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            // Commit transaction
            $this->conn->commit();

            return [
                'status' => 'success',
                'message' => 'User deleted successfully'
            ];
        } catch(Exception $e) {
            // Rollback transaction nếu có lỗi
            $this->conn->rollBack();
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Các hàm helper
    private function getUserRoles($user_id) {
        $query = "SELECT r.* FROM roles r
                 INNER JOIN user_roles ur ON r.id = ur.role_id
                 WHERE ur.user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getUserPermissions($user_id) {
        $query = "SELECT DISTINCT p.* FROM permissions p
                 INNER JOIN role_permissions rp ON p.id = rp.permission_id
                 INNER JOIN user_roles ur ON rp.role_id = ur.role_id
                 WHERE ur.user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function assignUserRoles($user_id, $roles) {
        $query = "INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)";
        $stmt = $this->conn->prepare($query);
        
        foreach ($roles as $role_id) {
            $stmt->execute([
                'user_id' => $user_id,
                'role_id' => $role_id
            ]);
        }
    }

    private function updateUserRoles($user_id, $roles) {
        // Xóa tất cả vai trò cũ
        $query = "DELETE FROM user_roles WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        // Thêm vai trò mới
        if (!empty($roles)) {
            $this->assignUserRoles($user_id, $roles);
        }
    }

    private function getTotalUsers($params) {
        $query = "SELECT COUNT(DISTINCT u.id) as total 
                 FROM " . $this->table_name . " u
                 LEFT JOIN user_profiles up ON u.id = up.user_id
                 LEFT JOIN user_roles ur ON u.id = ur.user_id
                 WHERE 1=1";
        
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query .= " AND (u.username LIKE '%$search%' OR up.full_name LIKE '%$search%' OR up.email LIKE '%$search%')";
        }

        if (!empty($params['department_id'])) {
            $department_id = $params['department_id'];
            $query .= " AND up.department_id = $department_id";
        }

        if (!empty($params['role_id'])) {
            $role_id = $params['role_id'];
            $query .= " AND ur.role_id = $role_id";
        }

        if (isset($params['is_active'])) {
            $is_active = $params['is_active'] ? 1 : 0;
            $query .= " AND u.is_active = $is_active";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
}

// Xử lý request
$database = new Database();
$db = $database->getConnection();
$api = new UsersAPI($db);

$method = $_SERVER['REQUEST_METHOD'];
$response = [];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            $response = $api->getUser($_GET['id']);
        } else {
            $response = $api->getUsers($_GET);
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $response = $api->createUser($data);
        break;
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $_GET['id'];
        $response = $api->updateUser($id, $data);
        break;
    case 'DELETE':
        $id = $_GET['id'];
        $response = $api->deleteUser($id);
        break;
    default:
        $response = [
            'status' => 'error',
            'message' => 'Method not allowed'
        ];
        break;
}

echo json_encode($response); 