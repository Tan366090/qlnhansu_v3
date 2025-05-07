<?php
namespace App\Utils;

class SearchHandler {
    public static function buildSearchQuery($baseQuery, $searchFields, $searchTerm, $params = []) {
        if (empty($searchTerm)) {
            return ['query' => $baseQuery, 'params' => $params];
        }
        
        $searchConditions = [];
        foreach ($searchFields as $field) {
            $searchConditions[] = "$field LIKE :search_term";
        }
        
        $searchQuery = implode(' OR ', $searchConditions);
        $whereClause = strpos($baseQuery, 'WHERE') !== false ? 'AND' : 'WHERE';
        
        $baseQuery .= " $whereClause ($searchQuery)";
        $params[':search_term'] = "%$searchTerm%";
        
        return ['query' => $baseQuery, 'params' => $params];
    }
    
    public static function buildFilterQuery($baseQuery, $filters, $params = []) {
        if (empty($filters)) {
            return ['query' => $baseQuery, 'params' => $params];
        }
        
        $filterConditions = [];
        foreach ($filters as $field => $value) {
            if ($value !== null && $value !== '') {
                $paramName = ":filter_$field";
                $filterConditions[] = "$field = $paramName";
                $params[$paramName] = $value;
            }
        }
        
        if (!empty($filterConditions)) {
            $whereClause = strpos($baseQuery, 'WHERE') !== false ? 'AND' : 'WHERE';
            $baseQuery .= " $whereClause " . implode(' AND ', $filterConditions);
        }
        
        return ['query' => $baseQuery, 'params' => $params];
    }
    
    public static function buildSortQuery($baseQuery, $sortField, $sortOrder = 'ASC') {
        if (empty($sortField)) {
            return $baseQuery;
        }
        
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
        return "$baseQuery ORDER BY $sortField $sortOrder";
    }
} 