<?php

class ActivitiesApi {
    public function getActivities($params = []) {
        try {
            $query = "SELECT a.*, u.username, u.email
                     FROM activities a
                     LEFT JOIN users u ON a.user_id = u.user_id
                     WHERE 1=1";
            
            // Thêm điều kiện tìm kiếm
            if (!empty($params['search'])) {
                $search = $params['search'];
                $query .= " AND (u.username LIKE '%$search%' OR u.email LIKE '%$search%' 
                          OR a.type LIKE '%$search%' OR a.description LIKE '%$search%')";
            }

            // Thêm điều kiện người dùng
            if (!empty($params['user_id'])) {
                $user_id = $params['user_id'];
                $query .= " AND a.user_id = $user_id";
            }

            // Thêm điều kiện loại hoạt động
            if (!empty($params['type'])) {
                $type = $params['type'];
                $query .= " AND a.type = '$type'";
            }

            // Thêm điều kiện trạng thái
            if (!empty($params['status'])) {
                $status = $params['status'];
                $query .= " AND a.status = '$status'";
            }

            // Thêm điều kiện thời gian
            if (!empty($params['start_date'])) {
                $start_date = $params['start_date'];
                $query .= " AND a.created_at >= '$start_date'";
            }

            if (!empty($params['end_date'])) {
                $end_date = $params['end_date'];
                $query .= " AND a.created_at <= '$end_date'";
            }

            // Thêm phân trang
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            $query .= " ORDER BY a.created_at DESC LIMIT $limit OFFSET $offset";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'status' => 'success',
                'data' => $activities,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $this->getTotalActivities($params)
                ]
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
} 