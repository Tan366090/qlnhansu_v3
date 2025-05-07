<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

class ProjectsAPI {
    private $conn;
    private $table_name = "projects";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy danh sách dự án
    public function getProjects($params = []) {
        try {
            $query = "SELECT p.*, d.name as department_name,
                     e.full_name as manager_name, c.name as client_name
                     FROM " . $this->table_name . " p
                     LEFT JOIN departments d ON p.department_id = d.id
                     LEFT JOIN employees e ON p.manager_id = e.id
                     LEFT JOIN clients c ON p.client_id = c.id
                     WHERE 1=1";
            
            // Thêm điều kiện tìm kiếm
            if (!empty($params['search'])) {
                $search = $params['search'];
                $query .= " AND (p.project_code LIKE '%$search%' OR p.name LIKE '%$search%')";
            }

            // Thêm điều kiện phòng ban
            if (!empty($params['department_id'])) {
                $department_id = $params['department_id'];
                $query .= " AND p.department_id = $department_id";
            }

            // Thêm điều kiện quản lý dự án
            if (!empty($params['manager_id'])) {
                $manager_id = $params['manager_id'];
                $query .= " AND p.manager_id = $manager_id";
            }

            // Thêm điều kiện khách hàng
            if (!empty($params['client_id'])) {
                $client_id = $params['client_id'];
                $query .= " AND p.client_id = $client_id";
            }

            // Thêm điều kiện trạng thái
            if (isset($params['status'])) {
                $status = $params['status'];
                $query .= " AND p.status = '$status'";
            }

            // Thêm điều kiện khoảng thời gian
            if (!empty($params['start_date_from']) && !empty($params['start_date_to'])) {
                $start_date_from = $params['start_date_from'];
                $start_date_to = $params['start_date_to'];
                $query .= " AND p.start_date BETWEEN '$start_date_from' AND '$start_date_to'";
            }

            if (!empty($params['end_date_from']) && !empty($params['end_date_to'])) {
                $end_date_from = $params['end_date_from'];
                $end_date_to = $params['end_date_to'];
                $query .= " AND p.end_date BETWEEN '$end_date_from' AND '$end_date_to'";
            }

            // Thêm phân trang
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            $query .= " ORDER BY p.start_date DESC LIMIT $limit OFFSET $offset";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'status' => 'success',
                'data' => $projects,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $this->getTotalProjects($params)
                ]
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Lấy thông tin chi tiết dự án
    public function getProjectDetail($id) {
        try {
            $query = "SELECT p.*, d.name as department_name,
                     e.full_name as manager_name, c.name as client_name
                     FROM " . $this->table_name . " p
                     LEFT JOIN departments d ON p.department_id = d.id
                     LEFT JOIN employees e ON p.manager_id = e.id
                     LEFT JOIN clients c ON p.client_id = c.id
                     WHERE p.id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $project = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($project) {
                // Lấy danh sách thành viên
                $members = $this->getProjectMembers($id);
                $project['members'] = $members;

                // Lấy danh sách công việc
                $tasks = $this->getProjectTasks($id);
                $project['tasks'] = $tasks;

                return [
                    'status' => 'success',
                    'data' => $project
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Project not found'
                ];
            }
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Tạo dự án mới
    public function createProject($data) {
        try {
            // Bắt đầu transaction
            $this->conn->beginTransaction();

            // 1. Tạo dự án
            $query = "INSERT INTO " . $this->table_name . "
                     (project_code, name, department_id, manager_id,
                      client_id, start_date, end_date, budget,
                      status, description)
                     VALUES
                     (:project_code, :name, :department_id, :manager_id,
                      :client_id, :start_date, :end_date, :budget,
                      :status, :description)";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'project_code' => $data['project_code'],
                'name' => $data['name'],
                'department_id' => $data['department_id'],
                'manager_id' => $data['manager_id'],
                'client_id' => $data['client_id'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'budget' => $data['budget'],
                'status' => $data['status'] ?? 'planning',
                'description' => $data['description']
            ]);

            $project_id = $this->conn->lastInsertId();

            // 2. Thêm thành viên nếu có
            if (!empty($data['members'])) {
                $this->addProjectMembers($project_id, $data['members']);
            }

            // 3. Thêm công việc nếu có
            if (!empty($data['tasks'])) {
                $this->addProjectTasks($project_id, $data['tasks']);
            }

            // Commit transaction
            $this->conn->commit();

            return [
                'status' => 'success',
                'message' => 'Project created successfully',
                'project_id' => $project_id
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

    // Cập nhật dự án
    public function updateProject($id, $data) {
        try {
            $query = "UPDATE " . $this->table_name . "
                     SET name = :name,
                         department_id = :department_id,
                         manager_id = :manager_id,
                         client_id = :client_id,
                         start_date = :start_date,
                         end_date = :end_date,
                         budget = :budget,
                         status = :status,
                         description = :description,
                         updated_at = CURRENT_TIMESTAMP
                     WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'id' => $id,
                'name' => $data['name'],
                'department_id' => $data['department_id'],
                'manager_id' => $data['manager_id'],
                'client_id' => $data['client_id'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'budget' => $data['budget'],
                'status' => $data['status'],
                'description' => $data['description']
            ]);

            return [
                'status' => 'success',
                'message' => 'Project updated successfully'
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Xóa dự án
    public function deleteProject($id) {
        try {
            // Bắt đầu transaction
            $this->conn->beginTransaction();

            // 1. Xóa thành viên
            $query = "DELETE FROM project_members WHERE project_id = :project_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':project_id', $id);
            $stmt->execute();

            // 2. Xóa công việc
            $query = "DELETE FROM project_tasks WHERE project_id = :project_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':project_id', $id);
            $stmt->execute();

            // 3. Xóa dự án
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            // Commit transaction
            $this->conn->commit();

            return [
                'status' => 'success',
                'message' => 'Project deleted successfully'
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
    private function getProjectMembers($project_id) {
        $query = "SELECT pm.*, e.full_name as employee_name,
                 d.name as department_name
                 FROM project_members pm
                 LEFT JOIN employees e ON pm.employee_id = e.id
                 LEFT JOIN departments d ON e.department_id = d.id
                 WHERE pm.project_id = :project_id
                 ORDER BY pm.role ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':project_id', $project_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getProjectTasks($project_id) {
        $query = "SELECT t.*, e.full_name as assigned_to_name
                 FROM project_tasks t
                 LEFT JOIN employees e ON t.assigned_to = e.id
                 WHERE t.project_id = :project_id
                 ORDER BY t.due_date ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':project_id', $project_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function addProjectMembers($project_id, $members) {
        $query = "INSERT INTO project_members
                 (project_id, employee_id, role, join_date)
                 VALUES
                 (:project_id, :employee_id, :role, :join_date)";

        $stmt = $this->conn->prepare($query);
        foreach ($members as $member) {
            $stmt->execute([
                'project_id' => $project_id,
                'employee_id' => $member['employee_id'],
                'role' => $member['role'],
                'join_date' => $member['join_date'] ?? date('Y-m-d')
            ]);
        }
    }

    private function addProjectTasks($project_id, $tasks) {
        $query = "INSERT INTO project_tasks
                 (project_id, title, description, assigned_to,
                  due_date, priority, status)
                 VALUES
                 (:project_id, :title, :description, :assigned_to,
                  :due_date, :priority, :status)";

        $stmt = $this->conn->prepare($query);
        foreach ($tasks as $task) {
            $stmt->execute([
                'project_id' => $project_id,
                'title' => $task['title'],
                'description' => $task['description'],
                'assigned_to' => $task['assigned_to'],
                'due_date' => $task['due_date'],
                'priority' => $task['priority'] ?? 'medium',
                'status' => $task['status'] ?? 'pending'
            ]);
        }
    }

    private function getTotalProjects($params) {
        $query = "SELECT COUNT(*) as total 
                 FROM " . $this->table_name . " p
                 WHERE 1=1";
        
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query .= " AND (p.project_code LIKE '%$search%' OR p.name LIKE '%$search%')";
        }

        if (!empty($params['department_id'])) {
            $department_id = $params['department_id'];
            $query .= " AND p.department_id = $department_id";
        }

        if (!empty($params['manager_id'])) {
            $manager_id = $params['manager_id'];
            $query .= " AND p.manager_id = $manager_id";
        }

        if (!empty($params['client_id'])) {
            $client_id = $params['client_id'];
            $query .= " AND p.client_id = $client_id";
        }

        if (isset($params['status'])) {
            $status = $params['status'];
            $query .= " AND p.status = '$status'";
        }

        if (!empty($params['start_date_from']) && !empty($params['start_date_to'])) {
            $start_date_from = $params['start_date_from'];
            $start_date_to = $params['start_date_to'];
            $query .= " AND p.start_date BETWEEN '$start_date_from' AND '$start_date_to'";
        }

        if (!empty($params['end_date_from']) && !empty($params['end_date_to'])) {
            $end_date_from = $params['end_date_from'];
            $end_date_to = $params['end_date_to'];
            $query .= " AND p.end_date BETWEEN '$end_date_from' AND '$end_date_to'";
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
$api = new ProjectsAPI($db);

$method = $_SERVER['REQUEST_METHOD'];
$response = [];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            $response = $api->getProjectDetail($_GET['id']);
        } else {
            $response = $api->getProjects($_GET);
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $response = $api->createProject($data);
        break;
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $_GET['id'];
        $response = $api->updateProject($id, $data);
        break;
    case 'DELETE':
        $id = $_GET['id'];
        $response = $api->deleteProject($id);
        break;
    default:
        $response = [
            'status' => 'error',
            'message' => 'Method not allowed'
        ];
        break;
}

echo json_encode($response); 