<?php
namespace App\Controllers;

use App\Utils\ResponseHandler;
use App\Config\Database;
use App\Utils\RBACHandler;
use App\Utils\Logger;

class TrainingController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function index($page = 1, $perPage = 10) {
        try {
            $conn = $this->db->getConnection();
            
            // Get total count
            $sql = "SELECT COUNT(*) FROM trainings WHERE status = 'active'";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            
            $total = $stmt->fetchColumn();
            $totalPages = ceil($total / $perPage);
            $offset = ($page - 1) * $perPage;
            
            // Get paginated trainings
            $sql = "SELECT t.*, d.name as department_name,
                    COUNT(et.employee_id) as participant_count 
                    FROM trainings t 
                    LEFT JOIN departments d ON t.department_id = d.id 
                    LEFT JOIN employee_trainings et ON t.id = et.training_id 
                    WHERE t.status = 'active' 
                    GROUP BY t.id 
                    ORDER BY t.start_date DESC 
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            
            $trainings = $stmt->fetchAll();
            
            return ResponseHandler::success([
                'trainings' => $trainings,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'total_pages' => $totalPages
                ]
            ], 'Trainings retrieved successfully');
        } catch (\Exception $e) {
            Logger::error("Failed to get trainings: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function show($id) {
        try {
            $conn = $this->db->getConnection();
            
            // Get training details
            $sql = "SELECT t.*, d.name as department_name 
                    FROM trainings t 
                    LEFT JOIN departments d ON t.department_id = d.id 
                    WHERE t.id = :id AND t.status = 'active'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            $training = $stmt->fetch();
            
            if (!$training) {
                return ResponseHandler::error('Training not found', 404);
            }
            
            // Get participants
            $sql = "SELECT e.id, e.name, e.employee_code, et.status, 
                    et.completion_date, et.score 
                    FROM employee_trainings et 
                    JOIN employees e ON et.employee_id = e.id 
                    WHERE et.training_id = :training_id";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':training_id' => $id]);
            $participants = $stmt->fetchAll();
            
            $training['participants'] = $participants;
            
            return ResponseHandler::success($training, 'Training retrieved successfully');
        } catch (\Exception $e) {
            Logger::error("Failed to get training: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function store() {
        try {
            $conn = $this->db->getConnection();
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['title']) || empty($data['start_date']) || 
                empty($data['end_date']) || empty($data['trainer'])) {
                return ResponseHandler::error('Missing required fields');
            }
            
            $conn->beginTransaction();
            
            $sql = "INSERT INTO trainings (title, description, start_date, 
                    end_date, trainer, location, department_id, max_participants, 
                    status) VALUES (:title, :description, :start_date, 
                    :end_date, :trainer, :location, :department_id, 
                    :max_participants, 'active')";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':title' => $data['title'],
                ':description' => $data['description'] ?? null,
                ':start_date' => $data['start_date'],
                ':end_date' => $data['end_date'],
                ':trainer' => $data['trainer'],
                ':location' => $data['location'] ?? null,
                ':department_id' => $data['department_id'] ?? null,
                ':max_participants' => $data['max_participants'] ?? null
            ]);
            
            $trainingId = $conn->lastInsertId();
            
            // Add participants if provided
            if (!empty($data['participants'])) {
                $sql = "INSERT INTO employee_trainings (training_id, employee_id, 
                        status) VALUES (:training_id, :employee_id, 'enrolled')";
                
                $stmt = $conn->prepare($sql);
                
                foreach ($data['participants'] as $employeeId) {
                    $stmt->execute([
                        ':training_id' => $trainingId,
                        ':employee_id' => $employeeId
                    ]);
                }
            }
            
            $conn->commit();
            
            Logger::info("Training created: {$trainingId}");
            
            return ResponseHandler::success([
                'id' => $trainingId
            ], 'Training created successfully');
        } catch (\Exception $e) {
            $conn->rollBack();
            Logger::error("Failed to create training: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function update($id) {
        try {
            $conn = $this->db->getConnection();
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['title']) || empty($data['start_date']) || 
                empty($data['end_date']) || empty($data['trainer'])) {
                return ResponseHandler::error('Missing required fields');
            }
            
            $conn->beginTransaction();
            
            $sql = "UPDATE trainings 
                    SET title = :title, 
                        description = :description, 
                        start_date = :start_date, 
                        end_date = :end_date, 
                        trainer = :trainer, 
                        location = :location, 
                        department_id = :department_id, 
                        max_participants = :max_participants 
                    WHERE id = :id AND status = 'active'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':title' => $data['title'],
                ':description' => $data['description'] ?? null,
                ':start_date' => $data['start_date'],
                ':end_date' => $data['end_date'],
                ':trainer' => $data['trainer'],
                ':location' => $data['location'] ?? null,
                ':department_id' => $data['department_id'] ?? null,
                ':max_participants' => $data['max_participants'] ?? null
            ]);
            
            if ($stmt->rowCount() === 0) {
                $conn->rollBack();
                return ResponseHandler::error('Training not found', 404);
            }
            
            // Update participants if provided
            if (isset($data['participants'])) {
                // Remove existing participants
                $sql = "DELETE FROM employee_trainings WHERE training_id = :training_id";
                $stmt = $conn->prepare($sql);
                $stmt->execute([':training_id' => $id]);
                
                // Add new participants
                if (!empty($data['participants'])) {
                    $sql = "INSERT INTO employee_trainings (training_id, employee_id, 
                            status) VALUES (:training_id, :employee_id, 'enrolled')";
                    
                    $stmt = $conn->prepare($sql);
                    
                    foreach ($data['participants'] as $employeeId) {
                        $stmt->execute([
                            ':training_id' => $id,
                            ':employee_id' => $employeeId
                        ]);
                    }
                }
            }
            
            $conn->commit();
            
            Logger::info("Training updated: {$id}");
            
            return ResponseHandler::success(null, 'Training updated successfully');
        } catch (\Exception $e) {
            $conn->rollBack();
            Logger::error("Failed to update training: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function destroy($id) {
        try {
            $conn = $this->db->getConnection();
            
            $conn->beginTransaction();
            
            $sql = "UPDATE trainings 
                    SET status = 'deleted', 
                        deleted_at = NOW() 
                    WHERE id = :id AND status = 'active'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            if ($stmt->rowCount() === 0) {
                $conn->rollBack();
                return ResponseHandler::error('Training not found', 404);
            }
            
            $conn->commit();
            
            Logger::info("Training deleted: {$id}");
            
            return ResponseHandler::success(null, 'Training deleted successfully');
        } catch (\Exception $e) {
            $conn->rollBack();
            Logger::error("Failed to delete training: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function updateParticipantStatus($trainingId, $employeeId) {
        try {
            $conn = $this->db->getConnection();
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['status'])) {
                return ResponseHandler::error('Missing status field');
            }
            
            $validStatuses = ['enrolled', 'completed', 'failed', 'dropped'];
            if (!in_array($data['status'], $validStatuses)) {
                return ResponseHandler::error('Invalid status value');
            }
            
            $sql = "UPDATE employee_trainings 
                    SET status = :status, 
                        completion_date = :completion_date, 
                        score = :score 
                    WHERE training_id = :training_id 
                    AND employee_id = :employee_id";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':training_id' => $trainingId,
                ':employee_id' => $employeeId,
                ':status' => $data['status'],
                ':completion_date' => $data['completion_date'] ?? null,
                ':score' => $data['score'] ?? null
            ]);
            
            if ($stmt->rowCount() === 0) {
                return ResponseHandler::error('Training participant not found', 404);
            }
            
            Logger::info("Training participant status updated: Training {$trainingId}, Employee {$employeeId}");
            
            return ResponseHandler::success(null, 'Participant status updated successfully');
        } catch (\Exception $e) {
            Logger::error("Failed to update participant status: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
    
    public function getEmployeeTrainings($employeeId, $page = 1, $perPage = 10) {
        try {
            $conn = $this->db->getConnection();
            
            // Get total count
            $sql = "SELECT COUNT(*) FROM employee_trainings et 
                    JOIN trainings t ON et.training_id = t.id 
                    WHERE et.employee_id = :employee_id 
                    AND t.status = 'active'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':employee_id' => $employeeId]);
            
            $total = $stmt->fetchColumn();
            $totalPages = ceil($total / $perPage);
            $offset = ($page - 1) * $perPage;
            
            // Get paginated trainings
            $sql = "SELECT t.*, et.status as participant_status, 
                    et.completion_date, et.score 
                    FROM employee_trainings et 
                    JOIN trainings t ON et.training_id = t.id 
                    WHERE et.employee_id = :employee_id 
                    AND t.status = 'active' 
                    ORDER BY t.start_date DESC 
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':employee_id', $employeeId, \PDO::PARAM_INT);
            $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            
            $trainings = $stmt->fetchAll();
            
            return ResponseHandler::success([
                'trainings' => $trainings,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'total_pages' => $totalPages
                ]
            ], 'Employee trainings retrieved successfully');
        } catch (\Exception $e) {
            Logger::error("Failed to get employee trainings: " . $e->getMessage());
            return ResponseHandler::error($e->getMessage());
        }
    }
} 