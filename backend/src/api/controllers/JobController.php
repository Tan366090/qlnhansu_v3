<?php
namespace App\Controllers;

use App\Utils\ResponseHandler;
use App\Config\Database;
use App\Utils\RBACHandler;
use App\Utils\Logger;

class JobController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function index() {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "SELECT j.*, d.name as department_name, 
                    COUNT(e.id) as employee_count 
                    FROM jobs j 
                    LEFT JOIN departments d ON j.department_id = d.id 
                    LEFT JOIN employees e ON j.id = e.job_id 
                    WHERE j.status = 'active' 
                    GROUP BY j.id 
                    ORDER BY j.created_at DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            
            $jobs = $stmt->fetchAll();
            
            return ResponseHandler::success($jobs);
        } catch (\Exception $e) {
            Logger::error("Failed to fetch jobs: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function show($id) {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "SELECT j.*, d.name as department_name, 
                    COUNT(e.id) as employee_count 
                    FROM jobs j 
                    LEFT JOIN departments d ON j.department_id = d.id 
                    LEFT JOIN employees e ON j.id = e.job_id 
                    WHERE j.id = :id AND j.status = 'active' 
                    GROUP BY j.id";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            $job = $stmt->fetch();
            
            if (!$job) {
                return ResponseHandler::error('Job not found', 404);
            }
            
            return ResponseHandler::success($job);
        } catch (\Exception $e) {
            Logger::error("Failed to fetch job: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function store() {
        try {
            $conn = $this->db->getConnection();
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['title']) || empty($data['department_id']) || 
                empty($data['description']) || empty($data['requirements'])) {
                return ResponseHandler::error('Missing required fields');
            }
            
            $conn->beginTransaction();
            
            $sql = "INSERT INTO jobs (title, department_id, description, 
                    requirements, salary_range, status) 
                    VALUES (:title, :department_id, :description, 
                    :requirements, :salary_range, 'active')";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':title' => $data['title'],
                ':department_id' => $data['department_id'],
                ':description' => $data['description'],
                ':requirements' => $data['requirements'],
                ':salary_range' => $data['salary_range'] ?? null
            ]);
            
            $jobId = $conn->lastInsertId();
            
            $conn->commit();
            
            Logger::info("New job created: {$data['title']}");
            
            return ResponseHandler::success(['id' => $jobId], 'Job created successfully');
        } catch (\Exception $e) {
            $conn->rollBack();
            Logger::error("Failed to create job: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function update($id) {
        try {
            $conn = $this->db->getConnection();
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['title']) || empty($data['description']) || 
                empty($data['requirements'])) {
                return ResponseHandler::error('Missing required fields');
            }
            
            $conn->beginTransaction();
            
            $sql = "UPDATE jobs 
                    SET title = :title, 
                        description = :description, 
                        requirements = :requirements, 
                        salary_range = :salary_range 
                    WHERE id = :id AND status = 'active'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':title' => $data['title'],
                ':description' => $data['description'],
                ':requirements' => $data['requirements'],
                ':salary_range' => $data['salary_range'] ?? null
            ]);
            
            if ($stmt->rowCount() === 0) {
                $conn->rollBack();
                return ResponseHandler::error('Job not found', 404);
            }
            
            $conn->commit();
            
            Logger::info("Job updated: {$data['title']}");
            
            return ResponseHandler::success(null, 'Job updated successfully');
        } catch (\Exception $e) {
            $conn->rollBack();
            Logger::error("Failed to update job: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function destroy($id) {
        try {
            $conn = $this->db->getConnection();
            
            $conn->beginTransaction();
            
            // Check if job has active employees
            $sql = "SELECT COUNT(*) as count FROM employees 
                    WHERE job_id = :job_id AND status = 'active'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':job_id' => $id]);
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                $conn->rollBack();
                return ResponseHandler::error('Cannot delete job with active employees');
            }
            
            $sql = "UPDATE jobs 
                    SET status = 'inactive' 
                    WHERE id = :id AND status = 'active'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            if ($stmt->rowCount() === 0) {
                $conn->rollBack();
                return ResponseHandler::error('Job not found', 404);
            }
            
            $conn->commit();
            
            Logger::info("Job deleted: {$id}");
            
            return ResponseHandler::success(null, 'Job deleted successfully');
        } catch (\Exception $e) {
            $conn->rollBack();
            Logger::error("Failed to delete job: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function getByDepartment($departmentId) {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "SELECT j.*, d.name as department_name, 
                    COUNT(e.id) as employee_count 
                    FROM jobs j 
                    LEFT JOIN departments d ON j.department_id = d.id 
                    LEFT JOIN employees e ON j.id = e.job_id 
                    WHERE j.department_id = :department_id AND j.status = 'active' 
                    GROUP BY j.id 
                    ORDER BY j.created_at DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':department_id' => $departmentId]);
            
            $jobs = $stmt->fetchAll();
            
            return ResponseHandler::success($jobs);
        } catch (\Exception $e) {
            Logger::error("Failed to fetch department jobs: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function getAvailablePositions() {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "SELECT j.*, d.name as department_name, 
                    COUNT(e.id) as employee_count 
                    FROM jobs j 
                    LEFT JOIN departments d ON j.department_id = d.id 
                    LEFT JOIN employees e ON j.id = e.job_id 
                    WHERE j.status = 'active' AND j.is_hiring = 1 
                    GROUP BY j.id 
                    ORDER BY j.created_at DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            
            $jobs = $stmt->fetchAll();
            
            return ResponseHandler::success($jobs);
        } catch (\Exception $e) {
            Logger::error("Failed to fetch available positions: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
} 