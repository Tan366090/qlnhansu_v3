<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';

class TrainingAPI {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
        $this->conn->exec("SET NAMES utf8mb4");
        $this->conn->exec("SET CHARACTER SET utf8mb4");
        $this->conn->exec("SET character_set_connection=utf8mb4");
    }

    // Get dashboard statistics
    public function getDashboardStats() {
        try {
            $stats = [
                'total_degrees' => $this->getTotalDegrees(),
                'total_certificates' => $this->getTotalCertificates(),
                'expiring_certificates' => $this->getExpiringCertificates(),
                'total_courses' => $this->getTotalCourses(),
                'active_registrations' => $this->getActiveRegistrations()
            ];
            return $stats;
        } catch (Exception $e) {
            throw new Exception("Error getting dashboard stats: " . $e->getMessage());
        }
    }

    private function getTotalDegrees() {
        $query = "SELECT COUNT(*) as count FROM degrees";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    private function getTotalCertificates() {
        $query = "SELECT COUNT(*) as count FROM certificates";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    private function getTotalCourses() {
        $query = "SELECT COUNT(*) as count FROM training_courses WHERE status = 'active'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    private function getActiveRegistrations() {
        $query = "SELECT COUNT(*) as count FROM training_registrations WHERE status IN ('registered', 'attended')";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    private function getExpiringCertificates() {
        $query = "SELECT COUNT(*) as count FROM certificates 
                 WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    // Get degree type distribution for chart
    public function getDegreeTypeDistribution() {
        try {
            $query = "SELECT degree_name, COUNT(*) as count 
                     FROM degrees 
                     GROUP BY degree_name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error getting degree distribution: " . $e->getMessage());
        }
    }

    // Get certificate organization distribution for chart
    public function getCertificateOrgDistribution() {
        try {
            $query = "SELECT issuing_organization, COUNT(*) as count 
                     FROM certificates 
                     GROUP BY issuing_organization";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error getting certificate distribution: " . $e->getMessage());
        }
    }

    // Get all degrees and certificates with pagination and filters
    public function getQualifications($page = 1, $limit = 10, $search = '', $type = '', $status = '') {
        try {
            $offset = ($page - 1) * $limit;
            $where = [];
            $params = [];

            if ($search) {
                $where[] = "(d.degree_name LIKE ? OR c.name LIKE ? OR e.name LIKE ?)";
                $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
            }

            if ($type === 'degree') {
                $where[] = "d.degree_id IS NOT NULL";
            } elseif ($type === 'certificate') {
                $where[] = "c.id IS NOT NULL";
            }

            if ($status) {
                switch ($status) {
                    case 'valid':
                        $where[] = "(c.expiry_date IS NULL OR c.expiry_date > CURDATE())";
                        break;
                    case 'expired':
                        $where[] = "c.expiry_date < CURDATE()";
                        break;
                    case 'expiring':
                        $where[] = "c.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
                        break;
                }
            }

            $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

            // First get degrees
            $degreesQuery = "SELECT 
                d.degree_id as id,
                'degree' as type,
                d.degree_name as name,
                COALESCE(e.name, 'Chưa có thông tin') as employee_name,
                d.institution as organization,
                d.graduation_date as issue_date,
                NULL as expiry_date,
                'valid' as status,
                COALESCE(d.attachment_url, '') as attachment_url,
                d.major,
                d.gpa,
                NULL as credential_id,
                d.created_at,
                d.updated_at
            FROM degrees d
            LEFT JOIN employees e ON e.id = d.employee_id
            $whereClause";

            // Then get certificates
            $certificatesQuery = "SELECT 
                c.id,
                'certificate' as type,
                c.name,
                COALESCE(e.name, 'Chưa có thông tin') as employee_name,
                c.issuing_organization as organization,
                c.issue_date,
                c.expiry_date,
                CASE 
                    WHEN c.expiry_date IS NULL THEN 'valid'
                    WHEN c.expiry_date < CURDATE() THEN 'expired'
                    WHEN c.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'expiring'
                    ELSE 'valid'
                END as status,
                COALESCE(c.file_url, '') as attachment_url,
                NULL as major,
                NULL as gpa,
                c.credential_id,
                c.created_at,
                c.updated_at
            FROM certificates c
            LEFT JOIN employees e ON e.id = c.employee_id
            $whereClause";

            // Combine results with pagination
            $query = "SELECT * FROM (
                ($degreesQuery) UNION ALL ($certificatesQuery)
            ) as combined 
            ORDER BY issue_date DESC, id DESC 
            LIMIT $limit OFFSET $offset";

            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get total count
            $countQuery = "SELECT COUNT(*) as count FROM (
                ($degreesQuery) UNION ALL ($certificatesQuery)
            ) as combined";
            $stmt = $this->conn->prepare($countQuery);
            $stmt->execute(array_slice($params, 0, -2)); // Remove limit and offset
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            return [
                'data' => $data,
                'total' => $total,
                'page' => (int)$page,
                'limit' => (int)$limit
            ];
        } catch (Exception $e) {
            throw new Exception("Error getting qualifications: " . $e->getMessage());
        }
    }

    // Get course list
    public function getCourses($page = 1, $limit = 10, $search = '') {
        try {
            $offset = ($page - 1) * $limit;
            $where = [];
            $params = [];

            if ($search) {
                $where[] = "(name LIKE ? OR description LIKE ?)";
                $params = array_merge($params, ["%$search%", "%$search%"]);
            }

            $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

            $query = "SELECT * FROM training_courses $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get total count
            $countQuery = "SELECT COUNT(*) as count FROM training_courses $whereClause";
            $stmt = $this->conn->prepare($countQuery);
            $stmt->execute(array_slice($params, 0, -2));
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            return [
                'data' => $data,
                'total' => $total,
                'page' => (int)$page,
                'limit' => (int)$limit
            ];
        } catch (Exception $e) {
            throw new Exception("Error getting courses: " . $e->getMessage());
        }
    }

    // Get employee registrations
    public function getEmployeeRegistrations($employeeId) {
        try {
            $query = "SELECT tr.*, tc.name as course_name, tc.description, tc.duration, tc.cost
                     FROM training_registrations tr
                     JOIN training_courses tc ON tr.course_id = tc.id
                     WHERE tr.employee_id = ?
                     ORDER BY tr.registration_date DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$employeeId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error getting employee registrations: " . $e->getMessage());
        }
    }

    // Get course evaluations
    public function getCourseEvaluations($courseId) {
        try {
            $query = "SELECT te.*, e.name as evaluator_name
                     FROM training_evaluations te
                     JOIN employees e ON te.evaluator_employee_id = e.id
                     WHERE te.registration_id IN (
                         SELECT id FROM training_registrations WHERE course_id = ?
                     )
                     ORDER BY te.evaluation_date DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$courseId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error getting course evaluations: " . $e->getMessage());
        }
    }
}

// Initialize database connection
try {
    $database = new Database();
    $db = $database->getConnection();
    $api = new TrainingAPI($db);

    // Get request parameters
    $action = $_GET['action'] ?? '';
    $page = $_GET['page'] ?? 1;
    $limit = $_GET['limit'] ?? 10;
    $search = $_GET['search'] ?? '';
    $type = $_GET['type'] ?? '';
    $status = $_GET['status'] ?? '';
    $employeeId = $_GET['employee_id'] ?? null;
    $courseId = $_GET['course_id'] ?? null;

    switch($action) {
        case 'dashboard_stats':
            echo json_encode($api->getDashboardStats(), JSON_UNESCAPED_UNICODE);
            break;
        case 'degree_distribution':
            echo json_encode($api->getDegreeTypeDistribution(), JSON_UNESCAPED_UNICODE);
            break;
        case 'certificate_distribution':
            echo json_encode($api->getCertificateOrgDistribution(), JSON_UNESCAPED_UNICODE);
            break;
        case 'list':
            $result = $api->getQualifications($page, $limit, $search, $type, $status);
            if (!isset($result['data'])) {
                throw new Exception('Invalid data format received from server');
            }
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            break;
        case 'courses':
            echo json_encode($api->getCourses($page, $limit, $search), JSON_UNESCAPED_UNICODE);
            break;
        case 'registrations':
            if ($employeeId) {
                echo json_encode($api->getEmployeeRegistrations($employeeId), JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(['error' => 'Employee ID is required'], JSON_UNESCAPED_UNICODE);
            }
            break;
        case 'evaluations':
            if ($courseId) {
                echo json_encode($api->getCourseEvaluations($courseId), JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(['error' => 'Course ID is required'], JSON_UNESCAPED_UNICODE);
            }
            break;
        default:
            echo json_encode(['error' => 'Invalid action'], JSON_UNESCAPED_UNICODE);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?> 