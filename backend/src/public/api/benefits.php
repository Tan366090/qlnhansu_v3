<?php
header('Content-Type: application/json');
require_once '../config/config.php';

class BenefitsAPI {
    private $conn;
    private $table_insurance = 'benefits_insurance';
    private $table_policies = 'benefits_policies';
    private $table_applications = 'benefits_applications';
    private $table_costs = 'benefits_costs';

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Insurance management
    public function getInsurance() {
        $sql = "SELECT * FROM {$this->table_insurance}";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function addInsurance($data) {
        $sql = "INSERT INTO {$this->table_insurance} (type, provider, coverage, premium, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssss", $data['type'], $data['provider'], $data['coverage'], $data['premium'], $data['status']);
        return $stmt->execute();
    }

    // Policy management
    public function getPolicies() {
        $sql = "SELECT * FROM {$this->table_policies}";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function addPolicy($data) {
        $sql = "INSERT INTO {$this->table_policies} (name, description, eligibility, benefits, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssss", $data['name'], $data['description'], $data['eligibility'], $data['benefits'], $data['status']);
        return $stmt->execute();
    }

    // Application management
    public function getApplications() {
        $sql = "SELECT * FROM {$this->table_applications}";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function addApplication($data) {
        $sql = "INSERT INTO {$this->table_applications} (employee_id, benefit_type, status, applied_date) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isss", $data['employee_id'], $data['benefit_type'], $data['status'], $data['applied_date']);
        return $stmt->execute();
    }

    public function updateApplicationStatus($id, $status) {
        $sql = "UPDATE {$this->table_applications} SET status = ?, approved_date = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $approved_date = $status === 'APPROVED' ? date('Y-m-d') : null;
        $stmt->bind_param("ssi", $status, $approved_date, $id);
        return $stmt->execute();
    }

    // Cost management
    public function calculateCost($period) {
        $sql = "SELECT b.type, COUNT(a.id) as employee_count, SUM(b.premium) as total_cost 
                FROM {$this->table_insurance} b 
                LEFT JOIN {$this->table_applications} a ON b.type = a.benefit_type 
                WHERE a.status = 'APPROVED' AND a.applied_date >= ?
                GROUP BY b.type";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $period);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function saveCost($data) {
        $sql = "INSERT INTO {$this->table_costs} (benefit_type, employee_count, total_cost, period) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sids", $data['benefit_type'], $data['employee_count'], $data['total_cost'], $data['period']);
        return $stmt->execute();
    }

    // Report generation
    public function generateReport($type, $period) {
        switch($type) {
            case 'insurance':
                return $this->getInsuranceReport($period);
            case 'applications':
                return $this->getApplicationsReport($period);
            case 'costs':
                return $this->getCostsReport($period);
            default:
                return [];
        }
    }

    private function getInsuranceReport($period) {
        $sql = "SELECT * FROM {$this->table_insurance} WHERE created_at >= ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $period);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    private function getApplicationsReport($period) {
        $sql = "SELECT * FROM {$this->table_applications} WHERE applied_date >= ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $period);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    private function getCostsReport($period) {
        $sql = "SELECT * FROM {$this->table_costs} WHERE period >= ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $period);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

// Initialize API
$benefitsAPI = new BenefitsAPI($conn);

// Handle requests
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch($method) {
    case 'GET':
        switch($action) {
            case 'insurance':
                echo json_encode($benefitsAPI->getInsurance());
                break;
            case 'policies':
                echo json_encode($benefitsAPI->getPolicies());
                break;
            case 'applications':
                echo json_encode($benefitsAPI->getApplications());
                break;
            case 'costs':
                $period = $_GET['period'] ?? date('Y-m-d');
                echo json_encode($benefitsAPI->calculateCost($period));
                break;
            case 'report':
                $type = $_GET['type'] ?? '';
                $period = $_GET['period'] ?? date('Y-m-d');
                echo json_encode($benefitsAPI->generateReport($type, $period));
                break;
            default:
                http_response_code(404);
                echo json_encode(['error' => 'Invalid action']);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        switch($action) {
            case 'add_insurance':
                echo json_encode(['success' => $benefitsAPI->addInsurance($data)]);
                break;
            case 'add_policy':
                echo json_encode(['success' => $benefitsAPI->addPolicy($data)]);
                break;
            case 'add_application':
                echo json_encode(['success' => $benefitsAPI->addApplication($data)]);
                break;
            case 'update_application':
                echo json_encode(['success' => $benefitsAPI->updateApplicationStatus($data['id'], $data['status'])]);
                break;
            case 'save_cost':
                echo json_encode(['success' => $benefitsAPI->saveCost($data)]);
                break;
            default:
                http_response_code(404);
                echo json_encode(['error' => 'Invalid action']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
?> 