<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';

// Kết nối database
$database = new Database();
$db = $database->getConnection();

// Nhận dữ liệu từ request
$data = json_decode(file_get_contents('php://input'), true);

// Validate dữ liệu
if (!isset($data['keyword']) || !isset($data['category'])) {
    echo json_encode(['error' => 'Missing required parameters']);
    exit;
}

$keyword = $data['keyword'];
$category = $data['category'];
$dateFrom = $data['dateFrom'] ?? null;
$dateTo = $data['dateTo'] ?? null;
$status = $data['status'] ?? 'all';
$sortBy = $data['sortBy'] ?? 'relevance';

$results = [];

try {
    switch ($category) {
        case 'employees':
            $query = "SELECT id, CONCAT(first_name, ' ', last_name) as title, 'Nhân viên' as category, 
                     position as description, created_at as date, CONCAT('/employees/view.html?id=', id) as url
                     FROM employees 
                     WHERE (first_name LIKE :keyword OR last_name LIKE :keyword OR position LIKE :keyword)";
            
            if ($status !== 'all') {
                $query .= " AND status = :status";
            }
            
            if ($dateFrom && $dateTo) {
                $query .= " AND created_at BETWEEN :dateFrom AND :dateTo";
            }
            
            $query .= " ORDER BY " . ($sortBy === 'date' ? "created_at" : "first_name") . " DESC";
            
            $stmt = $db->prepare($query);
            $keyword = "%$keyword%";
            $stmt->bindParam(':keyword', $keyword);
            
            if ($status !== 'all') {
                $stmt->bindParam(':status', $status);
            }
            
            if ($dateFrom && $dateTo) {
                $stmt->bindParam(':dateFrom', $dateFrom);
                $stmt->bindParam(':dateTo', $dateTo);
            }
            
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        case 'departments':
            $query = "SELECT id, name as title, 'Phòng ban' as category, 
                     description, created_at as date, CONCAT('/departments/view.html?id=', id) as url
                     FROM departments 
                     WHERE name LIKE :keyword OR description LIKE :keyword";
            
            if ($dateFrom && $dateTo) {
                $query .= " AND created_at BETWEEN :dateFrom AND :dateTo";
            }
            
            $query .= " ORDER BY " . ($sortBy === 'date' ? "created_at" : "name") . " DESC";
            
            $stmt = $db->prepare($query);
            $keyword = "%$keyword%";
            $stmt->bindParam(':keyword', $keyword);
            
            if ($dateFrom && $dateTo) {
                $stmt->bindParam(':dateFrom', $dateFrom);
                $stmt->bindParam(':dateTo', $dateTo);
            }
            
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        case 'documents':
            $query = "SELECT id, title, 'Tài liệu' as category, 
                     description, created_at as date, CONCAT('/documents/view.html?id=', id) as url
                     FROM documents 
                     WHERE title LIKE :keyword OR description LIKE :keyword";
            
            if ($dateFrom && $dateTo) {
                $query .= " AND created_at BETWEEN :dateFrom AND :dateTo";
            }
            
            $query .= " ORDER BY " . ($sortBy === 'date' ? "created_at" : "title") . " DESC";
            
            $stmt = $db->prepare($query);
            $keyword = "%$keyword%";
            $stmt->bindParam(':keyword', $keyword);
            
            if ($dateFrom && $dateTo) {
                $stmt->bindParam(':dateFrom', $dateFrom);
                $stmt->bindParam(':dateTo', $dateTo);
            }
            
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        case 'projects':
            $query = "SELECT id, name as title, 'Dự án' as category, 
                     description, created_at as date, CONCAT('/projects/view.html?id=', id) as url
                     FROM projects 
                     WHERE name LIKE :keyword OR description LIKE :keyword";
            
            if ($status !== 'all') {
                $query .= " AND status = :status";
            }
            
            if ($dateFrom && $dateTo) {
                $query .= " AND created_at BETWEEN :dateFrom AND :dateTo";
            }
            
            $query .= " ORDER BY " . ($sortBy === 'date' ? "created_at" : "name") . " DESC";
            
            $stmt = $db->prepare($query);
            $keyword = "%$keyword%";
            $stmt->bindParam(':keyword', $keyword);
            
            if ($status !== 'all') {
                $stmt->bindParam(':status', $status);
            }
            
            if ($dateFrom && $dateTo) {
                $stmt->bindParam(':dateFrom', $dateFrom);
                $stmt->bindParam(':dateTo', $dateTo);
            }
            
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        default:
            // Tìm kiếm tất cả
            $results = [];
            break;
    }
    
    echo json_encode($results);
    
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?> 