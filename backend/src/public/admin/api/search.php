<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Get search parameters from POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    $category = $data['category'] ?? 'all';
    $keyword = $data['keyword'] ?? '';
    $dateFrom = $data['dateFrom'] ?? null;
    $dateTo = $data['dateTo'] ?? null;
    $status = $data['status'] ?? 'all';
    $sortBy = $data['sortBy'] ?? 'relevance';
    $sortOrder = $data['sortOrder'] ?? 'desc';

    $results = [];

    switch ($category) {
        case 'employees':
            $query = "SELECT 
                e.employee_id as id,
                CONCAT(e.first_name, ' ', e.last_name) as title,
                'Nhân viên' as category,
                p.position_name as description,
                e.created_at as date,
                CONCAT('/employees/view.php?id=', e.employee_id) as link
                FROM employees e
                LEFT JOIN positions p ON e.position_id = p.position_id
                WHERE 1=1";

            if ($keyword) {
                $query .= " AND (e.first_name LIKE :keyword OR e.last_name LIKE :keyword OR e.email LIKE :keyword)";
            }
            if ($status !== 'all') {
                $query .= " AND e.status = :status";
            }
            if ($dateFrom) {
                $query .= " AND e.created_at >= :dateFrom";
            }
            if ($dateTo) {
                $query .= " AND e.created_at <= :dateTo";
            }

            $query .= " ORDER BY " . ($sortBy === 'date' ? 'e.created_at' : 'e.first_name') . " " . $sortOrder;

            $stmt = $conn->prepare($query);
            if ($keyword) $stmt->bindValue(':keyword', "%$keyword%");
            if ($status !== 'all') $stmt->bindValue(':status', $status);
            if ($dateFrom) $stmt->bindValue(':dateFrom', $dateFrom);
            if ($dateTo) $stmt->bindValue(':dateTo', $dateTo);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;

        case 'documents':
            $query = "SELECT 
                d.document_id as id,
                d.title,
                dc.category_name as category,
                d.description,
                d.created_at as date,
                CONCAT('/documents/view.php?id=', d.document_id) as link
                FROM documents d
                LEFT JOIN document_categories dc ON d.category_id = dc.category_id
                WHERE 1=1";

            if ($keyword) {
                $query .= " AND (d.title LIKE :keyword OR d.description LIKE :keyword)";
            }
            if ($status !== 'all') {
                $query .= " AND d.status = :status";
            }
            if ($dateFrom) {
                $query .= " AND d.created_at >= :dateFrom";
            }
            if ($dateTo) {
                $query .= " AND d.created_at <= :dateTo";
            }

            $query .= " ORDER BY " . ($sortBy === 'date' ? 'd.created_at' : 'd.title') . " " . $sortOrder;

            $stmt = $conn->prepare($query);
            if ($keyword) $stmt->bindValue(':keyword', "%$keyword%");
            if ($status !== 'all') $stmt->bindValue(':status', $status);
            if ($dateFrom) $stmt->bindValue(':dateFrom', $dateFrom);
            if ($dateTo) $stmt->bindValue(':dateTo', $dateTo);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;

        case 'projects':
            $query = "SELECT 
                p.project_id as id,
                p.project_name as title,
                'Dự án' as category,
                p.description,
                p.start_date as date,
                CONCAT('/projects/view.php?id=', p.project_id) as link
                FROM projects p
                WHERE 1=1";

            if ($keyword) {
                $query .= " AND (p.project_name LIKE :keyword OR p.description LIKE :keyword)";
            }
            if ($status !== 'all') {
                $query .= " AND p.status = :status";
            }
            if ($dateFrom) {
                $query .= " AND p.start_date >= :dateFrom";
            }
            if ($dateTo) {
                $query .= " AND p.start_date <= :dateTo";
            }

            $query .= " ORDER BY " . ($sortBy === 'date' ? 'p.start_date' : 'p.project_name') . " " . $sortOrder;

            $stmt = $conn->prepare($query);
            if ($keyword) $stmt->bindValue(':keyword', "%$keyword%");
            if ($status !== 'all') $stmt->bindValue(':status', $status);
            if ($dateFrom) $stmt->bindValue(':dateFrom', $dateFrom);
            if ($dateTo) $stmt->bindValue(':dateTo', $dateTo);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;

        default:
            // Search across all categories
            $allResults = [];
            
            // Search employees
            $employeeQuery = "SELECT 
                e.employee_id as id,
                CONCAT(e.first_name, ' ', e.last_name) as title,
                'Nhân viên' as category,
                p.position_name as description,
                e.created_at as date,
                CONCAT('/employees/view.php?id=', e.employee_id) as link
                FROM employees e
                LEFT JOIN positions p ON e.position_id = p.position_id
                WHERE 1=1";
            
            if ($keyword) {
                $employeeQuery .= " AND (e.first_name LIKE :keyword OR e.last_name LIKE :keyword OR e.email LIKE :keyword)";
            }
            
            $stmt = $conn->prepare($employeeQuery);
            if ($keyword) $stmt->bindValue(':keyword', "%$keyword%");
            $stmt->execute();
            $allResults = array_merge($allResults, $stmt->fetchAll(PDO::FETCH_ASSOC));
            
            // Search documents
            $documentQuery = "SELECT 
                d.document_id as id,
                d.title,
                dc.category_name as category,
                d.description,
                d.created_at as date,
                CONCAT('/documents/view.php?id=', d.document_id) as link
                FROM documents d
                LEFT JOIN document_categories dc ON d.category_id = dc.category_id
                WHERE 1=1";
            
            if ($keyword) {
                $documentQuery .= " AND (d.title LIKE :keyword OR d.description LIKE :keyword)";
            }
            
            $stmt = $conn->prepare($documentQuery);
            if ($keyword) $stmt->bindValue(':keyword', "%$keyword%");
            $stmt->execute();
            $allResults = array_merge($allResults, $stmt->fetchAll(PDO::FETCH_ASSOC));
            
            // Search projects
            $projectQuery = "SELECT 
                p.project_id as id,
                p.project_name as title,
                'Dự án' as category,
                p.description,
                p.start_date as date,
                CONCAT('/projects/view.php?id=', p.project_id) as link
                FROM projects p
                WHERE 1=1";
            
            if ($keyword) {
                $projectQuery .= " AND (p.project_name LIKE :keyword OR p.description LIKE :keyword)";
            }
            
            $stmt = $conn->prepare($projectQuery);
            if ($keyword) $stmt->bindValue(':keyword', "%$keyword%");
            $stmt->execute();
            $allResults = array_merge($allResults, $stmt->fetchAll(PDO::FETCH_ASSOC));
            
            // Sort all results
            usort($allResults, function($a, $b) use ($sortBy, $sortOrder) {
                $valueA = $sortBy === 'date' ? strtotime($a['date']) : $a['title'];
                $valueB = $sortBy === 'date' ? strtotime($b['date']) : $b['title'];
                
                return $sortOrder === 'asc' ? $valueA <=> $valueB : $valueB <=> $valueA;
            });
            
            $results = $allResults;
            break;
    }

    echo json_encode([
        'success' => true,
        'data' => $results
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 