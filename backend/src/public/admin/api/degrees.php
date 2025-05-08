<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/database.php';

// Start session for basic state management
session_start();

// Set default session data if not exists (for development)
if (!isset($_SESSION['initialized'])) {
    $_SESSION['initialized'] = true;
    $_SESSION['last_access'] = time();
    $_SESSION['user_id'] = 1; // Set default user ID for development
    $_SESSION['role'] = 'admin'; // Set default role as admin for development
}

// Comment out authentication checks for development
/*
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized access'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Check if user has admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Access denied'], JSON_UNESCAPED_UNICODE);
    exit;
}
*/

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
            
            // Log the stats for debugging
            error_log("Dashboard stats: " . print_r($stats, true));
            
            return $stats;
        } catch (Exception $e) {
            error_log("Error in getDashboardStats: " . $e->getMessage());
            throw new Exception("Error getting dashboard stats: " . $e->getMessage());
        }
    }

    private function getTotalDegrees() {
        try {
            $query = "SELECT COUNT(*) as count FROM degrees";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['count'];
        } catch (Exception $e) {
            error_log("Error in getTotalDegrees: " . $e->getMessage());
            return 0;
        }
    }

    private function getTotalCertificates() {
        try {
            $query = "SELECT COUNT(*) as count FROM certificates";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['count'];
        } catch (Exception $e) {
            error_log("Error in getTotalCertificates: " . $e->getMessage());
            return 0;
        }
    }

    private function getTotalCourses() {
        try {
            $query = "SELECT COUNT(*) as count FROM training_courses WHERE status = 'active'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['count'];
        } catch (Exception $e) {
            error_log("Error in getTotalCourses: " . $e->getMessage());
            return 0;
        }
    }

    private function getActiveRegistrations() {
        try {
            $query = "SELECT COUNT(*) as count FROM training_registrations WHERE status IN ('registered', 'attended')";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['count'];
        } catch (Exception $e) {
            error_log("Error in getActiveRegistrations: " . $e->getMessage());
            return 0;
        }
    }

    private function getExpiringCertificates() {
        try {
            $query = "SELECT COUNT(*) as count FROM certificates 
                     WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['count'];
        } catch (Exception $e) {
            error_log("Error in getExpiringCertificates: " . $e->getMessage());
            return 0;
        }
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

    // Get all qualifications with pagination and filters
    public function getQualifications($page = 1, $limit = 10, $type = '', $status = '') {
        try {
            error_log("=== getQualifications called ===");
            error_log("Parameters: page=$page, limit=$limit, type=$type, status=$status");
            
            $offset = ($page - 1) * $limit;
            $where = [];
            $params = [];

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
            error_log("Where clause: " . $whereClause);
            error_log("Parameters: " . print_r($params, true));

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
            LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

            error_log("Final query: " . $query);
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Query results: " . print_r($data, true));

            // Get total count
            $countQuery = "SELECT COUNT(*) as count FROM (
                ($degreesQuery) UNION ALL ($certificatesQuery)
            ) as combined";
            $stmt = $this->conn->prepare($countQuery);
            $stmt->execute($params);
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            error_log("Total count: " . $total);

            $result = [
                'data' => $data,
                'total' => $total,
                'page' => (int)$page,
                'limit' => (int)$limit,
                'message' => $total > 0 ? 'Tìm thấy ' . $total . ' kết quả' : 'Không tìm thấy kết quả nào phù hợp'
            ];
            
            error_log("=== getQualifications completed ===");
            return $result;
        } catch (Exception $e) {
            error_log("Error in getQualifications: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
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

    // Create new degree or certificate
    public function createQualification($data) {
        try {
            $this->conn->beginTransaction();

            // Handle file upload if exists
            $attachment_url = null;
            if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../../uploads/qualifications/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $file_extension = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
                $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png'];
                
                if (!in_array($file_extension, $allowed_extensions)) {
                    throw new Exception('Chỉ chấp nhận file PDF, JPEG hoặc PNG');
                }

                if ($_FILES['attachment']['size'] > 5 * 1024 * 1024) {
                    throw new Exception('File không được vượt quá 5MB');
                }

                $filename = uniqid() . '.' . $file_extension;
                $filepath = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $filepath)) {
                    $attachment_url = 'uploads/qualifications/' . $filename;
                } else {
                    throw new Exception('Lỗi khi upload file');
                }
            }

            if ($data['type'] === 'degree') {
                $query = "INSERT INTO degrees (employee_id, degree_name, major, institution, graduation_date, gpa, attachment_url) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $this->conn->prepare($query);
                $stmt->execute([
                    $data['employee_id'],
                    $data['name'],
                    $data['major'] ?? null,
                    $data['organization'],
                    $data['issue_date'],
                    $data['gpa'] ?? null,
                    $attachment_url
                ]);
            } else {
                $query = "INSERT INTO certificates (employee_id, name, issuing_organization, issue_date, expiry_date, credential_id, file_url) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $this->conn->prepare($query);
                $stmt->execute([
                    $data['employee_id'],
                    $data['name'],
                    $data['organization'],
                    $data['issue_date'],
                    $data['expiry_date'] ?? null,
                    $data['credential_id'] ?? null,
                    $attachment_url
                ]);
            }

            $this->conn->commit();
            return ['success' => true, 'message' => 'Thêm thành công'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw new Exception("Error creating qualification: " . $e->getMessage());
        }
    }

    // Update existing degree or certificate
    public function updateQualification($data) {
        try {
            $this->conn->beginTransaction();

            // Handle file upload if exists
            $attachment_url = null;
            if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../../uploads/qualifications/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $file_extension = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
                $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png'];
                
                if (!in_array($file_extension, $allowed_extensions)) {
                    throw new Exception('Chỉ chấp nhận file PDF, JPEG hoặc PNG');
                }

                if ($_FILES['attachment']['size'] > 5 * 1024 * 1024) {
                    throw new Exception('File không được vượt quá 5MB');
                }

                $filename = uniqid() . '.' . $file_extension;
                $filepath = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $filepath)) {
                    $attachment_url = 'uploads/qualifications/' . $filename;

                    // Delete old file if exists
                    if ($data['type'] === 'degree') {
                        $query = "SELECT attachment_url FROM degrees WHERE degree_id = ?";
                    } else {
                        $query = "SELECT file_url FROM certificates WHERE id = ?";
                    }
                    $stmt = $this->conn->prepare($query);
                    $stmt->execute([$data['id']]);
                    $old_file = $stmt->fetchColumn();

                    if ($old_file && file_exists('../../' . $old_file)) {
                        unlink('../../' . $old_file);
                    }
                } else {
                    throw new Exception('Lỗi khi upload file');
                }
            }

            if ($data['type'] === 'degree') {
                $query = "UPDATE degrees 
                         SET employee_id = ?, degree_name = ?, major = ?, institution = ?, 
                             graduation_date = ?, gpa = ?, updated_at = NOW()";
                $params = [
                    $data['employee_id'],
                    $data['name'],
                    $data['major'] ?? null,
                    $data['organization'],
                    $data['issue_date'],
                    $data['gpa'] ?? null
                ];

                if ($attachment_url) {
                    $query .= ", attachment_url = ?";
                    $params[] = $attachment_url;
                }

                $query .= " WHERE degree_id = ?";
                $params[] = $data['id'];

                $stmt = $this->conn->prepare($query);
                $stmt->execute($params);
            } else {
                $query = "UPDATE certificates 
                         SET employee_id = ?, name = ?, issuing_organization = ?, issue_date = ?, 
                             expiry_date = ?, credential_id = ?, updated_at = NOW()";
                $params = [
                    $data['employee_id'],
                    $data['name'],
                    $data['organization'],
                    $data['issue_date'],
                    $data['expiry_date'] ?? null,
                    $data['credential_id'] ?? null
                ];

                if ($attachment_url) {
                    $query .= ", file_url = ?";
                    $params[] = $attachment_url;
                }

                $query .= " WHERE id = ?";
                $params[] = $data['id'];

                $stmt = $this->conn->prepare($query);
                $stmt->execute($params);
            }

            $this->conn->commit();
            return ['success' => true, 'message' => 'Cập nhật thành công'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw new Exception("Error updating qualification: " . $e->getMessage());
        }
    }

    // Delete degree or certificate
    public function deleteQualification($id, $type) {
        try {
            $this->conn->beginTransaction();

            if ($type === 'degree') {
                $query = "DELETE FROM degrees WHERE degree_id = ?";
            } else {
                $query = "DELETE FROM certificates WHERE id = ?";
            }

            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);

            $this->conn->commit();
            return ['success' => true, 'message' => 'Xóa thành công'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw new Exception("Error deleting qualification: " . $e->getMessage());
        }
    }

    // Get single qualification by ID
    public function getQualification($id, $type) {
        try {
            if ($type === 'degree') {
                $query = "SELECT d.*, e.name as employee_name 
                         FROM degrees d 
                         LEFT JOIN employees e ON e.id = d.employee_id 
                         WHERE d.degree_id = ?";
            } else {
                $query = "SELECT c.*, e.name as employee_name 
                         FROM certificates c 
                         LEFT JOIN employees e ON e.id = c.employee_id 
                         WHERE c.id = ?";
            }

            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error getting qualification: " . $e->getMessage());
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
    $type = $_GET['type'] ?? '';
    $status = $_GET['status'] ?? '';
    $employeeId = $_GET['employee_id'] ?? null;
    $courseId = $_GET['course_id'] ?? null;

    // Handle POST requests for create/update operations
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        switch($action) {
            case 'create':
                echo json_encode($api->createQualification($data), JSON_UNESCAPED_UNICODE);
                break;
            case 'update':
                echo json_encode($api->updateQualification($data), JSON_UNESCAPED_UNICODE);
                break;
            case 'delete':
                if (isset($data['id']) && isset($data['type'])) {
                    echo json_encode($api->deleteQualification($data['id'], $data['type']), JSON_UNESCAPED_UNICODE);
                } else {
                    echo json_encode(['error' => 'Missing required parameters'], JSON_UNESCAPED_UNICODE);
                }
                break;
            default:
                echo json_encode(['error' => 'Invalid action'], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    // Handle GET requests
    switch($action) {
        case 'dashboard_stats':
            try {
                error_log("Fetching dashboard stats...");
                $stats = $api->getDashboardStats();
                error_log("Dashboard stats result: " . print_r($stats, true));
                
                $response = ['success' => true, 'data' => $stats];
                error_log("Sending response: " . print_r($response, true));
                
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
            } catch (Exception $e) {
                error_log("Error in dashboard_stats: " . $e->getMessage());
                echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            }
            break;
        case 'export':
            try {
                error_log("=== Starting export process ===");
                error_log("Request parameters: " . print_r($_GET, true));
                
                // Get all data without pagination for export
                $result = $api->getQualifications(1, 1000, $type, $status);
                error_log("Raw result from getQualifications: " . print_r($result, true));
                
                if (!isset($result['data'])) {
                    error_log("No 'data' key in result");
                    throw new Exception('Cấu trúc dữ liệu không hợp lệ');
                }
                
                if (empty($result['data'])) {
                    error_log("Data array is empty");
                    throw new Exception('Không có dữ liệu để xuất');
                }
                
                $response = [
                    'success' => true,
                    'data' => $result['data']
                ];
                
                error_log("Final response: " . print_r($response, true));
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                error_log("=== Export process completed ===");
            } catch (Exception $e) {
                error_log("Export error: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
                echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            }
            break;
        case 'degree_distribution':
            try {
                $data = $api->getDegreeTypeDistribution();
                echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
            } catch (Exception $e) {
                echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            }
            break;
        case 'certificate_distribution':
            try {
                $data = $api->getCertificateOrgDistribution();
                echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
            } catch (Exception $e) {
                echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            }
            break;
        case 'list':
            try {
                $result = $api->getQualifications($page, $limit, $type, $status);
                echo json_encode(['success' => true, 'data' => $result], JSON_UNESCAPED_UNICODE);
            } catch (Exception $e) {
                echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            }
            break;
        case 'get':
            if (isset($_GET['id']) && isset($_GET['type'])) {
                try {
                    $data = $api->getQualification($_GET['id'], $_GET['type']);
                    echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
                } catch (Exception $e) {
                    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
                }
            } else {
                echo json_encode(['error' => 'Missing required parameters'], JSON_UNESCAPED_UNICODE);
            }
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