<?php
namespace App\Utils;

class PaginationHandler {
    public static function paginate($query, $params = [], $page = 1, $perPage = 10) {
        $db = \App\Config\Database::getInstance();
        $conn = $db->getConnection();
        
        // Get total count
        $countQuery = preg_replace('/SELECT.*?FROM/', 'SELECT COUNT(*) as total FROM', $query);
        $countQuery = preg_replace('/ORDER BY.*$/', '', $countQuery);
        
        $stmt = $conn->prepare($countQuery);
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];
        
        // Calculate pagination
        $totalPages = ceil($total / $perPage);
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;
        
        // Add pagination to query
        $query .= " LIMIT :limit OFFSET :offset";
        $params[':limit'] = $perPage;
        $params[':offset'] = $offset;
        
        // Get paginated results
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $items = $stmt->fetchAll();
        
        return [
            'items' => $items,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => $totalPages,
                'has_next_page' => $page < $totalPages,
                'has_prev_page' => $page > 1
            ]
        ];
    }
} 