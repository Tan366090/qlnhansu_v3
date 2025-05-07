<?php
header('Content-Type: application/json');
require_once '../config/database.php';

class AdvancedSearch {
    private $conn;
    private $keyword;
    private $category;
    private $dateFrom;
    private $dateTo;
    private $status;
    private $sortBy;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function setParameters($params) {
        $this->keyword = $params['keyword'] ?? '';
        $this->category = $params['category'] ?? 'all';
        $this->dateFrom = $params['dateFrom'] ?? '';
        $this->dateTo = $params['dateTo'] ?? '';
        $this->status = $params['status'] ?? 'all';
        $this->sortBy = $params['sortBy'] ?? 'relevance';
    }

    public function search() {
        $results = [];
        
        switch ($this->category) {
            case 'employees':
                $results = $this->searchEmployees();
                break;
            case 'departments':
                $results = $this->searchDepartments();
                break;
            case 'documents':
                $results = $this->searchDocuments();
                break;
            case 'projects':
                $results = $this->searchProjects();
                break;
            default:
                $results = array_merge(
                    $this->searchEmployees(),
                    $this->searchDepartments(),
                    $this->searchDocuments(),
                    $this->searchProjects()
                );
        }

        return $this->sortResults($results);
    }

    private function searchEmployees() {
        $sql = "SELECT 
                    e.id,
                    e.employee_code,
                    e.full_name as title,
                    'Nhân viên' as category,
                    CONCAT('Mã NV: ', e.employee_code, ', Phòng ban: ', d.name) as description,
                    e.created_at as date,
                    CONCAT('/employees/view.html?id=', e.id) as url
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.id
                WHERE 1=1";

        if ($this->keyword) {
            $sql .= " AND (e.full_name LIKE ? OR e.employee_code LIKE ?)";
        }

        if ($this->status !== 'all') {
            $sql .= " AND e.status = ?";
        }

        if ($this->dateFrom && $this->dateTo) {
            $sql .= " AND e.created_at BETWEEN ? AND ?";
        }

        $stmt = $this->conn->prepare($sql);
        $params = [];

        if ($this->keyword) {
            $keyword = "%{$this->keyword}%";
            $params[] = $keyword;
            $params[] = $keyword;
        }

        if ($this->status !== 'all') {
            $params[] = $this->status;
        }

        if ($this->dateFrom && $this->dateTo) {
            $params[] = $this->dateFrom;
            $params[] = $this->dateTo;
        }

        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    private function searchDepartments() {
        $sql = "SELECT 
                    id,
                    name as title,
                    'Phòng ban' as category,
                    description,
                    created_at as date,
                    CONCAT('/departments/view.html?id=', id) as url
                FROM departments
                WHERE 1=1";

        if ($this->keyword) {
            $sql .= " AND (name LIKE ? OR description LIKE ?)";
        }

        if ($this->status !== 'all') {
            $sql .= " AND status = ?";
        }

        if ($this->dateFrom && $this->dateTo) {
            $sql .= " AND created_at BETWEEN ? AND ?";
        }

        $stmt = $this->conn->prepare($sql);
        $params = [];

        if ($this->keyword) {
            $keyword = "%{$this->keyword}%";
            $params[] = $keyword;
            $params[] = $keyword;
        }

        if ($this->status !== 'all') {
            $params[] = $this->status;
        }

        if ($this->dateFrom && $this->dateTo) {
            $params[] = $this->dateFrom;
            $params[] = $this->dateTo;
        }

        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    private function searchDocuments() {
        $sql = "SELECT 
                    d.id,
                    d.title,
                    'Tài liệu' as category,
                    d.description,
                    d.created_at as date,
                    CONCAT('/documents/view.html?id=', d.id) as url
                FROM documents d
                WHERE 1=1";

        if ($this->keyword) {
            $sql .= " AND (d.title LIKE ? OR d.description LIKE ?)";
        }

        if ($this->status !== 'all') {
            $sql .= " AND d.status = ?";
        }

        if ($this->dateFrom && $this->dateTo) {
            $sql .= " AND d.created_at BETWEEN ? AND ?";
        }

        $stmt = $this->conn->prepare($sql);
        $params = [];

        if ($this->keyword) {
            $keyword = "%{$this->keyword}%";
            $params[] = $keyword;
            $params[] = $keyword;
        }

        if ($this->status !== 'all') {
            $params[] = $this->status;
        }

        if ($this->dateFrom && $this->dateTo) {
            $params[] = $this->dateFrom;
            $params[] = $this->dateTo;
        }

        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    private function searchProjects() {
        $sql = "SELECT 
                    p.id,
                    p.name as title,
                    'Dự án' as category,
                    p.description,
                    p.created_at as date,
                    CONCAT('/projects/view.html?id=', p.id) as url
                FROM projects p
                WHERE 1=1";

        if ($this->keyword) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        }

        if ($this->status !== 'all') {
            $sql .= " AND p.status = ?";
        }

        if ($this->dateFrom && $this->dateTo) {
            $sql .= " AND p.created_at BETWEEN ? AND ?";
        }

        $stmt = $this->conn->prepare($sql);
        $params = [];

        if ($this->keyword) {
            $keyword = "%{$this->keyword}%";
            $params[] = $keyword;
            $params[] = $keyword;
        }

        if ($this->status !== 'all') {
            $params[] = $this->status;
        }

        if ($this->dateFrom && $this->dateTo) {
            $params[] = $this->dateFrom;
            $params[] = $this->dateTo;
        }

        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    private function sortResults($results) {
        switch ($this->sortBy) {
            case 'date':
                usort($results, function($a, $b) {
                    return strtotime($b['date']) - strtotime($a['date']);
                });
                break;
            case 'name':
                usort($results, function($a, $b) {
                    return strcmp($a['title'], $b['title']);
                });
                break;
            default: // relevance
                // Keep original order for now
                break;
        }
        return $results;
    }
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Initialize search
$search = new AdvancedSearch($conn);
$search->setParameters($data);

// Perform search
$results = $search->search();

// Return results
echo json_encode($results);
?> 