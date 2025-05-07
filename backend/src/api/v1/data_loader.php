<?php
header('Access-Control-Allow-Origin: http://127.0.0.1:5501'); // Allow specific origin
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');
require_once '../config/database.php'; // Ensure this file contains DB connection logic

$action = $_GET['action'] ?? '';

try {
    $db = new PDO($dsn, $username, $password, $options);

    switch ($action) {
        case 'getUsers':
            $stmt = $db->query("SELECT * FROM users");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $users]);
            break;

        case 'getDepartments':
            $stmt = $db->query("SELECT id, name, description, manager, employeeCount FROM departments");
            $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $departments]);
            break;

        case 'getDocuments':
            $stmt = $db->query("SELECT * FROM documents");
            $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $documents]);
            break;

        case 'getEmployees':
            $stmt = $db->query("SELECT id, name, department_id FROM employees");
            $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $employees]);
            break;

        case 'deleteDepartment':
            $id = $_GET['id'] ?? null;
            if ($id) {
                $stmt = $db->prepare("DELETE FROM departments WHERE id = :id");
                $stmt->execute(['id' => $id]);
                echo json_encode(['success' => true, 'message' => 'Phòng ban đã được xóa.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'ID phòng ban không hợp lệ.']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
