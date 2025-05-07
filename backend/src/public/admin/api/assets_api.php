<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

class AssetsAPI {
    private $conn;
    private $table_name = "assets";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy danh sách tài sản
    public function getAssets($params = []) {
        try {
            $query = "SELECT a.*, d.name as department_name,
                     c.name as category_name, s.name as supplier_name,
                     e.full_name as assigned_to_name
                     FROM " . $this->table_name . " a
                     LEFT JOIN departments d ON a.department_id = d.id
                     LEFT JOIN asset_categories c ON a.category_id = c.id
                     LEFT JOIN suppliers s ON a.supplier_id = s.id
                     LEFT JOIN employees e ON a.assigned_to = e.id
                     WHERE 1=1";
            
            // Thêm điều kiện tìm kiếm
            if (!empty($params['search'])) {
                $search = $params['search'];
                $query .= " AND (a.asset_code LIKE '%$search%' OR a.name LIKE '%$search%')";
            }

            // Thêm điều kiện phòng ban
            if (!empty($params['department_id'])) {
                $department_id = $params['department_id'];
                $query .= " AND a.department_id = $department_id";
            }

            // Thêm điều kiện loại tài sản
            if (!empty($params['category_id'])) {
                $category_id = $params['category_id'];
                $query .= " AND a.category_id = $category_id";
            }

            // Thêm điều kiện nhà cung cấp
            if (!empty($params['supplier_id'])) {
                $supplier_id = $params['supplier_id'];
                $query .= " AND a.supplier_id = $supplier_id";
            }

            // Thêm điều kiện người sử dụng
            if (!empty($params['assigned_to'])) {
                $assigned_to = $params['assigned_to'];
                $query .= " AND a.assigned_to = $assigned_to";
            }

            // Thêm điều kiện trạng thái
            if (isset($params['status'])) {
                $status = $params['status'];
                $query .= " AND a.status = '$status'";
            }

            // Thêm điều kiện khoảng thời gian
            if (!empty($params['purchase_date_from']) && !empty($params['purchase_date_to'])) {
                $purchase_date_from = $params['purchase_date_from'];
                $purchase_date_to = $params['purchase_date_to'];
                $query .= " AND a.purchase_date BETWEEN '$purchase_date_from' AND '$purchase_date_to'";
            }

            // Thêm phân trang
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            $query .= " ORDER BY a.purchase_date DESC LIMIT $limit OFFSET $offset";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'status' => 'success',
                'data' => $assets,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $this->getTotalAssets($params)
                ]
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Lấy thông tin chi tiết tài sản
    public function getAssetDetail($id) {
        try {
            $query = "SELECT a.*, d.name as department_name,
                     c.name as category_name, s.name as supplier_name,
                     e.full_name as assigned_to_name
                     FROM " . $this->table_name . " a
                     LEFT JOIN departments d ON a.department_id = d.id
                     LEFT JOIN asset_categories c ON a.category_id = c.id
                     LEFT JOIN suppliers s ON a.supplier_id = s.id
                     LEFT JOIN employees e ON a.assigned_to = e.id
                     WHERE a.id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $asset = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($asset) {
                // Lấy danh sách lịch sử bảo trì
                $maintenance = $this->getAssetMaintenance($id);
                $asset['maintenance'] = $maintenance;

                // Lấy danh sách lịch sử sử dụng
                $assignments = $this->getAssetAssignments($id);
                $asset['assignments'] = $assignments;

                return [
                    'status' => 'success',
                    'data' => $asset
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Asset not found'
                ];
            }
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Tạo tài sản mới
    public function createAsset($data) {
        try {
            // Bắt đầu transaction
            $this->conn->beginTransaction();

            // 1. Tạo tài sản
            $query = "INSERT INTO " . $this->table_name . "
                     (asset_code, name, category_id, department_id,
                      supplier_id, purchase_date, purchase_cost,
                      warranty_period, status, description)
                     VALUES
                     (:asset_code, :name, :category_id, :department_id,
                      :supplier_id, :purchase_date, :purchase_cost,
                      :warranty_period, :status, :description)";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'asset_code' => $data['asset_code'],
                'name' => $data['name'],
                'category_id' => $data['category_id'],
                'department_id' => $data['department_id'],
                'supplier_id' => $data['supplier_id'],
                'purchase_date' => $data['purchase_date'],
                'purchase_cost' => $data['purchase_cost'],
                'warranty_period' => $data['warranty_period'],
                'status' => $data['status'] ?? 'available',
                'description' => $data['description']
            ]);

            $asset_id = $this->conn->lastInsertId();

            // 2. Thêm lịch sử sử dụng nếu có
            if (!empty($data['assignments'])) {
                $this->addAssetAssignments($asset_id, $data['assignments']);
            }

            // 3. Thêm lịch sử bảo trì nếu có
            if (!empty($data['maintenance'])) {
                $this->addAssetMaintenance($asset_id, $data['maintenance']);
            }

            // Commit transaction
            $this->conn->commit();

            return [
                'status' => 'success',
                'message' => 'Asset created successfully',
                'asset_id' => $asset_id
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

    // Cập nhật tài sản
    public function updateAsset($id, $data) {
        try {
            $query = "UPDATE " . $this->table_name . "
                     SET name = :name,
                         category_id = :category_id,
                         department_id = :department_id,
                         supplier_id = :supplier_id,
                         purchase_date = :purchase_date,
                         purchase_cost = :purchase_cost,
                         warranty_period = :warranty_period,
                         status = :status,
                         description = :description,
                         updated_at = CURRENT_TIMESTAMP
                     WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'id' => $id,
                'name' => $data['name'],
                'category_id' => $data['category_id'],
                'department_id' => $data['department_id'],
                'supplier_id' => $data['supplier_id'],
                'purchase_date' => $data['purchase_date'],
                'purchase_cost' => $data['purchase_cost'],
                'warranty_period' => $data['warranty_period'],
                'status' => $data['status'],
                'description' => $data['description']
            ]);

            return [
                'status' => 'success',
                'message' => 'Asset updated successfully'
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Xóa tài sản
    public function deleteAsset($id) {
        try {
            // Bắt đầu transaction
            $this->conn->beginTransaction();

            // 1. Xóa lịch sử sử dụng
            $query = "DELETE FROM asset_assignments WHERE asset_id = :asset_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':asset_id', $id);
            $stmt->execute();

            // 2. Xóa lịch sử bảo trì
            $query = "DELETE FROM asset_maintenance WHERE asset_id = :asset_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':asset_id', $id);
            $stmt->execute();

            // 3. Xóa tài sản
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            // Commit transaction
            $this->conn->commit();

            return [
                'status' => 'success',
                'message' => 'Asset deleted successfully'
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
    private function getAssetMaintenance($asset_id) {
        $query = "SELECT m.*, e.full_name as employee_name
                 FROM asset_maintenance m
                 LEFT JOIN employees e ON m.employee_id = e.id
                 WHERE m.asset_id = :asset_id
                 ORDER BY m.maintenance_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':asset_id', $asset_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getAssetAssignments($asset_id) {
        $query = "SELECT a.*, e.full_name as employee_name,
                 d.name as department_name
                 FROM asset_assignments a
                 LEFT JOIN employees e ON a.employee_id = e.id
                 LEFT JOIN departments d ON e.department_id = d.id
                 WHERE a.asset_id = :asset_id
                 ORDER BY a.assigned_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':asset_id', $asset_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function addAssetMaintenance($asset_id, $maintenance) {
        $query = "INSERT INTO asset_maintenance
                 (asset_id, employee_id, maintenance_date,
                  maintenance_type, description, cost)
                 VALUES
                 (:asset_id, :employee_id, :maintenance_date,
                  :maintenance_type, :description, :cost)";

        $stmt = $this->conn->prepare($query);
        foreach ($maintenance as $item) {
            $stmt->execute([
                'asset_id' => $asset_id,
                'employee_id' => $item['employee_id'],
                'maintenance_date' => $item['maintenance_date'],
                'maintenance_type' => $item['maintenance_type'],
                'description' => $item['description'],
                'cost' => $item['cost']
            ]);
        }
    }

    private function addAssetAssignments($asset_id, $assignments) {
        $query = "INSERT INTO asset_assignments
                 (asset_id, employee_id, assigned_date,
                  return_date, status, notes)
                 VALUES
                 (:asset_id, :employee_id, :assigned_date,
                  :return_date, :status, :notes)";

        $stmt = $this->conn->prepare($query);
        foreach ($assignments as $assignment) {
            $stmt->execute([
                'asset_id' => $asset_id,
                'employee_id' => $assignment['employee_id'],
                'assigned_date' => $assignment['assigned_date'],
                'return_date' => $assignment['return_date'] ?? null,
                'status' => $assignment['status'] ?? 'assigned',
                'notes' => $assignment['notes'] ?? null
            ]);
        }
    }

    private function getTotalAssets($params) {
        $query = "SELECT COUNT(*) as total 
                 FROM " . $this->table_name . " a
                 WHERE 1=1";
        
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query .= " AND (a.asset_code LIKE '%$search%' OR a.name LIKE '%$search%')";
        }

        if (!empty($params['department_id'])) {
            $department_id = $params['department_id'];
            $query .= " AND a.department_id = $department_id";
        }

        if (!empty($params['category_id'])) {
            $category_id = $params['category_id'];
            $query .= " AND a.category_id = $category_id";
        }

        if (!empty($params['supplier_id'])) {
            $supplier_id = $params['supplier_id'];
            $query .= " AND a.supplier_id = $supplier_id";
        }

        if (!empty($params['assigned_to'])) {
            $assigned_to = $params['assigned_to'];
            $query .= " AND a.assigned_to = $assigned_to";
        }

        if (isset($params['status'])) {
            $status = $params['status'];
            $query .= " AND a.status = '$status'";
        }

        if (!empty($params['purchase_date_from']) && !empty($params['purchase_date_to'])) {
            $purchase_date_from = $params['purchase_date_from'];
            $purchase_date_to = $params['purchase_date_to'];
            $query .= " AND a.purchase_date BETWEEN '$purchase_date_from' AND '$purchase_date_to'";
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
$api = new AssetsAPI($db);

$method = $_SERVER['REQUEST_METHOD'];
$response = [];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            $response = $api->getAssetDetail($_GET['id']);
        } else {
            $response = $api->getAssets($_GET);
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $response = $api->createAsset($data);
        break;
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $_GET['id'];
        $response = $api->updateAsset($id, $data);
        break;
    case 'DELETE':
        $id = $_GET['id'];
        $response = $api->deleteAsset($id);
        break;
    default:
        $response = [
            'status' => 'error',
            'message' => 'Method not allowed'
        ];
        break;
}

echo json_encode($response); 