<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Database configuration
$db_host = 'localhost';
$db_name = 'qlnhansu';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));

// Handle different HTTP methods
switch($method) {
    case 'GET':
        if (isset($path_parts[5]) && is_numeric($path_parts[5])) {
            // Get user by ID
            $user_id = $path_parts[5];
            $stmt = $pdo->prepare("
                SELECT u.*, d.name as department_name, p.name as position_name
                FROM users u
                LEFT JOIN departments d ON u.department_id = d.id
                LEFT JOIN positions p ON u.position_id = p.id
                WHERE u.id = ?
            ");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Get user's activities
                $stmt = $pdo->prepare("SELECT * FROM activities WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
                $stmt->execute([$user_id]);
                $user['recent_activities'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Get user's attendance
                $stmt = $pdo->prepare("SELECT * FROM attendance WHERE user_id = ? ORDER BY attendance_date DESC LIMIT 10");
                $stmt->execute([$user_id]);
                $user['recent_attendance'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode($user);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'User not found']);
            }
        } else {
            // Get all users with pagination and filters
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = ($page - 1) * $limit;
            
            $where = [];
            $params = [];
            
            if (isset($_GET['department_id'])) {
                $where[] = "u.department_id = ?";
                $params[] = $_GET['department_id'];
            }
            
            if (isset($_GET['position_id'])) {
                $where[] = "u.position_id = ?";
                $params[] = $_GET['position_id'];
            }
            
            if (isset($_GET['status'])) {
                $where[] = "u.status = ?";
                $params[] = $_GET['status'];
            }
            
            $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
            
            // Get total count
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as total
                FROM users u
                $where_clause
            ");
            $stmt->execute($params);
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Get users
            $stmt = $pdo->prepare("
                SELECT u.*, d.name as department_name, p.name as position_name
                FROM users u
                LEFT JOIN departments d ON u.department_id = d.id
                LEFT JOIN positions p ON u.position_id = p.id
                $where_clause
                ORDER BY u.id DESC
                LIMIT ? OFFSET ?
            ");
            $params[] = $limit;
            $params[] = $offset;
            $stmt->execute($params);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'users' => $users
            ]);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        $required_fields = ['username', 'password', 'email', 'full_name', 'department_id', 'position_id'];
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "Missing required field: $field"]);
                exit;
            }
        }
        
        try {
            $pdo->beginTransaction();
            
            // Insert user
            $stmt = $pdo->prepare("
                INSERT INTO users (
                    username, password, email, full_name, department_id, position_id,
                    phone, address, gender, birth_date, status, created_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, NOW()
                )
            ");
            
            $stmt->execute([
                $data['username'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['email'],
                $data['full_name'],
                $data['department_id'],
                $data['position_id'],
                $data['phone'] ?? null,
                $data['address'] ?? null,
                $data['gender'] ?? null,
                $data['birth_date'] ?? null,
                $data['status'] ?? 'active'
            ]);
            
            $user_id = $pdo->lastInsertId();
            
            // Log activity
            $stmt = $pdo->prepare("
                INSERT INTO activities (
                    user_id, type, description, user_agent, ip_address, status, created_at
                ) VALUES (
                    ?, 'create_user', ?, ?, ?, 'success', NOW()
                )
            ");
            
            $stmt->execute([
                $user_id,
                "Created new user: {$data['username']}",
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                $_SERVER['REMOTE_ADDR'] ?? null
            ]);
            
            $pdo->commit();
            echo json_encode([
                'message' => 'User created successfully',
                'user_id' => $user_id
            ]);
        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create user: ' . $e->getMessage()]);
        }
        break;

    case 'PUT':
        if (isset($path_parts[5]) && is_numeric($path_parts[5])) {
            $user_id = $path_parts[5];
            $data = json_decode(file_get_contents('php://input'), true);
            
            try {
                $pdo->beginTransaction();
                
                // Update user
                $update_fields = [];
                $params = [];
                
                $allowed_fields = [
                    'username', 'email', 'full_name', 'department_id', 'position_id',
                    'phone', 'address', 'gender', 'birth_date', 'status'
                ];
                
                foreach ($allowed_fields as $field) {
                    if (isset($data[$field])) {
                        $update_fields[] = "$field = ?";
                        $params[] = $data[$field];
                    }
                }
                
                if (!empty($update_fields)) {
                    $params[] = $user_id;
                    $stmt = $pdo->prepare("
                        UPDATE users
                        SET " . implode(", ", $update_fields) . "
                        WHERE id = ?
                    ");
                    $stmt->execute($params);
                }
                
                // Log activity
                $stmt = $pdo->prepare("
                    INSERT INTO activities (
                        user_id, type, description, user_agent, ip_address, status, created_at
                    ) VALUES (
                        ?, 'update_user', ?, ?, ?, 'success', NOW()
                    )
                ");
                
                $stmt->execute([
                    $user_id,
                    "Updated user profile",
                    $_SERVER['HTTP_USER_AGENT'] ?? null,
                    $_SERVER['REMOTE_ADDR'] ?? null
                ]);
                
                $pdo->commit();
                echo json_encode(['message' => 'User updated successfully']);
            } catch (Exception $e) {
                $pdo->rollBack();
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update user: ' . $e->getMessage()]);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'User ID is required']);
        }
        break;

    case 'DELETE':
        if (isset($path_parts[5]) && is_numeric($path_parts[5])) {
            $user_id = $path_parts[5];
            
            try {
                $pdo->beginTransaction();
                
                // Delete user
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                
                // Log activity
                $stmt = $pdo->prepare("
                    INSERT INTO activities (
                        user_id, type, description, user_agent, ip_address, status, created_at
                    ) VALUES (
                        ?, 'delete_user', ?, ?, ?, 'success', NOW()
                    )
                ");
                
                $stmt->execute([
                    $user_id,
                    "Deleted user",
                    $_SERVER['HTTP_USER_AGENT'] ?? null,
                    $_SERVER['REMOTE_ADDR'] ?? null
                ]);
                
                $pdo->commit();
                echo json_encode(['message' => 'User deleted successfully']);
            } catch (Exception $e) {
                $pdo->rollBack();
                http_response_code(500);
                echo json_encode(['error' => 'Failed to delete user: ' . $e->getMessage()]);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'User ID is required']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?> 