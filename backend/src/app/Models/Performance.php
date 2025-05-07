<?php
namespace App\Models;

use PDO;
use PDOException;

class Performance extends BaseModel {
    protected $table = 'performance_reviews';
    protected $primaryKey = 'review_id';

    public function createPerformanceReview($employeeId, $reviewerId, $reviewPeriod, $performanceScore, $attendanceScore, $qualityScore, $teamworkScore, $leadershipScore, $comments, $status = 'draft') {
        try {
            $data = [
                'employee_id' => $employeeId,
                'reviewer_id' => $reviewerId,
                'review_period' => $reviewPeriod,
                'performance_score' => $performanceScore,
                'attendance_score' => $attendanceScore,
                'quality_score' => $qualityScore,
                'teamwork_score' => $teamworkScore,
                'leadership_score' => $leadershipScore,
                'total_score' => ($performanceScore + $attendanceScore + $qualityScore + $teamworkScore + $leadershipScore) / 5,
                'comments' => $comments,
                'status' => $status,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $reviewId = $this->create($data);
            return [
                'success' => true,
                'review_id' => $reviewId
            ];
        } catch (PDOException $e) {
            error_log("Create Performance Review Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Lỗi hệ thống'
            ];
        }
    }

    public function updatePerformanceReview($reviewId, $performanceScore, $attendanceScore, $qualityScore, $teamworkScore, $leadershipScore, $comments) {
        try {
            $data = [
                'performance_score' => $performanceScore,
                'attendance_score' => $attendanceScore,
                'quality_score' => $qualityScore,
                'teamwork_score' => $teamworkScore,
                'leadership_score' => $leadershipScore,
                'total_score' => ($performanceScore + $attendanceScore + $qualityScore + $teamworkScore + $leadershipScore) / 5,
                'comments' => $comments,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            return $this->update($reviewId, $data);
        } catch (PDOException $e) {
            error_log("Update Performance Review Error: " . $e->getMessage());
            return false;
        }
    }

    public function updateReviewStatus($reviewId, $status, $comments = null) {
        try {
            $data = [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($comments !== null) {
                $data['comments'] = $comments;
            }

            return $this->update($reviewId, $data);
        } catch (PDOException $e) {
            error_log("Update Review Status Error: " . $e->getMessage());
            return false;
        }
    }

    public function getReviewDetails($reviewId) {
        try {
            $query = "SELECT r.*, 
                     e.full_name as employee_name, e.employee_code, 
                     re.full_name as reviewer_name, re.employee_code as reviewer_code,
                     p.position_name, d.department_name 
                     FROM {$this->table} r
                     JOIN employees e ON r.employee_id = e.employee_id
                     JOIN employees re ON r.reviewer_id = re.employee_id
                     JOIN positions p ON e.position_id = p.position_id
                     JOIN departments d ON e.department_id = d.department_id
                     WHERE r.review_id = ?";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$reviewId]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Review Details Error: " . $e->getMessage());
            return false;
        }
    }

    public function getEmployeeReviews($employeeId, $status = null) {
        try {
            $query = "SELECT r.*, re.full_name as reviewer_name, re.employee_code as reviewer_code 
                     FROM {$this->table} r
                     JOIN employees re ON r.reviewer_id = re.employee_id
                     WHERE r.employee_id = ?";
            $params = [$employeeId];

            if ($status) {
                $query .= " AND r.status = ?";
                $params[] = $status;
            }

            $query .= " ORDER BY r.review_period DESC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Employee Reviews Error: " . $e->getMessage());
            return [];
        }
    }

    public function getDepartmentReviews($departmentId, $status = null) {
        try {
            $query = "SELECT r.*, e.full_name as employee_name, e.employee_code, re.full_name as reviewer_name 
                     FROM {$this->table} r
                     JOIN employees e ON r.employee_id = e.employee_id
                     JOIN employees re ON r.reviewer_id = re.employee_id
                     WHERE e.department_id = ?";
            $params = [$departmentId];

            if ($status) {
                $query .= " AND r.status = ?";
                $params[] = $status;
            }

            $query .= " ORDER BY e.full_name ASC, r.review_period DESC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Department Reviews Error: " . $e->getMessage());
            return [];
        }
    }

    public function getPendingReviews($reviewerId = null) {
        try {
            $query = "SELECT r.*, e.full_name as employee_name, e.employee_code, d.department_name 
                     FROM {$this->table} r
                     JOIN employees e ON r.employee_id = e.employee_id
                     JOIN departments d ON e.department_id = d.department_id
                     WHERE r.status = 'draft'";
            
            $params = [];
            if ($reviewerId) {
                $query .= " AND r.reviewer_id = ?";
                $params[] = $reviewerId;
            }

            $query .= " ORDER BY r.created_at ASC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Pending Reviews Error: " . $e->getMessage());
            return [];
        }
    }

    public function getPerformanceStats($departmentId = null) {
        try {
            $query = "SELECT 
                        COUNT(DISTINCT r.review_id) as total_reviews,
                        COUNT(DISTINCT r.employee_id) as employees_with_reviews,
                        COUNT(DISTINCT CASE WHEN r.status = 'completed' THEN r.review_id END) as completed_reviews,
                        COUNT(DISTINCT CASE WHEN r.status = 'draft' THEN r.review_id END) as pending_reviews,
                        AVG(r.total_score) as average_score,
                        MIN(r.total_score) as min_score,
                        MAX(r.total_score) as max_score
                     FROM {$this->table} r";
            
            $params = [];
            if ($departmentId) {
                $query .= " JOIN employees e ON r.employee_id = e.employee_id
                          WHERE e.department_id = ?";
                $params[] = $departmentId;
            }

            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Performance Stats Error: " . $e->getMessage());
            return false;
        }
    }

    public function searchReviews($keyword, $departmentId = null, $status = null) {
        try {
            $query = "SELECT r.*, e.full_name as employee_name, e.employee_code, re.full_name as reviewer_name, d.department_name 
                     FROM {$this->table} r
                     JOIN employees e ON r.employee_id = e.employee_id
                     JOIN employees re ON r.reviewer_id = re.employee_id
                     JOIN departments d ON e.department_id = d.department_id
                     WHERE (e.full_name LIKE ? OR re.full_name LIKE ? OR r.comments LIKE ?)";
            $params = ["%$keyword%", "%$keyword%", "%$keyword%"];

            if ($departmentId) {
                $query .= " AND e.department_id = ?";
                $params[] = $departmentId;
            }

            if ($status) {
                $query .= " AND r.status = ?";
                $params[] = $status;
            }

            $query .= " ORDER BY e.full_name ASC, r.review_period DESC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Search Reviews Error: " . $e->getMessage());
            return [];
        }
    }
} 