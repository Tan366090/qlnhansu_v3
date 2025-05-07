<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';

class ContractAPI {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch ($method) {
            case 'GET':
                $this->getContracts();
                break;
            case 'POST':
                $this->createContract();
                break;
            case 'PUT':
                $this->updateContract();
                break;
            case 'DELETE':
                $this->deleteContract();
                break;
            default:
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
        }
    }

    private function getContracts() {
        try {
            $sql = "SELECT c.*, up.full_name as employee_name 
                    FROM contracts c 
                    LEFT JOIN employees e ON c.employee_id = e.id 
                    LEFT JOIN users u ON e.user_id = u.user_id 
                    LEFT JOIN user_profiles up ON u.user_id = up.user_id 
                    WHERE 1=1";
            
            // Thêm điều kiện tìm kiếm
            if (isset($_GET['employee_id'])) {
                $sql .= " AND c.employee_id = :employee_id";
            }
            if (isset($_GET['contract_type'])) {
                $sql .= " AND c.contract_type = :contract_type";
            }
            if (isset($_GET['status'])) {
                $sql .= " AND c.status = :status";
            }

            // Thêm phân trang
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = ($page - 1) * $limit;
            $sql .= " ORDER BY c.created_at DESC LIMIT :limit OFFSET :offset";

            $stmt = $this->conn->prepare($sql);
            
            // Bind parameters
            if (isset($_GET['employee_id'])) {
                $stmt->bindParam(':employee_id', $_GET['employee_id']);
            }
            if (isset($_GET['contract_type'])) {
                $stmt->bindParam(':contract_type', $_GET['contract_type']);
            }
            if (isset($_GET['status'])) {
                $stmt->bindParam(':status', $_GET['status']);
            }
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            $contracts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Lấy tổng số bản ghi
            $countQuery = "SELECT COUNT(*) FROM contracts";
            $countStmt = $this->conn->prepare($countQuery);
            $countStmt->execute();
            $total = $countStmt->fetchColumn();

            echo json_encode([
                'success' => true,
                'data' => $contracts,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'total_pages' => ceil($total / $limit)
                ]
            ]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi kết nối cơ sở dữ liệu: ' . $e->getMessage()
            ]);
        }
    }

    private function createContract() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            if (!isset($data['employee_id']) || !isset($data['contract_type']) || 
                !isset($data['start_date']) || !isset($data['salary'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Thiếu thông tin bắt buộc']);
                return;
            }

            $query = "INSERT INTO contracts 
                     (employee_id, contract_code, contract_type, start_date, end_date, 
                      salary, salary_currency, status, file_url)
                     VALUES 
                     (:employee_id, :contract_code, :contract_type, :start_date, :end_date,
                      :salary, :salary_currency, :status, :file_url)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':employee_id' => $data['employee_id'],
                ':contract_code' => $data['contract_code'] ?? null,
                ':contract_type' => $data['contract_type'],
                ':start_date' => $data['start_date'],
                ':end_date' => $data['end_date'] ?? null,
                ':salary' => $data['salary'],
                ':salary_currency' => $data['salary_currency'] ?? 'VND',
                ':status' => $data['status'] ?? 'draft',
                ':file_url' => $data['file_url'] ?? null
            ]);

            $contractId = $this->conn->lastInsertId();
            
            echo json_encode([
                'success' => true,
                'message' => 'Tạo hợp đồng thành công',
                'data' => ['id' => $contractId]
            ]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi tạo hợp đồng: ' . $e->getMessage()
            ]);
        }
    }

    private function updateContract() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Thiếu ID hợp đồng']);
                return;
            }

            $query = "UPDATE contracts 
                     SET employee_id = :employee_id,
                         contract_code = :contract_code,
                         contract_type = :contract_type,
                         start_date = :start_date,
                         end_date = :end_date,
                         salary = :salary,
                         salary_currency = :salary_currency,
                         status = :status,
                         file_url = :file_url
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':id' => $data['id'],
                ':employee_id' => $data['employee_id'] ?? null,
                ':contract_code' => $data['contract_code'] ?? null,
                ':contract_type' => $data['contract_type'] ?? null,
                ':start_date' => $data['start_date'] ?? null,
                ':end_date' => $data['end_date'] ?? null,
                ':salary' => $data['salary'] ?? null,
                ':salary_currency' => $data['salary_currency'] ?? 'VND',
                ':status' => $data['status'] ?? null,
                ':file_url' => $data['file_url'] ?? null
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật hợp đồng thành công'
            ]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi cập nhật hợp đồng: ' . $e->getMessage()
            ]);
        }
    }

    private function deleteContract() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Thiếu ID hợp đồng']);
                return;
            }

            // Kiểm tra xem hợp đồng có đang active không
            $checkQuery = "SELECT status FROM contracts WHERE id = :id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->execute([':id' => $data['id']]);
            $contract = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($contract && $contract['status'] === 'active') {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Không thể xóa hợp đồng đang active'
                ]);
                return;
            }

            $query = "DELETE FROM contracts WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $data['id']]);

            echo json_encode([
                'success' => true,
                'message' => 'Xóa hợp đồng thành công'
            ]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi xóa hợp đồng: ' . $e->getMessage()
            ]);
        }
    }
}

$api = new ContractAPI();
$api->handleRequest(); 