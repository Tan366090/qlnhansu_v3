<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

class DocumentsAPI {
    private $conn;
    private $table_name = "documents";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy danh sách tài liệu
    public function getDocuments($params = []) {
        try {
            $query = "SELECT d.*, c.name as category_name,
                     e.full_name as created_by_name, dep.name as department_name
                     FROM " . $this->table_name . " d
                     LEFT JOIN document_categories c ON d.category_id = c.id
                     LEFT JOIN employees e ON d.created_by = e.id
                     LEFT JOIN departments dep ON d.department_id = dep.id
                     WHERE 1=1";
            
            // Thêm điều kiện tìm kiếm
            if (!empty($params['search'])) {
                $search = $params['search'];
                $query .= " AND (d.document_code LIKE '%$search%' OR d.title LIKE '%$search%')";
            }

            // Thêm điều kiện phòng ban
            if (!empty($params['department_id'])) {
                $department_id = $params['department_id'];
                $query .= " AND d.department_id = $department_id";
            }

            // Thêm điều kiện loại tài liệu
            if (!empty($params['category_id'])) {
                $category_id = $params['category_id'];
                $query .= " AND d.category_id = $category_id";
            }

            // Thêm điều kiện người tạo
            if (!empty($params['created_by'])) {
                $created_by = $params['created_by'];
                $query .= " AND d.created_by = $created_by";
            }

            // Thêm điều kiện trạng thái
            if (isset($params['status'])) {
                $status = $params['status'];
                $query .= " AND d.status = '$status'";
            }

            // Thêm điều kiện khoảng thời gian
            if (!empty($params['created_at_from']) && !empty($params['created_at_to'])) {
                $created_at_from = $params['created_at_from'];
                $created_at_to = $params['created_at_to'];
                $query .= " AND d.created_at BETWEEN '$created_at_from' AND '$created_at_to'";
            }

            // Thêm phân trang
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            $query .= " ORDER BY d.created_at DESC LIMIT $limit OFFSET $offset";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'status' => 'success',
                'data' => $documents,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $this->getTotalDocuments($params)
                ]
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Lấy thông tin chi tiết tài liệu
    public function getDocumentDetail($id) {
        try {
            $query = "SELECT d.*, c.name as category_name,
                     e.full_name as created_by_name, dep.name as department_name
                     FROM " . $this->table_name . " d
                     LEFT JOIN document_categories c ON d.category_id = c.id
                     LEFT JOIN employees e ON d.created_by = e.id
                     LEFT JOIN departments dep ON d.department_id = dep.id
                     WHERE d.id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $document = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($document) {
                // Lấy danh sách phiên bản
                $versions = $this->getDocumentVersions($id);
                $document['versions'] = $versions;

                // Lấy danh sách phê duyệt
                $approvals = $this->getDocumentApprovals($id);
                $document['approvals'] = $approvals;

                return [
                    'status' => 'success',
                    'data' => $document
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Document not found'
                ];
            }
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Tạo tài liệu mới
    public function createDocument($data) {
        try {
            // Bắt đầu transaction
            $this->conn->beginTransaction();

            // 1. Tạo tài liệu
            $query = "INSERT INTO " . $this->table_name . "
                     (document_code, title, category_id, department_id,
                      created_by, version, status, description)
                     VALUES
                     (:document_code, :title, :category_id, :department_id,
                      :created_by, :version, :status, :description)";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'document_code' => $data['document_code'],
                'title' => $data['title'],
                'category_id' => $data['category_id'],
                'department_id' => $data['department_id'],
                'created_by' => $data['created_by'],
                'version' => $data['version'] ?? '1.0',
                'status' => $data['status'] ?? 'draft',
                'description' => $data['description']
            ]);

            $document_id = $this->conn->lastInsertId();

            // 2. Thêm phiên bản đầu tiên
            if (!empty($data['content'])) {
                $this->addDocumentVersion($document_id, [
                    'version' => $data['version'] ?? '1.0',
                    'content' => $data['content'],
                    'created_by' => $data['created_by']
                ]);
            }

            // 3. Thêm phê duyệt nếu có
            if (!empty($data['approvals'])) {
                $this->addDocumentApprovals($document_id, $data['approvals']);
            }

            // Commit transaction
            $this->conn->commit();

            return [
                'status' => 'success',
                'message' => 'Document created successfully',
                'document_id' => $document_id
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

    // Cập nhật tài liệu
    public function updateDocument($id, $data) {
        try {
            $query = "UPDATE " . $this->table_name . "
                     SET title = :title,
                         category_id = :category_id,
                         department_id = :department_id,
                         version = :version,
                         status = :status,
                         description = :description,
                         updated_at = CURRENT_TIMESTAMP
                     WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'id' => $id,
                'title' => $data['title'],
                'category_id' => $data['category_id'],
                'department_id' => $data['department_id'],
                'version' => $data['version'],
                'status' => $data['status'],
                'description' => $data['description']
            ]);

            return [
                'status' => 'success',
                'message' => 'Document updated successfully'
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Xóa tài liệu
    public function deleteDocument($id) {
        try {
            // Bắt đầu transaction
            $this->conn->beginTransaction();

            // 1. Xóa phiên bản
            $query = "DELETE FROM document_versions WHERE document_id = :document_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':document_id', $id);
            $stmt->execute();

            // 2. Xóa phê duyệt
            $query = "DELETE FROM document_approvals WHERE document_id = :document_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':document_id', $id);
            $stmt->execute();

            // 3. Xóa tài liệu
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            // Commit transaction
            $this->conn->commit();

            return [
                'status' => 'success',
                'message' => 'Document deleted successfully'
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
    private function getDocumentVersions($document_id) {
        $query = "SELECT v.*, e.full_name as created_by_name
                 FROM document_versions v
                 LEFT JOIN employees e ON v.created_by = e.id
                 WHERE v.document_id = :document_id
                 ORDER BY v.version DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':document_id', $document_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getDocumentApprovals($document_id) {
        $query = "SELECT a.*, e.full_name as approver_name
                 FROM document_approvals a
                 LEFT JOIN employees e ON a.approver_id = e.id
                 WHERE a.document_id = :document_id
                 ORDER BY a.approved_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':document_id', $document_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function addDocumentVersion($document_id, $version) {
        $query = "INSERT INTO document_versions
                 (document_id, version, content, created_by)
                 VALUES
                 (:document_id, :version, :content, :created_by)";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            'document_id' => $document_id,
            'version' => $version['version'],
            'content' => $version['content'],
            'created_by' => $version['created_by']
        ]);
    }

    private function addDocumentApprovals($document_id, $approvals) {
        $query = "INSERT INTO document_approvals
                 (document_id, approver_id, status, comment)
                 VALUES
                 (:document_id, :approver_id, :status, :comment)";

        $stmt = $this->conn->prepare($query);
        foreach ($approvals as $approval) {
            $stmt->execute([
                'document_id' => $document_id,
                'approver_id' => $approval['approver_id'],
                'status' => $approval['status'] ?? 'pending',
                'comment' => $approval['comment'] ?? null
            ]);
        }
    }

    private function getTotalDocuments($params) {
        $query = "SELECT COUNT(*) as total 
                 FROM " . $this->table_name . " d
                 WHERE 1=1";
        
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query .= " AND (d.document_code LIKE '%$search%' OR d.title LIKE '%$search%')";
        }

        if (!empty($params['department_id'])) {
            $department_id = $params['department_id'];
            $query .= " AND d.department_id = $department_id";
        }

        if (!empty($params['category_id'])) {
            $category_id = $params['category_id'];
            $query .= " AND d.category_id = $category_id";
        }

        if (!empty($params['created_by'])) {
            $created_by = $params['created_by'];
            $query .= " AND d.created_by = $created_by";
        }

        if (isset($params['status'])) {
            $status = $params['status'];
            $query .= " AND d.status = '$status'";
        }

        if (!empty($params['created_at_from']) && !empty($params['created_at_to'])) {
            $created_at_from = $params['created_at_from'];
            $created_at_to = $params['created_at_to'];
            $query .= " AND d.created_at BETWEEN '$created_at_from' AND '$created_at_to'";
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
$api = new DocumentsAPI($db);

$method = $_SERVER['REQUEST_METHOD'];
$response = [];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            $response = $api->getDocumentDetail($_GET['id']);
        } else {
            $response = $api->getDocuments($_GET);
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $response = $api->createDocument($data);
        break;
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $_GET['id'];
        $response = $api->updateDocument($id, $data);
        break;
    case 'DELETE':
        $id = $_GET['id'];
        $response = $api->deleteDocument($id);
        break;
    default:
        $response = [
            'status' => 'error',
            'message' => 'Method not allowed'
        ];
        break;
}

echo json_encode($response); 