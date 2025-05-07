<?php
header('Content-Type: application/json');
require_once '../config/config.php';

class RecruitmentAPI {
    private $conn;
    private $table_positions = 'recruitment_positions';
    private $table_candidates = 'recruitment_candidates';
    private $table_interviews = 'recruitment_interviews';
    private $table_evaluations = 'recruitment_evaluations';

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Position management
    public function getPositions() {
        $sql = "SELECT * FROM {$this->table_positions}";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function addPosition($data) {
        $sql = "INSERT INTO {$this->table_positions} (title, department, requirements, status) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssss", $data['title'], $data['department'], $data['requirements'], $data['status']);
        return $stmt->execute();
    }

    // Candidate management
    public function getCandidates() {
        $sql = "SELECT * FROM {$this->table_candidates}";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function addCandidate($data) {
        $sql = "INSERT INTO {$this->table_candidates} (name, email, phone, position, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssss", $data['name'], $data['email'], $data['phone'], $data['position'], $data['status']);
        return $stmt->execute();
    }

    // Interview management
    public function getInterviews() {
        $sql = "SELECT * FROM {$this->table_interviews}";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function scheduleInterview($data) {
        $sql = "INSERT INTO {$this->table_interviews} (candidate_id, interviewer_id, date, time, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iisss", $data['candidate_id'], $data['interviewer_id'], $data['date'], $data['time'], $data['status']);
        return $stmt->execute();
    }

    // Evaluation management
    public function addEvaluation($data) {
        $sql = "INSERT INTO {$this->table_evaluations} (candidate_id, technical_skills, soft_skills, experience, comments) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiiss", $data['candidate_id'], $data['technical_skills'], $data['soft_skills'], $data['experience'], $data['comments']);
        return $stmt->execute();
    }

    // Report generation
    public function generateReport($type, $period) {
        switch($type) {
            case 'positions':
                return $this->getPositionsReport($period);
            case 'candidates':
                return $this->getCandidatesReport($period);
            case 'interviews':
                return $this->getInterviewsReport($period);
            default:
                return [];
        }
    }

    private function getPositionsReport($period) {
        $sql = "SELECT * FROM {$this->table_positions} WHERE created_at >= ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $period);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    private function getCandidatesReport($period) {
        $sql = "SELECT * FROM {$this->table_candidates} WHERE created_at >= ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $period);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    private function getInterviewsReport($period) {
        $sql = "SELECT * FROM {$this->table_interviews} WHERE date >= ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $period);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

// Initialize API
$recruitmentAPI = new RecruitmentAPI($conn);

// Handle requests
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch($method) {
    case 'GET':
        switch($action) {
            case 'positions':
                echo json_encode($recruitmentAPI->getPositions());
                break;
            case 'candidates':
                echo json_encode($recruitmentAPI->getCandidates());
                break;
            case 'interviews':
                echo json_encode($recruitmentAPI->getInterviews());
                break;
            case 'report':
                $type = $_GET['type'] ?? '';
                $period = $_GET['period'] ?? date('Y-m-d');
                echo json_encode($recruitmentAPI->generateReport($type, $period));
                break;
            default:
                http_response_code(404);
                echo json_encode(['error' => 'Invalid action']);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        switch($action) {
            case 'add_position':
                echo json_encode(['success' => $recruitmentAPI->addPosition($data)]);
                break;
            case 'add_candidate':
                echo json_encode(['success' => $recruitmentAPI->addCandidate($data)]);
                break;
            case 'schedule_interview':
                echo json_encode(['success' => $recruitmentAPI->scheduleInterview($data)]);
                break;
            case 'add_evaluation':
                echo json_encode(['success' => $recruitmentAPI->addEvaluation($data)]);
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